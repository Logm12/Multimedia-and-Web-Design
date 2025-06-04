<?php
// app/controllers/DoctorController.php

class DoctorController {
    // Khai báo các thuộc tính model
    private $doctorModel;
    private $appointmentModel;
    private $doctorAvailabilityModel; // Nếu bạn cũng dùng model này
    private $db; // KHAI BÁO THUỘC TÍNH DATABASE

    public function __construct() {
        // Khởi tạo các model cần thiết
        $this->doctorModel = new DoctorModel();
        $this->appointmentModel = new AppointmentModel();
        $this->doctorAvailabilityModel = new DoctorAvailabilityModel(); // Khởi tạo nếu dùng
        $this->db = Database::getInstance(); // KHỞI TẠO DATABASE INSTANCE
    }

    // Hàm để load view
    protected function view($view, $data = []) {
        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist.");
        }
    }

    public function dashboard() {
        // Kiểm tra đăng nhập và vai trò Doctor
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
            header('Location: ' . BASE_URL . '/auth/login?redirect_message=Please log in as a Doctor.');
            exit();
        }

        $data = [
            'title' => 'Doctor Dashboard',
            'welcome_message' => 'Welcome Dr. ' . htmlspecialchars($_SESSION['user_fullname']) . ' to your dashboard!'
        ];

        $data['links'] = [
            ['url' => BASE_URL . '/doctor/mySchedule', 'text' => 'View My Schedule'],
            ['url' => BASE_URL . '/doctor/manageAvailability', 'text' => 'Manage My Availability'],
        ];

        $this->view('doctor/dashboard', $data);
    }

  public function mySchedule() {
    // Kiểm tra đăng nhập và vai trò Doctor
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    // ... (lấy $doctorId như trước) ...
    $doctorInfo = $this->doctorModel->getDoctorByUserId($_SESSION['user_id']);
    if (!$doctorInfo) {
        $_SESSION['error_message'] = "Doctor profile not found for your account.";
        header('Location: ' . BASE_URL . '/doctor/dashboard');
        exit();
    }
    $doctorId = $doctorInfo['DoctorID'];

    // --- THAY ĐỔI GIÁ TRỊ MẶC ĐỊNH CHO BỘ LỌC ---
    // Mặc định hiển thị tất cả lịch hẹn SẮP TỚI (Scheduled hoặc Confirmed)
    // Hoặc bạn có thể để là 'all_time' và 'All' (status) nếu muốn thấy cả quá khứ và tất cả trạng thái
    $dateFilterInput = $_GET['date'] ?? 'all_upcoming'; // Mặc định là 'all_upcoming'
    $statusFilterInput = $_GET['status'] ?? 'All';       // Mặc định là 'All' (tất cả trạng thái)

    // Validate $statusFilterInput (giữ nguyên như trước)
    $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
    if (!in_array($statusFilterInput, $validStatuses)) {
        $statusFilterInput = 'All';
    }

    // Xử lý $dateFilterInput để tạo $dateRangeFilter cho model
    $dateRangeFilter = [];
    $currentDateFilterForView = $dateFilterInput; // Giữ lại giá trị input để hiển thị trên view

    switch ($dateFilterInput) {
        case 'today':
            $dateRangeFilter['specific_date'] = date('Y-m-d');
            break;
        case 'this_week':
            $dateRangeFilter['start_date'] = date('Y-m-d', strtotime('monday this week'));
            $dateRangeFilter['end_date'] = date('Y-m-d', strtotime('sunday this week'));
            break;
        case 'all_upcoming':
            $dateRangeFilter['type'] = 'all_upcoming'; // Gửi tín hiệu cho model
            break;
        case 'all_time':
            // Để $dateRangeFilter rỗng, model sẽ hiểu là không lọc ngày
            break;
        default:
            if (DateTime::createFromFormat('Y-m-d', $dateFilterInput) !== false) {
                $dateRangeFilter['specific_date'] = $dateFilterInput;
            } else {
                $currentDateFilterForView = 'all_upcoming'; // Mặc định lại
                $dateRangeFilter['type'] = 'all_upcoming';
            }
            break;
    }

    // Gọi phương thức trong AppointmentModel
    // Truyền $dateFilterInput trực tiếp nếu model có thể xử lý các giá trị như 'all_upcoming'
    // Hoặc truyền $dateRangeFilter nếu model chỉ nhận khoảng ngày cụ thể
    $appointments = $this->appointmentModel->getAppointmentsByDoctorId(
        $doctorId,
        $statusFilterInput,
        $dateRangeFilter, // Truyền mảng này
        'a.AppointmentDateTime ASC' // Sắp xếp lịch sắp tới lên đầu
    );


    $data = [
        'title' => 'My Schedule',
        'appointments' => $appointments,
        'currentDateFilter' => $currentDateFilterForView, // Giá trị để select trên view
        'currentStatusFilter' => $statusFilterInput,
        'allStatuses' => $validStatuses
    ];

    $this->view('doctor/my_schedule', $data);
}

    // Action manageAvailability sẽ được thêm sau
  public function manageAvailability() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    $doctorInfo = $this->doctorModel->getDoctorByUserId($_SESSION['user_id']);
    if (!$doctorInfo) {
        $_SESSION['error_message'] = "Doctor profile not found.";
        header('Location: ' . BASE_URL . '/doctor/dashboard');
        exit();
    }
    $doctorId = $doctorInfo['DoctorID'];

    // Lấy ngày từ GET request, nếu không có thì mặc định
    $defaultStartDate = date('Y-m-d');
    $defaultEndDate = date('Y-m-d', strtotime('+6 days'));

    $startDateInput = $_GET['start_date'] ?? $defaultStartDate;
    $endDateInput = $_GET['end_date'] ?? $defaultEndDate;

    // Validate dates (cơ bản)
    $startDate = (DateTime::createFromFormat('Y-m-d', $startDateInput) !== false) ? $startDateInput : $defaultStartDate;
    $endDate = (DateTime::createFromFormat('Y-m-d', $endDateInput) !== false) ? $endDateInput : $defaultEndDate;

    if (strtotime($startDate) > strtotime($endDate)) {
        // Nếu ngày bắt đầu sau ngày kết thúc, có thể đặt lại hoặc báo lỗi
        $startDate = $defaultStartDate;
        $endDate = $defaultEndDate;
        // Hoặc $_SESSION['availability_message_error'] = "Start date cannot be after end date.";
    }


    $slots = $this->doctorAvailabilityModel->getSlotsByDoctorForDateRange($doctorId, $startDate, $endDate);

    $data = [
        'title' => 'Manage My Availability',
        'slots' => $slots,
        'currentStartDate' => $startDate, // Để hiển thị lại trên form lọc
        'currentEndDate' => $endDate    // Để hiển thị lại trên form lọc
    ];

    $this->view('doctor/manage_availability', $data);
}
public function addAvailabilitySlot() {
    // Đảm bảo là POST request và Doctor đã đăng nhập
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
        // Nếu là AJAX request, trả về JSON lỗi
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(403); // Forbidden
            echo json_encode(['success' => false, 'message' => 'Unauthorized or invalid request.']);
            exit;
        }
        // Nếu là non-AJAX, chuyển hướng
        $_SESSION['availability_message_error'] = 'Unauthorized or invalid request.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
        exit();
    }

    // Lấy DoctorID (đảm bảo bạn có logic này)
    $doctorInfo = $this->doctorModel->getDoctorByUserId($_SESSION['user_id']);
    if (!$doctorInfo) {
        // Xử lý lỗi AJAX/non-AJAX tương tự như trên
        $this->sendJsonResponse(false, 'Doctor profile not found.', 403);
        exit;
    }
    $doctorId = $doctorInfo['DoctorID'];

    // (THÊM CSRF TOKEN VALIDATION Ở ĐÂY)

    // Lấy và validate input
    $slotDate = $_POST['slot_date'] ?? null;
    $startTimeInput = $_POST['start_time'] ?? null;
    $endTimeInput = $_POST['end_time'] ?? null;
    $slotDurationMinutes = filter_var($_POST['slot_duration'] ?? 30, FILTER_VALIDATE_INT); // Mặc định 30 phút

    $errors = [];
    if (empty($slotDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $slotDate) || strtotime($slotDate) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Invalid or past date selected.';
    }
    if (empty($startTimeInput) || !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $startTimeInput)) {
        $errors[] = 'Invalid start time format (HH:MM).';
    }
    if (empty($endTimeInput) || !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $endTimeInput)) {
        $errors[] = 'Invalid end time format (HH:MM).';
    }
    if ($slotDurationMinutes === false || $slotDurationMinutes <= 0) {
        $errors[] = 'Invalid slot duration.';
    }

    if (!empty($errors)) {
        $this->sendJsonResponse(false, implode(' ', $errors), 400);
        exit;
    }

    $overallStartTime = new DateTime($slotDate . ' ' . $startTimeInput);
    $overallEndTime = new DateTime($slotDate . ' ' . $endTimeInput);

    if ($overallStartTime >= $overallEndTime) {
        $this->sendJsonResponse(false, 'End time must be after start time.', 400);
        exit;
    }

    $createdSlotsCount = 0;
    $failedSlotsInfo = [];
    $currentSlotStart = clone $overallStartTime;

    $this->db->beginTransaction(); // Sử dụng DB instance từ __construct nếu có, hoặc Database::getInstance()
    try {
        while ($currentSlotStart < $overallEndTime) {
            $currentSlotEnd = clone $currentSlotStart;
            $currentSlotEnd->add(new DateInterval('PT' . $slotDurationMinutes . 'M'));

            // Đảm bảo slot cuối cùng không vượt quá overallEndTime
            if ($currentSlotEnd > $overallEndTime) {
                // Bạn có thể quyết định không tạo slot cuối này nếu nó bị cắt ngắn
                // Hoặc điều chỉnh $currentSlotEnd = $overallEndTime (nếu muốn slot cuối có độ dài khác)
                // Hiện tại, chúng ta sẽ bỏ qua slot bị cắt ngắn
                break;
            }

            $newSlotId = $this->doctorAvailabilityModel->createSlot(
                $doctorId,
                $currentSlotStart->format('Y-m-d'),
                $currentSlotStart->format('H:i:s'),
                $currentSlotEnd->format('H:i:s')
            );

            if ($newSlotId) {
                $createdSlotsCount++;
            } else {
                // Lỗi có thể do chồng chéo hoặc lỗi DB khác
                $failedSlotsInfo[] = $currentSlotStart->format('H:i A') . ' - ' . $currentSlotEnd->format('H:i A') . ' (Possibly overlaps or DB error)';
            }
            $currentSlotStart = $currentSlotEnd; // Chuyển sang slot tiếp theo
        }

        if ($createdSlotsCount > 0 && empty($failedSlotsInfo)) {
            $this->db->commit();
            $_SESSION['availability_message_success'] = $createdSlotsCount . ' availability slot(s) added successfully.';
            $this->sendJsonResponse(true, $createdSlotsCount . ' slot(s) added successfully.');
        } elseif ($createdSlotsCount > 0 && !empty($failedSlotsInfo)) {
            $this->db->commit(); // Vẫn commit các slot thành công
            $errorMessage = $createdSlotsCount . ' slot(s) added. Failed to add: ' . implode(', ', $failedSlotsInfo);
            $_SESSION['availability_message_error'] = $errorMessage;
            $this->sendJsonResponse(true, $errorMessage, 207); // 207 Multi-Status
        } elseif (empty($failedSlotsInfo)){ // Không có slot nào được tạo và không có lỗi cụ thể (ví dụ: khoảng thời gian quá ngắn)
            $this->db->rollBack();
            $_SESSION['availability_message_error'] = 'No slots were created. The time range might be too short for the selected duration.';
            $this->sendJsonResponse(false, 'No slots created. Time range might be too short.', 400);
        }
         else { // Tất cả đều thất bại
            $this->db->rollBack();
            $errorMessage = 'Failed to add any slots. Reasons: ' . implode(', ', $failedSlotsInfo);
            $_SESSION['availability_message_error'] = $errorMessage;
            $this->sendJsonResponse(false, $errorMessage, 409); // 409 Conflict nếu do overlap
        }

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Error adding availability slots: " . $e->getMessage());
        $_SESSION['availability_message_error'] = 'An error occurred: ' . $e->getMessage();
        $this->sendJsonResponse(false, 'An error occurred: ' . $e->getMessage(), 500);
    }
    exit;
}

