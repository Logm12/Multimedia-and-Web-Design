<?php
// app/views/doctor/notifications_list.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Valued Doctor';
$currentAvatarPath = $_SESSION['user_avatar'] ?? null; // Get from session first
$avatarSrc = BASE_URL . '/assets/images/default_avatar.png'; // Default
if (!empty($currentAvatarPath) && $currentAvatarPath !== 'default_avatar.png') {
    if (filter_var($currentAvatarPath, FILTER_VALIDATE_URL)) {
        $avatarSrc = htmlspecialchars($currentAvatarPath);
    } elseif (file_exists(PUBLIC_PATH . $currentAvatarPath)) {
         $avatarSrc = BASE_URL . '/' . htmlspecialchars($currentAvatarPath);
    }
}
// $data = $data ?? ['title' => 'My Notifications', 'notifications' => []];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Notifications'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
           <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* Reuse sidebar, header, main content styles from doctor/dashboard.php */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }
        .dashboard-sidebar-cutie { width: 260px; background-color:rgb(10,46,106); color: #ecf0f1; padding: 25px 0; display: flex; flex-direction: column; }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a { display: flex; align-items: center; padding: 15px 25px; color: #bdc3c7; text-decoration: none; font-size: 15px; font-weight: 500; border-left: 4px solid transparent; transition: all 0.2s ease; }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie { background-color: #34495e; color: #fff; border-left-color: #3498db; }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #7f8c8d; }
 /* Container chung cho các hành động của user */
.user-actions {
    display: flex;
    align-items: center;
    gap: 15px; /* Khoảng cách giữa các phần tử */
}

/* Style cho các nút icon như chuông thông báo */
.icon-button {
    background: none;
    border: none;
    font-size: 20px; /* Kích thước icon lớn hơn một chút */
    color: #6c757d;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s ease, color 0.2s ease;
}
.icon-button:hover {
    background-color: #f1f3f5;
    color: #343a40;
}

/* --- Phần Dropdown Profile --- */
.profile-dropdown {
    position: relative; /* Quan trọng để định vị menu con */
}

/* Nút bấm để mở menu */
.profile-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    background-color: transparent;
    border: none;
    padding: 4px 8px;
    border-radius: 20px;
    transition: background-color 0.2s ease;
}
.profile-trigger:hover {
    background-color: #e9ecef;
}

.profile-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-name {
    font-weight: 500;
    font-size: 15px;
    color: #495057;
}

.dropdown-arrow {
    font-size: 12px;
    color: #6c757d;
}

/* Menu dropdown con */
.dropdown-menu {
    position: absolute;
    top: calc(100% + 10px); /* Vị trí dưới nút trigger, có khoảng cách 10px */
    right: 0;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
    min-width: 200px; /* Độ rộng tối thiểu */
    z-index: 1000;
    border: 1px solid #e9ecef;
    padding: 8px 0;
    overflow: hidden;
    transition: opacity 0.2s ease, transform 0.2s ease;
}

/* Trạng thái ẩn của menu (dùng cho JS) */
.dropdown-menu.hidden {
    opacity: 0;
    transform: translateY(-10px);
    pointer-events: none; /* Không thể click khi đang ẩn */
}

/* Các mục trong menu */
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    font-size: 14px;
    color: #495057;
    text-decoration: none;
    transition: background-color 0.2s ease;
}
.dropdown-item i {
    width: 16px; /* Căn chỉnh icon */
    text-align: center;
    color: #868e96;
}
.dropdown-item:hover {
    background-color: #f8f9fa;
}

/* Mục logout có màu đỏ để nhấn mạnh */
.dropdown-item-logout:hover {
    background-color: #fff5f5;
    color: #e03131;
}
.dropdown-item-logout:hover i {
    color: #e03131;
}

