<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Nurse') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}
$currentUrl = $_GET['url'] ?? '';
$appointment = $data['appointment'] ?? null;
$vitalsInput = $data['input'] ?? []; // Dùng input để điền lại form nếu có lỗi
$errors = $data['errors'] ?? [];

if (!$appointment) {
    $_SESSION['error_message'] = "Appointment data not found for recording vitals.";
    header('Location: ' . BASE_URL . '/nurse/listAppointments');
    exit();
}

$nurseSidebarMenu = [
    ['url' => '/nurse/dashboard', 'icon' => '🏠', 'text' => 'Dashboard', 'active_check' => ['nurse/dashboard']],
    ['url' => '/nurse/listAppointments', 'icon' => '🗓️', 'text' => 'Manage Appointments', 'active_check' => ['nurse/listAppointments', 'nurse/appointmentDetails', 'nurse/recordVitals', 'nurse/showRecordVitalsForm']],
    ['url' => '/nurse/updateProfile', 'icon' => '👤', 'text' => 'My Profile', 'active_check' => ['nurse/updateProfile']],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Record Vital Signs'); ?> - Nurse Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS chung và sidebar giống như file list.php và details.php */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; color: #343a40; margin: 0; padding: 0; }
        .page-wrapper-cutie { display: flex; min-height: 100vh; }

        /* Sidebar Styles */
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

        .form-container-cutie {
            background-color: #fff; padding: 30px; border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .form-container-cutie .form-title-cutie {
            font-size: 1.5em; font-weight: 600; color: #0a783c;
            margin: 0 0 10px 0; 
        }
        .form-container-cutie .form-subtitle-cutie {
            font-size: 1em; color: #5a6268; margin-bottom: 25px;
            padding-bottom: 15px; border-bottom: 1px solid #eee;
        }
        
        .form-grid-cutie {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .form-group-cutie { margin-bottom: 0; /* Bỏ margin bottom vì đã có gap từ grid */ }
        .form-group-cutie label {
            display: block; margin-bottom: 8px; font-weight: 500;
            color: #495057; font-size: 14px;
        }
        .form-group-cutie input[type="text"],
        .form-group-cutie input[type="number"],
        .form-group-cutie textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ced4da;
            border-radius: 6px; font-size: 14px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-group-cutie input[type="text"]:focus,
        .form-group-cutie input[type="number"]:focus,
        .form-group-cutie textarea:focus {
            border-color: #10ac84; box-shadow: 0 0 0 0.2rem rgba(16, 172, 132, 0.25); outline: none;
        }
        .form-group-cutie.full-width-cutie { grid-column: 1 / -1; } /* Cho textarea notes */
        .form-group-cutie textarea { resize: vertical; min-height: 80px; }
        .error-text-cutie { color: #d63031; font-size: 0.85em; margin-top: 5px; display: block; }
        
        .form-actions-cutie {
            margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;
            display: flex; justify-content: flex-end; gap: 10px;
        }
        .button-form-action-cutie {
            padding: 10px 20px; font-size: 14px; border-radius: 6px;
            text-decoration: none; color: #fff; border: none; cursor: pointer;
            font-weight: 500; transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .button-form-action-cutie:hover { transform: translateY(-1px); }
        .button-form-action-cutie.save { background-color: #28a745; }
        .button-form-action-cutie.save:hover { background-color: #218838; }
        .button-form-action-cutie.cancel { background-color: #6c757d; }
        .button-form-action-cutie.cancel:hover { background-color: #5a6268; }

        .message-feedback { /* Copy từ list.php */
            padding: 15px 20px; margin-bottom: 25px; border-radius: 8px; 
            font-size: 15px; text-align: center; font-weight: 500;
            border-left-width: 5px; border-left-style: solid;
        }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; }


        @media (max-width: 768px) {
            .sidebar-container-cutie { width: 100%; height: auto; position: static; padding: 15px 0; }
            .main-content-area-cutie { margin-left: 0; padding: 20px 15px; }
            .page-wrapper-cutie { flex-direction: column; }
            .content-header-cutie { flex-direction: column; align-items: flex-start; }
            .breadcrumb-cutie { margin-top: 10px; }
            .form-grid-cutie { grid-template-columns: 1fr; } /* Stack form groups on mobile */
            .form-actions-cutie { flex-direction: column; gap: 10px; }
            .form-actions-cutie .button-form-action-cutie { width: 100%; }
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
                        $isActive = false;
                        foreach ($item['active_check'] as $check) {
                            if (strpos($currentUrl, $check) !== false) {
                                 if ($check === 'nurse/dashboard' && !($currentUrl === 'nurse/dashboard' || $currentUrl === 'nurse' || $currentUrl === 'nurse/')) {
                                } else {
                                    $isActive = true;
                                    break;
                                }
                            }
                        }
                        ?>
                        <li>
                            <a href="<?php echo BASE_URL . htmlspecialchars($item['url']); ?>" class="<?php echo $isActive ? 'active-nav-cutie' : ''; ?>">
                                <span class="nav-icon-cutie"><?php echo $item['icon']; ?></span><?php echo htmlspecialchars($item['text']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li><a href="<?php echo BASE_URL; ?>/auth/logout" class="logout-link-cutie"><span class="nav-icon-cutie">🚪</span>Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content-area-cutie">
            <section class="content-header-cutie">
                <h1><?php echo htmlspecialchars($data['title'] ?? 'Record Vital Signs'); ?></h1>
                <ol class="breadcrumb-cutie">
                    <li><a href="<?php echo BASE_URL; ?>/nurse/dashboard"><span class="nav-icon-cutie" style="font-size:1em; margin-right:3px;">🏠</span>Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/nurse/listAppointments">Appointments</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>">Details #<?php echo $appointment['AppointmentID']; ?></a></li>
                    <li class="active-breadcrumb-cutie">Record Vitals</li>
                </ol>
            </section>

            <section class="content-body-cutie">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="message-feedback error"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="message-feedback success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>

                <div class="form-container-cutie">
                    <h3 class="form-title-cutie">Vital Signs for Patient: <?php echo htmlspecialchars($appointment['PatientFullName']); ?></h3>
                    <p class="form-subtitle-cutie">Appointment on: <?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($appointment['AppointmentDateTime']))); ?></p>
                    
                    <form action="<?php echo BASE_URL; ?>/nurse/saveVitals/<?php echo $appointment['AppointmentID']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="form-grid-cutie">
                            <div class="form-group-cutie">
                                <label for="HeartRate">Heart Rate (bpm)</label>
                                <input type="number" class="form-control-cutie" id="HeartRate" name="HeartRate" value="<?php echo htmlspecialchars($vitalsInput['HeartRate'] ?? ''); ?>" placeholder="e.g., 72">
                                <?php if (isset($errors['HeartRate'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['HeartRate']); ?></span><?php endif; ?>
                            </div>
                            <div class="form-group-cutie">
                                <label for="Temperature">Temperature (°C)</label>
                                <input type="text" class="form-control-cutie" id="Temperature" name="Temperature" placeholder="e.g., 36.8" value="<?php echo htmlspecialchars($vitalsInput['Temperature'] ?? ''); ?>">
                                <?php if (isset($errors['Temperature'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['Temperature']); ?></span><?php endif; ?>
                            </div>
                            <div class="form-group-cutie" style="grid-column: span 2;">
                                <label>Blood Pressure (mmHg)</label>
                                <div style="display: flex; gap: 10px;">
                                    <div style="flex: 1;">
                                        <input type="number" class="form-control-cutie" name="BloodPressureSystolic" placeholder="Systolic (e.g., 120)" value="<?php echo htmlspecialchars($vitalsInput['BloodPressureSystolic'] ?? ''); ?>">
                                        <?php if (isset($errors['BloodPressureSystolic'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['BloodPressureSystolic']); ?></span><?php endif; ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <input type="number" class="form-control-cutie" name="BloodPressureDiastolic" placeholder="Diastolic (e.g., 80)" value="<?php echo htmlspecialchars($vitalsInput['BloodPressureDiastolic'] ?? ''); ?>">
                                        <?php if (isset($errors['BloodPressureDiastolic'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['BloodPressureDiastolic']); ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group-cutie">
                                <label for="RespiratoryRate">Respiratory Rate (breaths/min)</label>
                                <input type="number" class="form-control-cutie" id="RespiratoryRate" name="RespiratoryRate" value="<?php echo htmlspecialchars($vitalsInput['RespiratoryRate'] ?? ''); ?>" placeholder="e.g., 16">
                                <?php if (isset($errors['RespiratoryRate'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['RespiratoryRate']); ?></span><?php endif; ?>
                            </div>
                            <div class="form-group-cutie">
                                <label for="Weight">Weight (kg)</label>
                                <input type="text" class="form-control-cutie" id="Weight" name="Weight" placeholder="e.g., 65.5" value="<?php echo htmlspecialchars($vitalsInput['Weight'] ?? ''); ?>">
                                <?php if (isset($errors['Weight'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['Weight']); ?></span><?php endif; ?>
                            </div>
                            <div class="form-group-cutie">
                                <label for="Height">Height (cm)</label>
                                <input type="text" class="form-control-cutie" id="Height" name="Height" placeholder="e.g., 170" value="<?php echo htmlspecialchars($vitalsInput['Height'] ?? ''); ?>">
                                <?php if (isset($errors['Height'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['Height']); ?></span><?php endif; ?>
                            </div>
                            <div class="form-group-cutie">
                                <label for="OxygenSaturation">Oxygen Saturation (SpO2 %)</label>
                                <input type="number" class="form-control-cutie" id="OxygenSaturation" name="OxygenSaturation" min="0" max="100" value="<?php echo htmlspecialchars($vitalsInput['OxygenSaturation'] ?? ''); ?>" placeholder="e.g., 98">
                                <?php if (isset($errors['OxygenSaturation'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['OxygenSaturation']); ?></span><?php endif; ?>
                            </div>
                            <div class="form-group-cutie full-width-cutie">
                                <label for="Notes">Additional Notes</label>
                                <textarea class="form-control-cutie" id="Notes" name="Notes" rows="3"><?php echo htmlspecialchars($vitalsInput['Notes'] ?? ''); ?></textarea>
                                 <?php if (isset($errors['Notes'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['Notes']); ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="form-actions-cutie">
                            <a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>" class="button-form-action-cutie cancel">Cancel</a>
                            <button type="submit" class="button-form-action-cutie save">Save Vital Signs</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>