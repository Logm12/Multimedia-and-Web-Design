<?php
// app/views/admin/medicines/form.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Admin';
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_admin_avatar.png';

$isEditMode = isset($data['medicineId']) && $data['medicineId'] > 0;
$formAction = $isEditMode ? (BASE_URL . '/admin/editMedicine/' . $data['medicineId']) : (BASE_URL . '/admin/createMedicine');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? ($isEditMode ? 'Edit Medicine' : 'Add Medicine')); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reuse styles from list.php for consistency */
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
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; color: #2c3e50; }
        .user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .user-actions-cutie .icon-button-cutie { background: none; border: none; font-size: 22px; color: #7f8c8d; cursor: pointer; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #0a3920; }

        .form-container-admin-styled-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; max-width: 700px; margin: 0 auto; }
        .form-container-admin-styled-cutie fieldset { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .form-container-admin-styled-cutie legend { font-size: 18px; font-weight: 600; color: #34495e; padding: 0 10px; margin-left: 10px; }
        .form-grid-admin-styled-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px 20px; }
        .form-group-admin-styled-cutie { margin-bottom: 10px; }
        .form-group-admin-styled-cutie label { display: block; font-size: 14px; color: #495057; margin-bottom: 6px; font-weight: 500; }
        .form-group-admin-styled-cutie input[type="text"], .form-group-admin-styled-cutie input[type="number"], .form-group-admin-styled-cutie textarea {
            width: 100%; padding: 9px 12px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px;
        }
        .form-group-admin-styled-cutie textarea { min-height: 80px; resize: vertical; }
        .form-group-admin-styled-cutie input:focus, .form-group-admin-styled-cutie textarea:focus { border-color: #3498db; box-shadow: 0 0 0 0.15rem rgba(52,152,219,.25); outline: none; }
        
        .form-actions-admin-styled-cutie { margin-top: 25px; display: flex; gap: 15px; }
        .btn-submit-form-cutie, .btn-cancel-form-cutie { padding: 10px 20px; border: none; border-radius: 6px; font-size: 15px; font-weight: 500; cursor: pointer; text-decoration: none; }
        .btn-submit-form-cutie { background-color: #2ecc71; color: white; } .btn-submit-form-cutie:hover { background-color: #27ae60; }
        .btn-cancel-form-cutie { background-color: #7f8c8d; color: white; } .btn-cancel-form-cutie:hover { background-color: #6c757d; }
        
        .error-message-box-cutie { margin-bottom: 15px; padding:10px; border:1px solid #e74c3c; background-color:#fdedec; border-radius: 6px; color: #c0392b;}
        .error-message-box-cutie ul { list-style-position: inside; padding-left: 5px; margin-top: 5px;}
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">HealthSys</a></div>
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
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? ($isEditMode ? 'Edit Medicine' : 'Add Medicine')); ?></h2></div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Admin Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

        <?php if (!empty($data['errors'])): ?>
            <div class="error-message-box-cutie"><strong>Please correct the following errors:</strong><ul>
                <?php foreach ($data['errors'] as $field => $errorMsg): ?><li><?php echo htmlspecialchars(is_int($field) ? $errorMsg : (ucfirst($field) .": ". $errorMsg) ); ?></li><?php endforeach; ?>
            </ul></div>
        <?php endif; ?>

        <div class="form-container-admin-styled-cutie">
            <form action="<?php echo $formAction; ?>" method="POST">
                <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                <?php if ($isEditMode): ?>
                    <input type="hidden" name="medicineId" value="<?php echo htmlspecialchars($data['medicineId']); ?>">
                <?php endif; ?>

                <fieldset>
                    <legend>Medicine Details</legend>
                    <div class="form-grid-admin-styled-cutie">
                        <div class="form-group-admin-styled-cutie">
                            <label for="Name">Medicine Name: *</label>
                            <input type="text" id="Name" name="Name" value="<?php echo htmlspecialchars($data['input']['Name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group-admin-styled-cutie">
                            <label for="Unit">Unit: * (e.g., tablet, ml, mg)</label>
                            <input type="text" id="Unit" name="Unit" value="<?php echo htmlspecialchars($data['input']['Unit'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group-admin-styled-cutie">
                            <label for="Manufacturer">Manufacturer:</label>
                            <input type="text" id="Manufacturer" name="Manufacturer" value="<?php echo htmlspecialchars($data['input']['Manufacturer'] ?? ''); ?>">
                        </div>
                        <div class="form-group-admin-styled-cutie">
                            <label for="StockQuantity">Stock Quantity: *</label>
                            <input type="number" id="StockQuantity" name="StockQuantity" value="<?php echo htmlspecialchars($data['input']['StockQuantity'] ?? '0'); ?>" min="0" required>
                        </div>
                        <div class="form-group-admin-styled-cutie" style="grid-column: 1 / -1;">
                            <label for="Description">Description:</label>
                            <textarea name="Description" id="Description" rows="3"><?php echo htmlspecialchars($data['input']['Description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </fieldset>

                <div class="form-actions-admin-styled-cutie">
                    <button type="submit" class="btn-submit-form-cutie"><?php echo $isEditMode ? 'Update Medicine' : 'Add Medicine'; ?></button>
                    <a href="<?php echo BASE_URL; ?>/admin/listMedicines" class="btn-cancel-form-cutie">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>