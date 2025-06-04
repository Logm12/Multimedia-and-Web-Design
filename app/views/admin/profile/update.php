<?php
// app/views/admin/profile/update.php
require_once __DIR__ . '/../../layouts/header.php'; // Hoặc header chung
?>

<h2><?php echo $data['title']; ?></h2>

<?php if (isset($_SESSION['profile_message_success'])): ?>
    <p class="success-message"><?php echo $_SESSION['profile_message_success']; unset($_SESSION['profile_message_success']); ?></p>
<?php endif; ?>
<?php if (isset($data['profile_message_error']) || isset($_SESSION['profile_message_error'])): ?>
    <p class="error-message">
        <?php /* ... hiển thị lỗi ... */ ?>
    </p>
<?php endif; ?>
<?php if (!empty($data['errors'])): ?>
    <div class="error-message" style="margin-bottom: 15px; padding:10px; border:1px solid red; background-color:#ffe0e0;">
        <strong>Please correct the following errors:</strong>
        <ul>
            <?php foreach ($data['errors'] as $fieldName => $error): // Sửa lại nếu $data['errors'] là mảng lỗi đơn giản ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>


<form action="<?php echo BASE_URL; ?>/admin/updateProfile" method="POST" enctype="multipart/form-data">
    <?php echo generateCsrfInput(); ?>

    <div style="text-align: center; margin-bottom: 20px;">
        <?php
        $avatarSrc = BASE_URL . '/assets/images/default_avatar.png';
        
        if (!empty($data['user']['Avatar']) && file_exists($data['user']['Avatar'])) {
            $avatarSrc = BASE_URL . '/' . htmlspecialchars($data['user']['Avatar']);
        }
        ?>
        <img id="avatarPreview" src="<?php echo $avatarSrc; ?>" alt="Profile Avatar" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #ccc; margin-bottom:10px;">
        <br>
        <label for="profile_avatar_input" class="btn" style="cursor: pointer; background-color:#007bff; color:white; padding: 8px 15px; border-radius:4px;">
            Change Profile Picture
        </label>
        <input type="file" name="profile_avatar" id="profile_avatar_input" style="display: none;" accept="image/png, image/jpeg, image/gif">
        <?php if (isset($data['errors']['profile_avatar'])): ?><p class="error-message" style="text-align:center; margin-top:5px;"><?php echo $data['errors']['profile_avatar']; ?></p><?php endif; ?>
    </div>

    <fieldset>
        <legend>Account Information</legend>
        <div class="form-group">
            <label for="FullName">Full Name: *</label>
            <input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($data['input']['FullName'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="Username">Username:</label>
            <input type="text" id="Username" name="Username" value="<?php echo htmlspecialchars($data['input']['Username'] ?? ''); ?>" readonly> <!-- Để readonly nếu không cho sửa -->
            <!-- Nếu cho sửa, bỏ readonly và thêm xử lý validation, kiểm tra trùng ở controller -->
        </div>
        <div class="form-group">
            <label for="Email">Email: *</label>
            <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($data['input']['Email'] ?? ''); ?>" required>
        </div>
         <div class="form-group">
            <label for="PhoneNumber">Phone Number:</label>
            <input type="text" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($data['input']['PhoneNumber'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="Address">Address:</label>
            <textarea name="Address" id="Address" rows="3"><?php echo htmlspecialchars($data['input']['Address'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset style="margin-top: 20px;">
        <legend>Change Password (leave blank if you don't want to change)</legend>
        <!-- ... (các trường current_password, new_password, confirm_new_password như của Patient) ... -->
         <div class="form-group">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password">
        </div>
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password">
        </div>
        <div class="form-group">
            <label for="confirm_new_password">Confirm New Password:</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password">
        </div>
    </fieldset>

    <div style="margin-top: 20px;">
        <button type="submit" class="btn">Update Admin Profile</button>
        <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="btn btn-secondary" style="background-color:#6c757d; margin-left:10px; text-decoration:none; color:white;">Back to Dashboard</a>
    </div>
</form>

<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>
<script>
// Copy JavaScript preview avatar từ trang update profile của Patient nếu cần
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('profile_avatar_input');
    const avatarPreview = document.getElementById('avatarPreview');
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>