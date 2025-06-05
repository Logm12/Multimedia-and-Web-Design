<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}
$currentUrl = $_GET['url'] ?? '';
// Lấy giá trị filter từ $data['current_filters'] mà controller truyền xuống
$filterDateFrom = $data['current_filters']['date_from'] ?? ''; // Để trống nếu không có
$filterDateTo = $data['current_filters']['date_to'] ?? '';   // Để trống nếu không có
$filterStatus = $data['current_filters']['status'] ?? 'All';
$allStatuses = $data['all_statuses'] ?? ['All', 'Scheduled', 'Confirmed', 'Completed', 'CancelledByPatient', 'CancelledByClinic', 'NoShow'];
$appointments = $data['appointments'] ?? [];

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
    ],
    [
        'url' => '/nurse/updateProfile', 
        'icon' => '👤', 
        'text' => 'My Profile', 
        'active_logic' => function($url) {
            return (strpos($url, 'nurse/updateProfile') !== false);
        }
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Manage Appointments'); ?> - Nurse Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS chung và sidebar giữ nguyên như trước */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; margin: 0; padding: 0; }
        .page-wrapper-cutie { display: flex; min-height: 100vh; }

        .sidebar-container-cutie {
            width: 260px; background: linear-gradient(135deg, rgba(10,57,32,1) 0%, rgba(13,142,100,1) 90%); 
            color: #ecf0f1; padding: 25px 0; display: flex; flex-direction: column;
            height: 100vh; position: fixed; top: 0; left: 0; overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.15); z-index: 1000;
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
            flex-grow: 1; padding: 30px; margin-left: 260px; 
            background-color: #f0f2f5; overflow-y: auto;
        }
        .content-header-cutie { margin-bottom: 25px; }
        .content-header-cutie h1 { font-size: 1.8em; font-weight: 600; color: #0a783c; margin: 0; }

        .filter-bar-cutie {
            background-color: #fff; padding: 20px; border-radius: 10px;
            margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .filter-bar-cutie .form-inline-cutie { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 20px; /* Tăng gap */ }
        .filter-bar-cutie .form-group-cutie { display: flex; flex-direction: column; }
        .filter-bar-cutie label { margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #495057; }
        .filter-bar-cutie .form-control-cutie {
            padding: 10px 12px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; min-width: 160px; /* Giảm min-width chút cho vừa 2 input date */
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .filter-bar-cutie .form-control-cutie:focus {
            border-color: #10ac84; box-shadow: 0 0 0 0.2rem rgba(16, 172, 132, 0.25); outline: none;
        }
        .filter-bar-cutie .button-filter-cutie {
            background-color: #10ac84; color: white; border: none;
            padding: 10px 20px; border-radius: 6px; font-size: 14px;
            cursor: pointer; font-weight: 500; transition: background-color 0.2s ease;
            height: 40px; /* Cho bằng chiều cao input */
        }
        .filter-bar-cutie .button-filter-cutie:hover { background-color: #0a783c; }
        
        /* CSS cho các phần khác giữ nguyên như trước */
        .data-table-container-cutie, .details-container-cutie, .form-container-cutie {
            background-color: #fff; padding: 25px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .data-table-container-cutie h3.table-title-cutie,
        .details-container-cutie h3.details-title-cutie,
        .form-container-cutie h3.form-title-cutie {
            font-size: 1.3em; font-weight: 600; color: #0a783c;
            margin: 0 0 20px 0; padding-bottom: 10px; border-bottom: 1px solid #eee;
        }
        .table-responsive-cutie { overflow-x: auto; }
        .table-cutie { width: 100%; border-collapse: collapse; }
        .table-cutie th, .table-cutie td {
            padding: 12px 15px; text-align: left; border-bottom: 1px solid #e9ecef;
            font-size: 14px; vertical-align: middle;
        }
        .table-cutie thead th { background-color: #f8f9fa; font-weight: 600; color: #495057; }
        .table-cutie tbody tr:hover { background-color: #f1f3f5; }
        
        .status-label-cutie {
            padding: 5px 10px; border-radius: 15px; font-size: 0.8em;
            font-weight: 500; color: #fff; text-transform: capitalize;
            display: inline-block; min-width: 80px; text-align: center;
        }
        .status-scheduled { background-color: #3498db; }
        .status-confirmed { background-color: #2ecc71; }
        .status-completed { background-color: #95a5a6; }
        .status-cancelledbypatient, .status-cancelledbyclinic { background-color: #e74c3c; }
        .status-noshow { background-color: #f39c12; }
        .status-pending { background-color: #f1c40f; color: #333;}


        .action-button-group-cutie .action-button-cutie {
            padding: 6px 10px; font-size: 13px; border-radius: 5px;
            text-decoration: none; color: #fff; border: none; cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            margin-right: 5px; display: inline-flex; align-items: center;
        }
        .action-button-group-cutie .action-button-cutie:hover { transform: translateY(-1px); }
        .action-button-group-cutie .action-button-cutie .icon-action-cutie { margin-right: 5px; }
        .action-button-cutie.details { background-color: #17a2b8; }
        .action-button-cutie.details:hover { background-color: #138496; }
        .action-button-cutie.vitals { background-color: #28a745; }
        .action-button-cutie.vitals:hover { background-color: #218838; }
        
        .info-message-cutie {
            background-color: #e0f2f1; color: #00796b;
            padding: 15px 20px; margin-bottom: 20px; border-radius: 8px;
            font-size: 15px; text-align: center; font-weight: 500;
            border-left: 5px solid #004d40;
        }

        @media (max-width: 992px) { /* Điều chỉnh cho filter bar khi màn hình nhỏ hơn */
            .filter-bar-cutie .form-inline-cutie { gap: 10px; }
            .filter-bar-cutie .form-control-cutie { min-width: 140px; }
        }
        @media (max-width: 768px) {
            .sidebar-container-cutie { width: 100%; height: auto; position: static; padding: 15px 0; }
            .main-content-area-cutie { margin-left: 0; padding: 20px 15px; }
            .page-wrapper-cutie { flex-direction: column; }
            .filter-bar-cutie .form-inline-cutie { flex-direction: column; align-items: stretch; }
            .filter-bar-cutie .form-control-cutie { min-width: 0; width: 100%; }
            .filter-bar-cutie .button-filter-cutie { width: 100%; margin-top: 10px; }
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
            $isActive = isset($item['active_logic']) && is_callable($item['active_logic']) 
                        ? $item['active_logic']($currentUrlForMenu) 
                        : false; 
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
                <h1><?php echo htmlspecialchars($data['title'] ?? 'Manage Appointments'); ?></h1>
            </section>

            <section class="content-body-cutie">
                <div class="filter-bar-cutie">
                    <form method="GET" action="<?php echo BASE_URL; ?>/nurse/listAppointments" class="form-inline-cutie">
                        <div class="form-group-cutie">
                            <label for="date_from">Date From:</label>
                            <input type="date" id="date_from" name="date_from" class="form-control-cutie" value="<?php echo htmlspecialchars($filterDateFrom); ?>">
                        </div>
                        <div class="form-group-cutie">
                            <label for="date_to">Date To:</label>
                            <input type="date" id="date_to" name="date_to" class="form-control-cutie" value="<?php echo htmlspecialchars($filterDateTo); ?>">
                        </div>
                        <div class="form-group-cutie">
                            <label for="status">Status:</label>
                            <select id="status" name="status" class="form-control-cutie">
                                <?php foreach ($allStatuses as $statusValue): ?>
                                    <option value="<?php echo htmlspecialchars($statusValue); ?>" <?php echo ($filterStatus == $statusValue) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' (Patient)', ' (Clinic)'], htmlspecialchars($statusValue))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="button-filter-cutie">Apply Filter</button>
                    </form>
                </div>
                
                <?php if (isset($_SESSION['info_message'])): ?>
                    <div class="info-message-cutie">
                        <?php echo htmlspecialchars($_SESSION['info_message']); unset($_SESSION['info_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="data-table-container-cutie">
                    <h3 class="table-title-cutie">Appointments List</h3>
                    <div class="table-responsive-cutie">
                        <table class="table-cutie">
                            <thead>
                                <tr>
                                    <th>Date & Time</th> {/* Thay đổi từ Time thành Date & Time */}
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Specialization</th>
                                    <th>Reason (Summary)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($appointments)): ?>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr>
                                            {/* Hiển thị cả ngày và giờ */}
                                            <td><?php echo htmlspecialchars(date('M j, Y H:i A', strtotime($appointment['AppointmentDateTime']))); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['PatientName']); ?></td>
                                            <td>Dr. <?php echo htmlspecialchars($appointment['DoctorName']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['SpecializationName'] ?? 'N/A'); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars(substr($appointment['ReasonForVisit'] ?? '', 0, 40))) . (strlen($appointment['ReasonForVisit'] ?? '') > 40 ? '...' : ''); ?></td>
                                            <td>
                                                <span class="status-label-cutie status-<?php echo htmlspecialchars(strtolower(str_replace(' ', '', $appointment['Status']))); ?>">
                                                    <?php echo htmlspecialchars($appointment['Status']); ?>
                                                </span>
                                            </td>
                                            <td class="action-button-group-cutie">
                                                <a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>" class="action-button-cutie details" title="View Details">
                                                    <span class="icon-action-cutie">👁️</span> Details
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>/nurse/showRecordVitalsForm/<?php echo $appointment['AppointmentID']; ?>" class="action-button-cutie vitals" title="Record/Edit Vitals">
                                                    <span class="icon-action-cutie">💓</span> Vitals
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px; color: #777;">No appointments found for the selected criteria.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
    
    <?php /* Nếu cậu muốn dùng Litepicker, thêm JS vào đây */ ?>
    <?php /*
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('date_from') && document.getElementById('date_to')) {
                new Litepicker({
                    element: document.getElementById('date_from'),
                    elementEnd: document.getElementById('date_to'),
                    singleMode: false,
                    allowRepick: true,
                    format: 'YYYY-MM-DD',
                    tooltipText: {
                        one: 'day',
                        other: 'days'
                    },
                    buttonText: {
                        previousMonth: `<!-- SVG ARROW LEFT -->`,
                        nextMonth: `<!-- SVG ARROW RIGHT -->`,
                        reset: `<span title="Clear Dates">Clear</span>`, // Hoặc icon
                        apply: `Apply`
                    },
                    setup: (picker) => {
                        picker.on('selected', (date1, date2) => {
                            // Có thể tự động submit form ở đây nếu muốn
                            // Hoặc để người dùng nhấn nút "Apply Filter"
                        });
                    }
                });
            }
        });
    </script>
    */ ?>
</body>
</html>