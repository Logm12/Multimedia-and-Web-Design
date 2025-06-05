<?php
// app/models/LeaveRequestModel.php

class LeaveRequestModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createLeaveRequest(array $data) {
        if (empty($data['DoctorID']) || empty($data['StartDate']) || empty($data['EndDate']) ||
            !filter_var($data['DoctorID'], FILTER_VALIDATE_INT) || $data['DoctorID'] <= 0 ||
            !DateTime::createFromFormat('Y-m-d', $data['StartDate']) ||
            !DateTime::createFromFormat('Y-m-d', $data['EndDate']) ||
            strtotime($data['EndDate']) < strtotime($data['StartDate'])) {
            error_log("LeaveRequestModel::createLeaveRequest - Invalid or missing required data.");
            return false;
        }
        try {
            $this->db->query("INSERT INTO leaverequests (DoctorID, StartDate, EndDate, Reason, Status, RequestedAt) 
                              VALUES (:doctor_id, :start_date, :end_date, :reason, :status, NOW())");
            $this->db->bind(':doctor_id', $data['DoctorID']);
            $this->db->bind(':start_date', $data['StartDate']);
            $this->db->bind(':end_date', $data['EndDate']);
            $this->db->bind(':reason', isset($data['Reason']) ? trim($data['Reason']) : null);
            $this->db->bind(':status', $data['Status'] ?? 'Pending');
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log("Error in LeaveRequestModel::createLeaveRequest: " . $e->getMessage());
        }
        return false;
    }

    public function getLeaveRequestById($leaveRequestId) {
        if (!filter_var($leaveRequestId, FILTER_VALIDATE_INT) || $leaveRequestId <= 0) return false;
        try {
            $this->db->query("SELECT lr.*, 
                                     u_doc.FullName AS DoctorName, 
                                     d.UserID AS DoctorUserID, /* Lấy UserID của bác sĩ để gửi thông báo nếu cần */
                                     u_admin.FullName AS ReviewedByAdminName 
                              FROM leaverequests lr
                              JOIN doctors d ON lr.DoctorID = d.DoctorID
                              JOIN users u_doc ON d.UserID = u_doc.UserID
                              LEFT JOIN users u_admin ON lr.ReviewedByUserID = u_admin.UserID
                              WHERE lr.LeaveRequestID = :leave_request_id");
            $this->db->bind(':leave_request_id', $leaveRequestId);
            return $this->db->single();
        } catch (PDOException $e) {
            error_log("Error in LeaveRequestModel::getLeaveRequestById: " . $e->getMessage());
        }
        return false;
    }

    public function getLeaveRequestsByDoctorId($doctorId, $status = null) {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0) return [];
        $sqlParams = [':doctor_id' => $doctorId];
        $sql = "SELECT lr.* 
                FROM leaverequests lr
                WHERE lr.DoctorID = :doctor_id";
        if (!empty($status) && in_array($status, ['Pending', 'Approved', 'Rejected', 'Cancelled'])) {
            $sql .= " AND lr.Status = :status";
            $sqlParams[':status'] = $status;
        }
        $sql .= " ORDER BY lr.RequestedAt DESC";
        try {
            $this->db->query($sql);
            foreach ($sqlParams as $param => $value) {
                $this->db->bind($param, $value);
            }
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error in LeaveRequestModel::getLeaveRequestsByDoctorId: " . $e->getMessage());
        }
        return [];
    }

