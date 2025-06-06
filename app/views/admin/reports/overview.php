<?php
// app/views/admin/reports/overview.php

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

$pageTitleForTopbar = $data['title'] ?? 'Reports & Statistics';
$welcomeMessageForTopbar = 'Analyze system data and view insightful statistics.';
$currentUrl = $_GET['url'] ?? 'report/overview';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitleForTopbar); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .topbar-title-section-cutie h1 { font-size: 22px; font-weight: 600; color: #2c3e50; margin:0; } /* Changed to h1 for main title */
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

        .report-filter-panel-cutie { background-color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .report-filter-panel-cutie h3 { font-size: 18px; color: #34495e; margin-top: 0; margin-bottom: 15px; }
        .filter-form-grid-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px 20px; align-items: flex-end; }
        .filter-form-group-cutie label { display: block; font-size: 14px; color: #495057; margin-bottom: 6px; font-weight: 500; }
        .filter-form-group-cutie input[type="date"], .filter-form-group-cutie select { width: 100%; padding: 9px 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
        .btn-apply-filter-cutie { padding: 10px 20px; background-color: #0a783c; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; }
        .btn-apply-filter-cutie:hover { background-color: #086330; }

        .stats-overview-row-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 25px; }
        .stat-box-cutie { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center; }
        .stat-box-cutie .stat-icon-cutie { font-size: 36px; margin-bottom: 10px; opacity: 0.7; } 
        .stat-box-cutie h3 { font-size: 28px; font-weight: 700; margin: 0 0 5px 0; }
        .stat-box-cutie p { font-size: 15px; color: #7f8c8d; margin: 0; }
        .bg-aqua-cutie { background-color: #00c0ef; color: white; } .bg-aqua-cutie .stat-icon-cutie { color: rgba(0,0,0,0.15); }
        .bg-green-cutie { background-color: #00a65a; color: white; } .bg-green-cutie .stat-icon-cutie { color: rgba(0,0,0,0.15); }
        
        .charts-row-cutie { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .chart-panel-cutie { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .chart-panel-cutie h3 { font-size: 17px; font-weight: 600; color: #34495e; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #ecf0f1; padding-bottom: 10px; }
        .chart-container-report-cutie { height: 280px; }

        .data-tables-row-cutie { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .data-table-panel-cutie { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .data-table-panel-cutie h3 { font-size: 17px; font-weight: 600; color: #34495e; margin-top: 0; margin-bottom: 15px; }
        .table-responsive-admin-cutie { overflow-x: auto; }
        .table-admin-cutie { width: 100%; border-collapse: collapse; font-size: 14px; }
        .table-admin-cutie th, .table-admin-cutie td { padding: 10px 12px; border-bottom: 1px solid #ecf0f1; text-align: left; }
        .table-admin-cutie thead th { background-color: #f7f9f9; font-weight: 500; color: #495057; }
        .table-admin-cutie tbody tr:hover { background-color: #fdfdfe; }
        .no-items-msg-cutie { text-align: center; padding: 30px; color: #7f8c8d; font-style: italic; }


        @media (max-width: 992px) { .charts-row-cutie, .data-tables-row-cutie { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { 
            body { flex-direction: column; }
            .dashboard-sidebar-cutie { width: 100%; height: auto; position: static; box-shadow: none; }
            .dashboard-main-content-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h1 { font-size: 20px; }
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-cutie { padding: 20px 15px; }
            .filter-form-grid-cutie { grid-template-columns: 1fr; }
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
                <h1><?php echo htmlspecialchars($pageTitleForTopbar); ?></h1>
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
            <div class="report-filter-panel-cutie">
                <h3>Filters</h3>
                <form method="GET" action="<?php echo BASE_URL; ?>/report/overview" class="filter-form-grid-cutie">
                    <div class="filter-form-group-cutie">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($data['filterStartDate'] ?? ''); ?>">
                    </div>
                    <div class="filter-form-group-cutie">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($data['filterEndDate'] ?? ''); ?>">
                    </div>
                    <div class="filter-form-group-cutie">
                        <button type="submit" class="btn-apply-filter-cutie">Apply Filter</button>
                    </div>
                </form>
            </div>

            <section class="stats-overview-row-cutie">
                <div class="stat-box-cutie bg-aqua-cutie">
                    <div class="stat-icon-cutie">👥</div><h3><?php echo htmlspecialchars($data['newPatientsCount'] ?? 0); ?></h3><p>New Patients</p>
                </div>
                <div class="stat-box-cutie bg-green-cutie">
                    <div class="stat-icon-cutie">✔️</div><h3><?php echo htmlspecialchars($data['completedAppointmentsCount'] ?? 0); ?></h3><p>Completed Appointments</p>
                </div>
            </section>

            <section class="charts-row-cutie">
                <div class="chart-panel-cutie">
                    <h3>Appointment Statuses</h3>
                    <div class="chart-container-report-cutie"><canvas id="appointmentStatusChart"></canvas></div>
                </div>
                <div class="chart-panel-cutie">
                    <h3>Completed Appointments Trend</h3>
                    <div class="chart-container-report-cutie"><canvas id="appointmentTrendChart"></canvas></div>
                </div>
            </section>

            <section class="data-tables-row-cutie" style="margin-top: 25px;">
                 <div class="data-table-panel-cutie">
                    <h3>Appointments by Doctor (Completed)</h3>
                    <div class="table-responsive-admin-cutie">
                        <table class="table-admin-cutie">
                            <thead><tr><th>Doctor Name</th><th>Completed Count</th></tr></thead>
                            <tbody>
                                <?php
                                $appByDocData = $data['appointmentsByDoctor'] ?? []; 
                                if (!empty($appByDocData)):
                                    foreach ($appByDocData as $row): ?>
                                    <tr><td><?php echo htmlspecialchars($row['doctor_name']); ?></td><td><?php echo $row['completed_count']; ?></td></tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="2" class="no-items-msg-cutie">No data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                 <div class="data-table-panel-cutie">
                    <h3>Appointments by Specialization (Completed)</h3>
                    <div class="table-responsive-admin-cutie">
                        <table class="table-admin-cutie">
                            <thead><tr><th>Specialization</th><th>Completed Count</th></tr></thead>
                            <tbody>
                                 <?php
                                $appBySpecData = $data['appointmentsBySpecialization'] ?? []; 
                                if (!empty($appBySpecData)):
                                    foreach ($appBySpecData as $row): ?>
                                    <tr><td><?php echo htmlspecialchars($row['specialization_name']); ?></td><td><?php echo $row['completed_count']; ?></td></tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="2" class="no-items-msg-cutie">No data available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
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

    const statusCtx = document.getElementById('appointmentStatusChart');
    if (statusCtx && typeof Chart !== 'undefined') {
        const statusChartData = <?php echo json_encode($data['appointmentStatusChartData'] ?? ['labels'=>[], 'data'=>[]]); ?>;
        if (statusChartData.labels && statusChartData.labels.length > 0) {
            new Chart(statusCtx, {
                type: 'pie', data: { labels: statusChartData.labels,
                    datasets: [{ label: 'Appointments', data: statusChartData.data,
                        backgroundColor: ['#3498DB','#2ECC71','#F1C40F','#E74C3C','#9B59B6','#34495E', '#7F8C8D'],
                        hoverOffset: 4 }]
                }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
            });
        } else {
            statusCtx.getContext('2d').fillText("No data to display for Appointment Statuses.", statusCtx.width / 2 - 80, statusCtx.height / 2);
        }
    }
    const trendCtx = document.getElementById('appointmentTrendChart');
    if (trendCtx && typeof Chart !== 'undefined') {
        const trendChartData = <?php echo json_encode($data['appointmentTrendChartData'] ?? ['labels'=>[], 'data'=>[]]); ?>;
        if (trendChartData.labels && trendChartData.labels.length > 0) {
            new Chart(trendCtx, {
                type: 'line', data: { labels: trendChartData.labels,
                    datasets: [{ label: 'Completed Appointments', data: trendChartData.data, fill: false, borderColor: '#2ECC71', tension: 0.1 }]
                }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0} } } }
            });
        } else {
            trendCtx.getContext('2d').fillText("No data to display for Appointment Trends.", trendCtx.width / 2 - 80, trendCtx.height / 2);
        }
    }
});
</script>
</body>
</html>