// Hàm helper để gửi JSON response (có thể đặt trong BaseController)
private function sendJsonResponse($success, $message, $httpCode = 200, $data = []) {
    header('Content-Type: application/json');
    http_response_code($httpCode);
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) {
        $response['data'] = $data;
    }
    echo json_encode($response);
}
public function deleteAvailabilitySlot() {
    // 1. Kiểm tra phương thức Request (phải là POST) & Xác thực Doctor
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
        $_SESSION['availability_message_error'] = 'Unauthorized or invalid request.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
        exit();
    }

    // Lấy DoctorID
    $doctorInfo = $this->doctorModel->getDoctorByUserId($_SESSION['user_id']);
    if (!$doctorInfo) {
        $_SESSION['availability_message_error'] = 'Doctor profile not found.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
        exit();
    }
    $doctorId = $doctorInfo['DoctorID'];

    // (THÊM CSRF TOKEN VALIDATION Ở ĐÂY - RẤT QUAN TRỌNG)
    // if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    //     $_SESSION['availability_message_error'] = 'Invalid CSRF token.';
    //     header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
    //     exit();
    // }

    $availabilityId = $_POST['availability_id'] ?? null;

    if (!filter_var($availabilityId, FILTER_VALIDATE_INT) || $availabilityId <= 0) {
        $_SESSION['availability_message_error'] = 'Invalid availability slot ID.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
        exit();
    }

    // 2. Xử lý logic xóa
    try {
        if ($this->doctorAvailabilityModel->deleteSlotByIdAndDoctor((int)$availabilityId, $doctorId)) {
            // Kiểm tra rowCount để biết slot có thực sự bị xóa không (tức là nó tồn tại và thỏa điều kiện)
            if ($this->db->rowCount() > 0) {
                 $_SESSION['availability_message_success'] = 'Availability slot deleted successfully.';
            } else {
                 $_SESSION['availability_message_error'] = 'Slot not found, already booked, or you do not have permission to delete it.';
            }
        } else {
            throw new Exception('Failed to execute delete operation on slot.');
        }
    } catch (Exception $e) {
        error_log("Error deleting availability slot: " . $e->getMessage());
        $_SESSION['availability_message_error'] = 'An error occurred: ' . $e->getMessage();
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
    exit();
}


