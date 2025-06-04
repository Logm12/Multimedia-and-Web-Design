<?php
// app/views/auth/login.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Login</h2>

<?php if (isset($data['error_message'])): ?>
    <p class="error-message"><?php echo htmlspecialchars($data['error_message']); ?></p>
<?php endif; ?>
<?php if (isset($data['success_message'])): // Ví dụ: nếu chuyển từ trang đăng ký thành công
    ?>
    <p class="success-message"><?php echo htmlspecialchars($data['success_message']); ?></p>
<?php endif; ?>


<form action="<?php echo BASE_URL; ?>/auth/login" method="POST">
    <div class="form-group">
        <label for="username_or_email">Username or Email:</label>
        <input type="text" id="username_or_email" name="username_or_email" value="<?php echo htmlspecialchars($data['input']['username_or_email'] ?? ''); ?>" required>
        <?php if (isset($data['errors']['username_or_email'])): ?><p class="error-message"><?php echo htmlspecialchars($data['errors']['username_or_email']); ?></p><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <?php if (isset($data['errors']['password'])): ?><p class="error-message"><?php echo htmlspecialchars($data['errors']['password']); ?></p><?php endif; ?>
    </div>
    <br>
    <button type="submit" class="btn">Login</button>
</form>
<p>Don't have an account? <a href="<?php echo BASE_URL; ?>/patient/register">Register here</a></p>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>