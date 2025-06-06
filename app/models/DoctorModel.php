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
            d.DoctorID, d.SpecializationID, d.Bio AS DoctorBio, d.ExperienceYears, d.ConsultationFee,
            u.UserID, u.FullName, u.Email, u.PhoneNumber, u.Address, u.Avatar, u.PasswordHash, -- <<< THÊM u.Avatar và u.PasswordHash
            s.Name AS SpecializationName
        FROM Doctors d
        JOIN Users u ON d.UserID = u.UserID
        LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
        WHERE d.UserID = :user_id AND u.Role = 'Doctor'
    ");
    $this->db->bind(':user_id', $userId);
    return $this->db->single();
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
// Trong file app/models/DoctorModel.php

/**
 * Cập nhật thông tin profile cho Doctor, bao gồm cả thông tin trong bảng 'users' và 'doctors'.
 * Sử dụng Transaction để đảm bảo tính toàn vẹn dữ liệu.
 * @param array $data Mảng chứa tất cả dữ liệu cần cập nhật, bao gồm 'user_id'.
 * @return bool True nếu cập nhật thành công, False nếu thất bại.
 */
public function updateDoctorProfile(array $data) {
    // Bắt đầu một transaction
    // Giả sử $this->db là đối tượng PDO hoặc một lớp wrapper cho PDO
    $this->db->beginTransaction();

    try {
        // ===== BƯỚC 1: CẬP NHẬT BẢNG `users` =====
        
        // Chuẩn bị câu lệnh SQL cơ bản
        $userSql = "UPDATE users SET FullName = :full_name, PhoneNumber = :phone_number";
        
        // Chuẩn bị các tham số cơ bản
        $userParams = [
            ':full_name'    => $data['FullName'],
            ':phone_number' => $data['PhoneNumber'],
            ':user_id'      => $data['user_id']
        ];

        // Chỉ thêm phần cập nhật Avatar nếu có avatar mới được truyền vào
        if (isset($data['Avatar'])) {
            $userSql .= ", Avatar = :avatar";
            $userParams[':avatar'] = $data['Avatar'];
        }

        // Chỉ thêm phần cập nhật Mật khẩu nếu có mật khẩu mới được truyền vào
        if (isset($data['NewPassword'])) {
            $userSql .= ", PasswordHash = :password_hash";
            $userParams[':password_hash'] = $data['NewPassword'];
        }

        // Hoàn thiện câu lệnh SQL
        $userSql .= " WHERE UserID = :user_id";

        // Thực thi câu lệnh cập nhật bảng `users`
        $this->db->query($userSql);
        foreach ($userParams as $key => &$val) {
            $this->db->bind($key, $val);
        }
        $this->db->execute();


        // ===== BƯỚC 2: CẬP NHẬT BẢNG `doctors` =====
        
        $doctorSql = "UPDATE doctors SET 
                        SpecializationID = :specialization_id, 
                        ExperienceYears = :experience_years, 
                        Bio = :doctor_bio 
                      WHERE UserID = :user_id";
        
        $this->db->query($doctorSql);
        $this->db->bind(':specialization_id', $data['SpecializationID']);
        $this->db->bind(':experience_years', $data['ExperienceYears']);
        $this->db->bind(':doctor_bio', $data['DoctorBio']);
        $this->db->bind(':user_id', $data['user_id']);
        
        $this->db->execute();

        // Nếu tất cả các câu lệnh trên chạy thành công, xác nhận các thay đổi
        $this->db->commit();
        
        return true; // Trả về true để báo hiệu thành công

    } catch (PDOException $e) {
        // Nếu có bất kỳ lỗi nào xảy ra trong khối try, hủy bỏ tất cả các thay đổi
        $this->db->rollBack();
        
        // Ghi lại lỗi để debug (rất quan trọng)
        error_log("DoctorProfileUpdate_Error: " . $e->getMessage());
        
        return false; // Trả về false để báo hiệu thất bại
    }
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