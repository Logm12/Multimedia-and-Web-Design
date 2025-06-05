<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}

$currentUrlForMenu = $_GET['url'] ?? '';
$pageTitle = $data['title'] ?? 'Review Leave Request';
$leaveRequest = $data['leaveRequest'] ?? null;
$overlappingAvailability = $data['overlappingAvailability'] ?? [];
$overlappingAppointments = $data['overlappingAppointments'] ?? [];
$input = $data['input'] ?? ['admin_notes' => $leaveRequest['AdminNotes'] ?? ''];
$errors = $data['errors'] ?? [];
$csrfToken = $_SESSION['csrf_token'] ?? '';

if (!$leaveRequest) {
    // Xử lý nếu không có leave request (ví dụ, redirect về trang quản lý)
    $_SESSION['admin_message_error'] = "Leave request not found or invalid ID.";
    header('Location: ' . BASE_URL . '/admin/manageLeaveRequests');
    exit();
}

$adminSidebarMenu = [ /* ... định nghĩa menu admin như trên ... */ ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS chung, sidebar, main-content, content-header, breadcrumb giữ nguyên như các view Admin khác */
        /* ... (copy CSS từ file manage_requests.php hoặc file view Admin khác) ... */

        .review-container-cutie {
            background-color: #fff; padding: 30px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06); max-width: 800px; margin: 0 auto;
        }
        .review-container-cutie .review-title-cutie {
            font-size: 1.5em; font-weight: 600; color: #0a783c;
            margin: 0 0 25px 0; padding-bottom: 15px; border-bottom: 1px solid #eee; text-align:center;
        }
        
        .details-section-cutie { margin-bottom: 25px; }
        .details-section-cutie h4 {
            font-size: 1.1em; color: #0a783c; margin-bottom: 10px;
            padding-bottom: 5px; border-bottom: 1px dashed #ccc;
        }
        .detail-grid-cutie { display: grid; grid-template-columns: 1fr 2fr; gap: 8px 15px; font-size: 14px; }
        .detail-grid-cutie dt { font-weight: 600; color: #495057; }
        .detail-grid-cutie dd { color: #212529; margin-left: 0; }
        .detail-grid-cutie dd.status-pending { color: #f39c12; font-weight: bold; }
        .detail-grid-cutie dd.status-approved { color: #2ecc71; font-weight: bold; }
        .detail-grid-cutie dd.status-rejected { color: #e74c3c; font-weight: bold; }
        .detail-grid-cutie dd.status-cancelled { color: #95a5a6; font-weight: bold; }

        .overlap-warning-cutie {
            background-color: #fffbeb; border: 1px solid #fde68a; color: #b45309;
            padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;
        }
        .overlap-warning-cutie h5 { margin: 0 0 10px 0; font-size: 1.05em; }
        .overlap-warning-cutie ul { list-style: disc; margin-left: 20px; padding-left: 0; }
        .overlap-warning-cutie li { margin-bottom: 5px; }

        .form-group-cutie { margin-bottom: 20px; }
        .form-group-cutie label {
            display: block; margin-bottom: 8px; font-weight: 500;
            color: #495057; font-size: 14px;
        }
        .form-group-cutie select,
        .form-group-cutie textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ced4da;
            border-radius: 6px; font-size: 14px;
        }
        .form-group-cutie textarea { resize: vertical; min-height: 80px; }
        .error-text-cutie { color: #d63031; font-size: 0.85em; margin-top: 5px; display: block; }
        
        .form-actions-cutie {
            margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;
            display: flex; justify-content: space-between; /* Nút cancel bên trái, approve/reject bên phải */
        }
        .button-form-action-cutie {
            padding: 10px 20px; font-size: 14px; border-radius: 6px;
            text-decoration: none; color: #fff; border: none; cursor: pointer;
            font-weight: 500;
        }
        .button-form-action-cutie.approve { background-color: #28a745; }
        .button-form-action-cutie.reject { background-color: #dc3545; margin-left:10px; }
        .button-form-action-cutie.cancel { background-color: #6c757d; }
        
        .message-feedback { /* Style cho thông báo session */
             padding: 15px 20px; margin-bottom: 20px; border-radius: 8px; 
            font-size: 15px; text-align: center; font-weight: 500;
            border-left-width: 5px; border-left-style: solid;
        }
        .message-feedback.error-list ul { list-style-position: inside; padding-left: 0; margin-top: 5px; }
        .message-feedback.error-list li { margin-bottom: 3px; }
        .message-feedback.error-list, .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; }

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
                <div class="review-container-cutie">
                    <h3 class="review-title-cutie">Reviewing Leave Request #<?php echo htmlspecialchars($leaveRequest['LeaveRequestID']); ?></h3>

                    <?php if (isset($_SESSION['admin_message_error'])): ?>
                        <div class="message-feedback error"><?php echo htmlspecialchars($_SESSION['admin_message_error']); unset($_SESSION['admin_message_error']); ?></div>
                    <?php endif; ?>
                     <?php if (!empty($errors)): ?>
                        <div class="message-feedback error-list">
                            <strong>Please correct the following:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="details-section-cutie">
                        <h4>Request Details</h4>
                        <dl class="detail-grid-cutie">
                            <dt>Doctor:</dt> <dd><?php echo htmlspecialchars($leaveRequest['DoctorName']); ?></dd>
                            <dt>Start Date:</dt> <dd><?php echo htmlspecialchars(date('F j, Y', strtotime($leaveRequest['StartDate']))); ?></dd>
                            <dt>End Date:</dt> <dd><?php echo htmlspecialchars(date('F j, Y', strtotime($leaveRequest['EndDate']))); ?></dd>
                            <dt>Reason:</dt> <dd><?php echo nl2br(htmlspecialchars($leaveRequest['Reason'] ?? 'N/A')); ?></dd>
                            <dt>Current Status:</dt> <dd class="status-<?php echo strtolower($leaveRequest['Status']); ?>"><?php echo htmlspecialchars($leaveRequest['Status']); ?></dd>
                            <dt>Requested At:</dt> <dd><?php echo htmlspecialchars(date('F j, Y H:i', strtotime($leaveRequest['RequestedAt']))); ?></dd>
                            <?php if ($leaveRequest['ReviewedByAdminName']): ?>
                                <dt>Reviewed By:</dt> <dd><?php echo htmlspecialchars($leaveRequest['ReviewedByAdminName']); ?></dd>
                                <dt>Reviewed At:</dt> <dd><?php echo htmlspecialchars(date('F j, Y H:i', strtotime($leaveRequest['ReviewedAt']))); ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>

                    <?php if (!empty($overlappingAvailability) || !empty($overlappingAppointments)): ?>
                        <div class="overlap-warning-cutie">
                            <h5>⚠️ Potential Conflicts Detected!</h5>
                            <?php if (!empty($overlappingAvailability)): ?>
                                <p><strong>Overlapping Availability Slots (Working & Not Booked):</strong></p>
                                <ul>
                                    <?php foreach ($overlappingAvailability as $slot): ?>
                                        <li><?php echo date('M j, Y', strtotime($slot['AvailableDate'])); ?>: <?php echo date('H:i', strtotime($slot['StartTime'])); ?> - <?php echo date('H:i', strtotime($slot['EndTime'])); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if (!empty($overlappingAppointments)): ?>
                                <p style="margin-top:10px;"><strong>Overlapping Booked Appointments (Scheduled/Confirmed):</strong></p>
                                <ul>
                                    <?php foreach ($overlappingAppointments as $appt): ?>
                                        <li><?php echo date('M j, Y H:i A', strtotime($appt['AppointmentDateTime'])); ?> with Patient: <?php echo htmlspecialchars($appt['PatientName']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <p style="margin-top:10px;">Approving this leave may require cancelling these appointments and/or adjusting availability.</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($leaveRequest['Status'] === 'Pending'): ?>
                        <form action="<?php echo BASE_URL; ?>/admin/processLeaveReview/<?php echo $leaveRequest['LeaveRequestID']; ?>" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            <div class="details-section-cutie">
                                <h4>Admin Action</h4>
                                <div class="form-group-cutie">
                                    <label for="status">Set Status: *</label>
                                    <select name="status" id="status" class="form-control-cutie" required>
                                        <option value="">-- Select Action --</option>
                                        <option value="Approved" <?php echo (($input['status'] ?? '') === 'Approved') ? 'selected' : ''; ?>>Approve</option>
                                        <option value="Rejected" <?php echo (($input['status'] ?? '') === 'Rejected') ? 'selected' : ''; ?>>Reject</option>
                                    </select>
                                    <?php if (isset($errors['status'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['status']); ?></span><?php endif; ?>
                                </div>
                                <div class="form-group-cutie">
                                    <label for="admin_notes">Admin Notes (Optional):</label>
                                    <textarea name="admin_notes" id="admin_notes" class="form-control-cutie" rows="3"><?php echo htmlspecialchars($input['admin_notes'] ?? ''); ?></textarea>
                                    <?php if (isset($errors['admin_notes'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['admin_notes']); ?></span><?php endif; ?>
                                </div>
                            </div>
                            <div class="form-actions-cutie">
                                <a href="<?php echo BASE_URL; ?>/admin/manageLeaveRequests" class="button-form-action-cutie cancel">Back to List</a>
                                <div>
                                    <button type="submit" name="action" value="reject" class="button-form-action-cutie reject" onclick="document.getElementById('status').value='Rejected';">Reject Request</button>
                                    <button type="submit" name="action" value="approve" class="button-form-action-cutie approve" onclick="document.getElementById('status').value='Approved';">Approve Request</button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="form-actions-cutie">
                             <a href="<?php echo BASE_URL; ?>/admin/manageLeaveRequests" class="button-form-action-cutie cancel">Back to List</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>