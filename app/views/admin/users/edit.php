<?php
// app/views/admin/users/edit.php

if (session_status() == PHP_SESSION_NONE) session_start();
if (!defined('BASE_URL')) { 
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../../../../public'));
}

$topbarUserFullName = $_SESSION['user_fullname'] ?? 'Admin';
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_admin_avatar.png'; 
if (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar'])) {
    $sessionAvatarPath = $_SESSION['user_avatar'];
    if (file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($sessionAvatarPath, '/'))) {
        $topbarUserAvatar = BASE_URL . '/' . ltrim($sessionAvatarPath, '/');
    }
}

$pageTitleForTopbar = $data['title'] ?? 'Edit User';
$welcomeMessageForTopbar = 'Modify the user details and settings below.';
$currentUrl = $_GET['url'] ?? 'admin/editUser';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitleForTopbar); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }
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
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-nav-cutie ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav-cutie li a { 
            display: flex; align-items: center; padding: 15px 25px; 
            color: #dfe6e9; text-decoration: none; font-size: 15px; 
            font-weight: 500; border-left: 4px solid transparent; 
            transition: all 0.2s ease; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: rgba(255, 255, 255, 0.15); color: #fff; 
            border-left-color: #55efc4; 
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #bdc3c7; }

        .dashboard-main-content-cutie { 
            flex: 1; 
            margin-left: 260px; 
            overflow-y: auto; 
            background-color: #f0f2f5;
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

        .actual-page-content-cutie {
            padding: 30px;
        }

        .form-container-admin-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; max-width: 800px; margin: 0 auto; }
        .form-container-admin-cutie fieldset { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .form-container-admin-cutie legend { font-size: 18px; font-weight: 600; color: #0a783c; padding: 0 10px; margin-left: 10px; }
        .form-grid-admin-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px 20px; }
        .form-group-admin-cutie { margin-bottom: 10px; }
        .form-group-admin-cutie label { display: block; font-size: 14px; color: #495057; margin-bottom: 6px; font-weight: 500; }
        .form-group-admin-cutie input[type="text"], .form-group-admin-cutie input[type="email"], .form-group-admin-cutie input[type="password"], .form-group-admin-cutie input[type="number"], .form-group-admin-cutie select, .form-group-admin-cutie textarea {
            width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-group-admin-cutie input[readonly] { background-color: #e9ecef; cursor: not-allowed; }
        .form-group-admin-cutie textarea { min-height: 70px; resize: vertical; }
        .form-group-admin-cutie input:focus, .form-group-admin-cutie select:focus, .form-group-admin-cutie textarea:focus { 
            border-color: #10ac84; box-shadow: 0 0 0 0.2rem rgba(16, 172, 132, 0.25); outline: none; 
        }
        
        .form-actions-admin-cutie { margin-top: 25px; display: flex; gap: 15px; justify-content: flex-end; }
        .btn-admin-action, .btn-admin-secondary { padding: 10px 20px; border: none; border-radius: 6px; font-size: 15px; font-weight: 500; cursor: pointer; text-decoration: none; transition: background-color 0.2s ease; }
        .btn-admin-action { background-color: #0a783c; color: white; } .btn-admin-action:hover { background-color: #086330; }
        .btn-admin-secondary { background-color: #6c757d; color: white; } .btn-admin-secondary:hover { background-color: #5a6268; }
        
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .error-message-container { margin-bottom: 15px; padding:10px; border:1px solid #ef4444; background-color:#fee2e2; border-radius: 6px; color: #991b1b;}
        .error-message-container ul { list-style-position: inside; padding-left: 5px; margin-top: 5px;}
        .error-message-container strong { display: block; margin-bottom: 5px;}

        @media (max-width: 768px) { 
            body { flex-direction: column; }
            .dashboard-sidebar-cutie { width: 100%; height: auto; position: static; box-shadow: none; }
            .dashboard-main-content-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h2 { font-size: 20px; }
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-cutie { padding: 20px 15px; }
            .form-container-admin-cutie { padding: 20px; }
            .form-actions-admin-cutie { flex-direction: column; }
            .btn-admin-action, .btn-admin-secondary { width: 100%; margin-bottom: 10px; }
        }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo-cutie">HealthSys</a></div>
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
                    <img src="<?php echo htmlspecialchars($topbarUserAvatar); ?>" alt="Admin Avatar">
                    <span><?php echo htmlspecialchars($topbarUserFullName); ?> ▼</span>
                    <div class="user-profile-dropdown-content-cutie" id="userProfileDropdown">
                        <a href="<?php echo BASE_URL; ?>/admin/updateProfile">My Profile</a>
                        <a href="<?php echo BASE_URL; ?>/auth/logout">Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="actual-page-content-cutie">
            <?php if (!empty($data['errors'])): ?>
                <div class="error-message-container"><strong>Please correct the following errors, sweetie:</strong><ul>
                    <?php foreach ($data['errors'] as $error): ?><li><?php echo htmlspecialchars($error); ?></li><?php endforeach; ?>
                </ul></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_management_message_success'])): ?>
                <p class="message-cutie success-message"><?php echo $_SESSION['user_management_message_success']; unset($_SESSION['user_management_message_success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_management_message_error'])): ?>
                <p class="message-cutie error-message-container"><?php echo $_SESSION['user_management_message_error']; unset($_SESSION['user_management_message_error']); ?></p>
            <?php endif; ?>

            <div class="form-container-admin-cutie">
                <form action="<?php echo BASE_URL . '/admin/editUser/' . ($data['userId'] ?? ''); ?>" method="POST">
                    <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                    <input type="hidden" name="userId" value="<?php echo htmlspecialchars($data['userId'] ?? ''); ?>">
                    <fieldset>
                        <legend>Account Information</legend>
                        <div class="form-grid-admin-cutie">
                            <div class="form-group-admin-cutie"><label for="FullName">Full Name: *</label><input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($data['input']['FullName'] ?? ''); ?>" required></div>
                            <div class="form-group-admin-cutie"><label for="Username">Username: (Cannot change)</label><input type="text" id="Username" name="Username" value="<?php echo htmlspecialchars($data['input']['Username'] ?? ''); ?>" readonly></div>
                            <div class="form-group-admin-cutie"><label for="Email">Email: *</label><input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($data['input']['Email'] ?? ''); ?>" required></div>
                            <div class="form-group-admin-cutie"><label for="PhoneNumber">Phone Number:</label><input type="text" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($data['input']['PhoneNumber'] ?? ''); ?>"></div>
                            <div class="form-group-admin-cutie"><label for="Role">Role: *</label><select id="Role" name="Role" required onchange="toggleDoctorFieldsEdit()"><?php foreach (($data['roles'] ?? []) as $roleOption): ?><option value="<?php echo $roleOption; ?>" <?php echo (($data['input']['Role'] ?? '') == $roleOption) ? 'selected' : ''; ?>><?php echo $roleOption; ?></option><?php endforeach; ?></select></div>
                            <div class="form-group-admin-cutie"><label for="Status">Status: *</label><select id="Status" name="Status" required><?php foreach (($data['statuses'] ?? []) as $statusOption): ?><option value="<?php echo $statusOption; ?>" <?php echo (($data['input']['Status'] ?? '') == $statusOption) ? 'selected' : ''; ?>><?php echo $statusOption; ?></option><?php endforeach; ?></select></div>
                            <div class="form-group-admin-cutie" style="grid-column: 1 / -1;"><label for="Address">Address:</label><textarea name="Address" id="Address" rows="2"><?php echo htmlspecialchars($data['input']['Address'] ?? ''); ?></textarea></div>
                        </div>
                    </fieldset>
                    <fieldset id="doctorFieldsEdit" style="display: <?php echo (($data['input']['Role'] ?? '') == 'Doctor') ? 'block' : 'none'; ?>;">
                        <legend>Doctor Specific Information</legend>
                        <div class="form-grid-admin-cutie">
                            <div class="form-group-admin-cutie"><label for="SpecializationIDEdit">Specialization:</label><select id="SpecializationIDEdit" name="SpecializationID"><option value="">-- Select --</option><?php if (!empty($data['specializations'])) foreach ($data['specializations'] as $spec): ?><option value="<?php echo $spec['SpecializationID']; ?>" <?php echo (($data['input']['SpecializationID'] ?? '') == $spec['SpecializationID']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($spec['Name']); ?></option><?php endforeach; ?></select></div>
                            <div class="form-group-admin-cutie"><label for="ExperienceYearsEdit">Years of Experience:</label><input type="number" id="ExperienceYearsEdit" name="ExperienceYears" value="<?php echo htmlspecialchars($data['input']['ExperienceYears'] ?? '0'); ?>" min="0"></div>
                            <div class="form-group-admin-cutie"><label for="ConsultationFeeEdit">Consultation Fee:</label><input type="number" id="ConsultationFeeEdit" name="ConsultationFee" value="<?php echo htmlspecialchars($data['input']['ConsultationFee'] ?? '0.00'); ?>" step="0.01" min="0"></div>
                            <div class="form-group-admin-cutie" style="grid-column: 1 / -1;"><label for="BioEdit">Bio:</label><textarea name="Bio" id="BioEdit" rows="3"><?php echo htmlspecialchars($data['input']['Bio'] ?? ''); ?></textarea></div>
                        </div>
                    </fieldset>
                    <fieldset><legend>Change Password (Optional)</legend>
                        <div class="form-grid-admin-cutie">
                            <div class="form-group-admin-cutie"><label for="NewPassword">New Password (min. 6 chars):</label><input type="password" id="NewPassword" name="NewPassword"></div>
                            <div class="form-group-admin-cutie"><label for="ConfirmNewPassword">Confirm New Password:</label><input type="password" id="ConfirmNewPassword" name="ConfirmNewPassword"></div>
                        </div>
                    </fieldset>
                    <div class="form-actions-admin-cutie">
                        <a href="<?php echo BASE_URL; ?>/admin/listUsers" class="btn-admin-secondary">Cancel</a>
                        <button type="submit" class="btn-admin-action">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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

        function toggleDoctorFieldsEdit() {
            const roleSelect = document.getElementById('Role');
            const doctorFields = document.getElementById('doctorFieldsEdit');
            if (roleSelect && doctorFields) { 
                doctorFields.style.display = (roleSelect.value === 'Doctor') ? 'block' : 'none';
            }
        }
        const roleSelectForEdit = document.getElementById('Role');
        if(roleSelectForEdit) {
            toggleDoctorFieldsEdit(); 
            roleSelectForEdit.addEventListener('change', toggleDoctorFieldsEdit);
        }
    });
</script>
</body>
</html>