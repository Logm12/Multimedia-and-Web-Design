<?php
// app/models/DoctorModel.php

class DoctorModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllActiveDoctorsWithSpecialization() {
        $this->db->query("
            SELECT
                d.DoctorID,
                u.FullName AS DoctorName,
                u.Email AS DoctorEmail,
                s.Name AS SpecializationName,
                d.Bio AS DoctorBio,
                d.ExperienceYears,
                d.ConsultationFee
            FROM Doctors d
            JOIN Users u ON d.UserID = u.UserID
            LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
            WHERE u.Role = 'Doctor' AND u.Status = 'Active'
            ORDER BY u.FullName ASC
        ");
        return $this->db->resultSet();
    }

    public function getDoctorById($doctorId) {
        $this->db->query("
            SELECT
                d.DoctorID,
                d.UserID, 
                u.FullName AS DoctorName,
                u.Email AS DoctorEmail,
                u.PhoneNumber AS DoctorPhone,
                u.Address AS DoctorAddress,
                s.SpecializationID,
                s.Name AS SpecializationName,
                d.Bio AS DoctorBio,
                d.ExperienceYears,
                d.ConsultationFee
            FROM Doctors d
            JOIN Users u ON d.UserID = u.UserID
            LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
            WHERE d.DoctorID = :doctorId AND u.Role = 'Doctor'
        ");
        $this->db->bind(':doctorId', $doctorId);
        return $this->db->single(); 
    }

    public function getDoctorByUserId($userId) {
        $this->db->query("
            SELECT
                d.DoctorID,
                d.SpecializationID,
                d.Bio,
                d.ExperienceYears,
                d.ConsultationFee,
                u.UserID, -- UserID từ bảng Users (chính là $userId truyền vào)
                u.FullName,
                u.Email,
                u.PhoneNumber,
                u.Address,
                s.Name AS SpecializationName -- Lấy tên chuyên khoa
            FROM Doctors d
            JOIN Users u ON d.UserID = u.UserID
            LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
            WHERE d.UserID = :user_id AND u.Role = 'Doctor' -- Đảm bảo user này là Doctor
        ");
        $this->db->bind(':user_id', $userId);
        return $this->db->single(); // Trả về một dòng (dạng mảng nếu PDO::FETCH_ASSOC)
    }

    // Bạn có thể thêm các phương thức khác sau này, ví dụ:
    // createDoctorProfile($userId, $data)
    // updateDoctorProfile($doctorId, $data)
    // getDoctorsBySpecializationId($specializationId)
    /**
 * Tạo hồ sơ chi tiết cho một Doctor mới sau khi User đã được tạo
 * @param int $userId ID của User vừa được tạo
 * @param array $data Mảng chứa thông tin của Doctor: SpecializationID, Bio, ExperienceYears, ConsultationFee
 * @return bool True nếu tạo thành công, false nếu thất bại
 */
public function createDoctorProfile($userId, $data) {
    $this->db->query("INSERT INTO Doctors (UserID, SpecializationID, Bio, ExperienceYears, ConsultationFee)
                      VALUES (:user_id, :specialization_id, :bio, :experience_years, :consultation_fee)");

        $this->db->bind(':user_id', $userId);
        $this->db->bind(':specialization_id', $data['SpecializationID'] ?? null);
        $this->db->bind(':bio', $data['Bio'] ?? null);
        $this->db->bind(':experience_years', $data['ExperienceYears'] ?? 0); 
        $this->db->bind(':consultation_fee', $data['ConsultationFee'] ?? 0.00); 

    return $this->db->execute();
}
// Trong DoctorModel.php
/**
 * Cập nhật hồ sơ chi tiết cho một Doctor dựa trên UserID
 * @param int $userId ID của User
 * @param array $data Mảng chứa thông tin cập nhật: SpecializationID, Bio, ExperienceYears, ConsultationFee
 * @return bool True nếu cập nhật thành công hoặc không có gì để cập nhật, false nếu lỗi
 */
public function updateDoctorProfile($userId, $data) {
    // Kiểm tra xem doctor profile có tồn tại không, nếu không thì có thể tạo mới hoặc báo lỗi
    // Hiện tại, chúng ta giả định là update
    $this->db->query("UPDATE Doctors SET
                        SpecializationID = :specialization_id,
                        Bio = :bio,
                        ExperienceYears = :experience_years,
                        ConsultationFee = :consultation_fee
                      WHERE UserID = :user_id");

    $this->db->bind(':specialization_id', $data['SpecializationID'] ?? null);
    $this->db->bind(':bio', $data['Bio'] ?? null);
    $this->db->bind(':experience_years', $data['ExperienceYears'] ?? 0);
    $this->db->bind(':consultation_fee', $data['ConsultationFee'] ?? 0.00);
    $this->db->bind(':user_id', $userId);

    return $this->db->execute();
    // Để chắc chắn hơn, bạn có thể kiểm tra rowCount() xem có thực sự update không
    // Hoặc nếu không tìm thấy Doctor với UserID đó để update, có thể coi là lỗi
}
    /**
     * Counts the number of unique patients associated with a doctor through appointments.
     * This can serve as a proxy for "Followed Patients".
     * @param int $doctorId The ID of the doctor.
     * @return int The count of unique patients.
     */
    public function getFollowedPatientsCount($doctorId) {
        $this->db->query("
            SELECT COUNT(DISTINCT PatientID) as patient_count
            FROM Appointments
            WHERE DoctorID = :doctor_id
        ");
        $this->db->bind(':doctor_id', $doctorId);
        $row = $this->db->single();
        return $row ? (int)$row['patient_count'] : 0;
    }

    // <<<< "SIÊU NĂNG LỰC" MỚI ĐỂ PHỤC VỤ ADMIN CONTROLLER ĐÂY CẬU >>>>
    public function getAllDoctorsWithDetails() {
        try {
            $this->db->query("SELECT 
                                d.DoctorID, 
                                u.UserID, 
                                u.FullName, 
                                u.Email, 
                                u.PhoneNumber,
                                u.Avatar,
                                u.Status AS UserStatus,
                                s.Name AS SpecializationName,
                                d.Bio,
                                d.ExperienceYears,
                                d.ConsultationFee,
                                d.CreatedAt AS DoctorProfileCreatedAt
                            FROM doctors d
                            JOIN users u ON d.UserID = u.UserID
                            LEFT JOIN specializations s ON d.SpecializationID = s.SpecializationID
                            WHERE u.Role = 'Doctor' 
                            ORDER BY u.FullName ASC");
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error in DoctorModel::getAllDoctorsWithDetails: " . $e->getMessage());
            return []; 
        }
    }

    // Hàm này có thể hữu ích nếu Cậu muốn lấy danh sách bác sĩ đơn giản cho dropdown chẳng hạn
    public function getAllDoctorsSimple() {
        $this->db->query("SELECT d.DoctorID, u.FullName 
                        FROM doctors d
                        JOIN users u ON d.UserID = u.UserID
                        WHERE u.Status = 'Active' AND u.Role = 'Doctor'
                        ORDER BY u.FullName ASC");
        return $this->db->resultSet();
    }
}
?>