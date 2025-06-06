<?php
// app/views/patient/notifications/list.php

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Patient') {
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
$currentUser = $data['currentUser'] ?? [];
$topbarUserFullName = $currentUser['FullName'] ?? $_SESSION['user_fullname'] ?? 'Patient';
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_avatar.png'; 
if (!empty($currentUser['Avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($currentUser['Avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($currentUser['Avatar'], '/');
}

$pageTitleForTopbar = $data['title'] ?? 'My Notifications';
$welcomeMessageForTopbar = 'Here are your latest updates and alerts.';
$currentUrl = $_GET['url'] ?? 'notification/list';

// Patient Sidebar Menu
$patientSidebarMenu = [
    ['url' => BASE_URL . '/patient/dashboard', 'icon' => '🏠', 'text' => 'Dashboard', 'active_keys' => ['patient/dashboard', 'patient']],
    ['url' => BASE_URL . '/patient/browseDoctors', 'icon' => '👨‍⚕️', 'text' => 'Browse Doctors', 'active_keys' => ['patient/browseDoctors']],
    ['url' => BASE_URL . '/appointment/myAppointments', 'icon' => '🗓️', 'text' => 'My Appointments', 'active_keys' => ['appointment/myAppointments']],
    ['url' => BASE_URL . '/medicalrecord/myRecords', 'icon' => '📂', 'text' => 'My Medical Records', 'active_keys' => ['medicalrecord/myRecords']],
    ['url' => BASE_URL . '/notification/list', 'icon' => '🔔', 'text' => 'Notifications', 'active_keys' => ['notification/list']],
    ['url' => BASE_URL . '/patient/updateProfile', 'icon' => '👤', 'text' => 'My Profile', 'active_keys' => ['patient/updateProfile']],
];

$notifications = $data['notifications'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitleForTopbar); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Common Patient Styles (Sidebar, Topbar, Main Content) */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; background-color: #f4f7fc; 
            color: #343a40; margin: 0; padding: 0; display: flex; min-height: 100vh;
        }
        .patient-sidebar-cutie {
            width: 260px; background: #fff; 
            border-right: 1px solid #e7e9ed;
            padding: 25px 0; display: flex; flex-direction: column;
            height: 100vh; position: fixed; top: 0; left: 0; overflow-y: auto;
            z-index: 1000;
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 22px; font-weight: 700; color: #007bff; text-decoration: none; }
        .sidebar-nav-cutie ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav-cutie li a { 
            display: flex; align-items: center; padding: 14px 25px; 
            color: #5a6268; text-decoration: none; font-size: 15px; 
            font-weight: 500; border-left: 4px solid transparent; 
            transition: all 0.2s ease-in-out; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: #e9ecef; color: #007bff; 
            border-left-color: #007bff; 
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { 
            margin-right: 15px; font-size: 18px; width: 20px; 
            text-align: center;
        }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #6c757d; }

        .patient-main-content-cutie {
            flex-grow: 1; margin-left: 260px; 
            background-color: #f4f7fc; overflow-y: auto;
        }
        .topbar-shared-cutie {
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 30px; background-color: #ffffff;
            border-bottom: 1px solid #e7e9ed; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .topbar-title-section-cutie h2 { font-size: 22px; font-weight: 600; color: #343a40; margin:0; }
        .topbar-title-section-cutie p { font-size: 14px; color: #6c757d; margin-top: 4px; }
        .topbar-user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .topbar-user-actions-cutie .icon-button-cutie { 
            background: none; border: none; font-size: 20px; 
            color: #6c757d; cursor: pointer; padding: 5px;
            transition: color 0.2s ease;
        }
        .topbar-user-actions-cutie .icon-button-cutie:hover { color: #007bff; }
        .user-profile-toggle-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
        .user-profile-toggle-cutie img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #e9ecef; }
        .user-profile-toggle-cutie span { font-weight: 500; font-size: 14px; color: #007bff; }
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

        /* Styles for Notification Page */
        .notifications-container-cutie {
            max-width: 900px;
            margin: 0 auto;
        }
        .notifications-header-cutie {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn-mark-all-read-cutie {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }
        .notification-list-cutie {
            list-style: none;
            padding: 0;
        }
        .notification-item-cutie {
            background-color: #fff;
            border-left: 5px solid #007bff;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: box-shadow 0.2s ease;
        }
        .notification-item-cutie.unread-cutie {
            background-color: #e7f3ff; /* Light blue for unread */
            border-left-color: #0056b3;
        }
        .notification-item-cutie:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .notification-link-cutie {
            display: flex;
            align-items: center;
            padding: 20px;
            text-decoration: none;
            color: inherit;
        }
        .notification-icon-cutie {
            font-size: 24px;
            margin-right: 20px;
            color: #007bff;
        }
        .notification-content-cutie {
            flex-grow: 1;
        }
        .notification-content-cutie p {
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
        }
        .notification-time-cutie {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        .no-notifications-cutie {
            text-align: center;
            padding: 50px;
            background-color: #fff;
            border-radius: 8px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <aside class="patient-sidebar-cutie">
        <div class="sidebar-header-cutie">
            <a href="<?php echo BASE_URL; ?>/patient/dashboard" class="sidebar-logo-cutie">PulseCare</a>
        </div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <?php foreach ($patientSidebarMenu as $item): ?>
                    <?php 
                    $isActive = false;
                    if (is_array($item['active_keys'])) {
                        foreach ($item['active_keys'] as $key) {
                            if (strpos($currentUrl, $key) !== false) {
                                $isActive = true;
                                break;
                            }
                        }
                    } else {
                         if (strpos($currentUrl, $item['active_keys']) !== false) {
                            if ($item['active_keys'] === 'patient/dashboard' && ($currentUrl === 'patient/dashboard' || $currentUrl === 'patient')) {
                                $isActive = true;
                            } elseif ($item['active_keys'] !== 'patient/dashboard') {
                                $isActive = true;
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
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="logout-link-cutie" style="margin-top: 20px; border-top: 1px solid #e9ecef;">
                        <span class="nav-icon-cutie">🚪</span>Logout
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="patient-main-content-cutie">
        <header class="topbar-shared-cutie">
            <div class="topbar-title-section-cutie">
                <h2><?php echo htmlspecialchars($pageTitleForTopbar); ?></h2>
                <p><?php echo htmlspecialchars($welcomeMessageForTopbar); ?></p>
            </div>
            <div class="topbar-user-actions-cutie">
                <a href="<?php echo BASE_URL; ?>/notification/list" class="icon-button-cutie" title="Notifications">🔔</a>
                <div class="user-profile-toggle-cutie" id="userProfileToggle">
                    <img src="<?php echo htmlspecialchars($topbarUserAvatar); ?>" alt="Patient Avatar">
                    <span><?php echo htmlspecialchars($topbarUserFullName); ?> ▼</span>
                    <div class="user-profile-dropdown-content-cutie" id="userProfileDropdown">
                        <a href="<?php echo BASE_URL; ?>/patient/updateProfile">My Profile</a>
                        <a href="<?php echo BASE_URL; ?>/auth/logout">Logout</a>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="actual-page-content-wrapper-cutie">
            <div class="notifications-container-cutie">
                <div class="notifications-header-cutie">
                    <h3>Your Alerts</h3>
                    <?php if(!empty($notifications)): ?>
                        <a href="<?php echo BASE_URL; ?>/notification/markAllAsRead" class="btn-mark-all-read-cutie">Mark All as Read</a>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="message-feedback success" style="text-align:left; margin-bottom: 20px;">
                        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($notifications)): ?>
                    <ul class="notification-list-cutie">
                        <?php foreach ($notifications as $notification): ?>
                            <li class="notification-item-cutie <?php echo !$notification['IsRead'] ? 'unread-cutie' : ''; ?>">
                                <a href="<?php echo BASE_URL; ?>/notification/read/<?php echo $notification['NotificationID']; ?>" class="notification-link-cutie">
                                    <div class="notification-icon-cutie">
                                        <?php 
                                            // Display different icons based on notification type
                                            switch ($notification['Type']) {
                                                case 'APPOINTMENT_CONFIRMED': echo '✅'; break;
                                                case 'APPOINTMENT_CANCELLED': echo '❌'; break;
                                                case 'EMR_UPDATED': echo '📝'; break;
                                                case 'APPOINTMENT_REMINDER': echo '⏰'; break;
                                                default: echo 'ℹ️'; break;
                                            }
                                        ?>
                                    </div>
                                    <div class="notification-content-cutie">
                                        <p><?php echo htmlspecialchars($notification['Message']); ?></p>
                                        <div class="notification-time-cutie">
                                            <?php echo date('F j, Y, g:i a', strtotime($notification['CreatedAt'])); ?>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="no-notifications-cutie">
                        <p style="font-size: 48px; margin-bottom: 20px;">🎉</p>
                        <h4>All caught up!</h4>
                        <p>You have no new notifications.</p>
                    </div>
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