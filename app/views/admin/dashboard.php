<?php
// app/views/admin/dashboard.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Admin';
// Assuming Admin might also have an avatar, or use a default admin icon
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_admin_avatar.png'; 

// Data from controller (examples, replace with actual data)
$welcomeMessage = $data['welcome_message'] ?? 'Welcome to the Admin Dashboard, ' . htmlspecialchars($userFullName) . '!';
// Stats for the chart
$userStats = $data['user_stats'] ?? ['patients' => 0, 'doctors' => 0, 'nurses' => 0];
$medicineCount = $data['medicine_count'] ?? 0;
// Data for the calendar (e.g., system events, important dates, or even a general appointment overview if admin needs it)
$calendarEvents = $data['calendar_events'] ?? [];


// Links for the main menu blocks (these will be styled as cards)
$mainMenuLinks = [
    ['url' => BASE_URL . '/admin/manageUsers', 'text' => 'Manage Users', 'icon' => '👥', 'description' => 'View, create, and manage all user accounts.'],
    ['url' => BASE_URL . '/admin/manageMedicines', 'text' => 'Manage Medicines', 'icon' => '💊', 'description' => 'Oversee the medicine catalog and stock.'],
    ['url' => BASE_URL . '/report/overview', 'text' => 'Reports & Statistics', 'icon' => '📊', 'description' => 'View system reports and analytics.'],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Admin Dashboard'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
            width: 260px; 
            /* MÀU GRADIENT MỚI CỦA CẬU ĐÂY NÈ */
            background: linear-gradient(90deg, rgba(10,57,32,1) 0%, rgba(13,142,100,1) 90%); 
            color: #ecf0f1; 
            padding: 25px 0; 
            display: flex; 
            flex-direction: column;
        }
        .sidebar-header-cutie { 
            text-align: center; 
            margin-bottom: 30px; 
            padding: 0 20px; 
        }
        .sidebar-logo-cutie { 
            font-size: 24px; 
            font-weight: 700; 
            color: #fff; 
            text-decoration: none; 
        }
        .sidebar-nav-cutie ul { 
            list-style: none; 
            padding: 0;
            margin: 0; 
        }
        .sidebar-nav-cutie li a { 
            display: flex; 
            align-items: center; 
            padding: 15px 25px; 
            color: #dfe6e9; /* Màu chữ hơi sáng hơn cho dễ đọc trên gradient */
            text-decoration: none; 
            font-size: 15px; 
            font-weight: 500; 
            border-left: 4px solid transparent; 
            transition: all 0.2s ease; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: rgba(255, 255, 255, 0.15); /* Nền hơi sáng hơn khi hover/active */
            color: #fff; 
            border-left-color: #55efc4; /* Màu nhấn xanh mint sáng cho active (tương phản) */
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { 
            margin-right: 12px; 
            font-size: 18px; 
            width: 20px; 
            text-align: center; 
        }
        .sidebar-footer-cutie { 
            margin-top: auto; 
            padding: 20px 25px; 
            text-align: center; 
            font-size: 13px; 
            color: #bdc3c7; /* Màu chữ cho footer */
        }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        .main-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .welcome-message-cutie h2 { font-size: 26px; font-weight: 600; color: #2c3e50; }
        .welcome-message-cutie p { font-size: 15px; color: #7f8c8d; margin-top: 4px; }
        .user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .user-actions-cutie .icon-button-cutie { background: none; border: none; font-size: 22px; color: #7f8c8d; cursor: pointer; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #0a783c; }

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
        #adminCalendar { min-height: 450px; } /* Ensure calendar has enough height */
        
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        @media (max-width: 992px) { .admin-overview-grid-cutie { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { /* Sidebar responsive */ }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">HealthSys</a></div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/dashboard') !== false && strpos($_GET['url'] ?? '', 'admin/dashboard') === (strlen($_GET['url'] ?? '') - strlen('admin/dashboard'))) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listUsers" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/listUsers') !== false || strpos($_GET['url'] ?? '', 'admin/createUser') !== false || strpos($_GET['url'] ?? '', 'admin/editUser') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👥</span>Manage Users</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageSpecializations" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/manageSpecializations') !== false || strpos($_GET['url'] ?? '', 'admin/editSpecialization') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏷️</span>Specializations</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listMedicines" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/listMedicines') !== false || strpos($_GET['url'] ?? '', 'admin/createMedicine') !== false || strpos($_GET['url'] ?? '', 'admin/editMedicine') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">💊</span>Manage Medicines</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listAllAppointments" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/listAllAppointments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>All Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/report/overview" class="<?php echo (strpos($_GET['url'] ?? '', 'report/overview') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📊</span>Reports</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageLeaveRequests" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/manageLeaveRequests') !== false || strpos($_GET['url'] ?? '', 'admin/reviewLeaveRequest') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">✈️</span>Leave Requests</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageFeedbacks" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/manageFeedbacks') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⭐</span>Patient Feedbacks</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/databaseManagement" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/databaseManagement') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">💾</span>DB Management</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/updateProfile" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>My Profile</a></li>
                <!-- <li><a href="<?php echo BASE_URL; ?>/admin/systemSettings" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/systemSettings') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⚙️</span>System Settings</a></li> -->
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="welcome-message-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Admin Dashboard'); ?></h2><p><?php echo htmlspecialchars($welcomeMessage); ?></p></div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Admin Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

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
    </main>
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const statsCtx = document.getElementById('systemStatsChart');
        if (statsCtx) {
            new Chart(statsCtx, {
                type: 'bar',
                data: {
                    labels: ['Patients', 'Doctors', 'Nurses', 'Medicines'],
                    datasets: [{
                        label: 'Total Count',
                        data: [
                            <?php echo (int)($data['user_stats']['patients'] ?? 0); ?>, 
                            <?php echo (int)($data['user_stats']['doctors'] ?? 0); ?>, 
                            <?php echo (int)($data['user_stats']['nurses'] ?? 0); ?>,
                            <?php echo (int)($data['medicine_count'] ?? 0); ?>
                        ],
                        backgroundColor: ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c'],
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
            });
        }

        const calendarEl = document.getElementById('adminCalendar');
        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
                events: <?php echo json_encode($data['calendar_events'] ?? []); ?>
                // Add more FullCalendar options as needed for Admin
            });
            calendar.render();
        }
    });
    </script>
</body>
</html>