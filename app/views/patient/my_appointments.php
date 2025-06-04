<?php
// app/views/patient/my_appointments.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2>My Appointments</h2>

<?php if (isset($_SESSION['appointment_message_success'])): ?>
    <p class="success-message"><?php echo $_SESSION['appointment_message_success']; unset($_SESSION['appointment_message_success']); ?></p>
<?php endif; ?>
<?php if (isset($_SESSION['appointment_message_error'])): ?>
    <p class="error-message"><?php echo $_SESSION['appointment_message_error']; unset($_SESSION['appointment_message_error']); ?></p>
<?php endif; ?>

<div class="filter-form" style="margin-bottom: 20px;">
    <form method="GET" action="<?php echo BASE_URL; ?>/appointment/myAppointments"> <!-- Đã sửa Controller Path -->
        <label for="status">Filter by Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <?php foreach($data['allStatuses'] as $statusOption): ?>
                <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo ($data['currentFilter'] == $statusOption) ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], $statusOption)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>


<?php if (!empty($data['appointments'])): ?>
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Date & Time</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Doctor</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Specialization</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Reason</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Status</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['appointments'] as $appointment): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(date('D, M j, Y \a\t g:i A', strtotime($appointment['AppointmentDateTime']))); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['DoctorName']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['SpecializationName'] ?? 'N/A'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['ReasonForVisit'] ?? 'N/A'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <span class="status-<?php echo strtolower(htmlspecialchars($appointment['Status'])); ?>">
                            <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], htmlspecialchars($appointment['Status']))); ?>
                        </span>
                    </td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <?php if ($appointment['Status'] === 'Completed'): ?>
                            <a href="<?php echo BASE_URL . '/patient/viewAppointmentSummary/' . $appointment['AppointmentID']; ?>" class="btn btn-info btn-sm" style="background-color: #17a2b8; color:white; text-decoration:none; padding: 3px 6px;">View Summary</a>
                        <?php endif; ?>
                        <?php
                        $canCancel = false;
                        if (in_array($appointment['Status'], ['Scheduled', 'Confirmed'])) { // SỬA Ở ĐÂY
                            $appointmentTime = strtotime($appointment['AppointmentDateTime']); // SỬA Ở ĐÂY
                            $currentTime = time();
                            if (($appointmentTime - $currentTime) > (24 * 60 * 60)) {
                                $canCancel = true;
                            }
                        }
                        ?>
                         <?php if ($canCancel): ?>
                            <form action="<?php echo BASE_URL; ?>/appointment/cancelByPatient" method="POST" style="display:inline-block; margin-left: <?php echo ($appointment['Status'] === 'Completed') ? '5px' : '0'; ?>;" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['AppointmentID']; ?>">
                                <?php echo generateCsrfInput(); ?>
                                <button type="submit" class="btn btn-danger btn-sm" style="background-color: #dc3545; padding: 3px 6px;">Cancel</button>
                            </form>
                        <?php elseif (in_array($appointment['Status'], ['Scheduled', 'Confirmed'])): ?>
                            <small style="display:inline-block; margin-left: <?php echo ($appointment['Status'] === 'Completed') ? '5px' : '0'; ?>;">
                                Cannot cancel (too close)
                            </small>
                        <?php endif; ?>
                        <?php if (!in_array($appointment['Status'], ['Completed', 'Scheduled', 'Confirmed']) && !$canCancel ): echo '-'; endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have no appointments <?php echo ($data['currentFilter'] !== 'All') ? "with status '" . htmlspecialchars($data['currentFilter']) . "'" : ""; ?>.</p>
<?php endif; ?>

<style>
    .status-scheduled { color: #007bff; }
    .status-confirmed { color: #28a745; font-weight: bold; }
    .status-completed { color: #6c757d; }
    .status-cancelledbypatient, .status-cancelledbyclinic { color: #dc3545; text-decoration: line-through; }
    .status-noshow { color: #ffc107; }
</style>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>