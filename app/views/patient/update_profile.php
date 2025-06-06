<?php
// app/views/patient/update_profile.php

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = ($script_name_dir == '/' || $script_name_dir == '\\') ? '' : rtrim($script_name_dir, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}

$userFullName = $_SESSION['user_fullname'] ?? 'Valued Patient';
// Determine avatar source carefully
$currentAvatarPath = $data['patient']['Avatar'] ?? ($_SESSION['user_avatar'] ?? null);
$avatarSrc = BASE_URL . '/public/assets/img/default_avatar.png'; // Default
if (!empty($currentAvatarPath) && $currentAvatarPath !== 'default_avatar.png') {
    // Check if the path is already a full URL or needs BASE_URL prepended
    if (filter_var($currentAvatarPath, FILTER_VALIDATE_URL)) {
        $avatarSrc = htmlspecialchars($currentAvatarPath);
    } elseif (file_exists(PUBLIC_PATH . $currentAvatarPath)) { // Check relative to PUBLIC_PATH
         $avatarSrc = BASE_URL . '/' . htmlspecialchars($currentAvatarPath);
    } elseif (file_exists($currentAvatarPath)){ // Check if it's an absolute server path but accessible
         // This case is tricky and depends on server setup, usually avatars are in public web root
         // For simplicity, if it's not a URL and not in PUBLIC_PATH, stick to default or log warning
         // error_log("Avatar path issue: " . $currentAvatarPath);
    }
}


