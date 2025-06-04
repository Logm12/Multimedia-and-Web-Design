<?php
// app/models/MedicalRecordModel.php

class MedicalRecordModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy bản ghi y tế (nếu có) dựa trên AppointmentID
     * @param int $appointmentId
     * @return array|false
     */
    public function getRecordByAppointmentId($appointmentId) {
        $this->db->query("SELECT * FROM MedicalRecords WHERE AppointmentID = :appointment_id");
        $this->db->bind(':appointment_id', $appointmentId);
        return $this->db->single();
    }

    /**
 * Tạo hoặc cập nhật một bản ghi y tế (EMR) cho một cuộc hẹn
 * @param int $appointmentId
 * @param int $patientId
 * @param int $doctorId
 * @param string $visitDate
 * @param string|null $symptoms
 * @param string|null $diagnosis
 * @param string|null $treatmentPlan
 * @param string|null $notes
 * @param int|null $existingRecordId ID của record hiện tại nếu đang cập nhật
 * @return bool|int RecordID nếu thành công, false nếu thất bại
 */
public function saveMedicalRecord(
    $appointmentId,
    $patientId,
    $doctorId,
    $visitDate,
    $symptoms = null,
    $diagnosis = null,
    $treatmentPlan = null,
    $notes = null,
    $existingRecordId = null
) {
    if ($existingRecordId) {
        // Cập nhật record hiện có
        $this->db->query("UPDATE MedicalRecords
                          SET Symptoms = :symptoms,
                              Diagnosis = :diagnosis,
                              TreatmentPlan = :treatment_plan,
                              Notes = :notes,
                              UpdatedAt = CURRENT_TIMESTAMP
                          WHERE RecordID = :record_id AND AppointmentID = :appointment_id"); // Thêm AND AppointmentID để an toàn
        $this->db->bind(':record_id', $existingRecordId);
    } else {
        // Tạo record mới
        $this->db->query("INSERT INTO MedicalRecords
                              (AppointmentID, PatientID, DoctorID, VisitDate, Symptoms, Diagnosis, TreatmentPlan, Notes)
                          VALUES
                              (:appointment_id, :patient_id, :doctor_id, :visit_date, :symptoms, :diagnosis, :treatment_plan, :notes)");
    }

    $this->db->bind(':appointment_id', $appointmentId);
    $this->db->bind(':symptoms', $symptoms);
    $this->db->bind(':diagnosis', $diagnosis);
    $this->db->bind(':treatment_plan', $treatmentPlan);
    $this->db->bind(':notes', $notes);

    // Chỉ bind những cái này nếu là INSERT
    if (!$existingRecordId) {
        $this->db->bind(':patient_id', $patientId);
        $this->db->bind(':doctor_id', $doctorId);
        $this->db->bind(':visit_date', $visitDate);
    }

    if ($this->db->execute()) {
        return $existingRecordId ? $existingRecordId : $this->db->lastInsertId();
    }
    return false;
}
    // Các phương thức khác sẽ được thêm sau (ví dụ: thêm triệu chứng, chẩn đoán, đơn thuốc)
    /**
 * Lấy lịch sử bệnh án của một bệnh nhân
 * @param int $patientId
 * @param int|null $currentAppointmentId (Tùy chọn) ID của cuộc hẹn hiện tại để có thể loại trừ hoặc đánh dấu
 * @return array Danh sách các bản ghi y tế
 */
public function getMedicalHistoryByPatientId($patientId, $currentAppointmentId = null) {
    $sql = "SELECT
                mr.RecordID,
                mr.AppointmentID,
                mr.VisitDate,
                mr.Symptoms, -- Có thể chỉ lấy một phần tóm tắt nếu quá dài
                mr.Diagnosis, -- Chẩn đoán chính
                u_doc.FullName AS DoctorName -- Tên bác sĩ của lần khám đó
            FROM MedicalRecords mr
            JOIN Appointments a ON mr.AppointmentID = a.AppointmentID -- Lấy thông tin từ Appointments nếu cần
            JOIN Doctors d ON mr.DoctorID = d.DoctorID -- Lấy thông tin Doctor từ MedicalRecord
            JOIN Users u_doc ON d.UserID = u_doc.UserID
            WHERE mr.PatientID = :patient_id";

    if ($currentAppointmentId !== null) {
        // Tùy chọn: Loại trừ bản ghi của cuộc hẹn hiện tại khỏi lịch sử nếu không muốn hiển thị nó 2 lần
        // $sql .= " AND mr.AppointmentID != :current_appointment_id";
    }

    $sql .= " ORDER BY mr.VisitDate DESC"; // Sắp xếp lần khám mới nhất lên đầu

    $this->db->query($sql);
    $this->db->bind(':patient_id', $patientId);

    if ($currentAppointmentId !== null) {
        // $this->db->bind(':current_appointment_id', $currentAppointmentId);
    }

    return $this->db->resultSet();
}
}
?>