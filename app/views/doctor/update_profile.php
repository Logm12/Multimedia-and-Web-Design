<?php
// app/views/doctor/profile.php

// Giả sử BASE_URL, PUBLIC_PATH và các hằng số khác đã được định nghĩa trong file index.php và có thể truy cập toàn cục.
// Bạn không cần định nghĩa lại chúng ở đây nếu đã tái cấu trúc.
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = ($script_name_dir == '/' || $script_name_dir == '\\') ? '' : rtrim($script_name_dir, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}
$userFullName = $_SESSION['user_fullname'] ?? 'Valued Doctor';

// Đặt ở đầu file view
$currentAvatarPath = $data['input']['Avatar'] ?? $_SESSION['user_avatar'] ?? null;
$avatarSrc = BASE_URL . '/public/assets/img/default_avatar.png'; // Ảnh mặc định

if (!empty($currentAvatarPath)) {
    // Nếu đã là URL đầy đủ thì dùng luôn
    if (filter_var($currentAvatarPath, FILTER_VALIDATE_URL)) {
        $avatarSrc = htmlspecialchars($currentAvatarPath);
    } 
    // Nếu là đường dẫn tương đối, ghép với BASE_URL
    else {
        $avatarSrc = BASE_URL . '/' . ltrim(htmlspecialchars($currentAvatarPath), '/');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Update Profile'); ?> - Healthcare System</title>
    <!-- Các link CSS và font bạn đang dùng -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Link đến file CSS chung của Doctor -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/doctor_style.css"> 
    <!-- Ghi chú: Hãy tạo file CSS này và copy CSS từ file patient, chỉnh sửa màu sắc nếu cần -->
   <style>
        /* Tạm thời để CSS ở đây để dễ hình dung. Lý tưởng nhất là chuyển vào file doctor_style.css */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }
        .dashboard-sidebar-cutie {
           width: 260px; background-color:rgb(10,46,106); color: #fff;
            padding: 25px 0; display: flex; flex-direction: column;
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; }
        
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a {
            display: flex; align-items: center; padding: 15px 25px; color: #bdc3c7; text-decoration: none;
            font-size: 15px; font-weight: 500; transition: all 0.2s ease; border-left: 4px solid transparent;
        }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie {
            background-color: #34495e; color: #fff; border-left-color: #3498db; /* Accent color */
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #7f8c8d; }
        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        /* Copy các style còn lại từ file patient và dán vào đây hoặc vào file CSS chung */
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; }
        .main-header-cutie { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #dee2e6; }
        .profile-form-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; }
        .profile-form-container-cutie fieldset { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .profile-form-container-cutie legend { font-size: 18px; font-weight: 600; color: #3498db; padding: 0 10px; margin-left: 10px; }
        /* Tìm đến class này */
.avatar-upload-section-cutie {
    text-align: center;
    margin-bottom: 30px;
}

/* Yêu cầu ảnh preview hiển thị như một khối và căn giữa */
.avatar-preview-cutie {
    display: block; /* Quan trọng: làm cho ảnh chiếm trọn một hàng */
    margin-left: auto; /* Căn giữa theo chiều ngang */
    margin-right: auto; /* Căn giữa theo chiều ngang */
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #e9ecef;
    margin-bottom: 15px; /* Tạo khoảng cách với nút bên dưới */
}
        .btn-change-avatar-cutie { display: inline-block; background-color: #3498db; color: white; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; }
        .form-grid-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .form-group-cutie { margin-bottom: 18px; }
        .form-group-cutie label { display: block; font-size: 14px; color: #495057; margin-bottom: 7px; font-weight: 500; }
        .form-group-cutie input, .form-group-cutie textarea, .form-group-cutie select { width: 100%; padding: 10px 12px; border: 1px solid #ced4da; border-radius: 6px; font-size: 14px; }
        .form-group-cutie input:focus, .form-group-cutie textarea:focus, .form-group-cutie select:focus { border-color: #3498db; box-shadow: 0 0 0 0.2rem rgba(52,152,219,.25); outline: none; }
        .form-actions-cutie { margin-top: 30px; display: flex; gap: 15px; justify-content: flex-start; }
        .btn-submit-profile-cutie { background-color: #28a745; color: white; padding: 12px 25px; border: none; border-radius: 6px; font-size: 15px; font-weight: 500; cursor: pointer; }
        .btn-back-profile-cutie { background-color: #6c757d; color: white; text-decoration: none; text-align: center; padding: 12px 25px; border-radius: 6px; font-size: 15px; font-weight: 500; }
        .message-cutie { padding: 10px 15px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d1fae5; color: #065f46; }
        .error-message { background-color: #fee2e2; color: #991b1b; }
        .error-text-field-cutie { color: #dc3545; font-size: 12px; margin-top: 4px; }
        /* Copy CSS cho dropdown menu ở đây */
        .user-actions { display: flex; align-items: center; gap: 15px; } .icon-button { background: none; border: none; font-size: 20px; color: #6c757d; cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.2s ease; } .icon-button:hover { background-color: #f1f3f5; } .profile-dropdown { position: relative; } .profile-trigger { display: flex; align-items: center; gap: 8px; cursor: pointer; background-color: transparent; border: none; padding: 4px 8px; border-radius: 20px; transition: all 0.2s ease; } .profile-trigger:hover { background-color: #e9ecef; } .profile-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; } .profile-name { font-weight: 500; } .dropdown-arrow { font-size: 12px; } .dropdown-menu { position: absolute; top: calc(100% + 10px); right: 0; background-color: #fff; border-radius: 8px; box-shadow: 0 5px 25px rgba(0,0,0,0.1); min-width: 200px; z-index: 1000; border: 1px solid #e9ecef; padding: 8px 0; overflow: hidden; transition: all 0.2s ease; } .dropdown-menu.hidden { opacity: 0; transform: translateY(-10px); pointer-events: none; } .dropdown-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; font-size: 14px; color: #495057; text-decoration: none; transition: all 0.2s ease; } .dropdown-item i { width: 16px; text-align: center; color: #868e96; } .dropdown-item:hover { background-color: #f8f9fa; } .dropdown-item-logout:hover { background-color: #fff5f5; color: #e03131; } .dropdown-item-logout:hover i { color: #e03131; } .dropdown-divider { height: 1px; background-color: #e9ecef; margin: 8px 0; }
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
                <li><a href="<?php echo BASE_URL; ?>/doctor/myLeaveRequests" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/requestTimeOff') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">✈️</span>My Leave Requests</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/notifications" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/notifications') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🔔</span>Notifications</a></li>
                <li><a href="<?php echo BASE_URL; ?>/doctor/updateProfile" class="<?php echo (strpos($_GET['url'] ?? '', 'doctor/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>Update Profile</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Update Doctor Profile'); ?></h2></div>
            <!-- Header với dropdown menu người dùng -->
            <div class="user-actions">
                <button class="icon-button" title="Notifications"><i class="fas fa-bell"></i></button>
                <div class="profile-dropdown">
                    <button class="profile-trigger" id="profileDropdownTrigger">
                        <img src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="User Avatar" class="profile-avatar">
                        <span class="profile-name"><?php echo htmlspecialchars($userFullName); ?></span>
                        <i class="fas fa-caret-down dropdown-arrow"></i>
                    </button>
                    <div class="dropdown-menu hidden" id="profileDropdownMenu">
                        <a href="<?php echo BASE_URL; ?>/doctor/updateprofile" class="dropdown-item"><i class="fas fa-user-circle"></i> My Profile</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo BASE_URL; ?>/auth/logout" class="dropdown-item dropdown-item-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hiển thị thông báo thành công / lỗi -->
        <?php if (isset($_SESSION['profile_message_success'])): ?>
            <p class="message-cutie success-message"><?php echo $_SESSION['profile_message_success']; unset($_SESSION['profile_message_success']); ?></p>
        <?php endif; ?>
        <?php if (isset($data['profile_message_error'])): ?>
            <p class="message-cutie error-message"><?php echo htmlspecialchars($data['profile_message_error']); ?></p>
        <?php endif; ?>

        <div class="profile-form-container-cutie">
            <!-- Thay đổi action của form -->
            <form action="<?php echo BASE_URL; ?>/doctor/updateprofile" method="POST" enctype="multipart/form-data" novalidate>
                <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>

                <!-- Phần Upload Avatar (giữ nguyên) -->
                <!-- Cấu trúc MỚI - Tốt hơn -->
<div class="avatar-upload-section-cutie">
    <img id="avatarPreview" src="<?php echo $avatarSrc; ?>" alt="Profile Avatar" class="avatar-preview-cutie">
    
    <!-- Nút bấm giờ là một LABEL, nó sẽ kích hoạt input file bị ẩn -->
    <label for="profile_avatar_input" class="btn-change-avatar-cutie">
        <i class="fas fa-camera"></i> <!-- Thêm icon cho đẹp -->
        Change Profile Picture
    </label>
    
    <!-- Input file thật, sẽ được ẩn đi bằng CSS -->
    <input type="file" name="profile_avatar" id="profile_avatar_input" accept="image/png, image/jpeg, image/gif" style="display: none;">

    <?php if (isset($data['errors']['profile_avatar'])): ?>
        <p class="error-text-field-cutie" style="text-align:center; margin-top:10px;"><?php echo htmlspecialchars($data['errors']['profile_avatar']); ?></p>
    <?php endif; ?>
</div>

                <!-- Thay đổi các fieldset cho phù hợp với Doctor -->
                <fieldset>
                    <legend>Basic Information</legend>
                    <div class="form-grid-cutie">
                        <div class="form-group-cutie">
                            <label for="FullName">Full Name:</label>
                            <input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($data['input']['FullName'] ?? ''); ?>" required>
                            <?php if (isset($data['errors']['FullName'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['FullName']); ?></p><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="Email">Email:</label>
                            <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($data['input']['Email'] ?? ''); ?>" required readonly>
                             <small>Email cannot be changed.</small>
                        </div>
                        <div class="form-group-cutie">
                            <label for="PhoneNumber">Phone Number:</label>
                            <input type="text" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($data['input']['PhoneNumber'] ?? ''); ?>">
                            <?php if (isset($data['errors']['PhoneNumber'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['PhoneNumber']); ?></p><?php endif; ?>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Professional Details</legend>
                    <div class="form-grid-cutie">
                        <div class="form-group-cutie">
                            <label for="specialization_id">Specialization:</label>
                            <select id="specialization_id" name="specialization_id" required>
                                <option value="">-- Select Specialization --</option>
                                <?php if (!empty($data['specializations'])): ?>
                                    <?php foreach ($data['specializations'] as $spec): ?>
                                        <option value="<?php echo $spec['SpecializationID']; ?>" <?php echo (($data['input']['SpecializationID'] ?? '') == $spec['SpecializationID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($spec['SpecializationName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                             <?php if (isset($data['errors']['specialization_id'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['specialization_id']); ?></p><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="experience_years">Years of Experience:</label>
                            <input type="number" id="experience_years" name="experience_years" value="<?php echo htmlspecialchars($data['input']['ExperienceYears'] ?? '0'); ?>" min="0">
                        </div>
                        <div class="form-group-cutie" style="grid-column: 1 / -1;">
                            <label for="doctor_bio">Biography / Professional Statement:</label>
                            <textarea name="doctor_bio" id="doctor_bio" rows="5" placeholder="Introduce yourself to the patients..."><?php echo htmlspecialchars($data['input']['DoctorBio'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </fieldset>

                <!-- Fieldset đổi mật khẩu (giữ nguyên) -->
                <fieldset>
                    <legend>Change Password (leave blank if no change)</legend>
                    <div class="form-grid-cutie">
                        <!-- Giữ nguyên các trường current_password, new_password, confirm_new_password -->
                         <div class="form-group-cutie">
                            <label for="current_password">Current Password:</label>
                            <input type="password" id="current_password" name="current_password" autocomplete="current-password">
                            <?php if (isset($data['errors']['current_password'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['current_password']); ?></p><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="new_password">New Password:</label>
                            <input type="password" id="new_password" name="new_password" autocomplete="new-password">
                            <?php if (isset($data['errors']['new_password'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['new_password']); ?></p><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="confirm_new_password">Confirm New Password:</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" autocomplete="new-password">
                        </div>
                    </div>
                </fieldset>

                <div class="form-actions-cutie">
                    <button type="submit" class="btn-submit-profile-cutie">Update Profile</button>
                    <a href="<?php echo BASE_URL; ?>/doctor/dashboard" class="btn-back-profile-cutie">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </main>

<script>
// Giữ nguyên script xử lý avatar preview
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('profile_avatar_input');
    const avatarPreview = document.getElementById('avatarPreview');
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) { avatarPreview.src = e.target.result; }
                reader.readAsDataURL(file);
            }
        });
    }

    // Giữ nguyên script xử lý dropdown menu
    const trigger = document.getElementById('profileDropdownTrigger');
    const menu = document.getElementById('profileDropdownMenu');
    if (trigger && menu) {
        trigger.addEventListener('click', function(event) {
            event.stopPropagation();
            menu.classList.toggle('hidden');
        });
        window.addEventListener('click', function(event) {
            if (!menu.contains(event.target) && !trigger.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    }
});
</script>
</body>
</html>