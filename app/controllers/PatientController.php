<?php
// app/controllers/PatientController.php

class PatientController {
    private $userModel;
    private $patientModel;
    private $doctorModel;
    private $doctorAvailabilityModel;
    private $appointmentModel;
    private $medicalRecordModel;
    private $prescriptionModel;
    private $db; 

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (file_exists(__DIR__ . '/../helpers/csrf_helper.php')) {
            require_once __DIR__ . '/../helpers/csrf_helper.php';
        }

        // <<<< "BẢO VỆ" THÔNG MINH HƠN Ở ĐÂY NÈ CẬU >>>>
        $urlPath = $_GET['url'] ?? '';
        $urlParts = explode('/', rtrim($urlPath, '/'));
        $currentAction = $urlParts[1] ?? 'dashboard'; 

        // Define public actions that don't require a patient to be logged in
        $publicActions = ['register']; 
        
        if (!in_array($currentAction, $publicActions)) {
            // For all other actions, ensure the user is a logged-in patient
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
                $_SESSION['error_message'] = "Access denied. Please log in as a patient.";
                header('Location: ' . BASE_URL . '/auth/login');
                exit();
            }
        }
        // <<<< KẾT THÚC PHẦN "BẢO VỆ" >>>>

        $this->userModel = new UserModel();
        $this->patientModel = new PatientModel();
        $this->doctorModel = new DoctorModel();
        $this->doctorAvailabilityModel = new DoctorAvailabilityModel();
        $this->appointmentModel = new AppointmentModel();
        $this->medicalRecordModel = new MedicalRecordModel();
        $this->prescriptionModel = new PrescriptionModel();
        $this->db = Database::getInstance(); 
    }
    
    private function getPatientIdFromSession() {
        if (!isset($_SESSION['user_id'])) {
            // This case should be caught by the constructor check, but as a fallback
            $_SESSION['error_message'] = "Session not found. Please log in.";
            header('Location: ' . BASE_URL . '/auth/login'); 
            exit();
        }
        $patientInfo = $this->patientModel->getPatientByUserId($_SESSION['user_id']);
        if (!$patientInfo || !isset($patientInfo['PatientID'])) {
            $_SESSION['error_message'] = "Your patient profile could not be found.";
            header('Location: ' . BASE_URL . '/patient/dashboard'); 
            exit();
        }
        return $patientInfo['PatientID'];
    }

    protected function view($view, $data = []) {
        // Add user info to data for the layout
        if (!isset($data['currentUser'])) {
            $data['currentUser'] = $this->userModel->findUserById($_SESSION['user_id'] ?? 0);
        }
        $data['userRole'] = $_SESSION['user_role'] ?? '';

        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist, my dear.");
        }
    }

    public function register() {
        if (isset($_SESSION['user_id'])) { 
            header('Location: ' . BASE_URL . '/patient/dashboard');
            exit();
        }

        $data = ['title' => 'Patient Registration', 'input' => [], 'errors' => []];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $data['error_message'] = 'Invalid form submission. Please try again.';
                $this->view('patient/register', $data);
                return;
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $data['input'] = [
                'fullname' => trim($_POST['fullname'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
                'phone_number' => trim($_POST['phone_number'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
                'gender' => trim($_POST['gender'] ?? '')
            ];

            if (empty($data['input']['fullname'])) $data['errors']['fullname'] = 'Full name is required.';
            if (empty($data['input']['username'])) $data['errors']['username'] = 'Username is required.';
            elseif (strlen($data['input']['username']) < 4) $data['errors']['username'] = 'Username must be at least 4 characters.';
            elseif ($this->userModel->findUserByUsername($data['input']['username'])) $data['errors']['username'] = 'Username already taken.';
            if (empty($data['input']['email'])) $data['errors']['email'] = 'Email is required.';
            elseif (!filter_var($data['input']['email'], FILTER_VALIDATE_EMAIL)) $data['errors']['email'] = 'Invalid email format.';
            elseif ($this->userModel->findUserByEmail($data['input']['email'])) $data['errors']['email'] = 'Email already registered.';
            if (empty($data['input']['password'])) $data['errors']['password'] = 'Password is required.';
            elseif (strlen($data['input']['password']) < 6) $data['errors']['password'] = 'Password must be at least 6 characters.';
            if ($data['input']['password'] !== $data['input']['confirm_password']) $data['errors']['confirm_password'] = 'Passwords do not match.';
            if (!empty($data['input']['phone_number']) && !preg_match('/^[0-9]{10,15}$/', $data['input']['phone_number'])) $data['errors']['phone_number'] = 'Invalid phone number.';

            if (empty($data['errors'])) {
                $data['input']['password_hash'] = password_hash($data['input']['password'], PASSWORD_DEFAULT);
                
                $this->db->beginTransaction();
                try {
                    $userData = [
                        'Username' => $data['input']['username'], 'PasswordHash' => $data['input']['password_hash'],
                        'Email' => $data['input']['email'], 'FullName' => $data['input']['fullname'], 'Role' => 'Patient',
                        'PhoneNumber' => $data['input']['phone_number'] ?: null, 'Address' => $data['input']['address'] ?: null, 'Status' => 'Active'
                    ];
                    $newUserId = $this->userModel->createUser($userData);
                    if ($newUserId) {
                        $patientData = [
                            'UserID' => $newUserId,
                            'DateOfBirth' => !empty($data['input']['date_of_birth']) ? $data['input']['date_of_birth'] : null,
                            'Gender' => !empty($data['input']['gender']) ? $data['input']['gender'] : null
                        ];
                        if ($this->patientModel->createPatient($patientData)) {
                            $this->db->commit();
                            $_SESSION['success_message'] = 'Registration successful! You can now log in.';
                            header('Location: ' . BASE_URL . '/auth/login');
                            exit();
                        } else { $this->db->rollBack(); $data['error_message'] = 'Failed to create patient profile.'; }
                    } else { $this->db->rollBack(); $data['error_message'] = 'Failed to create user account.'; }
                } catch (Exception $e) {
                    $this->db->rollBack(); error_log("Patient Reg Error: " . $e->getMessage());
                    $data['error_message'] = 'A system error occurred. Please try again later.';
                }
            }
        }
        $this->view('patient/register', $data);
    }

    public function dashboard() {
        $patientId = $this->getPatientIdFromSession(); 

        $data = [
            'title' => 'Patient Dashboard',
            'welcome_message' => 'Welcome ' . htmlspecialchars($_SESSION['user_fullname'] ?? '') . '!',
            'browse_doctors_link' => BASE_URL . '/patient/browseDoctors',
            'upcoming_appointments_count' => $this->appointmentModel->getUpcomingAppointmentsCountForPatient($patientId),
            'active_prescriptions_count' => $this->prescriptionModel->getActivePrescriptionsCountForPatient($patientId),
            'todays_appointments' => $this->appointmentModel->getTodaysAppointmentsForPatient($patientId),
            'recent_chats_count' => 0, 
        ];
        $this->view('patient/dashboard', $data);
    }

    public function browseDoctors() {
        $doctors = $this->doctorModel->getAllActiveDoctorsWithSpecialization();
        $data = ['title' => 'Browse Doctors', 'doctors' => $doctors];
        $this->view('patient/browse_doctors', $data);
    }

    public function getDoctorAvailability($doctorId = 0) {
        header('Content-Type: application/json');
        $doctorId = (int)$doctorId;
        if ($doctorId <= 0) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'Invalid Doctor ID.']); exit; }
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+7 days'));
        $availableSlots = $this->doctorAvailabilityModel->getAvailableSlotsByDoctorId($doctorId, $startDate, $endDate);
        
        echo json_encode(['success' => ($availableSlots !== false), 'slots' => $availableSlots ?? [], 'message' => ($availableSlots === false) ? 'Could not retrieve availability.' : null]);
        exit;
    }

    public function myAppointments() {
        $patientId = $this->getPatientIdFromSession();
        $statusFilter = $_GET['status'] ?? 'All';
        $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
        if (!in_array($statusFilter, $validStatuses)) $statusFilter = 'All';
        
        $appointments = $this->appointmentModel->getAppointmentsByPatientId($patientId, $statusFilter);
        $data = [
            'title' => 'My Appointments', 'appointments' => $appointments,
            'currentFilter' => $statusFilter, 'allStatuses' => $validStatuses
        ];
        $this->view('patient/my_appointments', $data);
    }

    public function viewAppointmentSummary($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0) { $_SESSION['error_message'] = "Invalid appointment ID."; header('Location: ' . BASE_URL . '/patient/myAppointments'); exit(); }
        
        $patientId = $this->getPatientIdFromSession();
        $summaryData = $this->medicalRecordModel->getAppointmentSummaryForPatient($appointmentId, $patientId);

        if (!$summaryData || !$summaryData['appointment']) {
            $_SESSION['summary_message_error'] = 'Appointment not found or access denied.';
            header('Location: ' . BASE_URL . '/patient/myAppointments'); exit();
        }
        
        $data = [
            'title' => 'Appointment Summary - ' . htmlspecialchars(date('M j, Y', strtotime($summaryData['appointment']['AppointmentDateTime']))),
            'appointment' => $summaryData['appointment'],
            'medicalRecord' => $summaryData['medicalRecord'],
            'prescriptions' => $summaryData['prescriptions']
        ];
        $this->view('patient/appointment_summary', $data);
    }

    public function viewAllMedicalRecords() {
        $patientId = $this->getPatientIdFromSession();
        $filterDoctorId = filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT);
        $filterDateRange = filter_input(INPUT_GET, 'date_range', FILTER_SANITIZE_SPECIAL_CHARS);

        $medicalHistory = $this->medicalRecordModel->getMedicalHistoryByPatientId($patientId, null, $filterDoctorId, $filterDateRange);
        $doctorsConsulted = $this->medicalRecordModel->getDoctorsConsultedByPatient($patientId);
        
        $data = [
            'title' => 'My Medical Records', 'medicalHistory' => $medicalHistory,
            'filterOptions' => [
                'doctors' => $doctorsConsulted,
                'date_ranges' => ['all' => 'All Time', 'last_month' => 'Last Month', 'last_3_months' => 'Last 3 Months', 'last_6_months' => 'Last 6 Months', 'last_year' => 'Last Year']
            ],
            'currentFilters' => ['doctor_id' => $filterDoctorId, 'date_range' => $filterDateRange ?? 'all']
        ];
        $this->view('patient/all_medical_records', $data);
    }

    public function updateProfile() {
        $userId = $_SESSION['user_id']; 
        $patientSpecificId = $this->getPatientIdFromSession(); 

        $patientDetails = $this->patientModel->getPatientDetailsById($patientSpecificId); 
        if (!$patientDetails) { $_SESSION['profile_message_error'] = 'Could not retrieve profile.'; header('Location: ' . BASE_URL . '/patient/dashboard'); exit(); }
        
        $currentUserData = $this->userModel->findUserById($userId);
        if ($currentUserData) $patientDetails['PasswordHash'] = $currentUserData['PasswordHash'];
        else { $_SESSION['profile_message_error'] = 'Auth details not found.'; header('Location: ' . BASE_URL . '/patient/dashboard'); exit(); }

        $data = ['title' => 'Update My Profile', 'patient' => $patientDetails, 'input' => (array)$patientDetails, 'errors' => []];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $input = [
                'FullName' => trim($_POST['FullName'] ?? ''), 'Email' => trim($_POST['Email'] ?? ''),
                'PhoneNumber' => trim($_POST['PhoneNumber'] ?? ''), 'Address' => trim($_POST['Address'] ?? ''),
                'DateOfBirth' => trim($_POST['DateOfBirth'] ?? ''), 'Gender' => $_POST['Gender'] ?? '',
                'BloodType' => trim($_POST['BloodType'] ?? ''), 'InsuranceInfo' => trim($_POST['InsuranceInfo'] ?? ''),
                'MedicalHistorySummary' => trim($_POST['MedicalHistorySummary'] ?? ''),
                'current_password' => $_POST['current_password'] ?? '', 'new_password' => $_POST['new_password'] ?? '',
                'confirm_new_password' => $_POST['confirm_new_password'] ?? ''
            ];
            $data['input'] = array_merge((array)$patientDetails, $input); 

            $avatarPath = $patientDetails['Avatar']; 
            $avatarUpdated = false;

            if (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES['profile_avatar'];
                $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($fileExt, $allowedExt) && $file['size'] < 5000000) { 
                    $newFileName = "avatar_" . $userId . "_" . uniqid('', true) . "." . $fileExt;
                    $uploadDir = rtrim(PUBLIC_PATH, '/') . '/uploads/avatars/'; 
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
                    
                    $fileDestinationOnServer = $uploadDir . $newFileName;
                    $avatarPathForDb = 'uploads/avatars/' . $newFileName; 

                    if (move_uploaded_file($file['tmp_name'], $fileDestinationOnServer)) {
                        if (!empty($patientDetails['Avatar']) && $patientDetails['Avatar'] !== 'assets/images/default_avatar.png' && file_exists(PUBLIC_PATH . $patientDetails['Avatar'])) {
                            unlink(PUBLIC_PATH . $patientDetails['Avatar']);
                        }
                        $avatarPath = $avatarPathForDb;
                        $avatarUpdated = true;
                    } else { $data['errors']['profile_avatar'] = "Failed to move uploaded file."; }
                } else { $data['errors']['profile_avatar'] = $file['size'] >= 5000000 ? "File too large." : "Invalid file type."; }
            } elseif (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] != UPLOAD_ERR_NO_FILE) {
                $data['errors']['profile_avatar'] = "Upload error code: " . $_FILES['profile_avatar']['error'];
            }

            if (empty($input['FullName'])) $data['errors']['FullName'] = 'Full name required.';
            if (empty($input['Email'])) $data['errors']['Email'] = 'Email required.';
            elseif (!filter_var($input['Email'], FILTER_VALIDATE_EMAIL)) $data['errors']['Email'] = 'Invalid email.';
            elseif ($this->userModel->findUserByEmail($input['Email'], $userId)) $data['errors']['Email'] = 'Email already taken by another user.';

            $updatePassword = false;
            if (!empty($input['new_password'])) {
                if (empty($input['current_password'])) $data['errors']['current_password'] = 'Current password needed to change.';
                elseif (!password_verify($input['current_password'], $patientDetails['PasswordHash'])) $data['errors']['current_password'] = 'Incorrect current password.';
                if (strlen($input['new_password']) < 6) $data['errors']['new_password'] = 'New password too short (min 6).';
                if ($input['new_password'] !== $input['confirm_new_password']) $data['errors']['confirm_new_password'] = 'New passwords mismatch.';
                if (empty($data['errors']['current_password']) && empty($data['errors']['new_password']) && empty($data['errors']['confirm_new_password'])) $updatePassword = true;
            }

            if (empty($data['errors'])) {
                $this->db->beginTransaction();
                try {
                    $userDataToUpdate = ['FullName' => $input['FullName'], 'Email' => $input['Email'], 'PhoneNumber' => $input['PhoneNumber'], 'Address' => $input['Address']];
                    if ($avatarUpdated) $userDataToUpdate['Avatar'] = $avatarPath;
                    
                    $userUpdateSuccess = $this->userModel->updateUser($userId, $userDataToUpdate);
                    
                    $patientDataToUpdate = [
                        'DateOfBirth' => !empty($input['DateOfBirth']) ? $input['DateOfBirth'] : null, 'Gender' => $input['Gender'],
                        'BloodType' => $input['BloodType'], 'InsuranceInfo' => $input['InsuranceInfo'],
                        'MedicalHistorySummary' => $input['MedicalHistorySummary']
                    ];
                    $patientUpdateSuccess = $this->patientModel->updatePatient($patientSpecificId, $patientDataToUpdate);

                    $passwordUpdateSuccess = true;
                    if ($updatePassword) {
                        $newPasswordHash = password_hash($input['new_password'], PASSWORD_DEFAULT);
                        $passwordUpdateSuccess = $this->userModel->updatePassword($userId, $newPasswordHash);
                    }

                    if ($userUpdateSuccess && $patientUpdateSuccess && $passwordUpdateSuccess) {
                        $this->db->commit();
                        $_SESSION['profile_message_success'] = 'Profile updated successfully';
                        $_SESSION['user_fullname'] = $input['FullName']; 
                        if ($avatarUpdated) $_SESSION['user_avatar'] = $avatarPath;
                        header('Location: ' . BASE_URL . '/patient/updateProfile'); exit();
                    } else {
                        $this->db->rollBack();
                        if ($avatarUpdated && file_exists(PUBLIC_PATH . $avatarPath)) unlink(PUBLIC_PATH . $avatarPath); 
                        $data['profile_message_error'] = 'Database update failed. Try again.';
                    }
                } catch (Exception $e) {
                    $this->db->rollBack(); error_log("Profile Update Error: " . $e->getMessage());
                    if ($avatarUpdated && file_exists(PUBLIC_PATH . $avatarPath)) unlink(PUBLIC_PATH . $avatarPath);
                    $data['profile_message_error'] = 'An error occurred: ' . $e->getMessage();
                }
            }
        }
        
        $updatedPatientDetails = $this->patientModel->getPatientDetailsById($patientSpecificId);
        if ($updatedPatientDetails) { 
            $data['patient'] = array_merge($updatedPatientDetails, $data['input']);
            $data['patient']['Avatar'] = $_SESSION['user_avatar'] ?? $updatedPatientDetails['Avatar']; 
        }

        $this->view('patient/update_profile', $data);
    }
}
?>