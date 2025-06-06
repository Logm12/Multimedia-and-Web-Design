<?php
// app/views/nurse/appointments/details.php

if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}
if (!defined('BASE_URL')) { 
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../../../../public'));
}

$appointment = $data['appointment'] ?? null;
if (!$appointment) {
    $_SESSION['error_message'] = "Appointment data not found.";
    header('Location: ' . BASE_URL . '/nurse/listAppointments');
    exit();
}

// Dữ liệu cho Topbar và Sidebar
$topbarUserFullName = $data['currentUser']['FullName'] ?? ($_SESSION['user_fullname'] ?? 'Nurse');
$topbarUserAvatar = BASE_URL . '/public/assets/img/default_avatar.png';
if (isset($data['currentUser']['Avatar']) && !empty($data['currentUser']['Avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($data['currentUser']['Avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($data['currentUser']['Avatar'], '/');
} elseif (isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar']) && file_exists(rtrim(PUBLIC_PATH, '/') . '/' . ltrim($_SESSION['user_avatar'], '/'))) {
    $topbarUserAvatar = BASE_URL . '/' . ltrim($_SESSION['user_avatar'], '/');
}

$pageTitleForTopbar = $data['title'] ?? 'Appointment Details #' . htmlspecialchars($appointment['AppointmentID']);
$welcomeMessageForTopbar = 'Detailed information for appointment #' . htmlspecialchars($appointment['AppointmentID']);
$currentUrl = $_GET['url'] ?? 'nurse/appointmentDetails'; // <<<< Đảm bảo $currentUrl đúng

$nurseSidebarMenu = [
    [
        'url' => BASE_URL . '/nurse/dashboard', 
        'icon' => '🏠', 
        'text' => 'Dashboard', 
        'active_key' => 'nurse/dashboard'
    ],
    [
        'url' => BASE_URL . '/nurse/listAppointments', 
        'icon' => '🗓️', 
        'text' => 'Manage Appointments', 
        'active_key' => ['nurse/listAppointments', 'nurse/appointmentDetails', 'nurse/showRecordVitalsForm', 'nurse/showAddNursingNoteForm']
    ],
    [
        'url' => BASE_URL . '/nurse/updateProfile', 
        'icon' => '👤', 
        'text' => 'My Profile', 
        'active_key' => 'nurse/updateProfile'
    ],
];
// Kết thúc phần chuẩn bị dữ liệu cho Topbar và Sidebar
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitleForTopbar); ?> - Nurse Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS chung và sidebar giống như file list.php */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; margin: 0; padding: 0; display: flex; min-height: 100vh;}

