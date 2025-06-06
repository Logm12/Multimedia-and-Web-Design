<?php
// app/controllers/DoctorController.php
// Đường dẫn đúng: đi ra khỏi 'controllers', rồi đi vào 'core'


class DoctorController  {
    private $doctorModel;
    private $appointmentModel;
    private $doctorAvailabilityModel;
    private $feedbackModel;
    private $patientModel;
    private $userModel;
    private $notificationModel;
    private $leaveRequestModel; // <<<< THÊM MODEL MỚI NÈ CẬU
    
       private $specializationModel; // Khai báo thuộc tính


    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $urlPath = $_GET['url'] ?? '';
        $urlParts = explode('/', rtrim($urlPath, '/')); // Rtrim để xử lý dấu / cuối URL
        $controllerNameFromUrl = $urlParts[0] ?? '';
        // Action mặc định là 'dashboard' nếu không có gì được chỉ định
        $currentAction = $urlParts[1] ?? 'dashboard'; 

        // Xác thực vai trò Doctor cho các action không public
        if (strtolower($controllerNameFromUrl) === 'doctor') {
            // Các action public (ví dụ: trang profile công khai của bác sĩ nếu có)
            $publicActions = []; // Hiện tại không có action public nào cho Doctor
            
            if (!in_array($currentAction, $publicActions)) {
                if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
                    $_SESSION['error_message'] = "Access denied. Please log in as a Doctor.";
                    header('Location: ' . BASE_URL . '/auth/login');
                    exit();
                }
            }
        }
        
        // Khởi tạo các model
        try {
            $this->doctorModel = new DoctorModel();
            $this->appointmentModel = new AppointmentModel();
            $this->doctorAvailabilityModel = new DoctorAvailabilityModel();
            $this->feedbackModel = new FeedbackModel();
            $this->notificationModel = new NotificationModel(); 
            $this->patientModel = new PatientModel();
            $this->userModel = new UserModel();
            $this->leaveRequestModel = new LeaveRequestModel(); // <<<< KHỞI TẠO MODEL MỚI
            $this->specializationModel = new SpecializationModel(); // Khởi tạo model chuyên khoa
        } catch (Error $e) {
            error_log("FATAL: Model initialization error in DoctorController: " . $e->getMessage());
            die("A critical error occurred during application setup. Please check logs or contact support. Details: " . htmlspecialchars($e->getMessage()));
        }
    }
    
    private function getLoggedInDoctorId() {
        if (!isset($_SESSION['user_id'])) { 
            $_SESSION['error_message'] = "Session expired or invalid. Please log in again.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        // Lấy DoctorID từ bảng doctors dựa trên UserID trong session
        $doctorInfo = $this->doctorModel->getDoctorByUserId($_SESSION['user_id']);
        if (!$doctorInfo || !isset($doctorInfo['DoctorID'])) {
            // User này có Role Doctor nhưng không có record trong bảng doctors, hoặc getDoctorByUserId lỗi
            $_SESSION['error_message'] = "Doctor profile not found. Please contact administrator.";
            error_log("Doctor profile (DoctorID) missing for UserID: " . $_SESSION['user_id'] . ". Logging out.");
            // Đăng xuất để tránh lỗi ở các bước sau
            unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_fullname'], $_SESSION['user_avatar']);
            header('Location: ' . BASE_URL . '/auth/login?error=doctor_profile_missing'); 
            exit();
        }
        return (int)$doctorInfo['DoctorID'];
    }

    protected function view($view, $data = []) {
        // Thêm thông tin người dùng hiện tại vào $data nếu chưa có
        if (!isset($data['currentUser'])) {
            $data['currentUser'] = [
                'UserID' => $_SESSION['user_id'] ?? null,
                'FullName' => $_SESSION['user_fullname'] ?? 'Doctor',
                'Role' => $_SESSION['user_role'] ?? null,
                'Avatar' => $_SESSION['user_avatar'] ?? null
            ];
        }
        // Đảm bảo $data['title'] luôn tồn tại
        if (!isset($data['title'])) {
            $data['title'] = 'Doctor Panel'; // Title mặc định
        }

        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist, my dear.");
        }
    }

    private function sendJsonResponse($success, $message, $httpCode = 200, $data = []) {
        header('Content-Type: application/json');
        http_response_code($httpCode);
        $response = ['success' => $success, 'message' => $message];
        if (!empty($data)) {
            $response['data'] = $data;
        }
        echo json_encode($response);
        // Không exit ở đây để cho phép controller gọi nó rồi exit sau nếu cần
    }
    public function dashboard() {
        $doctorId = $this->getLoggedInDoctorId();
        $appointmentOverviewData = ['labels' => [], 'counts' => []];
        for ($i = 3; $i >= 0; $i--) {
            $weekStartDate = date('Y-m-d', strtotime("monday this week -{$i} weeks"));
            $appointmentOverviewData['labels'][] = "Week " . date('W', strtotime($weekStartDate));
            $appointmentOverviewData['counts'][] = rand(5, 25); // Placeholder, thay bằng logic thật
        }
        $data = [
            'title' => 'Doctor Dashboard',
            'welcome_message' => 'Welcome back, Dr. ' . htmlspecialchars($_SESSION['user_fullname'] ?? '') . '!',
            'followed_patients_count' => $this->doctorModel->getFollowedPatientsCount($doctorId),
            'follow_ups_due_count' => $this->appointmentModel->getFollowUpsDueCount($doctorId),
            'patient_feedbacks_count' => $this->feedbackModel->getPatientFeedbacksCountForDoctor($doctorId),
            'todays_appointments' => $this->appointmentModel->getTodaysAppointmentsForDoctor($doctorId),
            'appointment_overview_data' => $appointmentOverviewData
        ];
        $this->view('doctor/dashboard', $data);
    }

    public function mySchedule() {
        $doctorId = $this->getLoggedInDoctorId();
        $dateFilterInput = $_GET['date'] ?? 'all_upcoming'; 
        $statusFilterInput = $_GET['status'] ?? 'All';
        $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
        if (!in_array($statusFilterInput, $validStatuses)) $statusFilterInput = 'All';
        
        $dateRangeFilter = []; 
        $currentDateFilterForView = $dateFilterInput; // Giữ lại giá trị gốc để hiển thị

        switch ($dateFilterInput) {
            case 'today': $dateRangeFilter['specific_date'] = date('Y-m-d'); break;
            case 'this_week': 
                $dateRangeFilter['start_date'] = date('Y-m-d', strtotime('monday this week')); 
                $dateRangeFilter['end_date'] = date('Y-m-d', strtotime('sunday this week')); 
                break;
            case 'all_upcoming': $dateRangeFilter['type'] = 'all_upcoming'; break;
            case 'all_time': /* không cần filter ngày */ break;
            default:
                if (DateTime::createFromFormat('Y-m-d', $dateFilterInput) !== false) {
                    $dateRangeFilter['specific_date'] = $dateFilterInput;
                } else { 
                    $currentDateFilterForView = 'all_upcoming'; // Nếu không hợp lệ, quay về mặc định
                    $dateRangeFilter['type'] = 'all_upcoming'; 
                }
                break;
        }
        $appointments = $this->appointmentModel->getAppointmentsByDoctorId($doctorId, $statusFilterInput, $dateRangeFilter, 'a.AppointmentDateTime ASC');
        $data = [
            'title' => 'My Schedule', 
            'appointments' => $appointments, 
            'currentDateFilter' => $currentDateFilterForView, 
            'currentStatusFilter' => $statusFilterInput, 
            'allStatuses' => $validStatuses
        ];
        $this->view('doctor/my_schedule', $data);
    }

    public function manageAvailability() {
        $doctorId = $this->getLoggedInDoctorId();
        // Lấy ngày bắt đầu và kết thúc từ GET params cho FullCalendar, hoặc mặc định
        $viewStartDate = $_GET['start'] ?? date('Y-m-01'); 
        $viewEndDate = $_GET['end'] ?? date('Y-m-t', strtotime($viewStartDate . ' +2 months')); 
        
        // Lấy slots cho FullCalendar (đã có logic này ở hàm getAvailabilityEvents)
        // Ở đây chỉ cần truyền tháng hiện tại cho mini calendar nếu có
        $data = [
            'title' => 'Manage Availability', 
            'slotsForCalendar' => [], // Sẽ được load bằng AJAX qua getAvailabilityEvents
            'currentMonthYearForMiniCal' => date('F Y', strtotime($viewStartDate))
        ];
        $this->view('doctor/manage_availability', $data);
    }
    
    public function getAvailabilityEvents() {
        header('Content-Type: application/json');
        $doctorId = $this->getLoggedInDoctorId();
        $startParam = $_GET['start'] ?? null; 
        $endParam = $_GET['end'] ?? null;  
        
        // FullCalendar gửi start và end là thời điểm, cần chuyển về ngày
        $startDate = $startParam ? date('Y-m-d', strtotime($startParam)) : date('Y-m-01');
        $endDate = $endParam ? date('Y-m-d', strtotime($endParam . ' -1 day')) : date('Y-m-t', strtotime($startDate . ' +2 months')); // FC end là exclusive

        $rawSlots = $this->doctorAvailabilityModel->getSlotsByDoctorForDateRange($doctorId, $startDate, $endDate);
        $calendarEvents = [];
        foreach ($rawSlots as $slot) {
            $title = $slot['IsBooked'] ? ($slot['PatientName'] ?? 'Booked') : ($slot['SlotType'] === 'Blocked' ? 'Blocked' : 'Available');
            $className = $slot['IsBooked'] ? 'status-booked-fc' : ($slot['SlotType'] === 'Blocked' ? 'status-blocked-fc' : 'status-available-fc');
            $calendarEvents[] = [
                'id' => $slot['AvailabilityID'], 
                'title' => $title, 
                'start' => $slot['AvailableDate'] . 'T' . $slot['StartTime'], 
                'end' => $slot['AvailableDate'] . 'T' . $slot['EndTime'], 
                'className' => $className, 
                'extendedProps' => [
                    'isBooked' => (bool)$slot['IsBooked'], 
                    'slotType' => $slot['SlotType'], 
                    'appointmentId' => $slot['AppointmentID'] ?? null
                ]
            ];
        }
        echo json_encode($calendarEvents);
        exit;
    }

    public function addAvailabilitySlot() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->sendJsonResponse(false, 'Invalid request method.', 405); exit; }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) { $this->sendJsonResponse(false, 'Invalid CSRF token.', 403); exit; }
        
        $doctorId = $this->getLoggedInDoctorId();
        $slotDate = $_POST['slot_date'] ?? null; 
        $startTimeInput = $_POST['start_time'] ?? null; 
        $endTimeInput = $_POST['end_time'] ?? null;
        $slotDurationMinutes = filter_var($_POST['slot_duration'] ?? 30, FILTER_VALIDATE_INT, ['options' => ['min_range' => 5]]);
        $repeatOption = $_POST['repeat_option'] ?? 'none'; 
        $repeatUntil = $_POST['repeat_until'] ?? null;
        
        $errors = [];
        if (empty($slotDate) || !DateTime::createFromFormat('Y-m-d', $slotDate)) $errors[] = 'Invalid slot date.';
        if ($repeatOption === 'none' && strtotime($slotDate) < strtotime(date('Y-m-d'))) $errors[] = 'Cannot add slots for past dates (for non-repeating).';
        if (empty($startTimeInput) || !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $startTimeInput)) $errors[] = 'Invalid start time format (HH:MM).';
        if (empty($endTimeInput) || !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $endTimeInput)) $errors[] = 'Invalid end time format (HH:MM).';
        if ($slotDurationMinutes === false) $errors[] = 'Invalid slot duration (must be a positive number).';
        if ($repeatOption !== 'none' && (empty($repeatUntil) || !DateTime::createFromFormat('Y-m-d', $repeatUntil) || strtotime($repeatUntil) < strtotime($slotDate))) $errors[] = 'Invalid repeat until date (must be on or after slot date).';
        
        if (!empty($errors)) { $this->sendJsonResponse(false, implode(' ', $errors), 400); exit; }
        
        $overallStartTime = new DateTime($slotDate . ' ' . $startTimeInput); 
        $overallEndTime = new DateTime($slotDate . ' ' . $endTimeInput);
        if ($overallStartTime >= $overallEndTime) { $this->sendJsonResponse(false, 'End time must be after start time.', 400); exit; }
        
        $db = Database::getInstance(); 
        $db->beginTransaction();
        try {
            $slotsData = [
                'doctorId' => $doctorId, 
                'slotDate' => $slotDate, 
                'startTime' => $startTimeInput, 
                'endTime' => $endTimeInput, 
                'durationMinutes' => $slotDurationMinutes, 
                'repeatOption' => $repeatOption, 
                'repeatUntilDate' => $repeatOption !== 'none' ? $repeatUntil : null
            ];
            $result = $this->doctorAvailabilityModel->createRecurringSlots($slotsData);
            
            if ($result['success']) { 
                $db->commit(); 
                $this->sendJsonResponse(true, $result['message'] ?? ($result['created_count'] . ' slot(s) added successfully.'), ($result['failed_count'] ?? 0) > 0 ? 207 : 200);
            } else { 
                $db->rollBack(); 
                $this->sendJsonResponse(false, $result['message'] ?? 'Failed to add slots due to overlaps or other issues.', 400); 
            }
        } catch (Exception $e) { 
            if ($db->inTransaction()) $db->rollBack(); 
            error_log("DoctorController::addAvailabilitySlot Error: " . $e->getMessage()); 
            $this->sendJsonResponse(false, 'An unexpected error occurred: ' . $e->getMessage(), 500); 
        }
        exit;
    }

    public function deleteAvailabilitySlot() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->sendJsonResponse(false, 'Invalid method', 405); exit(); }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) { $this->sendJsonResponse(false, 'Invalid CSRF token.', 403); exit(); }

        $doctorId = $this->getLoggedInDoctorId();
        $availabilityId = $_POST['id'] ?? ($_POST['availability_id'] ?? null);
        if (!filter_var($availabilityId, FILTER_VALIDATE_INT) || $availabilityId <= 0) { $this->sendJsonResponse(false, 'Invalid slot ID.', 400); exit(); }
        
        try {
            if ($this->doctorAvailabilityModel->deleteSlotByIdAndDoctor((int)$availabilityId, $doctorId)) {
                $db = Database::getInstance(); // Lấy instance DB để kiểm tra rowCount
                if ($db->rowCount() > 0) {
                    $this->sendJsonResponse(true, 'Slot deleted successfully.');
                } else {
                    // Slot không tồn tại, không thuộc bác sĩ, hoặc đã được đặt và không thể xóa
                    $this->sendJsonResponse(false, 'Slot not found, does not belong to you, or is booked and cannot be deleted.', 404);
                }
            } else {
                // Lỗi chung từ model (ví dụ: query thất bại)
                throw new Exception('Database operation failed during slot deletion.');
            }
        } catch (Exception $e) { 
            error_log("DoctorController::deleteAvailabilitySlot Error: " . $e->getMessage()); 
            $this->sendJsonResponse(false, 'An error occurred: ' . $e->getMessage(), 500); 
        }
        exit();
    }
    
    public function updateAvailabilityEvent() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->sendJsonResponse(false, 'Invalid method', 405); exit(); }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) { $this->sendJsonResponse(false, 'Invalid CSRF token.', 403); exit(); }

        $doctorId = $this->getLoggedInDoctorId();
        $input = json_decode(file_get_contents('php://input'), true); // Giả sử client gửi JSON
        
        // Nếu client gửi form-data thì dùng $_POST
        // $eventId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        // $newStartStr = filter_var($_POST['start'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        // $newEndStr = filter_var($_POST['end'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        
        $eventId = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        $newStartStr = filter_var($input['start'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $newEndStr = filter_var($input['end'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$eventId || !$newStartStr || !$newEndStr) { $this->sendJsonResponse(false, 'Missing required data (id, start, end).', 400); exit(); }
        
        try {
            $newStartDate = date('Y-m-d', strtotime($newStartStr)); 
            $newStartTime = date('H:i:s', strtotime($newStartStr)); 
            $newEndTime = date('H:i:s', strtotime($newEndStr));
            
            // Cần kiểm tra xem slot có bị booked không trước khi cho update thời gian
            $slotDetails = $this->doctorAvailabilityModel->getSlotById($eventId);
            if (!$slotDetails || $slotDetails['DoctorID'] != $doctorId) {
                 $this->sendJsonResponse(false, 'Slot not found or not yours.', 404); exit();
            }
            if ($slotDetails['IsBooked']) {
                 $this->sendJsonResponse(false, 'Cannot modify a booked slot.', 403); exit();
            }

            $success = $this->doctorAvailabilityModel->updateSlotDateTime($eventId, $doctorId, $newStartDate, $newStartTime, $newEndTime);
            
            if ($success) {
                $this->sendJsonResponse(true, 'Slot updated successfully.');
            } else {
                $this->sendJsonResponse(false, 'Failed to update slot. It might conflict with another slot or an existing appointment.');
            }
        } catch (Exception $e) { 
            error_log("DoctorController::updateAvailabilityEvent Error: " . $e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred: ' . $e->getMessage(), 500); 
        }
        exit();
    }

    public function patientList() {
        $doctorId = $this->getLoggedInDoctorId();
        $searchTerm = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS);
        
        // Cần một hàm trong PatientModel để lấy danh sách bệnh nhân mà bác sĩ này đã từng khám
        // hoặc tất cả bệnh nhân nếu logic cho phép.
        // Ví dụ: $patients = $this->patientModel->getPatientsConsultedByDoctor($doctorId, $searchTerm);
        $patients = $this->patientModel->getAllPatientsWithDetails($searchTerm); // Tạm thời lấy tất cả

        $todaysAppointments = $this->appointmentModel->getTodaysAppointmentsForDoctor($doctorId);

        $data = [
            'title' => 'Patient Management',
            'patients' => $patients,
            'todays_appointments' => $todaysAppointments,
            'quick_stats' => [
                'total_patients' => $this->patientModel->getTotalPatientsCount(),
                'new_this_month' => $this->patientModel->getNewPatientsThisMonthCount(),
            ],
            'prescription_stats_data' => [ 
                'labels' => ['Drug A', 'Drug B', 'Drug C'], // Placeholder
                'counts' => [rand(20,50), rand(10,30), rand(5,15)]
            ]
        ];
        $this->view('doctor/patient_list', $data);
    }

    public function addPatient() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->sendJsonResponse(false, 'Invalid request.', 405); exit; }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) { $this->sendJsonResponse(false, 'Invalid CSRF token.', 403); exit; }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $input = [
            'fullname' => trim($_POST['fullname'] ?? ''), 'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''), 'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'phone_number' => trim($_POST['phone_number'] ?? null), // Cho phép null
            'address' => trim($_POST['address'] ?? null), // Cho phép null
            'date_of_birth' => trim($_POST['date_of_birth'] ?? null), // Cho phép null
            'gender' => trim($_POST['gender'] ?? null) // Cho phép null
        ];
        $errors = [];

        if (empty($input['fullname'])) $errors['fullname'] = 'Full name is required.';
        if (empty($input['username'])) $errors['username'] = 'Username is required.';
        elseif ($this->userModel->findUserByUsername($input['username'])) $errors['username'] = 'Username already taken.';
        if (empty($input['email'])) $errors['email'] = 'Email is required.';
        elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format.';
        elseif ($this->userModel->findUserByEmail($input['email'])) $errors['email'] = 'Email already registered.';
        if (empty($input['password'])) $errors['password'] = 'Password is required.';
        elseif (strlen($input['password']) < 6) $errors['password'] = 'Password must be at least 6 characters.';
        if ($input['password'] !== $input['confirm_password']) $errors['confirm_password'] = 'Passwords do not match.';
        if (!empty($input['date_of_birth']) && !DateTime::createFromFormat('Y-m-d', $input['date_of_birth'])) $errors['date_of_birth'] = 'Invalid date of birth format (YYYY-MM-DD).';
        if (!empty($input['gender']) && !in_array($input['gender'], ['Male', 'Female', 'Other'])) $errors['gender'] = 'Invalid gender.';


        if (!empty($errors)) { $this->sendJsonResponse(false, 'Validation failed. Please check the highlighted fields.', 400, ['errors' => $errors]); exit; }

        $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
        $db = Database::getInstance(); 
        $db->beginTransaction();
        try {
            $userData = [
                'Username' => $input['username'], 'PasswordHash' => $passwordHash, 'Email' => $input['email'],
                'FullName' => $input['fullname'], 'Role' => 'Patient', 
                'PhoneNumber' => $input['phone_number'] ?: null, // Lưu NULL nếu rỗng
                'Address' => $input['address'] ?: null, // Lưu NULL nếu rỗng
                'Status' => 'Active' 
            ];
            $newUserId = $this->userModel->createUser($userData);
            if ($newUserId) {
                $patientData = [
                    'UserID' => $newUserId,
                    'DateOfBirth' => $input['date_of_birth'] ?: null,
                    'Gender' => $input['gender'] ?: null
                ];
                if ($this->patientModel->createPatient($patientData)) {
                    $db->commit();
                    $this->sendJsonResponse(true, 'Patient added successfully!'); exit;
                } else { 
                    $db->rollBack(); 
                    $this->sendJsonResponse(false, 'Failed to create patient profile after user creation.'); exit; 
                }
            } else { 
                $db->rollBack(); 
                $this->sendJsonResponse(false, 'Failed to create user account for the patient.'); exit; 
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack(); 
            error_log("DoctorController::addPatient Error: " . $e->getMessage());
            $this->sendJsonResponse(false, 'An unexpected system error occurred: ' . $e->getMessage(), 500); exit;
        }
    }
    
    public function notifications() {
        $userIdForNotifications = $_SESSION['user_id']; 
        $notifications = $this->notificationModel->getNotificationsByUserId($userIdForNotifications);
        $unreadCount = $this->notificationModel->getUnreadNotificationCount($userIdForNotifications);

        $data = [
            'title' => 'My Notifications',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount 
        ];
        $this->view('doctor/notifications_list', $data);
    }

    public function markNotificationRead($notificationId = 0) {
        $userId = $_SESSION['user_id'];
        $notificationId = (int)$notificationId;
        if ($notificationId > 0) {
            $this->notificationModel->markAsRead($notificationId, $userId);
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/notifications'));
        exit();
    }
    
    public function markAllNotificationsRead() {
        $userId = $_SESSION['user_id'];
        $this->notificationModel->markAllAsRead($userId);
        $_SESSION['success_message'] = "All notifications marked as read, sweetie!";
        header('Location: ' . BASE_URL . '/doctor/notifications');
        exit();
    }
    public function requestLeave() {
        $doctorId = $this->getLoggedInDoctorId();
        $data = [
            'title' => 'Request Time Off',
            'input' => $_SESSION['leave_request_input'] ?? [], // Giữ lại input nếu có lỗi
            'errors' => $_SESSION['leave_request_errors'] ?? []
        ];
        unset($_SESSION['leave_request_input'], $_SESSION['leave_request_errors']);
        $this->view('doctor/leave/request_form', $data);
    }

    public function submitLeaveRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/doctor/requestLeave');
            exit();
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['leave_request_errors'] = ['csrf' => 'Invalid security token. Please try again.'];
            header('Location: ' . BASE_URL . '/doctor/requestLeave');
            exit();
        }

        $doctorId = $this->getLoggedInDoctorId();
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        $errors = [];
        $input = ['start_date' => $startDate, 'end_date' => $endDate, 'reason' => $reason];

        if (empty($startDate) || !DateTime::createFromFormat('Y-m-d', $startDate)) {
            $errors['start_date'] = 'Please select a valid start date.';
        }
        if (empty($endDate) || !DateTime::createFromFormat('Y-m-d', $endDate)) {
            $errors['end_date'] = 'Please select a valid end date.';
        }
        if (!empty($startDate) && !empty($endDate) && strtotime($endDate) < strtotime($startDate)) {
            $errors['end_date'] = 'End date cannot be before start date.';
        }
        if (!empty($startDate) && strtotime($startDate) < strtotime(date('Y-m-d'))) {
            $errors['start_date'] = 'Start date cannot be in the past.';
        }
        if (strlen($reason) > 1000) { // Giới hạn lý do
            $errors['reason'] = 'Reason is too long (max 1000 characters).';
        }

        // Kiểm tra lịch làm việc và lịch hẹn trùng lặp
        $overlappingAppointments = [];
        $overlappingAvailability = [];
        if (empty($errors) && !empty($startDate) && !empty($endDate)) {
            $overlappingAvailability = $this->leaveRequestModel->getOverlappingAvailability($doctorId, $startDate, $endDate);
            $overlappingAppointments = $this->leaveRequestModel->getOverlappingAppointments($doctorId, $startDate, $endDate);

            if (!empty($overlappingAvailability) || !empty($overlappingAppointments)) {
                // Thay vì báo lỗi ngay, có thể lưu vào session để hiển thị cảnh báo trên form
                $_SESSION['leave_request_warning'] = "Warning: Your requested time off overlaps with existing scheduled work or appointments. Please review them. Submitting will still create a pending request.";
                // Hoặc nếu muốn chặn hoàn toàn:
                // $errors['overlap'] = "Your requested time off overlaps with existing work/appointments. Please adjust your schedule or the leave dates.";
            }
        }


        if (!empty($errors)) {
            $_SESSION['leave_request_input'] = $input;
            $_SESSION['leave_request_errors'] = $errors;
            header('Location: ' . BASE_URL . '/doctor/requestLeave');
            exit();
        }

        $leaveData = [
            'DoctorID' => $doctorId,
            'StartDate' => $startDate,
            'EndDate' => $endDate,
            'Reason' => $reason,
            'Status' => 'Pending' // Trạng thái mặc định
        ];

        if ($this->leaveRequestModel->createLeaveRequest($leaveData)) {
            $_SESSION['success_message'] = 'Your leave request has been submitted successfully and is pending approval.';
            // Gửi thông báo cho Admin (nếu có hệ thống thông báo)
            // $adminUsers = $this->userModel->getUsersByRole('Admin');
            // foreach($adminUsers as $admin){
            //    $this->notificationModel->createNotification([...]);
            // }
            header('Location: ' . BASE_URL . '/doctor/myLeaveRequests');
        } else {
            $_SESSION['leave_request_input'] = $input;
            $_SESSION['error_message'] = 'Failed to submit leave request. Please try again.';
            header('Location: ' . BASE_URL . '/doctor/requestLeave');
        }
        exit();
    }

    public function myLeaveRequests() {
        $doctorId = $this->getLoggedInDoctorId();
        // Lấy thêm filter nếu cần, ví dụ: ?status=Pending
        $statusFilter = $_GET['status'] ?? null;
        
        $leaveRequests = $this->leaveRequestModel->getLeaveRequestsByDoctorId($doctorId, $statusFilter);
        $data = [
            'title' => 'My Leave Requests',
            'leaveRequests' => $leaveRequests,
            'currentStatusFilter' => $statusFilter,
            'allStatuses' => ['Pending', 'Approved', 'Rejected', 'Cancelled'] // Để tạo filter dropdown
        ];
        $this->view('doctor/leave/my_requests', $data);
    }

    public function cancelMyLeaveRequest($leaveRequestId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = 'Invalid request method.';
            header('Location: ' . BASE_URL . '/doctor/myLeaveRequests');
            exit();
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error_message'] = 'Invalid security token.';
            header('Location: ' . BASE_URL . '/doctor/myLeaveRequests');
            exit();
        }

        $leaveRequestId = (int)($_POST['leave_request_id_to_cancel'] ?? $leaveRequestId);
        if ($leaveRequestId <= 0) {
            $_SESSION['error_message'] = 'Invalid leave request ID.';
            header('Location: ' . BASE_URL . '/doctor/myLeaveRequests');
            exit();
        }
        
        $doctorId = $this->getLoggedInDoctorId();

        // LeaveRequestModel sẽ kiểm tra xem request có thuộc doctorId này và có status là Pending không
        if ($this->leaveRequestModel->cancelLeaveRequestByDoctor($leaveRequestId, $doctorId)) {
            $_SESSION['success_message'] = 'Leave request cancelled successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to cancel leave request. It might have already been processed or does not belong to you.';
        }
        header('Location: ' . BASE_URL . '/doctor/myLeaveRequests');
        exit();
    }
