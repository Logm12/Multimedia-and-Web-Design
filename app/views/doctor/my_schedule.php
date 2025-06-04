<?php
// app/views/doctor/my_schedule.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2>My Schedule</h2>

<?php if (isset($_SESSION['schedule_message_success'])): ?>
    <p class="success-message"><?php echo $_SESSION['schedule_message_success']; unset($_SESSION['schedule_message_success']); ?></p>
<?php endif; ?>
<?php if (isset($_SESSION['schedule_message_error'])): ?>
    <p class="error-message"><?php echo $_SESSION['schedule_message_error']; unset($_SESSION['schedule_message_error']); ?></p>
<?php endif; ?>

<!-- Form lọc (ví dụ đơn giản) -->
<!-- Trong app/views/doctor/my_schedule.php -->
<form method="GET" action="<?php echo BASE_URL; ?>/doctor/mySchedule" id="scheduleFilterForm" style="margin-bottom: 20px; display: flex; gap: 15px;">
    <div>
        <label for="date_filter">Date:</label>
        <select name="date" id="date_filter">
            <option value="all_upcoming" <?php echo ($data['currentDateFilter'] == 'all_upcoming') ? 'selected' : ''; ?>>All Upcoming</option>
            <option value="today" <?php echo ($data['currentDateFilter'] == 'today') ? 'selected' : ''; ?>>Today</option>
            <option value="this_week" <?php echo ($data['currentDateFilter'] == 'this_week') ? 'selected' : ''; ?>>This Week</option>
            <option value="all_time" <?php echo ($data['currentDateFilter'] == 'all_time') ? 'selected' : ''; ?>>All Time</option>
            <!-- Thêm input date range nếu muốn tùy chỉnh -->
        </select>
    </div>
    <div>
        <label for="status_filter">Status:</label>
        <select name="status" id="status_filter">
             <?php foreach($data['allStatuses'] as $statusOption): ?>
                <option value="<?php echo htmlspecialchars($statusOption); ?>" <?php echo ($data['currentStatusFilter'] == $statusOption) ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], $statusOption)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn">Filter</button>
</form>


<?php if (!empty($data['appointments'])): ?>
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Date & Time</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Patient Name</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Patient Phone</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Reason</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Status</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['appointments'] as $appointment): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(date('D, M j, Y \a\t g:i A', strtotime($appointment['AppointmentDateTime']))); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['PatientName']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['PatientPhoneNumber'] ?? 'N/A'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['ReasonForVisit'] ?? 'N/A'); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                         <span class="status-<?php echo strtolower(htmlspecialchars($appointment['Status'])); ?>">
                            <?php echo ucfirst(str_replace(['ByPatient', 'ByClinic'], [' by Patient', ' by Clinic'], htmlspecialchars($appointment['Status']))); ?>
                        </span>
                    </td>
                    <!-- Trong tbody của bảng lịch hẹn -->
                <td style="padding: 8px; border: 1px solid #ddd;">
                    <?php if (in_array($appointment['Status'], ['Scheduled', 'Confirmed', 'Completed'])): // Cho phép xem/sửa notes cả khi đã completed ?>
                        <a href="<?php echo BASE_URL . '/medicalrecord/viewConsultationDetails/' . $appointment['AppointmentID']; ?>" class="btn btn-sm" style="background-color: #17a2b8; color:white; text-decoration:none; padding: 3px 6px;">
                            <?php echo ($appointment['Status'] === 'Completed') ? 'View/Edit Notes' : 'Start Consultation / Add Notes'; ?>
                        </a>
                        <?php if (in_array($appointment['Status'], ['Scheduled', 'Confirmed'])): ?>
                        <form action="<?php echo BASE_URL; ?>/appointment/markAsCompleted" method="POST" style="display:inline-block; margin-left:5px;" onsubmit="return confirm('Mark this appointment as completed?');">
                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['AppointmentID']; ?>">
                            <?php // CSRF Token ?>
                            <button type="submit" class="btn btn-sm" style="background-color: #28a745;">Complete</button>
                        </form>
                        <?php endif; ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have no appointments <?php echo ($data['currentStatusFilter'] !== 'All') ? "with status '" . htmlspecialchars($data['currentStatusFilter']) . "'" : ""; ?> <?php /* Thêm thông tin về bộ lọc ngày nếu có */ ?>.</p>
<?php endif; ?>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>