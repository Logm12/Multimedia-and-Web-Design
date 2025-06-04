<?php
// app/views/admin/appointments/list.php
require_once __DIR__ . '/../../layouts/header.php'; // Hoặc header chung
?>

<h2><?php echo htmlspecialchars($data['title']); ?></h2>

<?php if (isset($_SESSION['admin_appointment_message_success'])): ?>
    <p class="success-message" style="margin-bottom: 15px; padding:10px; border:1px solid green; background-color:#e6ffe6;">
        <?php echo $_SESSION['admin_appointment_message_success']; unset($_SESSION['admin_appointment_message_success']); ?>
    </p>
<?php endif; ?>
<?php if (isset($_SESSION['admin_appointment_message_error'])): ?>
    <p class="error-message" style="margin-bottom: 15px; padding:10px; border:1px solid red; background-color:#ffe0e0;">
        <?php echo $_SESSION['admin_appointment_message_error']; unset($_SESSION['admin_appointment_message_error']); ?>
    </p>
<?php endif; ?>

<div class="controls" style="margin-bottom: 20px; padding:10px; border:1px solid #eee; background-color:#f9f9f9;">
    <form method="GET" action="<?php echo BASE_URL; ?>/admin/listAllAppointments">
        <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div>
                <label for="date_from">Date From:</label><br>
                <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($data['filters']['date_from'] ?? ''); ?>" class="form-control-sm">
            </div>
            <div>
                <label for="date_to">Date To:</label><br>
                <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($data['filters']['date_to'] ?? ''); ?>" class="form-control-sm">
            </div>
            <div>
                <label for="doctor_id_filter">Doctor:</label><br>
                <select name="doctor_id" id="doctor_id_filter" class="form-control-sm">
                    <option value="">All Doctors</option>
                    <?php if (!empty($data['doctorsForFilter'])): ?>
                        <?php foreach ($data['doctorsForFilter'] as $doctor): ?>
                            <option value="<?php echo $doctor['DoctorID']; ?>" <?php echo (($data['filters']['doctor_id'] ?? '') == $doctor['DoctorID']) ? 'selected' : ''; ?>>
                                Dr. <?php echo htmlspecialchars($doctor['DoctorName']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label for="patient_search_filter">Patient (Name/Phone):</label><br>
                <input type="text" name="patient_search" id="patient_search_filter" value="<?php echo htmlspecialchars($data['filters']['patient_search'] ?? ''); ?>" placeholder="Patient Name or Phone" class="form-control-sm">
            </div>
            <div>
                <label for="status_filter_app">Status:</label><br>
                <select name="status" id="status_filter_app" class="form-control-sm">
                    <?php foreach ($data['allStatuses'] as $statusOption): ?>
                        <option value="<?php echo $statusOption; ?>" <?php echo (($data['filters']['status'] ?? 'All') == $statusOption) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], $statusOption)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-sm" style="background-color:#007bff;">Filter</button>
                <a href="<?php echo BASE_URL; ?>/admin/listAllAppointments" class="btn btn-secondary btn-sm" style="background-color:#6c757d; text-decoration:none; color:white;">Clear Filters</a>
            </div>
        </div>
    </form>
</div>


<?php if (!empty($data['appointments'])): ?>
    <p>Total appointments found: <?php echo count($data['appointments']); ?></p>
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">ID</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Date & Time</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Doctor</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Specialization</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Patient Name</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Patient Phone</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Reason</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Status</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">EMR</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['appointments'] as $appointment): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $appointment['AppointmentID']; ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(date('D, M j, Y \a\t g:i A', strtotime($appointment['AppointmentDateTime']))); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">Dr. <?php echo htmlspecialchars($appointment['DoctorName']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['SpecializationName'] ?? 'N/A'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['PatientName']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['PatientPhoneNumber'] ?? 'N/A'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo nl2br(htmlspecialchars($appointment['ReasonForVisit'] ?? 'N/A')); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <span class="status-<?php echo strtolower(htmlspecialchars($appointment['Status'])); // Dùng class này để style màu nếu cần ?>">
                            <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], htmlspecialchars($appointment['Status']))); ?>
                        </span>
                    </td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <?php if (!empty($appointment['RecordID'])): ?>
                            <a href="<?php echo BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appointment['AppointmentID']; // Hoặc dùng RecordID nếu link EMR theo RecordID ?>" target="_blank" class="btn btn-sm" style="background-color:#28a745; color:white; text-decoration:none;padding:2px 5px;">View EMR</a>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <?php if (in_array($appointment['Status'], ['Scheduled', 'Confirmed'])): ?>
                            <!-- Admin có thể cần quyền hủy lịch -->
                            <!--
                            <form action="<?php echo BASE_URL; ?>/admin/cancelAppointment" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                <input type="hidden" name="appointment_id_to_cancel" value="<?php echo $appointment['AppointmentID']; ?>">
                                <?php // echo generateCsrfInput(); ?>
                                <button type="submit" class="btn btn-sm btn-danger" style="background-color:#dc3545;">Cancel</button>
                            </form>
                            -->
                            <small>Can be cancelled</small>
                        <?php elseif ($appointment['Status'] === 'Completed'): ?>
                            <small>Completed</small>
                        <?php else: ?>
                            <small><?php echo htmlspecialchars($appointment['Status']); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No appointments found matching your criteria.
        <?php if (array_filter($data['filters'])): // Kiểm tra xem có filter nào đang được áp dụng không?>
            <a href="<?php echo BASE_URL; ?>/admin/listAllAppointments">Clear all filters?</a>
        <?php endif; ?>
    </p>
<?php endif; ?>

<div style="margin-top:20px;">
    <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="btn btn-secondary" style="background-color:#6c757d; text-decoration:none; color:white;">« Back to Admin Dashboard</a>
</div>

<style> /* Thêm một chút style cho các status nếu muốn */
    .status-scheduled { color: #007bff; }
    .status-confirmed { color: #17a2b8; font-weight: bold;}
    .status-completed { color: #28a745; }
    .status-cancelledbypatient, .status-cancelledbyclinic { color: #dc3545; text-decoration: line-through;}
    .status-noshow { color: #ffc107; }
</style>

<?php
require_once __DIR__ . '/../../layouts/footer.php'; // Hoặc footer chung
?>