    public function getAllLeaveRequests(array $filters = []) {
        $sqlParams = [];
        $sql = "SELECT lr.*, u_doc.FullName AS DoctorName, s.Name AS SpecializationName
                FROM leaverequests lr
                JOIN doctors d ON lr.DoctorID = d.DoctorID
                JOIN users u_doc ON d.UserID = u_doc.UserID
                LEFT JOIN specializations s ON d.SpecializationID = s.SpecializationID
                WHERE 1=1";

        if (!empty($filters['status']) && $filters['status'] !== 'All' && in_array($filters['status'], ['Pending', 'Approved', 'Rejected', 'Cancelled'])) {
            $sql .= " AND lr.Status = :status";
            $sqlParams[':status'] = $filters['status'];
        }
        if (!empty($filters['doctor_id']) && filter_var($filters['doctor_id'], FILTER_VALIDATE_INT)) {
            $sql .= " AND lr.DoctorID = :doctor_id";
            $sqlParams[':doctor_id'] = $filters['doctor_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND lr.EndDate >= :date_from_filter"; 
            $sqlParams[':date_from_filter'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND lr.StartDate <= :date_to_filter";
            $sqlParams[':date_to_filter'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY lr.RequestedAt DESC";
        try {
            $this->db->query($sql);
            foreach ($sqlParams as $param => $value) {
                $this->db->bind($param, $value);
            }
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error in LeaveRequestModel::getAllLeaveRequests: " . $e->getMessage());
        }
        return [];
    }

    public function updateLeaveRequestStatus($leaveRequestId, $status, $adminNotes = null, $adminUserId = null) {
        if (!filter_var($leaveRequestId, FILTER_VALIDATE_INT) || $leaveRequestId <= 0 || 
            !in_array($status, ['Approved', 'Rejected']) ||
            ($adminUserId !== null && (!filter_var($adminUserId, FILTER_VALIDATE_INT) || $adminUserId <= 0))) {
            error_log("LeaveRequestModel::updateLeaveRequestStatus - Invalid parameters for admin review.");
            return false;
        }
        try {
            $this->db->query("UPDATE leaverequests 
                              SET Status = :status, AdminNotes = :admin_notes, 
                                  ReviewedByUserID = :reviewed_by_user_id, ReviewedAt = NOW(), UpdatedAt = NOW()
                              WHERE LeaveRequestID = :leave_request_id AND Status = 'Pending'");
            $this->db->bind(':status', $status);
            $this->db->bind(':admin_notes', $adminNotes);
            $this->db->bind(':reviewed_by_user_id', $adminUserId);
            $this->db->bind(':leave_request_id', $leaveRequestId);
            
            if ($this->db->execute()) {
                return $this->db->rowCount() > 0;
            }
        } catch (PDOException $e) {
            error_log("Error in LeaveRequestModel::updateLeaveRequestStatus: " . $e->getMessage());
        }
        return false;
    }

    public function cancelLeaveRequestByDoctor($leaveRequestId, $doctorId) {
        if (!filter_var($leaveRequestId, FILTER_VALIDATE_INT) || $leaveRequestId <= 0 ||
            !filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0) {
            return false;
        }
        try {
            $this->db->query("UPDATE leaverequests 
                              SET Status = 'Cancelled', UpdatedAt = NOW(), ReviewedByUserID = NULL, ReviewedAt = NULL, AdminNotes = NULL
                              WHERE LeaveRequestID = :leave_request_id 
                                AND DoctorID = :doctor_id 
                                AND Status = 'Pending'");
            $this->db->bind(':leave_request_id', $leaveRequestId);
            $this->db->bind(':doctor_id', $doctorId);
            
            if ($this->db->execute()) {
                return $this->db->rowCount() > 0;
            }
        } catch (PDOException $e) {
            error_log("Error in LeaveRequestModel::cancelLeaveRequestByDoctor: " . $e->getMessage());
        }
        return false;
    }

    public function getOverlappingAvailability($doctorId, $startDate, $endDate) {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0 ||
            !DateTime::createFromFormat('Y-m-d', $startDate) ||
            !DateTime::createFromFormat('Y-m-d', $endDate) ||
            strtotime($endDate) < strtotime($startDate)) {
            return [];
        }
        try {
            $this->db->query("SELECT AvailabilityID, AvailableDate, StartTime, EndTime, SlotType
                              FROM doctoravailability
                              WHERE DoctorID = :doctor_id
                                AND AvailableDate BETWEEN :start_date AND :end_date
                                AND IsBooked = FALSE 
                                AND SlotType = 'Working'");
            $this->db->bind(':doctor_id', $doctorId);
            $this->db->bind(':start_date', $startDate);
            $this->db->bind(':end_date', $endDate);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error in LeaveRequestModel::getOverlappingAvailability: " . $e->getMessage());
        }
        return [];
    }

    public function getOverlappingAppointments($doctorId, $startDate, $endDate, array $statuses = ['Scheduled', 'Confirmed']) {
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
        $sql = "SELECT a.AppointmentID, a.AppointmentDateTime, a.Status, u.FullName AS PatientName
                FROM appointments a
                JOIN patients p ON a.PatientID = p.PatientID
                JOIN users u ON p.UserID = u.UserID
                WHERE a.DoctorID = :doctor_id
                  AND DATE(a.AppointmentDateTime) BETWEEN :start_date AND :end_date";
        
        $statusInClausePlaceholders = [];
        foreach ($statuses as $key => $status) {
            $placeholder = ":statusOverlap{$key}";
            $statusInClausePlaceholders[] = $placeholder;
            $sqlParams[$placeholder] = $status;
        }
        $sql .= " AND a.Status IN (" . implode(',', $statusInClausePlaceholders) . ")";
        $sql .= " ORDER BY a.AppointmentDateTime ASC";

        try {
            $this->db->query($sql);
            foreach ($sqlParams as $param => $value) {
                $this->db->bind($param, $value);
            }
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error in LeaveRequestModel::getOverlappingAppointments: " . $e->getMessage());
        }
        return [];
    }
}
?>