public function updateSlotType() {
    // 1. Kiểm tra phương thức Request (phải là POST) & Xác thực Doctor
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
        $_SESSION['availability_message_error'] = 'Unauthorized or invalid request.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
        exit();
    }

    // Lấy DoctorID
    $doctorInfo = $this->doctorModel->getDoctorByUserId($_SESSION['user_id']);
    if (!$doctorInfo) {
        $_SESSION['availability_message_error'] = 'Doctor profile not found.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
        exit();
    }
    $doctorId = $doctorInfo['DoctorID'];

    // (THÊM CSRF TOKEN VALIDATION Ở ĐÂY)

    $availabilityId = $_POST['availability_id'] ?? null;
    $newType = $_POST['new_type'] ?? null; // 'Blocked' hoặc 'Working'

    if (!filter_var($availabilityId, FILTER_VALIDATE_INT) || $availabilityId <= 0) {
        $_SESSION['availability_message_error'] = 'Invalid availability slot ID.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
        exit();
    }

    $allowedTypes = ['Working', 'Blocked']; // Các loại trạng thái cho phép cập nhật
    if (empty($newType) || !in_array($newType, $allowedTypes)) {
        $_SESSION['availability_message_error'] = 'Invalid new slot type specified.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
        exit();
    }

    // 2. Xử lý logic cập nhật
    try {
        if ($this->doctorAvailabilityModel->updateSlotTypeByIdAndDoctor((int)$availabilityId, $doctorId, $newType)) {
            if ($this->db->rowCount() > 0) {
                $action = ($newType === 'Blocked') ? 'blocked' : 'made available';
                $_SESSION['availability_message_success'] = "Availability slot successfully {$action}.";
            } else {
                 $_SESSION['availability_message_error'] = 'Slot not found, already booked, or you do not have permission to update it.';
            }
        } else {
            throw new Exception('Failed to execute update operation on slot type.');
        }
    } catch (Exception $e) {
        error_log("Error updating slot type: " . $e->getMessage());
        $_SESSION['availability_message_error'] = 'An error occurred: ' . $e->getMessage();
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/manageAvailability'));
    exit();
}
}
?>