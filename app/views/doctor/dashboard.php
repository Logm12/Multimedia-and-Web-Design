<?php
// app/views/doctor/dashboard.php

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}
$userFullName = $_SESSION['user_fullname'] ?? 'Valued Doctor';
$currentAvatarPath = $_SESSION['user_avatar'] ?? null; // Get from session first
$avatarSrc = BASE_URL . '/assets/images/default_avatar.png'; // Default
if (!empty($currentAvatarPath) && $currentAvatarPath !== 'default_avatar.png') {
    if (filter_var($currentAvatarPath, FILTER_VALIDATE_URL)) {
        $avatarSrc = htmlspecialchars($currentAvatarPath);
    } elseif (file_exists(PUBLIC_PATH . $currentAvatarPath)) {
         $avatarSrc = BASE_URL . '/' . htmlspecialchars($currentAvatarPath);
    }
}
$currentUrlForMenu = $_GET['url'] ?? ''; 
$urlPartsForMenu = explode('/', rtrim($currentUrlForMenu, '/'));
$currentControllerForMenu = $urlPartsForMenu[0] ?? '';
$currentActionForMenu = $urlPartsForMenu[1] ?? 'index'; // Mặc định là 'index' nếu không có action

$pageTitle = $data['title'] ?? 'Doctor Dashboard';
$welcomeMessage = htmlspecialchars($data['welcome_message'] ?? ('Welcome back, Dr. ' . ($_SESSION['user_fullname'] ?? '') . '!'));
$followedPatientsCount = $data['followed_patients_count'] ?? 0;
$followUpsDueCount = $data['follow_ups_due_count'] ?? 0;
$patientFeedbacksCount = $data['patient_feedbacks_count'] ?? 0;
$todaysAppointments = $data['todays_appointments'] ?? [];
$appointmentOverviewData = $data['appointment_overview_data'] ?? ['labels' => [], 'counts' => []];
$doctorSidebarMenu = [
    [
        'url' => '/doctor/dashboard', 
        'icon' => '🏠', 
        'text' => 'Dashboard', 
        'active_logic' => function($controller, $action) {
            return ($controller === 'doctor' && ($action === 'dashboard' || $action === 'index'));
        }
    ],
    [
        'url' => '/doctor/mySchedule', 
        'icon' => '🗓️', 
        'text' => 'View My Schedule', 
        'active_logic' => function($url) {
            return (strpos($url, 'doctor/mySchedule') !== false);
        }
    ],
    [
        'url' => '/medicalrecord/viewConsultationDetails', // URL này có vẻ hơi lạ, thường EMR sẽ là một action của Doctor
        'icon' => '📝', 
        'text' => 'EMR', 
        'active_logic' => function($url) {
            // Cậu cần điều chỉnh logic này cho phù hợp với cách cậu tổ chức EMR
            // Ví dụ: nếu EMR là một phần của doctor/consultation/{id}
            return (strpos($url, 'medicalrecord/viewConsultationDetails') !== false || strpos($url, 'doctor/consultation') !== false);
        }
    ],
    [
        'url' => '/doctor/manageAvailability', 
        'icon' => '⏱️', 
        'text' => 'Manage Availability', 
        'active_logic' => function($url) {
            return (strpos($url, 'doctor/manageAvailability') !== false);
        }
    ],
    [
        'url' => '/doctor/patientList', 
        'icon' => '👥', 
        'text' => 'Patient List', 
        'active_logic' => function($url) {
            return (strpos($url, 'doctor/patientList') !== false);
        }
    ],
    [   // <<<< MỤC MỚI CHO DOCTOR >>>>
        'url' => '/doctor/myLeaveRequests', 
        'icon' => '✈️', 
        'text' => 'My Leave Requests', 
        'active_logic' => function($url) {
            // Active khi ở trang danh sách hoặc trang form request mới
            return (strpos($url, 'doctor/myLeaveRequests') !== false || strpos($url, 'doctor/requestLeave') !== false);
        }
    ],
    [
        'url' => '/doctor/notifications', 
        'icon' => '🔔', 
        'text' => 'Notifications', 
        'active_logic' => function($url) {
            return (strpos($url, 'doctor/notifications') !== false);
        }
    ],
    [
        'url' => '/doctor/updateprofile', 
        'icon' => '👤', 
        'text' => 'Update Profile', 
        'active_logic' => function($url) {
            return (strpos($url, 'doctor/updateprofile') !== false);
        }
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Doctor Dashboard'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
           width: 260px; background-color:rgb(10,46,106); color: #fff;
            padding: 25px 0; display: flex; flex-direction: column;
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a {
            display: flex; align-items: center; padding: 15px 25px; color: #bdc3c7; text-decoration: none;
            font-size: 15px; font-weight: 500; transition: all 0.2s ease; border-left: 4px solid transparent;
        }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie {
            background-color: #34495e; color: #fff; border-left-color: #3498db; /* Accent color */
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #7f8c8d; }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        .main-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .welcome-message-cutie h2 { font-size: 26px; font-weight: 600; color: #2c3e50; }
        .welcome-message-cutie p { font-size: 15px; color: #7f8c8d; margin-top: 4px; }
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

        .quick-stats-doctor-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card-doctor-cutie { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); text-align: center; }
        .stat-card-doctor-cutie h3 { font-size: 14px; font-weight: 500; color: #7f8c8d; margin-bottom: 10px; text-transform: uppercase; }
        .stat-card-doctor-cutie .stat-value-doctor-cutie { font-size: 30px; font-weight: 700; color: #3498db; }

        .content-grid-doctor-cutie { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 25px; }
        .content-panel-doctor-cutie { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
        .content-panel-doctor-cutie h3 { font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 20px; }
        
        .appointments-list-doctor-cutie ul { list-style: none; }
        .appointments-list-doctor-cutie li { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #ecf0f1; font-size: 14px; }
        .appointments-list-doctor-cutie li:last-child { border-bottom: none; }
        .appointments-list-doctor-cutie .time-patient-cutie { flex-grow: 1; }
        .appointments-list-doctor-cutie .time-cutie { font-weight: 600; color: #3498db; margin-right: 10px; }
        .appointments-list-doctor-cutie .patient-name-cutie { color: #34495e; }
        .appointments-list-doctor-cutie .reason-preview-cutie { font-size: 13px; color: #7f8c8d; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .no-appointments-msg-doctor-cutie { color: #7f8c8d; font-style: italic; padding-top: 10px; text-align: center; }

        .chart-container-cutie { position: relative; height: 280px; width: 100%; } /* For Chart.js */

        @media (max-width: 992px) { .content-grid-doctor-cutie { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { /* Sidebar responsive */ }
    </style>
</head>
<body>
<aside class="dashboard-sidebar-cutie">
    <div class="sidebar-header-cutie">
        <a href="<?php echo BASE_URL; ?>/doctor/dashboard" class="sidebar-logo-cutie">PulseCare</a>
    </div>
    <nav class="sidebar-nav-cutie">
        <ul>
            <?php foreach ($doctorSidebarMenu as $item): ?>
                <?php 
                $isActive = false;
                if (isset($item['active_logic']) && is_callable($item['active_logic'])) {
                    if ($item['text'] === 'Dashboard') {
                        $isActive = $item['active_logic']($currentControllerForMenu, $currentActionForMenu);
                    } else {
                        $isActive = $item['active_logic']($currentUrlForMenu);
                    }
                }
                ?>
                <li>
                    <a href="<?php echo BASE_URL . htmlspecialchars($item['url']); ?>" class="<?php echo $isActive ? 'active-nav-cutie' : ''; ?>" 
                       style="<?php if($isActive) { echo 'background-color: #34495e; color: #fff; border-left-color: #3498db;'; } else { echo 'color: #bdc3c7;';} ?>">
                        <span class="nav-icon-cutie"><?php echo $item['icon']; ?></span><?php echo htmlspecialchars($item['text']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="sidebar-footer-cutie" style="color: #7f8c8d;">
        © <?php echo date('Y'); ?> Healthcare System
    </div>
</aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="welcome-message-cutie"><h2>Doctor Dashboard</h2><p><?php echo htmlspecialchars($welcomeMessage); ?></p></div>
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
            <span class="profile-name">Dr.<?php echo htmlspecialchars($userFullName); ?></span>
            <i class="fas fa-caret-down dropdown-arrow"></i>
        </button>

        <!-- Menu dropdown, mặc định sẽ bị ẩn -->
        <div class="dropdown-menu hidden" id="profileDropdownMenu">
            <a href="<?php echo BASE_URL; ?>/doctor/updateprofile" class="dropdown-item">
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

        <section class="quick-stats-doctor-cutie">
            <div class="stat-card-doctor-cutie"><h3>Followed Patients</h3><div class="stat-value-doctor-cutie"><?php echo htmlspecialchars($followedPatientsCount); ?></div></div>
            <div class="stat-card-doctor-cutie"><h3>Follow-ups Due</h3><div class="stat-value-doctor-cutie"><?php echo htmlspecialchars($followUpsDueCount); ?></div></div>
            <div class="stat-card-doctor-cutie"><h3>Patient Feedbacks</h3><div class="stat-value-doctor-cutie"><?php echo htmlspecialchars($patientFeedbacksCount); ?></div></div>
            <div class="stat-card-doctor-cutie"><h3>Today's Appts</h3><div class="stat-value-doctor-cutie"><?php echo count($todaysAppointments); ?></div></div>
        </section>

        <section class="content-grid-doctor-cutie">
            <div class="content-panel-doctor-cutie appointments-list-doctor-cutie">
                <h3>Today's Appointments</h3>
                <?php if (!empty($todaysAppointments)): ?>
                    <ul>
                        <?php foreach ($todaysAppointments as $appt): ?>
                            <li>
                                <div class="time-patient-cutie">
                                    <span class="time-cutie"><?php echo htmlspecialchars(date("h:i A", strtotime($appt['AppointmentDateTime']))); ?></span>
                                    <span class="patient-name-cutie"><?php echo htmlspecialchars($appt['PatientName']); ?></span>
                                </div>
                                <span class="reason-preview-cutie" title="<?php echo htmlspecialchars($appt['ReasonForVisit'] ?? ''); ?>"><?php echo htmlspecialchars(substr($appt['ReasonForVisit'] ?? 'N/A', 0, 25) . (strlen($appt['ReasonForVisit'] ?? '') > 25 ? '...' : '')); ?></span>
                                <!-- Add link to EMR for this appointment -->
                                <a href="<?php echo BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appt['AppointmentID']; ?>" style="font-size:13px; color:#3498db; text-decoration:none; margin-left:10px;">Details</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-appointments-msg-doctor-cutie">No appointments scheduled for today,  <?php echo htmlspecialchars($_SESSION['user_fullname'] ?? ''); ?>. Enjoy your day!</p>
                <?php endif; ?>
            </div>
            <div class="content-panel-doctor-cutie">
                <h3>Weekly/Monthly Overview</h3>
                <div class="chart-container-cutie"><canvas id="appointmentOverviewChart"></canvas></div>
            </div>
        </section>
    </main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('appointmentOverviewChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line', // or 'bar'
            data: {
                labels: <?php echo json_encode($appointmentOverviewData['labels']); ?>,
                datasets: [{
                    label: 'Appointments',
                    data: <?php echo json_encode($appointmentOverviewData['counts']); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } // Ensure y-axis shows whole numbers for counts
            }
        });
    }
});
</script>
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
</body>
</html>