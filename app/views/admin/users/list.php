<?php
// app/views/admin/users/list.php

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

$pageTitleForTopbar = $data['title'] ?? 'Manage Users';
$welcomeMessageForTopbar = 'View, filter, and manage all user accounts in the system.';
$currentUrl = $_GET['url'] ?? 'admin/listUsers';

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

        .controls-toolbar-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 15px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); flex-wrap: wrap; gap:15px;}
        .filters-area-cutie { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .filter-group-admin-cutie label { font-size: 14px; color: #495057; margin-right: 5px; }
        .filter-group-admin-cutie select, .filter-group-admin-cutie input[type="text"] {
            padding: 8px 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; min-width: 150px;
        }
        .btn-admin-action-cutie { padding: 9px 18px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; transition: background-color 0.2s ease; }
        .btn-filter-users-cutie { background-color: #0a783c; color: white; }
        .btn-filter-users-cutie:hover { background-color: #086330; }
        .btn-add-user-cutie { background-color: #10ac84; color: white; }
        .btn-add-user-cutie:hover { background-color: #0e8c6d; }

        .users-table-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; }
        .users-table-cutie { width: 100%; border-collapse: collapse; }
        .users-table-cutie th, .users-table-cutie td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ecf0f1; font-size: 14px; vertical-align: middle;}
        .users-table-cutie th { background-color: #f7f9f9; font-weight: 600; color: #34495e; white-space: nowrap; }
        .users-table-cutie tbody tr:hover { background-color: #fdfdfe; }
        .status-badge-admin-cutie { padding: 4px 10px; border-radius: 15px; font-size: 12px; font-weight: 500; color: white; display: inline-block; text-align: center; min-width: 70px;}
        .status-active { background-color: #2ecc71; }
        .status-inactive { background-color: #e74c3c; }
        .status-pending { background-color: #f39c12; }
        .action-buttons-admin-cutie a, .action-buttons-admin-cutie button {
            padding: 6px 10px; font-size: 13px; border-radius: 5px; text-decoration: none;
            border: none; cursor: pointer; transition: opacity 0.2s ease; margin-right: 5px; margin-bottom: 5px; display: inline-block;
        }
        .action-buttons-admin-cutie a:hover, .action-buttons-admin-cutie button:hover { opacity: 0.8; }
        .btn-edit-user-cutie { background-color: #3498db; color: white; }
        .btn-deactivate-user-cutie { background-color: #f39c12; color: white; }
        .btn-activate-user-cutie { background-color: #2ecc71; color: white; }
        .btn-delete-user-cutie { background-color: #e74c3c; color: white; }
        
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .error-message { background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
        .no-users-msg-cutie { text-align: center; padding: 40px 20px; color: #7f8c8d; font-style: italic; }

        @media (max-width: 768px) { 
            body { flex-direction: column; }
            .dashboard-sidebar-cutie { width: 100%; height: auto; position: static; box-shadow: none; }
            .dashboard-main-content-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h2 { font-size: 20px; }
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-cutie { padding: 20px 15px; }
            .controls-toolbar-cutie { flex-direction: column; align-items: stretch; }
            .filters-area-cutie { flex-direction: column; align-items: stretch; }
            .filter-group-admin-cutie select, .filter-group-admin-cutie input[type="text"] { width: 100%; }
            .btn-add-user-cutie { width: 100%; margin-top: 10px; }
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
            <?php if (isset($_SESSION['user_management_message_success'])): ?>
                <p class="message-cutie success-message"><?php echo $_SESSION['user_management_message_success']; unset($_SESSION['user_management_message_success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_management_message_error'])): ?>
                <p class="message-cutie error-message"><?php echo $_SESSION['user_management_message_error']; unset($_SESSION['user_management_message_error']); ?></p>
            <?php endif; ?>

            <div class="controls-toolbar-cutie">
                <form method="GET" action="<?php echo BASE_URL; ?>/admin/listUsers" class="filters-area-cutie">
                    <div class="filter-group-admin-cutie">
                        <label for="role_filter">Role:</label>
                        <select name="role" id="role_filter" onchange="this.form.submit()">
                            <option value="All" <?php echo (($data['currentRoleFilter'] ?? 'All') == 'All') ? 'selected' : ''; ?>>All Roles</option>
                            <?php $roles = $data['allRoles'] ?? ['Admin', 'Doctor', 'Nurse', 'Patient']; ?>
                            <?php foreach ($roles as $roleValue): 
                                if ($roleValue === 'All') continue; // Skip 'All' as it's handled above
                            ?>
                                <option value="<?php echo $roleValue; ?>" <?php echo (($data['currentRoleFilter'] ?? '') == $roleValue) ? 'selected' : ''; ?>><?php echo $roleValue; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group-admin-cutie">
                        <label for="status_filter">Status:</label>
                        <select name="status" id="status_filter" onchange="this.form.submit()">
                            <?php $statuses = $data['allStatuses'] ?? ['All', 'Active', 'Inactive', 'Pending', 'Active_and_Pending']; ?>
                            <?php foreach ($statuses as $statusKey => $statusValue): 
                                $actualStatusValue = is_string($statusKey) ? $statusKey : $statusValue; 
                                $displayStatus = str_replace('_and_', ' & ', $statusValue);
                            ?>
                                <option value="<?php echo htmlspecialchars($actualStatusValue); ?>" <?php echo (($data['currentStatusFilter'] ?? 'All') == $actualStatusValue) ? 'selected' : ''; ?>><?php echo htmlspecialchars($displayStatus); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group-admin-cutie">
                        <label for="search_term">Search:</label>
                        <input type="text" name="search" id="search_term" value="<?php echo htmlspecialchars($data['currentSearchTerm'] ?? ''); ?>" placeholder="Name, Email...">
                    </div>
                    <button type="submit" class="btn-admin-action-cutie btn-filter-users-cutie">Filter</button>
                </form>
                <a href="<?php echo BASE_URL; ?>/admin/createUser" class="btn-admin-action-cutie btn-add-user-cutie">+ Add New User</a>
            </div>

            <div class="users-table-container-cutie">
                <?php if (!empty($data['users'])): ?>
                    <table class="users-table-cutie">
                        <thead>
                            <tr><th>#</th><th>Full Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Registered</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php $stt = 1; ?>
                            <?php foreach ($data['users'] as $user): ?>
                                <tr>
                                    <td><?php echo $stt++; ?></td>
                                    <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['Role']); ?></td>
                                    <td><span class="status-badge-admin-cutie status-<?php echo strtolower(htmlspecialchars($user['Status'])); ?>"><?php echo htmlspecialchars($user['Status']); ?></span></td>
                                    <td><?php echo htmlspecialchars(date('M j, Y', strtotime($user['CreatedAt']))); ?></td>
                                    <td class="action-buttons-admin-cutie">
                                        <a href="<?php echo BASE_URL . '/admin/editUser/' . $user['UserID']; ?>" class="btn-edit-user-cutie">Edit</a>
                                        <?php if ($user['UserID'] != $_SESSION['user_id']): ?>
                                            <?php if ($user['Status'] === 'Active'): ?>
                                                <form action="<?php echo BASE_URL; ?>/admin/updateUserStatus" method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                                    <input type="hidden" name="new_status" value="Inactive">
                                                    <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                                                    <button type="submit" class="btn-deactivate-user-cutie" onclick="return confirm('Deactivate this user?');">Deactivate</button>
                                                </form>
                                            <?php elseif (in_array($user['Status'], ['Inactive', 'Pending'])): ?>
                                                <form action="<?php echo BASE_URL; ?>/admin/updateUserStatus" method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                                    <input type="hidden" name="new_status" value="Active">
                                                    <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                                                    <button type="submit" class="btn-activate-user-cutie" onclick="return confirm('Activate this user?');">Activate</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($user['Status'] !== 'Archived'): ?>
                                                <form action="<?php echo BASE_URL; ?>/admin/deleteUser" method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id_to_delete" value="<?php echo $user['UserID']; ?>">
                                                    <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                                                    <button type="submit" class="btn-delete-user-cutie" onclick="return confirm('SOFT DELETE this user (mark as Inactive)? This action is generally reversible.');">Delete</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-users-msg-cutie">No users found matching your criteria. Try adjusting the filters or add a new user!</p>
                <?php endif; ?>
            </div>
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