<?php
// app/controllers/NurseController.php

class NurseController {
    private $userModel;
    private $appointmentModel;
    private $vitalSignModel;
    private $patientModel;
    private $doctorNurseAssignmentModel;
    private $nurseModel;
    private $medicalRecordModel;
    private $doctorModel; // <<<< THÊM DOCTOR MODEL VÀO ĐÂY NHA CẬU
    private $db; 

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (file_exists(__DIR__ . '/../helpers/csrf_helper.php')) {
            require_once __DIR__ . '/../helpers/csrf_helper.php';
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
            $_SESSION['error_message'] = "Unauthorized access. Please log in as a Nurse.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        try {
            $this->userModel = new UserModel();
            $this->appointmentModel = new AppointmentModel();
            $this->vitalSignModel = new VitalSignModel();
            $this->patientModel = new PatientModel();
            $this->doctorNurseAssignmentModel = new DoctorNurseAssignmentModel();
            $this->nurseModel = new NurseModel();
            $this->medicalRecordModel = new MedicalRecordModel();
            $this->doctorModel = new DoctorModel(); // <<<< KHỞI TẠO DOCTOR MODEL
            $this->db = Database::getInstance();
        } catch (Error $e) {
            error_log("FATAL: Model initialization error in NurseController: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            die("A critical error occurred during application setup. Please check the server logs or contact support. Error details: " . htmlspecialchars($e->getMessage()));
        }
    }

