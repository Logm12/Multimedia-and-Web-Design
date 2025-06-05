<?php
// app/models/PatientModel.php

class PatientModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new patient record linked to a UserID.
     * @param array $data Patient data: UserID, DateOfBirth, Gender, BloodType, InsuranceInfo, MedicalHistorySummary.
     * @return bool True on success, false on failure.
     */
    public function createPatient($data) {
        // Ensure CreatedAt and UpdatedAt are set for new patient records
        $this->db->query('INSERT INTO Patients (UserID, DateOfBirth, Gender, BloodType, InsuranceInfo, MedicalHistorySummary, CreatedAt, UpdatedAt)
                          VALUES (:UserID, :DateOfBirth, :Gender, :BloodType, :InsuranceInfo, :MedicalHistorySummary, NOW(), NOW())');

        $this->db->bind(':UserID', $data['UserID']);
        $this->db->bind(':DateOfBirth', $data['DateOfBirth'] ?? null);
        $this->db->bind(':Gender', $data['Gender'] ?? null);
        $this->db->bind(':BloodType', $data['BloodType'] ?? null);
        $this->db->bind(':InsuranceInfo', $data['InsuranceInfo'] ?? null);
        $this->db->bind(':MedicalHistorySummary', $data['MedicalHistorySummary'] ?? null);

        return $this->db->execute();
    }

    /**
     * Retrieves detailed information of a patient by PatientID (joins with Users).
     * @param int $patientId
     * @return array|false
     */
    public function getPatientDetailsById($patientId) {
        $this->db->query("
            SELECT
                p.PatientID, p.UserID, p.DateOfBirth, p.Gender, p.BloodType, 
                p.InsuranceInfo, p.MedicalHistorySummary, p.CreatedAt AS PatientCreatedAt, p.UpdatedAt AS PatientUpdatedAt,
                u.FullName, u.Email, u.PhoneNumber, u.Address AS UserAddress, u.Avatar, u.Status AS UserStatus
            FROM Patients p
            JOIN Users u ON p.UserID = u.UserID
            WHERE p.PatientID = :patient_id
        ");
        $this->db->bind(':patient_id', $patientId);
        return $this->db->single();
    }

    /**
     * Retrieves PatientID and UserID by UserID.
     * @param int $userId
     * @return array|false
     */
    public function getPatientByUserId($userId) {
        $this->db->query("SELECT PatientID, UserID FROM Patients WHERE UserID = :user_id");
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }

    /**
     * Updates patient-specific details.
     * @param int $patientId ID of the record in the Patients table.
     * @param array $data Array of fields to update (DateOfBirth, Gender, etc.).
     * @return bool
     */
    public function updatePatient($patientId, $data) {
        $this->db->query('UPDATE Patients SET
                            DateOfBirth = :date_of_birth, Gender = :gender, BloodType = :blood_type,
                            InsuranceInfo = :insurance_info, MedicalHistorySummary = :medical_history_summary,
                            UpdatedAt = NOW()
                          WHERE PatientID = :patient_id');

        $this->db->bind(':date_of_birth', $data['DateOfBirth'] ?? null);
        $this->db->bind(':gender', $data['Gender'] ?? null);
        $this->db->bind(':blood_type', $data['BloodType'] ?? null);
        $this->db->bind(':insurance_info', $data['InsuranceInfo'] ?? null);
        $this->db->bind(':medical_history_summary', $data['MedicalHistorySummary'] ?? null);
        $this->db->bind(':patient_id', $patientId);
        return $this->db->execute();
    }

    // --- NEW METHODS FOR DOCTOR'S PATIENT LIST ---

    /**
     * Retrieves all patients with their basic user details and last visit date.
     * Optionally filters by search term.
     * @param string|null $searchTerm Search by patient name, email, or phone.
     * @param int|null $doctorId (Optional) If you want to list patients specific to a doctor.
     * @return array List of patients.
     */
    public function getAllPatientsWithDetails($searchTerm = null, $doctorId = null) {
        // This query can become complex if "LastVisitDate" needs to be highly accurate
        // and consider different types of interactions.
        // For simplicity, this example gets the latest appointment date as LastVisitDate.
        $sql = "
            SELECT
                p.PatientID,
                u.FullName,
                u.Email,
                u.PhoneNumber,
                (SELECT MAX(a.AppointmentDateTime) 
                 FROM Appointments a 
                 WHERE a.PatientID = p.PatientID" . 
                 ($doctorId ? " AND a.DoctorID = :doctor_id_for_last_visit" : "") . 
                ") AS LastVisitDate
            FROM Patients p
            JOIN Users u ON p.UserID = u.UserID
            WHERE u.Role = 'Patient' AND u.Status = 'Active' 
        "; // Typically list active patients

        $params = [];
        if ($doctorId) {
            $params[':doctor_id_for_last_visit'] = $doctorId;
            // If you only want to list patients who have had an appointment with THIS doctor:
            // $sql .= " AND p.PatientID IN (SELECT DISTINCT app.PatientID FROM Appointments app WHERE app.DoctorID = :doctor_id_filter)";
            // $params[':doctor_id_filter'] = $doctorId;
        }


        if (!empty($searchTerm)) {
            $sql .= " AND (u.FullName LIKE :search_term OR u.Email LIKE :search_term OR u.PhoneNumber LIKE :search_term)";
            $params[':search_term'] = '%' . $searchTerm . '%';
        }
        $sql .= " ORDER BY u.FullName ASC";

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->resultSet();
    }

    /**
     * Counts the total number of active patients.
     * @return int
     */
    public function getTotalPatientsCount() {
        $this->db->query("SELECT COUNT(p.PatientID) as total_patients 
                          FROM Patients p
                          JOIN Users u ON p.UserID = u.UserID
                          WHERE u.Role = 'Patient' AND u.Status = 'Active'");
        $row = $this->db->single();
        return $row ? (int)$row['total_patients'] : 0;
    }

    /**
     * Counts the number of new active patients registered this month.
     * It checks Users.CreatedAt for registration date.
     * @return int
     */
    public function getNewPatientsThisMonthCount() {
        $firstDayOfMonth = date('Y-m-01 00:00:00');
        $lastDayOfMonth = date('Y-m-t 23:59:59');

        $this->db->query("
            SELECT COUNT(p.PatientID) as new_patients_this_month
            FROM Patients p
            JOIN Users u ON p.UserID = u.UserID
            WHERE u.Role = 'Patient' AND u.Status = 'Active'
              AND u.CreatedAt BETWEEN :first_day AND :last_day
        ");
        $this->db->bind(':first_day', $firstDayOfMonth);
        $this->db->bind(':last_day', $lastDayOfMonth);
        $row = $this->db->single();
        return $row ? (int)$row['new_patients_this_month'] : 0;
    }
}
?>