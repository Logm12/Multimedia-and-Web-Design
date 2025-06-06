<?php
// app/views/auth/forceChangePassword.php

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

$data = $data ?? ['title' => 'Set Your New Password', 'errors' => [], 'error_message' => null];
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
    <title><?php echo htmlspecialchars($data['title'] ?? 'Change Your Password'); ?> - PulseCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Using a slightly different, focused style for this single-purpose page */
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
        .form-container-cutie {
            background-color: #fff;
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 77, 64, 0.1);
            width: 100%;
            max-width: 480px;
            text-align: center;
            animation: form-appear-cutie 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        @keyframes form-appear-cutie { to { opacity: 1; transform: translateY(0); } }
        
        .form-logo-cutie {
            width: 60px; height: 60px;
            background-color: #00796B;
            color: white;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
        .form-container-cutie h1 { font-size: 24px; color: #004D40; margin-bottom: 10px; font-weight: 600; }
        .form-container-cutie p.form-description-cutie { font-size: 15px; color: #546E7A; margin-bottom: 25px; line-height: 1.6; }
        
        .form-group-cutie { margin-bottom: 20px; text-align: left; }
        .form-group-cutie label { display: block; font-size: 14px; color: #37474F; margin-bottom: 8px; font-weight: 500; }
        .form-group-cutie input[type="password"] {
            width: 100%; padding: 12px 15px; border: 1px solid #B0BEC5; border-radius: 8px;
            font-size: 15px; color: #37474F;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group-cutie input[type="password"]:focus {
            outline: none; border-color: #00796B; box-shadow: 0 0 0 3px rgba(0, 121, 107, 0.2);
        }

        .btn-auth-primary-cutie {
            background-color: #00796B; color: white; border: none; padding: 14px 20px;
            font-size: 16px; font-weight: 600; border-radius: 8px; cursor: pointer;
            width: 100%; text-transform: uppercase; letter-spacing: 0.8px;
            transition: background-color 0.3s ease, transform 0.2s ease; margin-top: 10px;
        }
        .btn-auth-primary-cutie:hover { background-color: #004D40; transform: translateY(-2px); }
        
        .message-box-cutie { padding: 12px 15px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; text-align: left; border-width: 1px; border-style: solid; }
        .error-message-cutie { background-color: #FFEBEE; color: #C62828; border-color: #FFCDD2; }
        .info-message-cutie { background-color: #E3F2FD; color: #1565C0; border-color: #90CAF9; }
        .error-text-cutie { color: #D32F2F; font-size: 12px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="form-container-cutie">
        <div class="form-logo-cutie">🔑</div>
        <h1><?php echo htmlspecialchars($data['title'] ?? 'Set Your New Password'); ?></h1>

        <p class="form-description-cutie">
            Welcome! For security reasons, you must set a new password for your account before proceeding.
        </p>

        <?php if (isset($data['error_message']) && $data['error_message']): ?>
            <p class="message-box-cutie error-message-cutie"><?php echo htmlspecialchars($data['error_message']); ?></p>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars(BASE_URL . '/auth/forceChangePassword'); ?>" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            
            <div class="form-group-cutie">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter your new password (min. 6 chars)" required>
                <?php if (isset($data['errors']['new_password'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['new_password']); ?></p><?php endif; ?>
            </div>

            <div class="form-group-cutie">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your new password" required>
                <?php if (isset($data['errors']['confirm_password'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['confirm_password']); ?></p><?php endif; ?>
            </div>
            
            <button type="submit" class="btn-auth-primary-cutie">Update Password & Continue</button>
        </form>
    </div>
</body>
</html>