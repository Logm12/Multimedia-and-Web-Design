<?php
// app/models/UserModel.php

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createUser($data) {
        $this->db->query('INSERT INTO Users (Username, PasswordHash, Email, FullName, Role, PhoneNumber, Address, Status, CreatedAt, UpdatedAt)
                          VALUES (:Username, :PasswordHash, :Email, :FullName, :Role, :PhoneNumber, :Address, :Status, NOW(), NOW())');
        $this->db->bind(':Username', $data['Username']);
        $this->db->bind(':PasswordHash', $data['PasswordHash']);
        $this->db->bind(':Email', $data['Email']);
        $this->db->bind(':FullName', $data['FullName']);
        $this->db->bind(':Role', $data['Role']); 
        $this->db->bind(':PhoneNumber', $data['PhoneNumber'] ?? null);
        $this->db->bind(':Address', $data['Address'] ?? null);
        $this->db->bind(':Status', $data['Status'] ?? 'Pending');
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            error_log("UserModel Error (createUser): Failed to create user.");
            return false;
        }
    }

    public function findUserByUsernameOrEmail($usernameOrEmail) {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail, null); 
        }
        return $this->findUserByUsername($usernameOrEmail, null);
    }

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

    public function findUserById($userId) {
        $this->db->query('SELECT UserID, Username, PasswordHash, Email, FullName, Role, Status, Avatar FROM Users WHERE UserID = :user_id');
        $this->db->bind(':user_id', $userId);
        return $this->db->single(); 
    }

    public function updateUser($userId, $data) {
        $setClauses = [];
        $params = [':user_id' => $userId];
        if (isset($data['FullName'])) { $setClauses[] = 'FullName = :fullname'; $params[':fullname'] = $data['FullName']; }
        if (isset($data['Email'])) { $setClauses[] = 'Email = :email'; $params[':email'] = $data['Email']; }
        if (isset($data['PhoneNumber'])) { $setClauses[] = 'PhoneNumber = :phone_number'; $params[':phone_number'] = $data['PhoneNumber']; }
        if (isset($data['Address'])) { $setClauses[] = 'Address = :address'; $params[':address'] = $data['Address']; }
        if (array_key_exists('Avatar', $data)) { $setClauses[] = 'Avatar = :avatar'; $params[':avatar'] = $data['Avatar']; }
        if (isset($data['Role'])) { $setClauses[] = 'Role = :role'; $params[':role'] = $data['Role']; } // Added for admin edit user
        if (isset($data['Status'])) { $setClauses[] = 'Status = :status'; $params[':status'] = $data['Status']; } // Added for admin edit user
        
        if (empty($setClauses)) return true;
        $sql = 'UPDATE Users SET ' . implode(', ', $setClauses) . ', UpdatedAt = NOW() WHERE UserID = :user_id';
        $this->db->query($sql);
        foreach ($params as $key => $value) { $this->db->bind($key, $value); }
        return $this->db->execute();
    }

    public function updatePassword($userId, $newPasswordHash) {
        $this->db->query('UPDATE Users SET PasswordHash = :password_hash, UpdatedAt = NOW() WHERE UserID = :user_id');
        $this->db->bind(':password_hash', $newPasswordHash);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function updatePasswordAndStatus($userId, $newPasswordHash, $newStatus) {
        $allowedStatuses = ['Active', 'Inactive', 'Pending', 'Suspended']; 
        if (!in_array($newStatus, $allowedStatuses)) {
            error_log("UserModel: Invalid status '{$newStatus}' for UserID {$userId}");
            return false;
        }
        $this->db->query('UPDATE Users SET PasswordHash = :password_hash, Status = :status, UpdatedAt = NOW() WHERE UserID = :user_id');
        $this->db->bind(':password_hash', $newPasswordHash);
        $this->db->bind(':status', $newStatus);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function updateAvatar($userId, $avatarPath) {
        $this->db->query('UPDATE Users SET Avatar = :avatar, UpdatedAt = NOW() WHERE UserID = :user_id');
        $this->db->bind(':avatar', $avatarPath); 
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function getAllUsers($roleFilter = null, $statusFilter = null, $searchTerm = null) {
        $sql = "SELECT UserID, Username, Email, FullName, PhoneNumber, Role, Status, CreatedAt, UpdatedAt FROM Users WHERE 1=1"; 
        $params = [];
        if (!empty($roleFilter) && $roleFilter !== 'All') { $sql .= " AND Role = :role"; $params[':role'] = $roleFilter; }
        if (!empty($statusFilter)) {
            if (is_array($statusFilter) && count($statusFilter) > 0) {
                $statusPlaceholders = [];
                foreach ($statusFilter as $key => $statusVal) { $paramName = ':status_' . $key; $statusPlaceholders[] = $paramName; $params[$paramName] = $statusVal; }
                $sql .= " AND Status IN (" . implode(',', $statusPlaceholders) . ")";
            } elseif (is_string($statusFilter) && $statusFilter !== 'All') { $sql .= " AND Status = :status"; $params[':status'] = $statusFilter; }
        }
        if (!empty($searchTerm)) { $sql .= " AND (FullName LIKE :search_term OR Email LIKE :search_term OR Username LIKE :search_term)"; $params[':search_term'] = '%' . $searchTerm . '%'; }
        if (empty($roleFilter) || $roleFilter === 'All') { $sql .= " ORDER BY FIELD(Role, 'Admin', 'Doctor', 'Nurse', 'Patient'), CreatedAt DESC"; } 
        else { $sql .= " ORDER BY CreatedAt DESC"; }
        $this->db->query($sql);
        foreach ($params as $key => $value) { $this->db->bind($key, $value); }
        return $this->db->resultSet(); 
    }

    public function updateUserStatus($userId, $newStatus) {
        $allowedStatuses = ['Active', 'Inactive', 'Pending', 'Suspended']; 
        if (!in_array($newStatus, $allowedStatuses)) {
            error_log("UserModel: Invalid status '{$newStatus}' for UserID {$userId}");
            return false;
        }
        $this->db->query('UPDATE Users SET Status = :status, UpdatedAt = NOW() WHERE UserID = :user_id');
        $this->db->bind(':status', $newStatus);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    public function createPasswordResetToken($userId, $email, $token, $expiresAt) {
        $this->deletePasswordResetTokensByUserId($userId);
        $this->db->query('INSERT INTO password_resets (UserID, Email, Token, ExpiresAt, CreatedAt) VALUES (:user_id, :email, :token, :expires_at, NOW())');
        $this->db->bind(':user_id', $userId); $this->db->bind(':email', $email);
        $this->db->bind(':token', $token); $this->db->bind(':expires_at', $expiresAt);
        if (!$this->db->execute()) { error_log("UserModel: Failed to create pwd reset token for UserID {$userId}."); return false; }
        return true;
    }

    public function findUserByPasswordResetToken($token) {
        $this->db->query('SELECT pr.UserID, pr.Email, pr.Token, pr.ExpiresAt, u.Status as UserStatus FROM password_resets pr JOIN Users u ON pr.UserID = u.UserID WHERE pr.Token = :token');
        $this->db->bind(':token', $token);
        $result = $this->db->single();
        if (!$result) error_log("UserModel: Pwd reset token '{$token}' not found.");
        return $result;
    }

    public function deletePasswordResetToken($token) {
        $this->db->query('DELETE FROM password_resets WHERE Token = :token');
        $this->db->bind(':token', $token);
        if (!$this->db->execute()) { error_log("UserModel: Failed to delete pwd reset token '{$token}'."); return false; }
        return true;
    }
    
    public function deletePasswordResetTokensByUserId($userId) {
        $this->db->query('DELETE FROM password_resets WHERE UserID = :user_id');
        $this->db->bind(':user_id', $userId);
        return $this->db->execute(); 
    }

    /**
     * Retrieves UserIDs for a specific role and status.
     * @param string $role
     * @param string $status
     * @return array List of arrays, each containing UserID.
     */
    public function getUserIdsByRoleAndStatus($role, $status = 'Active') {
        $this->db->query("SELECT UserID FROM Users WHERE Role = :role AND Status = :status");
        $this->db->bind(':role', $role);
        $this->db->bind(':status', $status);
        return $this->db->resultSet();
    }

    /**
     * Counts the number of users for a specific role and/or status.
     * @param string|null $role The role to count. If null, counts all users matching status.
     * @param string $status The status to filter by. Defaults to 'Active'. Use 'All' to ignore status.
     * @return int The count of users.
     */
    public function getUserCountByRole($role = null, $status = 'Active') {
        $sql = "SELECT COUNT(UserID) as user_count FROM Users WHERE 1=1";
        $params = [];
        if ($role !== null) { $sql .= " AND Role = :role"; $params[':role'] = $role; }
        if ($status !== null && $status !== 'All') { $sql .= " AND Status = :status"; $params[':status'] = $status; }
        $this->db->query($sql);
        foreach ($params as $key => $value) { $this->db->bind($key, $value); }
        $row = $this->db->single();
        return $row ? (int)$row['user_count'] : 0;
    }
/**
 * Finds a user's details based on their DoctorID.
 * @param int $doctorId The ID from the doctors table.
 * @return array|false The user's data or false if not found.
 */
public function findUserByDoctorId($doctorId) {
    $this->db->query("SELECT u.* 
                    FROM users u 
                    JOIN doctors d ON u.UserID = d.UserID 
                    WHERE d.DoctorID = :doctor_id");
    $this->db->bind(':doctor_id', $doctorId);
    return $this->db->single();
}
}
?>