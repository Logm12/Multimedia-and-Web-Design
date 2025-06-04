<?php
// app/controllers/AdminController.php

class AdminController {
    private $userModel;
    private $specializationModel;
    private $db;
    private $doctorModel; // Thêm nếu bạn cần quản lý bác sĩ
    private $mailService;
    private $medicineModel;
    private $appointmentModel;
    public function __construct() {
        // Khởi tạo các model cần thiết sau này khi làm CRUD
         $this->userModel = new UserModel();
        $this->specializationModel = new SpecializationModel();
        $this->db = Database::getInstance(); // Giả sử bạn có một lớp Database để quản lý kết nối
        $this->doctorModel = new DoctorModel(); // Thêm nếu bạn cần quản lý bác sĩ
        $this->mailService = new MailService();
        $this->medicineModel = new MedicineModel();
        $this->appointmentModel = new AppointmentModel(); // Thêm nếu bạn cần quản lý lịch hẹn
    }
    // Phương thức mặc định khi truy cập /admin
    public function index() {
        // Gọi action dashboard để hiển thị trang tổng quan của admin
        $this->dashboard();
    }
    // Hàm để load view
    protected function view($view, $data = []) {
        // Đảm bảo chỉ Admin mới truy cập được các view của Admin (trừ trang login)
        // Bạn có thể thêm một lớp kiểm tra chung ở đây hoặc trong từng action
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            // Nếu không phải là trang login của admin (nếu có) thì mới redirect
            // Vì trang login có thể không cần check session này
            if ($view !== 'admin/login') { // Ví dụ nếu bạn có trang login riêng cho admin
                 $_SESSION['error_message'] = "Unauthorized access.";
                 header('Location: ' . BASE_URL . '/auth/login'); // Hoặc trang login chung
                 exit();
            }
        }

        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist.");
        }
    }

    public function dashboard() {
        // Kiểm tra đăng nhập và vai trò Admin (đã có trong hàm view, nhưng check lại cho chắc)
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
            header('Location: ' . BASE_URL . '/auth/login?redirect_message=Please log in as an Administrator.');
            exit();
        }

        $data = [
            'title' => 'Admin Dashboard',
            'welcome_message' => 'Welcome Administrator, ' . htmlspecialchars($_SESSION['user_fullname']) . '!'
        ];

        // Thêm các link chức năng cho Admin
        $data['links'] = [
            ['url' => BASE_URL . '/admin/listUsers', 'text' => 'Manage Users'],
            ['url' => BASE_URL . '/admin/manageSpecializations', 'text' => 'Manage Specializations'],
            ['url' => BASE_URL . '/admin/listMedicines', 'text' => 'Manage Medicines'],
            ['url' => BASE_URL . '/admin/listAllAppointments', 'text' => 'View All Appointments'],
            ['url' => BASE_URL . '/report/overview', 'text' => 'Reports & Statistics'],
            ['url' => BASE_URL . '/admin/updateProfile', 'text' => 'My Profile'],
            // Thêm các link khác sau này
        ];

        $this->view('admin/dashboard', $data);
    }

    // Các action khác cho Admin sẽ được thêm ở đây
    // Ví dụ: manageSpecializations, manageUsers, etc.
    // Trong __construct() của AdminController, thêm:
// private $specializationModel;
// public function __construct() {
//     $this->specializationModel = new SpecializationModel();
// }


// Action để quản lý chuyên khoa
public function manageSpecializations() {
    // Đảm bảo specializationModel được khởi tạo
    if (!isset($this->specializationModel)) {
        $this->specializationModel = new SpecializationModel();
    }

    $specializations = $this->specializationModel->getAll();
    $data = [
        'title' => 'Manage Specializations',
        'specializations' => $specializations
    ];
    $this->view('admin/manage_specializations', $data);
}

// Action để hiển thị form thêm/sửa chuyên khoa và xử lý
public function editSpecialization($id = null) {
    // Đảm bảo specializationModel được khởi tạo
    if (!isset($this->specializationModel)) {
        $this->specializationModel = new SpecializationModel();
    }

    $data = [
        'title' => $id ? 'Edit Specialization' : 'Add New Specialization',
        'specialization' => null,
        'errors' => [],
        'input_name' => '',
        'input_description' => ''
    ];

    if ($id) {
        $data['specialization'] = $this->specializationModel->findById((int)$id);
        if (!$data['specialization']) {
            $_SESSION['admin_message_error'] = 'Specialization not found.';
            header('Location: ' . BASE_URL . '/admin/manageSpecializations');
            exit();
        }
        $data['input_name'] = $data['specialization']['Name'];
        $data['input_description'] = $data['specialization']['Description'];
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // (Thêm CSRF Token Validation ở đây)

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? null);
        $currentId = $_POST['id'] ?? ($data['specialization']['SpecializationID'] ?? null); // Lấy ID nếu đang edit

        $data['input_name'] = $name;
        $data['input_description'] = $description;

        if (empty($name)) {
            $data['errors']['name'] = 'Specialization name cannot be empty.';
        } elseif ($this->specializationModel->findByName($name, $currentId)) {
             $data['errors']['name'] = 'This specialization name already exists.';
        }


        if (empty($data['errors'])) {
            if ($currentId) { // Update
                if ($this->specializationModel->update((int)$currentId, $name, $description)) {
                    $_SESSION['admin_message_success'] = 'Specialization updated successfully.';
                } else {
                    $_SESSION['admin_message_error'] = 'Failed to update specialization.';
                }
            } else { // Create
                if ($this->specializationModel->create($name, $description)) {
                    $_SESSION['admin_message_success'] = 'Specialization added successfully.';
                } else {
                    $_SESSION['admin_message_error'] = 'Failed to add specialization.';
                }
            }
            header('Location: ' . BASE_URL . '/admin/manageSpecializations');
            exit();
        }
    }
    $this->view('admin/edit_specialization', $data);
}

