<?php
// app/models/MedicineModel.php

class MedicineModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllAdmin($searchTerm = null) {
        $sql = "SELECT MedicineID, Name, Unit, Manufacturer, Description, StockQuantity, CreatedAt, UpdatedAt 
                FROM Medicines";
        $params = [];
        if (!empty($searchTerm)) {
            $sql .= " WHERE Name LIKE :search_term OR Manufacturer LIKE :search_term OR Description LIKE :search_term";
            $params[':search_term'] = '%' . $searchTerm . '%';
        }
        $sql .= " ORDER BY Name ASC";
        $this->db->query($sql);
        foreach ($params as $key => $value) { $this->db->bind($key, $value); }
        return $this->db->resultSet();
    }

    public function getAllMedicinesForSelection() {
        $this->db->query("SELECT MedicineID, Name, Unit FROM Medicines ORDER BY Name ASC");
        return $this->db->resultSet();
    }

    // getMedicineById was duplicated by findById, keeping findById for consistency
    public function findById($medicineId) {
        $this->db->query("SELECT * FROM Medicines WHERE MedicineID = :id");
        $this->db->bind(':id', (int)$medicineId);
        return $this->db->single();
    }

    public function findByNameAndUnit($name, $unit, $excludeId = null) {
        $sql = "SELECT MedicineID FROM Medicines WHERE Name = :name AND Unit = :unit";
        $params = [':name' => $name, ':unit' => $unit];
        if ($excludeId !== null) {
            $sql .= " AND MedicineID != :exclude_id";
            $params[':exclude_id'] = (int)$excludeId;
        }
        $this->db->query($sql);
        foreach ($params as $key => $value) { $this->db->bind($key, $value); }
        return $this->db->single();
    }

    public function create($data) {
        // Ensure CreatedAt and UpdatedAt are set for new medicines
        $this->db->query("INSERT INTO Medicines (Name, Description, Unit, Manufacturer, StockQuantity, CreatedAt, UpdatedAt)
                          VALUES (:name, :description, :unit, :manufacturer, :stock_quantity, NOW(), NOW())");
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

    public function update($medicineId, $data) {
        $this->db->query("UPDATE Medicines SET
                            Name = :name, Description = :description, Unit = :unit,
                            Manufacturer = :manufacturer, StockQuantity = :stock_quantity,
                            UpdatedAt = NOW()
                          WHERE MedicineID = :id");
        $this->db->bind(':id', (int)$medicineId);
        $this->db->bind(':name', $data['Name']);
        $this->db->bind(':description', $data['Description'] ?? null);
        $this->db->bind(':unit', $data['Unit']);
        $this->db->bind(':manufacturer', $data['Manufacturer'] ?? null);
        $this->db->bind(':stock_quantity', $data['StockQuantity'] ?? 0);
        return $this->db->execute();
    }

    public function delete($medicineId) {
        // Consider checking usage in Prescriptions table before allowing deletion
        $this->db->query("DELETE FROM Medicines WHERE MedicineID = :id");
        $this->db->bind(':id', (int)$medicineId);
        return $this->db->execute();
    }

    public function countUsageInPrescriptions($medicineId) {
        $this->db->query("SELECT COUNT(PrescriptionID) as count FROM Prescriptions WHERE MedicineID = :medicine_id");
        $this->db->bind(':medicine_id', (int)$medicineId);
        $row = $this->db->single();
        return $row ? (int)$row['count'] : 0;
    }

    /**
     * Counts the total number of distinct medicines in the system.
     * @return int The total count of medicines.
     */
    public function getTotalMedicineCount() {
        $this->db->query("SELECT COUNT(MedicineID) as total_medicines FROM Medicines");
        $row = $this->db->single();
        return $row ? (int)$row['total_medicines'] : 0;
    }
}
?>