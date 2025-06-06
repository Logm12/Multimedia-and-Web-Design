<?php
// app/views/doctor/my_schedule.php

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}
if (!defined('BASE_URL')) { 
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../../../public'));
}

// Data for Topbar and Sidebar
$currentUser = $data['currentUser'] ?? [];
$topbarUserFullName = $currentUser['FullName'] ?? $_SESSION['user_fullname'] ?? 'Doctor';
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_avatar.png'; 
if (!empty($currentUser['Avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($currentUser['Avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($currentUser['Avatar'], '/');
}

$pageTitleForTopbar = $data['title'] ?? 'My Schedule';
$welcomeMessageForTopbar = 'View and manage your upcoming appointments.';
$currentUrl = $_GET['url'] ?? 'doctor/mySchedule';

// Doctor Sidebar Menu Definition
$doctorSidebarMenu = [
    ['url' => BASE_URL . '/doctor/dashboard', 'icon' => '🏠', 'text' => 'Dashboard', 'active_keys' => ['doctor/dashboard', 'doctor']],
    ['url' => BASE_URL . '/doctor/mySchedule', 'icon' => '🗓️', 'text' => 'My Schedule', 'active_keys' => ['doctor/mySchedule', 'medicalrecord/viewConsultationDetails']],
    ['url' => BASE_URL . '/doctor/manageAvailability', 'icon' => '⏰', 'text' => 'Manage Availability', 'active_keys' => ['doctor/manageAvailability']],
    ['url' => BASE_URL . '/doctor/patientList', 'icon' => '👥', 'text' => 'Patient List', 'active_keys' => ['doctor/patientList']],
    ['url' => BASE_URL . '/doctor/requestLeave', 'icon' => '✈️', 'text' => 'Request Leave', 'active_keys' => ['doctor/requestLeave', 'doctor/myLeaveRequests']],
    ['url' => BASE_URL . '/doctor/updateProfile', 'icon' => '👤', 'text' => 'My Profile', 'active_keys' => ['doctor/updateProfile']],
];

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
    <title><?php echo htmlspecialchars($pageTitleForTopbar); ?> - Doctor Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Common Styles */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        /* Sidebar Styles */
        .dashboard-sidebar-cutie {
            width: 260px; background: #2c3e50; color: #ecf0f1;
            padding: 25px 0; display: flex; flex-direction: column;
            height: 100vh; position: fixed; top: 0; left: 0; overflow-y: auto;
            z-index: 1000;
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-nav-cutie ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav-cutie li a { 
            display: flex; align-items: center; padding: 15px 25px; 
            color: #bdc3c7; text-decoration: none; font-size: 15px; 
            font-weight: 500; border-left: 4px solid transparent; 
            transition: all 0.2s ease; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: #34495e; color: #fff; 
            border-left-color: #3498db; 
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #7f8c8d; }

        /* Main Content & Topbar Styles */
        .dashboard-main-content-cutie {
            flex-grow: 1; margin-left: 260px; 
            overflow-y: auto;
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
        .topbar-user-actions-cutie .icon-button-cutie:hover { color: #2c3e50; }
        .user-profile-toggle-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
        .user-profile-toggle-cutie img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e0e0; }
        .user-profile-toggle-cutie span { font-weight: 500; font-size: 14px; color: #2c3e50; }
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

        /* Page Specific Styles */
        .schedule-toolbar-cutie { display: flex; justify-content: flex-start; align-items: center; margin-bottom: 20px; gap: 20px; flex-wrap: wrap; padding: 15px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .filter-group-cutie { display: flex; align-items: center; gap: 8px; }
        .filter-group-cutie label { font-size: 14px; color: #495057; font-weight: 500; }
        .filter-group-cutie select, .filter-group-cutie input[type="date"] {
            padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; background-color: #fff; color: #495057; min-width: 160px;
        }
        .filter-group-cutie select {
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236c757d'%3E%3Cpath fill-rule='evenodd' d='M8 11.293l-4.146-4.147a.5.5 0 0 1 .708-.708L8 9.879l3.438-3.438a.5.5 0 0 1 .707.708L8 11.293z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;
        }
        .filter-group-cutie select:focus, .filter-group-cutie input[type="date"]:focus { border-color: #3498db; box-shadow: 0 0 0 0.2rem rgba(52,152,219,.25); outline: none; }
        .btn-filter-schedule-cutie {
            padding: 9px 18px; background-color: #3498db; color: white; border: none;
            border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background-color 0.2s ease;
        }
        .btn-filter-schedule-cutie:hover { background-color: #2980b9; }

        .schedule-table-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; }
        .schedule-table-cutie { width: 100%; border-collapse: collapse; }
        .schedule-table-cutie th, .schedule-table-cutie td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ecf0f1; font-size: 14px; vertical-align: middle; }
        .schedule-table-cutie th { background-color: #f7f9f9; font-weight: 600; color: #34495e; white-space: nowrap; }
        .schedule-table-cutie tbody tr:hover { background-color: #fdfdfe; }
        
        .status-badge-doctor-cutie { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; white-space: nowrap; }
        .status-scheduled { background-color: #e9ecef; color: #495057; }
        .status-confirmed { background-color: #d1fae5; color: #065f46; }
        .status-completed { background-color: #e0f2f1; color: #004d40; }
        .status-cancelledbypatient, .status-cancelledbyclinic { background-color: #fee2e2; color: #991b1b; }
        .status-noshow { background-color: #fff3cd; color: #856404; }

        .action-buttons-doctor-cutie a, .action-buttons-doctor-cutie button {
            padding: 7px 12px; font-size: 13px; border-radius: 5px; text-decoration: none;
            border: none; cursor: pointer; transition: opacity 0.2s ease; margin-right: 6px; margin-bottom: 5px; display: inline-block;
        }
        .action-buttons-doctor-cutie a:hover, .action-buttons-doctor-cutie button:hover { opacity: 0.8; }
        .btn-consult-cutie { background-color: #3498db; color: white; }
        .btn-complete-cutie { background-color: #2ecc71; color: white; }
        
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .no-appointments-msg-doctor-cutie { text-align: center; padding: 40px 20px; color: #7f8c8d; font-style: italic; }

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
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>/doctor/dashboard" class="sidebar-logo-cutie">Doctor Panel</a></div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <?php foreach ($doctorSidebarMenu as $item): ?>
                    <?php 
                    $isActive = false;
                    if (is_array($item['active_keys'])) {
                        foreach ($item['active_keys'] as $key) {
                            if (strpos($currentUrl, $key) !== false) {
                                if (($key === 'doctor/dashboard' || $key === 'doctor')) {
                                    if ($currentUrl === 'doctor/dashboard' || $currentUrl === 'doctor' || $currentUrl === rtrim(BASE_URL . '/doctor','/')) { $isActive = true; }
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
                <a href="<?php echo BASE_URL; ?>/notification/list" class="icon-button-cutie" title="Notifications">🔔</a>
                <div class="user-profile-toggle-cutie" id="userProfileToggle">
                    <img src="<?php echo htmlspecialchars($topbarUserAvatar); ?>" alt="User Avatar">
                    <span>Dr. <?php echo htmlspecialchars($topbarUserFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

        <div class="actual-page-content-wrapper-cutie">
            <?php if (isset($_SESSION['schedule_message_success'])): ?>
                <p class="message-cutie success-message"><?php echo $_SESSION['schedule_message_success']; unset($_SESSION['schedule_message_success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['schedule_message_error'])): ?>
                <p class="message-cutie error-message"><?php echo $_SESSION['schedule_message_error']; unset($_SESSION['schedule_message_error']); ?></p>
            <?php endif; ?>

            <form method="GET" action="<?php echo BASE_URL; ?>/doctor/mySchedule" id="scheduleFilterForm" class="schedule-toolbar-cutie">
                <div class="filter-group-cutie">
                    <label for="date_filter">Date:</label>
                    <select name="date" id="date_filter" onchange="this.form.submit()">
                        <option value="all_upcoming" <?php echo (($data['currentDateFilter'] ?? '') == 'all_upcoming') ? 'selected' : ''; ?>>All Upcoming</option>
                        <option value="today" <?php echo (($data['currentDateFilter'] ?? '') == 'today') ? 'selected' : ''; ?>>Today</option>
                        <option value="this_week" <?php echo (($data['currentDateFilter'] ?? '') == 'this_week') ? 'selected' : ''; ?>>This Week</option>
                        <option value="all_time" <?php echo (($data['currentDateFilter'] ?? '') == 'all_time') ? 'selected' : ''; ?>>All Time</option>
                    </select>
                </div>
                <div class="filter-group-cutie">
                    <label for="status_filter">Status:</label>
                    <select name="status" id="status_filter" onchange="this.form.submit()">
                        <?php foreach($data['allStatuses'] as $statusOption): ?>
                            <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo (($data['currentStatusFilter'] ?? '') == $statusOption) ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], $statusOption)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <div class="schedule-table-container-cutie">
                <?php if (!empty($data['appointments'])): ?>
                    <table class="schedule-table-cutie">
                        <thead>
                            <tr>
                                <th>Date & Time</th><th>Patient Name</th><th>Patient Phone</th>
                                <th>Reason</th><th>Status</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['appointments'] as $appointment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(date('D, M j, Y \a\t g:i A', strtotime($appointment['AppointmentDateTime']))); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['PatientName']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['PatientPhoneNumber'] ?? 'N/A'); ?></td>
                                    <td title="<?php echo htmlspecialchars($appointment['ReasonForVisit'] ?? ''); ?>"><?php echo htmlspecialchars(substr($appointment['ReasonForVisit'] ?? 'N/A', 0, 30) . (strlen($appointment['ReasonForVisit'] ?? '') > 30 ? '...' : '')); ?></td>
                                    <td>
                                        <span class="status-badge-doctor-cutie status-<?php echo strtolower(htmlspecialchars($appointment['Status'])); ?>">
                                            <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], htmlspecialchars($appointment['Status']))); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons-doctor-cutie">
                                        <?php if (in_array($appointment['Status'], ['Scheduled', 'Confirmed', 'Completed'])): ?>
                                            <a href="<?php echo BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appointment['AppointmentID']; ?>" class="btn-consult-cutie">
                                                <?php echo ($appointment['Status'] === 'Completed') ? 'View/Edit Notes' : 'Start Consultation'; ?>
                                            </a>
                                            <?php if (in_array($appointment['Status'], ['Scheduled', 'Confirmed'])): ?>
                                            <form action="<?php echo BASE_URL; ?>/doctor/markAsCompleted" method="POST" style="display:inline-block;" onsubmit="return confirm('Mark this appointment as completed?');">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['AppointmentID']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <button type="submit" class="btn-complete-cutie">Complete</button>
                                            </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-appointments-msg-doctor-cutie">No appointments found for the selected filters, Dr. <?php echo htmlspecialchars($topbarUserFullName); ?>.</p>
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
        
        const dateFilter = document.getElementById('date_filter');
        const statusFilter = document.getElementById('status_filter');
        if(dateFilter) {
            dateFilter.addEventListener('change', function() {
                document.getElementById('scheduleFilterForm').submit();
            });
        }
        if(statusFilter) {
            statusFilter.addEventListener('change', function() {
                document.getElementById('scheduleFilterForm').submit();
            });
        }
    });
</script>
</body>
</html>