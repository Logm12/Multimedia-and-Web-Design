<?php
// app/views/patient/dashboard.php

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = ($script_name_dir == '/' || $script_name_dir == '\\') ? '' : rtrim($script_name_dir, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}

$userFullName = $_SESSION['user_fullname'] ?? 'Valued Patient';
// Determine avatar source carefully
$currentAvatarPath = $_SESSION['user_avatar'] ?? null; // Get from session first
$avatarSrc = BASE_URL . '/public/assets/img/default_avatar.png'; // Default
if (!empty($currentAvatarPath) && $currentAvatarPath !== 'default_avatar.png') {
    if (filter_var($currentAvatarPath, FILTER_VALIDATE_URL)) {
        $avatarSrc = htmlspecialchars($currentAvatarPath);
    } elseif (file_exists(PUBLIC_PATH . $currentAvatarPath)) {
         $avatarSrc = BASE_URL . '/' . htmlspecialchars($currentAvatarPath);
    }
}


// Data from controller, ensuring defaults if not set
$upcomingAppointmentsCount = $data['upcoming_appointments_count'] ?? 0;
$activePrescriptionsCount = $data['active_prescriptions_count'] ?? 0;
$recentChatsCount = $data['recent_chats_count'] ?? 0;
$todaysAppointments = $data['todays_appointments'] ?? [];
$welcomeMessage = $data['welcome_message'] ?? 'Welcome back, ' . htmlspecialchars($userFullName) . '!';
$browseDoctorsLink = $data['browse_doctors_link'] ?? BASE_URL . '/patient/browseDoctors';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Patient Dashboard'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
            width: 260px; background-color: #667EEA; color: #fff;
            padding: 25px 0; display: flex; flex-direction: column; transition: width 0.3s ease;
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
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;
            padding-bottom: 20px; border-bottom: 1px solid #e9ecef;
        }
        .welcome-message-cutie h2 { font-size: 26px; font-weight: 600; color: #212529; }
        .welcome-message-cutie p { font-size: 15px; color: #6c757d; margin-top: 4px; }

        .user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .user-actions-cutie .icon-button-cutie {
            background: none; border: none; font-size: 22px; color: #6c757d; cursor: pointer; position: relative;
            transition: color 0.2s ease;
        }
        .user-actions-cutie .icon-button-cutie:hover { color: #343a40; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e0e0; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #495057; }
        
        .quick-stats-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; margin-bottom: 30px; }
        .stat-card-cutie {
            background-color: #fff; padding: 20px 25px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card-cutie:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
        .stat-card-cutie h3 { font-size: 15px; font-weight: 600; color: #495057; margin-bottom: 8px; }
        .stat-card-cutie .stat-value-cutie { font-size: 32px; font-weight: 700; color: #667EEA; margin-bottom: 5px; }

        .content-grid-cutie { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .content-panel-cutie { background-color: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); min-height: 200px; }
        .content-panel-cutie h3 { font-size: 18px; font-weight: 600; color: #343a40; margin-bottom: 20px; }
        
        .appointments-list-cutie ul { list-style: none; padding: 0; }
        .appointments-list-cutie li {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 0; border-bottom: 1px solid #f1f3f5; font-size: 14px;
        }
        .appointments-list-cutie li:last-child { border-bottom: none; }
        .appointments-list-cutie .appointment-time-cutie { font-weight: 500; color: #495057; min-width: 80px; }
        .appointments-list-cutie .appointment-doctor-cutie { color: #212529; flex-grow: 1; margin-left: 15px; }
        .appointments-list-cutie .appointment-status-cutie {
            padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500;
        }
        .appointments-list-cutie .status-confirmed-cutie { background-color: #d1fae5; color: #065f46; }
        .appointments-list-cutie .status-pending-cutie { background-color: #ffedd5; color: #9a3412; } /* Assuming 'Scheduled' might be 'Pending' visually */
        .appointments-list-cutie .status-scheduled-cutie { background-color: #e0e7ff; color: #4338ca; } /* Added for scheduled */
        .appointments-list-cutie .status-cancelled-cutie { background-color: #fee2e2; color: #991b1b; }
        .no-appointments-message-cutie { color: #6c757d; font-style: italic; padding-top: 10px; }

        .overview-panel-cutie p { color: #6c757d; font-style: italic; text-align: center; padding-top: 50px; }

        @media (max-width: 992px) { /* ... responsive styles ... */ }
        @media (max-width: 768px) { /* ... responsive styles ... */ }
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
                <li><a href="<?php echo htmlspecialchars($browseDoctorsLink ?? BASE_URL . '/patient/browseDoctors'); ?>" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/browseDoctors') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🩺</span>Browse Doctors</a></li>
                <li><a href="<?php echo BASE_URL; ?>/appointment/myAppointments" class="<?php echo (strpos($_GET['url'] ?? '', 'appointment/myAppointments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>My Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/viewAllMedicalRecords" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/viewAllMedicalRecords') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📜</span>Medical Records</a></li>
                <li><a href="<?php echo BASE_URL; ?>/feedback/list" class="<?php echo (strpos($_GET['url'] ?? '', 'feedback/list') !== false || strpos($_GET['url'] ?? '', 'feedback/submit') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⭐</span>Feedback</a></li>
                <li><a href="<?php echo BASE_URL; ?>/notification/list" class="<?php echo (strpos($_GET['url'] ?? '', 'notification/list') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🔔</span>Notifications</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/updateProfile" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>Profile</a></li>
                <li>
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="logout-link-cutie" style="margin-top: 20px; border-top: 1px solid #e9ecef;">
                        <span class="nav-icon-cutie">🚪</span>Logout
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">
            © <?php echo date('Y'); ?> Healthcare System
        </div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="welcome-message-cutie">
                <h2>Patient Dashboard</h2>
                <p><?php echo htmlspecialchars($welcomeMessage); ?></p>
            </div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie" title="My Profile">
                    <img src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="User Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span>
                     ▼ 
                </div>
                 <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

        <section class="quick-stats-cutie">
            <div class="stat-card-cutie">
                <h3>Upcoming Appointments</h3>
                <div class="stat-value-cutie"><?php echo htmlspecialchars($upcomingAppointmentsCount); ?></div>
            </div>
            <div class="stat-card-cutie">
                <h3>Active Prescriptions</h3>
                <div class="stat-value-cutie"><?php echo htmlspecialchars($activePrescriptionsCount); ?></div>
            </div>
            <div class="stat-card-cutie">
                <h3>Recent Chats</h3>
                <div class="stat-value-cutie"><?php echo htmlspecialchars($recentChatsCount); ?></div>
            </div>
        </section>

        <section class="content-grid-cutie">
            <div class="content-panel-cutie appointments-list-cutie">
                <h3>Today's Appointments</h3>
                <?php if (!empty($todaysAppointments)): ?>
                    <ul>
                        <?php foreach ($todaysAppointments as $appt): ?>
                            <li>
                                <span class="appointment-time-cutie"><?php echo htmlspecialchars(date("h:i A", strtotime($appt['AppointmentDateTime'] ?? ''))); ?></span>
                                <span class="appointment-doctor-cutie"><?php echo htmlspecialchars($appt['DoctorFullName'] ?? 'N/A'); ?></span>
                                <span class="appointment-status-cutie 
                                    <?php 
                                        $status = strtolower($appt['Status'] ?? 'pending');
                                        $statusClass = 'status-pending-cutie'; // Default
                                        if ($status === 'confirmed') $statusClass = 'status-confirmed-cutie';
                                        elseif ($status === 'scheduled') $statusClass = 'status-scheduled-cutie'; // Added specific class for scheduled
                                        elseif (strpos($status, 'cancelled') !== false) $statusClass = 'status-cancelled-cutie';
                                        // Add more else if for other statuses like 'completed', 'noshow' if needed
                                        echo $statusClass;
                                    ?>">
                                    <?php echo htmlspecialchars(ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], $appt['Status'] ?? 'Pending'))); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-appointments-message-cutie">No appointments scheduled for today</p>
                <?php endif; ?>
            </div>
            <div class="content-panel-cutie overview-panel-cutie">
                <h3>Weekly/Monthly Overview</h3>
                <p>Chart and overview data coming soon</p>
            </div>
        </section>
    </main>
    
</body>
</html>