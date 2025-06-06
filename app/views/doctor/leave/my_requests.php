<?php
// file: app/views/doctor/my_leave_requests.php
// FILE NÀY BÂY GIỜ ĐÃ CÓ LAYOUT CHUẨN

// --- Giả định các biến này đã được chuẩn bị trong Controller ---
$pageTitle = $data['title'] ?? 'My Leave Requests';
$leaveRequests = $data['leaveRequests'] ?? [];
$currentStatusFilter = $data['currentStatusFilter'] ?? 'All';
$allStatuses = $data['allStatuses'] ?? ['All', 'Pending', 'Approved', 'Rejected', 'Cancelled'];
$csrfToken = $_SESSION['csrf_token'] ?? '';

// --- Logic chuẩn bị cho layout (Header, Sidebar) ---
$userFullName = $_SESSION['user_fullname'] ?? 'Valued Doctor';
$currentAvatarPath = $_SESSION['user_avatar'] ?? null; // Get from session first
$avatarSrc = BASE_URL . '/public/assets/images/default_avatar.png'; // Default
if (!empty($currentAvatarPath) && $currentAvatarPath !== 'default_avatar.png') {
    if (filter_var($currentAvatarPath, FILTER_VALIDATE_URL)) {
        $avatarSrc = htmlspecialchars($currentAvatarPath);
    } elseif (file_exists(PUBLIC_PATH . $currentAvatarPath)) {
         $avatarSrc = BASE_URL . '/' . htmlspecialchars($currentAvatarPath);
    }
}
$activePage = 'leave_requests'; // << Đánh dấu trang hiện tại
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Healthcare System</title>
    <!-- Copy toàn bộ link CSS, font từ file my_schedule.php -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Copy toàn bộ thẻ <style> từ my_schedule.php vào đây -->
    <style>
        *, *::before, *::after { box-sizing: border-box; /* ... */ }
        /* ... Dán toàn bộ CSS chung của Doctor vào đây ... */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
            width: 260px; 
            /* MÀU GRADIENT MỚI CỦA CẬU ĐÂY NÈ */
            background:rgb(10,46,106);
            color: #ecf0f1; 
            padding: 25px 0; 
            display: flex; 
            flex-direction: column;
        }
        .sidebar-header-cutie { 
            text-align: center; 
            margin-bottom: 30px; 
            padding: 0 20px; 
        }
        .sidebar-logo-cutie { 
            font-size: 24px; 
            font-weight: 700; 
            color: #fff; 
            text-decoration: none; 
        }
        .sidebar-nav-cutie ul { 
            list-style: none; 
            padding: 0;
            margin: 0; 
        }
        .sidebar-nav-cutie li a { 
            display: flex; 
            align-items: center; 
            padding: 15px 25px; 
            color: #dfe6e9; /* Màu chữ hơi sáng hơn cho dễ đọc trên gradient */
            text-decoration: none; 
            font-size: 15px; 
            font-weight: 500; 
            border-left: 4px solid transparent; 
            transition: all 0.2s ease; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: rgba(255, 255, 255, 0.15); /* Nền hơi sáng hơn khi hover/active */
            color: #fff; 
            border-left-color: #55efc4; /* Màu nhấn xanh mint sáng cho active (tương phản) */
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { 
            margin-right: 12px; 
            font-size: 18px; 
            width: 20px; 
            text-align: center; 
        }
        .sidebar-footer-cutie { 
            margin-top: auto; 
            padding: 20px 25px; 
            text-align: center; 
            font-size: 13px; 
            color: #bdc3c7; /* Màu chữ cho footer */
        }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; display: flex; flex-direction: column; }
        .main-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; color: #2c3e50; }
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
/* ========================================================== */
/*          CSS CHO CÁC THÀNH PHẦN CHUNG (PANEL, NÚT)         */
/* ========================================================== */

/* --- Panel nội dung chung --- */
.content-panel-doctor-cutie {
    background-color: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    margin-bottom: 25px;
}

/* --- Thanh Filter --- */
.schedule-toolbar-cutie {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 20px;
    padding: 0; /* Bỏ padding nếu nó đã nằm trong panel */
    box-shadow: none;
    background-color: transparent;
}
.filter-group-cutie {
    display: flex;
    flex-direction: column;
}
.filter-group-cutie label {
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 14px;
    color: #495057;
}
.filter-group-cutie select,
.filter-group-cutie input {
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    min-width: 220px;
}