// Action để xóa chuyên khoa
public function deleteSpecialization($id = null) {
    // Đảm bảo specializationModel được khởi tạo
    if (!isset($this->specializationModel)) {
        $this->specializationModel = new SpecializationModel();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Nên dùng POST cho delete
        // (Thêm CSRF Token Validation ở đây)
        $id_to_delete = $_POST['id_to_delete'] ?? null;
        if ($id_to_delete && filter_var($id_to_delete, FILTER_VALIDATE_INT)) {
            // Thêm kiểm tra xem chuyên khoa có đang được sử dụng không trước khi xóa
            // Ví dụ: $doctorsWithSpec = $this->doctorModel->getDoctorsBySpecializationId($id_to_delete);
            // if (count($doctorsWithSpec) > 0) {
            //     $_SESSION['admin_message_error'] = 'Cannot delete specialization. It is currently in use by doctors.';
            // } else {
                if ($this->specializationModel->delete((int)$id_to_delete)) {
                    $_SESSION['admin_message_success'] = 'Specialization deleted successfully.';
                } else {
                    $_SESSION['admin_message_error'] = 'Failed to delete specialization.';
                }
            // }
        } else {
             $_SESSION['admin_message_error'] = 'Invalid ID for deletion.';
        }
        header('Location: ' . BASE_URL . '/admin/manageSpecializations');
        exit();
    } else {
        // Nếu truy cập trực tiếp qua GET, không cho phép hoặc hiển thị trang xác nhận
        $_SESSION['admin_message_error'] = 'Invalid request method for deletion.';
        header('Location: ' . BASE_URL . '/admin/manageSpecializations');
        exit();
    }
}


