<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}

$currentUrlForMenu = $_GET['url'] ?? ''; 

// Lấy dữ liệu từ $data mà controller truyền sang, với giá trị mặc định nếu không có
$nurseFullName = $data['currentUser']['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Nurse'); // Ưu tiên từ $data['currentUser']
$upcomingAppointments = $data['upcoming_appointments'] ?? []; // Đảm bảo là mảng
$welcomeMessage = $data['welcome_message'] ?? "Welcome to your dashboard, {$nurseFullName}!";
// $data['title'] sẽ được dùng trực tiếp trong thẻ <title> và <h1>

$nurseSidebarMenu = [
    [
        'url' => '/nurse/dashboard', 
        'icon' => '🏠', 
        'text' => 'Dashboard', 
        'active_logic' => function($url) {
            $parts = explode('/', rtrim($url, '/'));
            $controller = $parts[0] ?? '';
            $action = $parts[1] ?? 'index';
            return ($controller === 'nurse' && ($action === 'dashboard' || $action === 'index'));
        }
    ],
    [
        'url' => '/nurse/listAppointments', 
        'icon' => '🗓️', 
        'text' => 'Manage Appointments', 
        'active_logic' => function($url) {
            $checks = ['nurse/listAppointments', 'nurse/appointmentDetails', 'nurse/showRecordVitalsForm', 'nurse/saveVitals'];
            foreach ($checks as $check) {
                if (strpos($url, $check) !== false) return true;
            }
            return false;
        }
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Nurse Dashboard'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f0f2f5; 
            color: #343a40; 
            margin: 0;
            padding: 0;
        }

        .page-wrapper-cutie { display: flex; min-height: 100vh; }

        .sidebar-container-cutie {
            width: 260px; 
            background: linear-gradient(135deg, rgba(10,57,32,1) 0%, rgba(13,142,100,1) 90%); 
            color: #ecf0f1; 
            padding: 25px 0; 
            display: flex; 
            flex-direction: column;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 22px; font-weight: 700; color: #fff; text-decoration: none; letter-spacing: 0.5px; }
        .sidebar-nav-cutie ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav-cutie li a { 
            display: flex; align-items: center; padding: 14px 25px; 
            color: #dfe6e9; text-decoration: none; font-size: 15px; 
            font-weight: 500; border-left: 4px solid transparent; 
            transition: all 0.25s ease-in-out; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: rgba(255, 255, 255, 0.1); color: #fff; 
            border-left-color: #55efc4; 
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { 
            margin-right: 15px; font-size: 18px; width: 20px; 
            text-align: center; transition: transform 0.2s ease;
        }
        .sidebar-nav-cutie li a:hover .nav-icon-cutie { transform: scale(1.1); }
        .sidebar-nav-cutie li a.logout-link-cutie { margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-nav-cutie li a.logout-link-cutie:hover { background-color: rgba(231, 76, 60, 0.2); border-left-color: #e74c3c; }


        .main-content-area-cutie {
            flex-grow: 1;
            padding: 30px; 
            margin-left: 260px; 
            background-color: #f0f2f5; 
            overflow-y: auto;
        }

        .content-header-cutie {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .content-header-cutie h1 {
            font-size: 1.8em;
            font-weight: 600;
            color: #0a783c;
            margin: 0;
        }
        .content-header-cutie small {
            font-size: 0.8em;
            color: #5a6268;
            margin-left: 10px;
        }
        
        .info-message-cutie {
            background-color: #e0f2f1; color: #00796b;
            padding: 15px 20px; margin-bottom: 20px; border-radius: 8px;
            font-size: 15px; text-align: center; font-weight: 500;
            border-left: 5px solid #004d40;
        }

        .dashboard-grid-cutie {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .quick-action-card-cutie {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            text-decoration: none;
            color: #343a40;
            display: flex;
            align-items: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .quick-action-card-cutie:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        .quick-action-card-cutie .icon-bg-cutie {
            font-size: 24px;
            padding: 15px;
            border-radius: 50%;
            margin-right: 15px;
            color: #fff;
        }
        .quick-action-card-cutie .icon-bg-cutie.appointments { background-color: #3498db; }
        .quick-action-card-cutie .icon-bg-cutie.profile { background-color: #9b59b6; }
        .quick-action-card-cutie .text-content-cutie h4 {
            font-size: 1.1em;
            font-weight: 600;
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        .quick-action-card-cutie .text-content-cutie p {
            font-size: 0.9em;
            color: #7f8c8d;
            margin: 0;
        }

        .data-table-container-cutie {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .data-table-container-cutie h3.table-title-cutie {
            font-size: 1.3em;
            font-weight: 600;
            color: #0a783c;
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .table-responsive-cutie { overflow-x: auto; }
        .table-cutie {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .table-cutie th, .table-cutie td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
            vertical-align: middle;
        }
        .table-cutie thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .table-cutie tbody tr:hover { background-color: #f1f3f5; }
        .status-label-cutie {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 500;
            color: #fff;
            text-transform: capitalize;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }
        .status-scheduled { background-color: #3498db; }
        .status-confirmed { background-color: #2ecc71; }
        .status-completed { background-color: #95a5a6; }
        .status-cancelledbypatient, .status-cancelledbyclinic { background-color: #e74c3c; }
        .status-noshow { background-color: #f39c12; }
        
        .action-button-cutie {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
        }
        .action-button-cutie .icon-action-cutie { margin-right: 5px; }
        .action-button-cutie.details { background-color: #17a2b8; }
        .action-button-cutie.details:hover { background-color: #138496; }
        .action-button-cutie.vitals { background-color: #28a745; }
        .action-button-cutie.vitals:hover { background-color: #218838; }


        @media (max-width: 768px) {
            .sidebar-container-cutie { width: 100%; height: auto; position: static; padding: 15px 0; }
            .sidebar-header-cutie { margin-bottom: 15px; }
            .sidebar-nav-cutie li a { padding: 12px 20px; font-size: 14px; }
            .main-content-area-cutie { margin-left: 0; padding: 20px 15px; }
            .page-wrapper-cutie { flex-direction: column; }
            .content-header-cutie h1 { font-size: 1.5em; }
            .dashboard-grid-cutie { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper-cutie">
        <aside class="sidebar-container-cutie">
            <div class="sidebar-header-cutie">
                <a href="<?php echo BASE_URL; ?>/nurse/dashboard" class="sidebar-logo-cutie">Nurse Panel</a>
            </div>
<nav class="sidebar-nav-cutie">
    <ul>
        <?php foreach ($nurseSidebarMenu as $item): ?>
            <?php 
            $isActive = false;
            if (isset($item['active_logic']) && is_callable($item['active_logic'])) {
                // Truyền các tham số cần thiết cho hàm active_logic
                if ($item['text'] === 'Dashboard') { // Logic đặc biệt cho Dashboard
                    $isActive = $item['active_logic']($currentControllerForMenu, $currentActionForMenu);
                } else { // Logic chung cho các mục khác
                    $isActive = $item['active_logic']($currentUrlForMenu);
                }
            }
            ?>
            <li>
                <a href="<?php echo BASE_URL . htmlspecialchars($item['url']); ?>" class="<?php echo $isActive ? 'active-nav-cutie' : ''; ?>">
                    <span class="nav-icon-cutie"><?php echo $item['icon']; ?></span><?php echo htmlspecialchars($item['text']); ?>
                </a>
            </li>
        <?php endforeach; ?>
        <li>
            <a href="<?php echo BASE_URL; ?>/auth/logout" class="logout-link-cutie">
                <span class="nav-icon-cutie">🚪</span>Logout
            </a>
        </li>
    </ul>
</nav>
        </aside>

        <main class="main-content-area-cutie">
            <section class="content-header-cutie">
                <h1>
                    <?php echo htmlspecialchars($data['title'] ?? 'Nurse Dashboard'); ?>
                    <small><?php echo htmlspecialchars($welcomeMessage); ?></small>
                </h1>
            </section>

            <section class="content-body-cutie">
                <?php if (isset($_SESSION['info_message'])): ?>
                    <div class="info-message-cutie">
                        <?php echo htmlspecialchars($_SESSION['info_message']); unset($_SESSION['info_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard-grid-cutie">
                    <a href="<?php echo BASE_URL; ?>/nurse/listAppointments" class="quick-action-card-cutie">
                        <span class="icon-bg-cutie appointments">🗓️</span>
                        <div class="text-content-cutie">
                            <h4>Manage Appointments</h4>
                            <p>View and manage patient appointments.</p>
                        </div>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/nurse/updateProfile" class="quick-action-card-cutie">
                        <span class="icon-bg-cutie profile">👤</span>
                        <div class="text-content-cutie">
                            <h4>My Profile</h4>
                            <p>Update your personal information.</p>
                        </div>
                    </a>
                </div>

                <div class="data-table-container-cutie">
                    <h3 class="table-title-cutie">Upcoming Appointments (<?php echo count($upcomingAppointments); ?>)</h3>
                    <div class="table-responsive-cutie">
                        <table class="table-cutie">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Specialization</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($upcomingAppointments)): ?>
                                    <?php foreach ($upcomingAppointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date('M j, Y H:i A', strtotime($appointment['AppointmentDateTime']))); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['PatientName']); ?></td>
                                            <td>Dr. <?php echo htmlspecialchars($appointment['DoctorName']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['SpecializationName'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="status-label-cutie status-<?php echo htmlspecialchars(strtolower(str_replace(' ', '', $appointment['Status']))); ?>">
                                                    <?php echo htmlspecialchars($appointment['Status']); ?>
                                                </span>
                                            </td>
                                            <td class="action-button-group-cutie">
                                                <a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>" class="action-button-cutie details" title="View Details"><span class="icon-action-cutie">👁️</span>Details</a>
                                                <a href="<?php echo BASE_URL; ?>/nurse/showRecordVitalsForm/<?php echo $appointment['AppointmentID']; ?>" class="action-button-cutie vitals" title="Record/Edit Vitals"><span class="icon-action-cutie">💓</span>Vitals</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 20px; color: #777;">No upcoming appointments found for your assigned doctors.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>