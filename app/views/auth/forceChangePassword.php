<?php
// app/views/auth/forceChangePassword.php

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = ($script_name_dir == '/' || $script_name_dir == '\\') ? '' : rtrim($script_name_dir, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Change Your Password'); ?> - Healthcare System</title>
    <style>
        /* Using the same cute styles from login/register */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body.force-change-page-body-cutie { /* Unique class for this body */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #66DDEE, #33AFFF);
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; overflow: hidden; position: relative; color: #333;
        }
        .deco-shape-cutie { position: absolute; border-radius: 50%; background-color: rgba(255, 255, 255, 0.1); z-index: 0; }
        .deco-shape-cutie.shape1-cutie { width: 200px; height: 200px; top: 15%; left: 10%; animation: floaty-animation 6s ease-in-out infinite; }
        .deco-shape-cutie.shape2-cutie { width: 150px; height: 150px; bottom: 10%; right: 8%; animation: floaty-animation 7s ease-in-out infinite 0.5s; }
        .deco-shape-cutie.shape3-cutie { width: 80px; height: 80px; top: 10%; left: 40%; animation: floaty-animation 5s ease-in-out infinite 1s; }
        @keyframes floaty-animation { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        .deco-line-cutie { position: absolute; bottom: 5%; left: 0; width: 100%; height: 80px; z-index: 0; opacity: 0.2; overflow: hidden; }
        .deco-line-cutie svg { width: 100%; height: 100%; stroke: white; stroke-width: 2; fill: transparent; }

        .form-container-cutie { /* Generic name for the form box */
            background-color: rgba(255, 255, 255, 0.95); padding: 40px 50px; border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15); width: 100%; max-width: 450px; /* Slightly wider if needed */
            text-align: center; position: relative; z-index: 1;
            animation: form-appear-cutie 0.6s ease-out forwards; opacity: 0; transform: translateY(20px);
        }
        @keyframes form-appear-cutie { to { opacity: 1; transform: translateY(0); } }
        .form-logo-cutie { font-size: 48px; color: #00BFA6; margin-bottom: 15px; font-weight: 300; line-height: 1; }
        .form-container-cutie h1 { font-size: 24px; color: #333; margin-bottom: 25px; font-weight: 600; }
        
        .form-group-cutie { margin-bottom: 20px; text-align: left; }
        .form-group-cutie label { display: block; font-size: 14px; color: #555; margin-bottom: 8px; font-weight: 500; }
        .form-group-cutie input[type="password"] {
            width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px;
            font-size: 16px; color: #333; background-color: #f9f9f9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group-cutie input[type="password"]:focus {
            outline: none; border-color: #00BFA6; box-shadow: 0 0 0 3px rgba(0, 191, 166, 0.2);
        }
        .form-group-cutie input::placeholder { color: #aaa; }

        .btn-cutie-primary {
            background-color: #00BFA6; color: white; border: none; padding: 14px 20px;
            font-size: 16px; font-weight: bold; border-radius: 8px; cursor: pointer;
            width: 100%; text-transform: uppercase; letter-spacing: 0.5px;
            transition: background-color 0.3s ease, transform 0.2s ease; margin-top: 10px;
        }
        .btn-cutie-primary:hover { background-color: #009682; transform: translateY(-2px); }
        
        .message-box-cutie { padding: 12px 15px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; text-align: left; border-width: 1px; border-style: solid; }
        .error-message-cutie { background-color: #ffebee; color: #c62828; border-color: #ef9a9a; }
        .success-message-cutie { background-color: #e8f5e9; color: #2e7d32; border-color: #a5d6a7; }
        .error-text-cutie { color: #d32f2f; font-size: 12px; margin-top: 5px; }
        .info-message-cutie { /* For informational messages */
            background-color: #e3f2fd; /* Light blue */
            color: #1565c0; /* Darker blue */
            border-color: #90caf9;
        }
    </style>
</head>
<body class="force-change-page-body-cutie">

    <div class="deco-shape-cutie shape1-cutie"></div>
    <div class="deco-shape-cutie shape2-cutie"></div>
    <div class="deco-shape-cutie shape3-cutie"></div>
    <div class="deco-line-cutie">
        <svg viewBox="0 0 1000 100" preserveAspectRatio="xMidYMid slice">
            <polyline points="0,50 100,50 150,20 200,80 250,50 300,30 350,70 400,50 450,60 500,40 550,50 600,20 650,80 700,50 750,30 800,70 850,50 900,60 950,40 1000,50" />
        </svg>
    </div>

    <div class="form-container-cutie">
        <div class="form-logo-cutie">+</div>
        <h1><?php echo htmlspecialchars($data['title'] ?? 'Set Your New Password'); ?></h1>

        <p class="message-box-cutie info-message-cutie" style="margin-bottom: 20px; text-align:center;">
            Welcome! For security, please set a new password for your account.
        </p>

        <?php if (isset($data['error_message']) && $data['error_message']): ?>
            <p class="message-box-cutie error-message-cutie"><?php echo htmlspecialchars($data['error_message']); ?></p>
        <?php endif; ?>
        <?php if (isset($data['success_message']) && $data['success_message']): ?>
            <p class="message-box-cutie success-message-cutie"><?php echo htmlspecialchars($data['success_message']); ?></p>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars(BASE_URL . '/auth/forceChangePassword'); ?>" method="POST" novalidate>
            <?php 
                if (function_exists('generateCsrfInput')) {
                    echo generateCsrfInput(); 
                }
            ?>
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
            
            <button type="submit" class="btn-cutie-primary">Update Password & Login</button>
        </form>
    </div>
</body>
</html>