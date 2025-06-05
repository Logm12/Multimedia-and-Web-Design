<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}
$currentUrl = $_GET['url'] ?? '';
$appointment = $data['appointment'] ?? null;

if (!$appointment) {
    // Xử lý trường hợp không có dữ liệu appointment (ví dụ: redirect hoặc hiển thị lỗi)
    $_SESSION['error_message'] = "Appointment data not found.";
    header('Location: ' . BASE_URL . '/nurse/listAppointments');
    exit();
}


$nurseSidebarMenu = [
    [
        'url' => '/nurse/dashboard', 
        'icon' => '🏠', 
        'text' => 'Dashboard', 
        'active_logic' => function($url) {
            $parts = explode('/', rtrim($url, '/'));
            $controller = $parts[0] ?? '';
            $action = $parts[1] ?? 'index';
            return ($controller === 'nurse' && ($action === 'dashboard' || $action === 'index'));
        }
    ],
    [
        'url' => '/nurse/listAppointments', 
        'icon' => '🗓️', 
        'text' => 'Manage Appointments', 
        'active_logic' => function($url) {
            $checks = ['nurse/listAppointments', 'nurse/appointmentDetails', 'nurse/showRecordVitalsForm', 'nurse/saveVitals'];
            foreach ($checks as $check) {
                if (strpos($url, $check) !== false) return true;
            }
            return false;
        }
    ],
    [
        'url' => '/nurse/updateProfile', 
        'icon' => '👤', 
        'text' => 'My Profile', 
        'active_logic' => function($url) {
            return (strpos($url, 'nurse/updateProfile') !== false);
        }
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Appointment Details'); ?> - Nurse Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS chung và sidebar giống như file list.php */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; margin: 0; padding: 0; }
        .page-wrapper-cutie { display: flex; min-height: 100vh; }

        .sidebar-container-cutie {
            width: 260px; background: linear-gradient(135deg, rgba(10,57,32,1) 0%, rgba(13,142,100,1) 90%); 
            color: #ecf0f1; padding: 25px 0; display: flex; flex-direction: column;
            height: 100vh; position: fixed; top: 0; left: 0; overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.15); z-index: 1000;
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


        .main-content-area-cutie {
            flex-grow: 1; padding: 30px; margin-left: 260px; 
            background-color: #f0f2f5; overflow-y: auto;
        }
        .content-header-cutie { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .content-header-cutie h1 { font-size: 1.8em; font-weight: 600; color: #0a783c; margin: 0; }
        .breadcrumb-cutie { list-style: none; padding: 0; margin: 0; display: flex; font-size: 14px; }
        .breadcrumb-cutie li { margin-right: 5px; }
        .breadcrumb-cutie li a { color: #10ac84; text-decoration: none; }
        .breadcrumb-cutie li a:hover { text-decoration: underline; }
        .breadcrumb-cutie li.active-breadcrumb-cutie { color: #5a6268; }
        .breadcrumb-cutie li + li::before { content: "/"; margin-right: 5px; color: #6c757d; }


        .details-container-cutie {
            background-color: #fff; padding: 30px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .details-container-cutie .details-title-cutie {
            font-size: 1.5em; font-weight: 600; color: #0a783c;
            margin: 0 0 25px 0; padding-bottom: 15px; border-bottom: 1px solid #eee;
        }
        
        .details-grid-cutie {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px 30px; /* row-gap column-gap */
        }
        .detail-item-cutie { margin-bottom: 15px; }
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
            white-space: pre-wrap; /* Giữ lại xuống dòng */
            line-height: 1.6;
        }
        .status-label-cutie { /* Copy từ list.php */
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
            display: flex; justify-content: space-between; align-items: center;
        }
        .action-button-cutie { /* Copy từ list.php */
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

        @media (max-width: 768px) {
            .sidebar-container-cutie { width: 100%; height: auto; position: static; padding: 15px 0; }
            .main-content-area-cutie { margin-left: 0; padding: 20px 15px; }
            .page-wrapper-cutie { flex-direction: column; }
            .content-header-cutie { flex-direction: column; align-items: flex-start; }
            .breadcrumb-cutie { margin-top: 10px; }
            .details-grid-cutie { grid-template-columns: 1fr; }
            .details-footer-actions-cutie { flex-direction: column; gap: 10px; }
            .details-footer-actions-cutie .action-button-cutie { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper-cutie">
        <aside class="sidebar-container-cutie">
            <div class="sidebar-header-cutie">
                <a href="<?php echo BASE_URL; ?>/nurse/dashboard" class="sidebar-logo-cutie">Nurse Panel</a>
            </div>
<nav class="sidebar-nav-cutie">
    <ul>
        <?php foreach ($nurseSidebarMenu as $item): ?>
            <?php 
            $isActive = isset($item['active_logic']) && is_callable($item['active_logic']) 
                        ? $item['active_logic']($currentUrlForMenu) 
                        : false; 
            ?>
            <li>
                <a href="<?php echo BASE_URL . htmlspecialchars($item['url']); ?>" class="<?php echo $isActive ? 'active-nav-cutie' : ''; ?>">
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
        </aside>

        <main class="main-content-area-cutie">
            <section class="content-header-cutie">
                <h1><?php echo htmlspecialchars($data['title'] ?? 'Appointment Details'); ?></h1>
                <ol class="breadcrumb-cutie">
                    <li><a href="<?php echo BASE_URL; ?>/nurse/dashboard"><span class="nav-icon-cutie" style="font-size:1em; margin-right:3px;">🏠</span>Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/nurse/listAppointments">Appointments</a></li>
                    <li class="active-breadcrumb-cutie">Details #<?php echo htmlspecialchars($appointment['AppointmentID']); ?></li>
                </ol>
            </section>

            <section class="content-body-cutie">
                <div class="details-container-cutie">
                    <h3 class="details-title-cutie">Appointment #<?php echo htmlspecialchars($appointment['AppointmentID']); ?> Information</h3>
                    
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
                    
                    <h3 class="details-title-cutie" style="margin-top:30px; font-size: 1.3em;">Patient Information</h3>
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

                    <h3 class="details-title-cutie" style="margin-top:30px; font-size: 1.3em;">Consultation Information</h3>
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
    <h3 class="details-title-cutie" style="margin-top:30px; font-size: 1.3em;">Nursing Notes</h3>
    <div class="detail-item-cutie" style="grid-column: 1 / -1;">
        <dt>Current Nursing Notes:</dt>
        <dd class="reason-notes-cutie">
            <?php 
            // Giả sử $data['medical_record'] được truyền từ controller và chứa thông tin bệnh án
            // Hoặc $data['appointment'] đã join và có NursingNotes
            $nursingNotesFromDb = $data['appointment']['NursingNotes'] ?? ($data['medical_record']['NursingNotes'] ?? 'No nursing notes recorded yet.');
            echo nl2br(htmlspecialchars(empty(trim($nursingNotesFromDb)) ? 'No nursing notes recorded yet.' : $nursingNotesFromDb)); 
            ?>
        </dd>
    </div>

    <div class="details-footer-actions-cutie">
        <a href="<?php echo BASE_URL; ?>/nurse/listAppointments?date=<?php echo date('Y-m-d', strtotime($appointment['AppointmentDateTime'])); ?>" class="action-button-cutie back">
           <span class="icon-action-cutie">⬅️</span> Back to List
        </a>
        <div> <?php // Bọc 2 nút này lại để dễ style ?>
            <a href="<?php echo BASE_URL; ?>/nurse/showAddNursingNoteForm/<?php echo $appointment['AppointmentID']; ?>" class="action-button-cutie" style="background-color: #ffc107; margin-right:10px;">
                <span class="icon-action-cutie">📝</span> Add/Edit Nursing Note
            </a>
            <a href="<?php echo BASE_URL; ?>/nurse/showRecordVitalsForm/<?php echo $appointment['AppointmentID']; ?>" class="action-button-cutie vitals">
                <span class="icon-action-cutie">💓</span> Record/Edit Vitals
            </a>
        </div>      
    </div>

                    <div class="details-footer-actions-cutie">
                        <a href="<?php echo BASE_URL; ?>/nurse/listAppointments?date=<?php echo date('Y-m-d', strtotime($appointment['AppointmentDateTime'])); ?>" class="action-button-cutie back">
                           <span class="icon-action-cutie">⬅️</span> Back to List
                        </a>
                        <a href="<?php echo BASE_URL; ?>/nurse/showRecordVitalsForm/<?php echo $appointment['AppointmentID']; ?>" class="action-button-cutie vitals">
                            <span class="icon-action-cutie">💓</span> Record/Edit Vitals
                        </a>      
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>