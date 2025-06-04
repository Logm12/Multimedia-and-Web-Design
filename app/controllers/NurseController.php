<?php
// app/controllers/NurseController.php

class NurseController {
    private $userModel;
    private $appointmentModel; // Sẽ cần để xem lịch hẹn
    // private $patientModel; // Sẽ cần để tìm kiếm bệnh nhân
    // private $vitalSignModel; // Nếu bạn tạo model riêng cho sinh hiệu
    // private $medicalRecordModel; // Nếu Nurse cập nhật vào bệnh án
    private $vitalSignModel; // Thêm vào
    private $patientModel;   // Thêm vào

    public function __construct() {
        // 1. Xác thực người dùng đã đăng nhập và có vai trò là 'Nurse'
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
            // Lưu lại URL hiện tại để redirect sau khi đăng nhập (nếu muốn)
            // $_SESSION['redirect_url'] = getCurrentUrl(); // Bạn cần tự viết hàm getCurrentUrl()

            $_SESSION['error_message'] = "Unauthorized access. Please log in as a Nurse.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        // Khởi tạo các model cần thiết
        
        $this->userModel = new UserModel();
        $this->appointmentModel = new AppointmentModel();
        $this->vitalSignModel = new VitalSignModel(); // Khởi tạo
        $this->patientModel = new PatientModel();   // Khởi tạo
    }

    /**
     * Phương thức helper để load view, đảm bảo chỉ Nurse thấy view của Nurse
     * (Hoặc bạn có thể có một BaseController với hàm view chung và kiểm tra vai trò ở đó)
     */
    protected function view($view, $data = []) {
        // Kiểm tra lại session và vai trò một lần nữa cho chắc chắn
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
            // Điều này không nên xảy ra nếu __construct đã kiểm tra, nhưng là một lớp bảo vệ thêm
            session_destroy(); // Hủy session và bắt đăng nhập lại
            header('Location: ' . BASE_URL . '/auth/login?error=session_issue');
            exit();
        }

