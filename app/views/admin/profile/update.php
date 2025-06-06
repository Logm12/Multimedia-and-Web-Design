<?php
// app/views/admin/profile/update.php

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}
if (!defined('BASE_URL')) { 
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../../../../public'));
}


$csrfToken = generateCsrfToken(); 
$currentUrl = $_GET['url'] ?? 'admin/updateProfile';


$topbarUserFullName = $_SESSION['user_fullname'] ?? 'Admin';
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_admin_avatar.png'; 
if (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar'])) {
    $sessionAvatarPath = $_SESSION['user_avatar'];
    if (file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($sessionAvatarPath, '/'))) {
        $topbarUserAvatar = BASE_URL . '/' . ltrim($sessionAvatarPath, '/');
    }
}
$pageTitleForTopbar = $data['title'] ?? 'Update My Profile';
$welcomeMessageForTopbar = 'Manage your personal information and security settings.';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitleForTopbar); ?> - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f0f2f5; 
            color: #343a40; 
            margin: 0;
            padding: 0;
            display: flex; /* Thêm để sidebar và main content nằm ngang */
            min-height: 100vh;
        }

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

        .main-content-area-cutie {
            flex-grow: 1;
            margin-left: 260px; 
            background-color: #f0f2f5; 
            overflow-y: auto;
        }
        
        .topbar-shared-cutie {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px 30px; 
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
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

        .actual-page-content-wrapper-cutie {
             padding: 30px;
        }

        .profile-container-cutie {
            background-color: #ffffff; 
            padding: 30px 40px; 
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
            width: 100%; 
            max-width: 850px; 
            margin: 0 auto; 
        }
        .profile-container-cutie h2.page-title-cutie { 
            color: #0a783c; 
            text-align: center;
            margin-bottom: 30px; 
            font-weight: 600;
            font-size: 2em; 
        }
        .message-feedback { 
            padding: 15px 20px; margin-bottom: 25px; border-radius: 8px; 
            font-size: 15px; text-align: center; font-weight: 500;
            border-left-width: 5px; border-left-style: solid;
        }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error-list { 
            background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444;
            text-align: left;
        }
        .message-feedback.error-list ul { list-style-position: inside; padding-left: 5px; margin-top: 5px;}
        .message-feedback.error-list strong { display: block; margin-bottom: 5px;}
        .error-text { 
            color: #d63031; font-size: 0.875em;
            margin-top: 4px; display: block;
        }
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
            body { flex-direction: column; }
            .sidebar-container-cutie { width: 100%; height: auto; position: static; padding: 15px 0; box-shadow: none; }
            .main-content-area-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h2 { font-size: 20px; }
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-wrapper-cutie { padding: 20px 15px; }
            .profile-container-cutie { padding: 20px; }
            .profile-container-cutie h2.page-title-cutie { font-size: 1.6em; margin-bottom: 20px; }
            .avatar-preview-cutie { width: 120px; height: 120px; }
            .form-actions-cutie { text-align: center; }
            .button-submit-profile-cutie, .button-back-profile-cutie { display: block; width: 100%; margin: 10px 0 0 0; }
        }
    </style>
</head>
<body>
    <aside class="sidebar-container-cutie">
        <div class="sidebar-header-cutie">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo-cutie">Admin Panel</a>
        </div>
<nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="<?php echo (strpos($currentUrl, 'admin/dashboard') !== false && strpos($currentUrl, 'admin/dashboard') === (strlen($currentUrl) - strlen('admin/dashboard'))) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listUsers" class="<?php echo (strpos($currentUrl, 'admin/listUsers') !== false || strpos($currentUrl, 'admin/createUser') !== false || strpos($currentUrl, 'admin/editUser') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👥</span>Manage Users</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageSpecializations" class="<?php echo (strpos($currentUrl, 'admin/manageSpecializations') !== false || strpos($currentUrl, 'admin/editSpecialization') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏷️</span>Specializations</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listMedicines" class="<?php echo (strpos($currentUrl, 'admin/listMedicines') !== false || strpos($currentUrl, 'admin/createMedicine') !== false || strpos($currentUrl, 'admin/editMedicine') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">💊</span>Manage Medicines</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listAllAppointments" class="<?php echo (strpos($currentUrl, 'admin/listAllAppointments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>All Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/report/overview" class="<?php echo (strpos($currentUrl, 'report/overview') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📊</span>Reports</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageLeaveRequests" class="<?php echo (strpos($currentUrl, 'admin/manageLeaveRequests') !== false || strpos($currentUrl, 'admin/reviewLeaveRequest') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">✈️</span>Leave Requests</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageFeedbacks" class="<?php echo (strpos($currentUrl, 'admin/manageFeedbacks') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⭐</span>Patient Feedbacks</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageDoctorNurseAssignments" class="<?php echo (strpos($currentUrl, 'admin/manageDoctorNurseAssignments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🔗</span>Doctor-Nurse Assign</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/updateProfile" class="<?php echo (strpos($currentUrl, 'admin/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>My Profile</a></li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="logout-link-cutie" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                        <span class="nav-icon-cutie">🚪</span>Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content-area-cutie">
        <header class="topbar-shared-cutie">
            <div class="topbar-title-section-cutie">
                <h2><?php echo htmlspecialchars($pageTitleForTopbar); ?></h2>
                <p><?php echo htmlspecialchars($welcomeMessageForTopbar); ?></p>
            </div>
            <div class="topbar-user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications" onclick="alert('Notifications (coming soon!)');">🔔</button>
                <div class="user-profile-toggle-cutie" id="userProfileToggle">
                    <img src="<?php echo htmlspecialchars($topbarUserAvatar); ?>" alt="User Avatar">
                    <span><?php echo htmlspecialchars($topbarUserFullName); ?> ▼</span>
                    <div class="user-profile-dropdown-content-cutie" id="userProfileDropdown">
                        <a href="<?php echo BASE_URL; ?>/admin/updateProfile">My Profile</a>
                        <a href="<?php echo BASE_URL; ?>/auth/logout">Logout</a>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="actual-page-content-wrapper-cutie">
            <div class="profile-container-cutie">
                <h2 class="page-title-cutie"><?php echo htmlspecialchars($data['title']); ?></h2>

                <?php if (isset($_SESSION['profile_message_success'])): ?>
                    <div class="message-feedback success">
                        <?php echo htmlspecialchars($_SESSION['profile_message_success']); unset($_SESSION['profile_message_success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php 
                $allErrors = $data['errors'] ?? [];
                if (isset($data['profile_message_error'])) $allErrors['general_profile'] = $data['profile_message_error'];
                if (isset($_SESSION['profile_message_error'])) {
                    $allErrors['session_profile'] = $_SESSION['profile_message_error'];
                    unset($_SESSION['profile_message_error']);
                }
                ?>

                <?php if (!empty($allErrors)): ?>
                    <div class="message-feedback error-list">
                        <strong>Please correct the following errors, sweetie:</strong>
                        <ul>
                            <?php foreach ($allErrors as $errorField => $errorMsg): ?>
                                <li><?php echo htmlspecialchars($errorMsg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo BASE_URL; ?>/admin/updateProfile" method="POST" enctype="multipart/form-data" class="profile-form-cutie">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                    <div class="avatar-upload-section-cutie">
                        <?php
                        $avatarSrc = BASE_URL . '/public/assets/images/default_avatar.png'; 
                        if (!empty($data['user']['Avatar'])) {
                            $userAvatarPath = $data['user']['Avatar'];
                            if (file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($userAvatarPath, '/'))) {
                                $avatarSrc = BASE_URL . '/' . ltrim(htmlspecialchars($userAvatarPath), '/');
                            }
                        }
                        ?>
                        <img id="avatarPreview" src="<?php echo $avatarSrc; ?>" alt="Profile Avatar" class="avatar-preview-cutie">
                        <br>
                        <label for="profile_avatar_input" class="button-change-avatar-cutie">
                            Change Profile Picture
                        </label>
                        <input type="file" name="profile_avatar" id="profile_avatar_input" style="display: none;" accept="image/png, image/jpeg, image/gif">
                        <?php if (isset($data['errors']['profile_avatar'])): ?><span class="error-text"><?php echo htmlspecialchars($data['errors']['profile_avatar']); ?></span><?php endif; ?>
                    </div>

                    <fieldset>
                        <legend>Account Information</legend>
                        <div class="form-group-cutie">
                            <label for="FullName">Full Name: *</label>
                            <input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($data['input']['FullName'] ?? ''); ?>" required>
                            <?php if (isset($data['errors']['FullName'])): ?><span class="error-text"><?php echo htmlspecialchars($data['errors']['FullName']); ?></span><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="Username">Username:</label>
                            <input type="text" id="Username" name="Username" value="<?php echo htmlspecialchars($data['input']['Username'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-group-cutie">
                            <label for="Email">Email: *</label>
                            <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($data['input']['Email'] ?? ''); ?>" required>
                            <?php if (isset($data['errors']['Email'])): ?><span class="error-text"><?php echo htmlspecialchars($data['errors']['Email']); ?></span><?php endif; ?>
                        </div>
                         <div class="form-group-cutie">
                            <label for="PhoneNumber">Phone Number:</label>
                            <input type="text" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($data['input']['PhoneNumber'] ?? ''); ?>">
                            <?php if (isset($data['errors']['PhoneNumber'])): ?><span class="error-text"><?php echo htmlspecialchars($data['errors']['PhoneNumber']); ?></span><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="Address">Address:</label>
                            <textarea name="Address" id="Address" rows="3"><?php echo htmlspecialchars($data['input']['Address'] ?? ''); ?></textarea>
                            <?php if (isset($data['errors']['Address'])): ?><span class="error-text"><?php echo htmlspecialchars($data['errors']['Address']); ?></span><?php endif; ?>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Change Password (leave blank if not changing)</legend>
                         <div class="form-group-cutie">
                            <label for="current_password">Current Password:</label>
                            <input type="password" id="current_password" name="current_password">
                            <?php if (isset($data['errors']['current_password'])): ?><span class="error-text"><?php echo htmlspecialchars($data['errors']['current_password']); ?></span><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="new_password">New Password (min. 6 characters):</label>
                            <input type="password" id="new_password" name="new_password">
                            <?php if (isset($data['errors']['new_password'])): ?><span class="error-text"><?php echo htmlspecialchars($data['errors']['new_password']); ?></span><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="confirm_new_password">Confirm New Password:</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password">
                            <?php if (isset($data['errors']['confirm_new_password'])): ?><span class="error-text"><?php echo htmlspecialchars($data['errors']['confirm_new_password']); ?></span><?php endif; ?>
                        </div>
                    </fieldset>

                    <div class="form-actions-cutie">
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="button-back-profile-cutie">Back to Dashboard</a>
                        <button type="submit" class="button-submit-profile-cutie">Update Admin Profile</button>
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