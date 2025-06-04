<?php
// app/controllers/PatientController.php

class PatientController {
    private $userModel;
    private $patientModel;
    private $doctorModel;
    private $doctorAvailabilityModel; // Thêm DoctorAvailabilityModel
    private $appointmentModel;
    private $medicalRecordModel;
    private $prescriptionModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->patientModel = new PatientModel();
        $this->doctorModel = new DoctorModel();
        $this->doctorAvailabilityModel = new DoctorAvailabilityModel(); // Khởi tạo
         $this->appointmentModel = new AppointmentModel(); // Thêm
        $this->medicalRecordModel = new MedicalRecordModel(); // Thêm
        $this->prescriptionModel = new PrescriptionModel(); // Thêm 
    }

    // Hàm để load view (bạn có thể đặt hàm này trong một BaseController sau này)
    protected function view($view, $data = []) {
        // Kiểm tra file view có tồn tại không
        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            // File view không tồn tại
            die("View '{$view}' does not exist.");
        }
    }


    public function register() {
        $data = [
            'title' => 'Patient Registration',
            'input' => [], // Để giữ lại giá trị input khi có lỗi
            'errors' => []  // Để hiển thị lỗi validation
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Xử lý dữ liệu form
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS); // Xử lý input cơ bản

            $data['input'] = [
                'fullname' => trim($_POST['fullname']),
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'confirm_password' => $_POST['confirm_password'],
                'phone_number' => trim($_POST['phone_number']),
                'address' => trim($_POST['address']),
                'date_of_birth' => trim($_POST['date_of_birth']),
                'gender' => trim($_POST['gender'])
            ];

            // --- VALIDATION ---
            // Họ tên
            if (empty($data['input']['fullname'])) {
                $data['errors']['fullname'] = 'Please enter your full name.';
            }

            // Username
            if (empty($data['input']['username'])) {
                $data['errors']['username'] = 'Please enter a username.';
            } elseif (strlen($data['input']['username']) < 4) {
                $data['errors']['username'] = 'Username must be at least 4 characters long.';
            } elseif ($this->userModel->findUserByUsername($data['input']['username'])) {
                $data['errors']['username'] = 'This username is already taken.';
            }

            // Email
            if (empty($data['input']['email'])) {
                $data['errors']['email'] = 'Please enter your email address.';
            } elseif (!filter_var($data['input']['email'], FILTER_VALIDATE_EMAIL)) {
                $data['errors']['email'] = 'Invalid email format.';
            } elseif ($this->userModel->findUserByEmail($data['input']['email'])) {
                $data['errors']['email'] = 'This email is already registered.';
            }

            // Password
            if (empty($data['input']['password'])) {
                $data['errors']['password'] = 'Please enter a password.';
            } elseif (strlen($data['input']['password']) < 6) {
                $data['errors']['password'] = 'Password must be at least 6 characters long.';
            }

            // Confirm Password
            if (empty($data['input']['confirm_password'])) {
                $data['errors']['confirm_password'] = 'Please confirm your password.';
            } elseif ($data['input']['password'] !== $data['input']['confirm_password']) {
                $data['errors']['confirm_password'] = 'Passwords do not match.';
            }

            // Số điện thoại (tùy chọn, validate nếu có)
            if (!empty($data['input']['phone_number']) && !preg_match('/^[0-9]{10,15}$/', $data['input']['phone_number'])) { // Adjusted regex for international numbers
                 $data['errors']['phone_number'] = 'Invalid phone number format.';
            }


            // Nếu không có lỗi
            if (empty($data['errors'])) {
                // Hash mật khẩu
                $data['input']['password_hash'] = password_hash($data['input']['password'], PASSWORD_DEFAULT);

                // Bắt đầu transaction
                $db = Database::getInstance(); // Lấy instance của DB
                $db->beginTransaction();

                try {
                    // Tạo user
                    $userData = [
                        'Username' => $data['input']['username'],
                        'PasswordHash' => $data['input']['password_hash'],
                        'Email' => $data['input']['email'],
                        'FullName' => $data['input']['fullname'],
                        'Role' => 'Patient',
                        'PhoneNumber' => $data['input']['phone_number'],
                        'Address' => $data['input']['address'],
                        'Status' => 'Active' // Hoặc 'Pending' nếu bạn muốn admin duyệt
                    ];
                    $newUserId = $this->userModel->createUser($userData);

                    if ($newUserId) {
                        // Tạo patient
                        $patientData = [
                            'UserID' => $newUserId,
                            'DateOfBirth' => !empty($data['input']['date_of_birth']) ? $data['input']['date_of_birth'] : null,
                            'Gender' => !empty($data['input']['gender']) ? $data['input']['gender'] : null
                            // Thêm các trường khác của Patient nếu có trong form
                        ];
                        if ($this->patientModel->createPatient($patientData)) {
                            $db->commit(); // Hoàn tất transaction
                             $data['success_message'] = 'Registration successful! You can now <a href="'.BASE_URL.'/auth/login">log in</a>.';
                            // Xóa input cũ để form trống sau khi thành công
                            $data['input'] = [];
                            // Chuyển hướng đến trang đăng nhập sau vài giây hoặc hiển thị link
                            // header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/auth/login');
                            // exit();
                        } else {
                            $db->rollBack(); // Hoàn tác transaction
                            $data['error_message'] = 'An error occurred while creating patient information. Please try again.';
                        }
                    } else {
                        $db->rollBack(); // Hoàn tác transaction
                        $data['error_message'] = 'An error occurred while creating your account. Please try again.';
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    // Log lỗi $e->getMessage()
                    error_log("Patient Registration Error: " . $e->getMessage()); // Ghi log lỗi
                    $data['error_message'] = 'A system error occurred. Please try again later.';
                }
            }
            // Load lại view với dữ liệu (và lỗi nếu có)
            $this->view('patient/register', $data);

        } else {
            // Hiển thị form đăng ký (GET request)
            $this->view('patient/register', $data);
        }
    }

    public function dashboard() {
        // Kiểm tra xem user đã đăng nhập và có phải là patient không
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $data = [
            'title' => 'Patient Dashboard',
            'welcome_message' => 'Welcome ' . htmlspecialchars($_SESSION['user_fullname']) . ' to your dashboard!'
        ];
        // Thêm link đến trang danh sách bác sĩ
        $data['browse_doctors_link'] = BASE_URL . '/patient/browseDoctors';
        $this->view('patient/dashboard', $data);
    }

    /**
     * Hiển thị danh sách bác sĩ cho bệnh nhân
     */
    public function browseDoctors() {
        // Kiểm tra đăng nhập (Patient mới được vào)
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
            // Hoặc bạn có thể tạo một trang thông báo chung thay vì chuyển hướng về login
            $_SESSION['error_message'] = "You need to be logged in as a patient to view this page.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $doctors = $this->doctorModel->getAllActiveDoctorsWithSpecialization();

        $data = [
            'title' => 'Browse Doctors',
            'doctors' => $doctors
        ];

        $this->view('patient/browse_doctors', $data);
    }
     /**
     * Xử lý AJAX request để lấy lịch trống của bác sĩ
     * @param int $doctorId
     */
    public function getDoctorAvailability($doctorId = 0) {
        // Đảm bảo đây là AJAX request (tùy chọn, nhưng tốt)
        // if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        //     http_response_code(403); // Forbidden
        //     echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        //     exit;
        // }

        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'message' => 'Authentication required.']);
            exit;
        }

        $doctorId = (int)$doctorId;
        if ($doctorId <= 0) {
            http_response_code(400); // Bad Request
            echo json_encode(['success' => false, 'message' => 'Invalid Doctor ID.']);
            exit;
        }

        // Lấy lịch trong 7 ngày tới (ví dụ)
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+7 days'));

        $availableSlots = $this->doctorAvailabilityModel->getAvailableSlotsByDoctorId($doctorId, $startDate, $endDate);

        // Thiết lập header là JSON
        header('Content-Type: application/json');

        if ($availableSlots !== false) {
            echo json_encode(['success' => true, 'slots' => $availableSlots]);
        } else {
            // Trường hợp model trả về false do lỗi query (ít khi xảy ra với PDOException)
            echo json_encode(['success' => false, 'message' => 'Could not retrieve availability.']);
        }
        exit; // Quan trọng để không có output nào khác sau JSON
    }

