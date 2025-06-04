<?php
// app/models/ReportModel.php

class ReportModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Đếm số lượng bệnh nhân mới trong một khoảng thời gian.
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @return int
     */
    public function getNewPatientsCount($startDate, $endDate) {
        $sql = "SELECT COUNT(UserID) as count
                FROM Users
                WHERE Role = 'Patient'
                AND CreatedAt >= :start_date AND CreatedAt < DATE_ADD(:end_date, INTERVAL 1 DAY)"; // Để bao gồm cả ngày endDate
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        $row = $this->db->single();
        return $row ? (int)$row['count'] : 0;
    }

    /**
     * Đếm số lượt khám đã hoàn thành trong một khoảng thời gian.
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @return int
     */
    public function getCompletedAppointmentsCount($startDate, $endDate) {
        $sql = "SELECT COUNT(AppointmentID) as count
                FROM Appointments
                WHERE Status = 'Completed'
                AND DATE(AppointmentDateTime) >= :start_date AND DATE(AppointmentDateTime) <= :end_date";
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        $row = $this->db->single();
        return $row ? (int)$row['count'] : 0;
    }

    /**
     * Thống kê lịch hẹn theo trạng thái trong một khoảng thời gian (dựa trên ngày tạo lịch).
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @return array
     */
    public function getAppointmentCountsByStatus($startDate, $endDate) {
        $sql = "SELECT Status, COUNT(AppointmentID) as count
                FROM Appointments
                WHERE CreatedAt >= :start_date AND CreatedAt < DATE_ADD(:end_date, INTERVAL 1 DAY)
                GROUP BY Status";
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        return $this->db->resultSet();
    }

    /**
     * Thống kê số lượt khám hoàn thành theo bác sĩ.
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @param int|null $specializationId
     * @return array
     */
    public function getCompletedAppointmentsByDoctor($startDate, $endDate, $specializationId = null) {
        $sql = "SELECT U.FullName as doctor_name, COUNT(A.AppointmentID) as completed_count
                FROM Appointments A
                JOIN Doctors DocTable ON A.DoctorID = DocTable.DoctorID
                JOIN Users U ON DocTable.UserID = U.UserID";
        $params = [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($specializationId) {
            $sql .= " JOIN Specializations S ON DocTable.SpecializationID = S.SpecializationID
                      WHERE A.Status = 'Completed'
                      AND DATE(A.AppointmentDateTime) >= :start_date AND DATE(A.AppointmentDateTime) <= :end_date
                      AND S.SpecializationID = :specialization_id";
            $params[':specialization_id'] = $specializationId;
        } else {
            $sql .= " WHERE A.Status = 'Completed'
                      AND DATE(A.AppointmentDateTime) >= :start_date AND DATE(A.AppointmentDateTime) <= :end_date";
        }

        $sql .= " GROUP BY A.DoctorID, U.FullName
                  ORDER BY completed_count DESC";

        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->resultSet();
    }

    /**
     * Thống kê số lượt khám hoàn thành theo chuyên khoa.
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @return array
     */
    public function getCompletedAppointmentsBySpecialization($startDate, $endDate) {
        $sql = "SELECT S.Name as specialization_name, COUNT(A.AppointmentID) as completed_count
                FROM Appointments A
                JOIN Doctors DocTable ON A.DoctorID = DocTable.DoctorID
                JOIN Specializations S ON DocTable.SpecializationID = S.SpecializationID
                WHERE A.Status = 'Completed'
                AND DATE(A.AppointmentDateTime) >= :start_date AND DATE(A.AppointmentDateTime) <= :end_date
                GROUP BY S.SpecializationID, S.Name
                ORDER BY completed_count DESC";
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        return $this->db->resultSet();
    }

     /**
     * Lấy dữ liệu lượt khám hoàn thành theo ngày để vẽ biểu đồ xu hướng.
     * @param string $startDate 'YYYY-MM-DD'
     * @param string $endDate 'YYYY-MM-DD'
     * @return array
     */
    public function getCompletedAppointmentsTrendByDay($startDate, $endDate) {
        $sql = "SELECT DATE(AppointmentDateTime) as visit_date, COUNT(AppointmentID) as completed_count
                FROM Appointments
                WHERE Status = 'Completed'
                AND DATE(AppointmentDateTime) >= :start_date AND DATE(AppointmentDateTime) <= :end_date
                GROUP BY DATE(AppointmentDateTime)
                ORDER BY visit_date ASC";
        $this->db->query($sql);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        return $this->db->resultSet();
    }
}
?>