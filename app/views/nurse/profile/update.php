<?php
// app/views/nurse/profile/update.php

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}
if (!defined('BASE_URL')) { 
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../../../../public'));
}

// Lấy thông tin từ $data do controller truyền qua
$currentUser = $data['currentUser'] ?? []; 
$inputData = $data['input'] ?? $currentUser; 
$errors = $data['errors'] ?? [];

// Dữ liệu cho Topbar và Sidebar
$topbarUserFullName = $currentUser['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Nurse');
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_avatar.png'; 
if (!empty($currentUser['Avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($currentUser['Avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($currentUser['Avatar'], '/');
} elseif (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($_SESSION['user_avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($_SESSION['user_avatar'], '/');
}


$pageTitleForTopbar = $data['title'] ?? 'Update My Profile';
$welcomeMessageForTopbar = 'Manage your personal information and security settings.';
$currentUrl = $_GET['url'] ?? 'nurse/updateProfile'; // <<<< Đảm bảo $currentUrl đúng

$nurseSidebarMenu = [
    [
        'url' => BASE_URL . '/nurse/dashboard', 
        'icon' => '🏠', 
        'text' => 'Dashboard', 
        'active_key' => 'nurse/dashboard'
    ],
    [
        'url' => BASE_URL . '/nurse/listAppointments', 
        'icon' => '🗓️', 
        'text' => 'Manage Appointments', 
        'active_key' => ['nurse/listAppointments', 'nurse/appointmentDetails', 'nurse/showRecordVitalsForm', 'nurse/showAddNursingNoteForm']
    ],
    [
        'url' => BASE_URL . '/nurse/updateProfile', 
        'icon' => '👤', 
        'text' => 'My Profile', 
        'active_key' => 'nurse/updateProfile'
    ],
];
$csrfToken = '';
if (function_exists('generateCsrfToken')) {
    $csrfToken = generateCsrfToken();
} elseif (isset($_SESSION['csrf_token'])) { // Fallback nếu helper chưa được gọi ở mọi nơi
    $csrfToken = $_SESSION['csrf_token'];
}
// Kết thúc phần chuẩn bị dữ liệu cho Topbar và Sidebar
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitleForTopbar); ?> - Nurse Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS chung cho sidebar, topbar, main content (giống các trang Nurse khác) */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; background-color: #f0f2f5; 
            color: #343a40; margin: 0; padding: 0; display: flex; min-height: 100vh;
        }
        /* Sidebar Styles */
.dashboard-sidebar-cutie {
            width: 260px; 
            background-color: rgb(10, 46, 106); 
            background-image: linear-gradient(180deg, rgba(255,255,255,0.07) 0%, rgba(255,255,255,0) 100%);
            color: #ecf0f1; 
            padding: 25px 0; 
            display: flex; 
            flex-direction: column;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.15);
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 22px; font-weight: 700; color: #fff; text-decoration: none; letter-spacing: 0.5px; }
        .sidebar-nav-cutie ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav-cutie li a { 
            display: flex; align-items: center; padding: 14px 25px; 
            color: #dfe6e9; text-decoration: none; font-size: 15px; 
            font-weight: 500; border-left: 4px solid transparent; 
            transition: all 0.25s ease-in-out; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: rgba(255, 255, 255, 0.1); color: #fff; 
            border-left-color: #55efc4; 
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { 
            margin-right: 15px; font-size: 18px; width: 20px; 
            text-align: center; transition: transform 0.2s ease;
        }
        .sidebar-nav-cutie li a:hover .nav-icon-cutie { transform: scale(1.1); }
        .sidebar-nav-cutie li a.logout-link-cutie { margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-nav-cutie li a.logout-link-cutie:hover { background-color: rgba(231, 76, 60, 0.2); border-left-color: #e74c3c; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #bdc3c7; }


        .dashboard-main-content-cutie {
            flex-grow: 1; margin-left: 260px; 
            background-color: #f0f2f5; overflow-y: auto;
        }
        .topbar-shared-cutie {
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 30px; background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .topbar-title-section-cutie h2 { font-size: 22px; font-weight: 600; color: #2c3e50; margin:0; }
        .topbar-title-section-cutie p { font-size: 14px; color: #7f8c8d; margin-top: 4px; }
        .topbar-user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .topbar-user-actions-cutie .icon-button-cutie { 
            background: none; border: none; font-size: 20px; 
            color: #7f8c8d; cursor: pointer; padding: 5px;
            transition: color 0.2s ease;
        }
        .topbar-user-actions-cutie .icon-button-cutie:hover { color: #0a783c; }
        .user-profile-toggle-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
        .user-profile-toggle-cutie img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e0e0; }
        .user-profile-toggle-cutie span { font-weight: 500; font-size: 14px; color: #0a783c; }
        .user-profile-dropdown-content-cutie {
            display: none; position: absolute; top: calc(100% + 5px); right: 0;
            background-color: #fff; border: 1px solid #ddd;
            border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1001; min-width: 160px; overflow: hidden;
        }
        .user-profile-dropdown-content-cutie a {
            display: block; padding: 10px 15px; text-decoration: none;
            color: #333; font-size: 14px; white-space: nowrap;
        }
        .user-profile-dropdown-content-cutie a:hover { background-color: #f5f5f5; }
        .user-profile-toggle-cutie:hover .user-profile-dropdown-content-cutie,
        .user-profile-dropdown-content-cutie.active-dropdown-cutie { display: block; }

        .actual-page-content-wrapper-cutie { padding: 30px; }

        /* CSS cho form profile (tương tự admin/profile/update.php) */
        .profile-container-cutie {
            background-color: #ffffff; padding: 30px 40px; border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07); width: 100%; 
            max-width: 850px; margin: 0 auto; 
        }
        .profile-container-cutie h2.page-title-profile { 
            color: #0a783c; text-align: center; margin-bottom: 30px; 
            font-weight: 600; font-size: 2em; 
        }
        .message-feedback { 
            padding: 15px 20px; margin-bottom: 25px; border-radius: 8px; 
            font-size: 15px; text-align: center; font-weight: 500;
            border-left-width: 5px; border-left-style: solid;
        }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; text-align:left;} /* Thêm text-align left cho error */
        .message-feedback.error ul { list-style-position: inside; padding-left: 5px; margin-top: 5px;}
        .message-feedback.error strong { display: block; margin-bottom: 5px;}
        .error-text { color: #d63031; font-size: 0.875em; margin-top: 4px; display: block; }

        .profile-form-cutie fieldset {
            border: 1px solid #ced4da; padding: 20px;
            border-radius: 8px; margin-bottom: 25px;
        }
        .profile-form-cutie legend {
            font-weight: 600; color: #0a783c; 
            padding: 0 10px; font-size: 1.1em;
        }
        .form-group-cutie { margin-bottom: 20px; }
        .form-group-cutie label {
            display: block; margin-bottom: 8px;
            font-weight: 500; color: #495057;
        }
        .form-group-cutie input[type="text"],
        .form-group-cutie input[type="email"],
        .form-group-cutie input[type="password"],
        .form-group-cutie textarea {
            width: 100%; padding: 12px 15px;
            border: 1px solid #ced4da; border-radius: 6px;
            font-size: 15px; transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-group-cutie input[type="text"]:focus,
        .form-group-cutie input[type="email"]:focus,
        .form-group-cutie input[type="password"]:focus,
        .form-group-cutie textarea:focus {
            border-color: #10ac84; 
            box-shadow: 0 0 0 0.2rem rgba(16, 172, 132, 0.25);
            outline: none;
        }
        .form-group-cutie input[readonly] { background-color: #e9ecef; cursor: not-allowed; }
        .form-group-cutie textarea { resize: vertical; min-height: 80px; }

        .avatar-upload-section-cutie { text-align: center; margin-bottom: 30px; }
        .avatar-preview-cutie {
            width: 160px; height: 160px; border-radius: 50%;
            object-fit: cover; border: 4px solid #c8e6c9; 
            margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .button-change-avatar-cutie {
            background-color: #10ac84; color: white;
            padding: 10px 20px; border-radius: 6px;
            cursor: pointer; font-weight: 500;
            border: none; transition: background-color 0.2s ease;
        }
        .button-change-avatar-cutie:hover { background-color: #0a783c; }
        .form-actions-cutie { margin-top: 30px; text-align: right; }
        .button-submit-profile-cutie, .button-back-profile-cutie {
            padding: 12px 25px; font-size: 15px; font-weight: 500;
            border-radius: 6px; text-decoration: none;
            border: none; cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .button-submit-profile-cutie {
            background-color: #0a783c; color: white; margin-left: 10px;
        }
        .button-submit-profile-cutie:hover { background-color: #086330; transform: translateY(-1px); }
        .button-back-profile-cutie { background-color: #6c757d; color: white; }
        .button-back-profile-cutie:hover { background-color: #5a6268; }

        @media (max-width: 768px) {
            .dashboard-sidebar-cutie { width: 100%; height: auto; position: static; box-shadow: none; }
            .dashboard-main-content-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h2 { font-size: 20px; }
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-wrapper-cutie { padding: 20px 15px; }
            .profile-container-cutie { padding: 20px; }
            .profile-container-cutie h2.page-title-profile { font-size: 1.6em; margin-bottom: 20px; }
            .avatar-preview-cutie { width: 120px; height: 120px; }
            .form-actions-cutie { text-align: center; }
            .button-submit-profile-cutie, .button-back-profile-cutie { display: block; width: 100%; margin: 10px 0 0 0; }
        }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie">
            <a href="<?php echo BASE_URL; ?>/nurse/dashboard" class="sidebar-logo-cutie">Nurse Panel</a>
        </div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <?php foreach ($nurseSidebarMenu as $item): ?>
                    <?php 
                    $isActive = false;
                    if (is_array($item['active_key'])) {
                        foreach ($item['active_key'] as $key) {
                            if (strpos($currentUrl, $key) !== false) {
                                $isActive = true;
                                break;
                            }
                        }
                    } else {
                         if (strpos($currentUrl, $item['active_key']) !== false) {
                            if ($item['active_key'] === 'nurse/dashboard' && ($currentUrl === 'nurse/dashboard' || $currentUrl === 'nurse')) {
                                $isActive = true;
                            } elseif ($item['active_key'] !== 'nurse/dashboard') {
                                $isActive = true;
                            }
                        }
                    }
                    ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($item['url']); ?>" class="<?php echo $isActive ? 'active-nav-cutie' : ''; ?>">
                            <span class="nav-icon-cutie"><?php echo $item['icon']; ?></span><?php echo htmlspecialchars($item['text']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="logout-link-cutie">
                        <span class="nav-icon-cutie">🚪</span>Logout
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="topbar-shared-cutie">
            <div class="topbar-title-section-cutie">
                <h2><?php echo htmlspecialchars($pageTitleForTopbar); ?></h2>
                <p><?php echo htmlspecialchars($welcomeMessageForTopbar); ?></p>
            </div>
            <div class="topbar-user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications" onclick="alert('Notifications (coming soon!)');">🔔</button>
                <div class="user-profile-toggle-cutie" id="userProfileToggle">
                    <img src="<?php echo htmlspecialchars($topbarUserAvatar); ?>" alt="Nurse Avatar">
                    <span><?php echo htmlspecialchars($topbarUserFullName); ?> ▼</span>
                    <div class="user-profile-dropdown-content-cutie" id="userProfileDropdown">
                        <a href="<?php echo BASE_URL; ?>/nurse/updateProfile">My Profile</a>
                        <a href="<?php echo BASE_URL; ?>/auth/logout">Logout</a>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="actual-page-content-wrapper-cutie">
            <div class="profile-container-cutie">
                <h2 class="page-title-profile"><?php echo htmlspecialchars($data['title'] ?? 'Update My Profile'); ?></h2>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="message-feedback success">
                        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                     <div class="message-feedback error"> <!-- Đổi class thành error cho nhất quán -->
                        <strong>Oh no, sweetie!</strong>
                        <p><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="message-feedback error"> <!-- Đổi class thành error -->
                        <strong>Please correct the following errors, my dear:</strong>
                        <ul>
                            <?php foreach ($errors as $errorField => $errorMsg): ?>
                                <li><?php echo htmlspecialchars($errorMsg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>/nurse/updateProfile" method="POST" enctype="multipart/form-data" class="profile-form-cutie">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                    <div class="avatar-upload-section-cutie">
                        <?php
                        $avatarDisplaySrc = BASE_URL . '/public/assets/img/default_avatar.png'; 
                        if (!empty($inputData['Avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($inputData['Avatar'], '/'))) {
                            $avatarDisplaySrc = BASE_URL . '/' . ltrim(htmlspecialchars($inputData['Avatar']), '/');
                        }
                        ?>
                        <img id="avatarPreview" src="<?php echo $avatarDisplaySrc; ?>" alt="Profile Avatar" class="avatar-preview-cutie">
                        <br>
                        <label for="profile_avatar_input" class="button-change-avatar-cutie">
                            Change Profile Picture
                        </label>
                        <input type="file" name="profile_avatar" id="profile_avatar_input" style="display: none;" accept="image/png, image/jpeg, image/gif">
                        <?php if (isset($errors['profile_avatar'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['profile_avatar']); ?></span><?php endif; ?>
                    </div>

                    <fieldset>
                        <legend>Account Information</legend>
                        <div class="form-group-cutie">
                            <label for="FullName">Full Name: *</label>
                            <input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($inputData['FullName'] ?? ''); ?>" required>
                            <?php if (isset($errors['FullName'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['FullName']); ?></span><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="Username">Username:</label>
                            <input type="text" id="Username" name="Username" value="<?php echo htmlspecialchars($inputData['Username'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-group-cutie">
                            <label for="Email">Email: *</label>
                            <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($inputData['Email'] ?? ''); ?>" required>
                            <?php if (isset($errors['Email'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['Email']); ?></span><?php endif; ?>
                        </div>
                         <div class="form-group-cutie">
                            <label for="PhoneNumber">Phone Number:</label>
                            <input type="text" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($inputData['PhoneNumber'] ?? ''); ?>">
                            <?php if (isset($errors['PhoneNumber'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['PhoneNumber']); ?></span><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="Address">Address:</label>
                            <textarea name="Address" id="Address" rows="3"><?php echo htmlspecialchars($inputData['Address'] ?? ''); ?></textarea>
                            <?php if (isset($errors['Address'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['Address']); ?></span><?php endif; ?>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Change Password (leave blank if not changing)</legend>
                         <div class="form-group-cutie">
                            <label for="current_password">Current Password:</label>
                            <input type="password" id="current_password" name="current_password">
                            <?php if (isset($errors['current_password'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['current_password']); ?></span><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="new_password">New Password (min. 6 characters):</label>
                            <input type="password" id="new_password" name="new_password">
                            <?php if (isset($errors['new_password'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['new_password']); ?></span><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="confirm_new_password">Confirm New Password:</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password">
                            <?php if (isset($errors['confirm_new_password'])): ?><span class="error-text"><?php echo htmlspecialchars($errors['confirm_new_password']); ?></span><?php endif; ?>
                        </div>
                    </fieldset>

                    <div class="form-actions-cutie">
                        <a href="<?php echo BASE_URL; ?>/nurse/dashboard" class="button-back-profile-cutie">Back to Dashboard</a>
                        <button type="submit" class="button-submit-profile-cutie">Update My Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('profile_avatar_input');
    const avatarPreview = document.getElementById('avatarPreview');
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    const userProfileToggle = document.getElementById('userProfileToggle');
    const userProfileDropdown = document.getElementById('userProfileDropdown');
    if (userProfileToggle && userProfileDropdown) {
        userProfileToggle.addEventListener('click', function(event) {
            event.stopPropagation();
            userProfileDropdown.classList.toggle('active-dropdown-cutie');
        });
        document.addEventListener('click', function(event) {
            if (userProfileDropdown.classList.contains('active-dropdown-cutie') && !userProfileToggle.contains(event.target)) {
                userProfileDropdown.classList.remove('active-dropdown-cutie');
            }
        });
    }
});
</script>
</body>
</html>