public function createUser() {
    // 1. Xác thực Admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
        $_SESSION['error_message'] = "Unauthorized access.";
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    // Khởi tạo các model (đã có trong __construct, không cần if(!isset) ở đây nữa)
    // $this->userModel, $this->specializationModel, $this->doctorModel

    $data = [
        'title' => 'Create New User',
        'specializations' => $this->specializationModel->getAllSpecializations(),
        'roles' => ['Doctor', 'Nurse'], // Admin có thể tạo các role này
        'input' => [],
        'errors' => []
    ];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $data['input'] = [
            'FullName' => trim($_POST['FullName'] ?? ''),
            'Username' => trim($_POST['Username'] ?? ''),
            'Email' => trim($_POST['Email'] ?? ''),
            // KHÔNG LẤY Password, ConfirmPassword từ POST nữa
            'PhoneNumber' => !empty(trim($_POST['PhoneNumber'])) ? trim($_POST['PhoneNumber']) : null, // Gán null nếu trống
            'Role' => $_POST['Role'] ?? '',
            'Status' => $_POST['Status'] ?? 'Pending', // Mặc định Status là Pending
            'SpecializationID' => $_POST['SpecializationID'] ?? null,
            'Bio' => trim($_POST['Bio'] ?? null),
            'ExperienceYears' => filter_var($_POST['ExperienceYears'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]),
            'ConsultationFee' => filter_var($_POST['ConsultationFee'] ?? 0.00, FILTER_VALIDATE_FLOAT, ['options' => ['default' => 0.00, 'decimal' => '.']]),
        ];

        // --- VALIDATION ---
        if (empty($data['input']['FullName'])) $data['errors'][] = 'Full Name is required.';
        if (empty($data['input']['Username'])) {
            $data['errors'][] = 'Username is required.';
        } elseif ($this->userModel->findUserByUsername($data['input']['Username'])) {
            $data['errors'][] = 'Username already exists.';
        }
        if (empty($data['input']['Email'])) {
            $data['errors'][] = 'Email is required.';
        } elseif (!filter_var($data['input']['Email'], FILTER_VALIDATE_EMAIL)) {
            $data['errors'][] = 'Invalid email format.';
        } elseif ($this->userModel->findUserByEmail($data['input']['Email'])) {
            $data['errors'][] = 'Email already exists.';
        }
        // BỎ VALIDATION CHO PASSWORD VÀ CONFIRMPASSWORD
        if (empty($data['input']['Role'])) $data['errors'][] = 'Role is required.';
        elseif (!in_array($data['input']['Role'], $data['roles'])) $data['errors'][] = 'Invalid role selected.';
        if (empty($data['input']['Status'])) $data['errors'][] = 'Status is required.';
        elseif (!in_array($data['input']['Status'], ['Active', 'Inactive', 'Pending'])) $data['errors'][] = 'Invalid status selected.';


        if ($data['input']['Role'] === 'Doctor') {
            // Bỏ bắt buộc SpecializationID nếu bạn muốn nó là tùy chọn
            // if (empty($data['input']['SpecializationID'])) {
            //     $data['errors'][] = 'Specialization is required for doctors.';
            // }
        }

        // (THÊM CSRF TOKEN VALIDATION Ở ĐÂY NẾU ĐÃ IMPLEMENT)
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) { // Giả sử bạn đã có hàm này
             $data['errors'][] = 'Invalid CSRF token. Action aborted.';
        }


        if (empty($data['errors'])) {
        $generatedPassword = bin2hex(random_bytes(6));
        $passwordHash = password_hash($generatedPassword, PASSWORD_DEFAULT);

        // Khởi tạo MailService
        if (!isset($this->mailService)) {
            $this->mailService = new MailService();
        }

        $this->db->beginTransaction(); // BẮT ĐẦU TRANSACTION CHỈ MỘT LẦN
        try {
            $userData = [
                'Username' => $data['input']['Username'],
                'PasswordHash' => $passwordHash,
                'Email' => $data['input']['Email'],
                'FullName' => $data['input']['FullName'],
                'Role' => $data['input']['Role'],
                'PhoneNumber' => $data['input']['PhoneNumber'] ?? null, // Đảm bảo model createUser xử lý được null
                'Address' => $data['input']['Address'] ?? null,       // Đảm bảo model createUser xử lý được null
                'Status' => $data['input']['Status']
            ];
            $newUserId = $this->userModel->createUser($userData);

            if (!$newUserId) {
                // Nếu tạo user cơ bản thất bại, ném Exception để rollback và báo lỗi
                throw new Exception('Failed to create base user account.');
            }

            // Tạo profile Doctor/Nurse nếu cần
            $profileCreated = true; // Giả sử thành công nếu không phải Doctor/Nurse
            if ($data['input']['Role'] === 'Doctor') {
                $doctorData = [
                    'SpecializationID' => $data['input']['SpecializationID'],
                    'Bio' => $data['input']['Bio'],
                    'ExperienceYears' => $data['input']['ExperienceYears'],
                    'ConsultationFee' => $data['input']['ConsultationFee']
                ];
                if (!$this->doctorModel->createDoctorProfile($newUserId, $doctorData)) {
                    throw new Exception('Failed to create Doctor profile details.');
                }
            } elseif ($data['input']['Role'] === 'Nurse') {
                // $nurseData = [ ... ];
                // if (!$this->nurseModel->createNurseProfile($newUserId, $nurseData)) {
                //     throw new Exception('Failed to create Nurse profile details.');
                // }
            }

            // Nếu tất cả các thao tác DB thành công (user và profile được tạo)
            $this->db->commit(); // COMMIT TRANSACTION Ở ĐÂY, SAU KHI MỌI THỨ DB OK

            // BÂY GIỜ MỚI GỬI EMAIL
            $emailSent = $this->mailService->sendWelcomeEmail(
                $data['input']['Email'],
                $data['input']['FullName'],
                $data['input']['Username'],
                $generatedPassword,
                $data['input']['Role']
            );

            if ($emailSent) {
                $_SESSION['user_management_message_success'] = ucfirst($data['input']['Role']) . " account '{$data['input']['Username']}' created. A welcome email with temporary password has been sent to {$data['input']['Email']}.";
            } else {
                // User vẫn được tạo thành công, nhưng email lỗi.
                $_SESSION['user_management_message_error'] = ucfirst($data['input']['Role']) . " account '{$data['input']['Username']}' created with Status: {$data['input']['Status']}, BUT FAILED TO SEND WELCOME EMAIL. Temporary Password: <strong>{$generatedPassword}</strong>. Please inform user manually.";
            }

            header('Location: ' . BASE_URL . '/admin/listUsers');
            exit();

        } catch (Exception $e) {
            // Nếu có bất kỳ Exception nào trong khối try (bao gồm cả Exception bạn tự ném ra)
            // thì rollback transaction (nếu nó vẫn còn active)
            if ($this->db->inTransaction()) { // Kiểm tra xem transaction có đang active không
                $this->db->rollBack();
            }
            error_log("Error creating user: " . $e->getMessage() . " Input: " . json_encode($data['input']));
            $data['errors'][] = 'An unexpected error occurred during user creation: ' . $e->getMessage();
        }
    }
    // Nếu có lỗi validation ban đầu hoặc lỗi trong khối try-catch, load lại view với $data
    $this->view('admin/users/create', $data);
}
$this->view('admin/users/create', $data);
}


public function listUsers() {
    // 1. Xác thực Admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
        $_SESSION['error_message'] = "Unauthorized access.";
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    // Khởi tạo UserModel nếu chưa có trong __construct
    if (!isset($this->userModel)) {
        $this->userModel = new UserModel();
    }

    $roleFilter = $_GET['role'] ?? 'All';
    $searchTerm = trim($_GET['search'] ?? '');

    // Mặc định hiển thị Active và Pending nếu không có status filter từ URL
    $statusFilterInput = $_GET['status'] ?? ['Active', 'Pending']; // Mảng làm mặc định

    // Validate $roleFilter
    $validRoles = ['All', 'Admin', 'Doctor', 'Nurse', 'Patient'];
    if (!in_array($roleFilter, $validRoles)) $roleFilter = 'All';

    // Xử lý $statusFilterInput cho việc truyền vào model và hiển thị trên view
    $statusFilterForModel = $statusFilterInput;
    $statusFilterForView = is_array($statusFilterInput) ? implode('_and_', $statusFilterInput) : $statusFilterInput; // Tạo một chuỗi đại diện cho view, ví dụ 'Active_and_Pending'

    // Nếu người dùng chọn "All Statuses" từ dropdown, thì $statusFilterForModel nên là 'All' hoặc null
    if (isset($_GET['status']) && $_GET['status'] === 'All') {
        $statusFilterForModel = 'All';
        $statusFilterForView = 'All';
    } elseif (isset($_GET['status']) && $_GET['status'] === 'Inactive') { // Xử lý khi người dùng chọn Inactive
        $statusFilterForModel = 'Inactive';
        $statusFilterForView = 'Inactive';
    } elseif (isset($_GET['status']) && $_GET['status'] === 'Active') {
        $statusFilterForModel = 'Active';
        $statusFilterForView = 'Active';
    } elseif (isset($_GET['status']) && $_GET['status'] === 'Pending') {
        $statusFilterForModel = 'Pending';
        $statusFilterForView = 'Pending';
    }
    // Nếu là mảng mặc định ['Active', 'Pending'] và không có GET['status'], statusFilterForModel đã đúng


    $users = $this->userModel->getAllUsers($roleFilter, $statusFilterForModel, $searchTerm);

    $data = [
        'title' => 'Manage Users',
        'users' => $users,
        'currentRoleFilter' => $roleFilter,
        'currentStatusFilter' => $statusFilterForView, // Dùng giá trị này cho selected trong dropdown
        'currentSearchTerm' => $searchTerm,
        'allRoles' => $validRoles,
        'allStatuses' => ['All', 'Active', 'Inactive', 'Pending', 'Active_and_Pending'] // Thêm lựa chọn mặc định vào đây
    ];
    $this->view('admin/users/list', $data);
}

