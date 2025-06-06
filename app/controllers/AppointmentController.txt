<?php
// app/controllers/AppointmentController.php

class AppointmentController {
    private $appointmentModel;
    private $doctorAvailabilityModel;
    private $db; // Để quản lý transaction

    public function __construct() {
        $this->appointmentModel = new AppointmentModel();
        $this->doctorAvailabilityModel = new DoctorAvailabilityModel();
        $this->db = Database::getInstance(); // Lấy instance Database
    }

    // Hàm để load view (nếu cần cho trang danh sách lịch hẹn sau này)
    // protected function view($view, $data = []) { ... }

    /**
     * Xử lý AJAX POST request để đặt lịch hẹn
     */
    public function bookSlot() {
        header('Content-Type: application/json'); // Luôn trả về JSON

        // Kiểm tra đăng nhập và vai trò Patient
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'message' => 'Authentication required. Please log in as a patient.']);
            exit;
        }

        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        // Lấy dữ liệu JSON từ body của request
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, TRUE); // TRUE để trả về mảng kết hợp

        // Validate dữ liệu đầu vào
        if (empty($input['availability_id']) || empty($input['doctor_id'])) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Missing required fields (availability_id, doctor_id).']);
            exit;
        }

        $availabilityId = filter_var($input['availability_id'], FILTER_VALIDATE_INT);
        $doctorId = filter_var($input['doctor_id'], FILTER_VALIDATE_INT);
        $reasonForVisit = isset($input['reason_for_visit']) ? trim(htmlspecialchars($input['reason_for_visit'])) : null;
        $patientId = $_SESSION['user_id']; // Lấy PatientID từ session

        if ($availabilityId === false || $availabilityId <= 0 || $doctorId === false || $doctorId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid availability ID or doctor ID.']);
            exit;
        }

        // Bắt đầu Transaction
        $this->db->beginTransaction();

        try {
            // 1. Lấy thông tin slot để đảm bảo nó tồn tại, còn trống và lấy thời gian
            $slotDetails = $this->doctorAvailabilityModel->getSlotById($availabilityId);

            if (!$slotDetails) {
                throw new Exception("Selected slot not found.");
            }
            if ($slotDetails['IsBooked']) {
                throw new Exception("Selected slot is no longer available.");
            }
            if ($slotDetails['DoctorID'] != $doctorId) { // Kiểm tra doctorId có khớp không (thêm an toàn)
                throw new Exception("Doctor ID mismatch for the selected slot.");
            }

            // Tạo AppointmentDateTime
            $appointmentDateTime = $slotDetails['AvailableDate'] . ' ' . $slotDetails['StartTime'];

            // 2. Tạo lịch hẹn
            $newAppointmentId = $this->appointmentModel->createAppointment(
                $patientId,
                $doctorId,
                $availabilityId,
                $appointmentDateTime,
                $reasonForVisit
            );

            if (!$newAppointmentId) {
                throw new Exception("Failed to create appointment record.");
            }

            // 3. Đánh dấu slot là đã được đặt
            // (Hàm markSlotAsBooked đã có kiểm tra IsBooked = FALSE trong SQL)
            if (!$this->doctorAvailabilityModel->markSlotAsBooked($availabilityId, $newAppointmentId)) {
                 // Nếu không update được (ví dụ slot đã bị ai đó đặt ngay trước đó), rollback
                throw new Exception("Failed to mark slot as booked. It might have been booked by someone else.");
            }

            // Nếu tất cả thành công, commit transaction
            $this->db->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Appointment booked successfully!',
                'appointment_id' => $newAppointmentId
            ]);
            exit;

        } catch (Exception $e) {
            // Nếu có lỗi, rollback transaction
            $this->db->rollBack();
            http_response_code(500); // Internal Server Error hoặc 409 Conflict nếu lỗi do slot đã đặt
            if ($e->getMessage() == "Selected slot is no longer available." || $e->getMessage() == "Failed to mark slot as booked. It might have been booked by someone else.") {
                http_response_code(409); // Conflict
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
    // Hàm để load view (bạn có thể đặt hàm này trong một BaseController sau này)
protected function view($view, $data = []) {
    if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
        require_once __DIR__ . '/../views/' . $view . '.php';
    } else {
        die("View '{$view}' does not exist.");
    }
}
    // Thêm action để Patient xem lịch hẹn của mình
   // Thêm action để Patient xem lịch hẹn của mình (ĐÃ CẬP NHẬT)
public function myAppointments() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
        // Thay vì $_SESSION['error_message'], chúng ta có thể dùng flash message nếu bạn có hệ thống đó,
        // hoặc đơn giản là chuyển hướng.
        header('Location: ' . BASE_URL . '/auth/login?redirect_message=Please log in to view your appointments.');
        exit();
    }

    // Giả sử UserID trong session chính là PatientID trong bảng Appointments
    // Nếu không, bạn cần logic để lấy PatientID từ UserID
    // Ví dụ:
    // $patientModel = new PatientModel(); // Cần khởi tạo
    // $patientDetails = $patientModel->getPatientByUserId($_SESSION['user_id']);
    // if (!$patientDetails) { /* Xử lý lỗi không tìm thấy patient */ }
    // $patientId = $patientDetails->PatientID;
    $patientId = $_SESSION['user_id']; // CẦN XEM XÉT LẠI LOGIC NÀY CHO CHÍNH XÁC

    // Lấy bộ lọc trạng thái từ GET request
    $statusFilter = $_GET['status'] ?? 'All';
    $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
    if (!in_array($statusFilter, $validStatuses)) {
        $statusFilter = 'All';
    }

    $appointments = $this->appointmentModel->getAppointmentsByPatientId($patientId, $statusFilter);

    $data = [
        'title' => 'My Appointments',
        'appointments' => $appointments,
        'currentFilter' => $statusFilter,
        'allStatuses' => $validStatuses // Để tạo select filter trên view
    ];

    // Load view hiển thị danh sách lịch hẹn
    $this->view('patient/my_appointments', $data); // Đường dẫn này phụ thuộc vào cấu trúc view của bạn
                                                  // Nếu view my_appointments.php nằm trong app/views/patient/
                                                  // thì sẽ là 'patient/my_appointments'
}
public function cancelByPatient() {
    // 1. Kiểm tra phương thức Request & Xác thực người dùng
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['appointment_message_error'] = 'Invalid request method.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/patient/myAppointments'));
        exit();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
        $_SESSION['appointment_message_error'] = 'Unauthorized action.';
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    // Lấy PatientID từ session (Đảm bảo logic này chính xác)
    $patientId = $_SESSION['user_id'];
    $appointmentId = $_POST['appointment_id'] ?? null;

    // (THÊM CSRF TOKEN VALIDATION Ở ĐÂY)
    // if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    //     $_SESSION['appointment_message_error'] = 'Invalid CSRF token.';
    //     header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/patient/myAppointments'));
    //     exit();
    // }


    if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0) {
        $_SESSION['appointment_message_error'] = 'Invalid Appointment ID.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/patient/myAppointments'));
        exit();
    }

    // 2. Xử lý logic hủy lịch với Transaction
    // Giả sử $this->db đã được khởi tạo trong __construct() của AppointmentController
    // Nếu chưa, bạn cần: $db = Database::getInstance(); và dùng $db->beginTransaction(); etc.
    $this->db->beginTransaction();
    try {
        // Lấy thông tin chi tiết lịch hẹn để kiểm tra quyền và lấy AvailabilityID
        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsForCancellation((int)$appointmentId, $patientId);

        if (!$appointmentDetails) { // Model trả về false nếu không tìm thấy hoặc không thuộc patient
            throw new Exception('Appointment not found or you do not have permission to cancel it.');
        }

        // SỬA Ở ĐÂY: Dùng cú pháp mảng
        // Kiểm tra trạng thái hiện tại của lịch hẹn
        if (!isset($appointmentDetails['Status']) || !in_array($appointmentDetails['Status'], ['Scheduled', 'Confirmed'])) {
            throw new Exception('This appointment cannot be cancelled (Current status: ' . htmlspecialchars($appointmentDetails['Status'] ?? 'Unknown') . ').');
        }

        // SỬA Ở ĐÂY: Dùng cú pháp mảng
        // Kiểm tra thời gian cho phép hủy (ví dụ: trước 24 giờ)
        if (!isset($appointmentDetails['AppointmentDateTime'])) {
            throw new Exception('Appointment date/time information is missing.');
        }
        $appointmentTime = strtotime($appointmentDetails['AppointmentDateTime']);
        $currentTime = time();
        $cancellationCutoffHours = 24; // Số giờ trước đó được phép hủy
        if (($appointmentTime - $currentTime) <= ($cancellationCutoffHours * 3600)) {
            throw new Exception('Cannot cancel appointment. It is too close to the appointment time (less than '.$cancellationCutoffHours.' hours). Current status: ' . htmlspecialchars($appointmentDetails['Status']));
        }

        // Cập nhật trạng thái lịch hẹn thành 'CancelledByPatient'
        if (!$this->appointmentModel->updateAppointmentStatus((int)$appointmentId, 'CancelledByPatient')) {
            throw new Exception('Failed to update appointment status.');
        }

        // SỬA Ở ĐÂY: Dùng cú pháp mảng
        // Giải phóng slot nếu có AvailabilityID
        if (!empty($appointmentDetails['AvailabilityID'])) { // Dùng !empty để kiểm tra cả null và 0 (nếu 0 là ID không hợp lệ)
            if (!$this->appointmentModel->markSlotAsAvailableAgain($appointmentDetails['AvailabilityID'])) {
                error_log("Failed to mark slot {$appointmentDetails['AvailabilityID']} as available for cancelled appointment {$appointmentId}. This might require manual correction.");
                // Không nhất thiết phải throw Exception ở đây nếu việc hủy lịch vẫn được ưu tiên
            }
        }

        $this->db->commit();
        $_SESSION['appointment_message_success'] = 'Appointment cancelled successfully.';

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Error cancelling appointment by patient (ID: {$appointmentId}): " . $e->getMessage());
        $_SESSION['appointment_message_error'] = 'Error: ' . $e->getMessage();
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/patient/myAppointments'));
    exit();
}
public function markAsCompleted() {
    // 1. Kiểm tra phương thức Request & Xác thực người dùng (phải là Doctor)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $_SESSION['schedule_message_error'] = 'Invalid request method.'; // Sử dụng session key khác cho schedule
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule')); // Quay lại trang trước đó
        exit();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
        $_SESSION['schedule_message_error'] = 'Unauthorized action. Only doctors can mark appointments as completed.';
        header('Location: ' . BASE_URL . '/auth/login'); // Hoặc trang dashboard của doctor nếu đã đăng nhập với role khác
        exit();
    }

    // Lấy DoctorID từ session - Cần đảm bảo bạn có logic này
    // $doctorModel = new DoctorModel(); // Cần khởi tạo nếu chưa có trong __construct của AppointmentController
    // $doctorInfo = $doctorModel->getDoctorByUserId($_SESSION['user_id']);
    // if (!$doctorInfo) {
    //     $_SESSION['schedule_message_error'] = 'Doctor profile not found.';
    //     header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule'));
    //     exit();
    // }
    // $currentDoctorId = $doctorInfo['DoctorID']; // Lấy DoctorID của người dùng hiện tại

    $appointmentId = $_POST['appointment_id'] ?? null;

    // (THÊM CSRF TOKEN VALIDATION Ở ĐÂY - RẤT QUAN TRỌNG)
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['schedule_message_error'] = 'Invalid CSRF token. Action aborted.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule'));
        exit();
    }
    // Xóa token sau khi dùng để tránh replay attacks (tùy theo cách bạn muốn quản lý token)
    // unset($_SESSION['csrf_token']); // Tạo lại ở request tiếp theo


    if (!filter_var($appointmentId, FILTER_VALIDATE_INT) || $appointmentId <= 0) {
        $_SESSION['schedule_message_error'] = 'Invalid Appointment ID.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule'));
        exit();
    }

    // 2. Xử lý logic
    // Không cần transaction phức tạp ở đây nếu chỉ cập nhật một bảng
    // Tuy nhiên, nếu việc "Complete" kích hoạt nhiều hành động khác, transaction sẽ cần thiết.
    try {
        // Lấy thông tin chi tiết lịch hẹn để kiểm tra DoctorID (nếu cần)
        $appointmentDetails = $this->appointmentModel->getAppointmentById((int)$appointmentId);

        if (!$appointmentDetails) {
            throw new Exception('Appointment not found.');
        }

        // Kiểm tra xem Doctor hiện tại có phải là người phụ trách lịch hẹn này không
        // Điều này quan trọng nếu $currentDoctorId đã được lấy chính xác
        // if ($appointmentDetails['DoctorID'] != $currentDoctorId) {
        //     throw new Exception('You are not authorized to modify this appointment.');
        // }

        // Kiểm tra trạng thái hiện tại của lịch hẹn (chỉ cho phép từ Scheduled/Confirmed)
        if (!in_array($appointmentDetails['Status'], ['Scheduled', 'Confirmed'])) {
            throw new Exception('This appointment cannot be marked as completed (current status: ' . $appointmentDetails['Status'] . ').');
        }

        // Cập nhật trạng thái lịch hẹn thành 'Completed'
        if ($this->appointmentModel->updateAppointmentStatus((int)$appointmentId, 'Completed')) {
            $_SESSION['schedule_message_success'] = 'Appointment marked as completed successfully.';
            // Tùy chọn: Ghi log hành động
            // Tùy chọn: Kích hoạt các hành động khác (ví dụ: tạo hóa đơn, gửi feedback request)
        } else {
            throw new Exception('Failed to update appointment status.');
        }

    } catch (Exception $e) {
        error_log("Error marking appointment as completed: " . $e->getMessage());
        $_SESSION['schedule_message_error'] = 'Error: ' . $e->getMessage();
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule')); // Quay lại trang lịch trình
    exit();
}
}
// Kết thúc class AppointmentController
?>