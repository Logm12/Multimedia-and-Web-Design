<?php
// app/views/admin/users/create.php
require_once __DIR__ . '/../../layouts/header.php';
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
<?php if (isset($_SESSION['user_create_message_success'])): ?>
    <p class="success-message"><?php echo $_SESSION['user_create_message_success']; unset($_SESSION['user_create_message_success']); ?></p>
<?php endif; ?>
<?php if (isset($_SESSION['user_create_message_error'])): ?>
    <p class="error-message"><?php echo $_SESSION['user_create_message_error']; unset($_SESSION['user_create_message_error']); ?></p>
<?php endif; ?>


<form action="<?php echo BASE_URL; ?>/admin/createUser" method="POST">
    <?php echo generateCsrfInput(); ?>

    <fieldset>
        <legend>Account Information</legend>
        <div class="form-group">
            <label for="FullName">Full Name: *</label>
            <input type="text" id="FullName" name="FullName" value="<?php echo htmlspecialchars($data['input']['FullName'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="Username">Username: *</label>
            <input type="text" id="Username" name="Username" value="<?php echo htmlspecialchars($data['input']['Username'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
    <label for="Email">Email: * (Will be used for notifications)</label>
    <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($data['input']['Email'] ?? ''); ?>" required>
</div>
<p style="font-size: 0.9em; color: #666;"><em>A temporary password will be generated for the new user. They will be instructed to change it upon first login.</em></p>

        <div class="form-group">
            <label for="Role">Role: *</label>
            <select id="Role" name="Role" required onchange="toggleDoctorFields()">
                <option value="">-- Select Role --</option>
                <?php foreach ($data['roles'] as $role): ?>
                    <option value="<?php echo $role; ?>" <?php echo (($data['input']['Role'] ?? '') == $role) ? 'selected' : ''; ?>><?php echo $role; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
       <div class="form-group">
    <label for="Status">Status: *</label>
    <select id="Status" name="Status" required>
        <option value="Pending" <?php echo (($data['input']['Status'] ?? 'Pending') == 'Pending') ? 'selected' : ''; ?>>Pending (User needs to activate/change password)</option>
        <option value="Active" <?php echo (($data['input']['Status'] ?? '') == 'Active') ? 'selected' : ''; ?>>Active</option>
        <option value="Inactive" <?php echo (($data['input']['Status'] ?? '') == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
    </select>
</div>
    </fieldset>

    <fieldset id="doctorFields" style="margin-top: 20px; display: <?php echo (($data['input']['Role'] ?? '') == 'Doctor') ? 'block' : 'none'; ?>;">
        <legend>Doctor Specific Information</legend>
        <div class="form-group">
            <label for="SpecializationID">Specialization:</label>
            <select id="SpecializationID" name="SpecializationID">
                <option value="">-- Select Specialization --</option>
                <?php foreach ($data['specializations'] as $spec): ?>
                    <option value="<?php echo $spec['SpecializationID']; ?>" <?php echo (($data['input']['SpecializationID'] ?? '') == $spec['SpecializationID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($spec['Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="Bio">Bio (Brief Introduction):</label>
            <textarea name="Bio" id="Bio" rows="3"><?php echo htmlspecialchars($data['input']['Bio'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="ExperienceYears">Years of Experience:</label>
            <input type="number" id="ExperienceYears" name="ExperienceYears" value="<?php echo htmlspecialchars($data['input']['ExperienceYears'] ?? '0'); ?>" min="0">
        </div>
        <div class="form-group">
            <label for="ConsultationFee">Consultation Fee:</label>
            <input type="number" id="ConsultationFee" name="ConsultationFee" value="<?php echo htmlspecialchars($data['input']['ConsultationFee'] ?? '0.00'); ?>" step="0.01" min="0">
        </div>
    </fieldset>

    <!-- TODO: Thêm fieldset cho Nurse nếu Role là Nurse và có các trường riêng -->

    <div style="margin-top: 20px;">
        <button type="submit" class="btn">Create User</button>
        <a href="<?php echo BASE_URL; ?>/admin/listUsers" class="btn btn-secondary" style="background-color:#6c757d; margin-left:10px; text-decoration:none; color:white;">Cancel & Back to List</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fullNameInput = document.getElementById('FullName');
    const usernameInput = document.getElementById('Username');
    const emailInput = document.getElementById('Email');
    const defaultEmailDomain = 'dakhoa.com'; // Thay bằng domain của bạn

    // Tạo các phần tử để hiển thị feedback (thông báo)
    let usernameFeedback = document.getElementById('usernameFeedback');
    if (!usernameFeedback) { // Nếu chưa có, tạo mới và chèn vào sau input username
        usernameFeedback = document.createElement('small');
        usernameFeedback.id = 'usernameFeedback';
        usernameInput.parentNode.insertBefore(usernameFeedback, usernameInput.nextSibling);
    }

    let emailFeedback = document.getElementById('emailFeedback');
    if (!emailFeedback) { // Nếu chưa có, tạo mới và chèn vào sau input email
        emailFeedback = document.createElement('small');
        emailFeedback.id = 'emailFeedback';
        emailInput.parentNode.insertBefore(emailFeedback, emailInput.nextSibling);
    }


    if (fullNameInput && usernameInput && emailInput) {
        // --- GỢI Ý USERNAME VÀ EMAIL ---
        fullNameInput.addEventListener('blur', function() {
            const fullName = this.value.trim();
            let suggestedUsernameBase = '';

            if (usernameInput.value === '' && fullName) {
                suggestedUsernameBase = fullName.toLowerCase()
                    .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                    .replace(/[^a-z0-9]/gi, '')
                    .substring(0, 20);
                if (suggestedUsernameBase) {
                    usernameInput.value = suggestedUsernameBase;
                    // Kích hoạt sự kiện blur của usernameInput để kiểm tra tính khả dụng ngay
                    usernameInput.dispatchEvent(new Event('blur'));
                }
            } else if (usernameInput.value !== '') {
                suggestedUsernameBase = usernameInput.value.trim();
            }

            if (emailInput.value === '' && suggestedUsernameBase) {
                emailInput.value = suggestedUsernameBase + '@' + defaultEmailDomain;
                // Kích hoạt sự kiện blur của emailInput để kiểm tra tính khả dụng ngay
                emailInput.dispatchEvent(new Event('blur'));
            }
        });

        usernameInput.addEventListener('blur', function() {
            const currentUsername = this.value.trim();
            // Cập nhật gợi ý email nếu username thay đổi và email đang theo mẫu cũ hoặc trống
            if (emailInput.value === '' || (emailInput.value.split('@')[0] !== currentUsername && emailInput.value.endsWith('@' + defaultEmailDomain))) {
                if (currentUsername) {
                    emailInput.value = currentUsername + '@' + defaultEmailDomain;
                     // Kích hoạt sự kiện blur của emailInput nếu giá trị thay đổi
                    emailInput.dispatchEvent(new Event('blur'));
                } else if (emailInput.value.endsWith('@' + defaultEmailDomain)) { // Nếu username bị xóa, xóa luôn phần gợi ý email
                    emailInput.value = '';
                }
            }
            // --- KIỂM TRA USERNAME TỒN TẠI (AJAX) ---
            checkUsernameAvailability(currentUsername);
        });

        emailInput.addEventListener('blur', function() {
            const currentEmail = this.value.trim();
            // --- KIỂM TRA EMAIL TỒN TẠI (AJAX) ---
            checkEmailAvailability(currentEmail);
        });
    }

    // --- HÀM KIỂM TRA USERNAME ---
    function checkUsernameAvailability(username) {
        usernameFeedback.textContent = ''; // Xóa feedback cũ
        if (username) {
            usernameFeedback.textContent = 'Checking...';
            usernameFeedback.style.color = 'orange';
            fetch(`<?php echo BASE_URL; ?>/admin/checkUsernameAvailability?username=${encodeURIComponent(username)}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.available) {
                        usernameFeedback.textContent = data.message || 'Username is available.';
                        usernameFeedback.style.color = 'green';
                    } else {
                        usernameFeedback.textContent = data.message || 'Username is already taken.';
                        usernameFeedback.style.color = 'red';
                    }
                })
                .catch(error => {
                    console.error('Error checking username:', error);
                    usernameFeedback.textContent = 'Error checking username.';
                    usernameFeedback.style.color = 'red';
                });
        }
    }

    // --- HÀM KIỂM TRA EMAIL ---
    function checkEmailAvailability(email) {
        emailFeedback.textContent = ''; // Xóa feedback cũ
        if (email) {
            // Kiểm tra định dạng email cơ bản phía client trước khi gửi AJAX
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                emailFeedback.textContent = 'Invalid email format.';
                emailFeedback.style.color = 'red';
                return;
            }

            emailFeedback.textContent = 'Checking...';
            emailFeedback.style.color = 'orange';
            fetch(`<?php echo BASE_URL; ?>/admin/checkEmailAvailability?email=${encodeURIComponent(email)}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.available) {
                        emailFeedback.textContent = data.message || 'Email is available.';
                        emailFeedback.style.color = 'green';
                    } else {
                        emailFeedback.textContent = data.message || 'Email is already registered.';
                        emailFeedback.style.color = 'red';
                    }
                })
                .catch(error => {
                    console.error('Error checking email:', error);
                    emailFeedback.textContent = 'Error checking email.';
                    emailFeedback.style.color = 'red';
                });
        }
    }


    // --- HÀM TOGGLE DOCTOR FIELDS (như cũ) ---
    function toggleDoctorFields() {
        const roleSelect = document.getElementById('Role');
        const doctorFields = document.getElementById('doctorFields');
        if (roleSelect && doctorFields) { // Thêm kiểm tra null
            if (roleSelect.value === 'Doctor') {
                doctorFields.style.display = 'block';
            } else {
                doctorFields.style.display = 'none';
            }
        }
    }
    // Gọi hàm khi tải trang và khi Role thay đổi
    const roleSelectForToggle = document.getElementById('Role');
    if (roleSelectForToggle) {
        toggleDoctorFields(); // Gọi khi tải trang
        roleSelectForToggle.addEventListener('change', toggleDoctorFields);
    }
});
</script>


<?php
require_once __DIR__ . '/../../layouts/footer.php';
?>