public function updateUserStatus() {
    // 1. Xác thực Admin và phương thức POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
        $_SESSION['user_management_message_error'] = 'Unauthorized or invalid request.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
        exit();
    }

    // (THÊM CSRF TOKEN VALIDATION Ở ĐÂY)
    // if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    //     $_SESSION['user_management_message_error'] = 'Invalid CSRF token.';
    //     header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
    //     exit();
    // }

    $userIdToUpdate = $_POST['user_id'] ?? null;
    $newStatus = $_POST['new_status'] ?? null;

    if (!filter_var($userIdToUpdate, FILTER_VALIDATE_INT) || $userIdToUpdate <= 0) {
        $_SESSION['user_management_message_error'] = 'Invalid User ID.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
        exit();
    }

    // Khởi tạo UserModel nếu chưa có
    if (!isset($this->userModel)) {
        $this->userModel = new UserModel();
    }

    // Kiểm tra xem có đang cố gắng vô hiệu hóa chính tài khoản admin đang đăng nhập không
    if ($userIdToUpdate == $_SESSION['user_id'] && $newStatus === 'Inactive') {
        $_SESSION['user_management_message_error'] = 'You cannot deactivate your own account.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
        exit();
    }


    if ($this->userModel->updateUserStatus((int)$userIdToUpdate, $newStatus)) {
        $_SESSION['user_management_message_success'] = "User status updated successfully to '{$newStatus}'.";
    } else {
        $_SESSION['user_management_message_error'] = "Failed to update user status. Invalid status or user not found.";
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
    exit();
}
public function checkUsernameAvailability() {
    header('Content-Type: application/json');
    $username = $_GET['username'] ?? '';
    if (empty($username)) {
        echo json_encode(['available' => false, 'message' => 'Username cannot be empty.']);
        exit;
    }
    // Cần khởi tạo userModel
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
    // ... (tương tự như checkUsername, gọi userModel->findUserByEmail($email)) ...
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
    // 1. Xác thực Admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
        $_SESSION['error_message'] = "Unauthorized access.";
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    $userId = (int)$userId;
    if ($userId <= 0) {
        $_SESSION['user_management_message_error'] = 'Invalid User ID specified for editing.';
        header('Location: ' . BASE_URL . '/admin/listUsers');
        exit();
    }

    // Lấy thông tin user hiện tại để điền vào form
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
    // Tương tự cho Nurse nếu có
    // $nurseProfile = null;
    // if ($userToEdit['Role'] === 'Nurse') {
    //     $nurseProfile = $this->nurseModel->getNurseByUserId($userId);
    // }

    $data = [
        'title' => 'Edit User - ' . htmlspecialchars($userToEdit['FullName']),
        'userToEdit' => $userToEdit,
        'doctorProfile' => $doctorProfile,
        // 'nurseProfile' => $nurseProfile,
        'specializations' => $this->specializationModel->getAllSpecializations(),
        'roles' => ['Admin', 'Doctor', 'Nurse', 'Patient'], // Tất cả các vai trò có thể
        'statuses' => ['Active', 'Inactive', 'Pending'],
        'input' => array_merge($userToEdit, $doctorProfile ?? [] /*, $nurseProfile ?? [] */), // Dữ liệu ban đầu cho form
        'errors' => [],
        'userId' => $userId // Truyền userId cho form action hoặc hidden input
    ];
    // Ghi đè Address từ Users nếu có (vì getPatientDetailsById có UserAddress)
    // Nếu bạn lấy Address từ bảng Users cho Doctor/Nurse thì $userToEdit['Address'] là đủ
    if (isset($userToEdit['Address'])) {
        $data['input']['Address'] = $userToEdit['Address'];
    }


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        // (CSRF TOKEN VALIDATION)
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
             $data['errors'][] = 'Invalid CSRF token. Action aborted.';
        }


        $data['input']['FullName'] = trim($_POST['FullName'] ?? '');
        $data['input']['Email'] = trim($_POST['Email'] ?? '');
        // $data['input']['Username'] = trim($_POST['Username'] ?? ''); // Cân nhắc không cho sửa username
        $data['input']['Role'] = $_POST['Role'] ?? $userToEdit['Role']; // Giữ role cũ nếu không chọn
        $data['input']['Status'] = $_POST['Status'] ?? $userToEdit['Status'];
        $data['input']['PhoneNumber'] = trim($_POST['PhoneNumber'] ?? null);
        $data['input']['Address'] = trim($_POST['Address'] ?? null);

        // Mật khẩu mới (tùy chọn)
        $newPassword = $_POST['NewPassword'] ?? '';
        $confirmNewPassword = $_POST['ConfirmNewPassword'] ?? '';

        // Doctor specific fields (nếu role là Doctor)
        if ($data['input']['Role'] === 'Doctor') {
            $data['input']['SpecializationID'] = $_POST['SpecializationID'] ?? null;
            $data['input']['Bio'] = trim($_POST['Bio'] ?? null);
            $data['input']['ExperienceYears'] = filter_var($_POST['ExperienceYears'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
            $data['input']['ConsultationFee'] = filter_var($_POST['ConsultationFee'] ?? 0.00, FILTER_VALIDATE_FLOAT, ['options' => ['default' => 0.00, 'decimal' => '.']]);
        }

        // --- VALIDATION ---
        if (empty($data['input']['FullName'])) $data['errors'][] = 'Full Name is required.';
        if (empty($data['input']['Email'])) {
            $data['errors'][] = 'Email is required.';
        } elseif (!filter_var($data['input']['Email'], FILTER_VALIDATE_EMAIL)) {
            $data['errors'][] = 'Invalid email format.';
        } elseif ($this->userModel->findUserByEmail($data['input']['Email'], $userId)) { // Loại trừ user hiện tại
            $data['errors'][] = 'Email already exists for another user.';
        }
        // Không validate username nếu không cho sửa. Nếu cho sửa, thêm:
        // if (empty($data['input']['Username'])) $data['errors'][] = 'Username is required.';
        // elseif ($this->userModel->findUserByUsername($data['input']['Username'], $userId)) $data['errors'][] = 'Username already exists for another user.';

        if (empty($data['input']['Role'])) $data['errors'][] = 'Role is required.';
        elseif (!in_array($data['input']['Role'], $data['roles'])) $data['errors'][] = 'Invalid role selected.';
        if (empty($data['input']['Status'])) $data['errors'][] = 'Status is required.';
        elseif (!in_array($data['input']['Status'], $data['statuses'])) $data['errors'][] = 'Invalid status selected.';


        // Validate mật khẩu mới nếu được nhập
        $updatePassword = false;
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                $data['errors'][] = 'New password must be at least 6 characters.';
            }
            if ($newPassword !== $confirmNewPassword) {
                $data['errors'][] = 'New passwords do not match.';
            }
            if (empty($data['errors']['new_password']) && empty($data['errors']['confirm_new_password'])) { // Chỉ đặt cờ nếu không có lỗi pass
                $updatePassword = true;
            }
        }
        // ... (Validation cho Doctor fields nếu role là Doctor) ...


        if (empty($data['errors'])) {
            $this->db->beginTransaction();
            try {
                $userDataToUpdate = [
                    'FullName' => $data['input']['FullName'],
                    'Email' => $data['input']['Email'],
                    // 'Username' => $data['input']['Username'], // Nếu cho sửa
                    'Role' => $data['input']['Role'],
                    'Status' => $data['input']['Status'],
                    'PhoneNumber' => $data['input']['PhoneNumber'],
                    'Address' => $data['input']['Address']
                ];
                // Hàm updateUser trong UserModel cần được điều chỉnh để không update PasswordHash nếu không cần
                if (!$this->userModel->updateUser($userId, $userDataToUpdate)) {
                    throw new Exception('Failed to update user base information.');
                }

                if ($updatePassword) {
                    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    if (!$this->userModel->updatePassword($userId, $newPasswordHash)) {
                        throw new Exception('Failed to update password.');
                    }
                }

                // Cập nhật Doctor Profile nếu Role là Doctor
                if ($userToEdit['Role'] === 'Doctor' && $data['input']['Role'] === 'Doctor') { // Vẫn là Doctor
                    $doctorDataToUpdate = [
                        'SpecializationID' => $data['input']['SpecializationID'],
                        'Bio' => $data['input']['Bio'],
                        'ExperienceYears' => $data['input']['ExperienceYears'],
                        'ConsultationFee' => $data['input']['ConsultationFee']
                    ];
                    if (!$this->doctorModel->updateDoctorProfile($userId, $doctorDataToUpdate)) {
                        throw new Exception('Failed to update doctor profile.');
                    }
                } elseif ($userToEdit['Role'] === 'Doctor' && $data['input']['Role'] !== 'Doctor') {
                    // TODO: Xử lý khi Doctor bị đổi Role (ví dụ: xóa Doctor Profile)
                    // $this->doctorModel->deleteDoctorProfileByUserId($userId);
                } elseif ($userToEdit['Role'] !== 'Doctor' && $data['input']['Role'] === 'Doctor') {
                    // TODO: Xử lý khi User được đổi thành Doctor (tạo Doctor Profile mới)
                    // $this->doctorModel->createDoctorProfile($userId, $doctorDataFromInput);
                }
                // Tương tự cho Nurse

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
public function deleteUser() { // Thường dùng POST cho các hành động xóa
    // 1. Xác thực Admin và phương thức POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
        $_SESSION['user_management_message_error'] = 'Unauthorized or invalid request for deletion.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
        exit();
    }

    // (THÊM CSRF TOKEN VALIDATION Ở ĐÂY)
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) { // Giả sử hàm này đã có
        $_SESSION['user_management_message_error'] = 'Invalid CSRF token. Deletion aborted.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
        exit();
    }

    $userIdToDelete = $_POST['user_id_to_delete'] ?? null; // Đổi tên input để tránh nhầm lẫn

    if (!filter_var($userIdToDelete, FILTER_VALIDATE_INT) || $userIdToDelete <= 0) {
        $_SESSION['user_management_message_error'] = 'Invalid User ID for deletion.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
        exit();
    }

    // Không cho Admin tự xóa chính mình
    if ($userIdToDelete == $_SESSION['user_id']) {
        $_SESSION['user_management_message_error'] = 'You cannot delete your own account.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
        exit();
    }

    // Lấy thông tin user để kiểm tra vai trò (tùy chọn, nếu có logic đặc biệt khi xóa Doctor/Patient)
    // $userToDeleteInfo = $this->userModel->findUserById((int)$userIdToDelete);
    // if (!$userToDeleteInfo) {
    //     $_SESSION['user_management_message_error'] = 'User not found.';
    //     header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listUsers'));
    //     exit();
    // }

    // THỰC HIỆN SOFT DELETE BẰNG CÁCH ĐẶT STATUS LÀ 'Inactive' (hoặc 'Archived', 'Deleted')
    $newStatusForDeletedUser = 'Inactive'; // Hoặc 'Archived' nếu bạn thêm status này

    // Transaction nếu việc "xóa" liên quan đến nhiều bảng (ví dụ: xóa DoctorProfile, NurseProfile)
    $this->db->beginTransaction();
    try {
        if ($this->userModel->updateUserStatus((int)$userIdToDelete, $newStatusForDeletedUser)) {
            // Nếu là Doctor, có thể bạn muốn xóa hoặc vô hiệu hóa DoctorProfile liên quan
            // if ($userToDeleteInfo && $userToDeleteInfo['Role'] === 'Doctor') {
            //     // $this->doctorModel->deleteDoctorProfileByUserId((int)$userIdToDelete); // Hoặc set inactive
            // }
            // Tương tự cho Nurse

            $this->db->commit();
            $_SESSION['user_management_message_success'] = "User (ID: {$userIdToDelete}) has been marked as '{$newStatusForDeletedUser}' (soft deleted).";
        } else {
            // Lỗi này có thể xảy ra nếu $newStatusForDeletedUser không hợp lệ trong ENUM của DB
            // hoặc updateUserStatus trả về false do lỗi DB.
            throw new Exception("Failed to update user status for soft deletion.");
        }
    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Error soft deleting user {$userIdToDelete}: " . $e->getMessage());
        $_SESSION['user_management_message_error'] = 'An error occurred while trying to delete the user: ' . $e->getMessage();
    }

    header('Location: ' . BASE_URL . '/admin/listUsers'); // Luôn redirect về listUsers
    exit();
}
public function listMedicines() {
    $this->authAdmin(); // Hàm kiểm tra admin dùng chung

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
    $this->authAdmin();
    $data = [
        'title' => 'Add New Medicine',
        'input' => [],
        'errors' => []
    ];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // (CSRF Token Validation)
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['admin_medicine_message_error'] = 'Invalid CSRF token.';
            // Redirect hoặc load lại view với lỗi
            $this->view('admin/medicines/form', $data); // Load lại form với lỗi
            return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $data['input'] = [
            'Name' => trim($_POST['Name'] ?? ''),
            'Unit' => trim($_POST['Unit'] ?? ''),
            'Description' => trim($_POST['Description'] ?? null),
            'Manufacturer' => trim($_POST['Manufacturer'] ?? null),
            'StockQuantity' => filter_var($_POST['StockQuantity'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]])
        ];

        if (empty($data['input']['Name'])) $data['errors']['Name'] = 'Medicine name is required.';
        if (empty($data['input']['Unit'])) $data['errors']['Unit'] = 'Medicine unit is required.';
        if ($this->medicineModel->findByNameAndUnit($data['input']['Name'], $data['input']['Unit'])) {
            $data['errors']['Name'] = 'This medicine (Name and Unit combination) already exists.';
        }
        // Thêm validation khác nếu cần

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
    $this->view('admin/medicines/form', $data); // Hiển thị form cho GET hoặc nếu có lỗi POST
}

