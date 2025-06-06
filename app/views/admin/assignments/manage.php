<?php
// app/views/admin/assignments/manage.php

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

$topbarUserFullName = $data['currentUser']['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Admin');
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_admin_avatar.png'; 
if (isset($data['currentUser']['Avatar']) && !empty($data['currentUser']['Avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($data['currentUser']['Avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($data['currentUser']['Avatar'], '/');
} elseif (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($_SESSION['user_avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($_SESSION['user_avatar'], '/');
}

$pageTitleForTopbar = $data['title'] ?? 'Manage Doctor-Nurse Assignments';
$welcomeMessageForTopbar = 'Assign nurses to doctors or manage existing assignments.';
$currentUrl = $_GET['url'] ?? 'admin/manageDoctorNurseAssignments'; 

$adminSidebarMenu = [
    ['url' => BASE_URL . '/admin/dashboard', 'icon' => '🏠', 'text' => 'Dashboard', 'active_keys' => ['admin/dashboard', 'admin']],
    ['url' => BASE_URL . '/admin/listUsers', 'icon' => '👥', 'text' => 'Manage Users', 'active_keys' => ['admin/listUsers', 'admin/createUser', 'admin/editUser']],
    ['url' => BASE_URL . '/admin/manageSpecializations', 'icon' => '🏷️', 'text' => 'Specializations', 'active_keys' => ['admin/manageSpecializations', 'admin/editSpecialization']],
    ['url' => BASE_URL . '/admin/listMedicines', 'icon' => '💊', 'text' => 'Manage Medicines', 'active_keys' => ['admin/listMedicines', 'admin/createMedicine', 'admin/editMedicine']],
    ['url' => BASE_URL . '/admin/listAllAppointments', 'icon' => '🗓️', 'text' => 'All Appointments', 'active_keys' => ['admin/listAllAppointments']],
    ['url' => BASE_URL . '/report/overview', 'icon' => '📊', 'text' => 'Reports', 'active_keys' => ['report/overview']],
    ['url' => BASE_URL . '/admin/manageLeaveRequests', 'icon' => '✈️', 'text' => 'Leave Requests', 'active_keys' => ['admin/manageLeaveRequests', 'admin/reviewLeaveRequest']],
    ['url' => BASE_URL . '/admin/manageFeedbacks', 'icon' => '⭐', 'text' => 'Patient Feedbacks', 'active_keys' => ['admin/manageFeedbacks']],
    ['url' => BASE_URL . '/admin/manageDoctorNurseAssignments', 'icon' => '🔗', 'text' => 'Doctor-Nurse Assign', 'active_keys' => ['admin/manageDoctorNurseAssignments']], // This page
    ['url' => BASE_URL . '/admin/updateProfile', 'icon' => '👤', 'text' => 'My Profile', 'active_keys' => ['admin/updateProfile']],
];


$assignmentsData = $data['assignments_data'] ?? [];
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
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; } /* Adjusted from 22px to 24px */
        .sidebar-nav-cutie ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav-cutie li a { 
            display: flex; align-items: center; padding: 15px 25px; /* Adjusted from 14px */
            color: #dfe6e9; text-decoration: none; font-size: 15px; 
            font-weight: 500; border-left: 4px solid transparent; 
            transition: all 0.2s ease; /* Adjusted from 0.25s */
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: rgba(255, 255, 255, 0.15); /* Adjusted from 0.1 */
            color: #fff; 
            border-left-color: #55efc4; 
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { 
            margin-right: 12px; /* Adjusted from 15px */
            font-size: 18px; width: 20px; 
            text-align: center; 
            /* transition: transform 0.2s ease; */ /* Removed as it was in the original but not in the dashboard one */
        }
        /* Removed :hover .nav-icon-cutie { transform: scale(1.1); } to match dashboard */
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #bdc3c7; }

        .dashboard-main-content-cutie { /* Using the same class as admin dashboard */
            flex-grow: 1; margin-left: 260px; 
            overflow-y: auto; background-color: #f0f2f5; /* Added to match dashboard */
        }
        .topbar-shared-cutie { /* Using the same class as admin dashboard */
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 30px; background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .topbar-title-section-cutie h2 { font-size: 22px; font-weight: 600; color: #2c3e50; margin:0; }
        .topbar-title-section-cutie p { font-size: 14px; color: #7f8c8d; margin-top: 4px; }
        .topbar-user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .topbar-user-actions-cutie .icon-button-cutie { 
            background: none; border: none; font-size: 20px; /* Adjusted from 22px */
            color: #7f8c8d; cursor: pointer; padding: 5px;
            transition: color 0.2s ease;
        }
        .topbar-user-actions-cutie .icon-button-cutie:hover { color: #0a783c; }
        .user-profile-toggle-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
        .user-profile-toggle-cutie img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e0e0; } /* Adjusted from 40px */
        .user-profile-toggle-cutie span { font-weight: 500; font-size: 14px; color: #0a783c; } /* Adjusted from 15px and color */
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

        /* Styles for Assignment Management Page */
        .assignments-container-cutie {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.07);
        }
        .assignments-container-cutie h3.section-title-assignments {
            font-size: 1.6em;
            color: #0a783c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .doctor-assignment-card-cutie {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 25px;
            padding: 20px;
            background-color: #fdfdfd;
        }
        .doctor-info-header-cutie {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .doctor-info-header-cutie h4 {
            margin: 0;
            font-size: 1.3em;
            color: #2c3e50;
        }
        .doctor-info-header-cutie .specialization-tag-cutie {
            background-color: #e0f2f1; 
            color: #00796b; 
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .assigned-nurses-list-cutie {
            list-style: none;
            padding-left: 0;
            margin-bottom: 15px;
        }
        .assigned-nurses-list-cutie li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
            font-size: 14px;
        }
        .assigned-nurses-list-cutie li:last-child { border-bottom: none; }
        .assigned-nurses-list-cutie .nurse-name-cutie { color: #34495e; }
        .btn-unassign-cutie {
            background-color: #e74c3c; color: white;
            border: none; padding: 5px 10px; border-radius: 5px;
            font-size: 12px; cursor: pointer; transition: background-color 0.2s;
        }
        .btn-unassign-cutie:hover { background-color: #c0392b; }

        .assign-nurse-form-cutie { display: flex; gap: 10px; align-items: flex-end; }
        .assign-nurse-form-cutie select {
            padding: 8px 10px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; flex-grow: 1;
        }
        .btn-assign-cutie {
            background-color: #2ecc71; color: white;
            border: none; padding: 8px 15px; border-radius: 6px;
            font-size: 14px; cursor: pointer; transition: background-color 0.2s;
            font-weight: 500;
        }
        .btn-assign-cutie:hover { background-color: #27ae60; }
        .no-nurses-message-cutie {
            font-style: italic; color: #7f8c8d; font-size: 14px;
        }
        .message-feedback { 
            padding: 12px 18px; margin-bottom: 20px; border-radius: 6px; 
            font-size: 14px; text-align: center; font-weight: 500;
            border-left-width: 4px; border-left-style: solid;
        }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; }

        @media (max-width: 768px) {
            .dashboard-sidebar-cutie { width: 100%; height: auto; position: static; box-shadow: none; }
            .dashboard-main-content-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h2 { font-size: 20px; }
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-wrapper-cutie { padding: 20px 15px; }
            .assign-nurse-form-cutie { flex-direction: column; align-items: stretch; }
            .assign-nurse-form-cutie select, .btn-assign-cutie { width: 100%; }
        }

    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie"> <!-- Using class from admin dashboard -->
        <div class="sidebar-header-cutie">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo-cutie">HealthSys</a> <!-- Logo text from admin dashboard -->
        </div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <?php foreach ($adminSidebarMenu as $item): ?>
                    <?php 
                    $isActive = false;
                    // Check if current URL matches any of the active keys for this menu item
                    if (is_array($item['active_keys'])) {
                        foreach ($item['active_keys'] as $key) {
                            if (strpos($currentUrl, $key) !== false) {
                                // Specific check for dashboard to avoid matching '/admin' in other URLs
                                if ($key === 'admin/dashboard' || $key === 'admin') {
                                    if ($currentUrl === 'admin/dashboard' || $currentUrl === 'admin' || $currentUrl === 'admin/') {
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

    <main class="dashboard-main-content-cutie"> <!-- Using class from admin dashboard -->
        <header class="topbar-shared-cutie"> <!-- Using class from admin dashboard -->
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
            <div class="assignments-container-cutie">
                <h3 class="section-title-assignments">Assign Nurses to Doctors</h3>

                <?php if (isset($_SESSION['assignment_message_success'])): ?>
                    <div class="message-feedback success">
                        <?php echo htmlspecialchars($_SESSION['assignment_message_success']); unset($_SESSION['assignment_message_success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['assignment_message_error'])): ?>
                    <div class="message-feedback error">
                        <?php echo htmlspecialchars($_SESSION['assignment_message_error']); unset($_SESSION['assignment_message_error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($assignmentsData)): ?>
                    <p class="no-nurses-message-cutie">No doctors found to assign nurses to. Please add doctors first.</p>
                <?php else: ?>
                    <?php foreach ($assignmentsData as $assignment): ?>
                        <div class="doctor-assignment-card-cutie">
                            <div class="doctor-info-header-cutie">
                                <h4>Dr. <?php echo htmlspecialchars($assignment['doctor_name']); ?></h4>
                                <span class="specialization-tag-cutie"><?php echo htmlspecialchars($assignment['doctor_specialization']); ?></span>
                            </div>

                            <h5>Currently Assigned Nurses:</h5>
                            <?php if (!empty($assignment['assigned_nurses'])): ?>
                                <ul class="assigned-nurses-list-cutie">
                                    <?php foreach ($assignment['assigned_nurses'] as $nurse): ?>
                                        <li>
                                            <span class="nurse-name-cutie"><?php echo htmlspecialchars($nurse['FullName']); ?> (ID: <?php echo $nurse['NurseID']; ?>)</span>
                                            <form action="<?php echo BASE_URL; ?>/admin/processAssignment" method="POST" onsubmit="return confirm('Are you sure you want to unassign Nurse <?php echo htmlspecialchars($nurse['FullName']); ?> from Dr. <?php echo htmlspecialchars($assignment['doctor_name']); ?>?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="doctor_id" value="<?php echo $assignment['doctor_id']; ?>">
                                                <input type="hidden" name="nurse_id" value="<?php echo $nurse['NurseID']; ?>">
                                                <input type="hidden" name="action_type" value="unassign">
                                                <button type="submit" class="btn-unassign-cutie">Unassign</button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="no-nurses-message-cutie">No nurses currently assigned to this doctor.</p>
                            <?php endif; ?>

                            <h5 style="margin-top: 15px;">Assign a New Nurse:</h5>
                            <?php if (!empty($assignment['available_nurses_for_assignment'])): ?>
                                <form action="<?php echo BASE_URL; ?>/admin/processAssignment" method="POST" class="assign-nurse-form-cutie">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                    <input type="hidden" name="doctor_id" value="<?php echo $assignment['doctor_id']; ?>">
                                    <input type="hidden" name="action_type" value="assign">
                                    <select name="nurse_id" required>
                                        <option value="">-- Select a Nurse to Assign --</option>
                                        <?php foreach ($assignment['available_nurses_for_assignment'] as $availNurse): ?>
                                            <option value="<?php echo $availNurse['NurseID']; ?>">
                                                <?php echo htmlspecialchars($availNurse['FullName']); ?> (ID: <?php echo $availNurse['NurseID']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn-assign-cutie">Assign Nurse</button>
                                </form>
                            <?php else: ?>
                                <p class="no-nurses-message-cutie">No other nurses available to assign to this doctor (or all are already assigned).</p>
                            <?php endif; ?>
                        </div> 
                    <?php endforeach; ?>
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