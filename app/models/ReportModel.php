<?php
// app/models/ReportModel.php

class ReportModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getNewPatientsCount($startDate, $endDate) {
        $sql = "SELECT COUNT(UserID) as count FROM Users WHERE Role = 'Patient' AND CreatedAt >= :start_date AND CreatedAt < DATE_ADD(:end_date, INTERVAL 1 DAY)";
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate); $this->db->bind(':end_date', $endDate);
        $row = $this->db->single();
        return $row ? (int)$row['count'] : 0;
    }

    public function getCompletedAppointmentsCount($startDate, $endDate) {
        $sql = "SELECT COUNT(AppointmentID) as count FROM Appointments WHERE Status = 'Completed' AND DATE(AppointmentDateTime) BETWEEN :start_date AND :end_date";
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate); $this->db->bind(':end_date', $endDate);
        $row = $this->db->single();
        return $row ? (int)$row['count'] : 0;
    }

    public function getAppointmentCountsByStatus($startDate, $endDate) {
        // Counts based on when the appointment was scheduled (CreatedAt) within the date range
        // If you want to count based on AppointmentDateTime, change CreatedAt to DATE(AppointmentDateTime)
        $sql = "SELECT Status, COUNT(AppointmentID) as count FROM Appointments WHERE DATE(AppointmentDateTime) BETWEEN :start_date AND :end_date GROUP BY Status";
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate); $this->db->bind(':end_date', $endDate);
        return $this->db->resultSet();
    }

    public function getCompletedAppointmentsByDoctor($startDate, $endDate, $specializationId = null) {
        $sql = "SELECT U.FullName as doctor_name, COUNT(A.AppointmentID) as completed_count
                FROM Appointments A
                JOIN Doctors DocTable ON A.DoctorID = DocTable.DoctorID
                JOIN Users U ON DocTable.UserID = U.UserID";
        $params = [':start_date' => $startDate, ':end_date' => $endDate];
        $whereClauses = ["A.Status = 'Completed'", "DATE(A.AppointmentDateTime) BETWEEN :start_date AND :end_date"];

        if ($specializationId && filter_var($specializationId, FILTER_VALIDATE_INT)) {
            $sql .= " JOIN Specializations S ON DocTable.SpecializationID = S.SpecializationID"; // Join only if filtering by spec
            $whereClauses[] = "S.SpecializationID = :specialization_id";
            $params[':specialization_id'] = $specializationId;
        }
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
        $sql .= " GROUP BY A.DoctorID, U.FullName ORDER BY completed_count DESC";
        $this->db->query($sql);
        foreach ($params as $key => $value) { $this->db->bind($key, $value); }
        return $this->db->resultSet();
    }

    public function getCompletedAppointmentsBySpecialization($startDate, $endDate) {
        $sql = "SELECT S.Name as specialization_name, COUNT(A.AppointmentID) as completed_count
                FROM Appointments A
                JOIN Doctors DocTable ON A.DoctorID = DocTable.DoctorID
                JOIN Specializations S ON DocTable.SpecializationID = S.SpecializationID
                WHERE A.Status = 'Completed' AND DATE(A.AppointmentDateTime) BETWEEN :start_date AND :end_date
                GROUP BY S.SpecializationID, S.Name ORDER BY completed_count DESC";
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate); $this->db->bind(':end_date', $endDate);
        return $this->db->resultSet();
    }

    public function getCompletedAppointmentsTrendByDay($startDate, $endDate) {
        $sql = "SELECT DATE(AppointmentDateTime) as visit_date, COUNT(AppointmentID) as completed_count
                FROM Appointments WHERE Status = 'Completed' AND DATE(AppointmentDateTime) BETWEEN :start_date AND :end_date
                GROUP BY DATE(AppointmentDateTime) ORDER BY visit_date ASC";
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate); $this->db->bind(':end_date', $endDate);
        return $this->db->resultSet();
    }
}
?>