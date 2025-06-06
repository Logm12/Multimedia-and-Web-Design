<?php
// app/views/admin/manage_specializations.php

if (session_status() == PHP_SESSION_NONE) session_start();
if (!defined('BASE_URL')) { 
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../../../public'));
}

$topbarUserFullName = $_SESSION['user_fullname'] ?? 'Admin';
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_admin_avatar.png'; 
if (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar'])) {
    $sessionAvatarPath = $_SESSION['user_avatar'];
    if (file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($sessionAvatarPath, '/'))) {
        $topbarUserAvatar = BASE_URL . '/' . ltrim($sessionAvatarPath, '/');
    }
}

$pageTitleForTopbar = $data['title'] ?? 'Manage Specializations';
$welcomeMessageForTopbar = 'Oversee all medical specializations within the system.';
$currentUrl = $_GET['url'] ?? 'admin/manageSpecializations';

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
        .sidebar-nav-cutie ul { list-style: none; padding:0; margin:0; }
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
        .topbar-title-section-cutie { flex-grow: 1; }
        .topbar-title-section-cutie h2 { font-size: 22px; font-weight: 600; color: #2c3e50; margin:0; }
        .topbar-title-section-cutie p { font-size: 14px; color: #7f8c8d; margin-top: 4px; }
        .topbar-actions-button-cutie { margin-left: 20px; }
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
        
        .btn-admin-action, .btn-admin-secondary { padding: 9px 18px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; transition: background-color 0.2s ease; }
        .btn-add-new-cutie { background-color: #0a783c; color: white; }
        .btn-add-new-cutie:hover { background-color: #086330; }
        
        .content-table-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; padding: 25px; margin-top: 20px;}
        .content-table-cutie { width: 100%; border-collapse: collapse; }
        .content-table-cutie th, .content-table-cutie td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ecf0f1; font-size: 14px; vertical-align: middle;}
        .content-table-cutie th { background-color: #f7f9f9; font-weight: 600; color: #34495e; white-space: nowrap; }
        .content-table-cutie tbody tr:hover { background-color: #fdfdfe; }
        .action-buttons-admin-table-cutie a, .action-buttons-admin-table-cutie button {
            padding: 6px 10px; font-size: 13px; border-radius: 5px; text-decoration: none;
            border: none; cursor: pointer; transition: opacity 0.2s ease; margin-right: 6px; display: inline-block;
        }
        .action-buttons-admin-table-cutie a:hover, .action-buttons-admin-table-cutie button:hover { opacity: 0.8; }
        .btn-edit-item-cutie { background-color: #f39c12; color: white; }
        .btn-delete-item-cutie { background-color: #e74c3c; color: white; }
        
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .error-message { background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
        .no-items-msg-cutie { text-align: center; padding: 30px; color: #7f8c8d; font-style: italic; }
        .back-to-dash-link-admin-cutie { display: inline-block; margin-top: 25px; padding: 10px 18px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
        .back-to-dash-link-admin-cutie:hover { background-color: #5a6268; }

        @media (max-width: 768px) { 
            body { flex-direction: column; }
            .dashboard-sidebar-cutie { width: 100%; height: auto; position: static; box-shadow: none; }
            .dashboard-main-content-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h2 { font-size: 20px; }
            .topbar-actions-button-cutie { width: 100%; text-align: right; margin-left: 0; margin-top:10px;}
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-cutie { padding: 20px 15px; }
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
            <div class="topbar-actions-button-cutie">
                <a href="<?php echo BASE_URL; ?>/admin/editSpecialization" class="btn-admin-action btn-add-new-cutie">+ Add New Specialization</a>
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
            <?php if (isset($_SESSION['admin_message_success'])): ?>
                <p class="message-cutie success-message"><?php echo $_SESSION['admin_message_success']; unset($_SESSION['admin_message_success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['admin_message_error'])): ?>
                <p class="message-cutie error-message"><?php echo $_SESSION['admin_message_error']; unset($_SESSION['admin_message_error']); ?></p>
            <?php endif; ?>

            <div class="content-table-container-cutie">
                <?php if (!empty($data['specializations'])): ?>
                    <table class="content-table-cutie">
                        <thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($data['specializations'] as $spec): ?>
                            <tr>
                                <td><?php echo $spec['SpecializationID']; ?></td>
                                <td><?php echo htmlspecialchars($spec['Name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($spec['Description'] ?? '', 0, 100) . (strlen($spec['Description'] ?? '') > 100 ? '...' : '')); ?></td>
                                <td class="action-buttons-admin-table-cutie">
                                    <a href="<?php echo BASE_URL . '/admin/editSpecialization/' . $spec['SpecializationID']; ?>" class="btn-edit-item-cutie">Edit</a>
                                    <form action="<?php echo BASE_URL . '/admin/deleteSpecialization'; ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this specialization? This might affect doctors associated with it.');">
                                        <input type="hidden" name="id_to_delete" value="<?php echo $spec['SpecializationID']; ?>">
                                        <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                                        <button type="submit" class="btn-delete-item-cutie">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-items-msg-cutie">No specializations found. Time to add some, perhaps? ✨</p>
                <?php endif; ?>
            </div>
            <p style="text-align: center; margin-top:25px;"><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="back-to-dash-link-admin-cutie">« Back to Admin Dashboard</a></p>
        </div>
    </main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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