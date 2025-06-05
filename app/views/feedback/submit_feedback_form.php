<?php
// app/views/feedback/submit_feedback_form.php

if (!defined('BASE_URL')) { /* ... BASE_URL definition ... */ }
$userFullName = $_SESSION['user_fullname'] ?? 'Patient';
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_avatar.png';
// $data = $data ?? ['title' => 'Submit Feedback', 'appointmentsForFeedback' => [], 'input' => [], 'errors' => [], 'success_message' => null, 'error_message' => null];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Submit Feedback'); ?> - Healthcare System</title>
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
            <div class="page-title-cutie"><h2><?php echo htmlspecialchars($data['title'] ?? 'Submit Feedback'); ?></h2></div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="User Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
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
</body>
</html>