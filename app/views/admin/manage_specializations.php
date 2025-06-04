<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<h2><?php echo $data['title']; ?></h2>
<a href="<?php echo BASE_URL; ?>/admin/editSpecialization" class="btn" style="margin-bottom:15px; background-color:#28a745;">+ Add New Specialization</a>

<?php if (isset($_SESSION['admin_message_success'])): ?>
    <p class="success-message"><?php echo $_SESSION['admin_message_success']; unset($_SESSION['admin_message_success']); ?></p>
<?php endif; ?>
<?php if (isset($_SESSION['admin_message_error'])): ?>
    <p class="error-message"><?php echo $_SESSION['admin_message_error']; unset($_SESSION['admin_message_error']); ?></p>
<?php endif; ?>

<?php if (!empty($data['specializations'])): ?>
<table style="width:100%; border-collapse:collapse;">
    <thead><tr style="background-color:#f0f0f0;">
        <th style="border:1px solid #ddd; padding:8px;">ID</th>
        <th style="border:1px solid #ddd; padding:8px;">Name</th>
        <th style="border:1px solid #ddd; padding:8px;">Description</th>
        <th style="border:1px solid #ddd; padding:8px;">Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach ($data['specializations'] as $spec): ?>
        <tr>
            <td style="border:1px solid #ddd; padding:8px;"><?php echo $spec['SpecializationID']; ?></td>
            <td style="border:1px solid #ddd; padding:8px;"><?php echo htmlspecialchars($spec['Name']); ?></td>
            <td style="border:1px solid #ddd; padding:8px;"><?php echo htmlspecialchars($spec['Description'] ?? ''); ?></td>
            <td style="border:1px solid #ddd; padding:8px;">
                <a href="<?php echo BASE_URL . '/admin/editSpecialization/' . $spec['SpecializationID']; ?>" class="btn btn-sm" style="background-color:#ffc107; color:black; text-decoration:none; padding:3px 6px;">Edit</a>
                <form action="<?php echo BASE_URL . '/admin/deleteSpecialization'; ?>" method="POST" style="display:inline-block; margin-left:5px;" onsubmit="return confirm('Are you sure you want to delete this specialization? This might affect doctors associated with it.');">
                    <input type="hidden" name="id_to_delete" value="<?php echo $spec['SpecializationID']; ?>">
                    <?php // echo generateCsrfInput(); // CSRF Token ?>
                    <button type="submit" class="btn btn-sm btn-danger" style="background-color:#dc3545;">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <p>No specializations found. You can add one!</p>
<?php endif; ?>
<p style="margin-top:20px;"><a href="<?php echo BASE_URL; ?>/admin/dashboard" class="btn" style="background-color:#6c757d;">Back to Admin Dashboard</a></p>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>