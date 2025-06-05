<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Doctor') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}

$currentUrlForMenu = $_GET['url'] ?? '';
$pageTitle = $data['title'] ?? 'Request Time Off';
$input = $data['input'] ?? [];
$errors = $data['errors'] ?? [];
$csrfToken = $_SESSION['csrf_token'] ?? ''; // Lấy CSRF token từ session

// Giả sử $doctorSidebarMenu được định nghĩa ở đây hoặc include từ file chung
// Ví dụ:
$doctorSidebarMenu = [
    ['url' => '/doctor/dashboard', 'icon' => '🏠', 'text' => 'Dashboard', 'active_logic' => function($url) { /* ... */ }],
    ['url' => '/doctor/mySchedule', 'icon' => '🗓️', 'text' => 'My Schedule', 'active_logic' => function($url) { /* ... */ }],
    ['url' => '/doctor/manageAvailability', 'icon' => '⏰', 'text' => 'Manage Availability', 'active_logic' => function($url) { /* ... */ }],
    ['url' => '/doctor/patientList', 'icon' => '👥', 'text' => 'Patient List', 'active_logic' => function($url) { /* ... */ }],
    ['url' => '/doctor/myLeaveRequests', 'icon' => '✈️', 'text' => 'My Leave Requests', 'active_logic' => function($url) {
        return (strpos($url, 'doctor/myLeaveRequests') !== false || strpos($url, 'doctor/requestLeave') !== false);
    }],
    ['url' => '/doctor/notifications', 'icon' => '🔔', 'text' => 'Notifications', 'active_logic' => function($url) { /* ... */ }],
    ['url' => '/doctor/updateProfile', 'icon' => '👤', 'text' => 'My Profile', 'active_logic' => function($url) { /* ... */ }],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Doctor Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php /* Cậu có thể thêm link tới thư viện Datepicker nếu muốn, ví dụ Litepicker */ ?>
    <?php /* <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/> */ ?>
    <style>
        /* CSS chung, sidebar, main-content, content-header, breadcrumb giữ nguyên như các view Doctor khác */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; margin: 0; padding: 0; }
        .page-wrapper-cutie { display: flex; min-height: 100vh; }

        .sidebar-container-cutie { /* Style sidebar như đã thống nhất */
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
        .content-header-cutie { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .content-header-cutie h1 { font-size: 1.8em; font-weight: 600; color: #0a783c; margin: 0; }
        
        .form-container-cutie {
            background-color: #fff; padding: 30px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06); max-width: 700px; margin: 0 auto;
        }
        .form-container-cutie .form-title-cutie {
            font-size: 1.5em; font-weight: 600; color: #0a783c;
            margin: 0 0 25px 0; padding-bottom: 15px; border-bottom: 1px solid #eee; text-align:center;
        }
        .form-group-cutie { margin-bottom: 20px; }
        .form-group-cutie label {
            display: block; margin-bottom: 8px; font-weight: 500;
            color: #495057; font-size: 14px;
        }
        .form-group-cutie input[type="date"],
        .form-group-cutie textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ced4da;
            border-radius: 6px; font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-group-cutie input[type="date"]:focus,
        .form-group-cutie textarea:focus {
            border-color: #10ac84; box-shadow: 0 0 0 0.2rem rgba(16, 172, 132, 0.25); outline: none;
        }
        .form-group-cutie textarea { resize: vertical; min-height: 100px; }
        .error-text-cutie { color: #d63031; font-size: 0.85em; margin-top: 5px; display: block; }
        
        .form-actions-cutie {
            margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;
            display: flex; justify-content: flex-end; gap: 10px;
        }
        .button-form-action-cutie {
            padding: 10px 20px; font-size: 14px; border-radius: 6px;
            text-decoration: none; color: #fff; border: none; cursor: pointer;
            font-weight: 500; transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .button-form-action-cutie:hover { transform: translateY(-1px); }
        .button-form-action-cutie.submit { background-color: #10ac84; }
        .button-form-action-cutie.submit:hover { background-color: #0a783c; }
        .button-form-action-cutie.cancel { background-color: #6c757d; }
        .button-form-action-cutie.cancel:hover { background-color: #5a6268; }

        .message-feedback {
            padding: 15px 20px; margin-bottom: 20px; border-radius: 8px; 
            font-size: 15px; text-align: left; font-weight: 500;
            border-left-width: 5px; border-left-style: solid;
        }
        .message-feedback.error-list ul { list-style-position: inside; padding-left: 0; margin-top: 5px; }
        .message-feedback.error-list li { margin-bottom: 3px; }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error-list, .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; }
        .message-feedback.warning { background-color: #fffbeb; color: #b45309; border-left-color: #f59e0b; }


        @media (max-width: 768px) {
            .sidebar-container-cutie { width: 100%; height: auto; position: static; padding: 15px 0; }
            .main-content-area-cutie { margin-left: 0; padding: 20px 15px; }
            .page-wrapper-cutie { flex-direction: column; }
            .form-actions-cutie { flex-direction: column-reverse; gap: 10px; } /* Nút submit ở dưới trên mobile */
            .form-actions-cutie .button-form-action-cutie { width: 100%; }
        }
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
                <div class="form-container-cutie">
                    <h3 class="form-title-cutie">Submit Your Leave Request</h3>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="message-feedback error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="message-feedback success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['leave_request_warning'])): ?>
                        <div class="message-feedback warning"><?php echo htmlspecialchars($_SESSION['leave_request_warning']); unset($_SESSION['leave_request_warning']); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="message-feedback error-list">
                            <strong>Oops! Please fix these little things:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo BASE_URL; ?>/doctor/submitLeaveRequest" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        
                        <div class="form-group-cutie">
                            <label for="start_date">Start Date: *</label>
                            <input type="date" class="form-control-cutie" id="start_date" name="start_date" value="<?php echo htmlspecialchars($input['start_date'] ?? ''); ?>" required>
                            <?php if (isset($errors['start_date'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['start_date']); ?></span><?php endif; ?>
                        </div>

                        <div class="form-group-cutie">
                            <label for="end_date">End Date: *</label>
                            <input type="date" class="form-control-cutie" id="end_date" name="end_date" value="<?php echo htmlspecialchars($input['end_date'] ?? ''); ?>" required>
                            <?php if (isset($errors['end_date'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['end_date']); ?></span><?php endif; ?>
                        </div>

                        <div class="form-group-cutie">
                            <label for="reason">Reason (Optional):</label>
                            <textarea class="form-control-cutie" id="reason" name="reason" rows="4"><?php echo htmlspecialchars($input['reason'] ?? ''); ?></textarea>
                            <?php if (isset($errors['reason'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['reason']); ?></span><?php endif; ?>
                        </div>
                        
                        <div class="form-actions-cutie">
                            <a href="<?php echo BASE_URL; ?>/doctor/myLeaveRequests" class="button-form-action-cutie cancel">Cancel</a>
                            <button type="submit" class="button-form-action-cutie submit">Submit Request</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
    <?php /* Cậu có thể thêm JS cho Litepicker ở đây nếu muốn */ ?>
    <?php /*
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('start_date') && document.getElementById('end_date')) {
                new Litepicker({ 
                    element: document.getElementById('start_date'),
                    elementEnd: document.getElementById('end_date'),
                    singleMode: false,
                    allowRepick: true,
                    minDate: new Date(), // Không cho chọn ngày quá khứ
                    format: 'YYYY-MM-DD',
                    numberOfMonths: 2, // Hiển thị 2 tháng
                    tooltipText: { one: 'day', other: 'days' },
                    buttonText: { apply: 'Select', cancel: 'Close' }
                });
            }
        });
    </script>
    */ ?>
</body>
</html>