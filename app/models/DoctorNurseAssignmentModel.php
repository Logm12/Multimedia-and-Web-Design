<?php
// app/models/DoctorNurseAssignmentModel.php

class DoctorNurseAssignmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy danh sách các DoctorID mà một NurseID cụ thể được phân công.
     * @param int $nurseId ID của Nurse (từ bảng nurses.NurseID)
     * @return array Mảng các DoctorID. Trả về mảng rỗng nếu không có hoặc nurseId không hợp lệ.
     */
    public function getAssignedDoctorIdsByNurseId($nurseId) {
        if (!filter_var($nurseId, FILTER_VALIDATE_INT) || $nurseId <= 0) {
            error_log("DoctorNurseAssignmentModel: Invalid NurseID provided: " . $nurseId);
            return [];
        }

        try {
            $this->db->query("SELECT DISTINCT DoctorID FROM doctornurseassignments WHERE NurseID = :nurse_id");
            $this->db->bind(':nurse_id', $nurseId);
            
            $results = $this->db->resultSet();
            
            $doctorIds = [];
            if ($results) {
                foreach ($results as $row) {
                    $doctorIds[] = (int)$row['DoctorID']; // Đảm bảo là kiểu int
                }
            }
            return $doctorIds;
        } catch (PDOException $e) {
            error_log("Error in getAssignedDoctorIdsByNurseId: " . $e->getMessage());
            // Trong môi trường production, không nên echo lỗi trực tiếp
            // Có thể throw exception hoặc trả về mảng rỗng và log lỗi
            return [];
        }
    }

    // Cậu có thể thêm các hàm khác ở đây nếu cần trong tương lai, ví dụ:
    // public function assignNurseToDoctor($nurseId, $doctorId) { /* ... */ }
    // public function removeAssignment($assignmentId) { /* ... */ }
    // public function getAssignmentsByDoctorId($doctorId) { /* ... */ }
}
?>