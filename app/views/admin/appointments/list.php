<?php
// app/views/admin/appointments/list.php

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

$pageTitleForTopbar = $data['title'] ?? 'All Appointments';
$welcomeMessageForTopbar = 'View and manage all scheduled appointments in the system.';
$currentUrl = $_GET['url'] ?? 'admin/listAllAppointments';

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

        .filters-toolbar-admin-cutie { background-color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .filters-form-grid-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px 20px; align-items: flex-end; }
        .filter-group-admin-appt-cutie label { display: block; font-size: 13px; color: #495057; margin-bottom: 6px; font-weight: 500; }
        .filter-group-admin-appt-cutie input, .filter-group-admin-appt-cutie select { width: 100%; padding: 9px 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
        .filter-group-admin-appt-cutie select {
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236c757d'%3E%3Cpath fill-rule='evenodd' d='M8 11.293l-4.146-4.147a.5.5 0 0 1 .708-.708L8 9.879l3.438-3.438a.5.5 0 0 1 .707.708L8 11.293z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;
        }
        .filter-buttons-group-cutie { display: flex; gap: 10px; margin-top: 10px; grid-column: 1 / -1; justify-content: flex-start;}
        .btn-admin-filter, .btn-admin-clear-filter { padding: 9px 18px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; text-decoration: none; }
        .btn-admin-filter { background-color: #0a783c; color: white; } .btn-admin-filter:hover { background-color: #086330; }
        .btn-admin-clear-filter { background-color: #bdc3c7; color: #2c3e50; } .btn-admin-clear-filter:hover { background-color: #95a5a6; }

        .appointments-table-container-admin-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; padding: 25px; }
        .appointments-table-admin-cutie { width: 100%; border-collapse: collapse; }
        .appointments-table-admin-cutie th, .appointments-table-admin-cutie td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #ecf0f1; font-size: 13px; white-space: nowrap; vertical-align: middle;}
        .appointments-table-admin-cutie th { background-color: #f7f9f9; font-weight: 600; color: #34495e; }
        .appointments-table-admin-cutie tbody tr:hover { background-color: #fdfdfe; }
        .status-badge-admin-appt-cutie { padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: 500; color: white; display: inline-block; text-align: center; min-width: 80px;}
        .status-scheduled { background-color: #5dade2; } .status-confirmed { background-color: #2ecc71; }
        .status-completed { background-color: #95a5a6; } .status-cancelledbypatient, .status-cancelledbyclinic { background-color: #e74c3c; }
        .status-noshow { background-color: #f39c12; } .status-rescheduled { background-color: #f1c40f; color:#2c3e50;}
        
        .action-buttons-admin-appt-cutie a, .action-buttons-admin-appt-cutie button {
            padding: 5px 8px; font-size: 12px; border-radius: 4px; text-decoration: none;
            border: none; cursor: pointer; transition: opacity 0.2s ease; margin-right: 5px; display: inline-block;
        }
        .action-buttons-admin-appt-cutie a:hover, .action-buttons-admin-appt-cutie button:hover { opacity: 0.8; }
        .btn-view-emr-cutie { background-color: #10ac84; color: white; }
        .btn-cancel-admin-cutie { background-color: #e74c3c; color: white; }
        
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
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-cutie { padding: 20px 15px; }
            .filters-form-grid-cutie { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo-cutie">HealthSys</a></div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="<?php echo (strpos($currentUrl, 'admin/dashboard') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listUsers" class="<?php echo (strpos($currentUrl, 'admin/listUsers') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👥</span>Manage Users</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageSpecializations" class="<?php echo (strpos($currentUrl, 'admin/manageSpecializations') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏷️</span>Specializations</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listMedicines" class="<?php echo (strpos($currentUrl, 'admin/listMedicines') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">💊</span>Manage Medicines</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listAllAppointments" class="<?php echo (strpos($currentUrl, 'admin/listAllAppointments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>All Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/report/overview" class="<?php echo (strpos($currentUrl, 'report/overview') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📊</span>Reports</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageLeaveRequests" class="<?php echo (strpos($currentUrl, 'admin/manageLeaveRequests') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">✈️</span>Leave Requests</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageFeedbacks" class="<?php echo (strpos($currentUrl, 'admin/manageFeedbacks') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⭐</span>Patient Feedbacks</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/databaseManagement" class="<?php echo (strpos($currentUrl, 'admin/databaseManagement') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">💾</span>DB Management</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/updateProfile" class="<?php echo (strpos($currentUrl, 'admin/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>My Profile</a></li>
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
            <?php if (isset($_SESSION['admin_appointment_message_success'])): ?>
                <p class="message-cutie success-message"><?php echo $_SESSION['admin_appointment_message_success']; unset($_SESSION['admin_appointment_message_success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['admin_appointment_message_error'])): ?>
                <p class="message-cutie error-message"><?php echo $_SESSION['admin_appointment_message_error']; unset($_SESSION['admin_appointment_message_error']); ?></p>
            <?php endif; ?>

            <div class="filters-toolbar-admin-cutie">
                <form method="GET" action="<?php echo BASE_URL; ?>/admin/listAllAppointments" class="filters-form-grid-cutie">
                    <div class="filter-group-admin-appt-cutie"><label for="date_from">Date From:</label><input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($data['filters']['date_from'] ?? ''); ?>"></div>
                    <div class="filter-group-admin-appt-cutie"><label for="date_to">Date To:</label><input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($data['filters']['date_to'] ?? ''); ?>"></div>
                    <div class="filter-group-admin-appt-cutie"><label for="doctor_id_filter">Doctor:</label><select name="doctor_id" id="doctor_id_filter"><option value="">All Doctors</option><?php if (!empty($data['doctorsForFilter'])) foreach ($data['doctorsForFilter'] as $doctor): ?><option value="<?php echo $doctor['DoctorID']; ?>" <?php echo (($data['filters']['doctor_id'] ?? '') == $doctor['DoctorID']) ? 'selected' : ''; ?>>Dr. <?php echo htmlspecialchars($doctor['DoctorName']); ?></option><?php endforeach; ?></select></div>
                    <div class="filter-group-admin-appt-cutie"><label for="patient_search_filter">Patient:</label><input type="text" name="patient_search" id="patient_search_filter" value="<?php echo htmlspecialchars($data['filters']['patient_search'] ?? ''); ?>" placeholder="Name or Phone"></div>
                    <div class="filter-group-admin-appt-cutie"><label for="status_filter_app">Status:</label><select name="status" id="status_filter_app"><?php foreach (($data['allStatuses'] ?? []) as $statusOption): ?><option value="<?php echo $statusOption; ?>" <?php echo (($data['filters']['status'] ?? 'All') == $statusOption) ? 'selected' : ''; ?>><?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], $statusOption)); ?></option><?php endforeach; ?></select></div>
                    <div class="filter-buttons-group-cutie"><button type="submit" class="btn-admin-filter">Filter</button><a href="<?php echo BASE_URL; ?>/admin/listAllAppointments" class="btn-admin-clear-filter">Clear Filters</a></div>
                </form>
            </div>

            <div class="appointments-table-container-admin-cutie">
                <?php if (!empty($data['appointments'])): ?>
                    <p style="padding: 0 0 10px 0; font-size:14px; color:#6c757d;">Total appointments found: <?php echo count($data['appointments']); ?></p>
                    <table class="appointments-table-admin-cutie">
                        <thead><tr><th>ID</th><th>Date & Time</th><th>Doctor</th><th>Specialization</th><th>Patient Name</th><th>Patient Phone</th><th>Reason</th><th>Status</th><th>EMR</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['appointments'] as $appointment): ?>
                                <tr>
                                    <td><?php echo $appointment['AppointmentID']; ?></td>
                                    <td><?php echo htmlspecialchars(date('D, M j, Y \a\t g:i A', strtotime($appointment['AppointmentDateTime']))); ?></td>
                                    <td>Dr. <?php echo htmlspecialchars($appointment['DoctorName']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['SpecializationName'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['PatientName']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['PatientPhoneNumber'] ?? 'N/A'); ?></td>
                                    <td title="<?php echo htmlspecialchars($appointment['ReasonForVisit'] ?? ''); ?>"><?php echo nl2br(htmlspecialchars(substr($appointment['ReasonForVisit'] ?? 'N/A', 0, 30) . (strlen($appointment['ReasonForVisit'] ?? '') > 30 ? '...' : ''))); ?></td>
                                    <td><span class="status-badge-admin-appt-cutie status-<?php echo strtolower(htmlspecialchars($appointment['Status'])); ?>"><?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], htmlspecialchars($appointment['Status']))); ?></span></td>
                                    <td><?php if (!empty($appointment['RecordID'])): ?><a href="<?php echo BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appointment['AppointmentID']; ?>" target="_blank" class="action-buttons-admin-appt-cutie btn-view-emr-cutie">View</a><?php else: ?>N/A<?php endif; ?></td>
                                    <td class="action-buttons-admin-appt-cutie">
                                        <?php if (in_array($appointment['Status'], ['Scheduled', 'Confirmed'])): ?>
                                            <form action="<?php echo BASE_URL; ?>/admin/cancelAppointmentByAdmin" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to cancel this appointment? The patient and doctor will be notified.');">
                                                <input type="hidden" name="appointment_id_to_cancel" value="<?php echo $appointment['AppointmentID']; ?>">
                                                <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                                                <button type="submit" class="btn-cancel-admin-cutie">Cancel</button>
                                            </form>
                                        <?php elseif ($appointment['Status'] === 'Completed'): ?>
                                            <span>Completed</span>
                                        <?php else: ?>
                                            <span><?php echo htmlspecialchars(ucfirst($appointment['Status'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-items-msg-cutie">No appointments found matching your criteria. <?php if (array_filter($data['filters'] ?? [])): ?><a href="<?php echo BASE_URL; ?>/admin/listAllAppointments">Clear all filters?</a><?php endif; ?></p>
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