    protected function view($view, $data = []) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
            $_SESSION['error_message'] = "Session invalid or role mismatch. Please log in again.";
            header('Location: ' . BASE_URL . '/auth/login?error=session_issue_nurse');
            exit();
        }
        
        $currentUserDataFromDB = $this->userModel->findUserById($_SESSION['user_id']);

        if (!isset($data['currentUser'])) {
            $data['currentUser'] = [
                'UserID' => $_SESSION['user_id'],
                'FullName' => $currentUserDataFromDB['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Nurse'),
                'Role' => $_SESSION['user_role'],
                'Avatar' => $currentUserDataFromDB['Avatar'] ?? ($_SESSION['user_avatar'] ?? null)
            ];
        } else { 
            $data['currentUser']['FullName'] = $data['currentUser']['FullName'] ?? $currentUserDataFromDB['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Nurse');
            $data['currentUser']['Avatar'] = $data['currentUser']['Avatar'] ?? $currentUserDataFromDB['Avatar'] ?? ($_SESSION['user_avatar'] ?? null);
        }
        $data['userRole'] = 'Nurse'; 

        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            error_log("NurseController: View '{$view}' does not exist.");
            die("View '{$view}' does not exist for Nurse, sweetie. Please check the path.");
        }
    }

    private function getCurrentNurseProfile() {
        if (!isset($_SESSION['user_id'])) { 
            error_log("NurseController::getCurrentNurseProfile - UserID not set in session.");
            return null;
        }
        $nurseData = $this->nurseModel->getNurseByUserId($_SESSION['user_id']); 
        if ($nurseData && isset($nurseData['NurseID'])) {
            $_SESSION['user_fullname'] = $nurseData['FullName']; 
            $_SESSION['user_avatar'] = $nurseData['Avatar'];     
            return $nurseData; 
        }
        return null;
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        $nurseProfile = $this->getCurrentNurseProfile();
        $nurseFullNameDisplay = $nurseProfile['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Nurse');

        $data = [
            'title' => 'Nurse Dashboard',
            'upcoming_appointments' => [],
            'welcome_message' => 'Welcome back, ' . htmlspecialchars($nurseFullNameDisplay) . '!'
        ];
        
        if (!$nurseProfile) {
            $_SESSION['error_message'] = "Could not retrieve your nurse profile. Please ensure your nurse record is set up correctly by an administrator.";
            $this->view('nurse/dashboard', $data);
            return;
        }
        $nurseId = $nurseProfile['NurseID'];
        
        $assignedDoctorIds = $this->doctorNurseAssignmentModel->getAssignedDoctorIdsByNurseId($nurseId);

        $today = date('Y-m-d');
        $sevenDaysLater = date('Y-m-d', strtotime($today . ' +7 days'));
        
        if (!empty($assignedDoctorIds)) {
            $data['upcoming_appointments'] = $this->appointmentModel->getUpcomingAppointmentsForNurseDashboard(
                $assignedDoctorIds,
                $today,
                $sevenDaysLater,
                10, 
                ['Scheduled', 'Confirmed'] 
            );
        } else {
            $_SESSION['info_message'] = "You are not currently assigned to any doctors. No upcoming appointments to display.";
        }
        
        $this->view('nurse/dashboard', $data);
    }

    public function listAppointments() {
        $nurseProfile = $this->getCurrentNurseProfile();
        if (!$nurseProfile) {
            $_SESSION['error_message'] = "Nurse profile not found. Cannot list appointments.";
            header('Location: ' . BASE_URL . '/nurse/dashboard'); exit();
        }
        $nurseId = $nurseProfile['NurseID'];
        $assignedDoctorIds = $this->doctorNurseAssignmentModel->getAssignedDoctorIdsByNurseId($nurseId);
        
        $assignedDoctors = [];
        if(!empty($assignedDoctorIds)) {
            $assignedDoctors = $this->doctorModel->getDoctorsByIds($assignedDoctorIds);
        }

        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $status = $_GET['status'] ?? 'All';
        $doctorIdFilter = filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT);

        if (empty($dateFrom) && empty($dateTo) && $status === 'All' && !$doctorIdFilter) { 
            $dateFrom = date('Y-m-d'); 
        }
        
        $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
        if (!in_array($status, $validStatuses)) {
            $status = 'All';
        }

        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'status' => $status,
            'doctor_id' => $doctorIdFilter 
        ];
        
        $appointments = [];
        if (!empty($assignedDoctorIds)) {
            $appointments = $this->appointmentModel->getAppointmentsFilteredForNurse($filters, $assignedDoctorIds);
        } else {
             $_SESSION['info_message'] = "You are not assigned to any doctors, so no appointments can be listed at this time.";
        }

        $data = [
            'title' => 'Manage Appointments',
            'appointments' => $appointments,
            'current_filters' => $filters,
            'all_statuses' => $validStatuses,
            'assigned_doctors' => $assignedDoctors 
        ];
        $this->view('nurse/appointments/list', $data);
    }

    public function appointmentDetails($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0) {
            $_SESSION['error_message'] = "Invalid Appointment ID.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }

        $nurseProfile = $this->getCurrentNurseProfile();
        if (!$nurseProfile) {
            $_SESSION['error_message'] = "Nurse profile not found.";
            header('Location: ' . BASE_URL . '/nurse/dashboard'); exit();
        }
        $assignedDoctorIds = $this->doctorNurseAssignmentModel->getAssignedDoctorIdsByNurseId($nurseProfile['NurseID']);
        
        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);

        if (!$appointmentDetails) {
            $_SESSION['error_message'] = "Appointment #{$appointmentId} not found.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }

        if (empty($assignedDoctorIds) || !isset($appointmentDetails['DoctorID']) || !in_array($appointmentDetails['DoctorID'], $assignedDoctorIds)) {
            $_SESSION['error_message'] = 'You are not authorized to view details for appointment #' . $appointmentId . '.';
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }
        
        $vitals = $this->vitalSignModel->getVitalSignsByAppointmentId($appointmentId);
        $medicalRecord = $this->medicalRecordModel->getRecordByAppointmentId($appointmentId); 
        
        $data = [
            'title' => 'Appointment Details #' . htmlspecialchars($appointmentDetails['AppointmentID']),
            'appointment' => $appointmentDetails,
            'vitals' => $vitals ?? null,
            'medical_record' => $medicalRecord ?? null 
        ];
        $this->view('nurse/appointments/details', $data);
    }

    public function showAddNursingNoteForm($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0) {
            $_SESSION['error_message'] = "Invalid Appointment ID.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }

        $nurseProfile = $this->getCurrentNurseProfile();
        if (!$nurseProfile) { $_SESSION['error_message'] = "Nurse profile not found."; header('Location: ' . BASE_URL . '/nurse/dashboard'); exit(); }
        $assignedDoctorIds = $this->doctorNurseAssignmentModel->getAssignedDoctorIdsByNurseId($nurseProfile['NurseID']);

        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);
        if (!$appointmentDetails || !isset($appointmentDetails['DoctorID'])) {
            $_SESSION['error_message'] = "Appointment not found or missing doctor information.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }

        if (empty($assignedDoctorIds) || !in_array($appointmentDetails['DoctorID'], $assignedDoctorIds)) {
            $_SESSION['error_message'] = 'You are not authorized to add notes for this appointment.';
            header('Location: ' . BASE_URL . '/nurse/appointmentDetails/' . $appointmentId); exit();
        }

        $medicalRecord = $this->medicalRecordModel->getRecordByAppointmentId($appointmentId);
        $currentNursingNotes = $medicalRecord['NursingNotes'] ?? '';
        if (isset($_SESSION['nursing_note_input'])) { 
            $currentNursingNotes = $_SESSION['nursing_note_input'];
        }

        $data = [
            'title' => 'Add/Edit Nursing Note for Appointment #' . $appointmentId,
            'appointment' => $appointmentDetails,
            'input_notes' => $currentNursingNotes,
            'errors' => $_SESSION['nursing_note_errors'] ?? []
        ];
        unset($_SESSION['nursing_note_errors'], $_SESSION['nursing_note_input']);

        $this->view('nurse/nursing_notes/add_edit_form', $data);
    }

    public function saveNursingNote($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }
        
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error_message'] = 'Invalid CSRF token.';
            header('Location: ' . BASE_URL . '/nurse/showAddNursingNoteForm/' . $appointmentId); exit();
        }

        $nurseProfile = $this->getCurrentNurseProfile();
        if (!$nurseProfile) { $_SESSION['error_message'] = "Nurse profile not found."; header('Location: ' . BASE_URL . '/nurse/dashboard'); exit(); }
        $assignedDoctorIds = $this->doctorNurseAssignmentModel->getAssignedDoctorIdsByNurseId($nurseProfile['NurseID']);

        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);
        if (!$appointmentDetails || !isset($appointmentDetails['PatientProfileID']) || !isset($appointmentDetails['DoctorID'])) {
            $_SESSION['error_message'] = 'Appointment data not found or incomplete.';
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }

        if (empty($assignedDoctorIds) || !in_array($appointmentDetails['DoctorID'], $assignedDoctorIds)) {
            $_SESSION['error_message'] = 'You are not authorized to save notes for this appointment.';
            header('Location: ' . BASE_URL . '/nurse/appointmentDetails/' . $appointmentId); exit();
        }

        $nursingNotes = trim($_POST['nursing_notes'] ?? '');
        $errors = [];

        if (strlen($nursingNotes) > 5000) {
            $errors['nursing_notes'] = 'Nursing note is too long (max 5000 characters).';
        }

        if (empty($errors)) {
            $patientId = $appointmentDetails['PatientProfileID']; 
            $doctorId = $appointmentDetails['DoctorID'];
            $recordedByUserId = $_SESSION['user_id'];

            if ($this->medicalRecordModel->saveNursingNotesForAppointment($appointmentId, $nursingNotes, $patientId, $doctorId, $recordedByUserId)) {
                $_SESSION['success_message'] = 'Nursing note saved successfully.';
                header('Location: ' . BASE_URL . '/nurse/appointmentDetails/' . $appointmentId);
                exit();
            } else {
                $_SESSION['error_message'] = 'Failed to save nursing note. Please try again.';
            }
        }

        $_SESSION['nursing_note_errors'] = $errors;
        $_SESSION['nursing_note_input'] = $nursingNotes; 
        header('Location: ' . BASE_URL . '/nurse/showAddNursingNoteForm/' . $appointmentId);
        exit();
    }

    public function showRecordVitalsForm($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0) {
            $_SESSION['error_message'] = "Invalid Appointment ID.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }

        $nurseProfile = $this->getCurrentNurseProfile();
        if (!$nurseProfile) { $_SESSION['error_message'] = "Nurse profile not found."; header('Location: ' . BASE_URL . '/nurse/dashboard'); exit(); }
        $assignedDoctorIds = $this->doctorNurseAssignmentModel->getAssignedDoctorIdsByNurseId($nurseProfile['NurseID']);

        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);
        if (!$appointmentDetails || !isset($appointmentDetails['DoctorID'])) {
            $_SESSION['error_message'] = "Appointment not found or missing doctor information.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }

        if (empty($assignedDoctorIds) || !in_array($appointmentDetails['DoctorID'], $assignedDoctorIds)) {
            $_SESSION['error_message'] = 'You are not authorized to record vitals for this appointment.';
            header('Location: ' . BASE_URL . '/nurse/appointmentDetails/' . $appointmentId); exit();
        }

        $existingVitals = $this->vitalSignModel->getVitalSignsByAppointmentId($appointmentId);
        $data = [
            'title' => 'Record Vital Signs for Appointment #' . $appointmentId,
            'appointment' => $appointmentDetails,
            'input' => $_SESSION['vitals_form_input'] ?? ($existingVitals ?: []),
            'errors' => $_SESSION['vitals_form_errors'] ?? []
        ];
        unset($_SESSION['vitals_form_input'], $_SESSION['vitals_form_errors']);
        $this->view('nurse/vitals/record_form', $data);
    }

    public function saveVitals($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = "Invalid request.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }
        
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error_message'] = 'Invalid CSRF token.';
            header('Location: ' . BASE_URL . '/nurse/showRecordVitalsForm/' . $appointmentId); exit();
        }

        $nurseProfile = $this->getCurrentNurseProfile();
        if (!$nurseProfile) { $_SESSION['error_message'] = "Nurse profile not found."; header('Location: ' . BASE_URL . '/nurse/dashboard'); exit(); }
        $assignedDoctorIds = $this->doctorNurseAssignmentModel->getAssignedDoctorIdsByNurseId($nurseProfile['NurseID']);

        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);
        if (!$appointmentDetails || !isset($appointmentDetails['PatientProfileID']) || !isset($appointmentDetails['DoctorID'])) {
            $_SESSION['error_message'] = 'Appointment data not found or incomplete.';
            header('Location: ' . BASE_URL . '/nurse/listAppointments'); exit();
        }
        
        if (empty($assignedDoctorIds) || !in_array($appointmentDetails['DoctorID'], $assignedDoctorIds)) {
            $_SESSION['error_message'] = 'You are not authorized to save vitals for this appointment.';
            header('Location: ' . BASE_URL . '/nurse/appointmentDetails/' . $appointmentId); exit();
        }

        $sanitizeAndValidate = function($value, $filter, $options = []) {
            $trimmedValue = trim($value ?? '');
            if ($trimmedValue === '') return null; 
            $validatedValue = filter_var($trimmedValue, $filter, $options);
            return ($validatedValue === false && $trimmedValue !== '0' && $trimmedValue !== '0.0') ? 'INVALID_VALUE_FLAG' : $validatedValue;
        };
        
        $input = [
            'AppointmentID' => $appointmentId,
            'PatientID' => $appointmentDetails['PatientProfileID'], 
            'RecordedByUserID' => $_SESSION['user_id'], 
            'HeartRate' => $sanitizeAndValidate($_POST['HeartRate'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 10, 'max_range'=>300]]),
            'Temperature' => $sanitizeAndValidate($_POST['Temperature'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => '.']]),
            'BloodPressureSystolic' => $sanitizeAndValidate($_POST['BloodPressureSystolic'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 30, 'max_range'=>300]]),
            'BloodPressureDiastolic' => $sanitizeAndValidate($_POST['BloodPressureDiastolic'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 20, 'max_range'=>200]]),
            'RespiratoryRate' => $sanitizeAndValidate($_POST['RespiratoryRate'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 5, 'max_range'=>60]]),
            'Weight' => $sanitizeAndValidate($_POST['Weight'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => '.']]),
            'Height' => $sanitizeAndValidate($_POST['Height'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => '.']]),
            'OxygenSaturation' => $sanitizeAndValidate($_POST['OxygenSaturation'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 50, 'max_range' => 100]]),
            'Notes' => trim($_POST['Notes'] ?? '')
        ];
        
        $errors = [];
        foreach ($input as $key => $value) {
            if ($value === 'INVALID_VALUE_FLAG') {
                $fieldName = str_replace('_', ' ', preg_replace('/(?<!^)[A-Z]/', ' $0', $key));
                $errors[$key] = 'Invalid value entered for ' . strtolower($fieldName) . '.';
                $input[$key] = $_POST[$key] ?? ''; 
            }
        }

        if (empty($errors)) {
            if ($this->vitalSignModel->createOrUpdateVitalSigns($input)) {
                $_SESSION['success_message'] = 'Vital signs recorded successfully.';
                header('Location: ' . BASE_URL . '/nurse/appointmentDetails/' . $appointmentId);
                exit();
            } else {
                $_SESSION['error_message'] = 'Failed to save vital signs. Please try again.';
            }
        }

        $_SESSION['vitals_form_errors'] = $errors;
        $_SESSION['vitals_form_input'] = $_POST; 
        header('Location: ' . BASE_URL . '/nurse/showRecordVitalsForm/' . $appointmentId);
        exit();
    }

    public function updateProfile() {
        $nurseProfileData = $this->getCurrentNurseProfile(); 
        if (!$nurseProfileData) {
            $_SESSION['error_message'] = "Could not retrieve your nurse profile to update. Please contact an administrator.";
            header('Location: ' . BASE_URL . '/nurse/dashboard');
            exit();
        }
        
        $userId = $_SESSION['user_id']; 
        if (!isset($nurseProfileData['PasswordHash'])) {
            $userTableDetails = $this->userModel->findUserById($userId);
            $nurseProfileData['PasswordHash'] = $userTableDetails['PasswordHash'] ?? null;
        }

        $data = [
            'title' => 'Update My Nurse Profile',
            'user' => $nurseProfileData, 
            'input' => $_SESSION['profile_form_input_nurse'] ?? $nurseProfileData, 
            'errors' => $_SESSION['profile_form_errors_nurse'] ?? []
        ];
        unset($_SESSION['profile_form_input_nurse'], $_SESSION['profile_form_errors_nurse']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $_SESSION['error_message'] = 'Invalid CSRF token. Profile update aborted.';
                header('Location: ' . BASE_URL . '/nurse/updateProfile'); exit();
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $newInputData = [
                'FullName' => trim($_POST['FullName'] ?? $nurseProfileData['FullName']),
                'Email' => trim($_POST['Email'] ?? $nurseProfileData['Email']),
                'PhoneNumber' => trim($_POST['PhoneNumber'] ?? $nurseProfileData['PhoneNumber']),
                'Address' => trim($_POST['Address'] ?? $nurseProfileData['Address']), 
                'current_password' => $_POST['current_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
                'confirm_new_password' => $_POST['confirm_new_password'] ?? ''
            ];
            $newInputData['Avatar'] = $nurseProfileData['Avatar']; 


            if (empty($newInputData['FullName'])) $data['errors']['FullName'] = 'Full name is required.';
            
            if (empty($newInputData['Email'])) {
                $data['errors']['Email'] = 'Email is required.';
            } elseif (!filter_var($newInputData['Email'], FILTER_VALIDATE_EMAIL)) {
                $data['errors']['Email'] = 'Invalid email format.';
            } elseif (strtolower($newInputData['Email']) !== strtolower($nurseProfileData['Email']) && $this->userModel->findUserByEmail($newInputData['Email'], $userId)) {
                $data['errors']['Email'] = 'This email is already registered by another user.';
            }

            $updatePassword = false;
            if (!empty($newInputData['new_password'])) {
                if (empty($newInputData['current_password'])) {
                    $data['errors']['current_password'] = 'Please enter your current password to set a new one.';
                } elseif (empty($nurseProfileData['PasswordHash']) || !password_verify($newInputData['current_password'], $nurseProfileData['PasswordHash'])) {
                     $data['errors']['current_password'] = 'Incorrect current password.';
                }
                if (strlen($newInputData['new_password']) < 6) {
                    $data['errors']['new_password'] = 'New password must be at least 6 characters.';
                }
                if ($newInputData['new_password'] !== $newInputData['confirm_new_password']) {
                    $data['errors']['confirm_new_password'] = 'New passwords do not match.';
                }
                if (empty($data['errors']['current_password']) && empty($data['errors']['new_password']) && empty($data['errors']['confirm_new_password'])) {
                    $updatePassword = true;
                }
            }

            $avatarPathForDB = $nurseProfileData['Avatar']; 
            $avatarFileUploaded = false; 

            if (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] == UPLOAD_ERR_OK) {
                $avatarFileUploaded = true; 
                $file = $_FILES['profile_avatar'];
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($fileExt, $allowedExtensions)) {
                    if ($fileSize < 5000000) { 
                        $newFileName = "avatar_nurse_" . $userId . "_" . uniqid('', true) . "." . $fileExt;
                        $uploadDirRelative = 'uploads/avatars/'; 
                        $uploadDirAbsolute = rtrim(PUBLIC_PATH, '/') . '/' . $uploadDirRelative;

                        if (!file_exists($uploadDirAbsolute)) {
                            if (!mkdir($uploadDirAbsolute, 0775, true) && !is_dir($uploadDirAbsolute)) {
                                 $data['errors']['profile_avatar'] = 'Failed to create upload directory.';
                            }
                        }
                        if (empty($data['errors']['profile_avatar']) && is_writable($uploadDirAbsolute)) {
                            $fileDestinationOnServer = $uploadDirAbsolute . $newFileName;
                            if (move_uploaded_file($fileTmpName, $fileDestinationOnServer)) {
                                if (!empty($nurseProfileData['Avatar']) && $nurseProfileData['Avatar'] !== 'assets/images/default_avatar.png' && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($nurseProfileData['Avatar'], '/'))) {
                                    @unlink(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($nurseProfileData['Avatar'], '/'));
                                }
                                $avatarPathForDB = rtrim($uploadDirRelative, '/') . '/' . $newFileName;
                                $newInputData['Avatar'] = $avatarPathForDB; 
                            } else {
                                $data['errors']['profile_avatar'] = "Failed to move uploaded file.";
                            }
                        } elseif (!is_writable($uploadDirAbsolute) && empty($data['errors']['profile_avatar'])) {
                             $data['errors']['profile_avatar'] = 'Upload directory is not writable.';
                        }
                    } else { $data['errors']['profile_avatar'] = "File is too large (max 5MB)."; }
                } else { $data['errors']['profile_avatar'] = "Invalid file type (jpg, jpeg, png, gif)."; }
            } elseif (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] != UPLOAD_ERR_NO_FILE) {
                $data['errors']['profile_avatar'] = "File upload error: " . $_FILES['profile_avatar']['error'];
            }


            if (empty($data['errors'])) {
                $this->db->beginTransaction();
                try {
                    $userDataToUpdateForUsersTable = [
                        'FullName' => $newInputData['FullName'],
                        'Email' => $newInputData['Email'],
                        'PhoneNumber' => $newInputData['PhoneNumber'] ?: null,
                        'Address' => $newInputData['Address'] ?: null,
                    ];
                    if ($avatarPathForDB !== $nurseProfileData['Avatar']) {
                        $userDataToUpdateForUsersTable['Avatar'] = $avatarPathForDB;
                    }

                    $userUpdateSuccess = $this->userModel->updateUser($userId, $userDataToUpdateForUsersTable);
                    
                    $nurseSpecificDataToUpdate = []; 
                    $nurseProfileUpdateSuccess = true;
                    if (!empty($nurseSpecificDataToUpdate)) {
                         $nurseProfileUpdateSuccess = $this->nurseModel->updateNurseProfile($nurseProfileData['NurseID'], $nurseSpecificDataToUpdate);
                    } else {
                        $this->nurseModel->updateNurseProfile($nurseProfileData['NurseID'], []);
                    }


                    $passwordUpdateSuccess = true; 
                    if ($updatePassword) {
                        $newPasswordHash = password_hash($newInputData['new_password'], PASSWORD_DEFAULT);
                        $passwordUpdateSuccess = $this->userModel->updatePassword($userId, $newPasswordHash);
                    }

                    if ($userUpdateSuccess && $passwordUpdateSuccess && $nurseProfileUpdateSuccess) {
                        $this->db->commit();
                        $_SESSION['success_message'] = 'Your profile has been updated successfully, sweetie!';
                        
                        $_SESSION['user_fullname'] = $newInputData['FullName'];
                        $_SESSION['user_email'] = $newInputData['Email']; 
                        if ($avatarPathForDB !== $nurseProfileData['Avatar']) {
                             $_SESSION['user_avatar'] = $avatarPathForDB; 
                        }
                        
                        header('Location: ' . BASE_URL . '/nurse/updateProfile');
                        exit();
                    } else {
                        $this->db->rollBack();
                        if ($avatarFileUploaded && $avatarPathForDB !== $nurseProfileData['Avatar'] && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($avatarPathForDB, '/'))) {
                            @unlink(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($avatarPathForDB, '/')); 
                        }
                        $_SESSION['error_message'] = 'Failed to update profile in database. Please try again.';
                    }
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) $this->db->rollBack();
                    if ($avatarFileUploaded && $avatarPathForDB !== $nurseProfileData['Avatar'] && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($avatarPathForDB, '/'))) {
                        @unlink(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($avatarPathForDB, '/')); 
                    }
                    error_log("Error updating nurse profile {$userId}: " . $e->getMessage());
                    $_SESSION['error_message'] = 'An unexpected error occurred: ' . $e->getMessage();
                }
            }
            
            $_SESSION['profile_form_input_nurse'] = $newInputData; 
            $_SESSION['profile_form_errors_nurse'] = $data['errors'];
            header('Location: ' . BASE_URL . '/nurse/updateProfile');
            exit();
        }
        
        $this->view('nurse/profile/update', $data); 
    }
}
?>