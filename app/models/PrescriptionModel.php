<?php
// app/models/PrescriptionModel.php

class PrescriptionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all prescribed medicines for a MedicalRecordID.
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
     * Adds a medicine to a prescription for a MedicalRecordID.
     * @param int $recordId
     * @param int $medicineId
     * @param string $dosage
     * @param string $frequency
     * @param string $duration
     * @param string|null $instructions
     * @return bool|int PrescriptionID on success.
     */
    public function addMedicineToPrescription($recordId, $medicineId, $dosage, $frequency, $duration, $instructions = null) {
        $this->db->query("
            INSERT INTO Prescriptions (RecordID, MedicineID, Dosage, Frequency, Duration, Instructions, CreatedAt, UpdatedAt)
            VALUES (:record_id, :medicine_id, :dosage, :frequency, :duration, :instructions, NOW(), NOW())
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
     * Deletes all prescribed medicines for a MedicalRecordID (used when updating a prescription).
     * @param int $recordId
     * @return bool
     */
    public function deletePrescriptionsByRecordId($recordId) {
        $this->db->query("DELETE FROM Prescriptions WHERE RecordID = :record_id");
        $this->db->bind(':record_id', $recordId);
        return $this->db->execute();
    }

    /**
     * Counts the number of active prescriptions for a specific patient.
     * Definition of "active" is based on recent medical records (e.g., last 90 days).
     * @param int $patientId The ID of the patient (Patients.PatientID).
     * @return int The count of active prescriptions.
     */
    public function getActivePrescriptionsCountForPatient($patientId) {
        $ninety_days_ago = date('Y-m-d H:i:s', strtotime('-90 days'));

        $this->db->query("
            SELECT COUNT(DISTINCT p.PrescriptionID) as active_count
            FROM Prescriptions p
            JOIN MedicalRecords mr ON p.RecordID = mr.RecordID
            WHERE mr.PatientID = :patient_id
              AND mr.VisitDate >= :ninety_days_ago
        ");
        $this->db->bind(':patient_id', $patientId);
        $this->db->bind(':ninety_days_ago', $ninety_days_ago);
        
        $row = $this->db->single();
        return $row ? (int)$row['active_count'] : 0;
    }
}
?>