<?php
// app/models/DoctorAvailabilityModel.php

class DoctorAvailabilityModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAvailableSlotsByDoctorId($doctorId, $startDate, $endDate) {
        $this->db->query("SELECT AvailabilityID, DoctorID, AvailableDate, StartTime, EndTime 
                          FROM DoctorAvailability 
                          WHERE DoctorID = :doctorId 
                            AND AvailableDate BETWEEN :startDate AND :endDate 
                            AND IsBooked = FALSE 
                            AND SlotType = 'Working' 
                          ORDER BY AvailableDate ASC, StartTime ASC");
        $this->db->bind(':doctorId', $doctorId);
        $this->db->bind(':startDate', $startDate);
        $this->db->bind(':endDate', $endDate);
        return $this->db->resultSet();
    }

    public function markSlotAsBooked($availabilityId, $appointmentId = null) {
        // Chỉ update nếu slot chưa được book và là 'Working'
        // Nếu muốn cho phép book cả slot 'Blocked' (ít khả năng), thì bỏ điều kiện SlotType
        $this->db->query("UPDATE DoctorAvailability 
                          SET IsBooked = TRUE 
                          WHERE AvailabilityID = :availabilityId AND IsBooked = FALSE AND SlotType = 'Working'");
        $this->db->bind(':availabilityId', $availabilityId);
        // Không cần bind $appointmentId vào đây, việc liên kết Appointment với AvailabilityID đã có trong bảng Appointments
        return ($this->db->execute() && $this->db->rowCount() > 0);
    }

    public function markSlotAsAvailableAgain($availabilityId) { // Đổi tên từ markSlotAsAvailable cho rõ nghĩa hơn
        if (empty($availabilityId) || !filter_var($availabilityId, FILTER_VALIDATE_INT) || $availabilityId <=0) {
            return true; // Coi như thành công nếu không có ID hợp lệ để xử lý
        }
        // Khi hủy lịch hẹn, slot này nên trở về trạng thái 'Working' (nếu trước đó nó là 'Working')
        // và IsBooked = FALSE.
        // Nếu slot bị 'Blocked' thì không nên tự động chuyển thành 'Working'.
        $this->db->query("UPDATE DoctorAvailability 
                          SET IsBooked = FALSE 
                          WHERE AvailabilityID = :availabilityId");
        // Không nên tự động đổi SlotType ở đây, việc đó nên do bác sĩ quản lý.
        $this->db->bind(':availabilityId', $availabilityId);
        return $this->db->execute();
    }

    public function getSlotById($availabilityId) {
        if (!filter_var($availabilityId, FILTER_VALIDATE_INT) || $availabilityId <= 0) {
            return false;
        }
        $this->db->query('SELECT * FROM DoctorAvailability WHERE AvailabilityID = :availabilityId');
        $this->db->bind(':availabilityId', $availabilityId);
        return $this->db->single();
    }

    public function getSlotsByDoctorForDateRange($doctorId, $startDate, $endDate) {
        $this->db->query("SELECT da.AvailabilityID, da.AvailableDate, da.StartTime, da.EndTime, da.IsBooked, da.SlotType, 
                                 a.AppointmentID, pat_user.FullName AS PatientName 
                          FROM DoctorAvailability da 
                          LEFT JOIN Appointments a ON da.AvailabilityID = a.AvailabilityID AND da.IsBooked = TRUE 
                          LEFT JOIN Patients pat_info ON a.PatientID = pat_info.PatientID 
                          LEFT JOIN Users pat_user ON pat_info.UserID = pat_user.UserID 
                          WHERE da.DoctorID = :doctor_id 
                            AND da.AvailableDate BETWEEN :start_date AND :end_date 
                          ORDER BY da.AvailableDate ASC, da.StartTime ASC");
        $this->db->bind(':doctor_id', $doctorId);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        return $this->db->resultSet();
    }

    public function checkSlotOverlap($doctorId, $date, $startTime, $endTime, $excludeAvailabilityId = null) {
        $sql = "SELECT COUNT(*) as count 
                FROM DoctorAvailability 
                WHERE DoctorID = :doctor_id 
                  AND AvailableDate = :available_date 
                  AND StartTime < :end_time 
                  AND EndTime > :start_time";
        $sqlParams = [
            ':doctor_id' => $doctorId,
            ':available_date' => $date,
            ':start_time' => $startTime,
            ':end_time' => $endTime
        ];
        if ($excludeAvailabilityId !== null && filter_var($excludeAvailabilityId, FILTER_VALIDATE_INT)) {
            $sql .= " AND AvailabilityID != :exclude_id";
            $sqlParams[':exclude_id'] = $excludeAvailabilityId;
        }
        $this->db->query($sql);
        foreach($sqlParams as $key => $value) {
            $this->db->bind($key, $value);
        }
        $row = $this->db->single();
        return $row ? ($row['count'] > 0) : false;
    }

    public function createSingleSlot($doctorId, $date, $startTime, $endTime, $slotType = 'Working') {
        if ($this->checkSlotOverlap($doctorId, $date, $startTime, $endTime)) {
            error_log("Overlap detected for Doctor ID: {$doctorId} on {$date} from {$startTime} to {$endTime} - Slot not created.");
            return false; // Trả về false nếu có overlap
        }
        $this->db->query("INSERT INTO DoctorAvailability (DoctorID, AvailableDate, StartTime, EndTime, IsBooked, SlotType, CreatedAt, UpdatedAt) 
                          VALUES (:doctor_id, :available_date, :start_time, :end_time, FALSE, :slot_type, NOW(), NOW())");
        $this->db->bind(':doctor_id', $doctorId);
        $this->db->bind(':available_date', $date);
        $this->db->bind(':start_time', $startTime);
        $this->db->bind(':end_time', $endTime);
        $this->db->bind(':slot_type', $slotType);
        
        try {
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log("Error creating single slot: " . $e->getMessage());
        }
        return false;
    }

    public function createRecurringSlots($slotsData) {
        $createdCount = 0;
        $failedCount = 0;
        $messages = [];

        $initialDate = new DateTime($slotsData['slotDate']);
        $overallStartTimeObj = new DateTime($slotsData['slotDate'] . ' ' . $slotsData['startTime']);
        $overallEndTimeObj = new DateTime($slotsData['slotDate'] . ' ' . $slotsData['endTime']);
        
        if ($overallStartTimeObj >= $overallEndTimeObj) {
             return ['success' => false, 'created_count' => 0, 'failed_count' => 0, 'message' => "Start time must be before end time."];
        }
        if ($slotsData['durationMinutes'] <= 0) {
            return ['success' => false, 'created_count' => 0, 'failed_count' => 0, 'message' => "Slot duration must be positive."];
        }

        $durationInterval = new DateInterval('PT' . $slotsData['durationMinutes'] . 'M');
        $repeatUntilDateObj = $slotsData['repeatUntilDate'] ? new DateTime($slotsData['repeatUntilDate'] . ' 23:59:59') : null;
        $currentDate = clone $initialDate;

        do {
            $currentSlotStart = clone $currentDate;
            $currentSlotStart->setTime((int)$overallStartTimeObj->format('H'), (int)$overallStartTimeObj->format('i'));

            $currentDayOverallEndTime = clone $currentDate;
            $currentDayOverallEndTime->setTime((int)$overallEndTimeObj->format('H'), (int)$overallEndTimeObj->format('i'));

            while ($currentSlotStart < $currentDayOverallEndTime) {
                $currentSlotEnd = clone $currentSlotStart;
                $currentSlotEnd->add($durationInterval);

                if ($currentSlotEnd > $currentDayOverallEndTime) {
                    break; 
                }
                
                if ($this->createSingleSlot(
                    $slotsData['doctorId'], 
                    $currentSlotStart->format('Y-m-d'), 
                    $currentSlotStart->format('H:i:s'), 
                    $currentSlotEnd->format('H:i:s')
                )) {
                    $createdCount++;
                } else {
                    $failedCount++;
                    $messages[] = "Slot on " . $currentSlotStart->format('Y-m-d H:i') . " failed (likely overlap).";
                }
                $currentSlotStart = $currentSlotEnd;
            }

            if ($slotsData['repeatOption'] === 'none') break;
            
            $nextDate = clone $currentDate;
            if ($slotsData['repeatOption'] === 'daily') $nextDate->add(new DateInterval('P1D'));
            elseif ($slotsData['repeatOption'] === 'weekly') $nextDate->add(new DateInterval('P1W'));
            elseif ($slotsData['repeatOption'] === 'monthly') $nextDate->add(new DateInterval('P1M'));
            else break; 

            if ($repeatUntilDateObj && $nextDate > $repeatUntilDateObj) break;
            $currentDate = $nextDate;

        } while (true); // Điều kiện dừng được xử lý bên trong vòng lặp
        
        $finalMessage = $createdCount . " slot(s) created.";
        if ($failedCount > 0) {
            $finalMessage .= " " . $failedCount . " slot(s) failed (likely due to overlaps).";
            // $finalMessage .= " Details: " . implode("; ", $messages); // Có thể thêm chi tiết lỗi nếu muốn
        }
        if ($createdCount === 0 && $failedCount === 0) {
             $finalMessage = "No slots were created. Please check the date range, time range, and duration.";
        }
        return ['success' => $createdCount > 0, 'created_count' => $createdCount, 'failed_count' => $failedCount, 'message' => $finalMessage];
    }

    public function deleteSlotByIdAndDoctor($availabilityId, $doctorId) {
        // Chỉ xóa slot nếu nó chưa được đặt (IsBooked = FALSE)
        $this->db->query("DELETE FROM DoctorAvailability 
                          WHERE AvailabilityID = :availability_id 
                            AND DoctorID = :doctor_id 
                            AND IsBooked = FALSE");
        $this->db->bind(':availability_id', $availabilityId);
        $this->db->bind(':doctor_id', $doctorId);
        return $this->db->execute(); // Trả về true nếu query thành công, rowCount() sẽ cho biết có dòng nào bị xóa không
    }

    public function updateSlotTypeByIdAndDoctor($availabilityId, $doctorId, $newType) {
        $allowedTypes = ['Working', 'Blocked'];
        if (!in_array($newType, $allowedTypes)) {
            error_log("Invalid slot type '{$newType}' for update.");
            return false;
        }
        // Chỉ cho phép thay đổi type của slot chưa được đặt
        $this->db->query("UPDATE DoctorAvailability 
                          SET SlotType = :new_type, UpdatedAt = NOW() 
                          WHERE AvailabilityID = :availability_id 
                            AND DoctorID = :doctor_id 
                            AND IsBooked = FALSE");
        $this->db->bind(':new_type', $newType);
        $this->db->bind(':availability_id', $availabilityId);
        $this->db->bind(':doctor_id', $doctorId);
        return $this->db->execute();
    }

    public function updateSlotDateTime($availabilityId, $doctorId, $newDate, $newStartTime, $newEndTime) {
        // Kiểm tra xem slot có tồn tại, thuộc bác sĩ và chưa được đặt không
        $slot = $this->getSlotById($availabilityId);
        if (!$slot || $slot['DoctorID'] != $doctorId || $slot['IsBooked']) {
            return false; // Hoặc throw exception
        }
        // Kiểm tra overlap với các slot khác, loại trừ slot hiện tại
        if ($this->checkSlotOverlap($doctorId, $newDate, $newStartTime, $newEndTime, $availabilityId)) {
            return false; // Có overlap
        }
        $this->db->query("UPDATE DoctorAvailability 
                          SET AvailableDate = :new_date, StartTime = :new_start_time, EndTime = :new_end_time, UpdatedAt = NOW() 
                          WHERE AvailabilityID = :availability_id");
        $this->db->bind(':new_date', $newDate);
        $this->db->bind(':new_start_time', $newStartTime);
        $this->db->bind(':new_end_time', $newEndTime);
        $this->db->bind(':availability_id', $availabilityId);
        return $this->db->execute();
    }

    // --- HÀM MỚI CHO CHỨC NĂNG NGHỈ PHÉP ---
    /**
     * Vô hiệu hóa (hoặc xóa) các slot làm việc của bác sĩ trong một khoảng thời gian nghỉ phép.
     * Chỉ xử lý các slot 'Working' và chưa được đặt (IsBooked = FALSE).
     * Các slot đã được đặt cần được xử lý riêng (ví dụ: hủy lịch hẹn).
     *
     * @param int $doctorId ID của bác sĩ.
     * @param string $startDate Ngày bắt đầu nghỉ (YYYY-MM-DD).
     * @param string $endDate Ngày kết thúc nghỉ (YYYY-MM-DD).
     * @return bool True nếu có ít nhất một hành động được thực hiện, false nếu không hoặc có lỗi.
     */
    public function deactivateSlotsForLeave($doctorId, $startDate, $endDate) {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0 ||
            !DateTime::createFromFormat('Y-m-d', $startDate) ||
            !DateTime::createFromFormat('Y-m-d', $endDate) ||
            strtotime($endDate) < strtotime($startDate)) {
            error_log("Invalid parameters for deactivateSlotsForLeave.");
            return false;
        }

        try {
            // Cách 1: Xóa các slot trống trong khoảng thời gian nghỉ
            // $this->db->query("DELETE FROM DoctorAvailability
            //                   WHERE DoctorID = :doctor_id
            //                     AND AvailableDate BETWEEN :start_date AND :end_date
            //                     AND IsBooked = FALSE
            //                     AND SlotType = 'Working'");

            // Cách 2: Đánh dấu các slot trống là 'Blocked' (hoặc một loại mới 'OnLeave')
            // Nếu bảng DoctorAvailability chưa có SlotType ENUM('Working', 'Blocked', 'OnLeave'), cậu cần thêm 'OnLeave'
            // ALTER TABLE DoctorAvailability MODIFY SlotType ENUM('Working','Blocked','OnLeave') DEFAULT 'Working';
            $this->db->query("UPDATE DoctorAvailability
                              SET SlotType = 'Blocked', IsBooked = FALSE, Notes = CONCAT(IFNULL(Notes, ''), ' Auto-blocked due to approved leave.') 
                              WHERE DoctorID = :doctor_id
                                AND AvailableDate BETWEEN :start_date AND :end_date
                                AND IsBooked = FALSE 
                                AND SlotType = 'Working'");
            // IsBooked = FALSE để đảm bảo không block lại slot đã có người đặt (việc hủy lịch hẹn đó sẽ do logic khác xử lý)
            
            $this->db->bind(':doctor_id', $doctorId);
            $this->db->bind(':start_date', $startDate);
            $this->db->bind(':end_date', $endDate);
            
            $this->db->execute();
            return $this->db->rowCount() >= 0; // Trả về true nếu query chạy, dù có thể không có dòng nào được update
        } catch (PDOException $e) {
            error_log("Error in deactivateSlotsForLeave (DoctorID: {$doctorId}): " . $e->getMessage());
            return false;
        }
    }
}
?>