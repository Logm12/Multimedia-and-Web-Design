<?php

class NurseController {
    private $userModel;
    private $appointmentModel;
    private $vitalSignModel;
    private $patientModel;
    private $doctorNurseAssignmentModel;
    private $nurseModel;
    private $medicalRecordModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
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
        } catch (Error $e) {
            error_log("FATAL: Model initialization error in NurseController: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
            die("A critical error occurred during application setup. Please check the server logs or contact support. Error details: " . htmlspecialchars($e->getMessage()));
        }
    }

    protected function view($view, $data = []) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
            session_destroy();
            header('Location: ' . BASE_URL . '/auth/login?error=session_issue');
            exit();
        }
        if (!isset($data['currentUser'])) {
            $data['currentUser'] = [
                'UserID' => $_SESSION['user_id'],
                'FullName' => $_SESSION['user_fullname'] ?? 'Nurse',
                'Role' => $_SESSION['user_role'],
                'Avatar' => $_SESSION['user_avatar'] ?? null
            ];
        }
        $data['userRole'] = 'Nurse';

        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist for Nurse, sweetie.");
        }
    }

    private function getCurrentNurseProfile() {
        $nurseData = $this->nurseModel->getNurseByUserId($_SESSION['user_id']);
        if ($nurseData && isset($nurseData['NurseID'])) {
            return $nurseData;
        }
        error_log("Nurse profile not found for UserID: " . $_SESSION['user_id']);
        return null;
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        $nurseFullName = $_SESSION['user_fullname'] ?? 'Nurse';
        $data = [
            'title' => 'Nurse Dashboard',
            'upcoming_appointments' => [],
            'welcome_message' => 'Welcome back, ' . htmlspecialchars($nurseFullName) . '!'
        ];

        $nurseProfile = $this->getCurrentNurseProfile();
        if (!$nurseProfile) {
            $_SESSION['error_message'] = "Could not retrieve your nurse profile. Please ensure your nurse record is set up correctly.";
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
            $_SESSION['error_message'] = "Nurse profile not found.";
            header('Location: ' . BASE_URL . '/nurse/dashboard'); exit();
        }
        $nurseId = $nurseProfile['NurseID'];

        $assignedDoctorIds = $this->doctorNurseAssignmentModel->getAssignedDoctorIdsByNurseId($nurseId);

        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $status = $_GET['status'] ?? 'All';

        if (empty($dateFrom) && empty($dateTo)) {
            $dateFrom = date('Y-m-d');
        } elseif (!empty($dateFrom) && empty($dateTo)) {
            // $dateTo = $dateFrom; // Tùy logic, có thể để trống dateTo nếu muốn lọc từ dateFrom đến vô cùng
        } elseif (empty($dateFrom) && !empty($dateTo)) {
            // $dateFrom = $dateTo; // Tùy logic
        }

        $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
        if (!in_array($status, $validStatuses)) {
            $status = 'All';
        }

        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'status' => $status
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
            'all_statuses' => $validStatuses
        ];
        $this->view('nurse/appointments/list', $data);
    }

    public function appointmentDetails($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0) {
            $_SESSION['error_message'] = "Invalid Appointment ID.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments');
            exit();
        }

        $nurseProfile = $this->getCurrentNurseProfile();
        if (!$nurseProfile) {
            $_SESSION['error_message'] = "Nurse profile not found.";
            header('Location: ' . BASE_URL . '/nurse/dashboard'); exit();
        }
        $assignedDoctorIds = $this->doctorNurseAssignmentModel->getAssignedDoctorIdsByNurseId($nurseProfile['NurseID']);
        
        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);

        if (!$appointmentDetails) {
            $_SESSION['error_message'] = "Appointment not found.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments');
            exit();
        }

        if (empty($assignedDoctorIds) || !isset($appointmentDetails['DoctorID']) || !in_array($appointmentDetails['DoctorID'], $assignedDoctorIds)) {
            $_SESSION['error_message'] = 'You are not authorized to view this appointment\'s details.';
            header('Location: ' . BASE_URL . '/nurse/listAppointments');
            exit();
        }
        
        $vitals = $this->vitalSignModel->getVitalSignsByAppointmentId($appointmentId);
        $medicalRecord = $this->medicalRecordModel->getRecordByAppointmentId($appointmentId); // Sử dụng hàm getRecordByAppointmentId
        
        $data = [
            'title' => 'Appointment Details',
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
        if (!$nurseProfile) {
            $_SESSION['error_message'] = "Nurse profile not found.";
            header('Location: ' . BASE_URL . '/nurse/dashboard'); exit();
        }
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

        $data = [
            'title' => 'Add/Edit Nursing Note for Appointment #' . $appointmentId,
            'appointment' => $appointmentDetails,
            'input_notes' => $_SESSION['nursing_note_input'] ?? ($medicalRecord['NursingNotes'] ?? ''),
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
        if (!$nurseProfile) {
            $_SESSION['error_message'] = "Nurse profile not found.";
            header('Location: ' . BASE_URL . '/nurse/dashboard'); exit();
        }
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
        if (!$nurseProfile) {
            $_SESSION['error_message'] = "Nurse profile not found.";
            header('Location: ' . BASE_URL . '/nurse/dashboard'); exit();
        }
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
        if (!$nurseProfile) {
            $_SESSION['error_message'] = "Nurse profile not found.";
            header('Location: ' . BASE_URL . '/nurse/dashboard'); exit();
        }
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
            return ($validatedValue === false) ? 'INVALID_VALUE_FLAG' : $validatedValue;
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
}
?>