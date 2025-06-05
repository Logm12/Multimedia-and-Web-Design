<?php
// app/views/feedback/list_feedbacks.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Patient';
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_avatar.png';
// $data = $data ?? ['title' => 'My Feedback History', 'feedbacks' => []];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Feedback History'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Using similar styles from previous patient pages */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }
        .dashboard-sidebar-cutie { width: 260px; background-color: #667EEA; color: #fff; padding: 25px 0; display: flex; flex-direction: column; }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a { display: flex; align-items: center; padding: 15px 25px; color: #e0e0e0; text-decoration: none; font-size: 15px; font-weight: 500; border-left: 4px solid transparent; transition: all 0.2s ease; }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie { background-color: rgba(255,255,255,0.1); color: #fff; border-left-color: #fff; }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #c0c0c0; }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        .main-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; color: #212529; }
        .user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .user-actions-cutie .icon-button-cutie { background: none; border: none; font-size: 22px; color: #6c757d; cursor: pointer; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #495057; }

        .feedback-history-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 25px; }
        .feedback-history-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .feedback-history-header-cutie h3 { font-size: 20px; font-weight: 600; color: #343a40; }
        .btn-submit-new-feedback-cutie {
            background-color: #667EEA; color: white; padding: 10px 18px; border: none;
            border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;
            transition: background-color 0.2s ease;
        }
        .btn-submit-new-feedback-cutie:hover { background-color: #5a67d8; }

        .feedback-table-cutie { width: 100%; border-collapse: collapse; }
        .feedback-table-cutie th, .feedback-table-cutie td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e9ecef; font-size: 14px; }
        .feedback-table-cutie th { background-color: #f8f9fa; font-weight: 600; color: #495057; }
        .feedback-table-cutie .rating-stars-cutie span { font-size: 18px; color: #ffc107; margin-right: 2px; }
        .feedback-table-cutie .rating-stars-cutie .star-empty-cutie { color: #e0e0e0; }
        .feedback-table-cutie .comment-cell-cutie { max-width: 350px; line-height: 1.6; }
        .no-feedback-msg-cutie { text-align: center; padding: 30px; color: #6c757d; font-style: italic; }
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        @media (max-width: 768px) { /* Sidebar responsive */ }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">HealthSys</a></div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/patient/dashboard"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL . '/patient/browseDoctors'; ?>"><span class="nav-icon-cutie">🩺</span>Browse Doctors</a></li>
                <li><a href="<?php echo BASE_URL; ?>/appointment/myAppointments"><span class="nav-icon-cutie">🗓️</span>My Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/viewAllMedicalRecords"><span class="nav-icon-cutie">📜</span>Medical Records</a></li>
                <li><a href="<?php echo BASE_URL; ?>/feedback/list" class="active-nav-cutie"><span class="nav-icon-cutie">⭐</span>Feedback</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/updateProfile"><span class="nav-icon-cutie">👤</span>Profile</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Feedback History'); ?></h2></div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="User Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

        <?php if (isset($_SESSION['feedback_success_message'])): ?>
            <p class="message-cutie success-message"><?php echo $_SESSION['feedback_success_message']; unset($_SESSION['feedback_success_message']); ?></p>
        <?php endif; ?>

        <div class="feedback-history-container-cutie">
            <div class="feedback-history-header-cutie">
                <h3>Your Submitted Feedbacks</h3>
                <a href="<?php echo BASE_URL; ?>/feedback/submit" class="btn-submit-new-feedback-cutie">+ Submit New Feedback</a>
            </div>

            <?php if (!empty($data['feedbacks'])): ?>
                <table class="feedback-table-cutie">
                    <thead>
                        <tr><th>Date Submitted</th><th>Doctor</th><th>Visit Date</th><th>Rating</th><th>Comment</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['feedbacks'] as $fb): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('M j, Y H:i', strtotime($fb['CreatedAt']))); ?></td>
                            <td>Dr. <?php echo htmlspecialchars($fb['DoctorName']); ?></td>
                            <td><?php echo htmlspecialchars(date('M j, Y', strtotime($fb['VisitDate']))); ?></td>
                            <td class="rating-stars-cutie">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span><?php echo ($i <= $fb['Rating']) ? '★' : '☆'; ?></span>
                                <?php endfor; ?>
                            </td>
                            <td class="comment-cell-cutie"><?php echo nl2br(htmlspecialchars($fb['Comments'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-feedback-msg-cutie">You haven't submitted any feedback yet. Share your thoughts!</p>
                <p class="no-feedback-msg-cutie">You haven't submitted any feedback yet. Share your thoughts!</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>