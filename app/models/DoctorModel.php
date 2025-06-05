<?php
// app/models/DoctorModel.php

class DoctorModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy tất cả các bác sĩ đang hoạt động cùng với thông tin chuyên khoa
     * @return array Danh sách các bác sĩ
     */
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

    /**
     * Lấy thông tin chi tiết của một bác sĩ bằng DoctorID
     * @param int $doctorId
     * @return array|false // Thay đổi từ object sang array nếu default fetch mode là ASSOC
     */
    public function getDoctorById($doctorId) {
        $this->db->query("
            SELECT
                d.DoctorID,
                d.UserID, -- Thêm UserID để tham chiếu ngược nếu cần
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
            WHERE d.DoctorID = :doctorId AND u.Role = 'Doctor' -- Không cần u.Status = 'Active' ở đây nếu chỉ lấy theo DoctorID
                                                              -- vì DoctorID đã là duy nhất và thuộc về một doctor.
                                                              -- Trạng thái của User có thể kiểm tra riêng.
        ");
        $this->db->bind(':doctorId', $doctorId);
        return $this->db->single(); // Sẽ trả về mảng nếu default fetch mode là ASSOC
    }

    /**
     * Lấy thông tin bác sĩ (bao gồm DoctorID) bằng UserID (MỚI THÊM)
     * @param int $userId ID của người dùng từ bảng Users
     * @return array|false Thông tin bác sĩ nếu tìm thấy, false nếu không
     */
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
    $this->db->bind(':experience_years', $data['ExperienceYears'] ?? 0); // Mặc định 0 nếu không có
    $this->db->bind(':consultation_fee', $data['ConsultationFee'] ?? 0.00); // Mặc định 0.00 nếu không có

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
}
?>