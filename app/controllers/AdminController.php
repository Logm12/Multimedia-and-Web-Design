<?php
// app/controllers/AdminController.php

class AdminController {
    private $userModel;
    private $specializationModel;
    private $db;
    private $doctorModel;
    private $mailService;
    private $medicineModel;
    private $appointmentModel;
    private $patientModel;
    private $feedbackModel;
    private $notificationModel;
    private $doctorAvailabilityModel;
    private $backupPath;
    private $leaveRequestModel;
    private $nurseModel; 
    private $doctorNurseAssignmentModel; 

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once __DIR__ . '/../helpers/csrf_helper.php';
        
        $urlPath = $_GET['url'] ?? '';
        $urlParts = explode('/', rtrim($urlPath, '/'));
        $controllerNameFromUrl = $urlParts[0] ?? '';
        $currentAction = $urlParts[1] ?? 'dashboard'; 

        if (strtolower($controllerNameFromUrl) === 'admin') {
            $publicActions = []; 
            if (!in_array($currentAction, $publicActions)) {
                if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
                    $_SESSION['error_message'] = "Access denied. Admin login required.";
                    header('Location: ' . BASE_URL . '/auth/login');
                    exit();
                }
            }
        }
        
        try {
            $this->userModel = new UserModel();
            $this->specializationModel = new SpecializationModel();
            $this->db = Database::getInstance();
            $this->doctorModel = new DoctorModel();
            $this->mailService = new MailService();
            $this->medicineModel = new MedicineModel();
            $this->appointmentModel = new AppointmentModel();
            $this->patientModel = new PatientModel();
            $this->feedbackModel = new FeedbackModel();
            $this->notificationModel = new NotificationModel();
            $this->doctorAvailabilityModel = new DoctorAvailabilityModel();
            $this->leaveRequestModel = new LeaveRequestModel(); 
            $this->nurseModel = new NurseModel(); 
            $this->doctorNurseAssignmentModel = new DoctorNurseAssignmentModel(); 
            $this->backupPath = __DIR__ . '/../../storage/backups/'; 
        } catch (Error $e) {
            error_log("FATAL: Model initialization error in AdminController: " . $e->getMessage());
            die("A critical error occurred during application setup. Please check logs or contact support. Details: " . htmlspecialchars($e->getMessage()));
        }
    }
    
    public function index() {
        $this->dashboard();
    }

    protected function view($view, $data = []) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            if (strpos($view, 'admin/auth/') === false && $view !== 'admin/login') { 
                 $_SESSION['error_message'] = "Unauthorized access. Admin login required.";
                 header('Location: ' . BASE_URL . '/auth/login'); 
                 exit();
            }
        }
        
        $currentUserFromDB = $this->userModel->findUserById($_SESSION['user_id'] ?? 0);

        if (!isset($data['currentUser'])) {
            $data['currentUser'] = [
                'UserID' => $_SESSION['user_id'] ?? null,
                'FullName' => $currentUserFromDB['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Admin'),
                'Role' => $_SESSION['user_role'] ?? null,
                'Avatar' => $currentUserFromDB['Avatar'] ?? ($_SESSION['user_avatar'] ?? null)
            ];
        } else { 
            $data['currentUser']['FullName'] = $data['currentUser']['FullName'] ?? $currentUserFromDB['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Admin');
            $data['currentUser']['Avatar'] = $data['currentUser']['Avatar'] ?? $currentUserFromDB['Avatar'] ?? ($_SESSION['user_avatar'] ?? null);
        }

        if (!isset($data['title'])) {
            $data['title'] = 'Admin Panel'; 
        }

        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            error_log("AdminController: View '{$view}' does not exist.");
            die("View '{$view}' does not exist, my dear admin. Please check the path.");
        }
    }
    
    public function dashboard() {
        $adminUserId = $_SESSION['user_id'];
        $userStats = [
            'patients' => $this->userModel->getUserCountByRole('Patient'),
            'doctors' => $this->userModel->getUserCountByRole('Doctor'),
            'nurses' => $this->userModel->getUserCountByRole('Nurse')
        ];
        $medicineCount = $this->medicineModel->getTotalMedicineCount();
        $calendarEvents = [
            ['title' => 'System Update', 'start' => date('Y-m-d', strtotime('+5 days')), 'allDay' => true, 'backgroundColor' => '#f39c12', 'borderColor' => '#f39c12'],
            ['title' => 'Monthly Report Due', 'start' => date('Y-m-t'), 'allDay' => true, 'backgroundColor' => '#dd4b39', 'borderColor' => '#dd4b39']
        ];
        $data = [
            'title' => 'Admin Dashboard',
            'welcome_message' => 'Welcome Administrator, ' . htmlspecialchars($_SESSION['user_fullname'] ?? '') . '!',
            'user_stats' => $userStats,
            'medicine_count' => $medicineCount,
            'calendar_events' => $calendarEvents
        ];
        $this->view('admin/dashboard', $data);
    }

    public function manageSpecializations() {
        $specializations = $this->specializationModel->getAll();
        $data = ['title' => 'Manage Specializations', 'specializations' => $specializations];
        $this->view('admin/manage_specializations', $data);
    }

    public function editSpecialization($id = null) {
        $data = ['title' => $id ? 'Edit Specialization' : 'Add New Specialization', 'specialization' => null, 'errors' => [], 'input_name' => '', 'input_description' => ''];
        if ($id) {
            $data['specialization'] = $this->specializationModel->findById((int)$id);
            if (!$data['specialization']) {
                $_SESSION['admin_message_error'] = 'Specialization not found.';
                header('Location: ' . BASE_URL . '/admin/manageSpecializations'); exit();
            }
            $data['input_name'] = $data['specialization']['Name'] ?? '';
            $data['input_description'] = $data['specialization']['Description'] ?? '';
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $_SESSION['admin_message_error'] = 'Invalid CSRF token.'; 
                header('Location: ' . BASE_URL . '/admin/manageSpecializations'); exit();
            }
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? null);
            $currentId = $_POST['id'] ?? ($data['specialization']['SpecializationID'] ?? null);
            $data['input_name'] = $name; $data['input_description'] = $description;
            if (empty($name)) $data['errors']['name'] = 'Specialization name cannot be empty.';
            elseif ($this->specializationModel->findByName($name, $currentId)) $data['errors']['name'] = 'This name already exists.';
            if (empty($data['errors'])) {
                $success = $currentId ? $this->specializationModel->update((int)$currentId, $name, $description) : $this->specializationModel->create($name, $description);
                $_SESSION[$success ? 'admin_message_success' : 'admin_message_error'] = 'Specialization ' . ($success ? ($currentId ? 'updated' : 'added') . ' successfully.' : 'operation failed.');
                header('Location: ' . BASE_URL . '/admin/manageSpecializations'); exit();
            }
        }
        $this->view('admin/edit_specialization', $data);
    }

    public function deleteSpecialization() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $_SESSION['admin_message_error'] = 'Invalid request method.'; header('Location: ' . BASE_URL . '/admin/manageSpecializations'); exit(); }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) { $_SESSION['admin_message_error'] = 'Invalid CSRF token.'; header('Location: ' . BASE_URL . '/admin/manageSpecializations'); exit(); }
        $id_to_delete = $_POST['id_to_delete'] ?? null;
        if ($id_to_delete && filter_var($id_to_delete, FILTER_VALIDATE_INT)) {
            if ($this->specializationModel->delete((int)$id_to_delete)) $_SESSION['admin_message_success'] = 'Specialization deleted.';
            else $_SESSION['admin_message_error'] = 'Failed to delete specialization.';
        } else $_SESSION['admin_message_error'] = 'Invalid ID for deletion.';
        header('Location: ' . BASE_URL . '/admin/manageSpecializations'); exit();
    }
    public function createUser() {
        $data = [
            'title' => 'Create New User',
            'specializations' => $this->specializationModel->getAllSpecializations(),
            'roles' => ['Doctor', 'Nurse', 'Admin', 'Patient'],
            'input' => [], 'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $data['errors'][] = 'Invalid CSRF token.'; $this->view('admin/users/create', $data); return;
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $data['input'] = [
                'FullName' => trim($_POST['FullName'] ?? ''), 'Username' => trim($_POST['Username'] ?? ''),
                'Email' => trim($_POST['Email'] ?? ''), 'PhoneNumber' => trim($_POST['PhoneNumber'] ?? null),
                'Role' => $_POST['Role'] ?? '', 'Status' => $_POST['Status'] ?? 'Pending',
                'SpecializationID' => $_POST['SpecializationID'] ?? null, 'Bio' => trim($_POST['Bio'] ?? null),
                'ExperienceYears' => filter_var($_POST['ExperienceYears'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]),
                'ConsultationFee' => filter_var($_POST['ConsultationFee'] ?? 0.00, FILTER_VALIDATE_FLOAT, ['options' => ['default' => 0.00, 'decimal' => '.']]),
                'Address' => trim($_POST['Address'] ?? null)
            ];

            if (empty($data['input']['FullName'])) $data['errors'][] = 'Full Name required.';
            if (empty($data['input']['Username'])) $data['errors'][] = 'Username required.';
            elseif ($this->userModel->findUserByUsername($data['input']['Username'])) $data['errors'][] = 'Username exists.';
            if (empty($data['input']['Email'])) $data['errors'][] = 'Email required.';
            elseif (!filter_var($data['input']['Email'], FILTER_VALIDATE_EMAIL)) $data['errors'][] = 'Invalid email.';
            elseif ($this->userModel->findUserByEmail($data['input']['Email'])) $data['errors'][] = 'Email exists.';
            if (empty($data['input']['Role']) || !in_array($data['input']['Role'], $data['roles'])) $data['errors'][] = 'Invalid role.';
            if (empty($data['input']['Status']) || !in_array($data['input']['Status'], ['Active', 'Inactive', 'Pending'])) $data['errors'][] = 'Invalid status.';
            if ($data['input']['Role'] === 'Doctor' && empty($data['input']['SpecializationID'])) $data['errors'][] = 'Specialization required for Doctor.';

            if (empty($data['errors'])) {
                $generatedPassword = bin2hex(random_bytes(6));
                $passwordHash = password_hash($generatedPassword, PASSWORD_DEFAULT);
                $this->db->beginTransaction();
                try {
                    $userData = ['Username' => $data['input']['Username'], 'PasswordHash' => $passwordHash, 'Email' => $data['input']['Email'], 'FullName' => $data['input']['FullName'], 'Role' => $data['input']['Role'], 'PhoneNumber' => $data['input']['PhoneNumber'], 'Address' => $data['input']['Address'], 'Status' => $data['input']['Status']];
                    $newUserId = $this->userModel->createUser($userData);
                    if (!$newUserId) throw new Exception('Failed to create base user.');

                    if ($data['input']['Role'] === 'Doctor') {
                        $doctorData = ['SpecializationID' => $data['input']['SpecializationID'], 'Bio' => $data['input']['Bio'], 'ExperienceYears' => $data['input']['ExperienceYears'], 'ConsultationFee' => $data['input']['ConsultationFee']];
                        if (!$this->doctorModel->createDoctorProfile($newUserId, $doctorData)) throw new Exception('Failed to create Doctor profile.');
                    } elseif ($data['input']['Role'] === 'Nurse') {
                        if (!$this->nurseModel->createNurseProfile($newUserId)) throw new Exception('Failed to create Nurse profile.'); 
                    } elseif ($data['input']['Role'] === 'Patient') {
                        $patientData = ['UserID' => $newUserId]; 
                        if (!$this->patientModel->createPatient($patientData)) throw new Exception('Failed to create Patient profile.');
                    }

                    $this->db->commit();
                    $emailSent = $this->mailService->sendWelcomeEmail($data['input']['Email'], $data['input']['FullName'], $data['input']['Username'], $generatedPassword, $data['input']['Role']);
                    $_SESSION[$emailSent ? 'user_management_message_success' : 'user_management_message_error'] = ucfirst($data['input']['Role']) . " '{$data['input']['Username']}' created. " . ($emailSent ? "Welcome email sent." : "FAILED TO SEND EMAIL. Temp Pass: <strong>{$generatedPassword}</strong>.");
                    header('Location: ' . BASE_URL . '/admin/listUsers'); exit();
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) $this->db->rollBack();
                    error_log("Create User Error: " . $e->getMessage());
                    $data['errors'][] = 'Creation error: ' . $e->getMessage();
                }
            }
        }
        $this->view('admin/users/create', $data);
    }

    public function listUsers() {
        $roleFilter = $_GET['role'] ?? 'All';
        $searchTerm = trim($_GET['search'] ?? '');
        $statusFilterInput = $_GET['status'] ?? ['Active', 'Pending'];
        $validRoles = ['All', 'Admin', 'Doctor', 'Nurse', 'Patient'];
        if (!in_array($roleFilter, $validRoles)) $roleFilter = 'All';
        $statusFilterForModel = $statusFilterInput;
        $statusFilterForView = is_array($statusFilterInput) ? implode('_and_', $statusFilterInput) : $statusFilterInput;
        if (isset($_GET['status']) && in_array($_GET['status'], ['All', 'Inactive', 'Active', 'Pending'])) {
            $statusFilterForModel = $_GET['status']; $statusFilterForView = $_GET['status'];
        }
        $users = $this->userModel->getAllUsers($roleFilter, $statusFilterForModel, $searchTerm);
        $data = ['title' => 'Manage Users', 'users' => $users, 'currentRoleFilter' => $roleFilter, 'currentStatusFilter' => $statusFilterForView, 'currentSearchTerm' => $searchTerm, 'allRoles' => $validRoles, 'allStatuses' => ['All', 'Active', 'Inactive', 'Pending', 'Active_and_Pending']];
        $this->view('admin/users/list', $data);
    }

    public function updateUserStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers')); exit(); }
        $userIdToUpdate = $_POST['user_id'] ?? null; $newStatus = $_POST['new_status'] ?? null;
        if (!filter_var($userIdToUpdate, FILTER_VALIDATE_INT) || $userIdToUpdate <= 0) $_SESSION['user_management_message_error'] = 'Invalid User ID.';
        elseif ($userIdToUpdate == $_SESSION['user_id'] && $newStatus === 'Inactive') $_SESSION['user_management_message_error'] = 'Cannot deactivate own account.';
        else {
            if ($this->userModel->updateUserStatus((int)$userIdToUpdate, $newStatus)) $_SESSION['user_management_message_success'] = "Status updated to '{$newStatus}'.";
            else $_SESSION['user_management_message_error'] = "Failed to update status.";
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers')); exit();
    }

    public function checkUsernameAvailability() {
        header('Content-Type: application/json');
        $username = $_GET['username'] ?? '';
        if (empty($username)) {
            echo json_encode(['available' => false, 'message' => 'Username cannot be empty.']);
            exit;
        }
        if (!isset($this->userModel)) $this->userModel = new UserModel();

        if ($this->userModel->findUserByUsername($username)) {
            echo json_encode(['available' => false, 'message' => 'Username already taken.']);
        } else {
            echo json_encode(['available' => true]);
        }
        exit;
    }

    public function checkEmailAvailability() {
        header('Content-Type: application/json');
        $email = $_GET['email'] ?? '';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
             echo json_encode(['available' => false, 'message' => 'Invalid email format.']);
             exit;
        }
        if (!isset($this->userModel)) $this->userModel = new UserModel();

        if ($this->userModel->findUserByEmail($email)) {
            echo json_encode(['available' => false, 'message' => 'Email already registered.']);
        } else {
            echo json_encode(['available' => true]);
        }
        exit;
    }
    public function editUser($userId = 0) {
        $userId = (int)$userId;
        if ($userId <= 0) {
            $_SESSION['user_management_message_error'] = 'Invalid User ID specified for editing.';
            header('Location: ' . BASE_URL . '/admin/listUsers');
            exit();
        }

        $userToEdit = $this->userModel->findUserById($userId);
        if (!$userToEdit) {
            $_SESSION['user_management_message_error'] = "User with ID {$userId} not found.";
            header('Location: ' . BASE_URL . '/admin/listUsers');
            exit();
        }

        $doctorProfile = null;
        if ($userToEdit['Role'] === 'Doctor') {
            $doctorProfile = $this->doctorModel->getDoctorByUserId($userId);
        }

        $data = [
            'title' => 'Edit User - ' . htmlspecialchars($userToEdit['FullName']),
            'userToEdit' => $userToEdit,
            'doctorProfile' => $doctorProfile,
            'specializations' => $this->specializationModel->getAllSpecializations(),
            'roles' => ['Admin', 'Doctor', 'Nurse', 'Patient'],
            'statuses' => ['Active', 'Inactive', 'Pending'],
            'input' => array_merge($userToEdit, $doctorProfile ?? []),
            'errors' => [],
            'userId' => $userId
        ];
        if (isset($userToEdit['Address'])) {
            $data['input']['Address'] = $userToEdit['Address'];
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                 $data['errors'][] = 'Invalid CSRF token. Action aborted.';
            }

            $data['input']['FullName'] = trim($_POST['FullName'] ?? '');
            $data['input']['Email'] = trim($_POST['Email'] ?? '');
            $data['input']['Role'] = $_POST['Role'] ?? $userToEdit['Role'];
            $data['input']['Status'] = $_POST['Status'] ?? $userToEdit['Status'];
            $data['input']['PhoneNumber'] = trim($_POST['PhoneNumber'] ?? null);
            $data['input']['Address'] = trim($_POST['Address'] ?? null);

            $newPassword = $_POST['NewPassword'] ?? '';
            $confirmNewPassword = $_POST['ConfirmNewPassword'] ?? '';

            if ($data['input']['Role'] === 'Doctor') {
                $data['input']['SpecializationID'] = $_POST['SpecializationID'] ?? null;
                $data['input']['Bio'] = trim($_POST['Bio'] ?? null);
                $data['input']['ExperienceYears'] = filter_var($_POST['ExperienceYears'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
                $data['input']['ConsultationFee'] = filter_var($_POST['ConsultationFee'] ?? 0.00, FILTER_VALIDATE_FLOAT, ['options' => ['default' => 0.00, 'decimal' => '.']]);
            }

            if (empty($data['input']['FullName'])) $data['errors'][] = 'Full Name is required.';
            if (empty($data['input']['Email'])) {
                $data['errors'][] = 'Email is required.';
            } elseif (!filter_var($data['input']['Email'], FILTER_VALIDATE_EMAIL)) {
                $data['errors'][] = 'Invalid email format.';
            } elseif ($this->userModel->findUserByEmail($data['input']['Email'], $userId)) {
                $data['errors'][] = 'Email already exists for another user.';
            }
            if (empty($data['input']['Role']) || !in_array($data['input']['Role'], $data['roles'])) $data['errors'][] = 'Invalid role selected.';
            if (empty($data['input']['Status']) || !in_array($data['input']['Status'], $data['statuses'])) $data['errors'][] = 'Invalid status selected.';

            $updatePassword = false;
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) $data['errors'][] = 'New password must be at least 6 characters.';
                if ($newPassword !== $confirmNewPassword) $data['errors'][] = 'New passwords do not match.';
                if (empty($data['errors']['new_password']) && empty($data['errors']['confirm_new_password'])) $updatePassword = true;
            }

            if (empty($data['errors'])) {
                $this->db->beginTransaction();
                try {
                    $userDataToUpdate = [
                        'FullName' => $data['input']['FullName'], 'Email' => $data['input']['Email'],
                        'Role' => $data['input']['Role'], 'Status' => $data['input']['Status'],
                        'PhoneNumber' => $data['input']['PhoneNumber'], 'Address' => $data['input']['Address']
                    ];
                    if (!$this->userModel->updateUser($userId, $userDataToUpdate)) throw new Exception('Failed to update user base information.');

                    if ($updatePassword) {
                        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                        if (!$this->userModel->updatePassword($userId, $newPasswordHash)) throw new Exception('Failed to update password.');
                    }

                    if ($userToEdit['Role'] === 'Doctor' && $data['input']['Role'] === 'Doctor') {
                        $doctorDataToUpdate = [
                            'SpecializationID' => $data['input']['SpecializationID'], 'Bio' => $data['input']['Bio'],
                            'ExperienceYears' => $data['input']['ExperienceYears'], 'ConsultationFee' => $data['input']['ConsultationFee']
                        ];
                        if (!$this->doctorModel->updateDoctorProfile($userId, $doctorDataToUpdate)) throw new Exception('Failed to update doctor profile.');
                    }

                    $this->db->commit();
                    $_SESSION['user_management_message_success'] = 'User profile updated successfully.';
                    header('Location: ' . BASE_URL . '/admin/listUsers');
                    exit();
                } catch (Exception $e) {
                    $this->db->rollBack();
                    error_log("Error updating user {$userId}: " . $e->getMessage());
                    $data['errors'][] = 'An error occurred: ' . $e->getMessage();
                }
            }
        }
        $this->view('admin/users/edit', $data);
    }

    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->authAdmin(false)) {
            $_SESSION['user_management_message_error'] = 'Unauthorized or invalid request for deletion.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
            exit();
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['user_management_message_error'] = 'Invalid CSRF token. Deletion aborted.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
            exit();
        }
        $userIdToDelete = $_POST['user_id_to_delete'] ?? null;
        if (!filter_var($userIdToDelete, FILTER_VALIDATE_INT) || $userIdToDelete <= 0) {
            $_SESSION['user_management_message_error'] = 'Invalid User ID for deletion.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
            exit();
        }
        if ($userIdToDelete == $_SESSION['user_id']) {
            $_SESSION['user_management_message_error'] = 'You cannot delete your own account.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
            exit();
        }

        $newStatusForDeletedUser = 'Inactive';
        $this->db->beginTransaction();
        try {
            if ($this->userModel->updateUserStatus((int)$userIdToDelete, $newStatusForDeletedUser)) {
                $this->db->commit();
                $_SESSION['user_management_message_success'] = "User (ID: {$userIdToDelete}) has been marked as '{$newStatusForDeletedUser}' (soft deleted).";
            } else {
                throw new Exception("Failed to update user status for soft deletion.");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error soft deleting user {$userIdToDelete}: " . $e->getMessage());
            $_SESSION['user_management_message_error'] = 'An error occurred while trying to delete the user: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/listUsers');
        exit();
    }
    public function listMedicines() {
        $searchTerm = trim($_GET['search'] ?? '');
        $medicines = $this->medicineModel->getAllAdmin($searchTerm);
        $data = [
            'title' => 'Manage Medicines',
            'medicines' => $medicines,
            'currentSearchTerm' => $searchTerm
        ];
        $this->view('admin/medicines/list', $data);
    }

    public function createMedicine() {
        $data = ['title' => 'Add New Medicine', 'input' => [], 'errors' => []];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $_SESSION['admin_medicine_message_error'] = 'Invalid CSRF token.';
                $this->view('admin/medicines/form', $data);
                return;
            }
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $data['input'] = [
                'Name' => trim($_POST['Name'] ?? ''), 'Unit' => trim($_POST['Unit'] ?? ''),
                'Description' => trim($_POST['Description'] ?? null), 'Manufacturer' => trim($_POST['Manufacturer'] ?? null),
                'StockQuantity' => filter_var($_POST['StockQuantity'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]])
            ];
            if (empty($data['input']['Name'])) $data['errors']['Name'] = 'Medicine name is required.';
            if (empty($data['input']['Unit'])) $data['errors']['Unit'] = 'Medicine unit is required.';
            if ($this->medicineModel->findByNameAndUnit($data['input']['Name'], $data['input']['Unit'])) {
                $data['errors']['Name'] = 'This medicine (Name and Unit combination) already exists.';
            }
            if (empty($data['errors'])) {
                if ($this->medicineModel->create($data['input'])) {
                    $_SESSION['admin_medicine_message_success'] = 'Medicine added successfully.';
                    header('Location: ' . BASE_URL . '/admin/listMedicines');
                    exit();
                } else {
                    $data['errors'][] = 'Failed to add medicine to the database.';
                }
            }
        }
        $this->view('admin/medicines/form', $data);
    }

    public function editMedicine($medicineId = 0) {
        $medicineId = (int)$medicineId;
        if ($medicineId <= 0) { header('Location: ' . BASE_URL . '/admin/listMedicines'); exit; }
        $medicine = $this->medicineModel->findById($medicineId);
        if (!$medicine) {
            $_SESSION['admin_medicine_message_error'] = 'Medicine not found.';
            header('Location: ' . BASE_URL . '/admin/listMedicines');
            exit();
        }
        $data = [
            'title' => 'Edit Medicine - ' . htmlspecialchars($medicine['Name']),
            'medicine' => $medicine, 'input' => $medicine,
            'errors' => [], 'medicineId' => $medicineId
        ];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $data['input'] = [
                'Name' => trim($_POST['Name'] ?? $medicine['Name']), 'Unit' => trim($_POST['Unit'] ?? $medicine['Unit']),
                'Description' => trim($_POST['Description'] ?? $medicine['Description']), 'Manufacturer' => trim($_POST['Manufacturer'] ?? $medicine['Manufacturer']),
                'StockQuantity' => filter_var($_POST['StockQuantity'] ?? $medicine['StockQuantity'], FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]])
            ];
            $data['input']['MedicineID'] = $medicineId;
            if (empty($data['input']['Name'])) $data['errors']['Name'] = 'Medicine name is required.';
            if (empty($data['input']['Unit'])) $data['errors']['Unit'] = 'Medicine unit is required.';
            if ($this->medicineModel->findByNameAndUnit($data['input']['Name'], $data['input']['Unit'], $medicineId)) {
                $data['errors']['Name'] = 'Another medicine with this Name and Unit combination already exists.';
            }
            if (empty($data['errors'])) {
                if ($this->medicineModel->update($medicineId, $data['input'])) {
                    $_SESSION['admin_medicine_message_success'] = 'Medicine updated successfully.';
                    header('Location: ' . BASE_URL . '/admin/listMedicines');
                    exit();
                } else {
                    $data['errors'][] = 'Failed to update medicine in the database.';
                }
            }
        }
        $this->view('admin/medicines/form', $data);
    }

    public function deleteMedicine() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/admin/listMedicines'); exit; }
        $medicineId = $_POST['medicine_id_to_delete'] ?? null;
        if (!filter_var($medicineId, FILTER_VALIDATE_INT) || $medicineId <= 0) {
            $_SESSION['admin_medicine_message_error'] = 'Invalid Medicine ID for deletion.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listMedicines'));
            exit();
        }
        $usageCount = $this->medicineModel->countUsageInPrescriptions((int)$medicineId);
        if ($usageCount > 0) {
            $_SESSION['admin_medicine_message_error'] = "Cannot delete this medicine. It is currently used in {$usageCount} prescription(s).";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listMedicines'));
            exit();
        }
        if ($this->medicineModel->delete((int)$medicineId)) {
            if ($this->db->rowCount() > 0) {
                $_SESSION['admin_medicine_message_success'] = 'Medicine deleted successfully.';
            } else {
                $_SESSION['admin_medicine_message_error'] = 'Medicine not found or already deleted.';
            }
        } else {
            $_SESSION['admin_medicine_message_error'] = 'Failed to delete medicine.';
        }
        header('Location: ' . BASE_URL . '/admin/listMedicines');
        exit();
    }
   protected function authAdmin($redirect = true) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            if ($redirect) {
                $_SESSION['error_message'] = "Unauthorized access to admin area.";
                header('Location: ' . BASE_URL . '/auth/login');
                exit();
            }
            return false;
        }
        return true;
    }

    public function listAllAppointments() {
        $filters = [
            'date_from' => trim($_GET['date_from'] ?? ''), 'date_to' => trim($_GET['date_to'] ?? ''),
            'doctor_id' => filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT),
            'patient_search' => trim($_GET['patient_search'] ?? ''), 'status' => $_GET['status'] ?? 'All'
        ];
        $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
        if (!in_array($filters['status'], $validStatuses)) $filters['status'] = 'All';
        $appointments = $this->appointmentModel->getAllAppointmentsForAdmin($filters);
        $doctorsForFilter = $this->doctorModel->getAllActiveDoctorsWithSpecialization();
        $data = [
            'title' => 'View All Appointments', 'appointments' => $appointments,
            'filters' => $filters, 'doctorsForFilter' => $doctorsForFilter,
            'allStatuses' => $validStatuses
        ];
        $this->view('admin/appointments/list', $data);
    }

    public function cancelAppointmentByAdmin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['admin_appointment_message_error'] = 'Invalid request method.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listAllAppointments')); exit();
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['admin_appointment_message_error'] = 'Invalid CSRF token.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listAllAppointments')); exit();
        }
        $appointmentIdToCancel = filter_input(INPUT_POST, 'appointment_id_to_cancel', FILTER_VALIDATE_INT);
        if (!$appointmentIdToCancel || $appointmentIdToCancel <= 0) {
            $_SESSION['admin_appointment_message_error'] = 'Invalid Appointment ID provided for cancellation.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listAllAppointments')); exit();
        }
        $appointmentDetails = $this->appointmentModel->getAppointmentById($appointmentIdToCancel);
        if (!$appointmentDetails) {
            $_SESSION['admin_appointment_message_error'] = "Appointment #{$appointmentIdToCancel} not found.";
            header('Location: ' . BASE_URL . '/admin/listAllAppointments'); exit();
        }
        if (!in_array($appointmentDetails['Status'], ['Scheduled', 'Confirmed'])) {
            $_SESSION['admin_appointment_message_error'] = "Appointment #{$appointmentIdToCancel} cannot be cancelled (Status: " . htmlspecialchars($appointmentDetails['Status']) . ").";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listAllAppointments')); exit();
        }
        $this->db->beginTransaction();
        try {
            if (!$this->appointmentModel->updateAppointmentStatus($appointmentIdToCancel, 'CancelledByClinic')) throw new Exception('Failed to update appointment status.');
            if (!empty($appointmentDetails['AvailabilityID'])) {
                if (!$this->doctorAvailabilityModel->markSlotAsAvailableAgain($appointmentDetails['AvailabilityID'])) {
                    error_log("Admin cancel: Failed to mark slot {$appointmentDetails['AvailabilityID']} as available for appt {$appointmentIdToCancel}.");
                }
            }
            $this->db->commit();
            $_SESSION['admin_appointment_message_success'] = "Appointment #{$appointmentIdToCancel} has been successfully cancelled by Admin.";
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Admin cancel appointment error for #{$appointmentIdToCancel}: " . $e->getMessage());
            $_SESSION['admin_appointment_message_error'] = 'Error cancelling appointment: ' . $e->getMessage();
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listAllAppointments'));
        exit();
    }


    public function updateProfile() {
        $userId = $_SESSION['user_id'];
        $currentUser = $this->userModel->findUserById($userId);

        if (!$currentUser) {
            $_SESSION['profile_message_error'] = 'Could not retrieve your profile information.';
            session_destroy();
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $data = [
            'title' => 'Update My Admin Profile',
            'user' => $currentUser,
            'input' => (array) $currentUser,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $data['input'] = [
                'FullName' => trim($_POST['FullName'] ?? $currentUser['FullName']),
                'Email' => trim($_POST['Email'] ?? $currentUser['Email']),
                'Username' => trim($currentUser['Username']), 
                'PhoneNumber' => trim($_POST['PhoneNumber'] ?? $currentUser['PhoneNumber']),
                'Address' => trim($_POST['Address'] ?? $currentUser['Address']),
                'current_password' => $_POST['current_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
                'confirm_new_password' => $_POST['confirm_new_password'] ?? ''
            ];
            $data['input']['Avatar'] = $currentUser['Avatar'];


            if (empty($data['input']['FullName'])) $data['errors']['FullName'] = 'Full name is required.';
            
            if (empty($data['input']['Email'])) {
                $data['errors']['Email'] = 'Email is required.';
            } elseif (!filter_var($data['input']['Email'], FILTER_VALIDATE_EMAIL)) {
                $data['errors']['Email'] = 'Invalid email format.';
            } elseif (strtolower($data['input']['Email']) !== strtolower($currentUser['Email']) && $this->userModel->findUserByEmail($data['input']['Email'], $userId)) {
                $data['errors']['Email'] = 'This email is already registered by another user.';
            }

            $updatePassword = false;
            if (!empty($data['input']['new_password'])) {
                if (empty($data['input']['current_password'])) {
                    $data['errors']['current_password'] = 'Please enter your current password to set a new one.';
                } elseif (!password_verify($data['input']['current_password'], $currentUser['PasswordHash'])) {
                     $data['errors']['current_password'] = 'Incorrect current password.';
                }
                if (strlen($data['input']['new_password']) < 6) {
                    $data['errors']['new_password'] = 'New password must be at least 6 characters.';
                }
                if ($data['input']['new_password'] !== $data['input']['confirm_new_password']) {
                    $data['errors']['confirm_new_password'] = 'New passwords do not match.';
                }
                if (empty($data['errors']['current_password']) && empty($data['errors']['new_password']) && empty($data['errors']['confirm_new_password'])) {
                    $updatePassword = true;
                }
            }

            $avatarPathForDB = $currentUser['Avatar']; 
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
                        $newFileName = "avatar_admin_" . $userId . "_" . uniqid('', true) . "." . $fileExt;
                        $uploadDirRelative = 'uploads/avatars/'; 
                        $uploadDirAbsolute = rtrim(PUBLIC_PATH, '/') . '/' . $uploadDirRelative;

                        if (!file_exists($uploadDirAbsolute)) {
                            if (!mkdir($uploadDirAbsolute, 0775, true) && !is_dir($uploadDirAbsolute)) {
                                 $data['errors']['profile_avatar'] = 'Failed to create upload directory. Please check permissions for ' . htmlspecialchars($uploadDirAbsolute);
                            }
                        }

                        if (empty($data['errors']['profile_avatar']) && is_writable($uploadDirAbsolute)) { 
                            $fileDestinationOnServer = $uploadDirAbsolute . $newFileName;
                            if (move_uploaded_file($fileTmpName, $fileDestinationOnServer)) {
                                if (!empty($currentUser['Avatar']) && 
                                    $currentUser['Avatar'] !== 'assets/images/default_avatar.png' && 
                                    file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($currentUser['Avatar'], '/'))) {
                                    @unlink(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($currentUser['Avatar'], '/'));
                                }
                                $avatarPathForDB = rtrim($uploadDirRelative, '/') . '/' . $newFileName; 
                                $data['input']['Avatar'] = $avatarPathForDB; 
                            } else {
                                $data['errors']['profile_avatar'] = "Failed to move uploaded file. Check server permissions for " . htmlspecialchars($uploadDirAbsolute);
                                error_log("move_uploaded_file failed for admin {$userId}. Temp: {$fileTmpName}, Dest: {$fileDestinationOnServer}");
                            }
                        } elseif (!is_writable($uploadDirAbsolute) && empty($data['errors']['profile_avatar'])) {
                            $data['errors']['profile_avatar'] = 'Upload directory is not writable: ' . htmlspecialchars($uploadDirAbsolute);
                        }
                    } else {
                        $data['errors']['profile_avatar'] = "Your file is too large (max 5MB).";
                    }
                } else {
                    $data['errors']['profile_avatar'] = "Invalid file type (allowed: jpg, jpeg, png, gif).";
                }
            } elseif (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] != UPLOAD_ERR_NO_FILE) {
                $data['errors']['profile_avatar'] = "An error occurred during file upload. Error code: " . $_FILES['profile_avatar']['error'];
            }

            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $data['errors']['csrf'] = 'Invalid CSRF token. Action aborted.';
            }

            if (empty($data['errors'])) {
                $this->db->beginTransaction();
                try {
                    $phoneNumberToSave = trim($data['input']['PhoneNumber']);
                    if ($phoneNumberToSave === '') {
                        $phoneNumberToSave = null; 
                    }

                    $userDataToUpdate = [
                        'FullName' => $data['input']['FullName'],
                        'Email' => $data['input']['Email'],
                        'PhoneNumber' => $phoneNumberToSave,
                        'Address' => $data['input']['Address'],
                    ];
                    
                    if ($avatarPathForDB !== $currentUser['Avatar']) {
                        $userDataToUpdate['Avatar'] = $avatarPathForDB;
                    }

                    $userUpdateSuccess = $this->userModel->updateUser($userId, $userDataToUpdate);
                    
                    $passwordUpdateSuccess = true; 
                    if ($updatePassword) {
                        $newPasswordHash = password_hash($data['input']['new_password'], PASSWORD_DEFAULT);
                        $passwordUpdateSuccess = $this->userModel->updatePassword($userId, $newPasswordHash);
                    }

                    if ($userUpdateSuccess && $passwordUpdateSuccess) {
                        $this->db->commit();
                        $_SESSION['profile_message_success'] = 'Admin profile updated successfully.';
                        
                        $_SESSION['user_fullname'] = $data['input']['FullName'];
                        $_SESSION['user_email'] = $data['input']['Email'];
                        if ($avatarPathForDB !== $currentUser['Avatar']) {
                             $_SESSION['user_avatar'] = $avatarPathForDB; 
                        }
                        

                        $this->updateProfile(); 
                        return;
                    } else {
                        $this->db->rollBack();
                        if ($avatarFileUploaded && $avatarPathForDB !== $currentUser['Avatar'] && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($avatarPathForDB, '/'))) {
                            @unlink(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($avatarPathForDB, '/')); 
                        }
                        $data['errors']['database'] = 'Failed to update profile in database. Please try again.';
                    }
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) $this->db->rollBack();
                    if ($avatarFileUploaded && $avatarPathForDB !== $currentUser['Avatar'] && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($avatarPathForDB, '/'))) {
                        @unlink(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($avatarPathForDB, '/')); 
                    }
                    error_log("Error updating admin profile {$userId}: " . $e->getMessage());
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'PhoneNumber') !== false) {
                        $data['errors']['PhoneNumber'] = 'This phone number is already in use or there was an issue saving it. Please try a different one or leave it blank.';
                    } else {
                        $data['errors']['exception'] = 'An unexpected error occurred. Please try again later.';
                    }
                }
            }
        }
        $this->view('admin/profile/update', $data);
    }


    public function manageFeedbacks() {
        $filters = [
            'doctor_id' => filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT),
            'rating' => filter_input(INPUT_GET, 'rating', FILTER_VALIDATE_INT),
            'is_published' => filter_input(INPUT_GET, 'is_published', FILTER_SANITIZE_SPECIAL_CHARS),
            'search_term' => trim(filter_input(INPUT_GET, 'search_term', FILTER_SANITIZE_SPECIAL_CHARS) ?? '')
        ];

        $feedbacks = $this->feedbackModel->getAllFeedbacksWithDetails($filters);
        $doctors = $this->doctorModel->getAllDoctorsSimple(); // Lấy danh sách bác sĩ để lọc
        
        $data = [
            'title' => 'Manage Patient Feedbacks',
            'feedbacks' => $feedbacks,
            'doctors' => $doctors, 
            'filters' => $filters 
        ];
        
        $this->view('admin/feedbacks/manage', $data);
    }

    public function toggleFeedbackPublication() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['admin_feedback_message_error'] = 'Invalid request method.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/manageFeedbacks')); 
            exit();
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['admin_feedback_message_error'] = 'Invalid CSRF token.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/manageFeedbacks')); 
            exit();
        }

        $feedbackId = filter_input(INPUT_POST, 'feedback_id', FILTER_VALIDATE_INT);
        $currentStatus = filter_input(INPUT_POST, 'current_status', FILTER_VALIDATE_INT);

        if (!$feedbackId || $feedbackId <= 0 || !in_array($currentStatus, [0, 1])) {
            $_SESSION['admin_feedback_message_error'] = 'Invalid feedback data provided.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/manageFeedbacks')); 
            exit();
        }

        $newStatus = ($currentStatus == 1) ? 0 : 1;
        
        if ($this->feedbackModel->updateFeedbackPublicationStatus($feedbackId, $newStatus)) {
            $actionText = $newStatus == 1 ? 'published' : 'hidden';
            $_SESSION['admin_feedback_message_success'] = "Feedback #{$feedbackId} has been successfully {$actionText}.";
        } else {
            $_SESSION['admin_feedback_message_error'] = "Failed to update publication status for feedback #{$feedbackId}.";
        }
        
        header('Location: ' . BASE_URL . '/admin/manageFeedbacks');
        exit();
    }
    public function databaseManagement() {
        $backupFiles = [];
        if (is_dir($this->backupPath)) {
            $files = glob($this->backupPath . '*.sql');
            if ($files) {
                foreach ($files as $file) {
                    $backupFiles[] = [
                        'name' => basename($file),
                        'size' => filesize($file),
                        'date' => filemtime($file)
                    ];
                }
                usort($backupFiles, function($a, $b) {
                    return $b['date'] <=> $a['date'];
                });
            }
        }
        $data = [
            'title' => 'Database Backup & Restore',
            'backupFiles' => $backupFiles,
            'backupPath' => $this->backupPath,
            'isWritable' => is_writable($this->backupPath)
        ];
        $this->view('admin/database_management', $data);
    }

    public function createBackup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/databaseManagement'); exit();
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['db_message_error'] = 'Invalid CSRF token.';
            header('Location: ' . BASE_URL . '/admin/databaseManagement'); exit();
        }

        if (!is_dir($this->backupPath)) {
            if (!mkdir($this->backupPath, 0775, true)) {
                 $_SESSION['db_message_error'] = 'Failed to create backup directory: ' . htmlspecialchars($this->backupPath);
                 header('Location: ' . BASE_URL . '/admin/databaseManagement'); exit();
            }
        }

        if (!is_writable($this->backupPath)) {
            $_SESSION['db_message_error'] = 'Backup directory is not writable: ' . htmlspecialchars($this->backupPath);
            header('Location: ' . BASE_URL . '/admin/databaseManagement'); exit();
        }

        $dbConfig = require __DIR__ . '/../../config/database.php';
        $dbName = $dbConfig['name'];
        $dbUser = $dbConfig['user'];
        $dbPass = $dbConfig['password']; 
        $dbHost = $dbConfig['host'];

        $mysqldumpExecutable = 'C:/xampp/mysql/bin/mysqldump.exe'; 

        $fileName = $dbName . '_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = $this->backupPath . $fileName;
        
        $passwordArg = !empty($dbPass) ? '--password=' . escapeshellarg($dbPass) : '';
        
        $command = sprintf('%s --host=%s --user=%s %s --result-file=%s %s',
            escapeshellarg($mysqldumpExecutable),
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            $passwordArg, 
            escapeshellarg($filePath), 
            escapeshellarg($dbName)
        );
        
        @exec($command, $output, $return_var);

        if ($return_var === 0 && file_exists($filePath) && filesize($filePath) > 0) {
            $_SESSION['db_message_success'] = 'Database backup created successfully: ' . htmlspecialchars($fileName);
        } else {
            $_SESSION['db_message_error'] = 'Failed to create database backup. Please check server configuration, paths, and permissions.';
            error_log("mysqldump failed. Return var: $return_var. Command: $command. Output: " . implode("\n", $output));
            if (file_exists($filePath) && filesize($filePath) === 0) { 
                unlink($filePath);
            }
        }
        header('Location: ' . BASE_URL . '/admin/databaseManagement');
        exit();
    }

    public function restoreBackup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/databaseManagement'); exit();
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['db_message_error'] = 'Invalid CSRF token.';
            header('Location: ' . BASE_URL . '/admin/databaseManagement'); exit();
        }

        $fileName = $_POST['backup_file'] ?? '';
        $filePath = $this->backupPath . basename($fileName);

        if (empty($fileName) || !file_exists($filePath) || strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'sql') {
            $_SESSION['db_message_error'] = 'Invalid or non-existent SQL backup file selected.';
            header('Location: ' . BASE_URL . '/admin/databaseManagement'); exit();
        }

        $dbConfig = require __DIR__ . '/../../config/database.php';
        $dbName = $dbConfig['name'];
        $dbUser = $dbConfig['user'];
        $dbPass = $dbConfig['password']; 
        $dbHost = $dbConfig['host'];

        $mysqlExecutable = 'C:/xampp/mysql/bin/mysql.exe'; 

        $passwordArg = !empty($dbPass) ? '--password=' . escapeshellarg($dbPass) : '';

        $command = sprintf('%s --host=%s --user=%s %s %s < %s',
            escapeshellarg($mysqlExecutable),
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            $passwordArg, 
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );
        
        @exec($command, $output, $return_var);

        if ($return_var === 0) {
            $_SESSION['db_message_success'] = 'Database restored successfully from ' . htmlspecialchars($fileName) . '. It might be good to log out and log back in.';
        } else {
            $_SESSION['db_message_error'] = 'Failed to restore database. An error occurred.';
            error_log("mysql restore failed. Return var: $return_var. Command: $command. Output: " . implode("\n", $output));
        }
        header('Location: ' . BASE_URL . '/admin/databaseManagement');
        exit();
    }
    public function manageLeaveRequests() {
        $filters = [
            'status' => $_GET['status'] ?? 'All', 
            'doctor_id' => filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? '')
        ];

        $leaveRequests = $this->leaveRequestModel->getAllLeaveRequests($filters);
        $doctors = $this->doctorModel->getAllDoctorsSimple(); 

        $data = [
            'title' => 'Manage Leave Requests',
            'leaveRequests' => $leaveRequests,
            'doctors' => $doctors,
            'currentFilters' => $filters,
            'allStatuses' => ['All', 'Pending', 'Approved', 'Rejected', 'Cancelled']
        ];
        $this->view('admin/leave/manage_requests', $data);
    }

    public function reviewLeaveRequest($leaveRequestId = 0) {
        $leaveRequestId = (int)$leaveRequestId;
        if ($leaveRequestId <= 0) {
            $_SESSION['admin_message_error'] = 'Invalid leave request ID.';
            header('Location: ' . BASE_URL . '/admin/manageLeaveRequests');
            exit();
        }

        $leaveRequest = $this->leaveRequestModel->getLeaveRequestById($leaveRequestId);
        if (!$leaveRequest) {
            $_SESSION['admin_message_error'] = 'Leave request not found.';
            header('Location: ' . BASE_URL . '/admin/manageLeaveRequests');
            exit();
        }

        $overlappingAvailability = $this->leaveRequestModel->getOverlappingAvailability($leaveRequest['DoctorID'], $leaveRequest['StartDate'], $leaveRequest['EndDate']);
        $overlappingAppointments = $this->leaveRequestModel->getOverlappingAppointments($leaveRequest['DoctorID'], $leaveRequest['StartDate'], $leaveRequest['EndDate']);

        $data = [
            'title' => 'Review Leave Request',
            'leaveRequest' => $leaveRequest,
            'overlappingAvailability' => $overlappingAvailability,
            'overlappingAppointments' => $overlappingAppointments,
            'input' => $_SESSION['review_leave_input'] ?? ['admin_notes' => $leaveRequest['AdminNotes'] ?? ''],
            'errors' => $_SESSION['review_leave_errors'] ?? []
        ];
        unset($_SESSION['review_leave_input'], $_SESSION['review_leave_errors']);

        $this->view('admin/leave/review_form', $data);
    }

    public function processLeaveReview($leaveRequestId = 0) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/manageLeaveRequests');
            exit();
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['admin_message_error'] = 'Invalid security token.';
            header('Location: ' . BASE_URL . '/admin/reviewLeaveRequest/' . $leaveRequestId);
            exit();
        }

        $leaveRequestId = (int)$leaveRequestId;
        if ($leaveRequestId <= 0) {
            $_SESSION['admin_message_error'] = 'Invalid leave request ID for processing.';
            header('Location: ' . BASE_URL . '/admin/manageLeaveRequests');
            exit();
        }

        $leaveRequest = $this->leaveRequestModel->getLeaveRequestById($leaveRequestId);
        if (!$leaveRequest) {
            $_SESSION['admin_message_error'] = 'Leave request not found for processing.';
            header('Location: ' . BASE_URL . '/admin/manageLeaveRequests');
            exit();
        }
        if (in_array($leaveRequest['Status'], ['Approved', 'Rejected'])) {
             $_SESSION['admin_message_error'] = 'This leave request has already been processed.';
             header('Location: ' . BASE_URL . '/admin/reviewLeaveRequest/' . $leaveRequestId);
             exit();
        }


        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $newStatus = trim($_POST['status'] ?? '');
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $adminUserId = $_SESSION['user_id'];

        $errors = [];
        $input = ['admin_notes' => $adminNotes, 'status' => $newStatus];

        if (!in_array($newStatus, ['Approved', 'Rejected'])) {
            $errors['status'] = 'Invalid status selected for review.';
        }
        if (strlen($adminNotes) > 1000) {
            $errors['admin_notes'] = 'Admin notes are too long (max 1000 characters).';
        }

        if (!empty($errors)) {
            $_SESSION['review_leave_input'] = $input;
            $_SESSION['review_leave_errors'] = $errors;
            header('Location: ' . BASE_URL . '/admin/reviewLeaveRequest/' . $leaveRequestId);
            exit();
        }

        $this->db->beginTransaction();
        try {
            if ($this->leaveRequestModel->updateLeaveRequestStatus($leaveRequestId, $newStatus, $adminNotes, $adminUserId)) {
                
                if ($newStatus === 'Approved') {
                    $conflictingAppointments = $this->leaveRequestModel->getOverlappingAppointments($leaveRequest['DoctorID'], $leaveRequest['StartDate'], $leaveRequest['EndDate'], ['Scheduled', 'Confirmed']);
                    foreach ($conflictingAppointments as $appt) {
                        $this->appointmentModel->updateAppointmentStatus($appt['AppointmentID'], 'CancelledByClinic');
                        error_log("Admin approved leave: Appointment #{$appt['AppointmentID']} for Doctor #{$leaveRequest['DoctorID']} was auto-cancelled due to approved leave.");
                    }
                }
                
                $this->db->commit();
                $_SESSION['admin_message_success'] = "Leave request #{$leaveRequestId} has been {$newStatus}.";

            } else {
                $this->db->rollBack();
                $_SESSION['admin_message_error'] = 'Failed to process leave request. Please try again.';
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error processing leave review for #{$leaveRequestId}: " . $e->getMessage());
            $_SESSION['admin_message_error'] = 'An unexpected error occurred: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/admin/manageLeaveRequests');
        exit();
    }

    public function manageDoctorNurseAssignments() {
        $doctors = $this->doctorModel->getAllDoctorsWithDetails(); 
        $allNurses = $this->nurseModel->getAllNursesWithUserDetails(); 
        
        $assignmentsData = [];
        if (is_array($doctors)) { // Check if $doctors is an array before looping
            foreach ($doctors as $doctor) {
                $assignedNurses = $this->doctorNurseAssignmentModel->getAssignedNursesByDoctorId($doctor['DoctorID']);
                $unassignedNursesForThisDoctor = $this->doctorNurseAssignmentModel->getUnassignedNursesForDoctor($doctor['DoctorID'], $allNurses);
                
                $assignmentsData[] = [
                    'doctor_id' => $doctor['DoctorID'],
                    'doctor_name' => $doctor['FullName'], 
                    'doctor_specialization' => $doctor['SpecializationName'] ?? 'N/A', 
                    'assigned_nurses' => $assignedNurses,
                    'available_nurses_for_assignment' => $unassignedNursesForThisDoctor
                ];
            }
        }


        $data = [
            'title' => 'Manage Doctor-Nurse Assignments',
            'assignments_data' => $assignmentsData,
        ];
        $this->view('admin/assignments/manage', $data); 
    }

    public function processAssignment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['assignment_message_error'] = 'Invalid request method.';
            $this->manageDoctorNurseAssignments(); 
            return;
        }
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['assignment_message_error'] = 'Invalid CSRF token.';
            $this->manageDoctorNurseAssignments();
            return;
        }

        $doctorId = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
        $nurseId = filter_input(INPUT_POST, 'nurse_id', FILTER_VALIDATE_INT);
        $actionType = trim($_POST['action_type'] ?? ''); 

        if (!$doctorId || !$nurseId || !in_array($actionType, ['assign', 'unassign'])) {
            $_SESSION['assignment_message_error'] = 'Invalid data provided for assignment processing.';
            $this->manageDoctorNurseAssignments();
            return;
        }

        $result = null;
        if ($actionType === 'assign') {
            $result = $this->doctorNurseAssignmentModel->assignNurseToDoctor($doctorId, $nurseId);
        } elseif ($actionType === 'unassign') {
            $result = $this->doctorNurseAssignmentModel->unassignNurseFromDoctor($doctorId, $nurseId);
        }

        if ($result && isset($result['success']) && $result['success']) { 
            $_SESSION['assignment_message_success'] = $result['message'];
        } else {
            $_SESSION['assignment_message_error'] = $result['message'] ?? 'An unknown error occurred during assignment processing.';
        }
        
        $this->manageDoctorNurseAssignments();
    }
}
?>