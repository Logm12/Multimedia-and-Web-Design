<?php
// app/models/NurseModel.php

class NurseModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy thông tin chi tiết của một Nurse dựa trên UserID.
     * Thông tin này bao gồm NurseID (là khóa chính của bảng 'nurses').
     *
     * @param int $userId ID của User (từ bảng 'users')
     * @return array|false Mảng chứa thông tin Nurse nếu tìm thấy, ngược lại trả về false.
     */
    public function getNurseByUserId($userId) {
        if (!filter_var($userId, FILTER_VALIDATE_INT) || $userId <= 0) {
            error_log("NurseModel: Invalid UserID provided to getNurseByUserId: " . $userId);
            return false;
        }

        try {
            // Giả sử bảng 'nurses' có cột 'UserID' là khóa ngoại liên kết đến 'users.UserID'
            // và 'NurseID' là khóa chính của bảng 'nurses'.
            // Chúng ta cũng lấy thông tin từ bảng 'users' như FullName, Email, Avatar.
            $this->db->query("SELECT 
                                n.NurseID, 
                                n.UserID, 
                                u.FullName, 
                                u.Email, 
                                u.PhoneNumber,
                                u.Avatar,
                                u.Status AS UserStatus,
                                n.CreatedAt AS NurseProfileCreatedAt, 
                                n.UpdatedAt AS NurseProfileUpdatedAt
                            FROM nurses n
                            JOIN users u ON n.UserID = u.UserID
                            WHERE n.UserID = :user_id");
            $this->db->bind(':user_id', $userId);
            
            $nurseData = $this->db->single();
            
            if ($nurseData) {
                return $nurseData;
            } else {
                // Ghi log nếu không tìm thấy nurse profile cho user ID này
                // Điều này có thể xảy ra nếu một user có Role='Nurse' nhưng chưa có record trong bảng 'nurses'
                error_log("NurseModel: No nurse profile found for UserID: " . $userId);
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error in getNurseByUserId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy thông tin chi tiết của một Nurse dựa trên NurseID (khóa chính của bảng nurses).
     *
     * @param int $nurseId ID của Nurse (từ bảng 'nurses.NurseID')
     * @return array|false Mảng chứa thông tin Nurse nếu tìm thấy, ngược lại trả về false.
     */
    public function getNurseById($nurseId) {
        if (!filter_var($nurseId, FILTER_VALIDATE_INT) || $nurseId <= 0) {
            error_log("NurseModel: Invalid NurseID provided to getNurseById: " . $nurseId);
            return false;
        }

        try {
            $this->db->query("SELECT 
                                n.NurseID, 
                                n.UserID, 
                                u.FullName, 
                                u.Email, 
                                u.PhoneNumber,
                                u.Avatar,
                                u.Status AS UserStatus,
                                n.CreatedAt AS NurseProfileCreatedAt, 
                                n.UpdatedAt AS NurseProfileUpdatedAt
                            FROM nurses n
                            JOIN users u ON n.UserID = u.UserID
                            WHERE n.NurseID = :nurse_id");
            $this->db->bind(':nurse_id', $nurseId);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log("Error in getNurseById: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tạo một hồ sơ Nurse mới.
     * Được gọi khi Admin tạo User mới với vai trò Nurse, hoặc khi một User được chuyển vai trò thành Nurse.
     *
     * @param int $userId UserID của người dùng sẽ trở thành Nurse.
     * @param array $data Mảng dữ liệu bổ sung cho Nurse (nếu có, ví dụ: department, qualifications, etc.)
     * @return int|false ID của Nurse mới được tạo (NurseID) hoặc false nếu thất bại.
     */
    public function createNurseProfile($userId, $data = []) {
        if (!filter_var($userId, FILTER_VALIDATE_INT) || $userId <= 0) {
            error_log("NurseModel: Invalid UserID for creating nurse profile: " . $userId);
            return false;
        }

        // Kiểm tra xem UserID này đã có nurse profile chưa để tránh trùng lặp
        $existingNurse = $this->getNurseByUserId($userId);
        if ($existingNurse) {
            error_log("NurseModel: Nurse profile already exists for UserID: " . $userId);
            return $existingNurse['NurseID']; // Trả về ID đã có
        }

        try {
            // Thêm các trường khác vào câu INSERT nếu bảng 'nurses' của cậu có thêm thông tin
            // Ví dụ: DepartmentID, Qualifications, etc.
            // $sql = "INSERT INTO nurses (UserID, DepartmentID, Qualifications, CreatedAt, UpdatedAt) 
            //         VALUES (:user_id, :department_id, :qualifications, NOW(), NOW())";
            
            $sql = "INSERT INTO nurses (UserID, CreatedAt, UpdatedAt) VALUES (:user_id, NOW(), NOW())";
            $this->db->query($sql);
            $this->db->bind(':user_id', $userId);
            // $this->db->bind(':department_id', $data['DepartmentID'] ?? null); 
            // $this->db->bind(':qualifications', $data['Qualifications'] ?? null);

            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error in createNurseProfile: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật thông tin hồ sơ Nurse.
     *
     * @param int $nurseId NurseID cần cập nhật.
     * @param array $data Mảng dữ liệu cần cập nhật (ví dụ: department, qualifications).
     * @return bool True nếu thành công, false nếu thất bại.
     */
    public function updateNurseProfile($nurseId, $data = []) {
        if (!filter_var($nurseId, FILTER_VALIDATE_INT) || $nurseId <= 0 || empty($data)) {
            error_log("NurseModel: Invalid data for updating nurse profile. NurseID: " . $nurseId);
            return false;
        }

        // Xây dựng câu SQL UPDATE động dựa trên các key trong $data
        // Ví dụ: UPDATE nurses SET DepartmentID = :department_id, Qualifications = :qualifications, UpdatedAt = NOW() WHERE NurseID = :nurse_id
        // Cần cẩn thận để chỉ cho phép cập nhật các trường hợp lệ.
        // Hiện tại, bảng 'nurses' của cậu có vẻ chỉ có UserID, CreatedAt, UpdatedAt, nên hàm này có thể chưa cần thiết lắm
        // trừ khi cậu thêm các trường khác vào bảng 'nurses'.
        
        // Ví dụ nếu có trường 'Department'
        // if (isset($data['Department'])) {
        //     $this->db->query("UPDATE nurses SET Department = :department, UpdatedAt = NOW() WHERE NurseID = :nurse_id");
        //     $this->db->bind(':department', $data['Department']);
        //     $this->db->bind(':nurse_id', $nurseId);
        //     return $this->db->execute();
        // }
        
        // Nếu không có trường nào cụ thể để update trong bảng 'nurses' ngoài các timestamp
        // thì hàm này có thể chỉ return true hoặc không làm gì cả.
        // Hoặc nếu cậu muốn update UpdatedAt:
        // $this->db->query("UPDATE nurses SET UpdatedAt = NOW() WHERE NurseID = :nurse_id");
        // $this->db->bind(':nurse_id', $nurseId);
        // return $this->db->execute();

        return true; // Placeholder, sửa lại nếu có trường cần update
    }
    
    /**
     * Lấy tất cả các Nurse (có thể dùng cho Admin để quản lý).
     * @return array Danh sách các Nurse.
     */
    public function getAllNursesWithUserDetails() {
        try {
            $this->db->query("SELECT 
                                n.NurseID, 
                                n.UserID, 
                                u.FullName, 
                                u.Email, 
                                u.PhoneNumber,
                                u.Avatar,
                                u.Status AS UserStatus,
                                n.CreatedAt AS NurseProfileCreatedAt
                            FROM nurses n
                            JOIN users u ON n.UserID = u.UserID
                            ORDER BY u.FullName ASC");
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error in getAllNursesWithUserDetails: " . $e->getMessage());
            return [];
        }
    }

    // Cậu có thể thêm các hàm khác nếu cần, ví dụ:
    // deleteNurseProfile($nurseId) (có thể là soft delete bằng cách cập nhật status trong bảng users)
}
?>