<?php
// app/views/patient/dashboard.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Patient Dashboard</h2>

<?php if (isset($data['welcome_message'])): ?>
    <p><?php echo htmlspecialchars($data['welcome_message']); ?></p>
<?php endif; ?>

<p>This is your patient dashboard. More features will be added soon.</p>
<ul>
    <li><a href="<?php echo $data['browse_doctors_link'] ?? '#'; ?>">Browse Doctors & Book Appointment</a></li>
    <li><a href="<?php echo BASE_URL; ?>/appointment/myAppointments">My Appointments</a></li> <!-- ĐÃ SỬA -->
    <!-- SỬA HOẶC THÊM LINK NÀY -->
        <li><a href="<?php echo BASE_URL; ?>/patient/viewAllMedicalRecords">View My Medical Records</a></li>
        <li><a href="<?php echo BASE_URL; ?>/patient/updateProfile">Update My Profile (Placeholder)</a></li> <!-- Giữ lại placeholder cho bước sau -->
</ul>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>