<?php
// app/views/patient/appointment_summary.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2><?php echo $data['title']; ?></h2>

<?php if (isset($_SESSION['summary_message_error'])): // Dùng key session riêng nếu cần ?>
    <p class="error-message"><?php echo $_SESSION['summary_message_error']; unset($_SESSION['summary_message_error']); ?></p>
<?php endif; ?>

<div class="summary-container" style="border: 1px solid #ccc; padding: 20px; border-radius: 5px;">

    <h3>Appointment Details</h3>
    <p><strong>Date & Time:</strong> <?php echo htmlspecialchars(date('D, M j, Y \a\t g:i A', strtotime($data['appointment']['AppointmentDateTime']))); ?></p>
    <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($data['appointment']['DoctorName']); ?></p>
    <p><strong>Specialization:</strong> <?php echo htmlspecialchars($data['appointment']['SpecializationName'] ?? 'N/A'); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($data['appointment']['Status']); ?></p>
    <?php if (!empty($data['appointment']['ReasonForVisit'])): ?>
        <p><strong>Reason for Visit:</strong> <?php echo nl2br(htmlspecialchars($data['appointment']['ReasonForVisit'])); ?></p>
    <?php endif; ?>

    <hr style="margin: 20px 0;">

    <?php if ($data['medicalRecord']): ?>
        <h3>Consultation Summary</h3>
        <?php if (!empty($data['medicalRecord']['Diagnosis'])): ?>
            <p><strong>Diagnosis:</strong><br><?php echo nl2br(htmlspecialchars($data['medicalRecord']['Diagnosis'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($data['medicalRecord']['TreatmentPlan'])): ?>
            <p><strong>Treatment Plan / Doctor's Advice:</strong><br><?php echo nl2br(htmlspecialchars($data['medicalRecord']['TreatmentPlan'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($data['medicalRecord']['Notes'])): ?>
            <p><strong>Additional Notes from Doctor:</strong><br><?php echo nl2br(htmlspecialchars($data['medicalRecord']['Notes'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($data['prescriptions'])): ?>
            <hr style="margin: 20px 0;">
            <h3>Prescription</h3>
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="padding: 5px; border: 1px solid #ccc;">Medicine</th>
                        <th style="padding: 5px; border: 1px solid #ccc;">Dosage</th>
                        <th style="padding: 5px; border: 1px solid #ccc;">Frequency</th>
                        <th style="padding: 5px; border: 1px solid #ccc;">Duration</th>
                        <th style="padding: 5px; border: 1px solid #ccc;">Instructions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['prescriptions'] as $prescribed): ?>
                    <tr>
                        <td style="padding: 5px; border: 1px solid #ccc;"><?php echo htmlspecialchars($prescribed['MedicineName']); ?> (<?php echo htmlspecialchars($prescribed['MedicineUnit'] ?? ''); ?>)</td>
                        <td style="padding: 5px; border: 1px solid #ccc;"><?php echo htmlspecialchars($prescribed['Dosage']); ?></td>
                        <td style="padding: 5px; border: 1px solid #ccc;"><?php echo htmlspecialchars($prescribed['Frequency']); ?></td>
                        <td style="padding: 5px; border: 1px solid #ccc;"><?php echo htmlspecialchars($prescribed['Duration']); ?></td>
                        <td style="padding: 5px; border: 1px solid #ccc;"><?php echo htmlspecialchars($prescribed['Instructions'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No medication was prescribed for this consultation.</p>
        <?php endif; ?>

    <?php else: ?>
        <p>Consultation details have not been recorded by the doctor yet, or this appointment type does not include a medical record.</p>
    <?php endif; ?>

    <p style="margin-top: 30px;">
        <a href="<?php echo BASE_URL; ?>/patient/myAppointments" class="btn" style="background-color: #6c757d;">« Back to My Appointments</a>
        <!-- TODO: Thêm nút In sau -->
        <!-- <button type="button" onclick="window.print();" class="btn" style="margin-left: 10px;">Print Summary</button> -->
    </p>
</div>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>