<?php
// app/views/nurse/nursing_notes/add_edit_form.php
// File này sẽ có layout tương tự các file view khác của Nurse (sidebar, header, ...)
// Tớ sẽ tập trung vào phần form chính

if (session_status() == PHP_SESSION_NONE) session_start();
// ... (kiểm tra session, role Nurse) ...
$currentUrl = $_GET['url'] ?? '';
$appointment = $data['appointment'] ?? null;
$current_nursing_notes = $data['input_notes'] ?? ''; // Ưu tiên input cũ nếu có lỗi
$errors = $data['errors'] ?? [];

if (!$appointment) { /* Xử lý lỗi */ }

$nurseSidebarMenu = [ /* ... định nghĩa menu như các view khác ... */ ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['title'] ?? 'Add/Edit Nursing Note'); ?> - Nurse Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS chung, sidebar, main-content, content-header, breadcrumb giữ nguyên như các view Nurse khác */
        /* ... (copy CSS từ các file view Nurse trước đó) ... */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Inter', sans-serif; background-color: #f0f2f5; 
            color: #343a40; margin: 0; padding: 0; display: flex; min-height: 100vh;
        }

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
            text-align: center;
        }
        .sidebar-nav-cutie li a.logout-link-cutie { margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-nav-cutie li a.logout-link-cutie:hover { background-color: rgba(231, 76, 60, 0.2); border-left-color: #e74c3c; }
        .sidebar-footer-cutie { margin-top: auto; padding: 20px 25px; text-align: center; font-size: 13px; color: #bdc3c7; }

        /* Main Content & Topbar Styles */
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

        .actual-page-content-wrapper-cutie { padding: 30px; }
        .form-container-cutie { /* Style cho container của form */
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
        .form-group-cutie { margin-bottom: 20px; }
        .form-group-cutie label {
            display: block; margin-bottom: 8px; font-weight: 500;
            color: #495057; font-size: 14px;
        }
        .form-group-cutie textarea {
            width: 100%; padding: 10px 12px; border: 1px solid #ced4da;
            border-radius: 6px; font-size: 14px; min-height: 150px; /* Tăng chiều cao */
            transition: border-color 0.2s ease, box-shadow 0.2s ease; resize: vertical;
        }
        .form-group-cutie textarea:focus {
            border-color: #10ac84; box-shadow: 0 0 0 0.2rem rgba(16, 172, 132, 0.25); outline: none;
        }
        .error-text-cutie { color: #d63031; font-size: 0.85em; margin-top: 5px; display: block; }
        
        .form-actions-cutie { /* Style cho các nút action của form */
            margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;
            display: flex; justify-content: flex-end; gap: 10px;
        }
        .button-form-action-cutie { /* Style chung cho nút */
            padding: 10px 20px; font-size: 14px; border-radius: 6px;
            text-decoration: none; color: #fff; border: none; cursor: pointer;
            font-weight: 500; transition: background-color 0.2s ease, transform 0.1s ease;
        }
        .button-form-action-cutie:hover { transform: translateY(-1px); }
        .button-form-action-cutie.save { background-color: #28a745; } /* Nút Save */
        .button-form-action-cutie.save:hover { background-color: #218838; }
        .button-form-action-cutie.cancel { background-color: #6c757d; } /* Nút Cancel */
        .button-form-action-cutie.cancel:hover { background-color: #5a6268; }

        .message-feedback { /* Style cho thông báo session */
            padding: 15px 20px; margin-bottom: 25px; border-radius: 8px; 
            font-size: 15px; text-align: center; font-weight: 500;
            border-left-width: 5px; border-left-style: solid;
        }
        .message-feedback.success { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .message-feedback.error { background-color: #fee2e2; color: #991b1b; border-left-color: #ef4444; }
    </style>
</head>
<body>
    <div class="page-wrapper-cutie">
        <aside class="sidebar-container-cutie">
            <?php // Nhúng code sidebar của Nurse vào đây ?>
        </aside>

        <main class="main-content-area-cutie">
            <section class="content-header-cutie">
                 <h1><?php echo htmlspecialchars($data['title']); ?></h1>
                <ol class="breadcrumb-cutie">
                    <li><a href="<?php echo BASE_URL; ?>/nurse/dashboard">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/nurse/listAppointments">Appointments</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>">Details #<?php echo $appointment['AppointmentID']; ?></a></li>
                    <li class="active-breadcrumb-cutie">Nursing Note</li>
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
                    <h3 class="form-title-cutie">Patient: <?php echo htmlspecialchars($appointment['PatientFullName']); ?></h3>
                    <p class="form-subtitle-cutie">Appointment on: <?php echo htmlspecialchars(date('F j, Y, g:i A', strtotime($appointment['AppointmentDateTime']))); ?></p>
                    
                    <form action="<?php echo BASE_URL; ?>/nurse/saveNursingNote/<?php echo $appointment['AppointmentID']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <div class="form-group-cutie">
                            <label for="nursing_notes">Nursing Note:</label>
                            <textarea class="form-control-cutie" id="nursing_notes" name="nursing_notes" rows="10"><?php echo htmlspecialchars($current_nursing_notes); ?></textarea>
                            <?php if (isset($errors['nursing_notes'])): ?><span class="error-text-cutie"><?php echo htmlspecialchars($errors['nursing_notes']); ?></span><?php endif; ?>
                        </div>
                        
                        <div class="form-actions-cutie">
                            <a href="<?php echo BASE_URL; ?>/nurse/appointmentDetails/<?php echo $appointment['AppointmentID']; ?>" class="button-form-action-cutie cancel">Cancel</a>
                            <button type="submit" class="button-form-action-cutie save">Save Note</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>