<?php
// app/models/VitalSignModel.php
class VitalSignModel {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createOrUpdateVitalSigns($data) {
        // Kiểm tra xem đã có vital signs cho AppointmentID này chưa
        // Giả sử AppointmentID là UNIQUE hoặc PRIMARY KEY cho việc xác định update/insert
        // Nếu AppointmentID có thể NULL và không UNIQUE, logic này cần điều chỉnh
        $this->db->query("SELECT VitalSignID FROM VitalSigns WHERE AppointmentID = :AppointmentID");
        $this->db->bind(':AppointmentID', $data['AppointmentID']); // Quan trọng: Đảm bảo $data['AppointmentID'] có giá trị hợp lệ
        $existing = $this->db->single();

        if ($existing) { // Cập nhật nếu đã có
            $sql = "UPDATE VitalSigns SET PatientID = :PatientID, RecordedByUserID = :RecordedByUserID,
                    HeartRate = :HeartRate, Temperature = :Temperature,
                    BloodPressureSystolic = :BloodPressureSystolic, BloodPressureDiastolic = :BloodPressureDiastolic,
                    RespiratoryRate = :RespiratoryRate, Weight = :Weight, Height = :Height,
                    OxygenSaturation = :OxygenSaturation, Notes = :Notes, RecordedAt = CURRENT_TIMESTAMP
                    WHERE VitalSignID = :VitalSignID"; // Nên UPDATE bằng VitalSignID nếu đã lấy được
            // HOẶC WHERE AppointmentID = :AppointmentID nếu AppointmentID là UNIQUE NOT NULL
        } else { // Tạo mới nếu chưa có
            $sql = "INSERT INTO VitalSigns (AppointmentID, PatientID, RecordedByUserID, HeartRate, Temperature,
                    BloodPressureSystolic, BloodPressureDiastolic, RespiratoryRate, Weight, Height,
                    OxygenSaturation, Notes)
                    VALUES (:AppointmentID, :PatientID, :RecordedByUserID, :HeartRate, :Temperature,
                    :BloodPressureSystolic, :BloodPressureDiastolic, :RespiratoryRate, :Weight, :Height,
                    :OxygenSaturation, :Notes)";
        }

        $this->db->query($sql);

        if ($existing) {
            $this->db->bind(':VitalSignID', $existing['VitalSignID']); // Bind cho UPDATE
        }
        // Bind các giá trị chung cho cả INSERT và UPDATE (trừ VitalSignID cho INSERT)
        $this->db->bind(':AppointmentID', $data['AppointmentID'] ?? null); // Cho phép AppointmentID là null nếu CSDL cho phép
        $this->db->bind(':PatientID', $data['PatientID']); // Giả sử PatientID luôn NOT NULL
        $this->db->bind(':RecordedByUserID', $data['RecordedByUserID'] ?? null); // Cho phép RecordedByUserID là null (dù trong logic này nó sẽ là user hiện tại)

        $this->db->bind(':HeartRate', $data['HeartRate'] ?? null, ($data['HeartRate'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT));
        $this->db->bind(':Temperature', $data['Temperature'] ?? null); // PDO tự xử lý NULL cho DECIMAL/VARCHAR
        $this->db->bind(':BloodPressureSystolic', $data['BloodPressureSystolic'] ?? null, ($data['BloodPressureSystolic'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT));
        $this->db->bind(':BloodPressureDiastolic', $data['BloodPressureDiastolic'] ?? null, ($data['BloodPressureDiastolic'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT));
        $this->db->bind(':RespiratoryRate', $data['RespiratoryRate'] ?? null, ($data['RespiratoryRate'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT));
        $this->db->bind(':Weight', $data['Weight'] ?? null);
        $this->db->bind(':Height', $data['Height'] ?? null);
        $this->db->bind(':OxygenSaturation', $data['OxygenSaturation'] ?? null, ($data['OxygenSaturation'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT));
        $this->db->bind(':Notes', $data['Notes'] ?? null);

        return $this->db->execute();
    }

    public function getVitalSignsByAppointmentId($appointmentId) {
        // Câu query này vẫn ổn
        $this->db->query("SELECT vs.*, u.FullName as RecordedByUserName
                          FROM VitalSigns vs
                          LEFT JOIN Users u ON vs.RecordedByUserID = u.UserID
                          WHERE vs.AppointmentID = :appointment_id");
        $this->db->bind(':appointment_id', $appointmentId);
        return $this->db->single();
    }
}
?>