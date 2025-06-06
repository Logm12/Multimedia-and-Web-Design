<?php
// app/views/auth/forgot_password_form.php

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

$data = $data ?? ['title' => 'Forgot Password?', 'input' => [], 'error_message' => null, 'success_message' => null];
$csrfToken = '';
if (function_exists('generateCsrfToken')) {
    $csrfToken = generateCsrfToken();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Forgot Password'); ?> - PulseCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Common Auth Styles */
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
        
        .form-group-cutie { margin-bottom: 20px; text-align: left; }
        .form-group-cutie label { display: block; font-size: 14px; color: #37474F; margin-bottom: 8px; font-weight: 500; }
        .form-group-cutie input[type="email"], .form-group-cutie input[type="password"] {
            width: 100%; padding: 12px 15px; border: 1px solid #B0BEC5; border-radius: 8px; font-size: 15px; color: #37474F;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group-cutie input[type="email"]:focus, .form-group-cutie input[type="password"]:focus { 
            outline: none; border-color: #00796B;
            box-shadow: 0 0 0 3px rgba(0, 121, 107, 0.2); 
        }
        
        .btn-auth-primary-cutie {
            background-color: #00796B;
            color: white; border: none; padding: 14px 20px; font-size: 16px; font-weight: 600;
            border-radius: 8px; cursor: pointer; width: 100%; text-transform: uppercase; letter-spacing: 0.8px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 10px;
        }
        .btn-auth-primary-cutie:hover { background-color: #004D40; transform: translateY(-2px); }
        
        .message-box-cutie { padding: 12px 15px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; text-align: left; border-width: 1px; border-style: solid; }
        .error-message-cutie { background-color: #FFEBEE; color: #C62828; border-color: #FFCDD2; }
        .success-message-cutie { background-color: #E8F5E9; color: #2E7D32; border-color: #C8E6C9; }
        
        .auth-alternative-link-cutie { margin-top: 25px; text-align: center; font-size: 14px; color: #455A64; }
        .auth-alternative-link-cutie a { color: #00796B; text-decoration: none; font-weight: 600; }
        .auth-alternative-link-cutie a:hover { text-decoration: underline; }

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
        }
    </style>
</head>
<body>
    <div class="auth-container-wrapper-cutie">
        <div class="auth-form-section-cutie">
            <div class="auth-header-cutie">
                <img src="<?php echo BASE_URL; ?>/assets/images/pulsecare_logo.png" alt="PulseCare Logo" class="auth-logo-img-cutie">
            </div>

            <h1>Forgot Your Password?</h1>
            <p class="form-description-cutie">
                No worries! Enter the email address associated with your account, and we'll send you a link to reset your password.
            </p>

            <?php if (isset($data['error_message']) && $data['error_message']): ?>
                <p class="message-box-cutie error-message-cutie"><?php echo htmlspecialchars($data['error_message']); ?></p>
            <?php endif; ?>
            <?php if (isset($data['success_message']) && $data['success_message']): ?>
                <p class="message-box-cutie success-message-cutie"><?php echo htmlspecialchars($data['success_message']); ?></p>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars(BASE_URL . '/auth/processForgotPassword'); ?>" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                
                <div class="form-group-cutie">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="youremail@example.com" value="<?php echo htmlspecialchars($data['input']['email'] ?? '', ENT_QUOTES); ?>" required>
                </div>
                
                <button type="submit" class="btn-auth-primary-cutie">Send Reset Link</button>
            </form>
            <p class="auth-alternative-link-cutie">
                Remember your password? <a href="<?php echo htmlspecialchars(BASE_URL . '/auth/login'); ?>">Login</a>
            </p>
        </div>
        <div class="auth-decorative-section-cutie">
            <div class="decorative-content-cutie">
                <img src="<?php echo BASE_URL; ?>/assets/images/doctor_login_image.png" alt="Professional Doctor">
                <h2>We're here to help you get back on track.</h2>
                <p>Your well-being is our priority, and that includes easy access to your account.</p>
            </div>
        </div>
    </div>
</body>
</html>