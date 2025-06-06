<?php
// app/views/auth/login.php

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = ($script_name_dir == '/' || $script_name_dir == '\\') ? '' : rtrim($script_name_dir, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$data = $data ?? ['title' => 'Login', 'input' => [], 'errors' => [], 'error_message' => null, 'success_message' => null];
$csrfToken = '';
if (function_exists('generateCsrfToken')) {
    $csrfToken = generateCsrfToken();
} elseif (isset($_SESSION['csrf_token'])) {
    $csrfToken = $_SESSION['csrf_token'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Login'); ?> - PulseCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #E0F7FA 0%, #B2EBF2 100%);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            color: #37474F;
            padding: 20px;
        }
        .auth-container-wrapper-cutie { 
            display: flex; 
            width: 100%; 
            max-width: 950px;
            min-height: 600px;
            background-color: #fff; 
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 77, 64, 0.1);
            overflow: hidden; 
        }
        
        .auth-form-section-cutie { 
            flex: 1; 
            padding: 40px 50px; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
        }
        .auth-header-cutie { 
            display: flex; 
            align-items: center; 
            margin-bottom: 30px;
        }
        .auth-logo-img-cutie { 
            width: 120px;
            height: auto;
            margin-right: 15px;
        }
        
        .auth-form-section-cutie h1 { 
            font-size: 26px;
            font-weight: 700; 
            color: #004D40;
            margin-bottom: 10px; 
        }
        .auth-form-section-cutie .form-description-cutie { 
            font-size: 15px; 
            color: #546E7A; 
            margin-bottom: 30px;
            line-height: 1.6; 
        }
        
        .role-selector-cutie { margin-bottom: 25px; }
        .role-selector-cutie p { font-size: 14px; color: #37474F; margin-bottom: 10px; font-weight: 500; text-align: left; }
        .role-buttons-cutie { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .role-button-cutie {
            padding: 12px 10px; border: 1px solid #CFD8DC; border-radius: 8px; background-color: #fff;
            cursor: pointer; text-align: center; transition: all 0.3s ease; font-size: 13px; color: #455A64;
            display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 8px;
        }
        .role-button-cutie:hover { border-color: #00796B; background-color: #E0F2F1;}
        .role-button-cutie.selected-role-cutie { 
            border-color: #004D40;
            background-color: #00796B;
            color: #fff;
            font-weight: 600; 
        }
        .role-button-cutie .role-icon-placeholder-cutie { font-size: 18px; }

        .form-group-cutie { margin-bottom: 20px; text-align: left; }
        .form-group-cutie label { display: block; font-size: 14px; color: #37474F; margin-bottom: 8px; font-weight: 500; }
        .form-group-cutie input[type="text"], .form-group-cutie input[type="password"] {
            width: 100%; padding: 12px 15px; border: 1px solid #B0BEC5; border-radius: 8px; font-size: 15px; color: #37474F;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group-cutie input[type="text"]:focus, .form-group-cutie input[type="password"]:focus { 
            outline: none; border-color: #00796B;
            box-shadow: 0 0 0 3px rgba(0, 121, 107, 0.2); 
        }
        
        .forgot-password-link-cutie { 
            display: block; text-align: right; font-size: 13px; 
            color: #00796B;
            text-decoration: none; margin-top: -10px; margin-bottom: 20px; 
        }
        .forgot-password-link-cutie:hover { text-decoration: underline; }

        .btn-auth-primary-cutie {
            background-color: #00796B;
            color: white; border: none; padding: 14px 20px; font-size: 16px; font-weight: 600;
            border-radius: 8px; cursor: pointer; width: 100%; text-transform: uppercase; letter-spacing: 0.8px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-auth-primary-cutie:hover { background-color: #004D40; transform: translateY(-2px); }
        
        .message-box-cutie { padding: 12px 15px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; text-align: left; border-width: 1px; border-style: solid; }
        .error-message-cutie { background-color: #FFEBEE; color: #C62828; border-color: #FFCDD2; }
        .success-message-cutie { background-color: #E8F5E9; color: #2E7D32; border-color: #C8E6C9; }
        .error-text-cutie { color: #D32F2F; font-size: 12px; margin-top: 5px; }
        
        .auth-alternative-link-cutie { margin-top: 25px; text-align: center; font-size: 14px; color: #455A64; }
        .auth-alternative-link-cutie a { color: #00796B; text-decoration: none; font-weight: 600; }
        .auth-alternative-link-cutie a:hover { text-decoration: underline; }
        .auth-alternative-link-cutie.hidden-by-role-cutie { display: none !important; }

        .auth-decorative-section-cutie {
            flex: 1.2;
            background: linear-gradient(to bottom right, #4DB6AC, #26A69A);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            position: relative; 
            overflow: hidden;
            padding: 40px;
        }
        .decorative-content-cutie {
            text-align: center;
            color: white;
        }
        .decorative-content-cutie img {
            max-width: 80%;
            height: auto;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .decorative-content-cutie h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        .decorative-content-cutie p {
            font-size: 16px;
            line-height: 1.7;
            max-width: 400px;
            margin: 0 auto;
            opacity: 0.9;
        }

        @media (max-width: 850px) {
            .auth-container-wrapper-cutie { flex-direction: column; max-width: 500px; min-height: auto; }
            .auth-decorative-section-cutie { 
                min-height: 300px;
                flex: unset;
                order: -1;
            }
            .decorative-content-cutie img { max-width: 60%; margin-bottom: 20px;}
            .decorative-content-cutie h2 { font-size: 22px; }
            .decorative-content-cutie p { font-size: 14px; }
            .auth-form-section-cutie { padding: 30px; }
            .role-buttons-cutie { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="auth-container-wrapper-cutie">
        <div class="auth-form-section-cutie">
            <div class="auth-header-cutie">
                <!-- <<<< SỬA LẠI ĐƯỜNG DẪN Ở ĐÂY NÈ CẬU >>>> -->
                <img src="<?php echo BASE_URL; ?>/assets/images/pulsecare_logo.png" alt="PulseCare Logo" class="auth-logo-img-cutie">
            </div>

            <h1>Welcome Back!</h1>
            <p class="form-description-cutie">Your health is our priority. Please log in to continue.</p>

            <?php if (isset($data['error_message']) && $data['error_message']): ?>
                <p class="message-box-cutie error-message-cutie"><?php echo htmlspecialchars($data['error_message']); ?></p>
            <?php endif; ?>
            <?php if (isset($data['success_message']) && $data['success_message']): ?>
                <p class="message-box-cutie success-message-cutie"><?php echo htmlspecialchars($data['success_message']); ?></p>
            <?php endif; ?>

            <div class="role-selector-cutie">
                <p>I am a...</p>
                <div class="role-buttons-cutie">
                    <button type="button" class="role-button-cutie selected-role-cutie" data-role="Patient">
                        <span class="role-icon-placeholder-cutie">👤</span> Patient
                    </button>
                    <button type="button" class="role-button-cutie" data-role="Nurse">
                        <span class="role-icon-placeholder-cutie">👩‍⚕️</span> Nurse
                    </button>
                    <button type="button" class="role-button-cutie" data-role="Doctor">
                        <span class="role-icon-placeholder-cutie">👨‍⚕️</span> Doctor
                    </button>
                    <button type="button" class="role-button-cutie" data-role="Admin">
                        <span class="role-icon-placeholder-cutie">⚙️</span> Admin
                    </button>
                </div>
            </div>

            <form action="<?php echo htmlspecialchars(BASE_URL . '/auth/login'); ?>" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <!-- Thêm input ẩn cho vai trò được chọn -->
                <input type="hidden" name="selected_role" id="selected_role_input" value="Patient">
                
                <div class="form-group-cutie">
                    <label for="username_or_email">Username or Email</label>
                    <input type="text" id="username_or_email" name="username_or_email" placeholder="e.g., yourusername or email@example.com" value="<?php echo htmlspecialchars($data['input']['username_or_email'] ?? '', ENT_QUOTES); ?>" required>
                    <?php if (isset($data['errors']['username_or_email'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['username_or_email']); ?></p><?php endif; ?>
                </div>

                <div class="form-group-cutie">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <?php if (isset($data['errors']['password'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['password']); ?></p><?php endif; ?>
                </div>
                <a href="<?php echo htmlspecialchars(BASE_URL . '/auth/forgotPassword'); ?>" class="forgot-password-link-cutie">Forgot password?</a>
                
                <button type="submit" class="btn-auth-primary-cutie">Sign In</button>
            </form>
            <p class="auth-alternative-link-cutie" id="signup_link_container"> 
                Don't have an account? <a href="<?php echo htmlspecialchars(BASE_URL . '/patient/register'); ?>">Sign Up as a Patient</a>
            </p>
        </div>
        <div class="auth-decorative-section-cutie">
            <div class="decorative-content-cutie">
                <!-- <<<< VÀ SỬA LẠI ĐƯỜNG DẪN Ở ĐÂY NỮA NHA >>>> -->
                <img src="<?php echo BASE_URL; ?>/assets/images/doctor_login_image.png" alt="Professional Doctor">
                <h2>Taking care of your health is our top priority.</h2>
                <p>Being healthy is more than just not getting sick. It entails mental, physical, and social well-being. It's not just about treatment, it's about healing.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleButtons = document.querySelectorAll('.role-button-cutie');
            const selectedRoleInput = document.getElementById('selected_role_input');
            const signupLinkContainer = document.getElementById('signup_link_container'); 

            function toggleSignUpLink(selectedRole) {
                if (signupLinkContainer) {
                    if (selectedRole === 'Patient') {
                        signupLinkContainer.classList.remove('hidden-by-role-cutie');
                    } else {
                        signupLinkContainer.classList.add('hidden-by-role-cutie');
                    }
                }
            }
            
            const defaultRole = selectedRoleInput ? selectedRoleInput.value : 'Patient';
            toggleSignUpLink(defaultRole);
            
            roleButtons.forEach(button => {
                if (button.dataset.role === defaultRole) {
                    button.classList.add('selected-role-cutie');
                } else {
                    button.classList.remove('selected-role-cutie');
                }
            });

            roleButtons.forEach(button => {
                button.addEventListener('click', function () {
                    roleButtons.forEach(btn => btn.classList.remove('selected-role-cutie'));
                    this.classList.add('selected-role-cutie');
                    const currentRole = this.dataset.role;
                    if(selectedRoleInput) {
                        selectedRoleInput.value = currentRole;
                    }
                    toggleSignUpLink(currentRole); 
                });
            });
        });
    </script>
</body>
</html>