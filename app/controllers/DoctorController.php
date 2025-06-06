<?php
// app/controllers/DoctorController.php

class DoctorController {
    private $doctorModel;
    private $appointmentModel;
    private $doctorAvailabilityModel;
    private $feedbackModel;
    private $patientModel;
    private $userModel;
    private $notificationModel;
    private $leaveRequestModel;
    private $medicalRecordModel; // Assuming this is added for EMR
    private $prescriptionModel;  // Assuming this is added for EMR
    private $medicineModel;      // Assuming this is added for EMR
    private $db;                 // Assuming this is added for transactions

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (file_exists(__DIR__ . '/../helpers/csrf_helper.php')) {
            require_once __DIR__ . '/../helpers/csrf_helper.php';
        }
        
        $urlPath = $_GET['url'] ?? '';
        $urlParts = explode('/', rtrim($urlPath, '/'));
        $controllerNameFromUrl = $urlParts[0] ?? '';
        $currentAction = $urlParts[1] ?? 'dashboard'; 

        if (strtolower($controllerNameFromUrl) === 'doctor') {
            $publicActions = []; 
            if (!in_array($currentAction, $publicActions)) {
                if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
                    $_SESSION['error_message'] = "Access denied. Please log in as a Doctor.";
                    header('Location: ' . BASE_URL . '/auth/login');
                    exit();
                }
            }
        }
        
        try {
            $this->doctorModel = new DoctorModel();
            $this->appointmentModel = new AppointmentModel();
            $this->doctorAvailabilityModel = new DoctorAvailabilityModel();
            $this->feedbackModel = new FeedbackModel();
            $this->notificationModel = new NotificationModel(); 
            $this->patientModel = new PatientModel();
            $this->userModel = new UserModel();
            $this->leaveRequestModel = new LeaveRequestModel();
            // Assuming these are needed for the EMR saving logic
            $this->medicalRecordModel = new MedicalRecordModel();
            $this->prescriptionModel = new PrescriptionModel();
            $this->medicineModel = new MedicineModel();
            $this->db = Database::getInstance();
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
        $doctorInfo = $this->doctorModel->getDoctorByUserId($_SESSION['user_id']);
        if (!$doctorInfo || !isset($doctorInfo['DoctorID'])) {
            $_SESSION['error_message'] = "Doctor profile not found. Please contact administrator.";
            error_log("Doctor profile (DoctorID) missing for UserID: " . $_SESSION['user_id'] . ". Logging out.");
            unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_fullname'], $_SESSION['user_avatar']);
            header('Location: ' . BASE_URL . '/auth/login?error=doctor_profile_missing'); 
            exit();
        }
        return (int)$doctorInfo['DoctorID'];
    }

    protected function view($view, $data = []) {
        if (!isset($data['currentUser'])) {
            $data['currentUser'] = [
                'UserID' => $_SESSION['user_id'] ?? null,
                'FullName' => $_SESSION['user_fullname'] ?? 'Doctor',
                'Role' => $_SESSION['user_role'] ?? null,
                'Avatar' => $_SESSION['user_avatar'] ?? null
            ];
        }
        if (!isset($data['title'])) {
            $data['title'] = 'Doctor Panel'; 
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
    }
    public function dashboard() {
        $doctorId = $this->getLoggedInDoctorId();
        $appointmentOverviewData = ['labels' => [], 'counts' => []];
        for ($i = 3; $i >= 0; $i--) {
            $weekStartDate = date('Y-m-d', strtotime("monday this week -{$i} weeks"));
            $appointmentOverviewData['labels'][] = "Week " . date('W', strtotime($weekStartDate));
            $appointmentOverviewData['counts'][] = rand(5, 25); 
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
        $currentDateFilterForView = $dateFilterInput; 

        switch ($dateFilterInput) {
            case 'today': $dateRangeFilter['specific_date'] = date('Y-m-d'); break;
            case 'this_week': 
                $dateRangeFilter['start_date'] = date('Y-m-d', strtotime('monday this week')); 
                $dateRangeFilter['end_date'] = date('Y-m-d', strtotime('sunday this week')); 
                break;
            case 'all_upcoming': $dateRangeFilter['type'] = 'all_upcoming'; break;
            case 'all_time': break;
            default:
                if (DateTime::createFromFormat('Y-m-d', $dateFilterInput) !== false) {
                    $dateRangeFilter['specific_date'] = $dateFilterInput;
                } else { 
                    $currentDateFilterForView = 'all_upcoming'; 
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
        $viewStartDate = $_GET['start'] ?? date('Y-m-01'); 
        $viewEndDate = $_GET['end'] ?? date('Y-m-t', strtotime($viewStartDate . ' +2 months')); 
        
        $data = [
            'title' => 'Manage Availability', 
            'slotsForCalendar' => [], 
            'currentMonthYearForMiniCal' => date('F Y', strtotime($viewStartDate))
        ];
        $this->view('doctor/manage_availability', $data);
    }
    
    public function getAvailabilityEvents() {
        header('Content-Type: application/json');
        $doctorId = $this->getLoggedInDoctorId();
        $startParam = $_GET['start'] ?? null; 
        $endParam = $_GET['end'] ?? null;  
        
        $startDate = $startParam ? date('Y-m-d', strtotime($startParam)) : date('Y-m-01');
        $endDate = $endParam ? date('Y-m-d', strtotime($endParam . ' -1 day')) : date('Y-m-t', strtotime($startDate . ' +2 months')); 

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
                $db = Database::getInstance(); 
                if ($db->rowCount() > 0) {
                    $this->sendJsonResponse(true, 'Slot deleted successfully.');
                } else {
                    $this->sendJsonResponse(false, 'Slot not found, does not belong to you, or is booked and cannot be deleted.', 404);
                }
            } else {
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
        $input = json_decode(file_get_contents('php://input'), true); 
        
        $eventId = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
        $newStartStr = filter_var($input['start'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $newEndStr = filter_var($input['end'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$eventId || !$newStartStr || !$newEndStr) { $this->sendJsonResponse(false, 'Missing required data (id, start, end).', 400); exit(); }
        
        try {
            $newStartDate = date('Y-m-d', strtotime($newStartStr)); 
            $newStartTime = date('H:i:s', strtotime($newStartStr)); 
            $newEndTime = date('H:i:s', strtotime($newEndStr));
            
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
        
        $patients = $this->patientModel->getAllPatientsWithDetails($searchTerm); 

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
                'labels' => ['Drug A', 'Drug B', 'Drug C'], 
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
            'phone_number' => trim($_POST['phone_number'] ?? null), 
            'address' => trim($_POST['address'] ?? null), 
            'date_of_birth' => trim($_POST['date_of_birth'] ?? null), 
            'gender' => trim($_POST['gender'] ?? null) 
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
                'PhoneNumber' => $input['phone_number'] ?: null, 
                'Address' => $input['address'] ?: null, 
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
            'input' => $_SESSION['leave_request_input'] ?? [], 
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
        if (strlen($reason) > 1000) { 
            $errors['reason'] = 'Reason is too long (max 1000 characters).';
        }

        $overlappingAppointments = [];
        $overlappingAvailability = [];
        if (empty($errors) && !empty($startDate) && !empty($endDate)) {
            $overlappingAvailability = $this->leaveRequestModel->getOverlappingAvailability($doctorId, $startDate, $endDate);
            $overlappingAppointments = $this->leaveRequestModel->getOverlappingAppointments($doctorId, $startDate, $endDate);

            if (!empty($overlappingAvailability) || !empty($overlappingAppointments)) {
                $_SESSION['leave_request_warning'] = "Warning: Your requested time off overlaps with existing scheduled work or appointments. Please review them. Submitting will still create a pending request.";
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
            'Status' => 'Pending' 
        ];

        if ($this->leaveRequestModel->createLeaveRequest($leaveData)) {
            $_SESSION['success_message'] = 'Your leave request has been submitted successfully and is pending approval.';
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
        $statusFilter = $_GET['status'] ?? null;
        
        $leaveRequests = $this->leaveRequestModel->getLeaveRequestsByDoctorId($doctorId, $statusFilter);
        $data = [
            'title' => 'My Leave Requests',
            'leaveRequests' => $leaveRequests,
            'currentStatusFilter' => $statusFilter,
            'allStatuses' => ['Pending', 'Approved', 'Rejected', 'Cancelled'] 
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

        if ($this->leaveRequestModel->cancelLeaveRequestByDoctor($leaveRequestId, $doctorId)) {
            $_SESSION['success_message'] = 'Leave request cancelled successfully.';
        } else {
            $_SESSION['error_message'] = 'Failed to cancel leave request. It might have already been processed or does not belong to you.';
        }
        header('Location: ' . BASE_URL . '/doctor/myLeaveRequests');
        exit();
    }

    // <<<< GIẢ SỬ ĐÂY LÀ PHƯƠNG THỨC LƯU BỆNH ÁN VÀ ĐƠN THUỐC CỦA CẬU >>>>
    public function saveConsultationAndPrescription() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Handle error, maybe redirect or send JSON response
            header('Location: ' . BASE_URL . '/doctor/mySchedule');
            exit();
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['consultation_message_error'] = 'Invalid CSRF token.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule'));
            exit();
        }

        $doctorId = $this->getLoggedInDoctorId();
        $appointmentId = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);
        
        if (!$appointmentDetails || $appointmentDetails['DoctorID'] != $doctorId) {
            $_SESSION['consultation_message_error'] = 'Unauthorized action or appointment not found.';
            header('Location: ' . BASE_URL . '/doctor/mySchedule');
            exit();
        }
        
        $patientId = $appointmentDetails['PatientUserID']; // Lấy UserID của bệnh nhân từ chi tiết lịch hẹn
        $symptoms = trim($_POST['symptoms'] ?? '');
        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $treatmentPlan = trim($_POST['treatment_plan'] ?? '');
        $notes = trim($_POST['consultation_notes'] ?? '');
        $prescriptionsArray = $_POST['prescriptions'] ?? []; 

        $this->db->beginTransaction();
        try {
            $medicalRecordData = [
                'AppointmentID' => $appointmentId,
                'PatientID' => $patientId,
                'DoctorID' => $doctorId,
                'VisitDate' => $appointmentDetails['AppointmentDateTime'],
                'Symptoms' => $symptoms,
                'Diagnosis' => $diagnosis,
                'TreatmentPlan' => $treatmentPlan,
                'Notes' => $notes,
            ];
            
            $existingRecord = $this->medicalRecordModel->getRecordByAppointmentId($appointmentId);
            $recordId = $existingRecord ? $existingRecord['RecordID'] : null;
            
            if ($recordId) {
                $this->medicalRecordModel->updateMedicalRecord($recordId, $medicalRecordData);
            } else {
                $recordId = $this->medicalRecordModel->createMedicalRecord($medicalRecordData);
                if (!$recordId) throw new Exception("Failed to create medical record.");
            }

            $this->prescriptionModel->deletePrescriptionsByRecordId($recordId);

            foreach ($prescriptionsArray as $item) {
                $prescriptionData = [
                    'RecordID' => $recordId,
                    'MedicineID' => $item['medicine_id'],
                    'Dosage' => $item['dosage'] ?? '',
                    'Frequency' => $item['frequency'] ?? '',
                    'Duration' => $item['duration'] ?? '',
                    'Instructions' => $item['instructions'] ?? ''
                ];
                if (!$this->prescriptionModel->addPrescriptionItem($prescriptionData)) {
                    throw new Exception("Failed to add prescription item for medicine ID " . $item['medicine_id']);
                }
                
                if (isset($item['quantity_to_dispense']) && $item['quantity_to_dispense'] > 0) {
                    $decreaseResult = $this->medicineModel->decreaseStock($item['medicine_id'], $item['quantity_to_dispense']);
                    if (!$decreaseResult['success']) {
                        throw new Exception($decreaseResult['message'] . " (Medicine ID: " . $item['medicine_id'] . ")");
                    }
                }
            }
            
            $this->appointmentModel->updateAppointmentStatus($appointmentId, 'Completed');

            $notificationData = [
                'UserID' => $patientId,
                'Type' => 'EMR_UPDATED',
                'Message' => 'Your consultation summary with Dr. ' . $_SESSION['user_fullname'] . ' on ' . date('M j, Y') . ' is now available for viewing.',
                'Link' => '/patient/viewAppointmentSummary/' . $appointmentId,
                'RelatedEntityID' => $appointmentId,
                'EntityType' => 'appointment'
            ];
            $this->notificationModel->createNotification($notificationData);

            $this->db->commit();
            $_SESSION['consultation_message_success'] = 'Consultation details and prescription saved successfully!';
            header('Location: ' . BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appointmentId);
            exit();

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error in saveConsultationAndPrescription: " . $e->getMessage());
            $_SESSION['consultation_message_error'] = "Database error: " . $e->getMessage();
            $_SESSION['emr_form_input'] = $_POST; 
            header('Location: ' . BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appointmentId);
            exit();
        }
    }

    public function markAsCompleted() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['schedule_message_error'] = 'Invalid request method.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule'));
            exit();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
            $_SESSION['schedule_message_error'] = 'Unauthorized action.';
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['schedule_message_error'] = 'Invalid CSRF token. Action aborted.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule'));
            exit();
        }

        $appointmentId = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
        $doctorId = $this->getLoggedInDoctorId(); // Lấy DoctorID của người đang đăng nhập

        if (!$appointmentId || $appointmentId <= 0) {
            $_SESSION['schedule_message_error'] = 'Invalid Appointment ID.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule'));
            exit();
        }

        try {
            $appointmentDetails = $this->appointmentModel->getAppointmentById($appointmentId);

            if (!$appointmentDetails) {
                throw new Exception('Appointment not found.');
            }

            // Kiểm tra xem bác sĩ có đúng là người phụ trách lịch hẹn này không
            if ($appointmentDetails['DoctorID'] != $doctorId) {
                throw new Exception('You are not authorized to modify this appointment.');
            }

            if (!in_array($appointmentDetails['Status'], ['Scheduled', 'Confirmed'])) {
                throw new Exception('This appointment cannot be marked as completed (current status: ' . htmlspecialchars($appointmentDetails['Status']) . ').');
            }

            if ($this->appointmentModel->updateAppointmentStatus($appointmentId, 'Completed')) {
                $_SESSION['schedule_message_success'] = 'Appointment #' . $appointmentId . ' marked as completed successfully.';
            } else {
                throw new Exception('Failed to update appointment status in the database.');
            }

        } catch (Exception $e) {
            error_log("Error marking appointment as completed by DoctorID {$doctorId}: " . $e->getMessage());
            $_SESSION['schedule_message_error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/doctor/mySchedule'));
        exit();
    }
}
?>