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