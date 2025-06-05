<?php
// app/models/FeedbackModel.php

class FeedbackModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getFeedbacksByPatientId($patientId) {
        $this->db->query("
            SELECT pf.FeedbackID, pf.Rating, pf.Comments, pf.CreatedAt, u_doc.FullName AS DoctorName, a.AppointmentDateTime AS VisitDate 
            FROM patientfeedbacks pf
            JOIN Appointments a ON pf.AppointmentID = a.AppointmentID
            JOIN Doctors d ON pf.DoctorID = d.DoctorID
            JOIN Users u_doc ON d.UserID = u_doc.UserID
            WHERE pf.PatientID = :patient_id
            ORDER BY pf.CreatedAt DESC
        ");
        $this->db->bind(':patient_id', $patientId);
        return $this->db->resultSet();
    }

    public function addFeedback($data) {
        $this->db->query("
            INSERT INTO patientfeedbacks (PatientID, DoctorID, AppointmentID, Rating, Comments, CreatedAt, UpdatedAt, IsPublished)
            VALUES (:patient_id, :doctor_id, :appointment_id, :rating, :comments, NOW(), NOW(), 0)
        ");
        $this->db->bind(':patient_id', $data['PatientID']);
        $this->db->bind(':doctor_id', $data['DoctorID']);
        $this->db->bind(':appointment_id', $data['AppointmentID']);
        $this->db->bind(':rating', $data['Rating']);
        $this->db->bind(':comments', $data['Comments']);
        return $this->db->execute();
    }

    public function getCompletedAppointmentsForFeedbackOptions($patientId) {
        $this->db->query("
            SELECT a.AppointmentID, a.AppointmentDateTime, u_doc.FullName AS DoctorName, d.DoctorID
            FROM Appointments a
            JOIN Doctors d ON a.DoctorID = d.DoctorID
            JOIN Users u_doc ON d.UserID = u_doc.UserID
            WHERE a.PatientID = :patient_id AND a.Status = 'Completed'
            ORDER BY a.AppointmentDateTime DESC
        ");
        $this->db->bind(':patient_id', $patientId);
        return $this->db->resultSet();
    }

    public function getPatientFeedbacksCountForDoctor($doctorId) {
        $this->db->query("SELECT COUNT(FeedbackID) as feedback_count FROM patientfeedbacks WHERE DoctorID = :doctor_id");
        $this->db->bind(':doctor_id', $doctorId);
        $row = $this->db->single();
        return $row ? (int)$row['feedback_count'] : 0;
    }

    /**
     * Retrieves all patient feedbacks for admin view, with optional filters.
     * @param array $filters Associative array of filters.
     * @return array List of feedbacks.
     */
    public function getAllFeedbacks($filters = []) {
        $sql = "SELECT 
                    pf.FeedbackID, pf.Rating, pf.Comments, pf.IsPublished, pf.CreatedAt AS FeedbackDate,
                    u_pat.FullName AS PatientName,
                    u_doc.FullName AS DoctorName,
                    a.AppointmentDateTime AS VisitDate,
                    a.AppointmentID
                FROM patientfeedbacks pf
                JOIN Patients pat_table ON pf.PatientID = pat_table.PatientID
                JOIN Users u_pat ON pat_table.UserID = u_pat.UserID
                JOIN Doctors doc ON pf.DoctorID = doc.DoctorID
                JOIN Users u_doc ON doc.UserID = u_doc.UserID
                LEFT JOIN Appointments a ON pf.AppointmentID = a.AppointmentID
                WHERE 1=1";
        $params = [];

        if (!empty($filters['doctor_id']) && filter_var($filters['doctor_id'], FILTER_VALIDATE_INT)) {
            $sql .= " AND pf.DoctorID = :doctor_id";
            $params[':doctor_id'] = $filters['doctor_id'];
        }
        if (isset($filters['rating']) && filter_var($filters['rating'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]])) {
            $sql .= " AND pf.Rating = :rating";
            $params[':rating'] = $filters['rating'];
        }
        if (isset($filters['is_published']) && in_array($filters['is_published'], ['0', '1'])) {
            $sql .= " AND pf.IsPublished = :is_published";
            $params[':is_published'] = (int)$filters['is_published'];
        }
        if (!empty($filters['search_term'])) {
            $sql .= " AND (u_pat.FullName LIKE :search_term OR u_doc.FullName LIKE :search_term OR pf.Comments LIKE :search_term)";
            $params[':search_term'] = '%' . $filters['search_term'] . '%';
        }
        $sql .= " ORDER BY pf.CreatedAt DESC";

        $this->db->query($sql);
        foreach ($params as $key => $value) { $this->db->bind($key, $value); }
        return $this->db->resultSet();
    }

    /**
     * Updates the publication status of a feedback entry.
     * @param int $feedbackId
     * @param bool $isPublished True to publish, false to unpublish.
     * @return bool
     */
    public function updateFeedbackPublicationStatus($feedbackId, $isPublished) {
        $this->db->query("UPDATE patientfeedbacks SET IsPublished = :is_published, UpdatedAt = NOW() WHERE FeedbackID = :feedback_id");
        $this->db->bind(':is_published', (int)$isPublished, PDO::PARAM_INT);
        $this->db->bind(':feedback_id', $feedbackId);
        return $this->db->execute();
    }
    
    /**
     * Retrieves a single feedback by its ID.
     * @param int $feedbackId
     * @return array|false
     */
    public function getFeedbackById($feedbackId) {
        $this->db->query("SELECT * FROM patientfeedbacks WHERE FeedbackID = :feedback_id");
        $this->db->bind(':feedback_id', $feedbackId);
        return $this->db->single();
    }
}
?>