public function editMedicine($medicineId = 0) {
    $this->authAdmin();
    $medicineId = (int)$medicineId;
    if ($medicineId <= 0) { /* redirect hoặc báo lỗi */ header('Location: ' . BASE_URL . '/admin/listMedicines'); exit; }

    $medicine = $this->medicineModel->findById($medicineId);
    if (!$medicine) {
        $_SESSION['admin_medicine_message_error'] = 'Medicine not found.';
        header('Location: ' . BASE_URL . '/admin/listMedicines');
        exit();
    }

    $data = [
        'title' => 'Edit Medicine - ' . htmlspecialchars($medicine['Name']),
        'medicine' => $medicine, // Để phân biệt với $data['input'] khi submit
        'input' => $medicine, // Dữ liệu ban đầu cho form
        'errors' => [],
        'medicineId' => $medicineId
    ];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // (CSRF Token Validation)
        // ...

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $data['input'] = [ // Lấy dữ liệu mới từ POST, giữ lại giá trị cũ nếu POST không có
            'Name' => trim($_POST['Name'] ?? $medicine['Name']),
            'Unit' => trim($_POST['Unit'] ?? $medicine['Unit']),
            'Description' => trim($_POST['Description'] ?? $medicine['Description']),
            'Manufacturer' => trim($_POST['Manufacturer'] ?? $medicine['Manufacturer']),
            'StockQuantity' => filter_var($_POST['StockQuantity'] ?? $medicine['StockQuantity'], FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]])
        ];
        // Cập nhật medicineId vào input để dùng trong validation findByNameAndUnit
        $data['input']['MedicineID'] = $medicineId;


        if (empty($data['input']['Name'])) $data['errors']['Name'] = 'Medicine name is required.';
        if (empty($data['input']['Unit'])) $data['errors']['Unit'] = 'Medicine unit is required.';
        // Kiểm tra trùng, loại trừ ID hiện tại
        if ($this->medicineModel->findByNameAndUnit($data['input']['Name'], $data['input']['Unit'], $medicineId)) {
            $data['errors']['Name'] = 'Another medicine with this Name and Unit combination already exists.';
        }
        // Thêm validation khác nếu cần

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

