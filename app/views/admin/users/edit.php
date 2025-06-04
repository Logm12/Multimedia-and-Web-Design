<?php
// app/views/admin/users/edit.php
require_once __DIR__ . '/../../layouts/header.php'; // Hoặc header chung của bạn
?>

<h2><?php echo $data['title']; ?></h2>

<?php if (!empty($data['errors'])): ?>
    <div class="error-message" style="margin-bottom: 15px; padding:10px; border:1px solid red; background-color:#ffe0e0;">
        <strong>Please correct the following errors:</strong>
        <ul>
            <?php foreach ($data['errors'] as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['user_management_message_success'])): // Dùng chung key session ?>
    <p class="success-message"><?php echo $_SESSION['user_management_message_success']; unset($_SESSION['user_management_message_success']); ?></p>
<?php endif; ?>
<?php if (isset($_SESSION['user_management_message_error'])): ?>
    <p class="error-message"><?php echo $_SESSION['user_management_message_error']; unset($_SESSION['user_management_message_error']); ?></p>
<?php endif; ?>


<form action="<?php echo BASE_URL; ?>/admin/editUser/<?php echo $data['userId']; ?>" method="POST">
    <?php echo generateCsrfInput(); // CSRF Token ?>
    <input type="hidden" name="userId" value="<?php echo $data['userId']; ?>"> <!-- Có thể không cần nếu đã có trong URL và controller xử lý đúng -->

    <fieldset>
        <legend>Account Information</legend>
        <div class="form-group">
            <label for="FullName">Full Name: *</label>
            <input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($data['input']['FullName'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="Username">Username: (Cannot be changed)</label>
            <input type="text" id="Username" name="Username" value="<?php echo htmlspecialchars($data['input']['Username'] ?? ''); ?>" readonly style="background-color:#e9ecef;">
        </div>
        <div class="form-group">
            <label for="Email">Email: *</label>
            <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($data['input']['Email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="Role">Role: *</label>
            <select id="Role" name="Role" required onchange="toggleDoctorFieldsEdit()">
                <option value="">-- Select Role --</option>
                <?php foreach ($data['roles'] as $roleOption): // Đổi tên biến để không trùng với $role của user hiện tại ?>
                    <option value="<?php echo $roleOption; ?>" <?php echo (($data['input']['Role'] ?? '') == $roleOption) ? 'selected' : ''; ?>><?php echo $roleOption; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="Status">Status: *</label>
            <select id="Status" name="Status" required>
                <?php foreach ($data['statuses'] as $statusOption): ?>
                     <option value="<?php echo $statusOption; ?>" <?php echo (($data['input']['Status'] ?? '') == $statusOption) ? 'selected' : ''; ?>><?php echo $statusOption; ?></option>
                <?php endforeach; ?>
            </select>
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

    <fieldset id="doctorFieldsEdit" style="margin-top: 20px; display: <?php echo (($data['input']['Role'] ?? '') == 'Doctor') ? 'block' : 'none'; ?>;">
        <legend>Doctor Specific Information</legend>
        <div class="form-group">
            <label for="SpecializationIDEdit">Specialization:</label>
            <select id="SpecializationIDEdit" name="SpecializationID">
                <option value="">-- Select Specialization --</option>
                <?php if (!empty($data['specializations'])): ?>
                    <?php foreach ($data['specializations'] as $spec): ?>
                        <option value="<?php echo $spec['SpecializationID']; ?>" <?php echo (($data['input']['SpecializationID'] ?? '') == $spec['SpecializationID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($spec['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="BioEdit">Bio (Brief Introduction):</label>
            <textarea name="Bio" id="BioEdit" rows="3"><?php echo htmlspecialchars($data['input']['Bio'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="ExperienceYearsEdit">Years of Experience:</label>
            <input type="number" id="ExperienceYearsEdit" name="ExperienceYears" value="<?php echo htmlspecialchars($data['input']['ExperienceYears'] ?? '0'); ?>" min="0">
        </div>
        <div class="form-group">
            <label for="ConsultationFeeEdit">Consultation Fee:</label>
            <input type="number" id="ConsultationFeeEdit" name="ConsultationFee" value="<?php echo htmlspecialchars($data['input']['ConsultationFee'] ?? '0.00'); ?>" step="0.01" min="0">
        </div>
    </fieldset>

    <!-- TODO: Thêm fieldset cho Nurse nếu Role là Nurse và có các trường riêng -->

    <fieldset style="margin-top: 20px;">
        <legend>Change Password (Optional - leave blank to keep current password)</legend>
        <div class="form-group">
            <label for="NewPassword">New Password:</label>
            <input type="password" id="NewPassword" name="NewPassword">
        </div>
        <div class="form-group">
            <label for="ConfirmNewPassword">Confirm New Password:</label>
            <input type="password" id="ConfirmNewPassword" name="ConfirmNewPassword">
        </div>
    </fieldset>


    <div style="margin-top: 20px;">
        <button type="submit" class="btn">Update User</button>
        <a href="<?php echo BASE_URL; ?>/admin/listUsers" class="btn btn-secondary" style="background-color:#6c757d; margin-left:10px; text-decoration:none; color:white;">Cancel & Back to List</a>
    </div>
</form>

<script>
    function toggleDoctorFieldsEdit() {
        const roleSelect = document.getElementById('Role');
        const doctorFields = document.getElementById('doctorFieldsEdit'); // Đảm bảo ID này khớp
        if (roleSelect && doctorFields) { // Thêm kiểm tra null
            if (roleSelect.value === 'Doctor') {
                doctorFields.style.display = 'block';
            } else {
                doctorFields.style.display = 'none';
            }
        }
        // TODO: Thêm logic tương tự cho Nurse fields nếu có
    }
    // Gọi hàm khi tải trang để đảm bảo trạng thái đúng
    document.addEventListener('DOMContentLoaded', toggleDoctorFieldsEdit);
</script>


<?php
require_once __DIR__ . '/../../layouts/footer.php'; // Hoặc footer chung
?>