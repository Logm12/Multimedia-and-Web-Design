<?php
// app/controllers/MedicalRecordController.php

class MedicalRecordController {
    private $appointmentModel;
    private $patientModel;
    private $userModel; // Để lấy thông tin chung của patient từ bảng Users
    private $doctorModel; // Để lấy thông tin doctor (nếu cần hiển thị)
    // private $medicalRecordModel; // Sẽ khởi tạo khi cần
    private $medicalRecordModel; // KHAI BÁO THUỘC TÍNH Ở ĐÂY
    private $medicineModel;
    private $prescriptionModel;
    private $db; // Nếu bạn dùng $this->db cho transaction

    public function __construct() {
        $this->appointmentModel = new AppointmentModel();
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
        $this->doctorModel = new DoctorModel();
        $this->medicalRecordModel = new MedicalRecordModel(); // KHỞI TẠO TRONG CONSTRUCTOR
        $this->medicineModel = new MedicineModel(); // Thêm
        $this->prescriptionModel = new PrescriptionModel(); // Thêm
        $this->db = Database::getInstance(); // Hoặc cách bạn lấy instance DB
    }

    // Hàm để load view
    protected function view($view, $data = []) {
        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist.");
        }
    }

    // Các action sẽ được thêm ở đây
    public function viewConsultationDetails($appointmentId = 0) {
    // 1. Xác thực người dùng đã đăng nhập
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Unauthorized access. Please log in.";
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    $currentUserRole = $_SESSION['user_role'];
    $currentUserId = $_SESSION['user_id']; // Đây là UserID từ bảng Users

    $appointmentId = (int)$appointmentId;
    if ($appointmentId <= 0) {
        $_SESSION['error_message'] = "Invalid appointment ID.";
        // Chuyển hướng về trang phù hợp với vai trò
        $redirectUrl = ($currentUserRole === 'Admin') ? '/admin/listAllAppointments' : (($currentUserRole === 'Doctor') ? '/doctor/mySchedule' : '/patient/dashboard');
        header('Location: ' . BASE_URL . $redirectUrl);
        exit();
    }

    // Lấy thông tin cuộc hẹn (bao gồm DoctorID và PatientID)
    // Giả sử getAppointmentById trả về đủ thông tin hoặc bạn có hàm getAppointmentByIdWithDoctorInfo
    $appointment = $this->appointmentModel->getAppointmentByIdWithDoctorInfo($appointmentId);

    if (!$appointment) {
        $_SESSION['error_message'] = "Appointment not found.";
        $redirectUrl = ($currentUserRole === 'Admin') ? '/admin/listAllAppointments' : (($currentUserRole === 'Doctor') ? '/doctor/mySchedule' : '/patient/dashboard');
        header('Location: ' . BASE_URL . $redirectUrl);
        exit();
    }

    // 2. Kiểm tra quyền hạn dựa trên vai trò
    $canView = false;
    $isConsultingDoctor = false; // Cờ để biết người dùng hiện tại có phải là bác sĩ của cuộc hẹn này không

    if ($currentUserRole === 'Admin') {
        $canView = true; // Admin có quyền xem tất cả
    } elseif ($currentUserRole === 'Doctor') {
        // Doctor cần lấy DoctorID của mình để so sánh
        $doctorInfoSession = $this->doctorModel->getDoctorByUserId($currentUserId); // Lấy thông tin Doctor của người đang đăng nhập
        if ($doctorInfoSession && $doctorInfoSession['DoctorID'] == $appointment['DoctorID']) {
            $canView = true;
            $isConsultingDoctor = true; // Đánh dấu đây là bác sĩ của cuộc hẹn
        }
    }
    // Bạn có thể thêm logic cho Patient xem EMR của họ ở đây nếu cần

    if (!$canView) {
        $_SESSION['error_message'] = "You are not authorized to view details for this appointment.";
        $redirectUrl = ($currentUserRole === 'Doctor') ? '/doctor/mySchedule' : '/auth/login'; // Nếu không phải admin hay doctor của hẹn thì về login
        header('Location: ' . BASE_URL . $redirectUrl);
        exit();
    }

    // 3. Lấy thông tin Patient
    $patient = $this->patientModel->getPatientDetailsById($appointment['PatientID']);
    if (!$patient) { /* ... xử lý lỗi ... */ }

    // 4. Lấy thông tin Doctor của cuộc hẹn này (để hiển thị trên trang)
    // $doctorInfoForPage đã được lấy trong $appointment nếu dùng getAppointmentByIdWithDoctorInfo
    // Nếu không, bạn cần lấy riêng: $doctorOfAppointment = $this->doctorModel->getDoctorById($appointment['DoctorID']);
    // Và dùng $doctorOfAppointment['FullName'] để hiển thị "Consulting Doctor"

    // 5. Lấy thông tin Medical Record hiện tại cho cuộc hẹn này
    $medicalRecord = $this->medicalRecordModel->getRecordByAppointmentId($appointmentId);

    // 6. Lấy lịch sử bệnh án của Patient
    $medicalHistory = $this->medicalRecordModel->getMedicalHistoryByPatientId($appointment['PatientID'], $appointmentId);

    // Lấy danh sách thuốc để chọn
    $allMedicines = $this->medicineModel->getAllMedicinesForSelection();

    // Lấy đơn thuốc hiện tại
    $currentPrescriptions = [];
    if ($medicalRecord && isset($medicalRecord['RecordID'])) {
        $currentPrescriptions = $this->prescriptionModel->getPrescriptionsByRecordId($medicalRecord['RecordID']);
    }

    $returnToAppointmentId = isset($_GET['return_to']) ? (int)$_GET['return_to'] : null;

    $data = [
        'title' => 'Consultation Details - ' . htmlspecialchars($patient['FullName']),
        'appointment' => $appointment,
        'patient' => $patient,
        'doctor' => $appointment, // $appointment đã chứa DoctorName, SpecializationName từ getAppointmentByIdWithDoctorInfo
        'medicalRecord' => $medicalRecord,
        'allMedicines' => $allMedicines,
        'currentPrescriptions' => $currentPrescriptions,
        'medicalHistory' => $medicalHistory,
        'returnToAppointmentId' => $returnToAppointmentId,
        'currentUserRole' => $_SESSION['user_role'], // Đảm bảo dòng này có
        'isConsultingDoctor' => $isConsultingDoctor // Truyền cờ này cho view để biết có cho phép sửa không
    ];

// Xử lý việc lưu bản ghi VÀ ĐƠN THUỐC nếu form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_record'])) {

    // KIỂM TRA CSRF TOKEN
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['consultation_message_error'] = 'Invalid or missing CSRF token. Action aborted.';
            // Ghi log chi tiết hơn cho admin/developer
            error_log("CSRF Token Validation Failed for user: " . ($_SESSION['user_id'] ?? 'guest') . " from IP: " . $_SERVER['REMOTE_ADDR']);
            header('Location: ' . BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appointmentId); // Redirect lại
            exit();
        }
        // Nếu token hợp lệ, tiếp tục xử lý
        // ... (code xử lý lưu EMR và prescriptions) ...
         // CHỈ DOCTOR PHỤ TRÁCH MỚI ĐƯỢC LƯU
        if (!$isConsultingDoctor) {
            $_SESSION['consultation_message_error'] = 'Only the consulting doctor can save or modify this medical record.';
            header('Location: ' . BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appointmentId);
            exit();
        }

    $symptoms = trim($_POST['symptoms'] ?? null);
    $diagnosis = trim($_POST['diagnosis'] ?? null);
    $treatmentPlan = trim($_POST['treatment_plan'] ?? null);
    $notes = trim($_POST['consultation_notes'] ?? null);
    $visitDate = $appointment['AppointmentDateTime'];

    $existingRecordId = $medicalRecord ? $medicalRecord['RecordID'] : null;

    // Sử dụng transaction vì có thể lưu EMR và Prescriptions
    $db = Database::getInstance(); // Hoặc $this->db nếu đã có trong controller
    $db->beginTransaction();

    try {
        $savedRecordId = $this->medicalRecordModel->saveMedicalRecord(
            $appointmentId,
            $appointment['PatientID'],
            $appointment['DoctorID'], // Sử dụng DoctorID của cuộc hẹn
            $appointment['AppointmentDateTime'], // Sử dụng VisitDate từ cuộc hẹn
            $symptoms,
            $diagnosis,
            $treatmentPlan,
            $notes,
            $existingRecordId
        );

        if ($savedRecordId) {
            // Xử lý lưu đơn thuốc
            // Trước tiên, xóa các thuốc cũ trong đơn (cách đơn giản để cập nhật)
            if ($existingRecordId) { // Chỉ xóa nếu đang update record cũ
                $this->prescriptionModel->deletePrescriptionsByRecordId($savedRecordId);
            }

            // Lặp qua các thuốc được gửi từ form và thêm vào đơn
            if (isset($_POST['prescriptions']) && is_array($_POST['prescriptions'])) {
                foreach ($_POST['prescriptions'] as $prescriptionItem) {
                    if (!empty($prescriptionItem['medicine_id']) && !empty($prescriptionItem['dosage'])) {
                        $this->prescriptionModel->addMedicineToPrescription(
                            $savedRecordId,
                            (int)$prescriptionItem['medicine_id'],
                            trim($prescriptionItem['dosage']),
                            trim($prescriptionItem['frequency'] ?? ''),
                            trim($prescriptionItem['duration'] ?? ''),
                            trim($prescriptionItem['instructions'] ?? null)
                        );
                    }
                }
            }
            $db->commit();
            $_SESSION['consultation_message_success'] = 'Medical record and prescription saved successfully.';
            header('Location: ' . BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appointmentId);
            exit();
        } else {
            $db->rollBack();
            $data['consultation_message_error'] = 'Failed to save medical record. Please try again.';
            // Giữ lại input (bạn đã có logic này)
        }
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Error saving medical record/prescription: " . $e->getMessage());
        $data['consultation_message_error'] = 'An error occurred: ' . $e->getMessage();
        // Giữ lại input
    }
}

$this->view('medical_record/consultation_details', $data);
}
}
?>