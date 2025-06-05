<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}

$currentUrlForMenu = $_GET['url'] ?? '';
$pageTitle = $data['title'] ?? 'Manage Leave Requests';
$leaveRequests = $data['leaveRequests'] ?? [];
$doctorsForFilter = $data['doctors'] ?? []; // Danh sách bác sĩ để lọc
$currentFilters = $data['currentFilters'] ?? [];
$allStatuses = $data['allStatuses'] ?? ['All', 'Pending', 'Approved', 'Rejected', 'Cancelled'];

// Giả sử $adminSidebarMenu được định nghĩa ở đây hoặc include từ file chung
$adminSidebarMenu = [ /* ... định nghĩa menu admin như các view admin khác ... */ ];
// Ví dụ, đảm bảo có mục active cho leave management
// [
//     'url' => '/admin/manageLeaveRequests', 
//     'icon' => '🌴', 
//     'text' => 'Leave Requests', 
//     'active_logic' => function($url) {
//         return (strpos($url, 'admin/manageLeaveRequests') !== false || strpos($url, 'admin/reviewLeaveRequest') !== false);
//     }
// ],
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS chung, sidebar, main-content, content-header giữ nguyên như các view Admin khác */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; margin: 0; padding: 0; }
        .page-wrapper-cutie { display: flex; min-height: 100vh; }

        .sidebar-container-cutie { /* Style sidebar như đã thống nhất cho Admin */
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

        .filter-bar-cutie { /* Style cho filter bar */
            background-color: #fff; padding: 20px; border-radius: 10px;
            margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .filter-bar-cutie .form-inline-cutie { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 15px; }
        .filter-bar-cutie .form-group-cutie { display: flex; flex-direction: column; flex-basis: 200px; /* Cho các input có độ rộng cơ bản */ }
        .filter-bar-cutie label { margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #495057; }
        .filter-bar-cutie .form-control-cutie {
            padding: 10px 12px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; width: 100%; /* Input chiếm hết độ rộng của form-group */
        }
        .filter-bar-cutie .button-filter-cutie {
            background-color: #10ac84; color: white; border: none;
            padding: 10px 20px; border-radius: 6px; font-size: 14px;
            cursor: pointer; font-weight: 500; height: 40px; align-self: flex-end; /* Căn nút với đáy input */
        }

        .data-table-container-cutie { /* Style cho container bảng */
            background-color: #fff; padding: 25px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .data-table-container-cutie h3.table-title-cutie {
            font-size: 1.3em; font-weight: 600; color: #0a783c; margin: 0 0 20px 0;
            padding-bottom: 15px; border-bottom: 1px solid #eee;
        }
        .table-responsive-cutie { overflow-x: auto; }
        .table-cutie { width: 100%; border-collapse: collapse; }
        .table-cutie th, .table-cutie td {
            padding: 12px 15px; text-align: left; border-bottom: 1px solid #e9ecef;
            font-size: 14px; vertical-align: middle;
        }
        .table-cutie thead th { background-color: #f8f9fa; font-weight: 600; color: #495057; }
        .table-cutie tbody tr:hover { background-color: #f1f3f5; }
        
        .status-label-cutie { /* Style cho nhãn trạng thái */
            padding: 5px 10px; border-radius: 15px; font-size: 0.8em;
            font-weight: 500; color: #fff; text-transform: capitalize;
            display: inline-block; min-width: 80px; text-align: center;
        }
        .status-pending { background-color: #f39c12; } 
        .status-approved { background-color: #2ecc71; }
        .status-rejected { background-color: #e74c3c; }
        .status-cancelled { background-color: #95a5a6; }

        .action-button-cutie.review { background-color: #3498db; } /* Nút Review */
        .action-button-cutie.review:hover { background-color: #2980b9; }
        .action-button-cutie { /* Style chung cho nút action trong bảng */
            padding: 6px 12px; font-size: 13px; border-radius: 5px;
            text-decoration: none; color: #fff; border: none; cursor: pointer;
            transition: background-color 0.2s ease; display: inline-flex; align-items: center;
        }
         .action-button-cutie .icon-action-cutie { margin-right: 5px; }

        .message-feedback { /* Style cho thông báo session */
             padding: 15px 20px; margin-bottom: 20px; border-radius: 8px; 
            font-size: 15px; text-align: center; font-weight: 500;
            border-left-width: 5px; border-left-style: solid;
        }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; }

        @media (max-width: 992px) {
            .filter-bar-cutie .form-group-cutie { flex-basis: calc(50% - 10px); } /* 2 cột trên tablet */
        }
        @media (max-width: 768px) {
            .sidebar-container-cutie { width: 100%; height: auto; position: static; padding: 15px 0; }
            .main-content-area-cutie { margin-left: 0; padding: 20px 15px; }
            .page-wrapper-cutie { flex-direction: column; }
            .filter-bar-cutie .form-inline-cutie { flex-direction: column; align-items: stretch; }
            .filter-bar-cutie .form-group-cutie { flex-basis: auto; }
            .filter-bar-cutie .button-filter-cutie { width: 100%; margin-top: 10px; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper-cutie">
        <aside class="sidebar-container-cutie">
            <div class="sidebar-header-cutie">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo-cutie">Admin Panel</a>
            </div>
            <nav class="sidebar-nav-cutie">
                <ul>
                    <?php /* foreach ($adminSidebarMenu as $item): ?>
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
                    <?php endforeach; */ ?>
                    <li><a href="<?php echo BASE_URL; ?>/auth/logout" class="logout-link-cutie"><span class="nav-icon-cutie">🚪</span>Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content-area-cutie">
            <section class="content-header-cutie">
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            </section>

            <section class="content-body-cutie">
                <div class="filter-bar-cutie">
                    <form method="GET" action="<?php echo BASE_URL; ?>/admin/manageLeaveRequests" class="form-inline-cutie">
                        <div class="form-group-cutie">
                            <label for="doctor_id">Doctor:</label>
                            <select id="doctor_id" name="doctor_id" class="form-control-cutie">
                                <option value="">All Doctors</option>
                                <?php foreach ($doctorsForFilter as $doctor): ?>
                                    <option value="<?php echo $doctor['DoctorID']; ?>" <?php echo (($currentFilters['doctor_id'] ?? '') == $doctor['DoctorID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($doctor['FullName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group-cutie">
                            <label for="status">Status:</label>
                            <select id="status" name="status" class="form-control-cutie">
                                <?php foreach ($allStatuses as $statusValue): ?>
                                    <option value="<?php echo htmlspecialchars($statusValue); ?>" <?php echo (($currentFilters['status'] ?? 'All') == $statusValue) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(htmlspecialchars($statusValue)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group-cutie">
                            <label for="date_from">Date From (Leave Period):</label>
                            <input type="date" id="date_from" name="date_from" class="form-control-cutie" value="<?php echo htmlspecialchars($currentFilters['date_from'] ?? ''); ?>">
                        </div>
                        <div class="form-group-cutie">
                            <label for="date_to">Date To (Leave Period):</label>
                            <input type="date" id="date_to" name="date_to" class="form-control-cutie" value="<?php echo htmlspecialchars($currentFilters['date_to'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="button-filter-cutie">Apply Filters</button>
                    </form>
                </div>

                <?php if (isset($_SESSION['admin_message_error'])): ?>
                    <div class="message-feedback error"><?php echo htmlspecialchars($_SESSION['admin_message_error']); unset($_SESSION['admin_message_error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['admin_message_success'])): ?>
                    <div class="message-feedback success"><?php echo htmlspecialchars($_SESSION['admin_message_success']); unset($_SESSION['admin_message_success']); ?></div>
                <?php endif; ?>

                <div class="data-table-container-cutie">
                    <h3 class="table-title-cutie">All Leave Requests</h3>
                    <div class="table-responsive-cutie">
                        <table class="table-cutie">
                            <thead>
                                <tr>
                                    <th>Req. ID</th>
                                    <th>Doctor</th>
                                    <th>Specialization</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($leaveRequests)): ?>
                                    <?php foreach ($leaveRequests as $request): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['LeaveRequestID']); ?></td>
                                            <td><?php echo htmlspecialchars($request['DoctorName']); ?></td>
                                            <td><?php echo htmlspecialchars($request['SpecializationName'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars(date('M j, Y', strtotime($request['StartDate']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('M j, Y', strtotime($request['EndDate']))); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars(substr($request['Reason'] ?? '', 0, 50))) . (strlen($request['Reason'] ?? '') > 50 ? '...' : ''); ?></td>
                                            <td>
                                                <span class="status-label-cutie status-<?php echo htmlspecialchars(strtolower($request['Status'])); ?>">
                                                    <?php echo htmlspecialchars($request['Status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($request['RequestedAt']))); ?></td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>/admin/reviewLeaveRequest/<?php echo $request['LeaveRequestID']; ?>" class="action-button-cutie review" title="Review Request">
                                                   <span class="icon-action-cutie">🔍</span> Review
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 20px; color: #777;">No leave requests found matching your criteria.</td>
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