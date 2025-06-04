<?php
// app/models/UserModel.php

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Tạo một user mới
     * @param array $data Dữ liệu user bao gồm: Username, PasswordHash, Email, FullName, Role, PhoneNumber, Address, Status
     * @return int|false Trả về UserID nếu thành công, false nếu thất bại
     */
    public function createUser($data) {
        $this->db->query('INSERT INTO Users (Username, PasswordHash, Email, FullName, Role, PhoneNumber, Address, Status)
                          VALUES (:Username, :PasswordHash, :Email, :FullName, :Role, :PhoneNumber, :Address, :Status)');

        // Bind values
        $this->db->bind(':Username', $data['Username']);
        $this->db->bind(':PasswordHash', $data['PasswordHash']);
        $this->db->bind(':Email', $data['Email']);
        $this->db->bind(':FullName', $data['FullName']);
        $this->db->bind(':Role', $data['Role']); // Sẽ là 'Patient'
        $this->db->bind(':PhoneNumber', $data['PhoneNumber'] ?? null);
        $this->db->bind(':Address', $data['Address'] ?? null);
        $this->db->bind(':Status', $data['Status'] ?? 'Pending'); // Mặc định là Pending cho Patient mới

        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }



   /**
 * Tìm user bằng Username, có thể loại trừ một UserID cụ thể
 * @param string $username
 * @param int|null $excludeUserId ID của user cần loại trừ (dùng khi update)
 * @return array|false
 */
public function findUserByUsername($username, $excludeUserId = null) {
     $sql = 'SELECT UserID, Username, PasswordHash, Email, FullName, Role, Status, Avatar FROM Users WHERE Username = :username';
    $params = [':username' => $username];
    if ($excludeUserId !== null) {
        $sql .= ' AND UserID != :exclude_user_id';
        $params[':exclude_user_id'] = $excludeUserId;
    }
    $this->db->query($sql);
    foreach ($params as $key => $value) {
        $this->db->bind($key, $value);
    }
    return $this->db->single();
}

/**
 * Tìm user bằng Email, có thể loại trừ một UserID cụ thể
 * @param string $email
 * @param int|null $excludeUserId ID của user cần loại trừ (dùng khi update)
 * @return array|false
 */
public function findUserByEmail($email, $excludeUserId = null) {
     $sql = 'SELECT UserID, Username, PasswordHash, Email, FullName, Role, Status, Avatar FROM Users WHERE Email = :email';
    $params = [':email' => $email];
    if ($excludeUserId !== null) {
        $sql .= ' AND UserID != :exclude_user_id';
        $params[':exclude_user_id'] = $excludeUserId;
    }
    $this->db->query($sql);
    foreach ($params as $key => $value) {
        $this->db->bind($key, $value);
    }
    return $this->db->single();
}

    // Bạn có thể thêm các phương thức khác như updateUser, findUserById, etc. sau này
    /**
 * Lấy thông tin user bằng UserID
 * @param int $userId
 * @return array|false
 */
public function findUserById($userId) {
    $this->db->query('SELECT * FROM Users WHERE UserID = :user_id');
    $this->db->bind(':user_id', $userId);
    return $this->db->single(); // Trả về mảng
}


/**
 * Cập nhật thông tin cơ bản của user
 * @param int $userId
 * @param array $data Mảng chứa các trường cần cập nhật (ví dụ: FullName, Email, PhoneNumber, Address)
 * @return bool
 */
// Trong UserModel.php - ví dụ sửa hàm updateUser
public function updateUser($userId, $data) { // Sửa lại để có thể nhận cả Avatar
    $sql = 'UPDATE Users SET
                FullName = :fullname,
                Email = :email,
                PhoneNumber = :phone_number,
                Address = :address';
    if (isset($data['Avatar'])) { // Nếu có Avatar trong data thì mới thêm vào câu SQL
        $sql .= ', Avatar = :avatar';
    }
    $sql .= ', UpdatedAt = CURRENT_TIMESTAMP WHERE UserID = :user_id';

    $this->db->query($sql);
    $this->db->bind(':fullname', $data['FullName']);
    $this->db->bind(':email', $data['Email']);
    $this->db->bind(':phone_number', $data['PhoneNumber']);
    $this->db->bind(':address', $data['Address']);
    if (isset($data['Avatar'])) {
        $this->db->bind(':avatar', $data['Avatar']);
    }
    $this->db->bind(':user_id', $userId);
    return $this->db->execute();
}

/**
 * Cập nhật mật khẩu của user
 * @param int $userId
 * @param string $newPasswordHash Mật khẩu đã được hash
 * @return bool
 */
public function updatePassword($userId, $newPasswordHash) {
    $this->db->query('UPDATE Users SET PasswordHash = :password_hash, UpdatedAt = CURRENT_TIMESTAMP
                      WHERE UserID = :user_id');
    $this->db->bind(':password_hash', $newPasswordHash);
    $this->db->bind(':user_id', $userId);
    return $this->db->execute();
}
/**
 * Cập nhật ảnh đại diện cho user
 * @param int $userId
 * @param string|null $avatarPath Đường dẫn tới file ảnh, hoặc NULL để xóa avatar
 * @return bool
 */
public function updateAvatar($userId, $avatarPath) {
    $this->db->query('UPDATE Users SET Avatar = :avatar, UpdatedAt = CURRENT_TIMESTAMP WHERE UserID = :user_id');
    $this->db->bind(':avatar', $avatarPath); // Sẽ là NULL nếu muốn xóa avatar
    $this->db->bind(':user_id', $userId);
    return $this->db->execute();
}
/**
 * Lấy tất cả người dùng với tùy chọn lọc và tìm kiếm
 * @param string|null $roleFilter Lọc theo vai trò ('Admin', 'Doctor', 'Nurse', 'Patient', hoặc null/All cho tất cả)
 * @param string|null $statusFilter Lọc theo trạng thái ('Active', 'Inactive', 'Pending', hoặc null/All cho tất cả)
 * @param string|null $searchTerm Từ khóa tìm kiếm (theo FullName hoặc Email)
 * @return array Danh sách người dùng
 */
public function getAllUsers($roleFilter = null, $statusFilter = null, $searchTerm = null) {
    $sql = "SELECT UserID, Username, Email, FullName, PhoneNumber, Role, Status, CreatedAt
            FROM Users
            WHERE 1=1";
    $params = [];

    if (!empty($roleFilter) && $roleFilter !== 'All') {
        $sql .= " AND Role = :role";
        $params[':role'] = $roleFilter;
    }

    if (!empty($statusFilter)) {
        if (is_array($statusFilter) && count($statusFilter) > 0) {
            // Xây dựng chuỗi placeholder động cho IN clause
            $statusPlaceholders = [];
            foreach ($statusFilter as $key => $statusVal) {
                $paramName = ':status_' . $key;
                $statusPlaceholders[] = $paramName;
                $params[$paramName] = $statusVal;
            }
            $sql .= " AND Status IN (" . implode(',', $statusPlaceholders) . ")";
        } elseif (is_string($statusFilter) && $statusFilter !== 'All') {
            $sql .= " AND Status = :status";
            $params[':status'] = $statusFilter;
        }
    }

    if (!empty($searchTerm)) {
        $sql .= " AND (FullName LIKE :search_term OR Email LIKE :search_term OR Username LIKE :search_term)";
        $params[':search_term'] = '%' . $searchTerm . '%';
    }

      // --- THAY ĐỔI ORDER BY Ở ĐÂY ---
    if (empty($roleFilter) || $roleFilter === 'All') {
        // Nếu đang xem tất cả các role, sắp xếp theo thứ tự Role mong muốn, sau đó theo ngày tạo
        $sql .= " ORDER BY FIELD(Role, 'Admin', 'Doctor', 'Nurse', 'Patient'), CreatedAt DESC";
    } else {
        // Nếu đang lọc theo một role cụ thể, chỉ cần sắp xếp theo ngày tạo (hoặc tiêu chí khác)
        $sql .= " ORDER BY CreatedAt DESC";
    }
    // Bạn có thể thay CreatedAt DESC bằng FullName ASC nếu muốn

    $this->db->query($sql);
    foreach ($params as $key => $value) {
        $this->db->bind($key, $value);
    }
    return $this->db->resultSet();
}

/**
 * Cập nhật trạng thái của một người dùng
 * @param int $userId
 * @param string $newStatus ('Active', 'Inactive', 'Pending')
 * @return bool
 */
public function updateUserStatus($userId, $newStatus) {
    // Validate newStatus nếu cần (ví dụ: chỉ cho phép các giá trị cụ thể)
    $allowedStatuses = ['Active', 'Inactive', 'Pending'];
    if (!in_array($newStatus, $allowedStatuses)) {
        error_log("Attempt to set invalid status '{$newStatus}' for UserID {$userId}");
        return false;
    }

    $this->db->query('UPDATE Users SET Status = :status, UpdatedAt = CURRENT_TIMESTAMP WHERE UserID = :user_id');
    $this->db->bind(':status', $newStatus);
    $this->db->bind(':user_id', $userId);
    return $this->db->execute();
}
}
?>