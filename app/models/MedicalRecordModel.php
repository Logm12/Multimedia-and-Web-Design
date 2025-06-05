<?php
// app/models/MedicalRecordModel.php

class MedicalRecordModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getRecordByAppointmentId($appointmentId) {
        if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0) {
            return false;
        }
        $this->db->query("SELECT * FROM MedicalRecords WHERE AppointmentID = :appointment_id");
        $this->db->bind(':appointment_id', $appointmentId);
        return $this->db->single();
    }

    public function saveMedicalRecord(
        $appointmentId,
        $patientId,
        $doctorId,
        $visitDate,
        $symptoms = null,
        $diagnosis = null,
        $treatmentPlan = null,
        $notes = null, // Đây là Doctor's notes
        $existingRecordId = null
        // $nursingNotes = null // Nếu muốn bác sĩ cũng có thể sửa nursing notes từ form của họ
    ) {
        if ($existingRecordId) {
            // Khi bác sĩ update, chỉ update các trường của bác sĩ
            // Nếu muốn cho phép bác sĩ sửa NursingNotes từ form của họ, thì thêm NursingNotes = :nursing_notes vào đây
            $this->db->query("UPDATE MedicalRecords
                              SET Symptoms = :symptoms, Diagnosis = :diagnosis, 
                                  TreatmentPlan = :treatment_plan, Notes = :doctor_notes, 
                                  UpdatedAt = NOW()
                              WHERE RecordID = :record_id AND AppointmentID = :appointment_id");
            $this->db->bind(':record_id', $existingRecordId);
            // $this->db->bind(':nursing_notes', $nursingNotes); // Nếu cho phép bác sĩ sửa
        } else {
            $this->db->query("INSERT INTO MedicalRecords
                                  (AppointmentID, PatientID, DoctorID, VisitDate, 
                                   Symptoms, Diagnosis, TreatmentPlan, Notes, 
                                   CreatedAt, UpdatedAt)
                              VALUES
                                  (:appointment_id, :patient_id, :doctor_id, :visit_date, 
                                   :symptoms, :diagnosis, :treatment_plan, :doctor_notes, 
                                   NOW(), NOW())");
            // $this->db->bind(':nursing_notes', $nursingNotes); // Nếu cho phép bác sĩ thêm khi tạo mới
        }

        $this->db->bind(':appointment_id', $appointmentId);
        $this->db->bind(':symptoms', $symptoms);
        $this->db->bind(':diagnosis', $diagnosis);
        $this->db->bind(':treatment_plan', $treatmentPlan);
        $this->db->bind(':doctor_notes', $notes); // Đổi tên placeholder để rõ ràng

        if (!$existingRecordId) {
            $this->db->bind(':patient_id', $patientId);
            $this->db->bind(':doctor_id', $doctorId);
            $this->db->bind(':visit_date', $visitDate);
        }

        try {
            if ($this->db->execute()) {
                return $existingRecordId ? $existingRecordId : $this->db->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log("MedicalRecordModel Error (saveMedicalRecord): " . $e->getMessage() . " for AppointmentID {$appointmentId}");
        }
        return false;
    }

    public function getMedicalHistoryByPatientId($patientId, $excludeAppointmentId = null, $filterDoctorId = null, $filterDateRange = null) {
        $sql = "SELECT
                    mr.RecordID, mr.AppointmentID, mr.VisitDate, mr.Diagnosis, 
                    u_doc.FullName AS DoctorName, s.Name AS SpecializationName 
                FROM MedicalRecords mr
                JOIN Doctors d ON mr.DoctorID = d.DoctorID
                JOIN Users u_doc ON d.UserID = u_doc.UserID
                LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
                WHERE mr.PatientID = :patient_id";
        $params = [':patient_id' => $patientId];
        if ($excludeAppointmentId !== null && filter_var($excludeAppointmentId, FILTER_VALIDATE_INT)) {
            $sql .= " AND mr.AppointmentID != :exclude_appointment_id";
            $params[':exclude_appointment_id'] = $excludeAppointmentId;
        }
        if ($filterDoctorId !== null && filter_var($filterDoctorId, FILTER_VALIDATE_INT) && $filterDoctorId > 0) {
            $sql .= " AND mr.DoctorID = :doctor_id";
            $params[':doctor_id'] = $filterDoctorId;
        }
        if (!empty($filterDateRange) && $filterDateRange !== 'all') {
            $startDate = null; $endDate = date('Y-m-d');
            switch ($filterDateRange) {
                case 'last_month': $startDate = date('Y-m-01', strtotime('-1 month')); $endDate = date('Y-m-t', strtotime('-1 month')); break;
                case 'last_3_months': $startDate = date('Y-m-d', strtotime('-3 months')); break;
                case 'last_6_months': $startDate = date('Y-m-d', strtotime('-6 months')); break;
                case 'last_year': $startDate = date('Y-m-d', strtotime('-1 year')); break;
            }
            if ($startDate) {
                $sql .= " AND mr.VisitDate BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate . ' 23:59:59';
            }
        }
        $sql .= " ORDER BY mr.VisitDate DESC, mr.RecordID DESC"; // Thêm RecordID để sắp xếp ổn định hơn
        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->resultSet();
    }

    public function getDoctorsConsultedByPatient($patientId) {
        $this->db->query("
            SELECT DISTINCT d.DoctorID, u.FullName AS DoctorName
            FROM MedicalRecords mr
            JOIN Doctors d ON mr.DoctorID = d.DoctorID
            JOIN Users u ON d.UserID = u.UserID
            WHERE mr.PatientID = :patient_id 
            ORDER BY u.FullName ASC
        ");
        $this->db->bind(':patient_id', $patientId);
        return $this->db->resultSet();
    }

    public function getAppointmentSummaryForPatient($appointmentId, $patientId) {
        $this->db->query("
            SELECT 
                a.AppointmentID, a.AppointmentDateTime, a.ReasonForVisit, a.Status,
                u_doc.FullName AS DoctorName, s.Name AS SpecializationName, a.PatientID 
            FROM Appointments a
            JOIN Doctors d ON a.DoctorID = d.DoctorID
            JOIN Users u_doc ON d.UserID = u_doc.UserID
            LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
            WHERE a.AppointmentID = :appointment_id AND a.PatientID = :patient_id
        ");
        $this->db->bind(':appointment_id', $appointmentId);
        $this->db->bind(':patient_id', $patientId);
        $appointment = $this->db->single();
        if (!$appointment) return false;

        $medicalRecord = $this->getRecordByAppointmentId($appointmentId); // Đảm bảo hàm này lấy cả NursingNotes
        $prescriptions = [];
        if ($medicalRecord && isset($medicalRecord['RecordID'])) {
            $prescriptionModel = new PrescriptionModel();
            $prescriptions = $prescriptionModel->getPrescriptionsByRecordId($medicalRecord['RecordID']);
        }
        return ['appointment' => $appointment, 'medicalRecord' => $medicalRecord, 'prescriptions' => $prescriptions];
    }

    // --- CÁC HÀM MỚI CHO NURSE ---

    /**
     * Nurse thêm hoặc cập nhật Ghi chú Điều dưỡng cho một bệnh án.
     * Nếu chưa có bệnh án cho cuộc hẹn này, một bệnh án mới sẽ được tạo với các thông tin cơ bản.
     *
     * @param int $appointmentId ID của cuộc hẹn.
     * @param string $nursingNotes Nội dung ghi chú điều dưỡng.
     * @param int $patientId ID của bệnh nhân (từ bảng patients.PatientID).
     * @param int $doctorId ID của bác sĩ (từ bảng doctors.DoctorID).
     * @param int $nurseUserId UserID của Nurse đang thực hiện.
     * @return bool True nếu thành công, false nếu thất bại.
     */
    public function saveNursingNotesForAppointment($appointmentId, $nursingNotes, $patientId, $doctorId, $nurseUserId) {
        if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0 ||
            !filter_var($patientId, FILTER_VALIDATE_INT) || $patientId <= 0 ||
            !filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0 ||
            !filter_var($nurseUserId, FILTER_VALIDATE_INT) || $nurseUserId <= 0) {
            error_log("MedicalRecordModel: Invalid IDs provided to saveNursingNotesForAppointment.");
            return false;
        }

        $existingRecord = $this->getRecordByAppointmentId($appointmentId);

        try {
            if ($existingRecord && isset($existingRecord['RecordID'])) {
                // Đã có record, cập nhật NursingNotes
                // Cậu có thể thêm một cột LastUpdatedByNurseID nếu muốn theo dõi ai sửa ghi chú điều dưỡng
                $this->db->query("UPDATE medicalrecords 
                                   SET NursingNotes = :nursing_notes, UpdatedAt = NOW() 
                                   WHERE RecordID = :record_id");
                $this->db->bind(':record_id', $existingRecord['RecordID']);
            } else {
                // Chưa có record, tạo mới với NursingNotes
                // Lấy VisitDate từ AppointmentDateTime
                $this->db->query("SELECT AppointmentDateTime FROM appointments WHERE AppointmentID = :appt_id_for_date_val");
                $this->db->bind(':appt_id_for_date_val', $appointmentId); // Dùng placeholder khác
                $appointmentData = $this->db->single();
                
                if (!$appointmentData) {
                    error_log("MedicalRecordModel: Could not find appointment to determine VisitDate for new medical record. Appt ID: " . $appointmentId);
                    return false; // Không tìm thấy lịch hẹn để lấy ngày
                }
                $visitDate = date('Y-m-d', strtotime($appointmentData['AppointmentDateTime']));

                // Các trường khác như Symptoms, Diagnosis, TreatmentPlan, Notes (của bác sĩ) sẽ là NULL ban đầu
                $this->db->query("INSERT INTO medicalrecords 
                                   (AppointmentID, PatientID, DoctorID, VisitDate, NursingNotes, CreatedAt, UpdatedAt) 
                                   VALUES (:appointment_id, :patient_id, :doctor_id, :visit_date, :nursing_notes, NOW(), NOW())");
                $this->db->bind(':patient_id', $patientId);
                $this->db->bind(':doctor_id', $doctorId);
                $this->db->bind(':visit_date', $visitDate);
            }
            
            $this->db->bind(':nursing_notes', $nursingNotes); // Có thể là NULL nếu muốn xóa ghi chú
            if ($existingRecord && isset($existingRecord['RecordID'])) {
                // Không cần bind appointment_id nữa nếu đang UPDATE bằng RecordID
            } else {
                 $this->db->bind(':appointment_id', $appointmentId); // Chỉ bind khi INSERT
            }
            
            return $this->db->execute();

        } catch (PDOException $e) {
            error_log("Error in saveNursingNotesForAppointment (ApptID: {$appointmentId}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * (VÍ DỤ) Cập nhật các kết quả xét nghiệm đơn giản do Nurse nhập.
     * Hàm này giả định các cột LabResult_BloodSugar, LabResult_UrineTestSummary đã tồn tại trong bảng medicalrecords.
     *
     * @param int $medicalRecordId ID của bệnh án (medicalrecords.RecordID).
     * @param array $labData Mảng chứa dữ liệu lab, ví dụ: ['LabResult_BloodSugar' => '120 mg/dL', 'LabResult_UrineTestSummary' => 'Normal']
     * @param int $nurseUserId UserID của Nurse đang thực hiện.
     * @return bool True nếu thành công, false nếu thất bại.
     */
    public function updateSimpleLabResultsByNurse($medicalRecordId, array $labData, $nurseUserId) {
        if (!filter_var($medicalRecordId, FILTER_VALIDATE_INT) || $medicalRecordId <= 0 || empty($labData)) {
            return false;
        }

        // Xây dựng phần SET của câu SQL một cách động và an toàn
        $setClauses = [];
        $sqlParams = [':record_id' => $medicalRecordId];
        // $sqlParams[':updated_by_nurse_id'] = $nurseUserId; // Nếu có cột để lưu ai update

        $allowedLabFields = ['LabResult_BloodSugar', 'LabResult_UrineTestSummary']; // Danh sách các trường lab Nurse được sửa

        foreach ($labData as $field => $value) {
            if (in_array($field, $allowedLabFields)) {
                $setClauses[] = "`{$field}` = :{$field}"; // Dùng backtick cho tên cột
                $sqlParams[":{$field}"] = $value;
            }
        }

        if (empty($setClauses)) {
            return false; // Không có trường hợp lệ nào để cập nhật
        }

        $sql = "UPDATE medicalrecords SET " . implode(', ', $setClauses) . ", UpdatedAt = NOW() 
                WHERE RecordID = :record_id";

        
        try {
            $this->db->query($sql);
            foreach ($sqlParams as $param => $value) {
                $this->db->bind($param, $value);
            }
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error in updateSimpleLabResultsByNurse (RecordID: {$medicalRecordId}): " . $e->getMessage());
            return false;
        }
    }
}
?>