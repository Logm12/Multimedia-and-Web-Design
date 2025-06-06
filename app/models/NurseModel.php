<?php
// app/models/NurseModel.php

class NurseModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getNurseByUserId($userId) {
        if (!filter_var($userId, FILTER_VALIDATE_INT) || $userId <= 0) {
            error_log("NurseModel: Invalid UserID provided to getNurseByUserId: " . print_r($userId, true));
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
                            WHERE n.UserID = :user_id");
            $this->db->bind(':user_id', $userId);
            
            $nurseData = $this->db->single();
            
            if ($nurseData) {
                return $nurseData;
            } else {
                error_log("NurseModel::getNurseByUserId - No nurse profile found for UserID: " . $userId);
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error in NurseModel::getNurseByUserId for UserID {$userId}: " . $e->getMessage());
            return false;
        }
    }

    public function getNurseById($nurseId) {
        if (!filter_var($nurseId, FILTER_VALIDATE_INT) || $nurseId <= 0) {
            error_log("NurseModel: Invalid NurseID provided to getNurseById: " . print_r($nurseId, true));
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
            $nurseData = $this->db->single();
            if (!$nurseData) {
                error_log("NurseModel::getNurseById - No nurse profile found for NurseID: " . $nurseId);
            }
            return $nurseData;
        } catch (PDOException $e) {
            error_log("Error in NurseModel::getNurseById for NurseID {$nurseId}: " . $e->getMessage());
            return false;
        }
    }
    
    public function createNurseProfile($userId, $data = []) {
        if (!filter_var($userId, FILTER_VALIDATE_INT) || $userId <= 0) {
            error_log("NurseModel: Invalid UserID for creating nurse profile: " . print_r($userId, true));
            return false;
        }

        $existingNurse = $this->getNurseByUserId($userId);
        if ($existingNurse) {
            error_log("NurseModel: Nurse profile already exists for UserID: " . $userId . ". Returning existing NurseID: " . $existingNurse['NurseID']);
            return $existingNurse['NurseID']; 
        }

        try {
            $sql = "INSERT INTO nurses (UserID, CreatedAt, UpdatedAt) VALUES (:user_id, NOW(), NOW())";
            $this->db->query($sql);
            $this->db->bind(':user_id', $userId);

            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            error_log("NurseModel::createNurseProfile - Failed to execute insert for UserID: " . $userId);
            return false;
        } catch (PDOException $e) {
            error_log("Error in NurseModel::createNurseProfile for UserID {$userId}: " . $e->getMessage());
            return false;
        }
    }

    public function updateNurseProfile($nurseId, $data = []) {
        if (!filter_var($nurseId, FILTER_VALIDATE_INT) || $nurseId <= 0) { // Bỏ empty($data) vì có thể chỉ update UpdatedAt
            error_log("NurseModel: Invalid NurseID for updating nurse profile: " . print_r($nurseId, true));
            return false;
        }
        
        // Hiện tại, nếu chỉ có UpdatedAt cần cập nhật khi có thay đổi ở bảng users (ví dụ avatar)
        // thì logic đó nên nằm ở UserModel hoặc controller tương ứng.
        // Hàm này sẽ hữu ích hơn khi bảng nurses có các trường riêng cần Nurse tự cập nhật.
        // Ví dụ, nếu có trường 'Department' hoặc 'ContactInfoExtension' trong bảng 'nurses'
        $fieldsToUpdate = [];
        $params = [':nurse_id' => $nurseId];

        // if (isset($data['Department'])) {
        //     $fieldsToUpdate[] = 'Department = :department';
        //     $params[':department'] = $data['Department'];
        // }
        // if (isset($data['ContactInfoExtension'])) {
        //     $fieldsToUpdate[] = 'ContactInfoExtension = :contact_info_extension';
        //     $params[':contact_info_extension'] = $data['ContactInfoExtension'];
        // }

        if (empty($fieldsToUpdate)) {
            // Nếu không có trường cụ thể nào được truyền để cập nhật,
            // có thể chỉ cập nhật UpdatedAt hoặc không làm gì cả.
            // For now, let's assume if $data is empty, we just touch UpdatedAt
             $this->db->query("UPDATE nurses SET UpdatedAt = NOW() WHERE NurseID = :nurse_id");
             $this->db->bind(':nurse_id', $nurseId);
             return $this->db->execute();
        }
        
        // $sql = "UPDATE nurses SET " . implode(', ', $fieldsToUpdate) . ", UpdatedAt = NOW() WHERE NurseID = :nurse_id";
        // $this->db->query($sql);
        // foreach($params as $key => $value){
        //     $this->db->bind($key, $value);
        // }
        // return $this->db->execute();
        return true; 
    }
    
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
            error_log("Error in NurseModel::getAllNursesWithUserDetails: " . $e->getMessage());
            return [];
        }
    }
}
?>