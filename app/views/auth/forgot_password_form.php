<?php
// app/views/auth/forgot_password_form.php

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = ($script_name_dir == '/' || $script_name_dir == '\\') ? '' : rtrim($script_name_dir, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}
// $data = $data ?? ['title' => 'Forgot Password?', 'input' => [], 'error_message' => null, 'success_message' => null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Forgot Password'); ?> - Healthcare System</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fc; display: flex; justify-content: center; align-items: center; min-height: 100vh; color: #333; }
        .auth-container-wrapper-cutie { display: flex; width: 100%; max-width: 1000px; min-height: 600px; background-color: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        
        .auth-form-section-cutie { flex: 1; padding: 40px 50px; display: flex; flex-direction: column; justify-content: center; }
        .auth-header-cutie { display: flex; align-items: center; margin-bottom: 30px; }
        .auth-logo-placeholder-cutie { width: 40px; height: 40px; background-color: #7c4dff; border-radius: 8px; display: flex; justify-content: center; align-items: center; color: white; font-weight: bold; font-size: 18px; margin-right: 12px; }
        .auth-hospital-name-cutie { font-size: 20px; font-weight: 600; color: #333; }
        
        .auth-form-section-cutie h1 { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 10px; }
        .auth-form-section-cutie .form-description-cutie { font-size: 15px; color: #666; margin-bottom: 30px; line-height: 1.6; }
        
        .form-group-cutie { margin-bottom: 20px; }
        .form-group-cutie label { display: block; font-size: 14px; color: #555; margin-bottom: 8px; font-weight: 500; }
        .form-group-cutie input[type="email"] {
            width: 100%; padding: 12px 15px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 16px; color: #333;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group-cutie input[type="email"]:focus { outline: none; border-color: #7c4dff; box-shadow: 0 0 0 3px rgba(124, 77, 255, 0.2); }
        
        .btn-auth-primary-cutie {
            background-color: #7c4dff; color: white; border: none; padding: 14px 20px; font-size: 16px; font-weight: bold;
            border-radius: 8px; cursor: pointer; width: 100%; text-transform: uppercase; letter-spacing: 0.5px;
            transition: background-color 0.3s ease, transform 0.2s ease; margin-top: 10px;
        }
        .btn-auth-primary-cutie:hover { background-color: #651fff; transform: translateY(-2px); }
        
        .message-box-cutie { padding: 12px 15px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; text-align: left; border-width: 1px; border-style: solid; }
        .error-message-cutie { background-color: #ffebee; color: #c62828; border-color: #ef9a9a; }
        .success-message-cutie { background-color: #e8f5e9; color: #2e7d32; border-color: #a5d6a7; }
        
        .auth-alternative-link-cutie { margin-top: 25px; text-align: center; font-size: 14px; }
        .auth-alternative-link-cutie a { color: #7c4dff; text-decoration: none; font-weight: 500; }
        .auth-alternative-link-cutie a:hover { text-decoration: underline; }

        .auth-decorative-section-cutie {
            flex: 1; background-color: #ede7f6; /* Light purple background */
            background-image: radial-gradient(#d1c4e9 1px, transparent 1px); background-size: 15px 15px; /* Plus pattern */
            display: flex; justify-content: center; align-items: center; position: relative; overflow: hidden;
        }
        .deco-card-stack-cutie { position: relative; width: 280px; height: 350px; }
        .deco-card-cutie {
            position: absolute; border-radius: 15px; background-color: rgba(255,255,255,0.6);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: center; font-size: 14px; color: #555;
        }
        .deco-card-cutie.c1 { width: 220px; height: 300px; background-color: #e3f2fd; top: 0; left: 30px; z-index: 1; transform: rotate(-5deg); }
        .deco-card-cutie.c2 { width: 250px; height: 280px; background-color: #e0f2f1; top: 50px; left: 0; z-index: 2; transform: rotate(8deg); }
        .deco-card-cutie.c3 { width: 200px; height: 250px; background-color: #fff3e0; top: 80px; left: 50px; z-index: 3; transform: rotate(-3deg); }
        .deco-card-cutie img { max-width: 80%; max-height: 80%; opacity: 0.7; } /* Placeholder for image */

        @media (max-width: 768px) {
            .auth-container-wrapper-cutie { flex-direction: column; max-width: 500px; min-height: auto; }
            .auth-decorative-section-cutie { display: none; /* Hide decorative part on small screens or make it smaller */ }
            .auth-form-section-cutie { padding: 30px; }
        }
    </style>
</head>
<body>
    <div class="auth-container-wrapper-cutie">
        <div class="auth-form-section-cutie">
            <div class="auth-header-cutie">
                <div class="auth-logo-placeholder-cutie">L</div> <!-- Placeholder Logo -->
                <span class="auth-hospital-name-cutie">Hospital's Name</span>
            </div>

            <h1><?php echo htmlspecialchars($data['title'] ?? 'Forgot Password?'); ?></h1>
            <p class="form-description-cutie">
                Enter the email address associated with your account, and we'll send you a link to reset your password.
            </p>

            <?php if (isset($data['error_message']) && $data['error_message']): ?>
                <p class="message-box-cutie error-message-cutie"><?php echo htmlspecialchars($data['error_message']); ?></p>
            <?php endif; ?>
            <?php if (isset($data['success_message']) && $data['success_message']): ?>
                <p class="message-box-cutie success-message-cutie"><?php echo htmlspecialchars($data['success_message']); ?></p>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars(BASE_URL . '/auth/processForgotPassword'); ?>" method="POST" novalidate>
                <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                
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
            <div class="deco-card-stack-cutie">
                <div class="deco-card-cutie c1"><span>Healthcare Illustration 1</span></div>
                <div class="deco-card-cutie c2"><span>Stay Healthy!</span></div>
                <div class="deco-card-cutie c3"><img src="https://via.placeholder.com/100?text=Doctor" alt="Decorative Image"></div>
            </div>
        </div>
    </div>
</body>
</html>