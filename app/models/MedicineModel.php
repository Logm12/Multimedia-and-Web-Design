<?php
// app/models/MedicineModel.php

class MedicineModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }
     /**
     * Lấy tất cả các thuốc (có thể có pagination sau này) cho Admin quản lý
     * @param string|null $searchTerm
     * @return array
     */
    public function getAllAdmin($searchTerm = null) {
        $sql = "SELECT MedicineID, Name, Unit, Manufacturer, Description, StockQuantity, CreatedAt
                FROM Medicines";
        $params = [];
        if (!empty($searchTerm)) {
            $sql .= " WHERE Name LIKE :search_term OR Manufacturer LIKE :search_term OR Description LIKE :search_term";
            $params[':search_term'] = '%' . $searchTerm . '%';
        }
        $sql .= " ORDER BY Name ASC";

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->resultSet();
    }

    /**
     * Lấy tất cả các thuốc đang hoạt động (hoặc toàn bộ) để hiển thị trong danh sách chọn
     * @return array
     */
    public function getAllMedicinesForSelection() {
        // Bạn có thể thêm điều kiện WHERE Status = 'Active' nếu có cột trạng thái cho thuốc
        $this->db->query("SELECT MedicineID, Name, Unit FROM Medicines ORDER BY Name ASC");
        return $this->db->resultSet();
    }

    /**
     * Lấy thông tin một thuốc bằng ID
     * @param int $medicineId
     * @return array|false
     */
    public function getMedicineById($medicineId) {
        $this->db->query("SELECT MedicineID, Name, Unit FROM Medicines WHERE MedicineID = :id");
        $this->db->bind(':id', $medicineId);
        return $this->db->single();
    }
    /**
     * Lấy thông tin một thuốc bằng ID
     * @param int $medicineId
     * @return array|false
     */
    public function findById($medicineId) { // Đổi tên hàm cho nhất quán với các model khác
        $this->db->query("SELECT * FROM Medicines WHERE MedicineID = :id");
        $this->db->bind(':id', (int)$medicineId);
        return $this->db->single();
    }
     /**
     * Kiểm tra xem tên thuốc (và đơn vị) đã tồn tại chưa (loại trừ ID hiện tại khi update)
     * @param string $name
     * @param string $unit
     * @param int|null $excludeId
     * @return array|false
     */
    public function findByNameAndUnit($name, $unit, $excludeId = null) {
        $sql = "SELECT MedicineID FROM Medicines WHERE Name = :name AND Unit = :unit";
        $params = [':name' => $name, ':unit' => $unit];
        if ($excludeId !== null) {
            $sql .= " AND MedicineID != :exclude_id";
            $params[':exclude_id'] = (int)$excludeId;
        }
        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->single();
    }

    /**
     * Tạo một thuốc mới
     * @param array $data Mảng chứa: Name, Description, Unit, Manufacturer, StockQuantity
     * @return bool|int MedicineID nếu thành công
     */
    public function create($data) {
        $this->db->query("INSERT INTO Medicines (Name, Description, Unit, Manufacturer, StockQuantity)
                          VALUES (:name, :description, :unit, :manufacturer, :stock_quantity)");
        $this->db->bind(':name', $data['Name']);
        $this->db->bind(':description', $data['Description'] ?? null);
        $this->db->bind(':unit', $data['Unit']);
        $this->db->bind(':manufacturer', $data['Manufacturer'] ?? null);
        $this->db->bind(':stock_quantity', $data['StockQuantity'] ?? 0);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Cập nhật thông tin một thuốc
     * @param int $medicineId
     * @param array $data Mảng chứa: Name, Description, Unit, Manufacturer, StockQuantity
     * @return bool
     */
    public function update($medicineId, $data) {
        $this->db->query("UPDATE Medicines SET
                            Name = :name,
                            Description = :description,
                            Unit = :unit,
                            Manufacturer = :manufacturer,
                            StockQuantity = :stock_quantity,
                            UpdatedAt = CURRENT_TIMESTAMP
                          WHERE MedicineID = :id");
        $this->db->bind(':id', (int)$medicineId);
        $this->db->bind(':name', $data['Name']);
        $this->db->bind(':description', $data['Description'] ?? null);
        $this->db->bind(':unit', $data['Unit']);
        $this->db->bind(':manufacturer', $data['Manufacturer'] ?? null);
        $this->db->bind(':stock_quantity', $data['StockQuantity'] ?? 0);
        return $this->db->execute();
    }

    /**
     * Xóa một thuốc
     * @param int $medicineId
     * @return bool
     */
    public function delete($medicineId) {
        // CẢNH BÁO: Cần kiểm tra xem thuốc này có đang được sử dụng trong bảng Prescriptions không
        // Nếu có, bạn không nên cho xóa hoặc cần xử lý logic (ví dụ: đánh dấu là không còn dùng)
        // Hiện tại: Xóa trực tiếp, có thể gây lỗi nếu có khóa ngoại RESTRICT
        $this->db->query("DELETE FROM Medicines WHERE MedicineID = :id");
        $this->db->bind(':id', (int)$medicineId);
        return $this->db->execute();
    }

    /**
     * Đếm số lần thuốc được sử dụng trong đơn thuốc
     * @param int $medicineId
     * @return int
     */
    public function countUsageInPrescriptions($medicineId) {
        $this->db->query("SELECT COUNT(*) as count FROM Prescriptions WHERE MedicineID = :medicine_id");
        $this->db->bind(':medicine_id', (int)$medicineId);
        $row = $this->db->single();
        return $row ? (int)$row['count'] : 0;
    }
}
?>