<?php
// app/controllers/AuthController.php

class AuthController {
    private $userModel;
    private $mailService; // Add MailService property

    public function __construct() {
        $this->userModel = new UserModel();
        $this->mailService = new MailService(); // Instantiate MailService
    }

    // Helper to load view
    protected function view($view, $data = []) {
        if (file_exists(__DIR__ . '/../views/' . $view . '.php')) {
            require_once __DIR__ . '/../views/' . $view . '.php';
        } else {
            die("View '{$view}' does not exist.");
        }
    }

    public function login() {
        // ... (phần login giữ nguyên như cậu đã cung cấp) ...
        $data = [
            'title' => 'Login',
            'input' => [],
            'errors' => [],
            'error_message' => $_SESSION['error_message'] ?? null, 
            'success_message' => $_SESSION['success_message'] ?? null
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']); 

        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard($_SESSION['user_role']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $data['error_message'] = 'Oops! Something went wrong with the form submission. Please try again.';
                $this->view('auth/login', $data);
                return;
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $data['input'] = [
                'username_or_email' => trim($_POST['username_or_email'] ?? ''),
                'password' => $_POST['password'] ?? ''
            ];

            if (empty($data['input']['username_or_email'])) {
                $data['errors']['username_or_email'] = 'Please enter your username or email.';
            }
            if (empty($data['input']['password'])) {
                $data['errors']['password'] = 'Password cannot be empty.';
            }

            if (empty($data['errors'])) {
                $loggedInUser = $this->userModel->findUserByUsernameOrEmail($data['input']['username_or_email']);

                if ($loggedInUser && isset($loggedInUser['PasswordHash']) && password_verify($data['input']['password'], $loggedInUser['PasswordHash'])) {
                    
                    if ($loggedInUser['Status'] === 'Active') {
                        $this->createUserSession($loggedInUser);
                        $this->redirectToDashboard($loggedInUser['Role']);
                        exit();
                    } elseif ($loggedInUser['Status'] === 'Pending') {
                        $_SESSION['force_change_password_user_id'] = $loggedInUser['UserID'];
                        $_SESSION['temp_old_password_verified'] = true; 
                        header('Location: ' . BASE_URL . '/auth/forceChangePassword');
                        exit();
                    } elseif ($loggedInUser['Status'] === 'Inactive') {
                        $data['error_message'] = 'Your account has been deactivated. Please contact support, honey.';
                    } else {
                        $data['error_message'] = 'Your account status is currently \''.htmlspecialchars($loggedInUser['Status']).'\'. Please contact support.';
                    }
                } else {
                    $data['error_message'] = 'Invalid username/email or password. Check again';
                }
            }
        }
        $this->view('auth/login', $data);
    }

    public function forceChangePassword() {
        // ... (phần forceChangePassword giữ nguyên như cậu đã cung cấp) ...
        if (!isset($_SESSION['force_change_password_user_id']) || !isset($_SESSION['temp_old_password_verified'])) {
            $_SESSION['error_message'] = "Oops, you can't access that page directly.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $data = [
            'title' => 'Change Your Password',
            'input' => [],
            'errors' => [],
            'error_message' => $_SESSION['error_message'] ?? null,
            'success_message' => $_SESSION['success_message'] ?? null
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $data['error_message'] = 'Form submission error. Please try again.';
                $this->view('auth/forceChangePassword', $data);
                return;
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $userId = $_SESSION['force_change_password_user_id'];

            $data['input'] = [
                'new_password' => $_POST['new_password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? ''
            ];

            if (empty($data['input']['new_password'])) {
                $data['errors']['new_password'] = 'New password cannot be empty, honey.';
            } elseif (strlen($data['input']['new_password']) < 6) { 
                $data['errors']['new_password'] = 'Password must be at least 6 characters long.';
            }

            if (empty($data['input']['confirm_password'])) {
                $data['errors']['confirm_password'] = 'Please confirm your new password';
            } elseif ($data['input']['new_password'] !== $data['input']['confirm_password']) {
                $data['errors']['confirm_password'] = 'Passwords do not match. Try again';
            }

            if (empty($data['errors'])) {
                $newPasswordHash = password_hash($data['input']['new_password'], PASSWORD_DEFAULT);
                if ($this->userModel->updatePasswordAndStatus($userId, $newPasswordHash, 'Active')) {
                    unset($_SESSION['force_change_password_user_id']);
                    unset($_SESSION['temp_old_password_verified']);
                    
                    $user = $this->userModel->findUserById($userId); 
                    if ($user) {
                        $this->createUserSession($user);
                        $_SESSION['success_message'] = 'Password changed successfully! Welcome aboard!';
                        $this->redirectToDashboard($user['Role']);
                        exit();
                    } else {
                        $_SESSION['error_message'] = 'Could not retrieve user details after password change. Please try logging in.';
                        header('Location: ' . BASE_URL . '/auth/login');
                        exit();
                    }
                } else {
                    $data['error_message'] = 'Could not update password. Please try again or contact support.';
                }
            }
        }
        $this->view('auth/forceChangePassword', $data);
    }

    // --- NEW METHODS FOR FORGOT PASSWORD ---

    /**
     * Displays the form to request a password reset.
     */
    public function forgotPassword() {
        $data = [
            'title' => 'Forgot Your Password?',
            'input' => [],
            'error_message' => $_SESSION['error_message'] ?? null,
            'success_message' => $_SESSION['success_message'] ?? null
        ];
        unset($_SESSION['error_message'], $_SESSION['success_message']);
        $this->view('auth/forgot_password_form', $data);
    }

    /**
     * Processes the forgot password request.
     */
    public function processForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
                $_SESSION['error_message'] = 'Invalid form submission. Please try again.';
                header('Location: ' . BASE_URL . '/auth/forgotPassword');
                exit();
            }

            $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_message'] = 'Please enter a valid email address.';
                header('Location: ' . BASE_URL . '/auth/forgotPassword');
                exit();
            }

