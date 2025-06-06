<?php

// app/views/doctor/my_schedule.php

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
// $data = $data ?? [ /* ... existing dummy data ... */ ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'My Schedule'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

   <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
             width: 260px; background-color:rgb(10,46,106); color: #fff;
            padding: 25px 0; display: flex; flex-direction: column;
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a { display: flex; align-items: center; padding: 15px 25px; color: #bdc3c7; text-decoration: none; font-size: 15px; font-weight: 500; border-left: 4px solid transparent; transition: all 0.2s ease; }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie { background-color: #34495e; color: #fff; border-left-color: #3498db; }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #7f8c8d; }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
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


        .schedule-toolbar-cutie { display: flex; justify-content: flex-start; align-items: center; margin-bottom: 20px; gap: 20px; flex-wrap: wrap; padding: 15px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .filter-group-cutie { display: flex; align-items: center; gap: 8px; }
        .filter-group-cutie label { font-size: 14px; color: #495057; font-weight: 500; }
        .filter-group-cutie select, .filter-group-cutie input[type="date"] {
            padding: 8px 12px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; background-color: #fff; color: #495057; min-width: 160px;
        }
        .filter-group-cutie select {
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%236c757d'%3E%3Cpath fill-rule='evenodd' d='M8 11.293l-4.146-4.147a.5.5 0 0 1 .708-.708L8 9.879l3.438-3.438a.5.5 0 0 1 .707.708L8 11.293z'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;
        }
        .filter-group-cutie select:focus, .filter-group-cutie input[type="date"]:focus { border-color: #3498db; box-shadow: 0 0 0 0.2rem rgba(52,152,219,.25); outline: none; }
        .btn-filter-schedule-cutie {
            padding: 9px 18px; background-color: #3498db; color: white; border: none;
            border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background-color 0.2s ease;
        }
        .btn-filter-schedule-cutie:hover { background-color: #2980b9; }

        .schedule-table-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; }
        .schedule-table-cutie { width: 100%; border-collapse: collapse; }
        .schedule-table-cutie th, .schedule-table-cutie td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ecf0f1; font-size: 14px; }
        .schedule-table-cutie th { background-color: #f7f9f9; font-weight: 600; color: #34495e; white-space: nowrap; }
        .schedule-table-cutie tbody tr:hover { background-color: #fdfdfe; }
        
        .status-badge-doctor-cutie { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; white-space: nowrap; }
        .status-scheduled { background-color: #e9ecef; color: #495057; }
        .status-confirmed { background-color: #d1fae5; color: #065f46; }
        .status-completed { background-color: #e0f2f1; color: #004d40; }
        .status-cancelledbypatient, .status-cancelledbyclinic { background-color: #fee2e2; color: #991b1b; }
        .status-noshow { background-color: #fff3cd; color: #856404; }

        .action-buttons-doctor-cutie a, .action-buttons-doctor-cutie button {
            padding: 7px 12px; font-size: 13px; border-radius: 5px; text-decoration: none;
            border: none; cursor: pointer; transition: opacity 0.2s ease; margin-right: 6px; margin-bottom: 5px; display: inline-block;
        }
        .action-buttons-doctor-cutie a:hover, .action-buttons-doctor-cutie button:hover { opacity: 0.8; }
        .btn-consult-cutie { background-color: #3498db; color: white; }
        .btn-complete-cutie { background-color: #2ecc71; color: white; }
        
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .no-appointments-msg-doctor-cutie { text-align: center; padding: 40px 20px; color: #7f8c8d; font-style: italic; }

        @media (max-width: 768px) { /* Sidebar responsive */ }
         /* Thêm các style cần thiết cho trang này nếu có */
        .form-group-cutie { margin-bottom: 20px; }
        .form-group-cutie label { display: block; font-weight: 500; margin-bottom: 8px; color: #495057; }
        .form-group-cutie textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }
        
        /* --- Styling cho các khối thông tin --- */
.info-group {
    margin-bottom: 12px;
}
.info-group strong {
    display: inline-block;
    width: 130px; /* Căn lề cho các label */
    color: #495057;
    font-weight: 600;
}
.info-group span {
    color: #212529;
}

/* --- Styling cho Form (kế thừa từ trang profile) --- */
.form-divider {
    border: 0;
    border-top: 1px solid #e9ecef;
    margin: 30px 0;
}

/* --- Styling cho Bảng Kê Đơn và Lịch sử --- */
/* Dùng lại class của bảng schedule nếu có, hoặc tạo mới */
.data-table-cutie {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
.data-table-cutie th,
.data-table-cutie td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ecf0f1;
    font-size: 14px;
}
.data-table-cutie thead th {
    background-color: #f7f9f9;
    font-weight: 600;
    color: #34495e;
    white-space: nowrap;
}
.data-table-cutie tbody tr:hover {
    background-color: #fdfdfe;
}
.data-table-cutie .action-buttons-cutie a,
.data-table-cutie .action-buttons-cutie button {
    /* Style này sẽ được định nghĩa ở dưới */
}

/* --- Styling cho khu vực Kê đơn --- */
.prescription-form-area {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-top: 15px;
}
.prescription-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: flex-end; /* Căn các phần tử xuống dưới */
}

/* --- Styling cho các nút bấm --- */
.btn-cutie {
    display: inline-block;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-primary-cutie { background-color: #3498db; color: white; border-color: #3498db; }
.btn-primary-cutie:hover { background-color: #2980b9; border-color: #2980b9; }
.btn-success-cutie { background-color: #2ecc71; color: white; border-color: #2ecc71; }
.btn-success-cutie:hover { background-color: #27ae60; border-color: #27ae60; }
.btn-secondary-cutie { background-color: #95a5a6; color: white; border-color: #95a5a6; }
.btn-secondary-cutie:hover { background-color: #7f8c8d; border-color: #7f8c8d; }
.btn-info-cutie { background-color: #17a2b8; color: white; border-color: #17a2b8; }
.btn-danger-cutie { background-color: #e74c3c; color: white; border-color: #e74c3c; }

/* --- Các Action ở cuối trang --- */
.page-actions-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-start; /* Hoặc flex-end nếu muốn nút sang phải */
    gap: 15px;
}
.dss-container {
    margin: 20px 0;
}

#run-ai-dss-btn i {
    margin-right: 8px;
}

.ai-results-panel {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-left: 4px solid #17a2b8; /* Màu nhấn của nút info */
    border-radius: 8px;
    padding: 15px 20px;
    margin-top: 15px;
    animation: fadeInResult 0.5s ease;
}

@keyframes fadeInResult {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.ai-results-panel h4 {
    color: #17a2b8;
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 16px;
    display: flex;
    align-items: center;
}
.ai-results-panel h4 i {
    margin-right: 8px;
}

/* --- KIỂU HIỂN THỊ KẾT QUẢ DẠNG THANH TIẾN TRÌNH CÓ MÀU SẮC --- */
.ai-suggestion-item {
    margin-bottom: 14px;
}
.ai-suggestion-item:last-child {
    margin-bottom: 0;
}

.ai-suggestion-item .suggestion-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    margin-bottom: 6px;
}
.ai-suggestion-item .suggestion-name {
    font-weight: 600; /* Làm đậm tên bệnh */
    color: #34495e;
}
.ai-suggestion-item .suggestion-probability {
    font-weight: 700;
    font-size: 13px;
    background-color: #e9ecef;
    padding: 2px 8px;
    border-radius: 12px;
}

.progress-bar-container {
    background-color: #e9ecef;
    border-radius: 20px;
    height: 12px; /* Cho thanh to hơn một chút */
    overflow: hidden;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.progress-bar-fill {
    height: 100%;
    border-radius: 20px;
    transition: width 0.8s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    font-size: 10px;
    color: white;
    font-weight: bold;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.2);
}

/* Định nghĩa các màu sắc dựa trên mức độ xác suất */
/* Vị trí 1 (quan trọng nhất) */
.progress-bar-fill.rank-1 { background: linear-gradient(to right, #3498db, #2980b9); } /* Xanh dương */

/* Vị trí 2 */
.progress-bar-fill.rank-2 { background: linear-gradient(to right, #2ecc71, #27ae60); } /* Xanh lá */

/* Vị trí 3 */
.progress-bar-fill.rank-3 { background: linear-gradient(to right, #9b59b6, #8e44ad); } /* Tím */

/* Vị trí 4 */
.progress-bar-fill.rank-4 { background: linear-gradient(to right, #1abc9c, #16a085); } /* Xanh ngọc */

/* Vị trí 5 */
.progress-bar-fill.rank-5 { background: linear-gradient(to right, #e67e22, #d35400); } /* Cam */


/* Kiểu hiển thị thông báo disclaimer */
.ai-disclaimer {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px dashed #ced4da;
    font-size: 12px;
    color: #7f8c8d;
    font-style: italic;
    text-align: center;
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
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'My Schedule'); ?></h2></div>
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

      <!-- ========================================================== -->
    <!--            KẾT THÚC PHẦN LAYOUT - BẮT ĐẦU NỘI DUNG          -->
    <!-- ========================================================== -->

        <!-- Hiển thị thông báo -->
        <?php if (isset($_SESSION['consultation_message_success'])): ?>
            <p class="message-cutie success-message"><?php /* ... */ ?></p>
        <?php endif; ?>
        <?php if (isset($data['consultation_message_error'])): ?>
            <p class="message-cutie error-message"><?php /* ... */ ?></p>
        <?php endif; ?>
         <!-- Thông tin cuộc hẹn và bệnh nhân -->
        <div class="content-grid-doctor-cutie" style="grid-template-columns: 1fr 1fr; margin-bottom: 25px;">
    <div class="content-panel-doctor-cutie">
        <h3>Appointment Information</h3>
        <div class="info-group"><strong>Date & Time:</strong> <span><?php echo htmlspecialchars(date('D, M j, Y \a\t g:i A', strtotime($data['appointment']['AppointmentDateTime']))); ?></span></div>
        <div class="info-group"><strong>Status:</strong> <span class="status-badge-doctor-cutie status-<?php echo strtolower(htmlspecialchars($data['appointment']['Status'])); ?>"><?php echo htmlspecialchars($data['appointment']['Status']); ?></span></div>
        <div class="info-group"><strong>Reason for Visit:</strong></div>
        <div style="padding-left: 10px;"><?php echo nl2br(htmlspecialchars($data['appointment']['ReasonForVisit'] ?? 'N/A')); ?></div>
    </div>
    <div class="content-panel-doctor-cutie">
        <h3>Patient Information</h3>
        <div class="info-group"><strong>Name:</strong> <span><?php echo htmlspecialchars($data['patient']['FullName']); ?></span></div>
        <div class="info-group"><strong>Date of Birth:</strong> <span><?php echo htmlspecialchars(date('M j, Y', strtotime($data['patient']['DateOfBirth'] ?? ''))); ?></span></div>
        <div class="info-group"><strong>Phone:</strong> <span><?php echo htmlspecialchars($data['patient']['PhoneNumber'] ?? 'N/A'); ?></span></div>
        <div class="info-group"><strong>Email:</strong> <span><?php echo htmlspecialchars($data['patient']['Email'] ?? 'N/A'); ?></span></div>
    </div>
</div>


 <!-- Lịch sử bệnh án -->
  <div class="content-panel-doctor-cutie" style="margin-bottom: 25px;">
    <h3>Patient's Medical History</h3>
    <div class="schedule-table-container-cutie">
        <table class="data-table-cutie"> <!-- Áp dụng class mới -->
            <thead>
                <tr> <!-- Xóa inline style -->
                    <th>Visit Date</th>
                    <th>Consulting Doctor</th>
                    <th>Diagnosis (Summary)</th>
                    <th>Actions</th>
                </tr>
            </thead>
        <tbody>
            <?php foreach ($data['medicalHistory'] as $historyItem): ?>
                <?php // Không hiển thị record của chính cuộc hẹn đang xem trong lịch sử (nếu model không loại trừ)
                if ($historyItem['AppointmentID'] == $data['appointment']['AppointmentID']) continue;
                ?>
                <tr>
                    <td ><?php echo htmlspecialchars(date('M j, Y', strtotime($historyItem['VisitDate']))); ?></td>
                    <td >Dr. <?php echo htmlspecialchars($historyItem['DoctorName']); ?></td>
                    <td >
                        <?php
                        // Hiển thị tóm tắt Diagnosis, ví dụ 100 ký tự đầu
                        $diagnosisSummary = $historyItem['Diagnosis'] ?? 'N/A';
                        if (strlen($diagnosisSummary) > 100) {
                            $diagnosisSummary = substr($diagnosisSummary, 0, 100) . '...';
                        }
                        echo htmlspecialchars($diagnosisSummary);
                        ?>
                    </td>
                    <td class="action-buttons-cutie">
                       <a href="<?php echo BASE_URL . '/medicalrecord/viewConsultationDetails/' . $historyItem['AppointmentID'] . '?return_to=' . $data['appointment']['AppointmentID']; ?>"  class="btn-cutie btn-secondary-cutie">View Details</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
      </div>
        </div>


<hr style="margin: 20px 0;">

        <!-- Form chính -->
<div class="content-panel-doctor-cutie">

<!-- BẮT ĐẦU FORM CHÍNH -->
<?php if ($data['isConsultingDoctor']): ?>
    <form action="<?php echo BASE_URL; ?>/medicalrecord/viewConsultationDetails/<?php echo $data['appointment']['AppointmentID']; ?>" method="POST" id="medicalRecordForm">
        <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>

        <h3>Consultation Details & Record</h3>
        
        <!-- ========================================================== -->
        <!--                  KHỐI 1: TRIỆU CHỨNG                     -->
        <!-- ========================================================== -->
        <div class="form-group-cutie">
            <label for="symptoms">Symptoms:</label>
            <textarea name="symptoms" id="symptoms" rows="4"><?php echo htmlspecialchars($data['input_symptoms'] ?? $data['medicalRecord']['Symptoms'] ?? ''); ?></textarea>
        </div>

        <!-- ========================================================== -->
        <!--           KHỐI 2: TÍCH HỢP AI DSS (VỊ TRÍ MỚI)            -->
        <!-- ========================================================== -->
        <div class="dss-container" style="margin-bottom: 20px;">
            <button type="button" id="run-ai-dss-btn" class="btn-cutie btn-info-cutie">
                <i class="fas fa-robot"></i> Run AI Diagnosis Suggestion
            </button>
            
            <!-- Khu vực hiển thị kết quả từ AI, mặc định sẽ bị ẩn -->
            <div id="ai-results-panel" class="ai-results-panel" style="display: none;">
                <h4><i class="fas fa-lightbulb"></i> AI Suggestions (Reference Only)</h4>
                <div id="ai-results-content">
                    <!-- JavaScript sẽ chèn kết quả vào đây -->
                </div>
                <div class="ai-disclaimer">
                    <strong>Disclaimer:</strong> AI results are for reference and do not replace professional medical judgment.
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!--                  KHỐI 3: CHẨN ĐOÁN                        -->
        <!-- ========================================================== -->
        <div class="form-group-cutie">
            <label for="diagnosis">Diagnosis:</label>
            <textarea name="diagnosis" id="diagnosis" rows="4"><?php echo htmlspecialchars($data['input_diagnosis'] ?? $data['medicalRecord']['Diagnosis'] ?? ''); ?></textarea>
        </div>
        <div class="form-group-cutie">
            <label for="treatment_plan">Treatment Plan:</label>
            <textarea name="treatment_plan" id="treatment_plan" rows="4" style="width:100%; padding:10px;"><?php echo htmlspecialchars($data['input_treatment_plan'] ?? $data['medicalRecord']['TreatmentPlan'] ?? ''); ?></textarea>
        </div>
         <div class="form-group-cutie">
            <label for="consultation_notes">Additional Notes:</label>
            <textarea name="consultation_notes" id="consultation_notes" rows="6" style="width:100%; padding:10px;"><?php echo htmlspecialchars($data['input_notes'] ?? $data['medicalRecord']['Notes'] ?? ''); ?></textarea>
        </div>

        <hr class="form-divider">
        <h3>Prescription</h3>


<!-- Phần hiển thị đơn thuốc hiện tại (Admin và Doctor đều có thể xem) -->
<h4>Current Prescription:</h4>
       <div class="schedule-table-container-cutie">
             <table class="data-table-cutie" id="currentPrescriptionTable"> 
    <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="padding: 5px; border: 1px solid #ccc;text-align:center">Medicine</th>
                <th style="padding: 5px; border: 1px solid #ccc;text-align:center">Dosage</th>
                <th style="padding: 5px; border: 1px solid #ccc;text-align:center">Frequency</th>
                <th style="padding: 5px; border: 1px solid #ccc;text-align:center">Duration</th>
                <th style="padding: 5px; border: 1px solid #ccc;text-align:center">Instructions</th>
                <th style="padding: 5px; border: 1px solid #ccc;text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
 <p id="no-prescription-message" style="display:none; text-align:center; color:#7f8c8d; padding: 15px;">No medication in current draft.</p>



    <hr>
    <div class="prescription-form-area">
    <h4>Add/Edit Medicine Details:</h4>
    <div class="prescription-form-grid">
        <input type="hidden" id="editing_prescription_id" value="">
        <div class="form-group-cutie">
            <label for="form_medicine_id">Medicine:</label>
            <select id="form_medicine_id" style="width: auto; margin-right:10px;">
                <option value="">-- Select Medicine --</option>
                <?php foreach ($data['allMedicines'] as $medicine): ?>
                    <option value="<?php echo $medicine['MedicineID']; ?>">
                        <?php echo htmlspecialchars($medicine['Name']); ?> (<?php echo htmlspecialchars($medicine['Unit'] ?? ''); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="form_dosage" style="margin-left:10px;">Dosage:</label>
            <input type="text" id="form_dosage" placeholder="e.g., 1 tablet, 10ml" style="width: auto; margin-right:10px;">
        </div>
       <div class="form-group-cutie">
            <label for="form_frequency">Frequency:</label>
            <input type="text" id="form_frequency" placeholder="e.g., Twice a day" style="width: auto; margin-right:10px;">
            <label for="form_duration" style="margin-left:10px;">Duration:</label>
            <input type="text" id="form_duration" placeholder="e.g., 7 days" style="width: auto; margin-right:10px;">
        </div>
        <div class="form-group-cutie">
            <label for="form_instructions">Instructions:</label>
            <input type="text" id="form_instructions" placeholder="e.g., After meals" style="width: 80%;">
        </div>
        
    </div>
<button type="button" id="add-or-update-medicine-btn" class="btn-cutie btn-primary-cutie">Add Medicine</button>
        <button type="button" id="cancel-edit-medicine-btn" class="btn-cutie btn-secondary-cutie" style="display:none;">Cancel Edit</button>
    <!-- Input ẩn để lưu trữ danh sách các thuốc sẽ được submit -->
    <div id="hidden-prescriptions-container">
        <!-- JavaScript sẽ thêm các input hidden vào đây -->
    </div>

    <!-- Nút submit chính chỉ hiển thị cho Doctor phụ trách -->
    <div class="page-actions-footer">
            <button type="submit" name="save_record" class="btn-cutie btn-success-cutie">
                <i class="fas fa-save"></i> Save Medical Record & Prescription
            </button>
        </div>

    </form> <!-- Đóng form chính ở đây, sau tất cả các input và nút submit của Doctor -->


    <?php else: // Nếu không phải là Doctor phụ trách (ví dụ: Admin xem) ?>
    <hr style="margin: 20px 0;">
    <h3>Consultation Record (Read-only)</h3>
    <p><strong>Symptoms:</strong><br><?php echo nl2br(htmlspecialchars($data['medicalRecord']['Symptoms'] ?? 'N/A')); ?></p>
    <p><strong>Diagnosis:</strong><br><?php echo nl2br(htmlspecialchars($data['medicalRecord']['Diagnosis'] ?? 'N/A')); ?></p>
    <p><strong>Treatment Plan:</strong><br><?php echo nl2br(htmlspecialchars($data['medicalRecord']['TreatmentPlan'] ?? 'N/A')); ?></p>
    <p><strong>Additional Notes:</strong><br><?php echo nl2br(htmlspecialchars($data['medicalRecord']['Notes'] ?? 'N/A')); ?></p>

    <?php if (!empty($data['currentPrescriptions'])): ?>
        <hr style="margin: 20px 0;">
        <h4>Prescription:</h4>
        <table style="width:100%; margin-bottom:15px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['currentPrescriptions'] as $prescribed): ?>
                <tr>
                    <td><?php echo htmlspecialchars($prescribed['MedicineName']); ?> (<?php echo htmlspecialchars($prescribed['MedicineUnit'] ?? ''); ?>)</td>
                    <td><?php echo htmlspecialchars($prescribed['Dosage']); ?></td>
                    <td><?php echo htmlspecialchars($prescribed['Frequency']); ?></td>
                    <td><?php echo htmlspecialchars($prescribed['Duration']); ?></td>
                    <td><?php echo htmlspecialchars($prescribed['Instructions'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($data['medicalRecord']): ?>
        <hr style="margin: 20px 0;">
        <h4>Prescription:</h4>
        <p>No medication was prescribed for this consultation.</p>
    <?php endif; ?>

    <?php if (!$data['medicalRecord']): ?>
        <p>No medical record has been created for this consultation yet.</p>
    <?php endif; ?>

<?php endif; // Kết thúc if ($data['isConsultingDoctor']) ?>

<!-- Nút Back to Current Consultation và Back to My Schedule hiển thị cho cả Admin và Doctor -->
<div class="page-actions-footer">

    <!-- Nút "Back to My Schedule" sẽ luôn hiển thị -->
    <a href="<?php echo ($data['currentUserRole'] === 'Admin') ? BASE_URL . '/admin/listAllAppointments' : BASE_URL . '/doctor/mySchedule'; ?>" 
       class="btn-cutie btn-secondary-cutie">
        <i class="fas fa-arrow-left"></i> <!-- Thêm icon cho đẹp -->
        Back to Schedule
    </a>

    <!-- Nút "Back to Current Consultation" chỉ hiển thị khi cần -->
    <?php if (isset($data['returnToAppointmentId']) && $data['returnToAppointmentId'] != $data['appointment']['AppointmentID']): ?>
        <a href="<?php echo BASE_URL . '/medicalrecord/viewConsultationDetails/' . $data['returnToAppointmentId']; ?>" 
           class="btn-cutie btn-primary-cutie">
            <i class="fas fa-notes-medical"></i> <!-- Thêm icon cho đẹp -->
            Back to Main Consultation
        </a>
    <?php endif; ?>

</div>
</div>
 </main>

<script>
    // Truyền cờ isConsultingDoctor và currentUserRole từ PHP vào JavaScript
    const IS_CONSULTING_DOCTOR = <?php echo json_encode($data['isConsultingDoctor'] ?? false); ?>;
    const CURRENT_USER_ROLE = <?php echo json_encode($data['currentUserRole'] ?? ''); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const currentPrescriptionTableBody = document.querySelector('#currentPrescriptionTable tbody');
    const medicineFormArea = document.getElementById('medicine-form-area');
    const formMedicineId = document.getElementById('form_medicine_id');
    const formDosage = document.getElementById('form_dosage');
    const formFrequency = document.getElementById('form_frequency');
    const formDuration = document.getElementById('form_duration');
    const formInstructions = document.getElementById('form_instructions');
    const addOrUpdateBtn = document.getElementById('add-or-update-medicine-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-medicine-btn');
    const editingPrescriptionIdInput = document.getElementById('editing_prescription_id');
    const hiddenPrescriptionsContainer = document.getElementById('hidden-prescriptions-container');
    const noPrescriptionMessage = document.getElementById('no-prescription-message');
     const runAiBtn = document.getElementById('run-ai-dss-btn');
    const symptomsTextarea = document.getElementById('symptoms');
    const resultsPanel = document.getElementById('ai-results-panel');
    const resultsContent = document.getElementById('ai-results-content');

    if (runAiBtn && symptomsTextarea && resultsPanel && resultsContent) {
        
        runAiBtn.addEventListener('click', function() {
            const symptoms = symptomsTextarea.value.trim();

            if (symptoms === '') {
                alert('Please enter a description of the symptoms first.');
                symptomsTextarea.focus();
                return;
            }

            // 1. Hiển thị trạng thái loading
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...';
            resultsPanel.style.display = 'block';
            resultsContent.innerHTML = '<p><em>Contacting AI service, please wait...</em></p>';

            // 2. Chuẩn bị dữ liệu để gửi đi
            const formData = new FormData();
            formData.append('symptoms', symptoms);
            // Nếu bạn có CSRF token, hãy thêm nó vào đây
            // formData.append('csrf_token', '<?php echo getCsrfToken(); ?>');
            
            // 3. Gửi request đến "cầu nối" PHP
            fetch('<?php echo BASE_URL; ?>/medicalrecord/getAiSuggestions', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Kiểm tra xem response có thành công không
                if (!response.ok) {
                    // Nếu server trả về lỗi (4xx, 5xx), cố gắng đọc lỗi JSON
                    return response.json().then(errData => {
                        throw new Error(errData.message || `Server error: ${response.status}`);
                    });
                }
                return response.json(); // Nếu thành công, đọc JSON
            })
            
                .then(data => {
    // THAY THẾ KHỐI IF NÀY BẰNG PHIÊN BẢN MỚI
    if (data.success && data.predictions && data.predictions.length > 0) {
        
        let htmlResult = '';

        // THAY THẾ VÒNG LẶP FOREACH BẰNG PHIÊN BẢN MỚI NÀY
        data.predictions.forEach((pred, index) => {
            // Lấy giá trị xác suất
            let probability = parseFloat(pred.probability);
            
            // Gán class màu sắc dựa trên vị trí (index + 1)
            // Ví dụ: index 0 -> rank-1, index 1 -> rank-2, ...
            let rankClass = `rank-${index + 1}`;

            htmlResult += `
                <div class="ai-suggestion-item">
                    <div class="suggestion-label">
                        <span class="suggestion-name">${pred.disease}</span>
                        <span class="suggestion-probability">${probability.toFixed(2)}%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill ${rankClass}" style="width: ${probability}%;"></div>
                    </div>
                </div>`;
        });
        resultsContent.innerHTML = htmlResult;

    } else {
        // Giữ nguyên logic xử lý khi không có kết quả
        resultsContent.innerHTML = `<p>${data.message || 'The AI could not determine a likely diagnosis from the symptoms provided.'}</p>`;
    }
})
            .catch(error => {
                // 5. Hiển thị lỗi nếu có
                console.error('AI Suggestion Error:', error);
                resultsContent.innerHTML = `<p style="color: #e74c3c;"><strong>Error:</strong> ${error.message}</p>`;
            })
            .finally(() => {
                // 6. Khôi phục lại nút bấm sau khi hoàn tất
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-robot"></i> Run AI Suggestion';
            });
        });
    }

    let draftPrescriptions = [];
    let nextDraftIdSuffix = 0; // Chỉ dùng để tạo ID mới duy nhất ở client

    // Ẩn/hiện các phần tử dựa trên vai trò và quyền
    if (!IS_CONSULTING_DOCTOR) {
        if (medicineFormArea) medicineFormArea.style.display = 'none';
        // Các nút khác liên quan đến việc thêm/sửa draft cũng nên được ẩn
        // Ví dụ: nếu có nút "+ Add Another Medicine" riêng thì cũng ẩn đi
    }


    // --- HÀM RENDER BẢNG HIỂN THỊ VÀ CẬP NHẬT INPUT ẨN ---
    function renderAndPopulateHidden() {
        if (!currentPrescriptionTableBody || !hiddenPrescriptionsContainer) {
            console.error("Table body or hidden container not found for prescription rendering.");
            return;
        }

        currentPrescriptionTableBody.innerHTML = '';
        hiddenPrescriptionsContainer.innerHTML = ''; // Xóa input ẩn cũ trước khi tạo mới

        if (draftPrescriptions.length === 0) {
            if (noPrescriptionMessage) noPrescriptionMessage.style.display = 'block';
            const thead = document.querySelector('#currentPrescriptionTable thead');
            if (thead) thead.style.display = 'none';
        } else {
            if (noPrescriptionMessage) noPrescriptionMessage.style.display = 'none';
            const thead = document.querySelector('#currentPrescriptionTable thead');
            if (thead) thead.style.display = ''; // Hoặc 'table-header-group'
        }

        draftPrescriptions.forEach((item, index) => {
            // Render dòng cho bảng hiển thị
            const row = currentPrescriptionTableBody.insertRow();
            row.setAttribute('data-draft-id', item.draftId);

            row.insertCell().textContent = item.medicineText || '--';
            row.insertCell().textContent = item.dosage;
            row.insertCell().textContent = item.frequency;
            row.insertCell().textContent = item.duration;
            row.insertCell().textContent = item.instructions;

            const actionsCell = row.insertCell();
            if (IS_CONSULTING_DOCTOR) { // Chỉ Doctor phụ trách mới có nút Edit/Delete
                actionsCell.innerHTML = `
                    <button type="button" class="btn btn-sm btn-info edit-draft-btn" style="background-color: #17a2b8; margin-right:5px; color:white; border:none; cursor:pointer; padding: 3px 6px;">Edit</button>
                    <button type="button" class="btn btn-sm btn-danger delete-draft-btn" style="background-color: #dc3545; color:white; border:none; cursor:pointer; padding: 3px 6px;">Delete</button>
                `;
            } else {
                actionsCell.textContent = '-'; // Hoặc để trống
            }

            // Tạo input hidden để submit (chỉ cần nếu isConsultingDoctor vì chỉ họ mới submit form chính)
            if (IS_CONSULTING_DOCTOR) {
                Object.keys(item).forEach(key => {
                    if (key !== 'medicineText') { // Không gửi medicineText
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        // Sử dụng index của mảng draftPrescriptions để đảm bảo thứ tự và không bị lỗ hổng index khi xóa
                        hiddenInput.name = `prescriptions[${index}][${key === 'draftId' ? 'prescription_id_temp' : key}]`; // Gửi draftId để biết cái nào mới, cái nào cũ
                        hiddenInput.value = item[key];
                        hiddenPrescriptionsContainer.appendChild(hiddenInput);
                    }
                });
            }
        });
    }

    // --- HÀM RESET FORM THÊM/SỬA THUỐC ---
    function resetMedicineForm() {
        if (!IS_CONSULTING_DOCTOR) return; // Chỉ reset nếu là Doctor
        if(editingPrescriptionIdInput) editingPrescriptionIdInput.value = '';
        if(formMedicineId) formMedicineId.value = '';
        if(formDosage) formDosage.value = '';
        if(formFrequency) formFrequency.value = '';
        if(formDuration) formDuration.value = '';
        if(formInstructions) formInstructions.value = '';
        if(addOrUpdateBtn) addOrUpdateBtn.textContent = 'Add Medicine';
        if(cancelEditBtn) cancelEditBtn.style.display = 'none';
        if(formMedicineId) formMedicineId.focus();
    }

    // --- LOAD ĐƠN THUỐC HIỆN TẠI VÀO DRAFT KHI TRANG TẢI ---
    // Chỉ thực hiện nếu là Doctor và có dữ liệu đơn thuốc cũ
    if (IS_CONSULTING_DOCTOR && <?php echo json_encode(!empty($data['currentPrescriptions'])); ?>) {
        <?php if (!empty($data['currentPrescriptions'])): ?>
            <?php foreach ($data['currentPrescriptions'] as $p): ?>
                let medicineTextOnLoad = 'Unknown Medicine';
                if (formMedicineId) { // Đảm bảo formMedicineId tồn tại
                    const optionFound = Array.from(formMedicineId.options).find(opt => opt.value == '<?php echo $p['MedicineID']; ?>');
                    if (optionFound) {
                        medicineTextOnLoad = optionFound.text;
                    }
                }
                draftPrescriptions.push({
                    draftId: 'db_' + <?php echo $p['PrescriptionID']; ?>, // ID từ DB, có tiền tố 'db_'
                    medicine_id: '<?php echo $p['MedicineID']; ?>',
                    medicineText: medicineTextOnLoad,
                    dosage: '<?php echo addslashes(htmlspecialchars($p['Dosage'])); ?>',
                    frequency: '<?php echo addslashes(htmlspecialchars($p['Frequency'])); ?>',
                    duration: '<?php echo addslashes(htmlspecialchars($p['Duration'])); ?>',
                    instructions: '<?php echo addslashes(htmlspecialchars($p['Instructions'] ?? '')); ?>'
                });
            <?php endforeach; ?>
        <?php endif; ?>
    }
    // Luôn render bảng, dù có dữ liệu hay không, để xử lý a/hiện thead và noPrescriptionMessage
    renderAndPopulateHidden();


    // --- SỰ KIỆN CHO NÚT "ADD/UPDATE MEDICINE" ---
    if (addOrUpdateBtn && IS_CONSULTING_DOCTOR) {
        addOrUpdateBtn.addEventListener('click', function() {
            const medicineIdVal = formMedicineId.value;
            const dosageVal = formDosage.value.trim();

            if (!medicineIdVal || !dosageVal) {
                alert('Please select a medicine and enter the dosage.');
                return;
            }

            const selectedOption = formMedicineId.options[formMedicineId.selectedIndex];
            const medicineTextVal = selectedOption ? selectedOption.text : '';

            const currentItemData = {
                medicine_id: medicineIdVal,
                medicineText: medicineTextVal,
                dosage: dosageVal,
                frequency: formFrequency.value.trim(),
                duration: formDuration.value.trim(),
                instructions: formInstructions.value.trim()
            };

            const editingId = editingPrescriptionIdInput.value;
            if (editingId) { // Đang sửa item đã có trong draft
                const indexToUpdate = draftPrescriptions.findIndex(item => item.draftId == editingId);
                if (indexToUpdate > -1) {
                    // Giữ lại draftId cũ, cập nhật các trường khác
                    draftPrescriptions[indexToUpdate] = { ...draftPrescriptions[indexToUpdate], ...currentItemData };
                }
            } else { // Đang thêm mới
                currentItemData.draftId = 'new_' + (Date.now() + nextDraftIdSuffix++); // ID tạm thời duy nhất cho client
                draftPrescriptions.push(currentItemData);
            }
            renderAndPopulateHidden(); // Render lại bảng và cập nhật input ẩn
            resetMedicineForm();
        });
    }

    // --- SỰ KIỆN CHO NÚT "CANCEL EDIT" ---
    if (cancelEditBtn && IS_CONSULTING_DOCTOR) {
        cancelEditBtn.addEventListener('click', resetMedicineForm);
    }

    // --- SỰ KIỆN CLICK TRÊN BẢNG (EVENT DELEGATION) ---
    if (currentPrescriptionTableBody && IS_CONSULTING_DOCTOR) {
        currentPrescriptionTableBody.addEventListener('click', function(e) {
            const target = e.target;
            const closestRow = target.closest('tr');
            if (!closestRow) return;
            const draftId = closestRow.dataset.draftId;

            if (target.classList.contains('edit-draft-btn')) {
                const itemToEdit = draftPrescriptions.find(item => item.draftId == draftId);
                if (itemToEdit) {
                    editingPrescriptionIdInput.value = itemToEdit.draftId;
                    formMedicineId.value = itemToEdit.medicine_id;
                    formDosage.value = itemToEdit.dosage;
                    formFrequency.value = itemToEdit.frequency;
                    formDuration.value = itemToEdit.duration;
                    formInstructions.value = itemToEdit.instructions;
                    addOrUpdateBtn.textContent = 'Update Medicine';
                    cancelEditBtn.style.display = 'inline-block';
                    formMedicineId.focus();
                }
            } else if (target.classList.contains('delete-draft-btn')) {
                if (confirm('Are you sure you want to remove this medicine from the prescription draft?')) {
                    draftPrescriptions = draftPrescriptions.filter(item => item.draftId != draftId);
                    renderAndPopulateHidden(); // Render lại bảng và cập nhật input ẩn
                    resetMedicineForm(); // Reset form nếu đang sửa item vừa xóa
                }
            }
        });
    }
});
</script>

</body>
</html>