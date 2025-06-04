<?php
// app/models/PrescriptionModel.php

class PrescriptionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy tất cả các thuốc đã kê cho một MedicalRecordID
     * @param int $recordId
     * @return array
     */
    public function getPrescriptionsByRecordId($recordId) {
        $this->db->query("
            SELECT pr.*, m.Name AS MedicineName, m.Unit AS MedicineUnit
            FROM Prescriptions pr
            JOIN Medicines m ON pr.MedicineID = m.MedicineID
            WHERE pr.RecordID = :record_id
            ORDER BY pr.PrescriptionID ASC
        ");
        $this->db->bind(':record_id', $recordId);
        return $this->db->resultSet();
    }

    /**
     * Thêm một thuốc vào đơn thuốc cho một MedicalRecordID
     * @param int $recordId
     * @param int $medicineId
     * @param string $dosage
     * @param string $frequency
     * @param string $duration
     * @param string|null $instructions
     * @return bool|int PrescriptionID nếu thành công
     */
    public function addMedicineToPrescription($recordId, $medicineId, $dosage, $frequency, $duration, $instructions = null) {
        $this->db->query("
            INSERT INTO Prescriptions (RecordID, MedicineID, Dosage, Frequency, Duration, Instructions)
            VALUES (:record_id, :medicine_id, :dosage, :frequency, :duration, :instructions)
        ");
        $this->db->bind(':record_id', $recordId);
        $this->db->bind(':medicine_id', $medicineId);
        $this->db->bind(':dosage', $dosage);
        $this->db->bind(':frequency', $frequency);
        $this->db->bind(':duration', $duration);
        $this->db->bind(':instructions', $instructions);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Xóa tất cả các thuốc trong đơn của một MedicalRecordID (dùng khi cập nhật đơn thuốc)
     * @param int $recordId
     * @return bool
     */
    public function deletePrescriptionsByRecordId($recordId) {
        $this->db->query("DELETE FROM Prescriptions WHERE RecordID = :record_id");
        $this->db->bind(':record_id', $recordId);
        return $this->db->execute();
    }

    // Có thể thêm các hàm update/delete từng dòng thuốc nếu cần
}
?>