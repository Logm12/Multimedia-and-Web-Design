<?php
// app/views/patient/register.php

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = ($script_name_dir == '/' || $script_name_dir == '\\') ? '' : rtrim($script_name_dir, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}
// $data = $data ?? ['title' => 'Patient Registration', 'input' => [], 'errors' => [], 'error_message' => null, 'success_message' => null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Patient Registration'); ?> - Healthcare System</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fc; display: flex; justify-content: center; align-items: flex-start; /* Align top for long forms */ min-height: 100vh; color: #333; padding-top: 30px; padding-bottom: 30px; } /* Added padding top/bottom */
        .auth-container-wrapper-cutie { display: flex; width: 100%; max-width: 1000px; /* min-height: 650px; */ background-color: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 30px; /* Margin for scroll */ }
        
        .auth-form-section-cutie { flex: 1; padding: 30px 40px; /* Adjusted padding */ display: flex; flex-direction: column; justify-content: flex-start; /* Align content to top */ }
        .auth-header-cutie { display: flex; align-items: center; margin-bottom: 20px; } /* Adjusted margin */
        .auth-logo-placeholder-cutie { width: 36px; height: 36px; background-color: #7c4dff; border-radius: 7px; display: flex; justify-content: center; align-items: center; color: white; font-weight: bold; font-size: 16px; margin-right: 10px; }
        .auth-hospital-name-cutie { font-size: 18px; font-weight: 600; color: #333; }
        
        .auth-form-section-cutie h1 { font-size: 26px; font-weight: 700; color: #333; margin-bottom: 20px; } /* Adjusted margin */
        
        .form-grid-cutie { display: grid; grid-template-columns: 1fr 1fr; gap: 0 20px; } /* Grid for two columns */
        .form-group-cutie { margin-bottom: 15px; text-align: left; } /* Adjusted margin */
        .form-group-cutie.full-width-cutie { grid-column: 1 / -1; } /* For elements spanning full width */

        .form-group-cutie label { display: block; font-size: 13px; color: #555; margin-bottom: 6px; font-weight: 500; } /* Adjusted font size */
        .form-group-cutie input[type="text"], .form-group-cutie input[type="email"], .form-group-cutie input[type="password"], .form-group-cutie input[type="date"], .form-group-cutie textarea, .form-group-cutie select {
            width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 7px; font-size: 14px; color: #333; /* Adjusted padding/font */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group-cutie textarea { min-height: 70px; resize: vertical; } /* Adjusted height */
        .form-group-cutie input:focus, .form-group-cutie textarea:focus, .form-group-cutie select:focus { outline: none; border-color: #7c4dff; box-shadow: 0 0 0 3px rgba(124, 77, 255, 0.2); }
        
        .btn-auth-primary-cutie {
            background-color: #7c4dff; color: white; border: none; padding: 12px 20px; font-size: 15px; font-weight: bold; /* Adjusted padding/font */
            border-radius: 8px; cursor: pointer; width: 100%; text-transform: uppercase; letter-spacing: 0.5px;
            transition: background-color 0.3s ease, transform 0.2s ease; margin-top: 15px; /* Adjusted margin */
        }
        .btn-auth-primary-cutie:hover { background-color: #651fff; transform: translateY(-2px); }
        
        .message-box-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 7px; font-size: 13px; text-align: left; border-width: 1px; border-style: solid; } /* Adjusted */
        .error-message-cutie { background-color: #ffebee; color: #c62828; border-color: #ef9a9a; }
        .success-message-cutie { background-color: #e8f5e9; color: #2e7d32; border-color: #a5d6a7; }
        .error-text-cutie { color: #d32f2f; font-size: 11px; margin-top: 4px; } /* Adjusted */
        
        .auth-alternative-link-cutie { margin-top: 20px; text-align: center; font-size: 13px; } /* Adjusted */
        .auth-alternative-link-cutie a { color: #7c4dff; text-decoration: none; font-weight: 500; }
        .auth-alternative-link-cutie a:hover { text-decoration: underline; }

        .auth-decorative-section-cutie {
            flex: 1; background-color: #ede7f6;
            background-image: radial-gradient(#d1c4e9 1px, transparent 1px); background-size: 15px 15px;
            display: flex; justify-content: center; align-items: center; position: relative; overflow: hidden;
            min-height: 650px; /* Ensure it has some height even if form is shorter */
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
        .deco-card-cutie img { max-width: 80%; max-height: 80%; opacity: 0.7; }

        @media (max-width: 850px) { /* Adjusted breakpoint for better form layout */
            .form-grid-cutie { grid-template-columns: 1fr; } /* Single column for smaller screens */
        }
        @media (max-width: 768px) {
            .auth-container-wrapper-cutie { flex-direction: column; max-width: 500px; min-height: auto; }
            .auth-decorative-section-cutie { display: none; }
            .auth-form-section-cutie { padding: 30px; }
        }
    </style>
</head>
<body>
    <div class="auth-container-wrapper-cutie">
        <div class="auth-form-section-cutie">
            <div class="auth-header-cutie">
                <div class="auth-logo-placeholder-cutie">L</div>
                <span class="auth-hospital-name-cutie">Hospital's Name</span>
            </div>

            <h1>Create Patient Account</h1>

            <?php if (isset($data['error_message']) && $data['error_message']): ?>
                <p class="message-box-cutie error-message-cutie"><?php echo htmlspecialchars($data['error_message']); ?></p>
            <?php endif; ?>
            <?php if (isset($data['success_message']) && $data['success_message']): ?>
                <p class="message-box-cutie success-message-cutie"><?php echo htmlspecialchars($data['success_message']); ?></p>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars(BASE_URL . '/patient/register'); ?>" method="POST" novalidate>
                <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                
                <div class="form-grid-cutie">
                    <div class="form-group-cutie">
                        <label for="fullname">Full Name:</label>
                        <input type="text" id="fullname" name="fullname" placeholder="E.g., Cutie Pie" value="<?php echo htmlspecialchars($data['input']['fullname'] ?? '', ENT_QUOTES); ?>" required>
                        <?php if (isset($data['errors']['fullname'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['fullname']); ?></p><?php endif; ?>
                    </div>

                    <div class="form-group-cutie">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" placeholder="Choose a unique username" value="<?php echo htmlspecialchars($data['input']['username'] ?? '', ENT_QUOTES); ?>" required>
                        <?php if (isset($data['errors']['username'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['username']); ?></p><?php endif; ?>
                    </div>

                    <div class="form-group-cutie">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($data['input']['email'] ?? '', ENT_QUOTES); ?>" required>
                        <?php if (isset($data['errors']['email'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['email']); ?></p><?php endif; ?>
                    </div>

                    <div class="form-group-cutie">
                        <label for="phone_number">Phone Number: (Optional)</label>
                        <input type="text" id="phone_number" name="phone_number" placeholder="E.g., 09xxxxxxxx" value="<?php echo htmlspecialchars($data['input']['phone_number'] ?? '', ENT_QUOTES); ?>">
                        <?php if (isset($data['errors']['phone_number'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['phone_number']); ?></p><?php endif; ?>
                    </div>
                    
                    <div class="form-group-cutie">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                        <?php if (isset($data['errors']['password'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['password']); ?></p><?php endif; ?>
                    </div>

                    <div class="form-group-cutie">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>
                        <?php if (isset($data['errors']['confirm_password'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['confirm_password']); ?></p><?php endif; ?>
                    </div>

                    <div class="form-group-cutie">
                        <label for="date_of_birth">Date of Birth: (Optional)</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($data['input']['date_of_birth'] ?? '', ENT_QUOTES); ?>">
                        <?php if (isset($data['errors']['date_of_birth'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['date_of_birth']); ?></p><?php endif; ?>
                    </div>

                    <div class="form-group-cutie">
                        <label for="gender">Gender: (Optional)</label>
                        <select id="gender" name="gender">
                            <option value="" <?php echo (!isset($data['input']['gender']) || $data['input']['gender'] == '') ? 'selected' : ''; ?>>-- Select Gender --</option>
                            <option value="Male" <?php echo (isset($data['input']['gender']) && $data['input']['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($data['input']['gender']) && $data['input']['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($data['input']['gender']) && $data['input']['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <?php if (isset($data['errors']['gender'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['gender']); ?></p><?php endif; ?>
                    </div>
                    
                    <div class="form-group-cutie full-width-cutie">
                        <label for="address">Address: (Optional)</label>
                        <textarea id="address" name="address" placeholder="Your current address"><?php echo htmlspecialchars($data['input']['address'] ?? '', ENT_QUOTES); ?></textarea>
                        <?php if (isset($data['errors']['address'])): ?><p class="error-text-cutie"><?php echo htmlspecialchars($data['errors']['address']); ?></p><?php endif; ?>
                    </div>
                </div> 
                
                <button type="submit" class="btn-auth-primary-cutie">Register</button>
            </form>
            <p class="auth-alternative-link-cutie">
                Already have an account? <a href="<?php echo htmlspecialchars(BASE_URL . '/auth/login'); ?>">Login Here</a>
            </p>
        </div>
        <div class="auth-decorative-section-cutie">
             <div class="deco-card-stack-cutie">
                <div class="deco-card-cutie c1"><span>Join Our Community</span></div>
                <div class="deco-card-cutie c2"><span>Easy Registration</span></div>
                <div class="deco-card-cutie c3"><img src="https://via.placeholder.com/120?text=Welcome" alt="Decorative Image"></div>
            </div>
        </div>
    </div>
</body>
</html>