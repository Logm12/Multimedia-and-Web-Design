<?php
// app/models/DoctorAvailabilityModel.php

class DoctorAvailabilityModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy các slot thời gian trống của một bác sĩ trong một khoảng ngày
     * @param int $doctorId ID của bác sĩ
     * @param string $startDate Ngày bắt đầu (Y-m-d)
     * @param string $endDate Ngày kết thúc (Y-m-d)
     * @return array Danh sách các slot trống
     */
    public function getAvailableSlotsByDoctorId($doctorId, $startDate, $endDate) {
        $this->db->query("
            SELECT AvailabilityID, DoctorID, AvailableDate, StartTime, EndTime
            FROM DoctorAvailability
            WHERE DoctorID = :doctorId
              AND AvailableDate BETWEEN :startDate AND :endDate
              AND IsBooked = FALSE
              AND SlotType = 'Working'
            ORDER BY AvailableDate ASC, StartTime ASC
        ");
        $this->db->bind(':doctorId', $doctorId);
        $this->db->bind(':startDate', $startDate);
        $this->db->bind(':endDate', $endDate);
        return $this->db->resultSet();
    }

    /**
     * Đánh dấu một slot là đã được đặt
     * @param int $availabilityId ID của slot
     * @param int $appointmentId ID của lịch hẹn (có thể null nếu chỉ muốn chặn slot)
     * @return bool
     */
    public function markSlotAsBooked($availabilityId, $appointmentId = null) {
        // Trong một ứng dụng thực tế, bạn có thể muốn kiểm tra xem slot này có thực sự trống không trước khi cập nhật
        // Hoặc dùng điều kiện WHERE IsBooked = FALSE trong câu UPDATE để tránh race condition cơ bản.
        // Tuy nhiên, việc kiểm tra trước khi gọi hàm này thường tốt hơn.

        $this->db->query("UPDATE DoctorAvailability SET IsBooked = TRUE WHERE AvailabilityID = :availabilityId AND IsBooked = FALSE");
        $this->db->bind(':availabilityId', $availabilityId);
        // Nếu cập nhật thành công (ít nhất 1 dòng bị ảnh hưởng - nghĩa là slot đó trống và được cập nhật)
        if ($this->db->execute() && $this->db->rowCount() > 0) {
            // Nếu có appointmentId, bạn có thể muốn liên kết nó (tuy nhiên bảng Appointments đã có AvailabilityID rồi)
            // Ví dụ: $this->db->query("UPDATE DoctorAvailability SET AppointmentLinkID = :appointmentId WHERE AvailabilityID = :availabilityId");
            // $this->db->bind(':appointmentId', $appointmentId); $this->db->bind(':availabilityId', $availabilityId); $this->db->execute();
            return true;
        }
        return false;
    }

     /**
     * Giải phóng một slot (đánh dấu là chưa đặt)
     * @param int $availabilityId ID của slot
     * @return bool
     */
    public function markSlotAsAvailable($availabilityId) {
        $this->db->query("UPDATE DoctorAvailability SET IsBooked = FALSE WHERE AvailabilityID = :availabilityId");
        $this->db->bind(':availabilityId', $availabilityId);
        return $this->db->execute();
    }
    /**
     * Lấy thông tin chi tiết của một slot bằng AvailabilityID
     * @param int $availabilityId
     * @return array|false
     */
    public function getSlotById($availabilityId) {
        $this->db->query('SELECT * FROM DoctorAvailability WHERE AvailabilityID = :availabilityId');
        $this->db->bind(':availabilityId', $availabilityId);
        return $this->db->single(); // Trả về một dòng (mảng) hoặc false
    }
    /**
 * Lấy tất cả các slot (làm việc, đã đặt, bị chặn) của một bác sĩ trong một khoảng ngày
 * @param int $doctorId
 * @param string $startDate (Y-m-d)
 * @param string $endDate (Y-m-d)
 * @return array
 */
public function getSlotsByDoctorForDateRange($doctorId, $startDate, $endDate) {
    $this->db->query("
        SELECT
            da.AvailabilityID,
            da.AvailableDate,
            da.StartTime,
            da.EndTime,
            da.IsBooked,
            da.SlotType,
            a.AppointmentID, -- Lấy AppointmentID nếu slot đã được đặt
            pat_user.FullName AS PatientName -- Lấy tên bệnh nhân nếu slot đã được đặt
        FROM DoctorAvailability da
        LEFT JOIN Appointments a ON da.AvailabilityID = a.AvailabilityID AND da.IsBooked = TRUE
        LEFT JOIN Patients pat_info ON a.PatientID = pat_info.PatientID
        LEFT JOIN Users pat_user ON pat_info.UserID = pat_user.UserID
        WHERE da.DoctorID = :doctor_id
          AND da.AvailableDate BETWEEN :start_date AND :end_date
        ORDER BY da.AvailableDate ASC, da.StartTime ASC
    ");
    $this->db->bind(':doctor_id', $doctorId);
    $this->db->bind(':start_date', $startDate);
    $this->db->bind(':end_date', $endDate);
    return $this->db->resultSet();
}
    /**
 * Kiểm tra xem có slot nào của bác sĩ bị chồng chéo với khoảng thời gian cho trước không
 * @param int $doctorId
 * @param string $date (Y-m-d)
 * @param string $startTime (H:i:s)
 * @param string $endTime (H:i:s)
 * @param int|null $excludeAvailabilityId ID của slot cần loại trừ khỏi việc kiểm tra (dùng khi cập nhật slot)
 * @return bool True nếu có chồng chéo, false nếu không
 */
public function checkSlotOverlap($doctorId, $date, $startTime, $endTime, $excludeAvailabilityId = null) {
    // Điều kiện chồng chéo:
    // (StartA < EndB) AND (EndA > StartB)
    $sql = "SELECT COUNT(*) as count
            FROM DoctorAvailability
            WHERE DoctorID = :doctor_id
              AND AvailableDate = :available_date
              AND StartTime < :end_time  -- Slot hiện tại bắt đầu trước khi slot mới kết thúc
              AND EndTime > :start_time  -- Slot hiện tại kết thúc sau khi slot mới bắt đầu";

    if ($excludeAvailabilityId !== null) {
        $sql .= " AND AvailabilityID != :exclude_id";
    }

    $this->db->query($sql);
    $this->db->bind(':doctor_id', $doctorId);
    $this->db->bind(':available_date', $date);
    $this->db->bind(':start_time', $startTime);
    $this->db->bind(':end_time', $endTime);

    if ($excludeAvailabilityId !== null) {
        $this->db->bind(':exclude_id', $excludeAvailabilityId);
    }

    $row = $this->db->single();
    return $row['count'] > 0;
}


/**
 * Tạo một slot làm việc mới cho bác sĩ
 * @param int $doctorId
 * @param string $date (Y-m-d)
 * @param string $startTime (H:i:s)
 * @param string $endTime (H:i:s)
 * @param string $slotType (Mặc định 'Working')
 * @return int|false AvailabilityID nếu thành công, false nếu thất bại
 */
public function createSlot($doctorId, $date, $startTime, $endTime, $slotType = 'Working') {
    // Kiểm tra chồng chéo trước khi tạo
    if ($this->checkSlotOverlap($doctorId, $date, $startTime, $endTime)) {
        // Ghi log hoặc ném Exception nếu muốn xử lý lỗi rõ ràng hơn ở controller
        error_log("Attempt to create overlapping slot for Doctor ID: {$doctorId} on {$date} from {$startTime} to {$endTime}");
        return false; // Hoặc ném một exception cụ thể
    }

    $this->db->query("
        INSERT INTO DoctorAvailability (DoctorID, AvailableDate, StartTime, EndTime, IsBooked, SlotType)
        VALUES (:doctor_id, :available_date, :start_time, :end_time, FALSE, :slot_type)
    ");
    $this->db->bind(':doctor_id', $doctorId);
    $this->db->bind(':available_date', $date);
    $this->db->bind(':start_time', $startTime);
    $this->db->bind(':end_time', $endTime);
    $this->db->bind(':slot_type', $slotType);

    if ($this->db->execute()) {
        return $this->db->lastInsertId();
    }
    return false;
}

// Phương thức để xóa slot (sẽ dùng sau)
/**
 * Xóa một slot trống của bác sĩ
 * @param int $availabilityId
 * @param int $doctorId
 * @return bool
 */
/**
 * Xóa một slot trống của bác sĩ (nếu nó thuộc về bác sĩ đó và chưa được đặt)
 * @param int $availabilityId
 * @param int $doctorId
 * @return bool True nếu xóa thành công (hoặc slot không tồn tại/không thỏa điều kiện để xóa), false nếu có lỗi DB
 */
public function deleteSlotByIdAndDoctor($availabilityId, $doctorId) {
    $this->db->query("
        DELETE FROM DoctorAvailability
        WHERE AvailabilityID = :availability_id
          AND DoctorID = :doctor_id
          AND IsBooked = FALSE -- Chỉ xóa nếu chưa được đặt
    ");
    $this->db->bind(':availability_id', $availabilityId);
    $this->db->bind(':doctor_id', $doctorId);
    
    // execute() trả về true nếu query thành công, không nhất thiết là có dòng bị xóa.
    // rowCount() sẽ cho biết số dòng bị ảnh hưởng.
    if ($this->db->execute()) {
        // return $this->db->rowCount() > 0; // Trả về true nếu thực sự có slot bị xóa
        return true; // Trả về true nếu query chạy OK, controller sẽ kiểm tra rowCount nếu cần thông báo chi tiết
    }
    return false;
}

/**
 * Cập nhật loại của một slot (ví dụ: từ Working sang Blocked hoặc ngược lại)
 * Chỉ cập nhật nếu slot thuộc về bác sĩ và chưa được đặt.
 * @param int $availabilityId
 * @param int $doctorId
 * @param string $newType ('Working', 'Blocked', etc.)
 * @return bool True nếu cập nhật thành công, false nếu có lỗi DB
 */
public function updateSlotTypeByIdAndDoctor($availabilityId, $doctorId, $newType) {
    // Kiểm tra xem newType có hợp lệ không (ví dụ: chỉ cho phép 'Working' hoặc 'Blocked')
    $allowedTypes = ['Working', 'Blocked']; // Mở rộng nếu có các type khác
    if (!in_array($newType, $allowedTypes)) {
        error_log("Invalid slot type '{$newType}' provided for update.");
        return false; // Hoặc ném Exception
    }

    $this->db->query("
        UPDATE DoctorAvailability
        SET SlotType = :new_type
        WHERE AvailabilityID = :availability_id
          AND DoctorID = :doctor_id
          AND IsBooked = FALSE -- Chỉ cập nhật nếu chưa được đặt
    ");
    $this->db->bind(':new_type', $newType);
    $this->db->bind(':availability_id', $availabilityId);
    $this->db->bind(':doctor_id', $doctorId);

    if ($this->db->execute()) {
        // return $this->db->rowCount() > 0; // True nếu có dòng được cập nhật
        return true;
    }
    return false;
}
}
?>