// $data = $data ?? [ /* ... existing dummy data ... */ ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Update Profile'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
            width: 260px; background-color: rgb(10,46,106);; color: #fff;
            padding: 25px 0; display: flex; flex-direction: column;
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; display: block; }
        
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a {
            display: flex; align-items: center; padding: 15px 25px; color: #e0e0e0; text-decoration: none;
            font-size: 15px; font-weight: 500; transition: background-color 0.2s ease, color 0.2s ease;
            border-left: 4px solid transparent;
        }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie {
            background-color: rgba(255,255,255,0.1); color: #fff; border-left-color: #fff;
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #c0c0c0; }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        .main-header-cutie {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;
            padding-bottom: 20px; border-bottom: 1px solid #dee2e6;
        }
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; color: #212529; }
        
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

        .profile-form-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; }
        .profile-form-container-cutie fieldset { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .profile-form-container-cutie legend { font-size: 18px; font-weight: 600; color: #343a40; padding: 0 10px; margin-left: 10px; }
        
        .avatar-upload-section-cutie { text-align: center; margin-bottom: 30px; }
        .avatar-preview-cutie { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #e9ecef; margin-bottom:15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .btn-change-avatar-cutie {
            display: inline-block; background-color: #667EEA; color: white; padding: 10px 20px;
            border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;
        }
        .btn-change-avatar-cutie:hover { background-color: #5a67d8; }
        #profile_avatar_input { display: none; }

        .form-grid-cutie { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .form-group-cutie { margin-bottom: 18px; }
        .form-group-cutie label { display: block; font-size: 14px; color: #495057; margin-bottom: 7px; font-weight: 500; }
        .form-group-cutie input[type="text"], .form-group-cutie input[type="email"], .form-group-cutie input[type="password"], .form-group-cutie input[type="date"], .form-group-cutie textarea, .form-group-cutie select {
            width: 100%; padding: 10px 12px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; color: #495057; background-color: #fff;
        }
        .form-group-cutie textarea { min-height: 80px; resize: vertical; }
        .form-group-cutie input:focus, .form-group-cutie textarea:focus, .form-group-cutie select:focus { border-color: #667EEA; box-shadow: 0 0 0 0.2rem rgba(102,126,234,.25); outline: none; }
        
        .form-actions-cutie { margin-top: 30px; display: flex; gap: 15px; justify-content: flex-start; }
        .btn-submit-profile-cutie, .btn-back-profile-cutie {
            padding: 12px 25px; border: none; border-radius: 6px; font-size: 15px; font-weight: 500; cursor: pointer;
            text-decoration: none; text-align: center;
        }
        .btn-submit-profile-cutie { background-color: #28a745; color: white; }
        .btn-submit-profile-cutie:hover { background-color: #218838; }
        .btn-back-profile-cutie { background-color: #6c757d; color: white; }
        .btn-back-profile-cutie:hover { background-color: #5a6268; }

        .message-cutie { padding: 10px 15px; margin-bottom: 20px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .error-message { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .error-text-field-cutie { color: #dc3545; font-size: 12px; margin-top: 4px; }

        @media (max-width: 768px) { /* Sidebar responsive */ }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie"><a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">PulseCare</a></div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/patient/dashboard"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo $data['browse_doctors_link'] ?? BASE_URL . '/patient/browseDoctors'; ?>"><span class="nav-icon-cutie">🩺</span>Browse Doctors</a></li>
                <li><a href="<?php echo BASE_URL; ?>/appointment/myAppointments"><span class="nav-icon-cutie">🗓️</span>My Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/viewAllMedicalRecords"><span class="nav-icon-cutie">📜</span>Medical Records</a></li>
                <li><a href="<?php echo BASE_URL; ?>/feedback/list"><span class="nav-icon-cutie">⭐</span>Feedback</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/updateProfile" class="active-nav-cutie"><span class="nav-icon-cutie">👤</span>Profile</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Update Profile'); ?></h2></div>
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

        <?php if (isset($_SESSION['profile_message_success'])): ?>
            <p class="message-cutie success-message"><?php echo $_SESSION['profile_message_success']; unset($_SESSION['profile_message_success']); ?></p>
        <?php endif; ?>
        <?php if (isset($data['profile_message_error']) || isset($_SESSION['profile_message_error'])): ?>
            <p class="message-cutie error-message">
                <?php
                if (isset($data['profile_message_error'])) echo htmlspecialchars($data['profile_message_error']);
                if (isset($_SESSION['profile_message_error'])) { echo htmlspecialchars($_SESSION['profile_message_error']); unset($_SESSION['profile_message_error']);}
                ?>
            </p>
        <?php endif; ?>

        <div class="profile-form-container-cutie">
            <form action="<?php echo BASE_URL; ?>/patient/updateProfile" method="POST" enctype="multipart/form-data" novalidate>
                <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>

                <div class="avatar-upload-section-cutie">
                    <img id="avatarPreview" src="<?php echo $avatarSrc; ?>" alt="Profile Avatar" class="avatar-preview-cutie">
                    <br>
                    <label for="profile_avatar_input" class="btn-change-avatar-cutie">Change Profile Picture</label>
                    <input type="file" name="profile_avatar" id="profile_avatar_input" accept="image/png, image/jpeg, image/gif">
                    <?php if (isset($data['errors']['profile_avatar'])): ?>
                        <p class="error-text-field-cutie" style="text-align:center; margin-top:10px;"><?php echo htmlspecialchars($data['errors']['profile_avatar']); ?></p>
                    <?php endif; ?>
                </div>

                <fieldset>
                    <legend>Personal Information</legend>
                    <div class="form-grid-cutie">
                        <div class="form-group-cutie">
                            <label for="FullName">Full Name:</label>
                            <input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($data['input']['FullName'] ?? ''); ?>" required>
                            <?php if (isset($data['errors']['FullName'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['FullName']); ?></p><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="Email">Email:</label>
                            <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($data['input']['Email'] ?? ''); ?>" required>
                            <?php if (isset($data['errors']['Email'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['Email']); ?></p><?php endif; ?>
                        </div>
                        <div class="form-group-cutie">
                            <label for="PhoneNumber">Phone Number:</label>
                            <input type="text" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($data['input']['PhoneNumber'] ?? ''); ?>">
                            <?php if (isset($data['errors']['PhoneNumber'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['PhoneNumber']); ?></p><?php endif; ?>
                        </div>
                         <div class="form-group-cutie">
                            <label for="DateOfBirth">Date of Birth:</label>
                            <input type="date" id="DateOfBirth" name="DateOfBirth" value="<?php echo htmlspecialchars($data['input']['DateOfBirth'] ?? ''); ?>">
                        </div>
                        <div class="form-group-cutie">
                            <label for="Gender">Gender:</label>
                            <select id="Gender" name="Gender">
                                <option value="">-- Select --</option>
                                <option value="Male" <?php echo (($data['input']['Gender'] ?? '') == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo (($data['input']['Gender'] ?? '') == 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo (($data['input']['Gender'] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group-cutie" style="grid-column: 1 / -1;">
                            <label for="Address">Address:</label>
                            <textarea name="Address" id="Address" rows="2"><?php echo htmlspecialchars($data['input']['Address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Medical Details</legend>
                    <div class="form-grid-cutie">
                        <div class="form-group-cutie">
                            <label for="BloodType">Blood Type:</label>
                            <input type="text" id="BloodType" name="BloodType" value="<?php echo htmlspecialchars($data['input']['BloodType'] ?? ''); ?>" placeholder="e.g., A+">
                        </div>
                        <div class="form-group-cutie">
                            <label for="InsuranceInfo">Insurance Information:</label>
                            <input type="text" id="InsuranceInfo" name="InsuranceInfo" value="<?php echo htmlspecialchars($data['input']['InsuranceInfo'] ?? ''); ?>">
                        </div>
                        <div class="form-group-cutie" style="grid-column: 1 / -1;">
                            <label for="MedicalHistorySummary">Medical History Summary:</label>
                            <textarea name="MedicalHistorySummary" id="MedicalHistorySummary" rows="3"><?php echo htmlspecialchars($data['input']['MedicalHistorySummary'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Change Password (leave blank if no change)</legend>
                    <div class="form-grid-cutie">
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
                            <?php if (isset($data['errors']['confirm_new_password'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['confirm_new_password']); ?></p><?php endif; ?>
                        </div>
                    </div>
                </fieldset>

                <div class="form-actions-cutie">
                    <button type="submit" class="btn-submit-profile-cutie">Update Profile</button>
                    <a href="<?php echo BASE_URL; ?>/patient/dashboard" class="btn-back-profile-cutie">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('profile_avatar_input');
    const avatarPreview = document.getElementById('avatarPreview');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) { // Basic check for image type
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else if (file) {
                alert("Please select a valid image file (jpg, png, gif), sweetie!");
                event.target.value = null; // Clear the invalid file selection
            }
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('profileDropdownTrigger');
    const menu = document.getElementById('profileDropdownMenu');

    if (trigger && menu) {
        // Sự kiện khi click vào nút trigger
        trigger.addEventListener('click', function(event) {
            event.stopPropagation(); // Ngăn sự kiện click lan ra ngoài
            menu.classList.toggle('hidden');
        });

        // Sự kiện khi click ra ngoài menu thì đóng menu lại
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