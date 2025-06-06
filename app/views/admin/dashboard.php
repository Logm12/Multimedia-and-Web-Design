<?php
// app/views/admin/dashboard.php

if (!defined('BASE_URL')) { 
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
}
if (session_status() == PHP_SESSION_NONE) session_start();


$topbarUserFullName = $_SESSION['user_fullname'] ?? 'Admin';
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_admin_avatar.png'; 
if (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar'])) {
    $sessionAvatarPath = $_SESSION['user_avatar'];
    if (file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($sessionAvatarPath, '/'))) {
        $topbarUserAvatar = BASE_URL . '/' . ltrim($sessionAvatarPath, '/');
    }
}

$pageTitleForTopbar = $data['title'] ?? 'Admin Dashboard';
$welcomeMessageForTopbar = $data['welcome_message'] ?? 'Welcome to the Admin Dashboard, ' . htmlspecialchars($topbarUserFullName) . '!';

$userStats = $data['user_stats'] ?? ['patients' => 0, 'doctors' => 0, 'nurses' => 0];
$medicineCount = $data['medicine_count'] ?? 0;
$calendarEvents = $data['calendar_events'] ?? [];

$mainMenuLinks = [
    ['url' => BASE_URL . '/admin/listUsers', 'text' => 'Manage Users', 'icon' => '👥', 'description' => 'View, create, and manage all user accounts.'],
    ['url' => BASE_URL . '/admin/manageMedicines', 'text' => 'Manage Medicines', 'icon' => '💊', 'description' => 'Oversee the medicine catalog and stock.'],
    ['url' => BASE_URL . '/report/overview', 'text' => 'Reports & Statistics', 'icon' => '📊', 'description' => 'View system reports and analytics.'],
];
$currentUrl = $_GET['url'] ?? 'admin/dashboard';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitleForTopbar); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
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
        .sidebar-nav-cutie ul { list-style: none; padding: 0; margin: 0; }
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

        .admin-menu-grid-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 30px; }
        .menu-card-admin-cutie {
            background-color: #fff; padding: 25px; border-radius: 10px; text-decoration: none; color: inherit;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06); display: flex; flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .menu-card-admin-cutie:hover { transform: translateY(-5px); box-shadow: 0 6px 16px rgba(0,0,0,0.1); }
        .menu-card-admin-cutie .menu-icon-admin-cutie { font-size: 36px; color: #3498db; margin-bottom: 15px; }
        .menu-card-admin-cutie h3 { font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 8px; }
        .menu-card-admin-cutie p { font-size: 14px; color: #7f8c8d; line-height: 1.6; flex-grow: 1; }

        .admin-overview-grid-cutie { display: grid; grid-template-columns: 1fr 1.5fr; gap: 25px; }
        .content-panel-admin-cutie { background-color: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
        .content-panel-admin-cutie h3 { font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 20px; }
        .chart-container-admin-cutie { position: relative; height: 300px; width: 100%; }
        #adminCalendar { min-height: 450px; } 
        
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        @media (max-width: 992px) { .admin-overview-grid-cutie { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { 
            .dashboard-sidebar-cutie { width: 100%; height: auto; position: static; box-shadow: none; }
            .dashboard-main-content-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h2 { font-size: 20px; }
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
        }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo-cutie">HealthSys</a></div>
<nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="<?php echo (strpos($currentUrl, 'admin/dashboard') !== false && strpos($currentUrl, 'admin/dashboard') === (strlen($currentUrl) - strlen('admin/dashboard'))) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listUsers" class="<?php echo (strpos($currentUrl, 'admin/listUsers') !== false || strpos($currentUrl, 'admin/createUser') !== false || strpos($currentUrl, 'admin/editUser') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👥</span>Manage Users</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageSpecializations" class="<?php echo (strpos($currentUrl, 'admin/manageSpecializations') !== false || strpos($currentUrl, 'admin/editSpecialization') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏷️</span>Specializations</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listMedicines" class="<?php echo (strpos($currentUrl, 'admin/listMedicines') !== false || strpos($currentUrl, 'admin/createMedicine') !== false || strpos($currentUrl, 'admin/editMedicine') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">💊</span>Manage Medicines</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listAllAppointments" class="<?php echo (strpos($currentUrl, 'admin/listAllAppointments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>All Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/report/overview" class="<?php echo (strpos($currentUrl, 'report/overview') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📊</span>Reports</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageLeaveRequests" class="<?php echo (strpos($currentUrl, 'admin/manageLeaveRequests') !== false || strpos($currentUrl, 'admin/reviewLeaveRequest') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">✈️</span>Leave Requests</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageFeedbacks" class="<?php echo (strpos($currentUrl, 'admin/manageFeedbacks') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⭐</span>Patient Feedbacks</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageDoctorNurseAssignments" class="<?php echo (strpos($currentUrl, 'admin/manageDoctorNurseAssignments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🔗</span>Doctor-Nurse Assign</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/updateProfile" class="<?php echo (strpos($currentUrl, 'admin/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>My Profile</a></li>
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

        <div class="actual-page-content-cutie">
            <?php if (isset($_SESSION['admin_message_success'])): ?>
                <p class="message-cutie success-message"><?php echo $_SESSION['admin_message_success']; unset($_SESSION['admin_message_success']); ?></p>
            <?php endif; ?>
            
            <section class="admin-menu-grid-cutie">
                <?php foreach ($mainMenuLinks as $link): ?>
                    <a href="<?php echo htmlspecialchars($link['url']); ?>" class="menu-card-admin-cutie">
                        <div class="menu-icon-admin-cutie"><?php echo $link['icon']; ?></div>
                        <h3><?php echo htmlspecialchars($link['text']); ?></h3>
                        <p><?php echo htmlspecialchars($link['description']); ?></p>
                    </a>
                <?php endforeach; ?>
            </section>

            <section class="admin-overview-grid-cutie">
                <div class="content-panel-admin-cutie">
                    <h3>System Overview</h3>
                    <div class="chart-container-admin-cutie"><canvas id="systemStatsChart"></canvas></div>
                </div>
                <div class="content-panel-admin-cutie">
                    <h3>System Calendar / Events</h3>
                    <div id='adminCalendar'></div>
                </div>
            </section>
        </div>
    </main>
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
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

        const statsCtx = document.getElementById('systemStatsChart');
        if (statsCtx) {
            new Chart(statsCtx, {
                type: 'bar',
                data: {
                    labels: ['Patients', 'Doctors', 'Nurses', 'Medicines'],
                    datasets: [{
                        label: 'Total Count',
                        data: [
                            <?php echo (int)($userStats['patients'] ?? 0); ?>, 
                            <?php echo (int)($userStats['doctors'] ?? 0); ?>, 
                            <?php echo (int)($userStats['nurses'] ?? 0); ?>,
                            <?php echo (int)($medicineCount ?? 0); ?>
                        ],
                        backgroundColor: ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c'],
                        borderRadius: 4,
                        barThickness: 30,
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            ticks: { 
                                stepSize: 1, 
                                precision: 0 
                            } 
                        },
                        x: { grid: { display: false } }
                    }, 
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y;
                                }
                            }
                        }
                    } 
                }
            });
        }

        const calendarEl = document.getElementById('adminCalendar');
        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
                events: <?php echo json_encode($calendarEvents ?? []); ?>,
                dayMaxEvents: true, 
                eventColor: '#0a783c',
                height: 'auto'
            });
            calendar.render();
        }
    });
    </script>
</body>
</html>