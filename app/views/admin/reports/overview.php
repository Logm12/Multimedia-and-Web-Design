<?php
// app/views/admin/reports/overview.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Admin';
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_admin_avatar.png';

// $data = $data ?? [ /* ... existing dummy data ... */ ];
// $filterStartDate = $data['filterStartDate'] ?? date('Y-m-01');
// $filterEndDate = $data['filterEndDate'] ?? date('Y-m-d');
// $newPatientsCount = $data['newPatientsCount'] ?? 0;
// $completedAppointmentsCount = $data['completedAppointmentsCount'] ?? 0;
// $appointmentStatusChartData = $data['appointmentStatusChartData'] ?? ['labels' => [], 'data' => []];
// $appointmentTrendChartData = $data['appointmentTrendChartData'] ?? ['labels' => [], 'data' => []];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Reports & Statistics'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie { width: 260px; background: linear-gradient(90deg, rgba(10,57,32,1) 0%, rgba(13,142,100,1) 90%); color: #ecf0f1; padding: 25px 0; display: flex; flex-direction: column; }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a { display: flex; align-items: center; padding: 15px 25px; color: #dfe6e9; text-decoration: none; font-size: 15px; font-weight: 500; border-left: 4px solid transparent; transition: all 0.2s ease; }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie { background-color: rgba(255,255,255,0.15); color: #fff; border-left-color: #55efc4; }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #bdc3c7; }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        .main-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .page-title-cutie h1 { font-size: 26px; font-weight: 600; color: #2c3e50; margin:0; } /* Changed h2 to h1 for main page title */
        
        .user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .user-actions-cutie .icon-button-cutie { background: none; border: none; font-size: 22px; color: #7f8c8d; cursor: pointer; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #0a3920; }

        .report-filter-panel-cutie { background-color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .report-filter-panel-cutie h3 { font-size: 18px; color: #34495e; margin-top: 0; margin-bottom: 15px; }
        .filter-form-grid-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px 20px; align-items: flex-end; }
        .filter-form-group-cutie label { display: block; font-size: 14px; color: #495057; margin-bottom: 6px; font-weight: 500; }
        .filter-form-group-cutie input[type="date"], .filter-form-group-cutie select { width: 100%; padding: 9px 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
        .btn-apply-filter-cutie { padding: 10px 20px; background-color: #3498db; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; }
        .btn-apply-filter-cutie:hover { background-color: #2980b9; }

        .stats-overview-row-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 25px; }
        .stat-box-cutie { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); text-align: center; }
        .stat-box-cutie .stat-icon-cutie { font-size: 36px; margin-bottom: 10px; opacity: 0.7; } /* Placeholder for icons */
        .stat-box-cutie h3 { font-size: 28px; font-weight: 700; margin: 0 0 5px 0; }
        .stat-box-cutie p { font-size: 15px; color: #7f8c8d; margin: 0; }
        .bg-aqua-cutie { background-color: #00c0ef; color: white; } .bg-aqua-cutie .stat-icon-cutie { color: rgba(0,0,0,0.15); }
        .bg-green-cutie { background-color: #00a65a; color: white; } .bg-green-cutie .stat-icon-cutie { color: rgba(0,0,0,0.15); }
        /* Add more bg colors if needed: bg-yellow, bg-red */

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

        @media (max-width: 992px) { .charts-row-cutie, .data-tables-row-cutie { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { /* Sidebar responsive */ }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">HealthSys</a></div>
        <nav class="sidebar-nav-cutie">
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/dashboard') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listUsers" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/listUsers') !== false || strpos($_GET['url'] ?? '', 'admin/createUser') !== false || strpos($_GET['url'] ?? '', 'admin/editUser') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👥</span>Manage Users</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageSpecializations" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/manageSpecializations') !== false || strpos($_GET['url'] ?? '', 'admin/editSpecialization') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏷️</span>Specializations</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listMedicines" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/listMedicines') !== false || strpos($_GET['url'] ?? '', 'admin/createMedicine') !== false || strpos($_GET['url'] ?? '', 'admin/editMedicine') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">💊</span>Manage Medicines</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/listAllAppointments" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/listAllAppointments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>All Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/report/overview" class="<?php echo (strpos($_GET['url'] ?? '', 'report/overview') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📊</span>Reports</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageLeaveRequests" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/manageLeaveRequests') !== false || strpos($_GET['url'] ?? '', 'admin/reviewLeaveRequest') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">✈️</span>Leave Requests</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/manageFeedbacks" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/manageFeedbacks') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⭐</span>Patient Feedbacks</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/updateProfile" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>My Profile</a></li>
                <!-- Thêm các mục khác cho Admin nếu cần, ví dụ: System Settings -->
                <!-- <li><a href="<?php echo BASE_URL; ?>/admin/systemSettings" class="<?php echo (strpos($_GET['url'] ?? '', 'admin/systemSettings') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⚙️</span>System Settings</a></li> -->
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-cutie"><h1><?php echo htmlspecialchars($data['title'] ?? 'Reports Overview'); ?></h1></div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Admin Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

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
            <!-- Add more stat boxes here if needed -->
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
                            // Assuming $this->reportModel is available if called from controller, or $data['appByDocData'] if passed
                            $appByDocData = $data['appointmentsByDoctor'] ?? []; // Get from $data
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
                            $appBySpecData = $data['appointmentsBySpecialization'] ?? []; // Get from $data
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
    </main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusCtx = document.getElementById('appointmentStatusChart');
    if (statusCtx && typeof Chart !== 'undefined') {
        const statusChartData = <?php echo json_encode($data['appointmentStatusChartData'] ?? ['labels'=>[], 'data'=>[]]); ?>;
        if (statusChartData.labels.length > 0) {
            new Chart(statusCtx, {
                type: 'pie', data: { labels: statusChartData.labels,
                    datasets: [{ label: 'Appointments', data: statusChartData.data,
                        backgroundColor: ['rgba(75,192,192,0.7)','rgba(255,99,132,0.7)','rgba(255,206,86,0.7)','rgba(54,162,235,0.7)','rgba(153,102,255,0.7)','rgba(255,159,64,0.7)'],
                        hoverOffset: 4 }]
                }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
            });
        }
    }
    const trendCtx = document.getElementById('appointmentTrendChart');
    if (trendCtx && typeof Chart !== 'undefined') {
        const trendChartData = <?php echo json_encode($data['appointmentTrendChartData'] ?? ['labels'=>[], 'data'=>[]]); ?>;
        if (trendChartData.labels.length > 0) {
            new Chart(trendCtx, {
                type: 'line', data: { labels: trendChartData.labels,
                    datasets: [{ label: 'Completed Appointments', data: trendChartData.data, fill: false, borderColor: 'rgb(75,192,192)', tension: 0.1 }]
                }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1} } } }
            });
        }
    }
});
</script>
</body>
</html>