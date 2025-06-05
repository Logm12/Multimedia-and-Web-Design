<?php
// app/views/admin/feedbacks/manage_feedbacks.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Admin';
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_admin_avatar.png';
// $data = $data ?? [ /* ... existing dummy data ... */ ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Manage Feedbacks'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reuse sidebar, header, main content styles from other admin pages */
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

        .filters-toolbar-admin-cutie { background-color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .filters-form-grid-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px 20px; align-items: flex-end; }
        .filter-group-admin-feedback-cutie label { display: block; font-size: 13px; color: #495057; margin-bottom: 6px; font-weight: 500; }
        .filter-group-admin-feedback-cutie input, .filter-group-admin-feedback-cutie select { width: 100%; padding: 9px 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
        .btn-apply-filter-feedback-cutie { padding: 10px 20px; background-color: #3498db; color: white; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; }
        .btn-apply-filter-feedback-cutie:hover { background-color: #2980b9; }

        .content-table-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; padding: 25px; }
        .content-table-cutie { width: 100%; border-collapse: collapse; }
        .content-table-cutie th, .content-table-cutie td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #ecf0f1; font-size: 13px; }
        .content-table-cutie th { background-color: #f7f9f9; font-weight: 600; color: #34495e; white-space: nowrap; }
        .content-table-cutie tbody tr:hover { background-color: #fdfdfe; }
        .content-table-cutie .comment-cell-feedback-cutie { max-width: 300px; white-space: normal; word-wrap: break-word; }
        .rating-stars-display-cutie span { font-size: 16px; color: #f39c12; margin-right: 1px;}
        .rating-stars-display-cutie .star-empty-cutie { color: #e0e0e0; }
        .status-published-cutie { color: #27ae60; font-weight: bold; }
        .status-unpublished-cutie { color: #7f8c8d; }
        .action-buttons-feedback-cutie button { padding: 6px 10px; font-size: 12px; border-radius: 5px; border: none; cursor: pointer; transition: opacity 0.2s ease; }
        .btn-publish-cutie { background-color: #2ecc71; color: white; }
        .btn-unpublish-cutie { background-color: #e74c3c; color: white; }
        
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .no-items-msg-cutie { text-align: center; padding: 30px; color: #7f8c8d; font-style: italic; }
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
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Manage Patient Feedbacks'); ?></h2></div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Admin Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

        <?php if (isset($_SESSION['admin_feedback_message_success'])): ?>
            <p class="message-cutie success-message"><?php echo $_SESSION['admin_feedback_message_success']; unset($_SESSION['admin_feedback_message_success']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['admin_feedback_message_error'])): ?>
            <p class="message-cutie error-message"><?php echo $_SESSION['admin_feedback_message_error']; unset($_SESSION['admin_feedback_message_error']); ?></p>
        <?php endif; ?>

        <div class="filters-toolbar-admin-cutie">
            <form method="GET" action="<?php echo BASE_URL; ?>/admin/manageFeedbacks" class="filters-form-grid-cutie">
                <div class="filter-group-admin-feedback-cutie">
                    <label for="filter_doctor">Doctor:</label>
                    <select name="doctor_id" id="filter_doctor">
                        <option value="">All Doctors</option>
                        <?php if (!empty($data['doctorsForFilter'])): foreach ($data['doctorsForFilter'] as $doctor): ?>
                            <option value="<?php echo $doctor['DoctorID']; ?>" <?php echo (($data['currentFilters']['doctor_id'] ?? '') == $doctor['DoctorID']) ? 'selected' : ''; ?>>
                                Dr. <?php echo htmlspecialchars($doctor['DoctorName']); ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="filter-group-admin-feedback-cutie">
                    <label for="filter_rating">Rating:</label>
                    <select name="rating" id="filter_rating">
                        <option value="">All Ratings</option>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo (($data['currentFilters']['rating'] ?? '') == $i) ? 'selected' : ''; ?>><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="filter-group-admin-feedback-cutie">
                    <label for="filter_published">Status:</label>
                    <select name="is_published" id="filter_published">
                        <option value="" <?php echo (($data['currentFilters']['is_published'] ?? '') == '') ? 'selected' : ''; ?>>All</option>
                        <option value="1" <?php echo (($data['currentFilters']['is_published'] ?? '') == '1') ? 'selected' : ''; ?>>Published</option>
                        <option value="0" <?php echo (($data['currentFilters']['is_published'] ?? '') == '0') ? 'selected' : ''; ?>>Unpublished</option>
                    </select>
                </div>
                 <div class="filter-group-admin-feedback-cutie">
                    <label for="filter_search_term">Search:</label>
                    <input type="text" name="search_term" id="filter_search_term" value="<?php echo htmlspecialchars($data['currentFilters']['search_term'] ?? ''); ?>" placeholder="Patient, Doctor, Comment...">
                </div>
                <div class="filter-group-admin-feedback-cutie" style="align-self: flex-end;">
                    <button type="submit" class="btn-apply-filter-feedback-cutie">Apply Filters</button>
                </div>
            </form>
        </div>

        <div class="content-table-container-cutie">
            <?php if (!empty($data['feedbacks'])): ?>
                <table class="content-table-cutie">
                    <thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Visit Date</th><th>Rating</th><th>Comment</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($data['feedbacks'] as $fb): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($fb['FeedbackDate']))); ?></td>
                            <td><?php echo htmlspecialchars($fb['PatientName']); ?></td>
                            <td>Dr. <?php echo htmlspecialchars($fb['DoctorName']); ?></td>
                            <td><?php echo $fb['VisitDate'] ? htmlspecialchars(date('M j, Y', strtotime($fb['VisitDate']))) : 'N/A'; ?></td>
                            <td class="rating-stars-display-cutie">
                                <?php for ($i = 1; $i <= 5; $i++): ?><span><?php echo ($i <= $fb['Rating']) ? '★' : '☆'; ?></span><?php endfor; ?>
                            </td>
                            <td class="comment-cell-feedback-cutie" title="<?php echo htmlspecialchars($fb['Comments']); ?>"><?php echo nl2br(htmlspecialchars(substr($fb['Comments'], 0, 100) . (strlen($fb['Comments']) > 100 ? '...' : ''))); ?></td>
                            <td><span class="<?php echo $fb['IsPublished'] ? 'status-published-cutie' : 'status-unpublished-cutie'; ?>"><?php echo $fb['IsPublished'] ? 'Published' : 'Unpublished'; ?></span></td>
                            <td class="action-buttons-feedback-cutie">
                                <form action="<?php echo BASE_URL; ?>/admin/toggleFeedbackPublication" method="POST" style="display:inline;">
                                    <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                                    <input type="hidden" name="feedback_id" value="<?php echo $fb['FeedbackID']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $fb['IsPublished']; ?>">
                                    <button type="submit" class="<?php echo $fb['IsPublished'] ? 'btn-unpublish-cutie' : 'btn-publish-cutie'; ?>">
                                        <?php echo $fb['IsPublished'] ? 'Unpublish' : 'Publish'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-items-msg-cutie">No patient feedbacks found matching your criteria. ✨</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>