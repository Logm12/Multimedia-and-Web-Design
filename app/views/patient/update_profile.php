<?php
// app/views/patient/update_profile.php
require_once __DIR__ . '/../layouts/header.php';
?>

<h2><?php echo $data['title']; ?></h2>

<?php if (isset($_SESSION['profile_message_success'])): ?>
    <p class="success-message"><?php echo $_SESSION['profile_message_success']; unset($_SESSION['profile_message_success']); ?></p>
<?php endif; ?>
<?php if (isset($data['profile_message_error']) || isset($_SESSION['profile_message_error'])): ?>
    <p class="error-message">
        <?php
        if (isset($data['profile_message_error'])) echo $data['profile_message_error'];
        if (isset($_SESSION['profile_message_error'])) { echo $_SESSION['profile_message_error']; unset($_SESSION['profile_message_error']);}
        ?>
    </p>
<?php endif; ?>

<form action="<?php echo BASE_URL; ?>/patient/updateProfile" method="POST" enctype="multipart/form-data"> <!-- THÊM enctype -->
    <?php echo generateCsrfInput(); // Đảm bảo hàm này tồn tại và được gọi ?>

    <div style="text-align: center; margin-bottom: 20px;">
        <?php
        $avatarSrc = BASE_URL . '/assets/images/default_avatar.png'; // Đường dẫn avatar mặc định
        if (!empty($data['patient']['Avatar']) && file_exists($data['patient']['Avatar'])) { // Kiểm tra file tồn tại
            $avatarSrc = BASE_URL . '/' . htmlspecialchars($data['patient']['Avatar']); // Đường dẫn từ gốc web
        } elseif (!empty($_SESSION['user_avatar']) && file_exists($_SESSION['user_avatar'])) {
            $avatarSrc = BASE_URL . '/' . htmlspecialchars($_SESSION['user_avatar']);
        }
        ?>
        <img id="avatarPreview" src="<?php echo $avatarSrc; ?>" alt="Profile Avatar" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #ccc; margin-bottom:10px;">
        <br>
        <label for="profile_avatar_input" class="btn" style="cursor: pointer; background-color:#007bff; color:white; padding: 8px 15px; border-radius:4px;">
            <i class="fas fa-camera"></i> Change Profile Picture <!-- Bạn có thể dùng icon FontAwesome nếu có -->
        </label>
        <input type="file" name="profile_avatar" id="profile_avatar_input" style="display: none;" accept="image/png, image/jpeg, image/gif">
        <?php if (isset($data['errors']['profile_avatar'])): ?>
            <p class="error-message" style="text-align:center; margin-top:5px;"><?php echo $data['errors']['profile_avatar']; ?></p>
        <?php endif; ?>
    </div>

    <fieldset>
        <legend>Personal Information</legend>
        <div class="form-group">
            <label for="FullName">Full Name:</label>
            <input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($data['input']['FullName'] ?? ''); ?>" required>
            <?php if (isset($data['errors']['FullName'])): ?><p class="error-message"><?php echo $data['errors']['FullName']; ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="Email">Email:</label>
            <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($data['input']['Email'] ?? ''); ?>" required>
            <?php if (isset($data['errors']['Email'])): ?><p class="error-message"><?php echo $data['errors']['Email']; ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="PhoneNumber">Phone Number:</label>
            <input type="text" id="PhoneNumber" name="PhoneNumber" value="<?php echo htmlspecialchars($data['input']['PhoneNumber'] ?? ''); ?>">
            <?php if (isset($data['errors']['PhoneNumber'])): ?><p class="error-message"><?php echo $data['errors']['PhoneNumber']; ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="Address">Address:</label>
            <textarea name="Address" id="Address" rows="3"><?php echo htmlspecialchars($data['input']['Address'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset style="margin-top: 20px;">
        <legend>Medical Details</legend>
        <div class="form-group">
            <label for="DateOfBirth">Date of Birth:</label>
            <input type="date" id="DateOfBirth" name="DateOfBirth" value="<?php echo htmlspecialchars($data['input']['DateOfBirth'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="Gender">Gender:</label>
            <select id="Gender" name="Gender">
                <option value="">-- Select --</option>
                <option value="Male" <?php echo (($data['input']['Gender'] ?? '') == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (($data['input']['Gender'] ?? '') == 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo (($data['input']['Gender'] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
         <div class="form-group">
            <label for="BloodType">Blood Type:</label>
            <input type="text" id="BloodType" name="BloodType" value="<?php echo htmlspecialchars($data['input']['BloodType'] ?? ''); ?>" placeholder="e.g., A+">
        </div>
        <div class="form-group">
            <label for="InsuranceInfo">Insurance Information:</label>
            <input type="text" id="InsuranceInfo" name="InsuranceInfo" value="<?php echo htmlspecialchars($data['input']['InsuranceInfo'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="MedicalHistorySummary">Medical History Summary:</label>
            <textarea name="MedicalHistorySummary" id="MedicalHistorySummary" rows="4"><?php echo htmlspecialchars($data['input']['MedicalHistorySummary'] ?? ''); ?></textarea>
        </div>
    </fieldset>

    <fieldset style="margin-top: 20px;">
        <legend>Change Password (leave blank if you don't want to change)</legend>
        <div class="form-group">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password">
            <?php if (isset($data['errors']['current_password'])): ?><p class="error-message"><?php echo $data['errors']['current_password']; ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password">
            <?php if (isset($data['errors']['new_password'])): ?><p class="error-message"><?php echo $data['errors']['new_password']; ?></p><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="confirm_new_password">Confirm New Password:</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password">
            <?php if (isset($data['errors']['confirm_new_password'])): ?><p class="error-message"><?php echo $data['errors']['confirm_new_password']; ?></p><?php endif; ?>
        </div>
    </fieldset>

    <div style="margin-top: 20px;">
        <button type="submit" class="btn">Update Profile</button>
        <a href="<?php echo BASE_URL; ?>/patient/dashboard" class="btn btn-secondary" style="background-color:#6c757d; margin-left:10px; text-decoration:none; color:white;">Back to Dashboard</a>
    </div>
</form>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
<script>
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