.dashboard-sidebar-cutie {
            width: 260px; 
            background-color: rgb(10, 46, 106); 
            background-image: linear-gradient(180deg, rgba(255,255,255,0.07) 0%, rgba(255,255,255,0) 100%);
            color: #ecf0f1; 
            padding: 25px 0; 
            display: flex; 
            flex-direction: column;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.15);
        }
        .sidebar-header-cutie { text-align: center; margin-bottom: 30px; padding: 0 20px; }
        .sidebar-logo-cutie { font-size: 22px; font-weight: 700; color: #fff; text-decoration: none; letter-spacing: 0.5px; }
        .sidebar-nav-cutie ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav-cutie li a { 
            display: flex; align-items: center; padding: 14px 25px; 
            color: #dfe6e9; text-decoration: none; font-size: 15px; 
            font-weight: 500; border-left: 4px solid transparent; 
            transition: all 0.25s ease-in-out; 
        }
        .sidebar-nav-cutie li a:hover, 
        .sidebar-nav-cutie li a.active-nav-cutie { 
            background-color: rgba(255, 255, 255, 0.1); color: #fff; 
            border-left-color: #55efc4; 
        }
        .sidebar-nav-cutie li a .nav-icon-cutie { 
            margin-right: 15px; font-size: 18px; width: 20px; 
            text-align: center; transition: transform 0.2s ease;
        }
        .sidebar-nav-cutie li a:hover .nav-icon-cutie { transform: scale(1.1); }
        .sidebar-nav-cutie li a.logout-link-cutie { margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-nav-cutie li a.logout-link-cutie:hover { background-color: rgba(231, 76, 60, 0.2); border-left-color: #e74c3c; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #bdc3c7; }


        .dashboard-main-content-cutie {
            flex-grow: 1; margin-left: 260px; 
            background-color: #f0f2f5; overflow-y: auto;
        }
        .topbar-shared-cutie {
            display: flex; justify-content: space-between; align-items: center; 
            padding: 15px 30px; background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .topbar-title-section-cutie h2 { font-size: 22px; font-weight: 600; color: #2c3e50; margin:0; }
        .topbar-title-section-cutie p { font-size: 14px; color: #7f8c8d; margin-top: 4px; }
        .topbar-user-actions-cutie { display: flex; align-items: center; gap: 20px; }
        .topbar-user-actions-cutie .icon-button-cutie { 
            background: none; border: none; font-size: 20px; 
            color: #7f8c8d; cursor: pointer; padding: 5px;
            transition: color 0.2s ease;
        }
        .topbar-user-actions-cutie .icon-button-cutie:hover { color: #0a783c; }
        .user-profile-toggle-cutie { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
        .user-profile-toggle-cutie img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #e0e0e0; }
        .user-profile-toggle-cutie span { font-weight: 500; font-size: 14px; color: #0a783c; }
        .user-profile-dropdown-content-cutie {
            display: none; position: absolute; top: calc(100% + 5px); right: 0;
            background-color: #fff; border: 1px solid #ddd;
            border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1001; min-width: 160px; overflow: hidden;
        }
        .user-profile-dropdown-content-cutie a {
            display: block; padding: 10px 15px; text-decoration: none;
            color: #333; font-size: 14px; white-space: nowrap;
        }
        .user-profile-dropdown-content-cutie a:hover { background-color: #f5f5f5; }
        .user-profile-toggle-cutie:hover .user-profile-dropdown-content-cutie,
        .user-profile-dropdown-content-cutie.active-dropdown-cutie { display: block; }

        .actual-page-content-cutie { padding: 30px; }
        
        .breadcrumb-cutie { list-style: none; padding: 0; margin: 0 0 20px 0; display: flex; font-size: 14px; align-items: center;}
        .breadcrumb-cutie li { margin-right: 5px; }
        .breadcrumb-cutie li a { color: #10ac84; text-decoration: none; }
        .breadcrumb-cutie li a:hover { text-decoration: underline; }
        .breadcrumb-cutie li.active-breadcrumb-cutie { color: #5a6268; }
        .breadcrumb-cutie li + li::before { content: "/"; margin-right: 5px; color: #6c757d; }
        .breadcrumb-cutie .nav-icon-cutie { font-size:1em; margin-right:3px; vertical-align: middle;}

        .details-container-cutie {
            background-color: #fff; padding: 30px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .details-container-cutie .details-section-title-cutie { 
            font-size: 1.3em; font-weight: 600; color: #0a783c;
            margin: 25px 0 15px 0; padding-bottom: 10px; border-bottom: 1px solid #eee;
        }
        .details-container-cutie .details-section-title-cutie:first-of-type { margin-top: 0; }
        
        .details-grid-cutie {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px 30px; 
        }
        .detail-item-cutie { margin-bottom: 10px; }
        .detail-item-cutie dt {
            font-weight: 600; color: #495057; font-size: 0.95em;
            margin-bottom: 5px; display: block;
        }
        .detail-item-cutie dd {
            color: #212529; font-size: 1em; margin-left: 0;
            padding: 8px; background-color: #f8f9fa; border-radius: 5px;
            border-left: 3px solid #10ac84;
        }
        .detail-item-cutie dd.reason-notes-cutie {
            white-space: pre-wrap; 
            line-height: 1.6;
        }
        .status-label-cutie { 
            padding: 5px 10px; border-radius: 15px; font-size: 0.8em;
            font-weight: 500; color: #fff; text-transform: capitalize;
            display: inline-block; min-width: 80px; text-align: center;
        }
        .status-scheduled { background-color: #3498db; }
        .status-confirmed { background-color: #2ecc71; }
        .status-completed { background-color: #95a5a6; }
        .status-cancelledbypatient, .status-cancelledbyclinic { background-color: #e74c3c; }
        .status-noshow { background-color: #f39c12; }
        .status-pending { background-color: #f1c40f; color: #333;}

        .details-footer-actions-cutie {
            margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;
        }
        .action-button-cutie { 
            padding: 10px 18px; font-size: 14px; border-radius: 6px;
            text-decoration: none; color: #fff; border: none; cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: inline-flex; align-items: center;
        }
        .action-button-cutie:hover { transform: translateY(-1px); }
        .action-button-cutie .icon-action-cutie { margin-right: 8px; }
        .action-button-cutie.back { background-color: #6c757d; }
        .action-button-cutie.back:hover { background-color: #5a6268; }
        .action-button-cutie.vitals { background-color: #28a745; }
        .action-button-cutie.vitals:hover { background-color: #218838; }
        .action-button-cutie.notes { background-color: #ffc107; color: #212529; }
        .action-button-cutie.notes:hover { background-color: #e0a800; }
        
        .info-message-cutie {
            background-color: #e0f2f1; color: #00796b;
            padding: 15px 20px; margin-bottom: 20px; border-radius: 8px;
            font-size: 15px; text-align: center; font-weight: 500;
            border-left: 5px solid #004d40;
        }

        @media (max-width: 768px) {
            .dashboard-sidebar-cutie { width: 100%; height: auto; position: static; box-shadow: none; }
            .dashboard-main-content-cutie { margin-left: 0; }
            .topbar-shared-cutie { flex-direction: column; align-items: flex-start; gap: 10px; }
            .topbar-title-section-cutie h2 { font-size: 20px; }
            .topbar-user-actions-cutie { width: 100%; justify-content: flex-end; }
            .actual-page-content-cutie { padding: 20px 15px; }
            .breadcrumb-cutie { margin-bottom: 15px; }
            .details-grid-cutie { grid-template-columns: 1fr; }
            .details-footer-actions-cutie { flex-direction: column; }
            .details-footer-actions-cutie .action-button-cutie { width: 100%; justify-content: center; margin-bottom: 10px;}
            .details-footer-actions-cutie div { width: 100%; display: flex; flex-direction: column; gap: 10px;}
        }
    </style>
</head>
<body>
    <aside class="dashboard-sidebar-cutie">
        <div class="sidebar-header-cutie">
            <a href="<?php echo BASE_URL; ?>/nurse/dashboard" class="sidebar-logo-cutie">Nurse Panel</a>
        </div>
        <nav class="sidebar-nav-cutie">
            <ul>
                <?php foreach ($nurseSidebarMenu as $item): ?>
                    <?php 
                    $isActive = false;
                    if (is_array($item['active_key'])) {
                        foreach ($item['active_key'] as $key) {
                            if (strpos($currentUrl, $key) !== false) {
                                $isActive = true;
                                break;
                            }
                        }
                    } else {
                         if (strpos($currentUrl, $item['active_key']) !== false) {
                            if ($item['active_key'] === 'nurse/dashboard' && ($currentUrl === 'nurse/dashboard' || $currentUrl === 'nurse')) {
                                $isActive = true;
                            } elseif ($item['active_key'] !== 'nurse/dashboard') {
                                $isActive = true;
                            }
                        }
                    }
                    ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($item['url']); ?>" class="<?php echo $isActive ? 'active-nav-cutie' : ''; ?>">
                            <span class="nav-icon-cutie"><?php echo $item['icon']; ?></span><?php echo htmlspecialchars($item['text']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="logout-link-cutie">
                        <span class="nav-icon-cutie">🚪</span>Logout
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer-cutie">© <?php echo date('Y'); ?> Healthcare System</div>
    </aside>

    <main class="dashboard-main-content-cutie">
        <header class="topbar-shared-cutie">
            <div class="topbar-title-section-cutie">
                <h2><?php echo htmlspecialchars($pageTitleForTopbar); ?></h2>
                <p><?php echo htmlspecialchars($welcomeMessageForTopbar); ?></p>
            </div>
            <div class="topbar-user-actions-cutie">
                <button class="icon-button-cutie" title="Notifications" onclick="alert('Notifications (coming soon!)');">🔔</button>
                <div class="user-profile-toggle-cutie" id="userProfileToggle">
                    <img src="<?php echo htmlspecialchars($topbarUserAvatar); ?>" alt="Nurse Avatar">
                    <span><?php echo htmlspecialchars($topbarUserFullName); ?> ▼</span>
                    <div class="user-profile-dropdown-content-cutie" id="userProfileDropdown">
                        <a href="<?php echo BASE_URL; ?>/nurse/updateProfile">My Profile</a>
                        <a href="<?php echo BASE_URL; ?>/auth/logout">Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="actual-page-content-cutie">
            <ol class="breadcrumb-cutie">
                <li><a href="<?php echo BASE_URL; ?>/nurse/dashboard"><span class="nav-icon-cutie">🏠</span>Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>/nurse/listAppointments">Appointments</a></li>
                <li class="active-breadcrumb-cutie">Details #<?php echo htmlspecialchars($appointment['AppointmentID']); ?></li>
            </ol>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="info-message-cutie" style="background-color: #d4edda; color: #155724; border-left-color: #28a745;">
                    <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="info-message-cutie" style="background-color: #f8d7da; color: #721c24; border-left-color: #dc3545;">
                    <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="details-container-cutie">
                <h3 class="details-section-title-cutie">Appointment #<?php echo htmlspecialchars($appointment['AppointmentID']); ?> Information</h3>
                <div class="details-grid-cutie">
                    <div class="detail-item-cutie">
                        <dt>Date & Time</dt>
                        <dd><?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($appointment['AppointmentDateTime']))); ?></dd>
                    </div>
                    <div class="detail-item-cutie">
                        <dt>Status</dt>
                        <dd><span class="status-label-cutie status-<?php echo htmlspecialchars(strtolower(str_replace(' ', '', $appointment['Status']))); ?>"><?php echo htmlspecialchars($appointment['Status']); ?></span></dd>
                    </div>
                    <div class="detail-item-cutie">
                        <dt>Booked At</dt>
                        <dd><?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($appointment['CreatedAt']))); ?></dd>
                    </div>
                </div>
                
                <h3 class="details-section-title-cutie">Patient Information</h3>
                <div class="details-grid-cutie">
                    <div class="detail-item-cutie">
                        <dt>Patient Name</dt>
                        <dd><?php echo htmlspecialchars($appointment['PatientFullName']); ?></dd>
                    </div>
                    <div class="detail-item-cutie">
                        <dt>Patient Email</dt>
                        <dd><?php echo htmlspecialchars($appointment['PatientEmail'] ?? 'N/A'); ?></dd>
                    </div>
                    <div class="detail-item-cutie">
                        <dt>Patient Phone</dt>
                        <dd><?php echo htmlspecialchars($appointment['PatientPhoneNumber'] ?? 'N/A'); ?></dd>
                    </div>
                    <div class="detail-item-cutie">
                        <dt>Date of Birth</dt>
                        <dd><?php echo $appointment['PatientDOB'] ? htmlspecialchars(date('F j, Y', strtotime($appointment['PatientDOB']))) : 'N/A'; ?></dd>
                    </div>
                    <div class="detail-item-cutie">
                        <dt>Gender</dt>
                        <dd><?php echo htmlspecialchars($appointment['PatientGender'] ?? 'N/A'); ?></dd>
                    </div>
                </div>

                <h3 class="details-section-title-cutie">Consultation Information</h3>
                 <div class="details-grid-cutie">
                    <div class="detail-item-cutie">
                        <dt>Doctor Name</dt>
                        <dd>Dr. <?php echo htmlspecialchars($appointment['DoctorFullName']); ?></dd>
                    </div>
                    <div class="detail-item-cutie">
                        <dt>Specialization</dt>
                        <dd><?php echo htmlspecialchars($appointment['SpecializationName'] ?? 'N/A'); ?></dd>
                    </div>
                    <?php if(!empty($appointment['NurseFullName'])): ?>
                    <div class="detail-item-cutie">
                        <dt>Assisting Nurse</dt>
                        <dd><?php echo htmlspecialchars($appointment['NurseFullName']); ?></dd>
                    </div>
                    <?php endif; ?>
                </div>
                 <div class="detail-item-cutie" style="grid-column: 1 / -1;">
                    <dt>Reason For Visit</dt>
                    <dd class="reason-notes-cutie"><?php echo nl2br(htmlspecialchars($appointment['ReasonForVisit'] ?? 'N/A')); ?></dd>
                </div>
                <div class="detail-item-cutie" style="grid-column: 1 / -1;">
                    <dt>Clinic Notes (from booking)</dt>
                    <dd class="reason-notes-cutie"><?php echo nl2br(htmlspecialchars($appointment['Notes'] ?? 'N/A')); ?></dd>
                </div>
                
                <h3 class="details-section-title-cutie">Nursing Notes</h3>
                <div class="detail-item-cutie" style="grid-column: 1 / -1;">
                    <dt>Current Nursing Notes:</dt>
                    <dd class="reason-notes-cutie">
                        <?php 
                        $nursingNotesFromDb = $data['medical_record']['NursingNotes'] ?? ($appointment['NursingNotes'] ?? 'No nursing notes recorded yet.');
                        echo nl2br(htmlspecialchars(empty(trim($nursingNotesFromDb)) ? 'No nursing notes recorded yet.' : $nursingNotesFromDb)); 
                        ?>
                    </dd>
                </div>

                <div class="details-footer-actions-cutie">
                    <a href="<?php echo BASE_URL; ?>/nurse/listAppointments?date=<?php echo date('Y-m-d', strtotime($appointment['AppointmentDateTime'])); ?>" class="action-button-cutie back">
                       <span class="icon-action-cutie">⬅️</span> Back to List
                    </a>
                    <div> 
                        <a href="<?php echo BASE_URL; ?>/nurse/showAddNursingNoteForm/<?php echo $appointment['AppointmentID']; ?>" class="action-button-cutie notes">
                            <span class="icon-action-cutie">📝</span> Add/Edit Nursing Note
                        </a>
                        <a href="<?php echo BASE_URL; ?>/nurse/showRecordVitalsForm/<?php echo $appointment['AppointmentID']; ?>" class="action-button-cutie vitals">
                            <span class="icon-action-cutie">💓</span> Record/Edit Vitals
                        </a>
                    </div>      
                </div>
            </div>
        </div>
    </main>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userProfileToggle = document.getElementById('userProfileToggle');
        const userProfileDropdown = document.getElementById('userProfileDropdown');
        if (userProfileToggle && userProfileDropdown) {
            userProfileToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                userProfileDropdown.classList.toggle('active-dropdown-cutie');
            });
            document.addEventListener('click', function(event) {
                if (userProfileDropdown.classList.contains('active-dropdown-cutie') && !userProfileToggle.contains(event.target)) {
                    userProfileDropdown.classList.remove('active-dropdown-cutie');
                }
            });
        }
    });
</script>
</body>
</html>