public function deleteMedicine() { // Nên dùng POST
    $this->authAdmin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* redirect hoặc báo lỗi */ header('Location: ' . BASE_URL . '/admin/listMedicines'); exit; }

    // (CSRF Token Validation)
    // ...

    $medicineId = $_POST['medicine_id_to_delete'] ?? null;
    if (!filter_var($medicineId, FILTER_VALIDATE_INT) || $medicineId <= 0) {
        $_SESSION['admin_medicine_message_error'] = 'Invalid Medicine ID for deletion.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/admin/listMedicines'));
        exit();
    }

    // Kiểm tra xem thuốc có đang được sử dụng không
    $usageCount = $this->medicineModel->countUsageInPrescriptions((int)$medicineId);
    if ($usageCount > 0) {
        $_SESSION['admin_medicine_message_error'] = "Cannot delete this medicine. It is currently used in {$usageCount} prescription(s). Consider deactivating it instead if you have such a feature.";
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

// Helper function để kiểm tra quyền Admin, có thể đặt trong BaseController
protected function authAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
        $_SESSION['error_message'] = "Unauthorized access to admin area.";
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }
}
public function listAllAppointments() {
    $this->authAdmin(); // Hàm kiểm tra admin dùng chung

    // Khởi tạo các model cần thiết nếu chưa có trong __construct
    if (!isset($this->appointmentModel)) $this->appointmentModel = new AppointmentModel();
    if (!isset($this->doctorModel)) $this->doctorModel = new DoctorModel();


    // Lấy các tham số lọc từ GET request
    $filters = [
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null,
        'doctor_id' => $_GET['doctor_id'] ?? null,
        'patient_search' => trim($_GET['patient_search'] ?? ''),
        'status' => $_GET['status'] ?? 'All'
    ];

    // Validate status filter
    $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
    if (!in_array($filters['status'], $validStatuses)) {
        $filters['status'] = 'All';
    }

    // Lấy danh sách appointments
    $appointments = $this->appointmentModel->getAllAppointmentsForAdmin($filters);

    // Lấy danh sách bác sĩ để điền vào dropdown lọc
    $doctorsForFilter = $this->doctorModel->getAllActiveDoctorsWithSpecialization(); // Hoặc một hàm tương tự chỉ lấy ID và Tên


    $data = [
        'title' => 'View All Appointments',
        'appointments' => $appointments,
        'filters' => $filters, // Truyền lại các filter đã áp dụng cho view để điền vào form
        'doctorsForFilter' => $doctorsForFilter,
        'allStatuses' => $validStatuses // Để tạo select status filter
    ];

    $this->view('admin/appointments/list', $data);

}

