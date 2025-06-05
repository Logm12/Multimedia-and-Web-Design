<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}

$csrfToken = $_SESSION['csrf_token'] ?? '';
$currentUrl = $_GET['url'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Database Management'); ?> - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f0f2f5; /* Màu nền chung của page */
            color: #343a40; 
            margin: 0;
            padding: 0;
        }

        .admin-page-wrapper-cutie {
            display: flex;
            min-height: 100vh;
        }

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
        }
        
        .sidebar-header-cutie { 
            text-align: center; 
            margin-bottom: 30px; 
            padding: 0 20px; 
        }
        .sidebar-logo-cutie { 
            font-size: 22px; /* Chỉnh lại chút cho vừa */
            font-weight: 700; 
            color: #fff; 
            text-decoration: none; 
            letter-spacing: 0.5px;
        }
        .sidebar-nav-cutie ul { 
            list-style: none; 
            padding: 0;
            margin: 0; 
        }
        .sidebar-nav-cutie li a { 
            display: flex; 
            align-items: center; 
            padding: 14px 25px; /* Tăng padding chút */
            color: #dfe6e9; 
            text-decoration: none; 
            font-size: 15px; 
            font-weight: 500; 
            border-left: 4px solid transparent; 
            transition: all 0.25s ease-in-out; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: rgba(255, 255, 255, 0.1); /* Giảm độ sáng chút */
            color: #fff; 
            border-left-color: #55efc4; 
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { 
            margin-right: 15px; /* Tăng khoảng cách icon */
            font-size: 18px; 
            width: 20px; 
            text-align: center; 
            transition: transform 0.2s ease;
        }
        .sidebar-nav-cutie li a:hover .nav-icon-cutie {
            transform: scale(1.1);
        }

        .main-content-area-cutie {
            flex-grow: 1;
            padding: 30px; /* Padding cho content area */
            margin-left: 260px; 
            background-color: #f0f2f5; /* Màu nền cho content area */
            overflow-y: auto;
        }

        .container-db-management { /* Đổi tên class để tránh trùng với .container-cute nếu có ở nơi khác */
            background-color: #ffffff; 
            padding: 30px; 
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
            width: 100%; 
            max-width: 950px; 
            margin: 0 auto; 
        }

        .container-db-management h1, .container-db-management h2 {
            color: #0a783c; /* Màu xanh đậm cho tiêu đề */
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .container-db-management h1 { font-size: 2em; }
        .container-db-management h2 { font-size: 1.6em; margin-top: 30px; }

        .button-action { /* Class chung cho các nút hành động */
            color: white;
            border: none;
            padding: 10px 20px; /* Giảm padding chút */
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 15px;
            margin: 5px 3px;
            cursor: pointer;
            border-radius: 8px; /* Bo góc vuông hơn chút */
            transition: background-color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .button-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .button-action.create-backup { background-color: #10ac84; /* Xanh lá cây */ }
        .button-action.create-backup:hover { background-color: #0a783c; }
        .button-action.restore { background-color: #ff9f43; /* Cam */ }
        .button-action.restore:hover { background-color: #e67e22; }
        .button-action.delete { background-color: #ee5253; /* Đỏ */ }
        .button-action.delete:hover { background-color: #d63031; }
        .button-action:disabled {
            background-color: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }


        .backup-list { list-style: none; padding: 0; margin-top: 20px; }
        .backup-list li {
            background-color: #e8f5e9; /* Xanh lá rất nhạt */
            border: 1px solid #c8e6c9;
            padding: 18px 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            transition: box-shadow 0.2s ease;
        }
        .backup-list li:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .backup-info span { display: block; margin-bottom: 6px; color: #388e3c; /* Xanh lá đậm hơn cho text */ font-size: 14px; }
        .backup-info strong { color: #1b5e20; font-weight: 600; } /* Xanh lá rất đậm */
        .backup-actions form { display: inline-block; margin-left: 10px; }

        .message-feedback { /* Đổi tên class để tránh trùng */
            padding: 15px 20px; 
            margin-bottom: 25px; 
            border-radius: 8px; 
            font-size: 15px; 
            text-align: center; 
            font-weight: 500;
            border-left-width: 5px;
            border-left-style: solid;
        }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; }
        
        .writable-status-info { /* Đổi tên class */
            text-align: center; 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 25px; 
            font-weight: 500;
        }
        .writable-status-info.ok { background-color: #e0f2f1; color: #00796b; }
        .writable-status-info.not-ok { background-color: #ffcdd2; color: #c62828; }

        @media (max-width: 768px) {
            .sidebar-container-cutie {
                width: 100%;
                height: auto;
                position: static;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                padding: 15px 0;
            }
            .sidebar-header-cutie { margin-bottom: 15px; }
            .sidebar-nav-cutie li a { padding: 12px 20px; font-size: 14px; }
            .main-content-area-cutie {
                margin-left: 0;
                padding: 20px 15px;
            }
            .admin-page-wrapper-cutie {
                flex-direction: column;
            }
            .container-db-management h1 { font-size: 1.6em; }
            .container-db-management h2 { font-size: 1.3em; }
            .backup-list li { flex-direction: column; align-items: stretch; padding: 15px; }
            .backup-info { margin-bottom: 10px; }
            .backup-actions { 
                margin-top: 10px; 
                width: 100%; 
                display: flex; 
                justify-content: space-around; /* Hoặc flex-end */
            }
            .backup-actions form { margin-left: 0; flex-grow: 1; text-align: center; }
            .backup-actions form .button-action { width: calc(50% - 5px); margin: 0 2.5px; }
            .button-action { padding: 10px 15px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="admin-page-wrapper-cutie">
        <aside class="sidebar-container-cutie">
            <div class="sidebar-header-cutie">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo-cutie">Admin Panel</a>
            </div>
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
                    <li><a href="<?php echo BASE_URL; ?>/admin/databaseManagement" class="<?php echo (strpos($currentUrl, 'admin/databaseManagement') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">💾</span>DB Management</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/updateProfile" class="<?php echo (strpos($currentUrl, 'admin/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>My Profile</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content-area-cutie">
            <div class="container-db-management">
                <h1><?php echo htmlspecialchars($title ?? 'Database Backup & Restore'); ?></h1>

                <?php if (isset($_SESSION['db_message_success'])): ?>
                    <div class="message-feedback success">
                        <?php echo htmlspecialchars($_SESSION['db_message_success']); unset($_SESSION['db_message_success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['db_message_error'])): ?>
                    <div class="message-feedback error">
                        <?php echo htmlspecialchars($_SESSION['db_message_error']); unset($_SESSION['db_message_error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($isWritable)): ?>
                    <div class="writable-status-info <?php echo $isWritable ? 'ok' : 'not-ok'; ?>">
                        <?php if ($isWritable): ?>
                            Backup directory (<code><?php echo htmlspecialchars($backupPath); ?></code>) is writable. Great!
                        <?php else: ?>
                            Oh dear! Backup directory (<code><?php echo htmlspecialchars($backupPath); ?></code>) is NOT writable. Please check permissions!
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div style="text-align: center; margin-bottom: 30px;">
                    <form action="<?php echo BASE_URL; ?>/admin/createBackup" method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <button type="submit" class="button-action create-backup" <?php echo (isset($isWritable) && !$isWritable) ? 'disabled' : ''; ?>>
                            ✨ Create New Backup ✨
                        </button>
                    </form>
                </div>

                <h2>Available Backups</h2>
                <?php if (!empty($backupFiles)): ?>
                    <ul class="backup-list">
                        <?php foreach ($backupFiles as $file): ?>
                            <li>
                                <div class="backup-info">
                                    <span><strong>File:</strong> <?php echo htmlspecialchars($file['name']); ?></span>
                                    <span><strong>Size:</strong> <?php echo round($file['size'] / 1024, 2); ?> KB</span>
                                    <span><strong>Date:</strong> <?php echo date('M j, Y H:i:s', $file['date']); ?></span>
                                </div>
                                <div class="backup-actions">
                                    <form action="<?php echo BASE_URL; ?>/admin/restoreBackup" method="POST" onsubmit="return confirmRestore('<?php echo htmlspecialchars($file['name'], ENT_QUOTES); ?>');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($file['name']); ?>">
                                        <button type="submit" class="button-action restore">Restore</button>
                                    </form>
                                    <form action="<?php echo BASE_URL; ?>/admin/deleteBackup" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete this backup file: <?php echo htmlspecialchars($file['name'], ENT_QUOTES); ?>? This cannot be undone, honey!');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="backup_file_to_delete" value="<?php echo htmlspecialchars($file['name']); ?>">
                                        <button type="submit" class="button-action delete">Delete</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="text-align:center; color: #555; margin-top: 20px;">No backup files found yet. How about creating one? 😊</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function confirmRestore(fileName) {
            return confirm(
                "🚨 DANGER ZONE! 🚨\n\n" +
                "Are you absolutely, positively, 100% sure you want to restore the database from '" + fileName + "'?\n\n" +
                "⚠️ THIS WILL OVERWRITE YOUR CURRENT DATABASE! ⚠️\n" +
                "All current data will be replaced with the data from this backup.\n" +
                "This action CANNOT be undone easily.\n\n" +
                "Please double-check and make sure this is what you want!"
            );
        }
    </script>
</body>
</html>