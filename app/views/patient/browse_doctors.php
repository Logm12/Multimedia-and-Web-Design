<?php
// app/views/patient/browse_doctors.php

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_path = ($script_name_dir == '/' || $script_name_dir == '\\') ? '' : rtrim($script_name_dir, '/');
    define('BASE_URL', $protocol . $host . $base_path);
}

$userFullName = $_SESSION['user_fullname'] ?? 'Valued Patient';
$userAvatar = $_SESSION['user_avatar'] ?? BASE_URL . '/public/assets/img/default_avatar.png';

// $data = $data ?? [
//     'title' => 'Browse Doctors',
//     'doctors' => [
//         ['DoctorID' => 1, 'DoctorName' => 'Dr. Emily Carter', 'SpecializationName' => 'Cardiology', 'ExperienceYears' => 10, 'DoctorBio' => 'Specializes in heart conditions.', 'ConsultationFee' => 150.00, 'DoctorAvatar' => BASE_URL . '/public/assets/img/doc1.jpg'],
//         ['DoctorID' => 2, 'DoctorName' => 'Dr. John Smith', 'SpecializationName' => 'Pediatrics', 'ExperienceYears' => 8, 'DoctorBio' => 'Loves working with children.', 'ConsultationFee' => 120.00, 'DoctorAvatar' => BASE_URL . '/public/assets/img/doc2.jpg'],
//         ['DoctorID' => 3, 'DoctorName' => 'Dr. Sarah Wilson', 'SpecializationName' => 'Dermatology', 'ExperienceYears' => 12, 'DoctorBio' => null, 'ConsultationFee' => 180.00, 'DoctorAvatar' => null],
//     ]
// ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Browse Doctors'); ?> - Healthcare System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; display: flex; min-height: 100vh; }

        .dashboard-sidebar-cutie {
            width: 260px; background-color: #343a40; /* Darker sidebar */ color: #f8f9fa;
            padding: 25px 0; display: flex; flex-direction: column; transition: width 0.3s ease;
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 24px; font-weight: 700; color: #fff; text-decoration: none; display: block; }
        
        .sidebar-nav-cutie ul { list-style: none; }
        .sidebar-nav-cutie li a {
            display: flex; align-items: center; padding: 15px 25px; color: #adb5bd; text-decoration: none;
            font-size: 15px; font-weight: 500; transition: background-color 0.2s ease, color 0.2s ease;
            border-left: 4px solid transparent;
        }
        .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie {
            background-color: #495057; color: #fff; border-left-color: #667EEA; /* Accent color for active */
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { margin-right: 12px; font-size: 18px; width: 20px; text-align: center; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #6c757d; }

        .dashboard-main-content-cutie { flex: 1; padding: 30px; overflow-y: auto; }
        .main-header-cutie {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;
            padding-bottom: 20px; border-bottom: 1px solid #dee2e6;
        }
        .page-title-cutie h2 { font-size: 26px; font-weight: 600; color: #212529; }
        
        .user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .user-actions-cutie .icon-button-cutie { background: none; border: none; font-size: 22px; color: #6c757d; cursor: pointer; }
        .user-profile-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile-cutie img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e0e0; }
        .user-profile-cutie span { font-weight: 500; font-size: 15px; color: #495057; }

        .doctors-container-cutie { display: flex; gap: 30px; }
        .doctors-list-panel-cutie { flex: 0 0 300px; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 20px; max-height: calc(100vh - 150px); overflow-y: auto; }
        .doctors-list-panel-cutie h3 { font-size: 18px; margin-bottom: 15px; color: #343a40; border-bottom: 1px solid #eee; padding-bottom: 10px;}
        .doctor-list-item-cutie {
            display: flex; align-items: center; padding: 12px 10px; margin-bottom: 8px; border-radius: 8px; cursor: pointer;
            transition: background-color 0.2s ease; border: 1px solid transparent;
        }
        .doctor-list-item-cutie:hover { background-color: #f8f9fa; border-color: #e9ecef; }
        .doctor-list-item-cutie.selected-doctor-cutie { background-color: #e7e9fc; border-color: #667EEA; }
        .doctor-avatar-small-cutie { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 12px; }
        .doctor-name-specialization-cutie strong { display: block; font-size: 15px; font-weight: 600; color: #343a40; }
        .doctor-name-specialization-cutie span { font-size: 13px; color: #6c757d; }

        .doctor-details-panel-cutie { flex: 1; background-color: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 25px; max-height: calc(100vh - 150px); overflow-y: auto; }
        .doctor-detail-card-cutie { display: none; /* Hidden by default, shown by JS */ }
        .doctor-detail-card-cutie.active-detail-cutie { display: block; animation: fadeInDetailCutie 0.5s ease; }
        @keyframes fadeInDetailCutie { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .detail-header-cutie { display: flex; align-items: center; margin-bottom: 20px; }
        .detail-avatar-large-cutie { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-right: 20px; border: 3px solid #e9ecef; }
        .detail-header-info-cutie h2 { font-size: 22px; font-weight: 600; color: #212529; margin-bottom: 4px; }
        .detail-header-info-cutie p { font-size: 15px; color: #667EEA; font-weight: 500; }

        .detail-section-cutie { margin-bottom: 20px; }
        .detail-section-cutie h4 { font-size: 16px; font-weight: 600; color: #495057; margin-bottom: 8px; border-bottom: 1px solid #f1f3f5; padding-bottom: 5px; }
        .detail-section-cutie p, .detail-section-cutie ul { font-size: 14px; color: #6c757d; line-height: 1.7; }
        .detail-section-cutie ul { list-style: none; padding-left: 0; }
        .detail-section-cutie ul li { padding: 8px 0; border-bottom: 1px dashed #e9ecef; }
        .detail-section-cutie ul li:last-child { border-bottom: none; }
        .detail-section-cutie .book-slot-btn-cutie {
            background-color: #667EEA; color: white; border: none; padding: 8px 15px; font-size: 13px;
            border-radius: 6px; cursor: pointer; transition: background-color 0.2s ease;
        }
        .detail-section-cutie .book-slot-btn-cutie:hover { background-color: #5a67d8; }
        .detail-section-cutie .book-slot-btn-cutie:disabled { background-color: #ccc; cursor: not-allowed; }

        .btn-view-availability-cutie {
            display: inline-block; background-color: #28a745; color: white; padding: 10px 18px;
            border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;
            text-decoration: none; transition: background-color 0.2s ease; margin-top: 15px;
        }
        .btn-view-availability-cutie:hover { background-color: #218838; }
        .btn-view-availability-cutie:disabled { background-color: #ccc; }

        .message-cutie { padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; }
        .success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        @media (max-width: 992px) {
            .doctors-container-cutie { flex-direction: column; }
            .doctors-list-panel-cutie { flex: 0 0 auto; max-height: 300px; margin-bottom: 20px; }
            .doctor-details-panel-cutie { max-height: none; }
        }
        @media (max-width: 768px) { /* Copied from dashboard for sidebar consistency */
            body { flex-direction: column; }
            .dashboard-sidebar-cutie { width: 100%; height: auto; flex-direction: row; overflow-x: auto; padding: 10px 0; justify-content: flex-start; }
            .sidebar-header-cutie { display: none; }
            .sidebar-nav-cutie ul { display: flex; flex-direction: row; }
            .sidebar-nav-cutie li a { padding: 10px 15px; border-left: none; border-bottom: 3px solid transparent; }
            .sidebar-nav-cutie li a:hover, .sidebar-nav-cutie li a.active-nav-cutie { border-bottom-color: #667EEA; background-color: transparent; }
            .sidebar-footer-cutie { display: none; }
            .dashboard-main-content-cutie { padding: 20px; }
        }
/* Thêm vào trong thẻ <style> của browse_doctors.php */

.availability-slots-container-cutie {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.availability-slots-container-cutie h4 {
    font-size: 15px;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 15px;
}

.available-slots-grid-cutie {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Responsive grid */
    gap: 15px;
}

.slot-card-cutie {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.slot-card-cutie:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.slot-info-cutie strong {
    font-size: 14px;
    color: #212529;
    display: block;
    margin-bottom: 5px;
}

.slot-info-cutie span {
    font-size: 13px;
    color: #6c757d;
    display: block;
}
.slot-info-cutie .slot-time-cutie {
    font-weight: 500;
    color: #495057;
}


.book-slot-btn-stylish-cutie {
    background-color: #5cb85c; /* Green for booking */
    color: white;
    border: none;
    padding: 10px 15px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.1s ease;
    margin-top: 12px;
    width: 100%; /* Make button full width of its container (the card) */
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.book-slot-btn-stylish-cutie .icon-calendar-plus-cutie { /* Placeholder for an icon */
    font-weight: bold; /* Example styling for a text icon */
}

.book-slot-btn-stylish-cutie:hover {
    background-color: #4cae4c;
    transform: scale(1.02);
}

.book-slot-btn-stylish-cutie:disabled {
    background-color: #d6d6d6;
    color: #888;
    cursor: not-allowed;
    transform: none;
}

.slot-booked-message-cutie {
    background-color: #d1fae5; /* Light green */
    color: #065f46; /* Dark green */
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    margin-top: 12px;
}
.slot-booked-message-cutie .icon-check-circle-cutie { /* Placeholder */
    margin-right: 8px;
    font-weight: bold;
}

.availability-slots-container-cutie .loading-message-cutie,
.availability-slots-container-cutie .no-slots-message-cutie,
.availability-slots-container-cutie .error-message-slots-cutie {
    font-size: 14px;
    color: #6c757d;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 6px;
    text-align: center;
}
.error-message-slots-cutie {
    color: #721c24;
    background-color: #f8d7da;
}
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie">
            <a href="<?php echo BASE_URL; ?>" class="sidebar-logo-cutie">HealthSys</a>
        </div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/patient/dashboard" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/dashboard') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo $data['browse_doctors_link'] ?? BASE_URL . '/patient/browseDoctors'; ?>" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/browseDoctors') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🩺</span>Browse Doctors</a></li>
                <li><a href="<?php echo BASE_URL; ?>/appointment/myAppointments" class="<?php echo (strpos($_GET['url'] ?? '', 'appointment/myAppointments') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">🗓️</span>My Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/viewAllMedicalRecords" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/viewAllMedicalRecords') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">📜</span>Medical Records</a></li>
                <li><a href="<?php echo BASE_URL; ?>/feedback/list" class="<?php echo (strpos($_GET['url'] ?? '', 'feedback/list') !== false || strpos($_GET['url'] ?? '', 'feedback/submit') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">⭐</span>Feedback</a></li>
                <li><a href="<?php echo BASE_URL; ?>/patient/updateProfile" class="<?php echo (strpos($_GET['url'] ?? '', 'patient/updateProfile') !== false) ? 'active-nav-cutie' : ''; ?>"><span class="nav-icon-cutie">👤</span>Profile</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">
            © <?php echo date('Y'); ?> Healthcare System
        </div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="main-header-cutie">
            <div class="page-title-cutie">
                <h2>Browse Available Doctors</h2>
            </div>
            <div class="user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications">🔔</button>
                <div class="user-profile-cutie" title="My Profile">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="User Avatar">
                    <span><?php echo htmlspecialchars($userFullName); ?></span> ▼
                </div>
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="icon-button-cutie" title="Logout" style="text-decoration:none;">🚪</a>
            </div>
        </header>

        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="message-cutie success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="message-cutie error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>

        <div class="doctors-container-cutie">
            <div class="doctors-list-panel-cutie">
                <h3>All Doctors</h3>
                <?php if (!empty($data['doctors'])): ?>
                    <?php foreach ($data['doctors'] as $doctor): 
                        $defaultAvatar = BASE_URL . '/public/assets/img/default_doctor_avatar.png'; // Path to a default doctor avatar
                        $doctorAvatar = $doctor['DoctorAvatar'] ?? $defaultAvatar;
                    ?>
                        <div class="doctor-list-item-cutie" data-doctor-id="<?php echo $doctor['DoctorID']; ?>">
                            <img src="<?php echo htmlspecialchars($doctorAvatar); ?>" alt="<?php echo htmlspecialchars($doctor['DoctorName'] ?? 'Doctor'); ?>" class="doctor-avatar-small-cutie">
                            <div class="doctor-name-specialization-cutie">
                                <strong><?php echo htmlspecialchars($doctor['DoctorName'] ?? 'N/A'); ?></strong>
                                <span><?php echo htmlspecialchars($doctor['SpecializationName'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No doctors are currently available.</p>
                <?php endif; ?>
            </div>

            <div class="doctor-details-panel-cutie">
                <?php if (!empty($data['doctors'])): ?>
                    <?php foreach ($data['doctors'] as $doctor): 
                        $defaultAvatar = BASE_URL . '/public/assets/img/default_doctor_avatar.png';
                        $doctorAvatar = $doctor['DoctorAvatar'] ?? $defaultAvatar;
                    ?>
                    <div class="doctor-detail-card-cutie" id="detail-for-doctor-<?php echo $doctor['DoctorID']; ?>">
                        <div class="detail-header-cutie">
                            <img src="<?php echo htmlspecialchars($doctorAvatar); ?>" alt="<?php echo htmlspecialchars($doctor['DoctorName'] ?? 'Doctor'); ?>" class="detail-avatar-large-cutie">
                            <div class="detail-header-info-cutie">
                                <h2><?php echo htmlspecialchars($doctor['DoctorName'] ?? 'N/A'); ?></h2>
                                <p><?php echo htmlspecialchars($doctor['SpecializationName'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <div class="detail-section-cutie">
                            <h4>About Dr. <?php echo htmlspecialchars(explode(' ', $doctor['DoctorName'] ?? 'N/A')[count(explode(' ', $doctor['DoctorName'] ?? 'N/A'))-1]); // Last name ?></h4>
                            <p><?php echo !empty($doctor['DoctorBio']) ? nl2br(htmlspecialchars($doctor['DoctorBio'])) : 'No biography provided.'; ?></p>
                        </div>
                        <div class="detail-section-cutie">
                            <h4>Experience</h4>
                            <p><?php echo htmlspecialchars($doctor['ExperienceYears'] ?? '0'); ?> years</p>
                        </div>
                        <div class="detail-section-cutie">
                            <h4>Consultation Fee</h4>
                            <p>$<?php echo htmlspecialchars(number_format($doctor['ConsultationFee'] ?? 0, 2)); ?></p>
                        </div>
                        <button class="btn-view-availability-cutie" data-doctor-id="<?php echo $doctor['DoctorID']; ?>">
                            View Availability & Book
                        </button>
                        <div class="availability-slots" id="slots-for-doctor-<?php echo $doctor['DoctorID']; ?>" style="margin-top:15px;">
                        </div>
                    </div>
                    <?php endforeach; ?>
                     <p id="doctor-detail-placeholder" style="text-align:center; color:#777; margin-top:50px; font-style:italic;">Select a doctor from the list to see details.</p>
                <?php else: ?>
                    <p style="text-align:center; color:#777; margin-top:50px;">No doctors found to display details.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const doctorListItems = document.querySelectorAll('.doctor-list-item-cutie');
    const doctorDetailCards = document.querySelectorAll('.doctor-detail-card-cutie');
    const detailPlaceholder = document.getElementById('doctor-detail-placeholder');

    function showDoctorDetail(doctorId) {
        doctorDetailCards.forEach(card => card.classList.remove('active-detail-cutie'));
        doctorListItems.forEach(item => item.classList.remove('selected-doctor-cutie'));
        const detailCardToShow = document.getElementById('detail-for-doctor-' + doctorId);
        const listItemToSelect = document.querySelector(`.doctor-list-item-cutie[data-doctor-id="${doctorId}"]`);
        if (detailCardToShow) {
            detailCardToShow.classList.add('active-detail-cutie');
            if(detailPlaceholder) detailPlaceholder.style.display = 'none';
        }
        if (listItemToSelect) listItemToSelect.classList.add('selected-doctor-cutie');
    }

    doctorListItems.forEach(item => {
        item.addEventListener('click', function() { showDoctorDetail(this.dataset.doctorId); });
    });
    if (doctorListItems.length > 0 && detailPlaceholder) { /* detailPlaceholder.style.display = 'block'; */ }


    const viewAvailabilityButtons = document.querySelectorAll('.btn-view-availability-cutie');
    viewAvailabilityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const doctorId = this.dataset.doctorId;
            // Important: Ensure slotsContainer is correctly identified for *this* doctor's detail card
            const doctorDetailCard = document.getElementById('detail-for-doctor-' + doctorId);
            if (!doctorDetailCard) return; // Should not happen if button is clicked from a visible card
            const slotsContainer = doctorDetailCard.querySelector('.availability-slots'); // Find within the specific doctor's card
            
            const currentButton = this;

            slotsContainer.innerHTML = '<p class="loading-message-cutie"><em>Loading availability...</em></p>';
            currentButton.disabled = true;
            currentButton.textContent = 'Loading...';

            fetch(`<?php echo BASE_URL; ?>/patient/getDoctorAvailability/${doctorId}`)
            .then(response => {
                if (!response.ok) return response.json().then(errData => { throw new Error(errData.message || `HTTP error! status: ${response.status}`); });
                return response.json();
            })
            .then(data => {
                currentButton.disabled = false;
                currentButton.textContent = 'View Availability & Book';
                slotsContainer.innerHTML = ''; // Clear loading message

                const slotsContainerWrapper = document.createElement('div');
                slotsContainerWrapper.className = 'availability-slots-container-cutie';
                
                const title = document.createElement('h4');
                title.textContent = 'Available Slots:';
                slotsContainerWrapper.appendChild(title);

                if (data.success && data.slots && data.slots.length > 0) {
                    const grid = document.createElement('div');
                    grid.className = 'available-slots-grid-cutie';
                    data.slots.forEach(slot => {
                        const startTime = new Date(`1970-01-01T${slot.StartTime}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                        const endTime = new Date(`1970-01-01T${slot.EndTime}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

                        const slotCard = document.createElement('div');
                        slotCard.className = 'slot-card-cutie';
                        slotCard.id = `slot-item-${slot.AvailabilityID}`;
                        
                        const slotInfo = document.createElement('div');
                        slotInfo.className = 'slot-info-cutie';
                        slotInfo.innerHTML = `<strong>${slot.AvailableDate}</strong>
                                              <span class="slot-time-cutie">${startTime} - ${endTime}</span>`;
                        
                        const bookButton = document.createElement('button');
                        bookButton.className = 'book-slot-btn-stylish-cutie';
                        bookButton.dataset.availabilityId = slot.AvailabilityID;
                        bookButton.dataset.doctorId = slot.DoctorID;
                        // Using innerHTML to easily add an icon-like element if needed
                        bookButton.innerHTML = '<span class="icon-calendar-plus-cutie">📅</span> Book This Slot'; 
                        
                        slotCard.appendChild(slotInfo);
                        slotCard.appendChild(bookButton);
                        grid.appendChild(slotCard);
                    });
                    slotsContainerWrapper.appendChild(grid);
                    slotsContainer.appendChild(slotsContainerWrapper);
                    attachBookSlotListeners(slotsContainer);
                } else if (data.success && data.slots && data.slots.length === 0) {
                    slotsContainer.innerHTML = '<p class="no-slots-message-cutie">No available slots found for the selected period.</p>';
                } else {
                    slotsContainer.innerHTML = `<p class="error-message-slots-cutie">${data.message || 'Could not retrieve availability.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching availability:', error);
                slotsContainer.innerHTML = `<p class="error-message-slots-cutie">Error: ${error.message}. Please try again.</p>`;
                currentButton.disabled = false;
                currentButton.textContent = 'View Availability & Book';
            });
        });
    });

    function attachBookSlotListeners(container) {
        const bookSlotButtons = container.querySelectorAll('.book-slot-btn-stylish-cutie');
        bookSlotButtons.forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', function() {
                const availabilityId = this.dataset.availabilityId;
                const doctorId = this.dataset.doctorId;
                const bookingButton = this;
                const reasonForVisit = prompt("Please enter the reason for your visit (optional):", "");

                if (reasonForVisit !== null) {
                    bookingButton.disabled = true;
                    bookingButton.innerHTML = '<span class="icon-calendar-plus-cutie">⏳</span> Booking...'; // Update with icon

                    fetch('<?php echo BASE_URL; ?>/appointment/bookSlot', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', /* 'X-CSRF-TOKEN': 'your_csrf_token_here' */ },
                        body: JSON.stringify({ availability_id: parseInt(availabilityId), doctor_id: parseInt(doctorId), reason_for_visit: reasonForVisit })
                    })
                    .then(response => response.json().then(data => { if (!response.ok) throw new Error(data.message || `HTTP error! ${response.status}`); return data; }))
                    .then(data => {
                        if (data.success) {
                            alert(data.message || 'Appointment booked successfully!');
                            const slotCard = document.getElementById(`slot-item-${availabilityId}`);
                            if (slotCard) {
                                // Replace card content with booked message
                                slotCard.innerHTML = `<div class="slot-booked-message-cutie">
                                                        <span class="icon-check-circle-cutie">✔️</span> Booked! (ID: ${data.appointment_id})
                                                      </div>`;
                                slotCard.style.textAlign = 'center'; // Center the booked message
                            }
                        } else {
                            alert(data.message || 'Failed to book appointment.');
                            bookingButton.disabled = false; 
                            bookingButton.innerHTML = '<span class="icon-calendar-plus-cutie">📅</span> Book This Slot';
                        }
                    })
                    .catch(error => {
                        console.error('Error booking slot:', error);
                        alert(`Error: ${error.message}.`);
                        bookingButton.disabled = false; 
                        bookingButton.innerHTML = '<span class="icon-calendar-plus-cutie">📅</span> Book This Slot';
                    });
                }
            });
        });
    }
});
</script>
</body>
</html>