public function updateprofile()
{
    // --- BƯỚC 1: KIỂM TRA ĐĂNG NHẬP VÀ CHUẨN BỊ DỮ LIỆU ---

    // Bắt đầu session nếu chưa có
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Kiểm tra quyền truy cập
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    $userId = $_SESSION['user_id'];

    // Lấy thông tin chi tiết của Doctor từ DB (bao gồm cả PasswordHash)
    $doctorDetails = $this->doctorModel->getDoctorByUserId($userId);
    if (!$doctorDetails) {
        // Có thể hiển thị một trang lỗi thay vì die()
        die("Critical Error: Doctor profile associated with your user account could not be found.");
    }
    
    // Lấy danh sách chuyên khoa cho dropdown
    $specializations = $this->specializationModel->getAll();

    // Chuẩn bị mảng $data ban đầu để truyền vào view
    $data = [
        'title' => 'Update My Profile',
        'input' => (array)$doctorDetails, // Dữ liệu từ DB cho lần tải đầu tiên
        'errors' => [],
        'specializations' => $specializations
    ];

    // --- BƯỚC 2: XỬ LÝ KHI NGƯỜI DÙNG SUBMIT FORM (REQUEST LÀ POST) ---
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        // 2.1. Lọc và làm sạch dữ liệu từ form
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $input = [
            'FullName' => trim($_POST['FullName'] ?? ''),
            'PhoneNumber' => trim($_POST['PhoneNumber'] ?? ''),
            'specialization_id' => (int)($_POST['specialization_id'] ?? 0),
            'experience_years' => (int)($_POST['experience_years'] ?? 0),
            'doctor_bio' => trim($_POST['doctor_bio'] ?? ''),
            'current_password' => $_POST['current_password'] ?? '',
            'new_password' => $_POST['new_password'] ?? '',
            'confirm_new_password' => $_POST['confirm_new_password'] ?? ''
        ];
        // Cập nhật mảng $data['input'] để nếu có lỗi, form sẽ giữ lại các giá trị người dùng vừa nhập
        $data['input'] = array_merge($data['input'], $input);

        // 2.2. Xử lý upload Avatar
        $avatarPath = $doctorDetails['Avatar']; // Giữ avatar hiện tại làm mặc định
        $avatarUpdated = false;

        if (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['profile_avatar'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileExt, $allowedExt) && $file['size'] < 5000000) { // Giới hạn 5MB
                $newFileName = "avatar_doctor_" . $userId . "_" . uniqid() . "." . $fileExt;
                $uploadDir = rtrim(PUBLIC_PATH, '/') . '/uploads/avatars/'; // Đảm bảo PUBLIC_PATH đã được định nghĩa
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0775, true); }
                
                $destination = $uploadDir . $newFileName;
                $pathForDb = 'uploads/avatars/' . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Xóa avatar cũ nếu nó tồn tại và không phải là avatar mặc định
                    if (!empty($doctorDetails['Avatar']) && strpos($doctorDetails['Avatar'], 'default_avatar.png') === false) {
                        if (file_exists(PUBLIC_PATH . $doctorDetails['Avatar'])) {
                            unlink(PUBLIC_PATH . $doctorDetails['Avatar']);
                        }
                    }
                    $avatarPath = $pathForDb;
                    $avatarUpdated = true;
                } else {
                    $data['errors']['profile_avatar'] = "Failed to move uploaded file.";
                }
            } else {
                $data['errors']['profile_avatar'] = ($file['size'] >= 5000000) ? "File is too large (Max 5MB)." : "Invalid file type (JPG, PNG, GIF only).";
            }
        } elseif (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] != UPLOAD_ERR_NO_FILE) {
            $data['errors']['profile_avatar'] = "An error occurred during file upload. Code: " . $_FILES['profile_avatar']['error'];
        }

        // 2.3. Validation các trường dữ liệu
        if (empty($input['FullName'])) $data['errors']['FullName'] = 'Full name is required.';
        if (empty($input['specialization_id'])) $data['errors']['specialization_id'] = 'Specialization is required.';
        // Thêm các validation khác nếu cần...

        // 2.4. Validation cho việc đổi mật khẩu
        $updatePassword = false;
        if (!empty($input['new_password'])) {
            if (empty($input['current_password'])) {
                $data['errors']['current_password'] = 'Current password is required to set a new one.';
            } elseif (!password_verify($input['current_password'], $doctorDetails['PasswordHash'])) {
                $data['errors']['current_password'] = 'Incorrect current password.';
            }
            if (strlen($input['new_password']) < 6) {
                $data['errors']['new_password'] = 'New password must be at least 6 characters long.';
            }
            if ($input['new_password'] !== $input['confirm_new_password']) {
                $data['errors']['confirm_new_password'] = 'New passwords do not match.';
            }
            // Nếu không có lỗi nào liên quan đến mật khẩu, đánh dấu để cập nhật
            if (empty($data['errors']['current_password']) && empty($data['errors']['new_password']) && empty($data['errors']['confirm_new_password'])) {
                $updatePassword = true;
            }
        }

        // --- BƯỚC 3: THỰC THI CẬP NHẬT NẾU KHÔNG CÓ LỖI ---
        if (empty($data['errors'])) {
            // 3.1. Chuẩn bị dữ liệu để truyền vào Model
            $dataForDb = [
                'user_id' => $userId,
                'FullName' => $input['FullName'],
                'PhoneNumber' => $input['PhoneNumber'],
                'SpecializationID' => $input['specialization_id'],
                'ExperienceYears' => $input['experience_years'],
                'DoctorBio' => $input['doctor_bio'],
            ];
            if ($avatarUpdated) {
                $dataForDb['Avatar'] = $avatarPath;
            }
            if ($updatePassword) {
                $dataForDb['NewPassword'] = password_hash($input['new_password'], PASSWORD_DEFAULT);
            }

            // 3.2. Gọi hàm model để cập nhật
            if ($this->doctorModel->updateDoctorProfile($dataForDb)) {
                // Cập nhật thành công!
                // Đặt thông báo thành công vào session
                $_SESSION['profile_message_success'] = 'Profile updated successfully!';
                
                // Cập nhật lại thông tin trong session đang hoạt động
                $_SESSION['user_fullname'] = $input['FullName'];
                if ($avatarUpdated) {
                    $_SESSION['user_avatar'] = $avatarPath;
                }
                
                // Chuyển hướng lại chính trang này để xóa dữ liệu POST (mẫu PRG)
                header('Location: ' . BASE_URL . '/doctor/updateprofile');
                exit();
            } else {
                // Lỗi khi cập nhật vào CSDL
                // Xóa file đã upload nếu có
                if ($avatarUpdated && file_exists(PUBLIC_PATH . $avatarPath)) {
                    unlink(PUBLIC_PATH . $avatarPath);
                }
                $data['profile_message_error'] = 'Database update failed. Please contact support.';
            }
        }
        // Nếu có lỗi validation, không làm gì cả, code sẽ tự động chạy xuống phần nạp view và hiển thị các lỗi trong $data['errors'].
    }

    // --- BƯỚC 4: NẠP FILE VIEW ---
    // Dòng code này được thực thi trong 2 trường hợp:
    // 1. Khi người dùng truy cập trang bằng phương thức GET.
    // 2. Khi người dùng submit form (POST) và có lỗi validation.
    require_once APPROOT . '/views/doctor/update_profile.php';
}
}
?>