// Action để Patient xem lịch sử/danh sách lịch hẹn của mình (Đã có từ trước, đảm bảo nó load view đúng)
public function myAppointments() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }
    // Khởi tạo AppointmentModel nếu chưa có trong __construct
    if (!isset($this->appointmentModel)) {
        $this->appointmentModel = new AppointmentModel();
    }

    // Lấy PatientID từ UserID trong session
    // Cần logic chính xác để lấy PatientID từ $_SESSION['user_id']
    // Ví dụ, nếu UserID = PatientID trực tiếp, hoặc PatientID được lưu riêng trong session,
    // hoặc query từ PatientModel:
    // $patientInfo = $this->patientModel->getPatientByUserId($_SESSION['user_id']);
    // if (!$patientInfo) { /* xử lý lỗi */ }
    // $patientId = $patientInfo['PatientID'];
    $patientId = $_SESSION['user_id']; // GIẢ SỬ UserID LÀ PatientID hoặc bạn đã có PatientID trong session

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
        'allStatuses' => $validStatuses
    ];
    $this->view('patient/my_appointments', $data);
}
// MỚI: Action để Patient xem tóm tắt cuộc hẹn và đơn thuốc
public function viewAppointmentSummary($appointmentId = 0) {
    // 1. Xác thực Patient
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
        $_SESSION['error_message'] = "Unauthorized access. Please log in.";
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    $appointmentId = (int)$appointmentId;
    if ($appointmentId <= 0) {
        $_SESSION['error_message'] = "Invalid appointment ID specified.";
        header('Location: ' . BASE_URL . '/patient/myAppointments');
        exit();
    }

    // Lấy PatientID từ UserID trong session (Cần logic chính xác)
    // $patientInfoFromSession = $this->patientModel->getPatientByUserId($_SESSION['user_id']);
    // if (!$patientInfoFromSession) {
    //     $_SESSION['error_message'] = "Patient profile not found for your account.";
    //     header('Location: ' . BASE_URL . '/patient/myAppointments');
    //     exit();
    // }
    // $currentPatientId = $patientInfoFromSession['PatientID'];
    $currentPatientId = $_SESSION['user_id']; // GIẢ SỬ UserID LÀ PatientID


    // Khởi tạo các model cần thiết nếu chưa có trong __construct
    if (!isset($this->appointmentModel)) $this->appointmentModel = new AppointmentModel();
    if (!isset($this->medicalRecordModel)) $this->medicalRecordModel = new MedicalRecordModel();
    if (!isset($this->prescriptionModel)) $this->prescriptionModel = new PrescriptionModel();
    // DoctorModel và UserModel đã có trong __construct của bạn

    // 2. Lấy thông tin cuộc hẹn
    $appointment = $this->appointmentModel->getAppointmentByIdWithDoctorInfo($appointmentId); // Cần hàm này trong AppointmentModel

    if (!$appointment) {
        $_SESSION['error_message'] = "Appointment not found.";
        header('Location: ' . BASE_URL . '/patient/myAppointments');
        exit();
    }

    // 3. Kiểm tra xem Patient hiện tại có phải là người của cuộc hẹn này không
    if ($appointment['PatientID'] != $currentPatientId) {
        $_SESSION['error_message'] = "You are not authorized to view this appointment summary.";
        header('Location: ' . BASE_URL . '/patient/myAppointments');
        exit();
    }

    // 4. Lấy thông tin Medical Record (nếu có) cho cuộc hẹn này
    $medicalRecord = $this->medicalRecordModel->getRecordByAppointmentId($appointmentId);

    // 5. Lấy đơn thuốc (nếu có medicalRecord và RecordID)
    $prescriptions = [];
    if ($medicalRecord && isset($medicalRecord['RecordID'])) {
        $prescriptions = $this->prescriptionModel->getPrescriptionsByRecordId($medicalRecord['RecordID']);
    }

    // 6. Lấy thông tin chi tiết của Patient (để hiển thị trên tóm tắt)
    $patientDetails = $this->patientModel->getPatientDetailsById($appointment['PatientID']);


    $data = [
        'title' => 'Appointment Summary - ' . htmlspecialchars(date('M j, Y', strtotime($appointment['AppointmentDateTime']))),
        'appointment' => $appointment, // Chứa thông tin cuộc hẹn và Doctor
        'patient' => $patientDetails,   // Thông tin chi tiết của Patient
        'medicalRecord' => $medicalRecord, // Có thể là null
        'prescriptions' => $prescriptions // Có thể là mảng rỗng
    ];

    $this->view('patient/appointment_summary', $data);
}
public function viewAllMedicalRecords() {
    // 1. Xác thực Patient
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
        $_SESSION['error_message'] = "Unauthorized access. Please log in.";
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }

    // Lấy PatientID từ UserID trong session (Cần logic chính xác)
    // Ví dụ:
    // $patientInfoSession = $this->patientModel->getPatientByUserId($_SESSION['user_id']);
    // if (!$patientInfoSession) {
    //     $_SESSION['error_message'] = "Patient profile not found for your account.";
    //     header('Location: ' . BASE_URL . '/patient/dashboard'); // Hoặc trang lỗi
    //     exit();
    // }
    // $patientId = $patientInfoSession['PatientID'];
    $patientId = $_SESSION['user_id']; // GIẢ SỬ UserID LÀ PatientID hoặc bạn đã có PatientID trong session


    // Khởi tạo MedicalRecordModel nếu chưa có trong __construct
    if (!isset($this->medicalRecordModel)) {
        $this->medicalRecordModel = new MedicalRecordModel();
    }

    // 2. Lấy toàn bộ lịch sử bệnh án của Patient
    // Sử dụng lại phương thức getMedicalHistoryByPatientId, không cần truyền currentAppointmentId
    $medicalHistory = $this->medicalRecordModel->getMedicalHistoryByPatientId($patientId);

    $data = [
        'title' => 'My Medical Records',
        'medicalHistory' => $medicalHistory
    ];

    $this->view('patient/all_medical_records', $data);
}
 public function updateProfile() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
            $_SESSION['error_message'] = "Unauthorized access. Please log in.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $patientDetails = $this->patientModel->getPatientDetailsById($userId); // Giả sử UserID là PatientID hoặc bạn có cách lấy PatientID

        if (!$patientDetails) {
            $_SESSION['profile_message_error'] = 'Could not retrieve your profile information.';
            header('Location: ' . BASE_URL . '/patient/dashboard');
            exit();
        }

        // Lấy PasswordHash từ bảng Users để kiểm tra mật khẩu hiện tại
        $currentUserData = $this->userModel->findUserById($userId); // Cần hàm này
        if ($currentUserData) {
            $patientDetails['PasswordHash'] = $currentUserData['PasswordHash'];
        } else {
            // Xử lý trường hợp không tìm thấy user data (rất lạ nếu patientDetails có)
            $_SESSION['profile_message_error'] = 'Could not retrieve user authentication details.';
            header('Location: ' . BASE_URL . '/patient/dashboard');
            exit();
        }


        $data = [
            'title' => 'Update My Profile',
            'patient' => $patientDetails,
            'input' => (array) $patientDetails,
            'errors' => []
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // Khối POST chính bắt đầu ở đây
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data['input'] = [
                'FullName' => trim($_POST['FullName'] ?? $patientDetails['FullName']),
                'Email' => trim($_POST['Email'] ?? $patientDetails['Email']),
                'PhoneNumber' => trim($_POST['PhoneNumber'] ?? $patientDetails['PhoneNumber']),
                'Address' => trim($_POST['Address'] ?? $patientDetails['UserAddress']),
                'DateOfBirth' => trim($_POST['DateOfBirth'] ?? $patientDetails['DateOfBirth']),
                'Gender' => $_POST['Gender'] ?? $patientDetails['Gender'],
                'BloodType' => trim($_POST['BloodType'] ?? $patientDetails['BloodType']),
                'InsuranceInfo' => trim($_POST['InsuranceInfo'] ?? $patientDetails['InsuranceInfo']),
                'MedicalHistorySummary' => trim($_POST['MedicalHistorySummary'] ?? $patientDetails['MedicalHistorySummary']),
                'current_password' => $_POST['current_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
                'confirm_new_password' => $_POST['confirm_new_password'] ?? ''
            ];

            // Xử lý upload ảnh đại diện
            $avatarPath = $patientDetails['Avatar'];
            $avatarUpdated = false;

            if (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] == UPLOAD_ERR_OK) {
                $file = $_FILES['profile_avatar'];
                // ... (toàn bộ logic xử lý file upload như bạn đã có) ...
                // ... (bao gồm move_uploaded_file, unlink avatar cũ, gán $avatarPath, $avatarUpdated) ...
                // (Đảm bảo copy đúng logic xử lý file từ câu trả lời trước)
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileError = $file['error'];

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($fileExt, $allowedExtensions)) {
                    if ($fileError === 0) {
                        if ($fileSize < 5000000) { // 5MB
                            $newFileName = "avatar_" . $userId . "_" . uniqid('', true) . "." . $fileExt;
                            $fileDestination = 'uploads/avatars/' . $newFileName;
                            if (!file_exists('uploads/avatars/')) {
                                mkdir('uploads/avatars/', 0775, true);
                            }
                            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                                if (!empty($patientDetails['Avatar']) && $patientDetails['Avatar'] != 'default_avatar.png' && file_exists($patientDetails['Avatar'])) {
                                    unlink($patientDetails['Avatar']);
                                }
                                $avatarPath = $fileDestination;
                                $avatarUpdated = true;
                            } else { $data['errors']['profile_avatar'] = "Failed to move uploaded file."; }
                        } else { $data['errors']['profile_avatar'] = "File is too large (max 5MB)."; }
                    } else { $data['errors']['profile_avatar'] = "Error uploading file (code: {$fileError})."; }
                } else { $data['errors']['profile_avatar'] = "Invalid file type (allowed: jpg, jpeg, png, gif)."; }
            } elseif (isset($_FILES['profile_avatar']) && $_FILES['profile_avatar']['error'] != UPLOAD_ERR_NO_FILE) {
                 $data['errors']['profile_avatar'] = "Error uploading file (code: {$_FILES['profile_avatar']['error']}).";
            }


            // --- VALIDATION ---
            // ... (toàn bộ code validation của bạn cho FullName, Email, Password...) ...
            if (empty($data['input']['FullName'])) { $data['errors']['FullName'] = 'Full name cannot be empty.'; }
            if (empty($data['input']['Email'])) { $data['errors']['Email'] = 'Email cannot be empty.'; }
            // ... các validation khác ...
            $updatePassword = false;
            if (!empty($data['input']['new_password'])) {
                if (empty($data['input']['current_password'])) {
                    $data['errors']['current_password'] = 'Please enter your current password to set a new one.';
                } elseif (!password_verify($data['input']['current_password'], $patientDetails['PasswordHash'])) {
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


            if (empty($data['errors'])) {
                $db = Database::getInstance(); // Hoặc $this->db nếu đã khởi tạo trong __construct
                $db->beginTransaction();
                try {
                    $userDataToUpdate = [
                        'FullName' => $data['input']['FullName'],
                        'Email' => $data['input']['Email'],
                        'PhoneNumber' => $data['input']['PhoneNumber'],
                        'Address' => $data['input']['Address'],
                        'Avatar' => $avatarPath
                    ];
                    $userUpdateSuccess = $this->userModel->updateUser($userId, $userDataToUpdate); // Đảm bảo hàm này nhận 'Avatar'

                    $patientDataToUpdate = [
                        'DateOfBirth' => !empty($data['input']['DateOfBirth']) ? $data['input']['DateOfBirth'] : null,
                        'Gender' => $data['input']['Gender'],
                        'BloodType' => $data['input']['BloodType'],
                        'InsuranceInfo' => $data['input']['InsuranceInfo'],
                        'MedicalHistorySummary' => $data['input']['MedicalHistorySummary']
                    ];
                    $patientUpdateSuccess = $this->patientModel->updatePatient($patientDetails['PatientID'], $patientDataToUpdate);

                    $passwordUpdateSuccess = true;
                    if ($updatePassword) {
                        $newPasswordHash = password_hash($data['input']['new_password'], PASSWORD_DEFAULT);
                        $passwordUpdateSuccess = $this->userModel->updatePassword($userId, $newPasswordHash);
                    }

                    if ($userUpdateSuccess && $patientUpdateSuccess && $passwordUpdateSuccess) {
                        $db->commit();
                        $_SESSION['profile_message_success'] = 'Profile updated successfully.';
                        $_SESSION['user_fullname'] = $data['input']['FullName'];
                        if ($avatarUpdated) {
                            $_SESSION['user_avatar'] = $avatarPath;
                        }
                        header('Location: ' . BASE_URL . '/patient/updateProfile');
                        exit();
                    } else {
                        $db->rollBack();
                        if ($avatarUpdated && $avatarPath !== $patientDetails['Avatar']) {
                            if(file_exists($avatarPath)) unlink($avatarPath);
                        }
                        $data['profile_message_error'] = 'Failed to update profile in database. Please try again.';
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    error_log("Error updating patient profile: " . $e->getMessage());
                    $data['profile_message_error'] = 'An error occurred: ' . $e->getMessage();
                    if ($avatarUpdated && $avatarPath !== $patientDetails['Avatar']) {
                        if(file_exists($avatarPath)) unlink($avatarPath);
                    }
                }
            }
        } // Đóng khối if ($_SERVER['REQUEST_METHOD'] == 'POST') chính

        // Cập nhật $data['patient'] để view luôn có thông tin mới nhất từ session nếu vừa update avatar
        // Hoặc tốt hơn là query lại $patientDetails sau khi update thành công để đảm bảo tính nhất quán
        // Nếu không update thành công, $data['input'] đã chứa giá trị mới để điền lại form
        if (isset($_SESSION['user_avatar'])) { // Dùng cho lần load trang SAU KHI update thành công
            $data['patient']['Avatar'] = $_SESSION['user_avatar'];
        }

        $this->view('patient/update_profile', $data);
    } // Đóng action updateProfile()

} // Đóng class PatientController (CHỈ CÓ MỘT DẤU NÀY)
?>