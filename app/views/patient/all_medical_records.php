<?php
// app/views/patient/all_medical_records.php

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
//     'title' => 'My Medical Records',
//     'medicalHistory' => [
//         ['RecordID' => 1, 'VisitDate' => '2024-07-15', 'DoctorName' => 'Dr. Emily Carter', 'Diagnosis' => 'Mild hypertension, needs lifestyle changes and regular monitoring.', 'AppointmentID' => 101],
//         ['RecordID' => 2, 'VisitDate' => '2024-06-20', 'DoctorName' => 'Dr. John Smith', 'Diagnosis' => 'Common cold, prescribed rest and fluids.', 'AppointmentID' => 95],
//     ],
//     'filterOptions' => [
//         'doctors' => [['DoctorID' => 1, 'DoctorName' => 'Dr. Emily Carter'], ['DoctorID' => 2, 'DoctorName' => 'Dr. John Smith']],
//         'date_ranges' => ['all' => 'All Time', 'last_month' => 'Last Month']
//     ],
//     'currentFilters' => ['doctor_id' => null, 'date_range' => 'all']
// ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'My Medical Records'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
            width: 260px; background-color: #667EEA; color: #fff;
            padding: 25px 0; display: flex; flex-direction: column;
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; display: block; }
        
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a {
            display: flex; align-items: center; padding: 15px 25px; color: #e0e0e0; text-decoration: none;
            font-size: 15px; font-weight: 500; transition: background-color 0.2s ease, color 0.2s ease;
            border-left: 4px solid transparent;
        }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie {
            background-color: rgba(255,255,255,0.1); color: #fff; border-left-color: #fff;
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #c0c0c0; }

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

        .records-toolbar-cutie { display: flex; justify-content: flex-end; align-items: center; margin-bottom: 20px; gap: 15px; flex-wrap: wrap; }
        .filter-dropdown-cutie select {
            padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; background-color: #fff; color: #495057;
            cursor: pointer; min-width: 180px;
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236c757d'%3E%3Cpath fill-rule='evenodd' d='M8 11.293l-4.146-4.147a.5.5 0 0 1 .708-.708L8 9.879l3.438-3.438a.5.5 0 0 1 .707.708L8 11.293z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;
        }
        .filter-dropdown-cutie select:focus { border-color: #667EEA; box-shadow: 0 0 0 0.2rem rgba(102,126,234,.25); outline: none; }

        .records-table-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; }
        .records-table-cutie { width: 100%; border-collapse: collapse; }
        .records-table-cutie th, .records-table-cutie td {
            padding: 12px 15px; text-align: left; border-bottom: 1px solid #e9ecef; font-size: 14px;
        }
        .records-table-cutie th { background-color: #f8f9fa; font-weight: 600; color: #495057; white-space: nowrap; }
        .records-table-cutie tbody tr:hover { background-color: #f1f3f5; }
        .records-table-cutie .diagnosis-summary-cutie { max-width: 350px; /* Adjusted width */ white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        .action-buttons-cutie a {
            padding: 6px 12px; font-size: 13px; border-radius: 5px; text-decoration: none;
            border: none; cursor: pointer; transition: opacity 0.2s ease;
            background-color: #667EEA; color: white; display: inline-block;
        }
        .action-buttons-cutie a:hover { opacity: 0.85; }

        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .no-records-msg-cutie { text-align: center; padding: 40px 20px; color: #6c757d; font-style: italic; }
        .back-to-dash-link-cutie { display: inline-block; margin-top: 30px; padding: 10px 18px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
        .back-to-dash-link-cutie:hover { background-color: #5a6268; }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            .dashboard-sidebar-cutie { width: 100%; height: auto; flex-direction: row; overflow-x: auto; padding: 10px 0; justify-content: flex-start; }
            .sidebar-header-cutie { display: none; }
            .sidebar-nav-cutie ul { display: flex; flex-direction: row; }
            .sidebar-nav-cutie li a { padding: 10px 15px; border-left: none; border-bottom: 3px solid transparent; }
            .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie { border-bottom-color: #fff; background-color: transparent; }
            .sidebar-footer-cutie { display: none; }
            .dashboard-main-content-cutie { padding: 20px; }
            .records-toolbar-cutie { flex-direction: column; align-items: stretch; gap: 10px; }
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
                <h2><?php echo htmlspecialchars($data['title'] ?? 'My Medical Records'); ?></h2>
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

        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="message-cutie error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>
        
        <form method="GET" action="<?php echo BASE_URL; ?>/patient/viewAllMedicalRecords">
            <div class="records-toolbar-cutie">
                <?php if (!empty($data['filterOptions']['doctors'])): ?>
                <div class="filter-dropdown-cutie">
                    <label for="filter_doctor" class="visually-hidden">Filter by Doctor:</label>
                    <select name="doctor_id" id="filter_doctor" onchange="this.form.submit()">
                        <option value="">All Doctors</option>
                        <?php foreach($data['filterOptions']['doctors'] as $doctor): ?>
                            <option value="<?php echo htmlspecialchars($doctor['DoctorID']); ?>" <?php echo (isset($data['currentFilters']['doctor_id']) && $data['currentFilters']['doctor_id'] == $doctor['DoctorID']) ? 'selected' : ''; ?>>
                                Dr. <?php echo htmlspecialchars($doctor['DoctorName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if (!empty($data['filterOptions']['date_ranges'])): ?>
                <div class="filter-dropdown-cutie">
                    <label for="filter_date_range" class="visually-hidden">Filter by Date:</label>
                    <select name="date_range" id="filter_date_range" onchange="this.form.submit()">
                        <?php foreach($data['filterOptions']['date_ranges'] as $key => $label): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo (isset($data['currentFilters']['date_range']) && $data['currentFilters']['date_range'] == $key) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </form>

        <div class="records-table-container-cutie">
            <?php if (!empty($data['medicalHistory'])): ?>
                <p style="padding: 0 15px 10px; font-size:14px; color:#6c757d;">This page shows a summary of your past consultations and medical records.</p>
                <table class="records-table-cutie">
                    <thead>
                        <tr>
                            <th>Visit Date</th>
                            <th>Consulting Doctor</th>
                            <th>Diagnosis (Summary)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['medicalHistory'] as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('D, M j, Y', strtotime($record['VisitDate']))); ?></td>
                                <td>Dr. <?php echo htmlspecialchars($record['DoctorName']); ?></td>
                                <td class="diagnosis-summary-cutie" title="<?php echo htmlspecialchars($record['Diagnosis'] ?? ''); ?>">
                                    <?php
                                    $diagnosisSummary = $record['Diagnosis'] ?? 'N/A';
                                    if (strlen($diagnosisSummary) > 70) {
                                        $diagnosisSummary = substr($diagnosisSummary, 0, 70) . '...';
                                    }
                                    echo htmlspecialchars($diagnosisSummary);
                                    ?>
                                </td>
                                <td class="action-buttons-cutie">
                                    <?php if (!empty($record['AppointmentID'])): ?>
                                        <a href="<?php echo BASE_URL . '/patient/viewAppointmentSummary/' . $record['AppointmentID']; ?>">View Detail</a>
                                    <?php else: ?>
                                        <span>Details N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-records-msg-cutie">You have no medical records in the system yet. Perhaps it's time for a check-up? </p>
            <?php endif; ?>
        </div>

        <p style="text-align: center; margin-top:30px;">
            <a href="<?php echo BASE_URL; ?>/patient/dashboard" class="back-to-dash-link-cutie">« Back to Dashboard</a>
        </p>
    </main>
    <style>.visually-hidden { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0; }</style>
</body>
</html>