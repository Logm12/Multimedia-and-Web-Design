<?php
// app/views/patient/all_medical_records.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2><?php echo $data['title']; ?></h2>

<?php if (isset($_SESSION['error_message'])): ?>
    <p class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
<?php endif; ?>

<?php if (!empty($data['medicalHistory'])): ?>
    <p>This page shows a summary of your past consultations and medical records.</p>
    <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Visit Date</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Consulting Doctor</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Diagnosis (Summary)</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['medicalHistory'] as $record): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(date('D, M j, Y', strtotime($record['VisitDate']))); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">Dr. <?php echo htmlspecialchars($record['DoctorName']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <?php
                        $diagnosisSummary = $record['Diagnosis'] ?? 'N/A';
                        if (strlen($diagnosisSummary) > 100) {
                            $diagnosisSummary = substr($diagnosisSummary, 0, 100) . '...';
                        }
                        echo htmlspecialchars($diagnosisSummary);
                        ?>
                    </td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <?php if (!empty($record['AppointmentID'])): ?>
                            <a href="<?php echo BASE_URL . '/patient/viewAppointmentSummary/' . $record['AppointmentID']; ?>" class="btn btn-info btn-sm" style="background-color: #17a2b8; color:white; text-decoration:none; padding: 3px 6px;">View Full Summary</a>
                        <?php else: ?>
                            <!-- Trường hợp record không có AppointmentID (ít xảy ra nếu EMR luôn gắn với appointment) -->
                            Details N/A
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have no medical records in the system yet.</p>
<?php endif; ?>

<p style="margin-top: 30px;">
    <a href="<?php echo BASE_URL; ?>/patient/dashboard" class="btn" style="background-color: #6c757d;">« Back to Dashboard</a>
</p>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>