/* Đường kẻ phân cách */
.dropdown-divider {
    height: 1px;
    background-color: #e9ecef;
    margin: 8px 0;
}
        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        .main-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .page-title-header-cutie { display: flex; justify-content: space-between; align-items: center; width: 100%;}
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; color: #2c3e50; }
        .page-actions-cutie { display: flex; gap: 10px; }
        .btn-mark-all-read-cutie {
            background-color: #3498db; color: white; padding: 9px 18px; border: none;
            border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;
            cursor: pointer; transition: background-color 0.2s ease;
        }
        .btn-mark-all-read-cutie:hover { background-color: #2980b9; }
        
        .user-actions-cutie { display: flex; align-items: center; gap: 20px; margin-left: auto; }
        .user-actions-cutie .icon-button-cutie { background: none; border: none; font-size: 22px; color: #7f8c8d; cursor: pointer; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #34495e; }

        .notifications-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 25px; }
        .notification-item-cutie {
            display: flex; align-items: flex-start; padding: 15px 0; border-bottom: 1px solid #ecf0f1;
            gap: 15px;
        }
        .notification-item-cutie:last-child { border-bottom: none; }
        .notification-item-cutie.unread-notification-cutie { background-color: #fdfdfe; border-left: 4px solid #3498db; padding-left: 11px;} /* Highlight unread */
        
        .notification-icon-cutie { font-size: 20px; color: #3498db; margin-top: 2px; flex-shrink: 0; }
        .notification-content-cutie { flex-grow: 1; }
        .notification-content-cutie a { text-decoration: none; color: inherit; }
        .notification-title-cutie { font-size: 15px; font-weight: 600; color: #2c3e50; margin-bottom: 3px; }
        .notification-message-cutie { font-size: 14px; color: #566573; line-height: 1.5; margin-bottom: 5px; }
        .notification-time-cutie { font-size: 12px; color: #7f8c8d; }
        .no-notifications-msg-cutie { text-align: center; padding: 30px; color: #7f8c8d; font-style: italic; }
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
                <li><a href="<?php echo BASE_URL; ?>/doctor/dashboard"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/mySchedule"><span class="nav-icon-cutie">🗓️</span>View My Schedule</a></li>
                <li><a href="<?php echo BASE_URL; ?>/medicalrecord/viewConsultationDetails"><span class="nav-icon-cutie">📝</span>EMR</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/manageAvailability"><span class="nav-icon-cutie">⏱️</span>Manage Availability</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/patientList"><span class="nav-icon-cutie">👥</span>Patient List</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/myLeaveRequests"><span class="nav-icon-cutie">✈️</span>My Leave Requests</a></li>
                               <li><a href="<?php echo BASE_URL; ?>/doctor/notifications" class="active-nav-cutie"><span class="nav-icon-cutie">🔔</span>Notifications</a></li>

                <li><a href="<?php echo BASE_URL; ?>/doctor/updateProfile"><span class="nav-icon-cutie">👤</span>Update Profile</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-header-cutie">
                <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Notifications'); ?></h2></div>
                <?php if (!empty($data['notifications'])): ?>
                <div class="page-actions-cutie">
                    <form action="<?php echo BASE_URL; ?>/doctor/markAllNotificationsRead" method="POST" style="display:inline;">
                        <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>
                        <button type="submit" class="btn-mark-all-read-cutie">Mark all as read</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
           <div class="user-actions">
    <!-- Nút thông báo với icon từ Font Awesome -->
    <button class="icon-button" title="Notifications">
        <i class="fas fa-bell"></i>
    </button>

    <!-- Khu vực profile, bao gồm cả trigger và menu dropdown -->
    <div class="profile-dropdown">
        <!-- Phần này là nút bấm để mở menu -->
        <button class="profile-trigger" id="profileDropdownTrigger">
            <img src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="User Avatar" class="profile-avatar">
            <span class="profile-name"><?php echo htmlspecialchars($userFullName); ?></span>
            <i class="fas fa-caret-down dropdown-arrow"></i>
        </button>

        <!-- Menu dropdown, mặc định sẽ bị ẩn -->
        <div class="dropdown-menu hidden" id="profileDropdownMenu">
            <a href="<?php echo BASE_URL; ?>/doctor/updateprofile" class="dropdown-item">
                <i class="fas fa-user-circle"></i> My Profile
            </a>
            <a href="#" class="dropdown-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?php echo BASE_URL; ?>/auth/logout" class="dropdown-item dropdown-item-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>
        </header>

        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="message-cutie success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        <?php endif; ?>

        <div class="notifications-container-cutie">
            <?php if (!empty($data['notifications'])): ?>
                <?php foreach ($data['notifications'] as $notification): ?>
                    <div class="notification-item-cutie <?php echo !$notification['IsRead'] ? 'unread-notification-cutie' : ''; ?>">
                        <span class="notification-icon-cutie">
                            <?php 
                                // Placeholder icons based on type
                                if (strpos($notification['Type'], 'appointment') !== false) echo '🗓️';
                                elseif (strpos($notification['Type'], 'feedback') !== false) echo '⭐';
                                else echo 'ℹ️';
                            ?>
                        </span>
                        <div class="notification-content-cutie">
                            <a href="<?php echo !empty($notification['Link']) ? htmlspecialchars(BASE_URL . $notification['Link']) : '#'; ?>" 
                               data-notification-id="<?php echo $notification['NotificationID']; ?>" class="notification-link-js">
                                <div class="notification-title-cutie">
                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $notification['Type']))); // Simple title from type ?>
                                </div>
                                <p class="notification-message-cutie"><?php echo htmlspecialchars($notification['ShortMessage'] ?? $notification['Message']); ?></p>
                                <p class="notification-time-cutie"><?php echo htmlspecialchars(date('M j, Y \a\t g:i A', strtotime($notification['CreatedAt']))); ?></p>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-notifications-msg-cutie">No new notifications for you, Dr. <?php echo htmlspecialchars($userFullName); ?>. All caught up! ✨</p>
            <?php endif; ?>
        </div>
    </main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationLinks = document.querySelectorAll('.notification-link-js');
    notificationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const notificationId = this.dataset.notificationId;
            const isRead = this.closest('.notification-item-cutie').classList.contains('unread-notification-cutie');
            const targetUrl = this.href;

            if (isRead && notificationId && targetUrl && targetUrl !== '#') { // Only mark as read if it's unread and has a valid link
                // Optimistically mark as read on UI
                this.closest('.notification-item-cutie').classList.remove('unread-notification-cutie');
                
                // Send AJAX to mark as read in backend (fire and forget or handle response)
                fetch(`<?php echo BASE_URL; ?>/doctor/markNotificationRead/${notificationId}`, {
                    method: 'POST', // Or GET if your route is set up for it
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        // Add CSRF token if your markNotificationRead action expects it via POST and AJAX
                        // 'X-CSRF-TOKEN': 'your_csrf_token_here_from_js_var_or_meta_tag'
                    }
                })
                .then(response => {
                    if (!response.ok) console.error('Failed to mark notification as read on server.');
                    // No need to redirect from JS if link click proceeds
                })
                .catch(error => console.error('Error marking notification as read:', error));
                
                // Allow default link behavior if it's not '#'
                // If it is '#', prevent default to avoid page jump
                if (targetUrl.endsWith('#')) {
                    e.preventDefault();
                }
            } else if (targetUrl.endsWith('#')) {
                 e.preventDefault(); // Prevent jump for links that are just '#'
            }
            // If targetUrl is valid, the browser will navigate after this event listener finishes.
        });
    });
});
</script>
</body>
</html>