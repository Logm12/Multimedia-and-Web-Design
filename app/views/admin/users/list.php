<?php
// app/views/admin/users/list.php
require_once __DIR__ . '/../../layouts/header.php'; // Giả sử bạn có header riêng cho Admin hoặc dùng header chung
?>

<h2><?php echo $data['title']; ?></h2>

<?php
// HIỂN THỊ THÔNG BÁO TỪ SESSION (CHO CREATE, UPDATE STATUS, DELETE)
if (isset($_SESSION['user_management_message_success'])): ?>
    <p class="success-message" style="margin-bottom: 15px; padding:10px; border:1px solid green; background-color:#e6ffe6;">
        <?php echo $_SESSION['user_management_message_success']; unset($_SESSION['user_management_message_success']); ?>
    </p>
<?php endif; ?>

<?php if (isset($_SESSION['user_management_message_error'])): ?>
    <p class="error-message" style="margin-bottom: 15px; padding:10px; border:1px solid red; background-color:#ffe0e0;">
        <?php echo $_SESSION['user_management_message_error']; unset($_SESSION['user_management_message_error']); ?>
    </p>
<?php endif; ?>

<?php // Thông báo riêng từ createUser nếu bạn muốn giữ key khác
if (isset($_SESSION['user_create_message_success'])): ?>
    <p class="success-message" style="margin-bottom: 15px; padding:10px; border:1px solid green; background-color:#e6ffe6;">
        <?php echo $_SESSION['user_create_message_success']; unset($_SESSION['user_create_message_success']); ?>
    </p>
<?php endif; ?>
<?php if (isset($_SESSION['user_create_message_error'])): ?>
    <p class="error-message" style="margin-bottom: 15px; padding:10px; border:1px solid red; background-color:#ffe0e0;">
        <?php echo $_SESSION['user_create_message_error']; unset($_SESSION['user_create_message_error']); ?>
    </p>
<?php endif; ?>

<div class="controls" style="margin-bottom: 20px; padding:10px; border:1px solid #eee; background-color:#f9f9f9;">
    <form method="GET" action="<?php echo BASE_URL; ?>/admin/listUsers" style="display: flex; gap: 15px; align-items:center;">
        <div>
            <label for="role_filter">Role:</label>
            <select name="role" id="role_filter">
                <option value="All" <?php echo ($data['currentRoleFilter'] == 'All') ? 'selected' : ''; ?>>All Roles</option>
                <?php $roles = ['Admin', 'Doctor', 'Nurse', 'Patient']; // Hoặc lấy từ $data['allRoles'] đã bỏ 'All' ?>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role; ?>" <?php echo ($data['currentRoleFilter'] == $role) ? 'selected' : ''; ?>><?php echo $role; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="status_filter">Status:</label>
            <select name="status" id="status_filter">
                <option value="All" <?php echo ($data['currentStatusFilter'] == 'All') ? 'selected' : ''; ?>>All Statuses</option>
                <?php $statuses = ['Active', 'Inactive', 'Pending']; // Hoặc lấy từ $data['allStatuses'] ?>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?php echo $status; ?>" <?php echo ($data['currentStatusFilter'] == $status) ? 'selected' : ''; ?>><?php echo $status; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="search_term">Search:</label>
            <input type="text" name="search" id="search_term" value="<?php echo htmlspecialchars($data['currentSearchTerm']); ?>" placeholder="Name, Email, Username...">
        </div>
        <button type="submit" class="btn btn-sm">Filter / Search</button>
        <a href="<?php echo BASE_URL; ?>/admin/createUser" class="btn btn-success btn-sm" style="margin-left:auto; background-color:#28a745; text-decoration:none; color:white;">+ Add New User</a>
    </form>
</div>


<?php if (!empty($data['users'])): ?>
    <table style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">ID</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Full Name</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Username</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Email</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Role</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Status</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Registered</th>
                <th style="padding: 8px; border: 1px solid #ddd; text-align:left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $stt = 1; // Khởi tạo biến đếm số thứ tự ?>
            <?php foreach ($data['users'] as $user): ?>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $stt++; ?></td> <!-- HIỂN THỊ VÀ TĂNG STT -->
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($user['FullName']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($user['Username']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($user['Email']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($user['Role']); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <span class="status-<?php echo strtolower(htmlspecialchars($user['Status'])); ?>">
                            <?php echo htmlspecialchars($user['Status']); ?>
                        </span>
                    </td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars(date('M j, Y', strtotime($user['CreatedAt']))); ?></td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <a href="<?php echo BASE_URL . '/admin/editUser/' . $user['UserID']; ?>" class="btn btn-sm btn-info" style="background-color:#17a2b8; text-decoration:none; color:white; padding:3px 6px;">Edit</a>

                        <?php if ($user['UserID'] != $_SESSION['user_id']): // Admin không tự thay đổi trạng thái của chính mình qua nút này ?>
                            <?php if ($user['Status'] === 'Active'): ?>
                                <form action="<?php echo BASE_URL; ?>/admin/updateUserStatus" method="POST" style="display:inline-block; margin-left:5px;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                    <input type="hidden" name="new_status" value="Inactive">
                                    <?php echo generateCsrfInput(); // CSRF Token ?>
                                    <button type="submit" class="btn btn-sm btn-warning" style="background-color:#ffc107; padding:3px 6px;" onclick="return confirm('Deactivate this user?');">Deactivate</button>
                                </form>
                            <?php elseif ($user['Status'] === 'Inactive' || $user['Status'] === 'Pending'): ?>
                                <form action="<?php echo BASE_URL; ?>/admin/updateUserStatus" method="POST" style="display:inline-block; margin-left:5px;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                    <input type="hidden" name="new_status" value="Active">
                                    <?php echo generateCsrfInput(); // CSRF Token ?>
                                    <button type="submit" class="btn btn-sm btn-success" style="background-color:#28a745; padding:3px 6px;" onclick="return confirm('Activate this user?');">Activate</button>
                                </form>
                            <?php endif; ?>
                            <!-- THÊM NÚT/FORM DELETE (SOFT DELETE) -->
                            <?php if ($user['Status'] !== 'Inactive' && $user['Status'] !== 'Archived'): // Chỉ hiển thị nút Delete nếu user chưa bị "xóa" ?>
                                <form action="<?php echo BASE_URL; ?>/admin/deleteUser" method="POST" style="display:inline-block; margin-left:5px;">
                                    <input type="hidden" name="user_id_to_delete" value="<?php echo $user['UserID']; ?>">
                                    <?php echo generateCsrfInput(); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" style="background-color:#dc3545;" onclick="return confirm('Are you sure you want to SOFT DELETE this user? This will mark them as Inactive/Archived and they cannot log in.');">Delete</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                         <!-- Nút Xóa sẽ thêm sau, cần cẩn thận -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No users found matching your criteria.</p>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../layouts/footer.php'; // Hoặc footer chung
?>