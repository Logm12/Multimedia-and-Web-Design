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
$avatarSrc = BASE_URL . '/public/assets/images/default_avatar.png'; // Default
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
            width: 260px; background-color:rgb(10,46,106); color: #fff;
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

        /* Container chung cho các hành động của user */
.user-actions {
    display: flex;
    align-items: center;
    gap: 15px; /* Khoảng cách giữa các phần tử */
}

/* Style cho các nút icon như chuông thông báo */
.icon-button {
    background: none;
    border: none;
    font-size: 20px; /* Kích thước icon lớn hơn một chút */
    color: #6c757d;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s ease, color 0.2s ease;
}
.icon-button:hover {
    background-color: #f1f3f5;
    color: #343a40;
}

/* --- Phần Dropdown Profile --- */
.profile-dropdown {
    position: relative; /* Quan trọng để định vị menu con */
}

/* Nút bấm để mở menu */
.profile-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    background-color: transparent;
    border: none;
    padding: 4px 8px;
    border-radius: 20px;
    transition: background-color 0.2s ease;
}
.profile-trigger:hover {
    background-color: #e9ecef;
}

.profile-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-name {
    font-weight: 500;
    font-size: 15px;
    color: #495057;
}

.dropdown-arrow {
    font-size: 12px;
    color: #6c757d;
}

/* Menu dropdown con */
.dropdown-menu {
    position: absolute;
    top: calc(100% + 10px); /* Vị trí dưới nút trigger, có khoảng cách 10px */
    right: 0;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    min-width: 200px; /* Độ rộng tối thiểu */
    z-index: 1000;
    border: 1px solid #e9ecef;
    padding: 8px 0;
    overflow: hidden;
    transition: opacity 0.2s ease, transform 0.2s ease;
}

/* Trạng thái ẩn của menu (dùng cho JS) */
.dropdown-menu.hidden {
    opacity: 0;
    transform: translateY(-10px);
    pointer-events: none; /* Không thể click khi đang ẩn */
}

/* Các mục trong menu */
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    font-size: 14px;
    color: #495057;
    text-decoration: none;
    transition: background-color 0.2s ease;
}
.dropdown-item i {
    width: 16px; /* Căn chỉnh icon */
    text-align: center;
    color: #868e96;
}
.dropdown-item:hover {
    background-color: #f8f9fa;
}

/* Mục logout có màu đỏ để nhấn mạnh */
.dropdown-item-logout:hover {
    background-color: #fff5f5;
    color: #e03131;
}
.dropdown-item-logout:hover i {
    color: #e03131;
}

/* Đường kẻ phân cách */
.dropdown-divider {
    height: 1px;
    background-color: #e9ecef;
    margin: 8px 0;
}
        
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
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('profileDropdownTrigger');
    const menu = document.getElementById('profileDropdownMenu');

    if (trigger && menu) {
        // Sự kiện khi click vào nút trigger
        trigger.addEventListener('click', function(event) {
            event.stopPropagation(); // Ngăn sự kiện click lan ra ngoài
            menu.classList.toggle('hidden');
        });

        // Sự kiện khi click ra ngoài menu thì đóng menu lại
        window.addEventListener('click', function(event) {
            if (!menu.contains(event.target) && !trigger.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    }
});
</script>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie">
            <a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">PulseCare</a>
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
           <div class="user-actions">
    <!-- Nút thông báo với icon từ Font Awesome -->
    <button class="icon-button" title="Notifications">
        <i class="fas fa-bell"></i>
    </button>

    <!-- Khu vực profile, bao gồm cả trigger và menu dropdown -->
    <div class="profile-dropdown">
        <!-- Phần này là nút bấm để mở menu -->
        <button class="profile-trigger" id="profileDropdownTrigger">
            <img src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="User Avatar" class="profile-avatar">
            <span class="profile-name"><?php echo htmlspecialchars($userFullName); ?></span>
            <i class="fas fa-caret-down dropdown-arrow"></i>
        </button>

        <!-- Menu dropdown, mặc định sẽ bị ẩn -->
        <div class="dropdown-menu hidden" id="profileDropdownMenu">
            <a href="<?php echo BASE_URL; ?>/patient/updateProfile" class="dropdown-item">
                <i class="fas fa-user-circle"></i> My Profile
            </a>
            <a href="#" class="dropdown-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?php echo BASE_URL; ?>/auth/logout" class="dropdown-item dropdown-item-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
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