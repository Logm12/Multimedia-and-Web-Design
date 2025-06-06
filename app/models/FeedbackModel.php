<?php
// app/models/FeedbackModel.php

class FeedbackModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getFeedbacksByPatientId($patientId) {
        // Assuming $patientId is UserID from users table
        $this->db->query("
            SELECT pf.FeedbackID, pf.Rating, pf.Comments, pf.CreatedAt, u_doc.FullName AS DoctorName, a.AppointmentDateTime AS VisitDate 
            FROM patientfeedbacks pf
            LEFT JOIN appointments a ON pf.AppointmentID = a.AppointmentID
            LEFT JOIN users u_doc ON pf.DoctorID = u_doc.UserID
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
        // Assuming $patientId is UserID from users table
        $this->db->query("
            SELECT a.AppointmentID, a.AppointmentDateTime, u_doc.FullName AS DoctorName, d.DoctorID
            FROM appointments a
            JOIN doctors d ON a.DoctorID = d.DoctorID
            JOIN users u_doc ON d.UserID = u_doc.UserID
            WHERE a.PatientID = :patient_id AND a.Status = 'Completed'
            AND a.AppointmentID NOT IN (SELECT AppointmentID FROM patientfeedbacks WHERE AppointmentID IS NOT NULL)
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

    // <<<< "NÂNG CẤP" HÀM NÀY CHO ADMIN NÈ CẬU >>>>
    public function getAllFeedbacksWithDetails($filters = []) {
        $sql = "SELECT 
                    pf.FeedbackID, pf.Rating, pf.Comments, pf.IsPublished, pf.CreatedAt AS FeedbackDate,
                    u_pat.FullName AS PatientName,
                    u_doc.FullName AS DoctorName,
                    a.AppointmentDateTime AS VisitDate,
                    a.AppointmentID
                FROM patientfeedbacks pf
                JOIN users u_pat ON pf.PatientID = u_pat.UserID
                LEFT JOIN users u_doc ON pf.DoctorID = u_doc.UserID
                LEFT JOIN appointments a ON pf.AppointmentID = a.AppointmentID
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

    public function updateFeedbackPublicationStatus($feedbackId, $isPublished) {
        $this->db->query("UPDATE patientfeedbacks SET IsPublished = :is_published, UpdatedAt = NOW() WHERE FeedbackID = :feedback_id");
        $this->db->bind(':is_published', (int)$isPublished, PDO::PARAM_INT);
        $this->db->bind(':feedback_id', $feedbackId);
        return $this->db->execute();
    }
    
    public function getFeedbackById($feedbackId) {
        $this->db->query("SELECT * FROM patientfeedbacks WHERE FeedbackID = :feedback_id");
        $this->db->bind(':feedback_id', $feedbackId);
        return $this->db->single();
    }
}
?>