public function updateProfile() {
    // 1. Xác thực Admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
        $_SESSION['error_message'] = "Unauthorized access.";
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    $userId = $_SESSION['user_id'];
    // UserModel đã được khởi tạo trong __construct

    $currentUser = $this->userModel->findUserById($userId); // Lấy thông tin User hiện tại
    if (!$currentUser) {
        // Xử lý trường hợp không tìm thấy user (hiếm khi xảy ra nếu session hợp lệ)
        $_SESSION['profile_message_error'] = 'Could not retrieve your profile information.';
        session_destroy(); // Đăng xuất luôn cho an toàn
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    $data = [
        'title' => 'Update My Admin Profile',
        'user' => $currentUser, // Dữ liệu hiện tại để điền form
        'input' => (array) $currentUser, // Giữ lại giá trị nếu có lỗi POST
        'errors' => []
    ];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

        $data['input'] = [
            'FullName' => trim($_POST['FullName'] ?? $currentUser['FullName']),
            'Email' => trim($_POST['Email'] ?? $currentUser['Email']),
            'Username' => trim($_POST['Username'] ?? $currentUser['Username']), // Nếu cho phép sửa Username
            'PhoneNumber' => trim($_POST['PhoneNumber'] ?? $currentUser['PhoneNumber']),
            'Address' => trim($_POST['Address'] ?? $currentUser['Address']),
            'current_password' => $_POST['current_password'] ?? '',
            'new_password' => $_POST['new_password'] ?? '',
            'confirm_new_password' => $_POST['confirm_new_password'] ?? ''
        ];

        // --- VALIDATION ---
        if (empty($data['input']['FullName'])) $data['errors']['FullName'] = 'Full name is required.';

        // Validate Email (chỉ kiểm tra trùng nếu email thay đổi)
        if (empty($data['input']['Email'])) {
            $data['errors']['Email'] = 'Email is required.';
        } elseif (!filter_var($data['input']['Email'], FILTER_VALIDATE_EMAIL)) {
            $data['errors']['Email'] = 'Invalid email format.';
        } elseif (strtolower($data['input']['Email']) !== strtolower($currentUser['Email']) && $this->userModel->findUserByEmail($data['input']['Email'], $userId)) {
            $data['errors']['Email'] = 'This email is already registered by another user.';
        }

        // Validate Username (nếu cho phép sửa và nó phải là duy nhất)
        // if (empty($data['input']['Username'])) {
        //     $data['errors']['Username'] = 'Username is required.';
        // } elseif (strtolower($data['input']['Username']) !== strtolower($currentUser['Username']) && $this->userModel->findUserByUsername($data['input']['Username'], $userId)) {
        //     $data['errors']['Username'] = 'This username is already taken.';
        // }


        // Validate mật khẩu nếu người dùng muốn thay đổi
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

        // Xử lý upload avatar (tương tự như PatientController::updateProfile)
        $avatarPath = $currentUser['Avatar'];
        $avatarUpdated = false;
        if (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['profile_avatar'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    // $fileError = $file['error']; // Đã kiểm tra là UPLOAD_ERR_OK
    $fileType = $file['type'];

    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExt, $allowedExtensions)) {
        if ($fileSize < 5000000) { // Giới hạn 5MB (5 * 1024 * 1024)
            // Tạo tên file duy nhất
            $newFileName = "avatar_" . $userId . "_" . uniqid('', true) . "." . $fileExt;
            $uploadDir = 'uploads/avatars/';
            $fileDestinationOnServer = PUBLIC_PATH . $uploadDir . $newFileName;

            // Tạo thư mục nếu chưa tồn tại (cần quyền ghi)
            // Đường dẫn cho mkdir phải là đường dẫn server tuyệt đối hoặc tương đối từ file đang chạy
            if (!file_exists(PUBLIC_PATH . $uploadDir)) {
                if (!mkdir(PUBLIC_PATH . $uploadDir, 0775, true) && !is_dir(PUBLIC_PATH . $uploadDir)) {
                     $data['errors']['profile_avatar'] = 'Failed to create upload directory.';
                }
            }

            if (empty($data['errors']['profile_avatar'])) { // Chỉ move nếu chưa có lỗi tạo thư mục
                
                if (move_uploaded_file($fileTmpName, $fileDestinationOnServer)) {
                    // Xóa avatar cũ (nếu có và không phải là avatar mặc định)
                    if (!empty($currentUser['Avatar']) && $currentUser['Avatar'] != 'assets/images/default_avatar.png' /* ví dụ */ ) {
                        $oldAvatarServerPath = PUBLIC_PATH . $currentUser['Avatar'];
                        if (file_exists($oldAvatarServerPath)) {
                            unlink($oldAvatarServerPath);
                        }
                    }
                    $avatarPath = $uploadDir . $newFileName; // Đường dẫn lưu vào DB (tương đối từ public)
                    $avatarUpdated = true;
                } else {
                    $data['errors']['profile_avatar'] = "Failed to move uploaded file to destination. Check permissions.";
                    // Ghi log lỗi chi tiết hơn ở đây nếu cần
                    error_log("move_uploaded_file failed for user {$userId}. Temp: {$fileTmpName}, Dest: {$fileDestinationOnServer}");
                }
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


        // (CSRF Token Validation)
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $data['errors']['csrf'] = 'Invalid CSRF token. Action aborted.';
        }


        if (empty($data['errors'])) { // Đảm bảo không có lỗi nào từ trước
    $this->db->beginTransaction();
    try {
        $userDataToUpdate = [
            'FullName' => $data['input']['FullName'],
            'Email' => $data['input']['Email'],
            'PhoneNumber' => $data['input']['PhoneNumber'], // Bây giờ nó có thể là null
            'Address' => $data['input']['Address'],
        ];
        if ($avatarUpdated) { // Chỉ thêm Avatar nếu nó thực sự được update
            $userDataToUpdate['Avatar'] = $avatarPath;
        }


                $userUpdateSuccess = $this->userModel->updateUser($userId, $userDataToUpdate); // Đảm bảo updateUser nhận mảng data
  

                $passwordUpdateSuccess = true;
                if ($updatePassword) {
                    $newPasswordHash = password_hash($data['input']['new_password'], PASSWORD_DEFAULT);
                    $passwordUpdateSuccess = $this->userModel->updatePassword($userId, $newPasswordHash);
                }

                if ($userUpdateSuccess && $passwordUpdateSuccess) {
                    
            $this->db->commit();
            
                    $_SESSION['profile_message_success'] = 'Admin profile updated successfully.';
                    // Cập nhật session
                    $_SESSION['user_fullname'] = $data['input']['FullName'];
                    $_SESSION['user_email'] = $data['input']['Email'];
                    if ($avatarUpdated || $avatarPath !== $currentUser['Avatar']) {
                        $_SESSION['user_avatar'] = $avatarPath;
                    }
                    header('Location: ' . BASE_URL . '/admin/updateProfile');
                    exit();
                } else {
                    echo "<br>Lỗi: userUpdateSuccess là false. ĐANG ROLLBACK TRANSACTION...";
            $this->db->rollBack();
           
                    // Xóa file avatar mới nếu DB update fail
                    if ($avatarUpdated && $avatarPath !== $currentUser['Avatar'] && file_exists($avatarPath)) {
                        unlink($avatarPath);
                    }
                    $data['profile_message_error'] = 'Failed to update profile in database.';
                }
            } catch (Exception $e) {
                 
        if ($this->db->inTransaction()) $this->db->rollBack();
        

                if ($avatarUpdated && $avatarPath !== $currentUser['Avatar'] && file_exists($avatarPath)) {
                    unlink($avatarPath);
                }
                error_log("Error updating admin profile: " . $e->getMessage());
                $data['profile_message_error'] = 'An error occurred: ' . $e->getMessage();
            }
               
} else {
    echo "Không cập nhật DB do có lỗi validation trước đó.";
    print_r($data['errors']);
   
}
      

    // Cập nhật $data['user'] để view có thông tin mới nhất từ session nếu vừa update thành công
    // Hoặc nếu không POST, nó vẫn là $currentUser
    if (isset($_SESSION['user_avatar'])) $data['user']['Avatar'] = $_SESSION['user_avatar'];
    if (isset($_SESSION['user_fullname'])) $data['user']['FullName'] = $_SESSION['user_fullname'];


    $this->view('admin/profile/update', $data);
}
}
}
?>