/* --- Tiêu đề và nút Action của bảng --- */
.table-title-action-cutie {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}
.table-title-action-cutie h3 {
    margin: 0;
    font-size: 20px;
    color: #2c3e50;
    font-weight: 600;
}

/* --- Các loại nút bấm chung --- */
.btn-cutie {
    display: inline-flex; /* Dùng inline-flex để icon và text căn giữa */
    align-items: center;
    gap: 8px; /* Khoảng cách giữa icon và text */
    padding: 9px 18px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-cutie:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.btn-primary-cutie { background-color: #3498db; color: white; border-color: #3498db; }
.btn-primary-cutie:hover { background-color: #2980b9; }

.btn-danger-cutie { background-color: #e74c3c; color: white; border-color: #e74c3c; }
.btn-danger-cutie:hover { background-color: #c0392b; }


/* ========================================================== */
/*              CSS CHO BẢNG DỮ LIỆU VÀ TRẠNG THÁI            */
/* ========================================================== */

/* --- Container và bảng --- */
.schedule-table-container-cutie {
    /* Đã có style ở trên, có thể dùng lại */
}
.table-responsive-cutie {
    overflow-x: auto;
}
.schedule-table-cutie {
    width: 100%;
    border-collapse: collapse;
}
.schedule-table-cutie th,
.schedule-table-cutie td {
    padding: 14px 15px;
    text-align: left;
    border-bottom: 1px solid #ecf0f1;
    font-size: 14px;
    vertical-align: middle;
}
.schedule-table-cutie thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    white-space: nowrap; /* Ngăn các tiêu đề cột bị xuống dòng */
}
.schedule-table-cutie tbody tr:hover {
    background-color: #f1f3f5;
}

/* --- Nhãn trạng thái (Status Badge) --- */
.status-badge-doctor-cutie {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600; /* Làm đậm chữ hơn */
    color: #fff;
    text-transform: capitalize;
    display: inline-block;
    min-width: 90px;
    text-align: center;
}
.status-pending { background-color: #f39c12; } /* Cam */
.status-approved { background-color: #2ecc71; } /* Xanh lá */
.status-rejected { background-color: #e74c3c; } /* Đỏ */
.status-cancelled { background-color: #95a5a6; } /* Xám */

/* --- Cột Actions --- */
.action-buttons-doctor-cutie {
    white-space: nowrap; /* Ngăn các nút bị xuống dòng */
}

/* --- Thông báo khi không có dữ liệu --- */
.no-appointments-msg-doctor-cutie {
    text-align: center;
    padding: 40px 20px;
    color: #7f8c8d;
    font-style: italic;
}

/* --- CSS cho thông báo session --- */
.message-cutie {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    border-left-width: 5px;
    border-left-style: solid;
}
.message-cutie.success-message {
    background-color: #d1fae5;
    color: #065f46;
    border-left-color: #10b981;
}
.message-cutie.error-message {
    background-color: #fee2e2;
    color: #991b1b;
    border-left-color: #ef4444;
}
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">PulseCare</a></div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/doctor/dashboard" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/dashboard') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/mySchedule" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/mySchedule') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>View My Schedule</a></li>
                <li><a href="<?php echo BASE_URL; ?>/medicalrecord/viewConsultationDetails" class="<?php echo (strpos($_GET['url'] ?? '', 'medicalrecord/viewConsultationDetails') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📝</span>EMR</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/manageAvailability" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/manageAvailability') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⏱️</span>Manage Availability</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/patientList" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/patientList') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👥</span>Patient List</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/requestTimeOff" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/requestTimeOff') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">✈️</span>My Leave Requests</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/notifications" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/notifications') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🔔</span>Notifications</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/updateProfile" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>Update Profile</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

     <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Leave Request'); ?></h2></div>
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
            <span class="profile-name">Dr.<?php echo htmlspecialchars($userFullName); ?></span>
            <i class="fas fa-caret-down dropdown-arrow"></i>
        </button>

        <!-- Menu dropdown, mặc định sẽ bị ẩn -->
        <div class="dropdown-menu hidden" id="profileDropdownMenu">
            <a href="<?php echo BASE_URL; ?>/patient/updateProfile" class="dropdown-item">
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

   <!-- ========================================================== -->
<!--            KẾT THÚC PHẦN LAYOUT - BẮT ĐẦU NỘI DUNG          -->
<!-- ========================================================== -->

    <!-- Hiển thị thông báo session (nếu có) -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message-cutie success-message"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message-cutie error-message"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Thanh Filter - Bọc trong một div panel riêng cho đẹp -->
    <div class="content-panel-doctor-cutie" style="margin-bottom: 25px;">
        <form method="GET" action="<?php echo BASE_URL; ?>/doctor/myLeaveRequests" class="schedule-toolbar-cutie" style="padding: 0; box-shadow: none; background-color: transparent;">
            <div class="filter-group-cutie">
                <label for="status_filter">Filter by Status:</label>
                <select name="status" id="status_filter" onchange="this.form.submit()">
                    <?php foreach ($allStatuses as $statusValue): ?>
                        <option value="<?php echo htmlspecialchars($statusValue); ?>" <?php echo ($currentStatusFilter == $statusValue) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(htmlspecialchars($statusValue)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Bảng Dữ liệu -->
    <div class="schedule-table-container-cutie"> <!-- Dùng lại class container của bảng -->
        
        <div class="table-title-action-cutie" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 20px; color: #2c3e50;">Your Leave Requests</h3>
            <a href="<?php echo BASE_URL; ?>/doctor/requestLeave" class="btn-cutie btn-primary-cutie"> <!-- Dùng class nút chung -->
                <i class="fas fa-plus"></i> Request New Leave
            </a>
        </div>

        <div class="table-responsive-cutie">
            <table class="schedule-table-cutie"> <!-- Dùng lại class của bảng schedule -->
                <thead>
                    <tr>
                        <th>Requested At</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Admin Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($leaveRequests)): ?>
                        <?php foreach ($leaveRequests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('D, M j, Y', strtotime($request['RequestedAt']))); ?></td>
                                <td><?php echo htmlspecialchars(date('D, M j, Y', strtotime($request['StartDate']))); ?></td>
                                <td><?php echo htmlspecialchars(date('D, M j, Y', strtotime($request['EndDate']))); ?></td>
                                <td title="<?php echo htmlspecialchars($request['Reason'] ?? ''); ?>"><?php echo htmlspecialchars(substr($request['Reason'] ?? '', 0, 40)) . (strlen($request['Reason'] ?? '') > 40 ? '...' : ''); ?></td>
                                <td>
                                    <!-- Sử dụng lại các class status badge bạn đã có -->
                                    <span class="status-badge-doctor-cutie status-<?php echo strtolower(htmlspecialchars($request['Status'])); ?>">
                                        <?php echo htmlspecialchars($request['Status']); ?>
                                    </span>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($request['AdminNotes'] ?? 'N/A')); ?></td>
                                <td class="action-buttons-doctor-cutie">
                                    <?php if ($request['Status'] === 'Pending'): ?>
                                        <form action="<?php echo BASE_URL; ?>/doctor/cancelMyLeaveRequest/<?php echo $request['LeaveRequestID']; ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel this leave request?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                            <button type="submit" class="btn-cutie btn-danger-cutie" title="Cancel Request">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <!-- Thay đổi colspan thành 7 -->
                            <td colspan="7" class="no-appointments-msg-doctor-cutie">
                                You haven't submitted any leave requests for the "<?php echo htmlspecialchars($currentStatusFilter); ?>" status yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Đặt ở cuối file, ngay trước thẻ </body> -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================================
    //          LOGIC CHO DROPDOWN MENU NGƯỜI DÙNG
    // ==========================================================
    const trigger = document.getElementById('profileDropdownTrigger');
    const menu = document.getElementById('profileDropdownMenu');

    // Kiểm tra xem các phần tử có tồn tại không trước khi thêm sự kiện
    if (trigger && menu) {
        
        // Sự kiện khi click vào nút trigger (tên/avatar)
        trigger.addEventListener('click', function(event) {
            // Ngăn sự kiện click lan ra ngoài (ví dụ đến window)
            event.stopPropagation();
            
            // Thêm hoặc xóa class 'hidden' để hiện/ẩn menu
            menu.classList.toggle('hidden');
        });

        // Sự kiện khi click ra bất cứ đâu trên trang
        window.addEventListener('click', function(event) {
            // Kiểm tra xem cú click có nằm ngoài menu và ngoài nút trigger không
            if (!menu.contains(event.target) && !trigger.contains(event.target)) {
                // Nếu đúng, luôn ẩn menu đi
                menu.classList.add('hidden');
            }
        });

        // (Tùy chọn) Đóng menu khi nhấn phím Escape
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
    }

    // ==========================================================
    //          CÁC ĐOẠN SCRIPT KHÁC CỦA BẠN (NẾU CÓ)
    // ==========================================================
    // Ví dụ: script cho biểu đồ, AJAX...
    
});
</script>
</body>
</html>