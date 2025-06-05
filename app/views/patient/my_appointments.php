<?php
// app/views/patient/my_appointments.php

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = ($script_name_dir == '/' || $script_name_dir == '\\') ? '' : rtrim($script_name_dir, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}

$userFullName = $_SESSION['user_fullname'] ?? 'Valued Patient';
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_avatar.png';

// $data = $data ?? [
//     'title' => 'My Appointments',
//     'appointments' => [
//         ['AppointmentID' => 1, 'AppointmentDateTime' => '2024-08-15 10:00:00', 'DoctorName' => 'Dr. Emily Carter', 'SpecializationName' => 'Cardiology', 'ReasonForVisit' => 'Chest pain', 'Status' => 'Confirmed'],
//         ['AppointmentID' => 2, 'AppointmentDateTime' => '2024-08-16 14:30:00', 'DoctorName' => 'Dr. John Smith', 'SpecializationName' => 'Pediatrics', 'ReasonForVisit' => 'Regular checkup', 'Status' => 'Scheduled'],
//         ['AppointmentID' => 3, 'AppointmentDateTime' => '2024-08-10 09:00:00', 'DoctorName' => 'Dr. Sarah Wilson', 'SpecializationName' => 'Dermatology', 'ReasonForVisit' => 'Skin rash', 'Status' => 'Completed'],
//         ['AppointmentID' => 4, 'AppointmentDateTime' => '2024-08-05 11:00:00', 'DoctorName' => 'Dr. Emily Carter', 'SpecializationName' => 'Cardiology', 'ReasonForVisit' => 'Follow-up', 'Status' => 'CancelledByPatient'],
//     ],
//     'allStatuses' => ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow'],
//     'currentFilter' => 'All'
// ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'My Appointments'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
            width: 260px; background-color: #343a40; color: #f8f9fa;
            padding: 25px 0; display: flex; flex-direction: column;
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; display: block; }
        
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a {
            display: flex; align-items: center; padding: 15px 25px; color: #adb5bd; text-decoration: none;
            font-size: 15px; font-weight: 500; transition: background-color 0.2s ease, color 0.2s ease;
            border-left: 4px solid transparent;
        }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie {
            background-color: #495057; color: #fff; border-left-color: #667EEA;
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #6c757d; }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        .main-header-cutie {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;
            padding-bottom: 20px; border-bottom: 1px solid #dee2e6;
        }
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; color: #212529; }
        
        .user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .user-actions-cutie .icon-button-cutie { background: none; border: none; font-size: 22px; color: #6c757d; cursor: pointer; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e0e0; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #495057; }

        .appointments-toolbar-cutie { display: flex; justify-content: flex-end; align-items: center; margin-bottom: 20px; gap: 15px; }
        .filter-dropdown-cutie select {
            padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; background-color: #fff; color: #495057;
            cursor: pointer; min-width: 180px;
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236c757d'%3E%3Cpath fill-rule='evenodd' d='M8 11.293l-4.146-4.147a.5.5 0 0 1 .708-.708L8 9.879l3.438-3.438a.5.5 0 0 1 .707.708L8 11.293z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;
        }
        .filter-dropdown-cutie select:focus { border-color: #667EEA; box-shadow: 0 0 0 0.2rem rgba(102,126,234,.25); outline: none; }

        .appointments-table-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; }
        .appointments-table-cutie { width: 100%; border-collapse: collapse; }
        .appointments-table-cutie th, .appointments-table-cutie td {
            padding: 12px 15px; text-align: left; border-bottom: 1px solid #e9ecef; font-size: 14px;
        }
        .appointments-table-cutie th { background-color: #f8f9fa; font-weight: 600; color: #495057; white-space: nowrap; }
        .appointments-table-cutie tbody tr:hover { background-color: #f1f3f5; }
        
        .status-badge-cutie {
            padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;
            display: inline-block; white-space: nowrap;
        }
        .status-scheduled-cutie { background-color: #e0e7ff; color: #4338ca; }
        .status-confirmed-cutie { background-color: #d1fae5; color: #065f46; }
        .status-completed-cutie { background-color: #f3f4f6; color: #4b5563; }
        .status-cancelledbypatient-cutie, .status-cancelledbyclinic-cutie { background-color: #fee2e2; color: #991b1b; }
        .status-noshow-cutie { background-color: #ffedd5; color: #9a3412; }

        .action-buttons-cutie a, .action-buttons-cutie button {
            padding: 6px 10px; font-size: 13px; border-radius: 5px; text-decoration: none;
            border: none; cursor: pointer; transition: opacity 0.2s ease; margin-right: 5px;
        }
        .action-buttons-cutie a:hover, .action-buttons-cutie button:hover { opacity: 0.8; }
        .btn-view-summary-cutie { background-color: #17a2b8; color: white; }
        .btn-cancel-appt-cutie { background-color: #dc3545; color: white; }
        .btn-disabled-cutie { background-color: #6c757d; color: #fff; cursor: not-allowed; font-size: 12px; padding: 4px 8px;}

        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .no-appointments-msg-cutie { text-align: center; padding: 40px 20px; color: #6c757d; font-style: italic; }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            .dashboard-sidebar-cutie { width: 100%; height: auto; flex-direction: row; overflow-x: auto; padding: 10px 0; justify-content: flex-start; }
            .sidebar-header-cutie { display: none; }
            .sidebar-nav-cutie ul { display: flex; flex-direction: row; }
            .sidebar-nav-cutie li a { padding: 10px 15px; border-left: none; border-bottom: 3px solid transparent; }
            .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie { border-bottom-color: #667EEA; background-color: transparent; }
            .sidebar-footer-cutie { display: none; }
            .dashboard-main-content-cutie { padding: 20px; }
            .appointments-toolbar-cutie { flex-direction: column; align-items: stretch; gap: 10px; }
            .filter-dropdown-cutie select { width: 100%; }
        }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie">
            <a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">HealthSys</a>
        </div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/patient/dashboard" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/dashboard') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo $data['browse_doctors_link'] ?? BASE_URL . '/patient/browseDoctors'; ?>" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/browseDoctors') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🩺</span>Browse Doctors</a></li>
                <li><a href="<?php echo BASE_URL; ?>/appointment/myAppointments" class="<?php echo (strpos($_GET['url'] ?? '', 'appointment/myAppointments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>My Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/viewAllMedicalRecords" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/viewAllMedicalRecords') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📜</span>Medical Records</a></li>
                <li><a href="<?php echo BASE_URL; ?>/feedback/list" class="<?php echo (strpos($_GET['url'] ?? '', 'feedback/list') !== false || strpos($_GET['url'] ?? '', 'feedback/submit') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⭐</span>Feedback</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/updateProfile" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>Profile</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">
            © <?php echo date('Y'); ?> Healthcare System
        </div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-cutie">
                <h2>My Appointments</h2>
            </div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie" title="My Profile">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="User Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

        <?php if (isset($_SESSION['appointment_message_success'])): ?>
            <p class="message-cutie success-message"><?php echo $_SESSION['appointment_message_success']; unset($_SESSION['appointment_message_success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['appointment_message_error'])): ?>
            <p class="message-cutie error-message"><?php echo $_SESSION['appointment_message_error']; unset($_SESSION['appointment_message_error']); ?></p>
        <?php endif; ?>

        <div class="appointments-toolbar-cutie">
            <form method="GET" action="<?php echo BASE_URL; ?>/appointment/myAppointments" class="filter-dropdown-cutie">
                <label for="status" class="visually-hidden">Filter by Status:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <?php foreach($data['allStatuses'] as $statusOption): ?>
                        <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo ($data['currentFilter'] == $statusOption) ? 'selected' : ''; ?>>
                            Filter: <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], $statusOption)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <!-- Add more dropdowns here if needed, e.g., Sort by Time, Filter by Date Range -->
        </div>

        <div class="appointments-table-container-cutie">
            <?php if (!empty($data['appointments'])): ?>
                <table class="appointments-table-cutie">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Doctor</th>
                            <th>Department</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['appointments'] as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('D, M j, Y \a\t g:i A', strtotime($appointment['AppointmentDateTime']))); ?></td>
                                <td><?php echo htmlspecialchars($appointment['DoctorName']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['SpecializationName'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($appointment['ReasonForVisit'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="status-badge-cutie status-<?php echo strtolower(htmlspecialchars($appointment['Status'])); ?>-cutie">
                                        <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], htmlspecialchars($appointment['Status']))); ?>
                                    </span>
                                </td>
                                <td class="action-buttons-cutie">
                                    <?php if ($appointment['Status'] === 'Completed'): ?>
                                        <a href="<?php echo BASE_URL . '/patient/viewAppointmentSummary/' . $appointment['AppointmentID']; ?>" class="btn-view-summary-cutie">View Summary</a>
                                    <?php endif; ?>
                                    <?php
                                    $canCancel = false;
                                    if (in_array($appointment['Status'], ['Scheduled', 'Confirmed'])) {
                                        $appointmentTime = strtotime($appointment['AppointmentDateTime']);
                                        $currentTime = time();
                                        if (($appointmentTime - $currentTime) > (24 * 60 * 60)) { // More than 24 hours away
                                            $canCancel = true;
                                        }
                                    }
                                    ?>
                                    <?php if ($canCancel): ?>
                                        <form action="<?php echo BASE_URL; ?>/appointment/cancelByPatient" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to cancel this appointment, sweetie?');">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['AppointmentID']; ?>">
                                            <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                                            <button type="submit" class="btn-cancel-appt-cutie">Cancel</button>
                                        </form>
                                    <?php elseif (in_array($appointment['Status'], ['Scheduled', 'Confirmed'])): ?>
                                        <span class="btn-disabled-cutie">Cannot cancel (too close)</span>
                                    <?php endif; ?>
                                    <?php if (!in_array($appointment['Status'], ['Completed', 'Scheduled', 'Confirmed']) && !$canCancel ): echo '-'; endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-appointments-msg-cutie">You have no appointments <?php echo ($data['currentFilter'] !== 'All' && $data['currentFilter'] !== '') ? "with status '" . htmlspecialchars(ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], $data['currentFilter']))) . "'" : "yet"; ?>. Time to book one!</p>
            <?php endif; ?>
        </div>
    </main>
    <style>.visually-hidden { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0; }</style>
</body>
</html>