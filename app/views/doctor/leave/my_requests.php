<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}

$currentUrlForMenu = $_GET['url'] ?? '';
$pageTitle = $data['title'] ?? 'My Leave Requests';
$leaveRequests = $data['leaveRequests'] ?? [];
$currentStatusFilter = $data['currentStatusFilter'] ?? 'All';
$allStatuses = $data['allStatuses'] ?? ['All', 'Pending', 'Approved', 'Rejected', 'Cancelled'];
$csrfToken = $_SESSION['csrf_token'] ?? '';

$doctorSidebarMenu = [ /* ... định nghĩa menu như trên ... */ ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Doctor Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS chung, sidebar, main-content, content-header giữ nguyên như các view Doctor khác */
        /* ... (copy CSS từ file request_form.php hoặc file view Doctor khác) ... */
        /* Thêm CSS cho filter bar và data table nếu cần (tương tự trang listAppointments của Nurse) */

        .filter-bar-cutie { /* Style cho filter bar */
            background-color: #fff; padding: 20px; border-radius: 10px;
            margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .filter-bar-cutie .form-inline-cutie { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 15px; }
        .filter-bar-cutie .form-group-cutie { display: flex; flex-direction: column; }
        .filter-bar-cutie label { margin-bottom: 5px; font-weight: 500; font-size: 14px; color: #495057; }
        .filter-bar-cutie .form-control-cutie {
            padding: 10px 12px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; min-width: 200px;
        }
        .filter-bar-cutie .button-filter-cutie {
            background-color: #10ac84; color: white; border: none;
            padding: 10px 20px; border-radius: 6px; font-size: 14px;
            cursor: pointer; font-weight: 500; height: 40px;
        }

        .data-table-container-cutie { /* Style cho container bảng */
            background-color: #fff; padding: 25px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .data-table-container-cutie .table-title-action-cutie {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;
        }
        .data-table-container-cutie h3.table-title-cutie {
            font-size: 1.3em; font-weight: 600; color: #0a783c; margin: 0;
        }
        .button-add-new-cutie { /* Nút "Request New Leave" */
            background-color: #10ac84; color: white; text-decoration: none;
            padding: 10px 18px; border-radius: 6px; font-size: 14px; font-weight: 500;
            transition: background-color 0.2s ease;
        }
        .button-add-new-cutie:hover { background-color: #0a783c; }

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
        .status-pending { background-color: #f39c12; } /* Orange */
        .status-approved { background-color: #2ecc71; } /* Green */
        .status-rejected { background-color: #e74c3c; } /* Red */
        .status-cancelled { background-color: #95a5a6; } /* Gray */

        .action-button-cutie.cancel-leave { background-color: #e74c3c; }
        .action-button-cutie.cancel-leave:hover { background-color: #c0392b; }
        .action-button-cutie:disabled { background-color: #bdc3c7; cursor: not-allowed; }
        
        .message-feedback { /* Style cho thông báo session */
             padding: 15px 20px; margin-bottom: 20px; border-radius: 8px; 
            font-size: 15px; text-align: center; font-weight: 500;
            border-left-width: 5px; border-left-style: solid;
        }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; }

    </style>
</head>
<body>
    <div class="page-wrapper-cutie">
        <aside class="sidebar-container-cutie">
             <div class="sidebar-header-cutie">
                <a href="<?php echo BASE_URL; ?>/doctor/dashboard" class="sidebar-logo-cutie">Doctor Panel</a>
            </div>
            <nav class="sidebar-nav-cutie">
                <ul>
                    <?php foreach ($doctorSidebarMenu as $item): ?>
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
                    <form method="GET" action="<?php echo BASE_URL; ?>/doctor/myLeaveRequests" class="form-inline-cutie">
                        <div class="form-group-cutie">
                            <label for="status">Filter by Status:</label>
                            <select id="status" name="status" class="form-control-cutie" onchange="this.form.submit()">
                                <?php foreach ($allStatuses as $statusValue): ?>
                                    <option value="<?php echo htmlspecialchars($statusValue); ?>" <?php echo ($currentStatusFilter == $statusValue) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(htmlspecialchars($statusValue)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                    </form>
                </div>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="message-feedback error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="message-feedback success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>

                <div class="data-table-container-cutie">
                    <div class="table-title-action-cutie">
                        <h3 class="table-title-cutie">Your Leave Requests</h3>
                        <a href="<?php echo BASE_URL; ?>/doctor/requestLeave" class="button-add-new-cutie">✈️ Request New Leave</a>
                    </div>
                    <div class="table-responsive-cutie">
                        <table class="table-cutie">
                            <thead>
                                <tr>
                                    <th>Requested At</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Admin Notes</th>
                                    <th>Reviewed At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($leaveRequests)): ?>
                                    <?php foreach ($leaveRequests as $request): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($request['RequestedAt']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('M j, Y', strtotime($request['StartDate']))); ?></td>
                                            <td><?php echo htmlspecialchars(date('M j, Y', strtotime($request['EndDate']))); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars(substr($request['Reason'] ?? '', 0, 50))) . (strlen($request['Reason'] ?? '') > 50 ? '...' : ''); ?></td>
                                            <td>
                                                <span class="status-label-cutie status-<?php echo htmlspecialchars(strtolower($request['Status'])); ?>">
                                                    <?php echo htmlspecialchars($request['Status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo nl2br(htmlspecialchars($request['AdminNotes'] ?? 'N/A')); ?></td>
                                            <td><?php echo $request['ReviewedAt'] ? htmlspecialchars(date('M j, Y H:i', strtotime($request['ReviewedAt']))) : 'N/A'; ?></td>
                                            <td>
                                                <?php if ($request['Status'] === 'Pending'): ?>
                                                    <form action="<?php echo BASE_URL; ?>/doctor/cancelMyLeaveRequest/<?php echo $request['LeaveRequestID']; ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel this leave request, sweetie?');" style="display:inline;">
                                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                        <input type="hidden" name="leave_request_id_to_cancel" value="<?php echo $request['LeaveRequestID']; ?>">
                                                        <button type="submit" class="action-button-cutie cancel-leave" title="Cancel Request">Cancel</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span>-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 20px; color: #777;">You haven't submitted any leave requests yet.</td>
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