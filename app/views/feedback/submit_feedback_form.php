<?php
// app/views/feedback/submit_feedback_form.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Patient';
// Bằng khối code này:
$currentAvatarPath = $_SESSION['user_avatar'] ?? null;
// Determine avatar source carefully
$currentAvatarPath = $_SESSION['user_avatar'] ?? null; // Get from session first
$avatarSrc = BASE_URL . '/public/assets/images/default_avatar.png'; // Default
if (!empty($currentAvatarPath) && $currentAvatarPath !== 'default_avatar.png') {
    if (filter_var($currentAvatarPath, FILTER_VALIDATE_URL)) {
        $avatarSrc = htmlspecialchars($currentAvatarPath);
    } elseif (file_exists(PUBLIC_PATH . $currentAvatarPath)) {
         $avatarSrc = BASE_URL . '/' . htmlspecialchars($currentAvatarPath);
    }
}
// $data = $data ?? ['title' => 'Submit Feedback', 'appointmentsForFeedback' => [], 'input' => [], 'errors' => [], 'success_message' => null, 'error_message' => null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Submit Feedback'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Using similar styles from previous patient pages */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }
        .dashboard-sidebar-cutie { width: 260px; background-color:rgb(10,46,106); color: #fff; padding: 25px 0; display: flex; flex-direction: column; }
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

        .feedback-form-container-cutie { background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; max-width: 700px; margin: 0 auto; }
        .feedback-form-container-cutie h3 { font-size: 20px; font-weight: 600; color: #343a40; margin-bottom: 25px; }
        .form-group-cutie { margin-bottom: 20px; }
        .form-group-cutie label { display: block; font-size: 14px; color: #495057; margin-bottom: 8px; font-weight: 500; }
        .form-group-cutie select, .form-group-cutie textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ced4da; border-radius: 6px;
            font-size: 14px; color: #495057; background-color: #fff;
        }
        .form-group-cutie textarea { min-height: 120px; resize: vertical; }
        .form-group-cutie select:focus, .form-group-cutie textarea:focus { border-color: #667EEA; box-shadow: 0 0 0 0.2rem rgba(102,126,234,.25); outline: none; }
        
        .rating-stars-input-cutie { display: flex; gap: 5px; margin-bottom: 5px; }
        .rating-stars-input-cutie .star-label-cutie { font-size: 28px; color: #e0e0e0; cursor: pointer; transition: color 0.2s ease; }
        .rating-stars-input-cutie .star-label-cutie:hover,
        .rating-stars-input-cutie .star-label-cutie.selected-star-cutie { color: #ffc107; } /* Gold for selected/hover */
        .rating-stars-input-cutie input[type="radio"] { display: none; } /* Hide actual radio buttons */

        .btn-submit-feedback-form-cutie {
            background-color: #667EEA; color: white; padding: 12px 25px; border: none;
            border-radius: 6px; font-size: 15px; font-weight: 500; cursor: pointer;
            transition: background-color 0.2s ease; display: block; width: 100%; margin-top: 10px;
        }
        .btn-submit-feedback-form-cutie:hover { background-color: #5a67d8; }
        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
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
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Submit Feedback'); ?></h2></div>
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

        <?php if (isset($data['success_message']) && $data['success_message']): ?>
            <p class="message-cutie success-message"><?php echo htmlspecialchars($data['success_message']); ?></p>
        <?php endif; ?>
        <?php if (isset($data['error_message']) && $data['error_message']): ?>
            <p class="message-cutie error-message"><?php echo htmlspecialchars($data['error_message']); ?></p>
        <?php endif; ?>

        <div class="feedback-form-container-cutie">
            <h3>Share Your Experience</h3>
            <form action="<?php echo BASE_URL; ?>/feedback/processSubmit" method="POST" novalidate>
                <?php if (function_exists('generateCsrfInput')) { echo generateCsrfInput(); } ?>

                <div class="form-group-cutie">
                    <label>Rating:</label>
                    <div class="rating-stars-input-cutie" id="ratingStars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo (isset($data['input']['rating']) && $data['input']['rating'] == $i) ? 'checked' : ''; ?> required>
                            <label for="star<?php echo $i; ?>" class="star-label-cutie" title="<?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>">☆</label>
                        <?php endfor; ?>
                    </div>
                    <?php if (isset($data['errors']['rating'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['rating']); ?></p><?php endif; ?>
                </div>

                <div class="form-group-cutie">
                    <label for="appointment_doctor">Select Appointment (Doctor & Visit Date):</label>
                    <select name="appointment_doctor" id="appointment_doctor" required>
                        <option value="">-- Select an Appointment --</option>
                        <?php if (!empty($data['appointmentsForFeedback'])): ?>
                            <?php foreach ($data['appointmentsForFeedback'] as $appt): 
                                $optionValue = $appt['AppointmentID'] . '_' . $appt['DoctorID'];
                                $isSelected = (isset($data['input']['appointment_doctor']) && $data['input']['appointment_doctor'] == $optionValue);
                            ?>
                                <option value="<?php echo htmlspecialchars($optionValue); ?>" <?php if($isSelected) echo 'selected'; ?>>
                                    Dr. <?php echo htmlspecialchars($appt['DoctorName']); ?> - Visit on <?php echo htmlspecialchars(date('M j, Y', strtotime($appt['AppointmentDateTime']))); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($data['errors']['appointment_doctor'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['appointment_doctor']); ?></p><?php endif; ?>
                </div>

                <div class="form-group-cutie">
                    <label for="comments">Your Comments:</label>
                    <textarea name="comments" id="comments" placeholder="Tell us about your experience, sweetie..." required><?php echo htmlspecialchars($data['input']['comments'] ?? ''); ?></textarea>
                    <?php if (isset($data['errors']['comments'])): ?><p class="error-text-field-cutie"><?php echo htmlspecialchars($data['errors']['comments']); ?></p><?php endif; ?>
                </div>

                <button type="submit" class="btn-submit-feedback-form-cutie">Submit Feedback</button>
            </form>
        </div>
    </main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ratingStarsContainer = document.getElementById('ratingStars');
    if (ratingStarsContainer) {
        const stars = ratingStarsContainer.querySelectorAll('.star-label-cutie');
        const radios = ratingStarsContainer.querySelectorAll('input[type="radio"]');

        function updateStars(selectedIndex) {
            stars.forEach((star, index) => {
                if (index < selectedIndex) {
                    star.textContent = '★'; // Filled star
                    star.classList.add('selected-star-cutie');
                } else {
                    star.textContent = '☆'; // Empty star
                    star.classList.remove('selected-star-cutie');
                }
            });
        }

        // Initial state based on checked radio
        radios.forEach((radio, index) => {
            if (radio.checked) {
                updateStars(index + 1);
            }
        });

        stars.forEach((star, index) => {
            star.addEventListener('mouseover', () => {
                // Temporarily fill stars up to the hovered one
                for (let i = 0; i <= index; i++) {
                    stars[i].textContent = '★';
                    stars[i].classList.add('selected-star-cutie');
                }
                for (let i = index + 1; i < stars.length; i++) {
                    stars[i].textContent = '☆';
                    stars[i].classList.remove('selected-star-cutie');
                }
            });

            star.addEventListener('mouseout', () => {
                // Revert to selected state
                let selectedRadioIndex = -1;
                radios.forEach((r, idx) => { if (r.checked) selectedRadioIndex = idx; });
                updateStars(selectedRadioIndex + 1);
            });

            star.addEventListener('click', () => {
                radios[index].checked = true;
                updateStars(index + 1);
            });
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