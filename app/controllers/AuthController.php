<?php
// app/controllers/AuthController.php

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // Hàm để load view (bạn có thể đặt hàm này trong một BaseController sau này)
    protected function view($view, $data = []) {
        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist.");
        }
    }

    public function login() {
        $data = [
            'title' => 'Login',
            'input' => [],
            'errors' => []
        ];

        // Nếu đã đăng nhập, chuyển hướng đến dashboard tương ứng
        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard($_SESSION['user_role']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data['input'] = [
                'username_or_email' => trim($_POST['username_or_email']),
                'password' => $_POST['password']
            ];

            // Validation
            if (empty($data['input']['username_or_email'])) {
                $data['errors']['username_or_email'] = 'Please enter your username or email.';
            }
            if (empty($data['input']['password'])) {
                $data['errors']['password'] = 'Please enter your password.';
            }

            if (empty($data['errors'])) {
                // Tìm user bằng username hoặc email
                $loggedInUser = $this->userModel->findUserByUsername($data['input']['username_or_email']);
                if (!$loggedInUser) {
                    $loggedInUser = $this->userModel->findUserByEmail($data['input']['username_or_email']);
                }

                 // SỬA Ở ĐÂY: Dùng cú pháp mảng
                if ($loggedInUser && isset($loggedInUser['PasswordHash']) && password_verify($data['input']['password'], $loggedInUser['PasswordHash'])) {
                    // Mật khẩu đúng, tạo session
                    // SỬA Ở ĐÂY: Dùng cú pháp mảng
                    if ($loggedInUser['Status'] === 'Active') {
                        $this->createUserSession($loggedInUser); // Truyền mảng vào createUserSession
                        $this->redirectToDashboard($loggedInUser['Role']); // SỬA Ở ĐÂY
                        exit();
                    } elseif ($loggedInUser->Status === 'Pending') {
                        $data['error_message'] = 'Your account is pending approval. Please wait for an administrator to activate it.';
                    } elseif ($loggedInUser->Status === 'Inactive') {
                        $data['error_message'] = 'Your account has been deactivated. Please contact support.';
                    } else {
                        $data['error_message'] = 'Account status unknown. Please contact support.';
                    }
                } else {
                    $data['error_message'] = 'Invalid username/email or password.';
                    // Để tránh user biết username/email có tồn tại hay không, không nên đặt lỗi cụ thể ở đây
                }
            }
        }
        $this->view('auth/login', $data);
    }

    // Sửa hàm này để nhận mảng
    private function createUserSession($userArray) { // Đổi tên tham số để rõ ràng hơn
        $_SESSION['user_id'] = $userArray['UserID']; // SỬA Ở ĐÂY
        $_SESSION['user_username'] = $userArray['Username']; // SỬA Ở ĐÂY
        $_SESSION['user_email'] = $userArray['Email']; // SỬA Ở ĐÂY
        $_SESSION['user_fullname'] = $userArray['FullName']; // SỬA Ở ĐÂY
        $_SESSION['user_role'] = $userArray['Role']; // SỬA Ở ĐÂY
        $_SESSION['user_avatar'] = $user['Avatar'] ?? null;

    }

    private function redirectToDashboard($role) {
        switch ($role) {
            case 'Admin':
                header('Location: ' . BASE_URL . '/admin/dashboard'); // Sẽ tạo sau
                break;
            case 'Doctor':
                header('Location: ' . BASE_URL . '/doctor/dashboard'); // Sẽ tạo sau
                break;
            case 'Nurse':
                header('Location: ' . BASE_URL . '/nurse/dashboard'); // Sẽ tạo sau
                break;
            case 'Patient':
                header('Location: ' . BASE_URL . '/patient/dashboard');
                break;
            default:
                header('Location: ' . BASE_URL . '/'); // Trang chủ nếu không có role cụ thể
                break;
        }
        exit();
    }

    public function logout() {
        // Xóa tất cả các biến session
        $_SESSION = array();

        // Nếu muốn hủy session hoàn toàn, hãy xóa cả cookie session.
        // Lưu ý: Điều này sẽ phá hủy session, không chỉ dữ liệu session!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Cuối cùng, hủy session.
        session_destroy();

        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }
}
?>