            $user = $this->userModel->findUserByEmail($email);

            if ($user) {
                try {
                    $token = bin2hex(random_bytes(32));
                } catch (Exception $e) {
                    // Fallback if random_bytes fails
                    $token = md5(uniqid(microtime(), true) . $email); 
                }
                
                $expiresAt = date('Y-m-d H:i:s', time() + 3600); // Token expires in 1 hour

                if ($this->userModel->createPasswordResetToken($user['UserID'], $email, $token, $expiresAt)) {
                    $resetLink = BASE_URL . '/auth/resetPassword/' . $token;
                    
                    // Send email using MailService
                    if ($this->mailService->sendPasswordResetEmail($user['Email'], $user['FullName'], $resetLink)) {
                        $_SESSION['success_message'] = 'If an account with that email exists, a password reset link has been sent. Please check your inbox (and spam folder!).';
                    } else {
                        $_SESSION['error_message'] = 'Oops! We couldn\'t send the reset email right now. Please try again later or contact support.';
                    }
                } else {
                    $_SESSION['error_message'] = 'Could not create a password reset request. Please try again.';
                }
            } else {
                // IMPORTANT: Do not reveal if the email exists or not for security reasons.
                // Show the same success message.
                $_SESSION['success_message'] = 'If an account with that email exists, a password reset link has been sent. Please check your inbox (and spam folder!).';
            }
            header('Location: ' . BASE_URL . '/auth/forgotPassword');
            exit();
        } else {
            // Redirect if accessed directly via GET without POST
            header('Location: ' . BASE_URL . '/auth/forgotPassword');
            exit();
        }
    }

    /**
     * Displays the form to reset the password using a token.
     * @param string $token The password reset token from the URL.
     */
    public function resetPassword($token = null) {
        if (empty($token)) {
            $_SESSION['error_message'] = 'Invalid or missing password reset token.';
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        $resetRequest = $this->userModel->findUserByPasswordResetToken($token);

        if (!$resetRequest || strtotime($resetRequest['ExpiresAt']) < time()) {
            $errorMessage = 'This password reset link is invalid or has expired. Please request a new one.';
            if ($resetRequest) { // If token found but expired, delete it
                $this->userModel->deletePasswordResetToken($token);
            }
            $_SESSION['error_message'] = $errorMessage;
            header('Location: ' . BASE_URL . '/auth/forgotPassword'); // Or login page
            exit();
        }

        $data = [
            'title' => 'Reset Your Password',
            'token' => $token, // Pass token to the view for the form action
            'input' => [],
            'errors' => [],
            'error_message' => $_SESSION['error_message'] ?? null,
        ];
        unset($_SESSION['error_message']);
        $this->view('auth/reset_password_form', $data);
    }


    public function processResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token']) || !isset($_POST['token'])) {
                $_SESSION['error_message'] = 'Invalid form submission or missing token. Please try again.';
                header('Location: ' . BASE_URL . '/auth/login'); // Or a more specific error page
                exit();
            }

            $token = $_POST['token'];
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $resetRequest = $this->userModel->findUserByPasswordResetToken($token);

            if (!$resetRequest || strtotime($resetRequest['ExpiresAt']) < time()) {
                $_SESSION['error_message'] = 'This password reset link is invalid or has expired. Please request a new one.';
                if ($resetRequest) {
                    $this->userModel->deletePasswordResetToken($token);
                }
                header('Location: ' . BASE_URL . '/auth/forgotPassword');
                exit();
            }

            // Validation for new password
            $errors = [];
            if (empty($newPassword)) {
                $errors['new_password'] = 'New password cannot be empty, honey.';
            } elseif (strlen($newPassword) < 6) {
                $errors['new_password'] = 'Password must be at least 6 characters long.';
            }
            if (empty($confirmPassword)) {
                $errors['confirm_password'] = 'Please confirm your new password.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match. Try again!';
            }

            if (empty($errors)) {
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($this->userModel->updatePassword($resetRequest['UserID'], $newPasswordHash)) {
                    // Delete the used token
                    $this->userModel->deletePasswordResetToken($token);



                    $_SESSION['success_message'] = 'Your password has been successfully reset! You can now log in with your new password.';
                    header('Location: ' . BASE_URL . '/auth/login');
                    exit();
                } else {
                    $_SESSION['error_message'] = 'Could not update your password. Please try again or contact support.';
                    header('Location: ' . BASE_URL . '/auth/resetPassword/' . $token);
                    exit();
                }
            } else {
                // Pass errors back to the form
                $_SESSION['reset_form_data'] = ['input' => $_POST, 'errors' => $errors, 'token' => $token];
                header('Location: ' . BASE_URL . '/auth/resetPassword/' . $token);
                exit();
            }
        } else {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
    }
    
    // --- END OF NEW METHODS ---
    
    private function createUserSession($userArray) {
        // ... (giữ nguyên) ...
        $_SESSION['user_id'] = $userArray['UserID'];
        $_SESSION['user_username'] = $userArray['Username'];
        $_SESSION['user_email'] = $userArray['Email'];
        $_SESSION['user_fullname'] = $userArray['FullName'];
        $_SESSION['user_role'] = $userArray['Role'];
        $_SESSION['user_avatar'] = $userArray['Avatar'] ?? null; 
        $_SESSION['user_status'] = $userArray['Status']; 
    }

    private function redirectToDashboard($role) {
        $dashboardPaths = [
            'Admin' => '/admin/dashboard',
            'Doctor' => '/doctor/dashboard',
            'Nurse' => '/nurse/dashboard',
            'Patient' => '/patient/dashboard'
        ];
        $path = $dashboardPaths[$role] ?? '/'; 
        header('Location: ' . BASE_URL . $path);
        exit();
    }

    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: ' . BASE_URL . '/auth/login');
        exit();
    }
}
?>