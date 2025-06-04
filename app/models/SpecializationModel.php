<?php
// app/models/SpecializationModel.php

class SpecializationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        $this->db->query("SELECT * FROM Specializations ORDER BY Name ASC");
        return $this->db->resultSet();
    }

    public function findById($id) {
        $this->db->query("SELECT * FROM Specializations WHERE SpecializationID = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create($name, $description = null) {
        $this->db->query("INSERT INTO Specializations (Name, Description) VALUES (:name, :description)");
        $this->db->bind(':name', $name);
        $this->db->bind(':description', $description);
        return $this->db->execute();
    }

    public function update($id, $name, $description = null) {
        $this->db->query("UPDATE Specializations SET Name = :name, Description = :description, UpdatedAt = CURRENT_TIMESTAMP WHERE SpecializationID = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $name);
        $this->db->bind(':description', $description);
        return $this->db->execute();
    }

    public function delete($id) {
        // Cân nhắc: Kiểm tra xem chuyên khoa này có đang được bác sĩ nào sử dụng không trước khi xóa
        // Nếu có, có thể không cho xóa hoặc set SpecializationID của Doctor thành NULL
        // Hiện tại, chúng ta sẽ xóa trực tiếp (có thể gây lỗi nếu có ràng buộc khóa ngoại chặt)
        $this->db->query("DELETE FROM Specializations WHERE SpecializationID = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function findByName($name, $excludeId = null) {
        $sql = "SELECT SpecializationID FROM Specializations WHERE Name = :name";
        if ($excludeId !== null) {
            $sql .= " AND SpecializationID != :exclude_id";
        }
        $this->db->query($sql);
        $this->db->bind(':name', $name);
        if ($excludeId !== null) {
            $this->db->bind(':exclude_id', $excludeId);
        }
        return $this->db->single();
    }
    public function getAllSpecializations() {
        $this->db->query("SELECT SpecializationID, Name FROM Specializations ORDER BY Name ASC");
        return $this->db->resultSet();
    }
}
?>