        // Thêm thông tin user hiện tại vào $data để có thể dùng trong layout/view
        if (!isset($data['currentUser'])) { // Chỉ thêm nếu chưa có
            $data['currentUser'] = [
                'UserID' => $_SESSION['user_id'],
                'FullName' => $_SESSION['user_fullname'],
                'Role' => $_SESSION['user_role'],
                'Avatar' => $_SESSION['user_avatar'] ?? null
            ];
        }
        $data['userRole'] = 'Nurse'; // Để dễ dàng tùy chỉnh layout/menu


        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            // Hoặc hiển thị trang lỗi 404 thân thiện hơn
            die("View '{$view}' does not exist for Nurse.");
        }
    }

    /**
     * Action mặc định cho Nurse, thường là trang dashboard
     */
    public function index() {
        $this->dashboard();
    }

    /**
     * Trang Dashboard của Nurse
     */
     public function dashboard() {
        $nurseId = $_SESSION['user_id'];
        $nurseInfo = $this->userModel->findUserById($nurseId);

        $today = date('Y-m-d');
        $upcomingAppointments = $this->appointmentModel->getUpcomingAppointmentsForNurseDashboard($today);

        $data = [
            'title' => 'Nurse Dashboard',
            'nurseInfo' => $nurseInfo,
            'upcomingAppointments' => $upcomingAppointments,
            'welcome_message' => 'Welcome Nurse, ' . htmlspecialchars($_SESSION['user_fullname']) . '!'
        ];

        // THÊM CÁC LINK CHỨC NĂNG CHO NURSE
        $data['links'] = [
            ['url' => BASE_URL . '/nurse/listAppointments', 'text' => 'Manage Appointments'],
            // Thêm các link khác cho Nurse sau này, ví dụ:
            // ['url' => BASE_URL . '/nurse/searchPatient', 'text' => 'Search Patient & Record Vitals'],
            // ['url' => BASE_URL . '/nurse/myProfile', 'text' => 'My Profile'],
        ];

        $this->view('nurse/dashboard', $data);
    }

    // Các action khác cho Nurse sẽ được thêm ở đây
    // Ví dụ: listAppointments, recordVitals, searchPatient, ...
     /**
     * Hiển thị danh sách lịch hẹn chi tiết cho Nurse
     */
    public function listAppointments() {
        $nurseId = $_SESSION['user_id'];

        // Lấy các tham số lọc từ GET request
        // Ví dụ: lọc theo ngày, trạng thái, bác sĩ (nếu Nurse hỗ trợ nhiều bác sĩ)
        $filterDate = $_GET['date'] ?? date('Y-m-d'); // Mặc định là ngày hiện tại
        $filterStatus = $_GET['status'] ?? 'All'; // Mặc định là tất cả trạng thái
        // $filterDoctorId = $_GET['doctor_id'] ?? null; // Sẽ cần nếu Nurse quản lý lịch cho nhiều bác sĩ

        // Validate các giá trị filter (bạn nên có hàm validate tốt hơn)
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $filterDate)) {
            $filterDate = date('Y-m-d');
        }
        $validStatuses = ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow', 'Rescheduled'];
        if (!in_array($filterStatus, $validStatuses)) {
            $filterStatus = 'All';
        }

        // Gọi phương thức từ AppointmentModel để lấy danh sách lịch hẹn
        // Phương thức này cần được tạo hoặc điều chỉnh trong AppointmentModel
        // Nó cần có khả năng lọc theo ngày, trạng thái, và có thể là bác sĩ được Nurse phân công
        $appointments = $this->appointmentModel->getAppointmentsForNurseView(
            $filterDate,
            $filterStatus
            // $nurseId, // Truyền nurseId nếu cần lọc theo phân công
            // $filterDoctorId // Truyền doctorId nếu có bộ lọc bác sĩ
        );

        $data = [
            'title' => 'Manage Appointments',
            'appointments' => $appointments,
            'filterDate' => $filterDate,
            'filterStatus' => $filterStatus,
            'allStatuses' => $validStatuses // Để tạo dropdown cho bộ lọc trạng thái
            // 'doctorsForFilter' => [] // Sẽ lấy danh sách bác sĩ mà nurse này hỗ trợ để làm bộ lọc
        ];

        // SAU NÀY: Nếu có logic phân công Nurse cho Doctor, bạn cần:
        // 1. Lấy danh sách các DoctorID mà Nurse này được phân công.
        //    $assignedDoctorIds = $this->userModel->getAssignedDoctorIdsForNurse($nurseId); // Cần tạo hàm này
        // 2. Truyền $assignedDoctorIds này vào $this->appointmentModel->getAppointmentsForNurseView(...)
        //    để model chỉ query các lịch hẹn của các bác sĩ đó.
        // 3. Lấy thông tin các bác sĩ đó để hiển thị trong bộ lọc Doctor (nếu có)
        //    $data['doctorsForFilter'] = $this->userModel->getUsersByIds($assignedDoctorIds, 'Doctor');


        $this->view('nurse/appointments/list', $data);
    }

    /**
     * Hiển thị chi tiết một lịch hẹn cụ thể
     * @param int $appointmentId
     */
    public function appointmentDetails($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0) {
            $_SESSION['error_message'] = "Invalid Appointment ID.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments');
            exit();
        }

        // Gọi model để lấy chi tiết lịch hẹn
        // Phương thức này cần join với các bảng Users (cho Patient, Doctor), Specializations
        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);

        if (!$appointmentDetails) {
            $_SESSION['error_message'] = "Appointment not found.";
            header('Location: ' . BASE_URL . '/nurse/listAppointments');
            exit();
        }

        // TODO: Kiểm tra xem Nurse này có quyền xem lịch hẹn này không (dựa trên phân công)
        // if (!$this->userModel->canNurseViewAppointment($this->userModel->getNurseProfileByUserId($_SESSION['user_id'])['NurseID'], $appointmentDetails['DoctorID'])) {
        //    $_SESSION['error_message'] = "You are not authorized to view this appointment's details.";
        //    header('Location: ' . BASE_URL . '/nurse/listAppointments');
        //    exit();
        // }


        $data = [
            'title' => 'Appointment Details',
            'appointment' => $appointmentDetails
        ];

        $this->view('nurse/appointments/details', $data);
    }
  public function showRecordVitalsForm($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0) { /* lỗi, redirect */ }

        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);
        if (!$appointmentDetails) { /* lỗi, redirect */ }

        // TODO: Kiểm tra Nurse có quyền với appointment này không

        // Lấy thông tin sinh hiệu đã có (nếu có) để điền vào form
        $existingVitals = $this->vitalSignModel->getVitalSignsByAppointmentId($appointmentId);

        $data = [
            'title' => 'Record Vital Signs for Appointment #' . $appointmentId,
            'appointment' => $appointmentDetails,
            'vitals' => $existingVitals ?: [], // Dữ liệu sinh hiệu (có thể rỗng)
            'errors' => $_SESSION['vitals_errors'] ?? [],
            'input' => $_SESSION['vitals_input'] ?? ($existingVitals ?: [])
        ];
        unset($_SESSION['vitals_errors'], $_SESSION['vitals_input']);

        $this->view('nurse/vitals/record_form', $data);
    }

     public function saveVitals($appointmentId = 0) {
        $appointmentId = (int)$appointmentId;
        if ($appointmentId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/nurse/listAppointments');
            exit();
        }

        $appointmentDetails = $this->appointmentModel->getAppointmentDetailsById($appointmentId);
        if (!$appointmentDetails || !isset($appointmentDetails['PatientProfileID'])) { // Kiểm tra cả PatientProfileID
            $_SESSION['error_message'] = 'Appointment or Patient data not found.';
            header('Location: ' . BASE_URL . '/nurse/listAppointments');
            exit();
        }

        // ... (TODO: Kiểm tra Nurse có quyền với appointment này không) ...

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        $input = [
            'AppointmentID' => $appointmentId,
            // Đảm bảo 'PatientProfileID' được trả về từ getAppointmentDetailsById
            // và đó là PatientID từ bảng Patients.
            'PatientID' => $appointmentDetails['PatientProfileID'], // Hoặc tên key bạn đặt cho PatientID của bảng Patients
            'RecordedByUserID' => $_SESSION['user_id'], // UserID của Nurse đang đăng nhập
            'HeartRate' => isset($_POST['HeartRate']) && trim($_POST['HeartRate']) !== '' ? filter_var(trim($_POST['HeartRate']), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null,
            'Temperature' => isset($_POST['Temperature']) && trim($_POST['Temperature']) !== '' ? filter_var(trim($_POST['Temperature']), FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => '.']]) : null,
            'BloodPressureSystolic' => isset($_POST['BloodPressureSystolic']) && trim($_POST['BloodPressureSystolic']) !== '' ? filter_var(trim($_POST['BloodPressureSystolic']), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null,
            'BloodPressureDiastolic' => isset($_POST['BloodPressureDiastolic']) && trim($_POST['BloodPressureDiastolic']) !== '' ? filter_var(trim($_POST['BloodPressureDiastolic']), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null,
            'RespiratoryRate' => isset($_POST['RespiratoryRate']) && trim($_POST['RespiratoryRate']) !== '' ? filter_var(trim($_POST['RespiratoryRate']), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null,
            'Weight' => isset($_POST['Weight']) && trim($_POST['Weight']) !== '' ? filter_var(trim($_POST['Weight']), FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => '.']]) : null,
            'Height' => isset($_POST['Height']) && trim($_POST['Height']) !== '' ? filter_var(trim($_POST['Height']), FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => '.']]) : null,
            'OxygenSaturation' => isset($_POST['OxygenSaturation']) && trim($_POST['OxygenSaturation']) !== '' ? filter_var(trim($_POST['OxygenSaturation']), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]) : null,
            'Notes' => trim($_POST['Notes'] ?? '')
        ];

        // Sau khi filter_var, nếu giá trị không hợp lệ, nó sẽ trả về false.
        // Chúng ta muốn null nếu không hợp lệ hoặc trống.
        // Đoạn code trên đã xử lý để trả về null nếu input trống hoặc không hợp lệ với filter_var
        // Tuy nhiên, filter_var trả về false nếu validation thất bại, không phải null.
        // Cần điều chỉnh lại một chút để đảm bảo giá trị là null nếu không hợp lệ.

        // CÁCH XỬ LÝ FILTER_VAR TỐT HƠN ĐỂ NHẬN NULL KHI KHÔNG HỢP LỆ HOẶC TRỐNG
        $sanitizeAndValidate = function($value, $filter, $options = []) {
            $trimmedValue = trim($value ?? '');
            if ($trimmedValue === '') {
                return null;
            }
            $validatedValue = filter_var($trimmedValue, $filter, $options);
            return ($validatedValue === false) ? null : $validatedValue; // Trả về null nếu filter thất bại
        };

        $input['HeartRate'] = $sanitizeAndValidate($_POST['HeartRate'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $input['Temperature'] = $sanitizeAndValidate($_POST['Temperature'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => '.']]);
        $input['BloodPressureSystolic'] = $sanitizeAndValidate($_POST['BloodPressureSystolic'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $input['BloodPressureDiastolic'] = $sanitizeAndValidate($_POST['BloodPressureDiastolic'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $input['RespiratoryRate'] = $sanitizeAndValidate($_POST['RespiratoryRate'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $input['Weight'] = $sanitizeAndValidate($_POST['Weight'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => '.']]);
        $input['Height'] = $sanitizeAndValidate($_POST['Height'] ?? null, FILTER_VALIDATE_FLOAT, ['options' => ['decimal' => '.']]);
        $input['OxygenSaturation'] = $sanitizeAndValidate($_POST['OxygenSaturation'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
        // Notes không cần $sanitizeAndValidate phức tạp như vậy
        $input['Notes'] = trim($_POST['Notes'] ?? '');


        $errors = [];
        // Bạn có thể thêm validation ở đây để thông báo lỗi cụ thể cho người dùng
        // Ví dụ: nếu một giá trị số nằm ngoài khoảng mong đợi sau khi đã là số.
        // if (isset($_POST['HeartRate']) && trim($_POST['HeartRate']) !== '' && $input['HeartRate'] === null) $errors['HeartRate'] = 'Invalid heart rate value.';
        // (Tương tự cho các trường khác)


        if (empty($errors)) {
            if ($this->vitalSignModel->createOrUpdateVitalSigns($input)) {
                $_SESSION['success_message'] = 'Vital signs recorded successfully.';
                header('Location: ' . BASE_URL . '/nurse/appointmentDetails/' . $appointmentId);
                exit();
            } else {
                $_SESSION['error_message'] = 'Failed to save vital signs. Please try again.';
            }
        }

        $_SESSION['vitals_errors'] = $errors;
        $_SESSION['vitals_input'] = $input;
        header('Location: ' . BASE_URL . '/nurse/recordVitals/' . $appointmentId);
        exit();
    }
}
?>