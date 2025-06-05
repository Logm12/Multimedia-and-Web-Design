<?php
// app/views/admin/manage_specializations.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Admin';
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_admin_avatar.png';
// $data = $data ?? [ /* ... existing dummy data ... */ ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Manage Specializations'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }
        .dashboard-sidebar-cutie { width: 260px; background: linear-gradient(90deg, rgba(10,57,32,1) 0%, rgba(13,142,100,1) 90%); color: #ecf0f1; padding: 25px 0; display: flex; flex-direction: column; }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a { display: flex; align-items: center; padding: 15px 25px; color: #dfe6e9; text-decoration: none; font-size: 15px; font-weight: 500; border-left: 4px solid transparent; transition: all 0.2s ease; }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie { background-color: rgba(255,255,255,0.15); color: #fff; border-left-color: #55efc4; }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #bdc3c7; }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        .main-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .page-title-header-cutie { display: flex; justify-content: space-between; align-items: center; width: 100%;}
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; color: #2c3e50; }
        .page-actions-cutie { display: flex; gap: 10px; }
        .btn-admin-action, .btn-admin-secondary { padding: 9px 18px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; transition: background-color 0.2s ease; }
        .btn-add-new-cutie { background-color: #2ecc71; color: white; }
        .btn-add-new-cutie:hover { background-color: #27ae60; }
        
        .user-actions-cutie { display: flex; align-items: center; gap: 20px; margin-left: auto; }
        .user-actions-cutie .icon-button-cutie { background: none; border: none; font-size: 22px; color: #7f8c8d; cursor: pointer; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #0a3920; }

        .content-table-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; padding: 25px; }
        .content-table-cutie { width: 100%; border-collapse: collapse; }
        .content-table-cutie th, .content-table-cutie td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ecf0f1; font-size: 14px; }
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
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .no-items-msg-cutie { text-align: center; padding: 30px; color: #7f8c8d; font-style: italic; }
        .back-to-dash-link-admin-cutie { display: inline-block; margin-top: 25px; padding: 10px 18px; background-color: #7f8c8d; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
        .back-to-dash-link-admin-cutie:hover { background-color: #6c757d; }

        @media (max-width: 768px) { /* Sidebar responsive */ }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">HealthSys</a></div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/dashboard') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listUsers" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/listUsers') !== false || strpos($_GET['url'] ?? '', 'admin/createUser') !== false || strpos($_GET['url'] ?? '', 'admin/editUser') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👥</span>Manage Users</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageSpecializations" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/manageSpecializations') !== false || strpos($_GET['url'] ?? '', 'admin/editSpecialization') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏷️</span>Specializations</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listMedicines" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/listMedicines') !== false || strpos($_GET['url'] ?? '', 'admin/createMedicine') !== false || strpos($_GET['url'] ?? '', 'admin/editMedicine') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">💊</span>Manage Medicines</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listAllAppointments" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/listAllAppointments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>All Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/report/overview" class="<?php echo (strpos($_GET['url'] ?? '', 'report/overview') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📊</span>Reports</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageLeaveRequests" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/manageLeaveRequests') !== false || strpos($_GET['url'] ?? '', 'admin/reviewLeaveRequest') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">✈️</span>Leave Requests</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageFeedbacks" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/manageFeedbacks') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⭐</span>Patient Feedbacks</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/updateProfile" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>My Profile</a></li>
                <!-- Thêm các mục khác cho Admin nếu cần, ví dụ: System Settings -->
                <!-- <li><a href="<?php echo BASE_URL; ?>/admin/systemSettings" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/systemSettings') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⚙️</span>System Settings</a></li> -->
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-header-cutie">
                 <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Manage Specializations'); ?></h2></div>
                 <div class="page-actions-cutie">
                    <a href="<?php echo BASE_URL; ?>/admin/editSpecialization" class="btn-admin-action btn-add-new-cutie">+ Add New Specialization</a>
                 </div>
            </div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Admin Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

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
                                <form action="<?php echo BASE_URL . '/admin/deleteSpecialization'; ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this specialization?');">
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
    </main>
</body>
</html>