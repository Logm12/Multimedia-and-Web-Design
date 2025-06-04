<?php
// app/views/patient/register.php
// Nạp header
require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Patient Account Registration</h2>

<?php if (isset($data['error_message'])): ?>
    <p class="error-message"><?php echo htmlspecialchars($data['error_message']); ?></p>
<?php endif; ?>
<?php if (isset($data['success_message'])): ?>
    <p class="success-message"><?php echo htmlspecialchars($data['success_message']); ?></p>
<?php endif; ?>

<form action="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/patient/register" method="POST">
    <div class="form-group">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($data['input']['fullname'] ?? ''); ?>" required>
        <?php if (isset($data['errors']['fullname'])): ?><p class="error-message"><?php echo htmlspecialchars($data['errors']['fullname']); ?></p><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($data['input']['username'] ?? ''); ?>" required>
        <?php if (isset($data['errors']['username'])): ?><p class="error-message"><?php echo htmlspecialchars($data['errors']['username']); ?></p><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($data['input']['email'] ?? ''); ?>" required>
        <?php if (isset($data['errors']['email'])): ?><p class="error-message"><?php echo htmlspecialchars($data['errors']['email']); ?></p><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <?php if (isset($data['errors']['password'])): ?><p class="error-message"><?php echo htmlspecialchars($data['errors']['password']); ?></p><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <?php if (isset($data['errors']['confirm_password'])): ?><p class="error-message"><?php echo htmlspecialchars($data['errors']['confirm_password']); ?></p><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="phone_number">Phone Number:</label>
        <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($data['input']['phone_number'] ?? ''); ?>">
        <?php if (isset($data['errors']['phone_number'])): ?><p class="error-message"><?php echo htmlspecialchars($data['errors']['phone_number']); ?></p><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="address">Address:</label>
        <textarea id="address" name="address"><?php echo htmlspecialchars($data['input']['address'] ?? ''); ?></textarea>
    </div>

    <div class="form-group">
        <label for="date_of_birth">Date of Birth:</label>
        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($data['input']['date_of_birth'] ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="gender">Gender:</label>
        <select id="gender" name="gender">
            <option value="">-- Select Gender --</option>
            <option value="Male" <?php echo (isset($data['input']['gender']) && $data['input']['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo (isset($data['input']['gender']) && $data['input']['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
            <option value="Other" <?php echo (isset($data['input']['gender']) && $data['input']['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
        </select>
    </div>
    <br>
    <button type="submit" class="btn">Register</button>
</form>

<?php
// Nạp footer
require_once __DIR__ . '/../layouts/footer.php';
?>