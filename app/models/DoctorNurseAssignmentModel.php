<?php
// app/models/DoctorNurseAssignmentModel.php

class DoctorNurseAssignmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAssignedDoctorIdsByNurseId($nurseId) {
        if (!filter_var($nurseId, FILTER_VALIDATE_INT) || $nurseId <= 0) {
            error_log("DoctorNurseAssignmentModel: Invalid NurseID provided to getAssignedDoctorIdsByNurseId: " . print_r($nurseId, true));
            return [];
        }

        try {
            $this->db->query("SELECT DISTINCT dna.DoctorID 
                            FROM doctornurseassignments dna
                            JOIN doctors d ON dna.DoctorID = d.DoctorID
                            JOIN users u_doc ON d.UserID = u_doc.UserID
                            WHERE dna.NurseID = :nurse_id AND u_doc.Status = 'Active'"); // Chỉ lấy bác sĩ active
            $this->db->bind(':nurse_id', $nurseId);
            
            $results = $this->db->resultSet();
            
            $doctorIds = [];
            if ($results) {
                foreach ($results as $row) {
                    $doctorIds[] = (int)$row['DoctorID']; 
                }
            }

            return $doctorIds;
        } catch (PDOException $e) {
            error_log("Error in DoctorNurseAssignmentModel::getAssignedDoctorIdsByNurseId for NurseID {$nurseId}: " . $e->getMessage());
            return [];
        }
    }

    public function getAssignedNursesByDoctorId($doctorId) {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0) {
            error_log("DoctorNurseAssignmentModel: Invalid DoctorID provided to getAssignedNursesByDoctorId: " . print_r($doctorId, true));
            return [];
        }
        try {
            $this->db->query("SELECT n.NurseID, u.UserID, u.FullName, u.Email, dna.AssignmentID
                            FROM doctornurseassignments dna
                            JOIN nurses n ON dna.NurseID = n.NurseID
                            JOIN users u ON n.UserID = u.UserID
                            WHERE dna.DoctorID = :doctor_id AND u.Status = 'Active' 
                            ORDER BY u.FullName ASC"); 
            $this->db->bind(':doctor_id', $doctorId);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error in DoctorNurseAssignmentModel::getAssignedNursesByDoctorId for DoctorID {$doctorId}: " . $e->getMessage());
            return [];
        }
    }

    // <<<< "PHÉP THUẬT" MỚI ĐỂ "SE DUYÊN" >>>>
    public function assignNurseToDoctor($doctorId, $nurseId) {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0 || 
            !filter_var($nurseId, FILTER_VALIDATE_INT) || $nurseId <= 0) {
            error_log("DoctorNurseAssignmentModel: Invalid DoctorID or NurseID for assignment. DoctorID: {$doctorId}, NurseID: {$nurseId}");
            return ['success' => false, 'message' => 'Invalid Doctor or Nurse ID.'];
        }

        $this->db->query("SELECT AssignmentID FROM doctornurseassignments WHERE DoctorID = :doctor_id AND NurseID = :nurse_id");
        $this->db->bind(':doctor_id', $doctorId);
        $this->db->bind(':nurse_id', $nurseId);
        if ($this->db->single()) {
            return ['success' => false, 'message' => 'This nurse is already assigned to this doctor!'];
        }

        try {
            $this->db->query("INSERT INTO doctornurseassignments (DoctorID, NurseID, AssignedAt) VALUES (:doctor_id, :nurse_id, NOW())");
            $this->db->bind(':doctor_id', $doctorId);
            $this->db->bind(':nurse_id', $nurseId);
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Nurse assigned successfully!'];
            }
            return ['success' => false, 'message' => 'Failed to assign nurse. Database error.'];
        } catch (PDOException $e) {
            error_log("Error in DoctorNurseAssignmentModel::assignNurseToDoctor (D:{$doctorId}, N:{$nurseId}): " . $e->getMessage());
            if ($e->getCode() == '23000') { 
                 return ['success' => false, 'message' => 'This assignment might already exist or conflict with other rules.'];
            }
            return ['success' => false, 'message' => 'An unexpected error occurred during assignment.'];
        }
    }

    public function unassignNurseFromDoctor($doctorId, $nurseId) {
         if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0 || 
            !filter_var($nurseId, FILTER_VALIDATE_INT) || $nurseId <= 0) {
            error_log("DoctorNurseAssignmentModel: Invalid DoctorID or NurseID for unassignment. DoctorID: {$doctorId}, NurseID: {$nurseId}");
            return ['success' => false, 'message' => 'Invalid Doctor or Nurse ID for unassignment.'];
        }
        try {
            $this->db->query("DELETE FROM doctornurseassignments WHERE DoctorID = :doctor_id AND NurseID = :nurse_id");
            $this->db->bind(':doctor_id', $doctorId);
            $this->db->bind(':nurse_id', $nurseId);
            if ($this->db->execute()) {
                if ($this->db->rowCount() > 0) {
                    return ['success' => true, 'message' => 'Nurse unassigned successfully'];
                }
                return ['success' => false, 'message' => 'Assignment not found or already removed.'];
            }
            return ['success' => false, 'message' => 'Failed to unassign nurse. Database error.'];
        } catch (PDOException $e) {
            error_log("Error in DoctorNurseAssignmentModel::unassignNurseFromDoctor (D:{$doctorId}, N:{$nurseId}): " . $e->getMessage());
            return ['success' => false, 'message' => 'An unexpected error occurred during unassignment.'];
        }
    }
    

    public function getUnassignedNursesForDoctor($doctorId, $allNurses) {
        if (!filter_var($doctorId, FILTER_VALIDATE_INT) || $doctorId <= 0) {
            return $allNurses; 
        }
        $assignedNursesRaw = $this->getAssignedNursesByDoctorId($doctorId);
        $assignedNurseIds = array_map(function($nurse) {
            return $nurse['NurseID'];
        }, $assignedNursesRaw);

        $unassignedNurses = [];
        foreach ($allNurses as $nurse) {
            if (!in_array($nurse['NurseID'], $assignedNurseIds)) {
                $unassignedNurses[] = $nurse;
            }
        }
        return $unassignedNurses;
    }
}
?>