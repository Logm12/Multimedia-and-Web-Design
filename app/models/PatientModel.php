<?php
// app/models/PatientModel.php

class PatientModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Tạo một patient mới liên kết với UserID
     * @param array $data Dữ liệu patient bao gồm: UserID, DateOfBirth, Gender, BloodType, InsuranceInfo, MedicalHistorySummary
     * @return bool True nếu thành công, false nếu thất bại
     */
    public function createPatient($data) {
        $this->db->query('INSERT INTO Patients (UserID, DateOfBirth, Gender, BloodType, InsuranceInfo, MedicalHistorySummary)
                          VALUES (:UserID, :DateOfBirth, :Gender, :BloodType, :InsuranceInfo, :MedicalHistorySummary)');

        // Bind values
        $this->db->bind(':UserID', $data['UserID']);
        $this->db->bind(':DateOfBirth', $data['DateOfBirth'] ?? null);
        $this->db->bind(':Gender', $data['Gender'] ?? null);
        $this->db->bind(':BloodType', $data['BloodType'] ?? null);
        $this->db->bind(':InsuranceInfo', $data['InsuranceInfo'] ?? null);
        $this->db->bind(':MedicalHistorySummary', $data['MedicalHistorySummary'] ?? null);

        // Execute
        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // Các phương thức khác cho PatientModel có thể được thêm sau
    /**
 * Lấy thông tin chi tiết của một bệnh nhân bằng PatientID (join với Users)
 * @param int $patientId
 * @return array|false
 */
public function getPatientDetailsById($patientId) {
    $this->db->query("
        SELECT
            p.PatientID,
            p.UserID,
            p.DateOfBirth,
            p.Gender,
            p.BloodType,
            p.InsuranceInfo,
            p.MedicalHistorySummary,
            u.FullName,
            u.Email,
            u.PhoneNumber,
            u.Address AS UserAddress -- Phân biệt với Address của Clinic nếu có
        FROM Patients p
        JOIN Users u ON p.UserID = u.UserID
        WHERE p.PatientID = :patient_id
    ");
    $this->db->bind(':patient_id', $patientId);
    return $this->db->single();
}

/**
 * (Nếu bạn chưa có) Lấy thông tin PatientID bằng UserID
 * @param int $userId
 * @return array|false
 */
public function getPatientByUserId($userId) {
    $this->db->query("SELECT PatientID, UserID FROM Patients WHERE UserID = :user_id");
    $this->db->bind(':user_id', $userId);
    return $this->db->single();
}
/**
 * Cập nhật thông tin chi tiết của patient
 * @param int $patientId ID của record trong bảng Patients
 * @param array $data Mảng chứa các trường cần cập nhật (DateOfBirth, Gender, etc.)
 * @return bool
 */
public function updatePatient($patientId, $data) {
    $this->db->query('UPDATE Patients SET
                        DateOfBirth = :date_of_birth,
                        Gender = :gender,
                        BloodType = :blood_type,
                        InsuranceInfo = :insurance_info,
                        MedicalHistorySummary = :medical_history_summary,
                        UpdatedAt = CURRENT_TIMESTAMP
                      WHERE PatientID = :patient_id'); // Quan trọng: WHERE bằng PatientID

    $this->db->bind(':date_of_birth', $data['DateOfBirth']);
    $this->db->bind(':gender', $data['Gender']);
    $this->db->bind(':blood_type', $data['BloodType']);
    $this->db->bind(':insurance_info', $data['InsuranceInfo']);
    $this->db->bind(':medical_history_summary', $data['MedicalHistorySummary']);
    $this->db->bind(':patient_id', $patientId);
    return $this->db->execute();
}
}
?>