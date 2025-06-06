<?php
class AppointmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createAppointment($patientId, $doctorId, $availabilityId, $appointmentDateTime, $reasonForVisit = null, $status = 'Scheduled') {
        try {
            $this->db->query('INSERT INTO Appointments (PatientID, DoctorID, AvailabilityID, AppointmentDateTime, ReasonForVisit, Status, CreatedAt, UpdatedAt)
                              VALUES (:patientId, :doctorId, :availabilityId, :appointmentDateTime, :reasonForVisit, :status, NOW(), NOW())');
            $this->db->bind(':patientId', $patientId);
            $this->db->bind(':doctorId', $doctorId);
            $this->db->bind(':availabilityId', $availabilityId);
            $this->db->bind(':appointmentDateTime', $appointmentDateTime);
            $this->db->bind(':reasonForVisit', $reasonForVisit);
            $this->db->bind(':status', $status);
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log("AppointmentModel::createAppointment Error: " . $e->getMessage());
        }
        return false;
    }

    public function getAppointmentById($appointmentId) {
        if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0) return false;
        $this->db->query('SELECT * FROM Appointments WHERE AppointmentID = :appointmentId');
        $this->db->bind(':appointmentId', $appointmentId);
        return $this->db->single();
    }

    public function getAppointmentsByPatientId($patientId, $statusFilter = 'All', $orderBy = 'a.AppointmentDateTime DESC') {
        if (!filter_var($patientId, FILTER_VALIDATE_INT) || $patientId <= 0) return [];
        $sql = "SELECT
                    a.AppointmentID, a.AppointmentDateTime, a.ReasonForVisit, a.Status, a.AvailabilityID,
                    u_doc.FullName AS DoctorName, s.Name AS SpecializationName
                FROM Appointments a
                JOIN Doctors d ON a.DoctorID = d.DoctorID
                JOIN Users u_doc ON d.UserID = u_doc.UserID
                LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
                WHERE a.PatientID = :patient_id";
        $sqlParams = [':patient_id' => $patientId];
        if ($statusFilter !== 'All' && !empty($statusFilter)) {
            $sql .= " AND a.Status = :status_filter";
            $sqlParams[':status_filter'] = $statusFilter;
        }
        $sql .= " ORDER BY " . filter_var($orderBy, FILTER_SANITIZE_SPECIAL_CHARS);
        try {
            $this->db->query($sql);
            foreach ($sqlParams as $param => $value) {
                $this->db->bind($param, $value);
            }
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("AppointmentModel::getAppointmentsByPatientId Error: " . $e->getMessage());
        }
        return [];
    }

    public function getAppointmentsByDoctorId($doctorId, $statusFilter = 'All', $dateRangeFilter = [], $orderBy = 'a.AppointmentDateTime ASC') {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0) return [];
        $sql = "SELECT
                    a.AppointmentID, a.AppointmentDateTime, a.ReasonForVisit, a.Status,
                    u_pat.FullName AS PatientName, u_pat.PhoneNumber AS PatientPhoneNumber
                FROM Appointments a
                JOIN Patients pat_info ON a.PatientID = pat_info.PatientID
                JOIN Users u_pat ON pat_info.UserID = u_pat.UserID
                WHERE a.DoctorID = :doctor_id";
        $params = [':doctor_id' => $doctorId];
        if ($statusFilter !== 'All' && !empty($statusFilter)) {
            $sql .= " AND a.Status = :status_filter";
            $params[':status_filter'] = $statusFilter;
        }
        if (!empty($dateRangeFilter['start_date']) && !empty($dateRangeFilter['end_date'])) {
            $sql .= " AND DATE(a.AppointmentDateTime) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $dateRangeFilter['start_date'];
            $params[':end_date'] = $dateRangeFilter['end_date'];
        } elseif (!empty($dateRangeFilter['specific_date'])) {
            $sql .= " AND DATE(a.AppointmentDateTime) = :specific_date";
            $params[':specific_date'] = $dateRangeFilter['specific_date'];
        } elseif (isset($dateRangeFilter['type']) && $dateRangeFilter['type'] === 'all_upcoming') {
             $sql .= " AND a.AppointmentDateTime >= CURDATE()";
             if ($statusFilter === 'All' || empty($statusFilter)) {
                $sql .= " AND a.Status IN ('Scheduled', 'Confirmed')";
             }
        }
        $sql .= " ORDER BY " . filter_var($orderBy, FILTER_SANITIZE_SPECIAL_CHARS);
        try {
            $this->db->query($sql);
            foreach ($params as $key => $value) {
                $this->db->bind($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("AppointmentModel::getAppointmentsByDoctorId Error: " . $e->getMessage());
        }
        return [];
    }

    public function updateAppointmentStatus($appointmentId, $status) {
        if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0 || empty($status)) return false;
        try {
            $this->db->query('UPDATE Appointments SET Status = :status, UpdatedAt = NOW() WHERE AppointmentID = :appointmentId');
            $this->db->bind(':status', $status);
            $this->db->bind(':appointmentId', $appointmentId);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("AppointmentModel::updateAppointmentStatus Error: " . $e->getMessage());
        }
        return false;
    }

    public function getAppointmentDetailsForCancellation($appointmentId, $patientId) {
        if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0 || 
            !filter_var($patientId, FILTER_VALIDATE_INT) || $patientId <= 0) return false;
        $this->db->query(
            "SELECT AppointmentID, PatientID, DoctorID, AvailabilityID, Status, AppointmentDateTime
             FROM Appointments
             WHERE AppointmentID = :appointment_id AND PatientID = :patient_id"
        );
        $this->db->bind(':appointment_id', $appointmentId);
        $this->db->bind(':patient_id', $patientId);
        return $this->db->single();
    }

    public function markSlotAsAvailableAgain($availabilityId) {
        if (empty($availabilityId) || !filter_var($availabilityId, FILTER_VALIDATE_INT) || $availabilityId <=0) {
            return true; 
        }
        try {
            $this->db->query("UPDATE DoctorAvailability SET IsBooked = FALSE WHERE AvailabilityID = :availability_id");
            $this->db->bind(':availability_id', $availabilityId);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("AppointmentModel::markSlotAsAvailableAgain Error: " . $e->getMessage());
        }
        return false;
    }

    public function getAppointmentByIdWithDoctorInfo($appointmentId) {
        if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0) return false;
        $this->db->query("
            SELECT a.*, u_doc.FullName AS DoctorName, s.Name AS SpecializationName
            FROM Appointments a
            JOIN Doctors d ON a.DoctorID = d.DoctorID
            JOIN Users u_doc ON d.UserID = u_doc.UserID
            LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
            WHERE a.AppointmentID = :appointment_id
        ");
        $this->db->bind(':appointment_id', $appointmentId);
        return $this->db->single();
    }

    public function getAllAppointmentsForAdmin($filters = [], $orderBy = 'a.AppointmentDateTime DESC') {
        $sql = "SELECT
                    a.AppointmentID, a.AppointmentDateTime, a.ReasonForVisit, a.Status,
                    u_doc.FullName AS DoctorName, s.Name AS SpecializationName,
                    u_pat.FullName AS PatientName, u_pat.PhoneNumber AS PatientPhoneNumber,
                    mr.RecordID
                FROM Appointments a
                JOIN Doctors d ON a.DoctorID = d.DoctorID
                JOIN Users u_doc ON d.UserID = u_doc.UserID
                JOIN Patients pat_info ON a.PatientID = pat_info.PatientID
                JOIN Users u_pat ON pat_info.UserID = u_pat.UserID
                LEFT JOIN Specializations s ON d.SpecializationID = s.SpecializationID
                LEFT JOIN MedicalRecords mr ON a.AppointmentID = mr.AppointmentID
                WHERE 1=1";
        $params = [];
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(a.AppointmentDateTime) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(a.AppointmentDateTime) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        if (!empty($filters['doctor_id']) && filter_var($filters['doctor_id'], FILTER_VALIDATE_INT)) {
            $sql .= " AND a.DoctorID = :doctor_id";
            $params[':doctor_id'] = (int)$filters['doctor_id'];
        }
        if (!empty($filters['patient_search'])) {
            $sql .= " AND (u_pat.FullName LIKE :patient_search OR u_pat.PhoneNumber LIKE :patient_search OR u_pat.Email LIKE :patient_search)";
            $params[':patient_search'] = '%' . $filters['patient_search'] . '%';
        }
        if (!empty($filters['status']) && $filters['status'] !== 'All') {
            $sql .= " AND a.Status = :status";
            $params[':status'] = $filters['status'];
        }
        $sql .= " ORDER BY " . filter_var($orderBy, FILTER_SANITIZE_SPECIAL_CHARS);
        try {
            $this->db->query($sql);
            foreach ($params as $key => $value) {
                $this->db->bind($key, $value);
            }
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("AppointmentModel::getAllAppointmentsForAdmin Error: " . $e->getMessage());
        }
        return [];
    }

    public function getAppointmentsFilteredForNurse($filters, array $assignedDoctorIds) {
        if (empty($assignedDoctorIds)) {
            return [];
        }

        $sql = "SELECT 
                    apt.AppointmentID, apt.AppointmentDateTime, apt.ReasonForVisit, apt.Status,
                    p.FullName AS PatientName,
                    d.FullName AS DoctorName,
                    s.Name AS SpecializationName
                FROM appointments apt
                JOIN users p ON apt.PatientID = p.UserID
                JOIN users d ON apt.DoctorID = d.UserID
                LEFT JOIN doctors doc_profile ON d.UserID = doc_profile.UserID
                LEFT JOIN specializations s ON doc_profile.SpecializationID = s.SpecializationID
                WHERE 1=1";


        $doctorPlaceholders = [];
        $params = [];
        foreach ($assignedDoctorIds as $key => $id) {
            $placeholder = ":doc_id_{$key}";
            $doctorPlaceholders[] = $placeholder;
            $params[$placeholder] = $id;
        }
        $sql .= " AND d.UserID IN (" . implode(', ', $doctorPlaceholders) . ")";
        
        // Áp dụng các bộ lọc
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(apt.AppointmentDateTime) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(apt.AppointmentDateTime) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        if (isset($filters['status']) && $filters['status'] !== 'All') {
            $sql .= " AND apt.Status = :status";
            $params[':status'] = $filters['status'];
        }
        if (isset($filters['doctor_id']) && !empty($filters['doctor_id'])) {
            $sql .= " AND d.UserID = :doctor_id_filter"; 
            $params[':doctor_id_filter'] = $filters['doctor_id'];
        }

        $sql .= " ORDER BY apt.AppointmentDateTime DESC";

        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }

        return $this->db->resultSet();
    }
    public function getUpcomingAppointmentsForNurseDashboard(array $assignedDoctorIds, $startDate, $endDate, $limit = 10, $statuses = ['Scheduled', 'Confirmed']) {
        if (empty($assignedDoctorIds) || empty($statuses) || !filter_var($limit, FILTER_VALIDATE_INT) || $limit <=0) {
            return [];
        }
        $sqlParams = [':start_date' => $startDate, ':end_date' => $endDate, ':limit' => $limit];
        $sql = "SELECT A.AppointmentID, A.AppointmentDateTime, A.Status,
                       PUser.FullName as PatientName, DocUser.FullName as DoctorName, S.Name as SpecializationName
                FROM Appointments A
                JOIN Patients Pat ON A.PatientID = Pat.PatientID
                JOIN Users PUser ON Pat.UserID = PUser.UserID
                JOIN Doctors Doc ON A.DoctorID = Doc.DoctorID
                JOIN Users DocUser ON Doc.UserID = DocUser.UserID
                LEFT JOIN Specializations S ON Doc.SpecializationID = S.SpecializationID
                WHERE DATE(A.AppointmentDateTime) BETWEEN :start_date AND :end_date";
        $doctorInClausePlaceholders = [];
        foreach ($assignedDoctorIds as $key => $docId) {
            $placeholder = ":docIdDash{$key}";
            $doctorInClausePlaceholders[] = $placeholder;
            $sqlParams[$placeholder] = $docId;
        }
        $sql .= " AND A.DoctorID IN (" . implode(',', $doctorInClausePlaceholders) . ")";
        $statusInClausePlaceholders = [];
        foreach ($statuses as $key => $status) {
            $placeholder = ":statusDash{$key}";
            $statusInClausePlaceholders[] = $placeholder;
            $sqlParams[$placeholder] = $status;
        }
        $sql .= " AND A.Status IN (" . implode(',', $statusInClausePlaceholders) . ")";
        $sql .= " ORDER BY A.AppointmentDateTime ASC LIMIT :limit";
        try {
            $this->db->query($sql);
            foreach ($sqlParams as $param => $value) {
                $this->db->bind($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("AppointmentModel::getUpcomingAppointmentsForNurseDashboard Error: " . $e->getMessage());
        }
        return [];
    }
    
    public function getAppointmentDetailsById($appointmentId) {
        if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0) return false;
        $sql = "SELECT A.*,
                       PUser.FullName as PatientFullName, PUser.Email as PatientEmail, PUser.PhoneNumber as PatientPhoneNumber,
                       Pat.PatientID as PatientProfileID, Pat.DateOfBirth as PatientDOB, Pat.Gender as PatientGender,
                       DocUser.FullName as DoctorFullName, Doc.DoctorID, 
                       S.Name as SpecializationName,
                       NUser.FullName as NurseFullName
                FROM Appointments A
                JOIN Patients Pat ON A.PatientID = Pat.PatientID
                JOIN Users PUser ON Pat.UserID = PUser.UserID
                JOIN Doctors Doc ON A.DoctorID = Doc.DoctorID
                JOIN Users DocUser ON Doc.UserID = DocUser.UserID
                LEFT JOIN Specializations S ON Doc.SpecializationID = S.SpecializationID
                LEFT JOIN Nurses N ON A.NurseID = N.NurseID
                LEFT JOIN Users NUser ON N.UserID = NUser.UserID
                WHERE A.AppointmentID = :appointment_id";
        $this->db->query($sql);
        $this->db->bind(':appointment_id', $appointmentId, PDO::PARAM_INT);
        return $this->db->single();
    }

    public function getUpcomingAppointmentsCountForPatient($patientId) {
        if (!filter_var($patientId, FILTER_VALIDATE_INT) || $patientId <= 0) return 0;
        $today = date('Y-m-d H:i:s');
        $this->db->query("
            SELECT COUNT(AppointmentID) as upcoming_count
            FROM Appointments
            WHERE PatientID = :patient_id
              AND (Status = 'Scheduled' OR Status = 'Confirmed')
              AND AppointmentDateTime >= :today
        ");
        $this->db->bind(':patient_id', $patientId);
        $this->db->bind(':today', $today);
        $row = $this->db->single();
        return $row ? (int)$row['upcoming_count'] : 0;
    }

    public function getTodaysAppointmentsForPatient($patientId) {
        if (!filter_var($patientId, FILTER_VALIDATE_INT) || $patientId <= 0) return [];
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        $this->db->query("
            SELECT a.AppointmentID, a.AppointmentDateTime, a.Status, u_doc.FullName AS DoctorFullName
            FROM Appointments a
            JOIN Doctors d ON a.DoctorID = d.DoctorID
            JOIN Users u_doc ON d.UserID = u_doc.UserID
            WHERE a.PatientID = :patient_id
              AND a.AppointmentDateTime BETWEEN :today_start AND :today_end
              AND a.Status NOT IN ('CancelledByPatient', 'CancelledByClinic', 'Completed', 'NoShow')
            ORDER BY a.AppointmentDateTime ASC
        ");
        $this->db->bind(':patient_id', $patientId);
        $this->db->bind(':today_start', $today_start);
        $this->db->bind(':today_end', $today_end);
        return $this->db->resultSet();
    }

    public function getFollowUpsDueCount($doctorId) {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0) return 0;
        $today = date('Y-m-d H:i:s');
        $nextSevenDays = date('Y-m-d H:i:s', strtotime('+7 days'));
        $this->db->query("
            SELECT COUNT(AppointmentID) as followup_count
            FROM Appointments
            WHERE DoctorID = :doctor_id
              AND (Status = 'Scheduled' OR Status = 'Confirmed')
              AND AppointmentDateTime BETWEEN :today AND :next_seven_days
        ");
        $this->db->bind(':doctor_id', $doctorId);
        $this->db->bind(':today', $today);
        $this->db->bind(':next_seven_days', $nextSevenDays);
        $row = $this->db->single();
        return $row ? (int)$row['followup_count'] : 0;
    }

    public function getTodaysAppointmentsForDoctor($doctorId) {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0) return [];
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        $this->db->query("
            SELECT a.AppointmentID, a.AppointmentDateTime, a.Status, a.ReasonForVisit, u_pat.FullName AS PatientName 
            FROM Appointments a
            JOIN Patients pat ON a.PatientID = pat.PatientID
            JOIN Users u_pat ON pat.UserID = u_pat.UserID
            WHERE a.DoctorID = :doctor_id
              AND a.AppointmentDateTime BETWEEN :today_start AND :today_end
              AND a.Status NOT IN ('CancelledByPatient', 'CancelledByClinic', 'Completed', 'NoShow')
            ORDER BY a.AppointmentDateTime ASC
        ");
        $this->db->bind(':doctor_id', $doctorId);
        $this->db->bind(':today_start', $today_start);
        $this->db->bind(':today_end', $today_end);
        return $this->db->resultSet();
    }

    public function getAppointmentsInDateRangeByDoctor($doctorId, $startDate, $endDate, array $statuses = ['Scheduled', 'Confirmed']) {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0 || 
            !DateTime::createFromFormat('Y-m-d', $startDate) || 
            !DateTime::createFromFormat('Y-m-d', $endDate) ||
            strtotime($endDate) < strtotime($startDate) ||
            empty($statuses)) {
            return [];
        }

        $sqlParams = [
            ':doctor_id' => $doctorId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];
        $sql = "SELECT AppointmentID, AppointmentDateTime, Status, PatientID 
                FROM Appointments
                WHERE DoctorID = :doctor_id
                  AND DATE(AppointmentDateTime) BETWEEN :start_date AND :end_date";
        
        $statusInClausePlaceholders = [];
        foreach ($statuses as $key => $status) {
            $placeholder = ":statusLeaveCheck{$key}";
            $statusInClausePlaceholders[] = $placeholder;
            $sqlParams[$placeholder] = $status;
        }
        $sql .= " AND Status IN (" . implode(',', $statusInClausePlaceholders) . ")";
        $sql .= " ORDER BY AppointmentDateTime ASC";

        try {
            $this->db->query($sql);
            foreach ($sqlParams as $param => $value) {
                $this->db->bind($param, $value);
            }
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("AppointmentModel::getAppointmentsInDateRangeByDoctor Error: " . $e->getMessage());
        }
        return [];
    }
}
?>