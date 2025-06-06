<?php
// app/views/admin/feedbacks/manage.php

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

// Data for Topbar and Sidebar
$topbarUserFullName = $data['currentUser']['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Admin');
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_admin_avatar.png'; 
if (isset($data['currentUser']['Avatar']) && !empty($data['currentUser']['Avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($data['currentUser']['Avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($data['currentUser']['Avatar'], '/');
} elseif (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($_SESSION['user_avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($_SESSION['user_avatar'], '/');
}

$pageTitleForTopbar = $data['title'] ?? 'Manage Patient Feedbacks';
$welcomeMessageForTopbar = 'Review and manage feedbacks submitted by patients.';
$currentUrl = $_GET['url'] ?? 'admin/manageFeedbacks';

// Admin Sidebar Menu
$adminSidebarMenu = [
    ['url' => BASE_URL . '/admin/dashboard', 'icon' => '🏠', 'text' => 'Dashboard', 'active_keys' => ['admin/dashboard', 'admin']],
    ['url' => BASE_URL . '/admin/listUsers', 'icon' => '👥', 'text' => 'Manage Users', 'active_keys' => ['admin/listUsers', 'admin/createUser', 'admin/editUser']],
    ['url' => BASE_URL . '/admin/manageSpecializations', 'icon' => '🏷️', 'text' => 'Specializations', 'active_keys' => ['admin/manageSpecializations', 'admin/editSpecialization']],
    ['url' => BASE_URL . '/admin/listMedicines', 'icon' => '💊', 'text' => 'Manage Medicines', 'active_keys' => ['admin/listMedicines', 'admin/createMedicine', 'admin/editMedicine']],
    ['url' => BASE_URL . '/admin/listAllAppointments', 'icon' => '🗓️', 'text' => 'All Appointments', 'active_keys' => ['admin/listAllAppointments']],
    ['url' => BASE_URL . '/report/overview', 'icon' => '📊', 'text' => 'Reports', 'active_keys' => ['report/overview']],
    ['url' => BASE_URL . '/admin/manageLeaveRequests', 'icon' => '✈️', 'text' => 'Leave Requests', 'active_keys' => ['admin/manageLeaveRequests', 'admin/reviewLeaveRequest']],
    ['url' => BASE_URL . '/admin/manageFeedbacks', 'icon' => '⭐', 'text' => 'Patient Feedbacks', 'active_keys' => ['admin/manageFeedbacks']],
    ['url' => BASE_URL . '/admin/databaseManagement', 'icon' => '💾', 'text' => 'DB Management', 'active_keys' => ['admin/databaseManagement']],
    ['url' => BASE_URL . '/admin/manageDoctorNurseAssignments', 'icon' => '🔗', 'text' => 'Doctor-Nurse Assign', 'active_keys' => ['admin/manageDoctorNurseAssignments']],
    ['url' => BASE_URL . '/admin/updateProfile', 'icon' => '👤', 'text' => 'My Profile', 'active_keys' => ['admin/updateProfile']],
];

$feedbacks = $data['feedbacks'] ?? [];
$doctors = $data['doctors'] ?? [];
$filters = $data['filters'] ?? [];
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
    <title><?php echo htmlspecialchars($pageTitleForTopbar); ?> - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Common Admin Styles (Sidebar, Topbar, Main Content) */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; background-color: #f0f2f5; 
            color: #343a40; margin: 0; padding: 0; display: flex; min-height: 100vh;
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
            text-align: center;
        }
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

        /* Styles for Feedback Management Page */
        .content-table-container-cutie {
            background-color: #fff; border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; padding: 25px;
        }
        .content-table-cutie { width: 100%; border-collapse: collapse; }
        .content-table-cutie th, .content-table-cutie td { 
            padding: 12px 15px; text-align: left; 
            border-bottom: 1px solid #ecf0f1; font-size: 14px; vertical-align: middle;
        }
        .content-table-cutie th { background-color: #f7f9f9; font-weight: 600; color: #34495e; white-space: nowrap; }
        .content-table-cutie tbody tr:hover { background-color: #fdfdfe; }
        .rating-stars-cutie { color: #f39c12; font-size: 1.1em; }
        .comment-cell-cutie { max-width: 400px; white-space: normal; }
        .status-badge-cutie {
            padding: 5px 12px; border-radius: 15px; font-size: 12px;
            font-weight: 500; color: white; display: inline-block;
        }
        .status-published { background-color: #2ecc71; }
        .status-hidden { background-color: #95a5a6; }
        .btn-toggle-publish-cutie {
            border: none; padding: 6px 12px; border-radius: 5px;
            font-size: 13px; cursor: pointer; transition: background-color 0.2s;
            font-weight: 500;
        }
        .btn-publish { background-color: #27ae60; color: white; }
        .btn-hide { background-color: #7f8c8d; color: white; }
        
        .message-feedback { 
            padding: 12px 18px; margin-bottom: 20px; border-radius: 6px; 
            font-size: 14px; text-align: center; font-weight: 500;
            border-left-width: 4px; border-left-style: solid;
        }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; }
        .no-items-msg-cutie { text-align: center; padding: 30px; color: #7f8c8d; font-style: italic; }

        @media (max-width: 768px) {
            .dashboard-sidebar-cutie { width: 100%; height: auto; position: static; box-shadow: none; }
            .dashboard-main-content-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h2 { font-size: 20px; }
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-wrapper-cutie { padding: 20px 15px; }
        }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo-cutie">Admin Panel</a>
        </div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <?php foreach ($adminSidebarMenu as $item): ?>
                    <?php 
                    $isActive = false;
                    if (is_array($item['active_keys'])) {
                        foreach ($item['active_keys'] as $key) {
                            if (strpos($currentUrl, $key) !== false) {
                                if (($key === 'admin/dashboard' || $key === 'admin')) {
                                    if ($currentUrl === 'admin/dashboard' || $currentUrl === 'admin' || $currentUrl === rtrim(BASE_URL . '/admin','/')) {
                                        $isActive = true;
                                    }
                                } else {
                                    $isActive = true;
                                }
                                if ($isActive) break;
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
        
        <div class="actual-page-content-wrapper-cutie">
            <div class="content-table-container-cutie">
                <?php if (isset($_SESSION['admin_feedback_message_success'])): ?>
                    <div class="message-feedback success">
                        <?php echo htmlspecialchars($_SESSION['admin_feedback_message_success']); unset($_SESSION['admin_feedback_message_success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['admin_feedback_message_error'])): ?>
                    <div class="message-feedback error">
                        <?php echo htmlspecialchars($_SESSION['admin_feedback_message_error']); unset($_SESSION['admin_feedback_message_error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($feedbacks)): ?>
                    <table class="content-table-cutie">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td><?php echo $feedback['FeedbackID']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($feedback['FeedbackDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($feedback['PatientName']); ?></td>
                                    <td>Dr. <?php echo htmlspecialchars($feedback['DoctorName'] ?? 'N/A'); ?></td>
                                    <td class="rating-stars-cutie">
                                        <?php echo str_repeat('⭐', (int)$feedback['Rating']); ?>
                                        <?php echo str_repeat('☆', 5 - (int)$feedback['Rating']); ?>
                                    </td>
                                    <td class="comment-cell-cutie" title="<?php echo htmlspecialchars($feedback['Comments']); ?>">
                                        <?php echo htmlspecialchars(substr($feedback['Comments'], 0, 100) . (strlen($feedback['Comments']) > 100 ? '...' : '')); ?>
                                    </td>
                                    <td>
                                        <?php if ($feedback['IsPublished']): ?>
                                            <span class="status-badge-cutie status-published">Published</span>
                                        <?php else: ?>
                                            <span class="status-badge-cutie status-hidden">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="<?php echo BASE_URL; ?>/admin/toggleFeedbackPublication" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['FeedbackID']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $feedback['IsPublished']; ?>">
                                            <button type="submit" class="btn-toggle-publish-cutie <?php echo $feedback['IsPublished'] ? 'btn-hide' : 'btn-publish'; ?>">
                                                <?php echo $feedback['IsPublished'] ? 'Hide' : 'Publish'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-items-msg-cutie">No patient feedbacks found yet. ✨</p>
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