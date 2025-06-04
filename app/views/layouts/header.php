<?php
// app/views/layouts/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title'] ?? 'Healthcare System'; ?></title>
    <!-- Add your CSS here -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css"> <!-- Example, BASE_URL needs to be defined -->
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        .error-message { color: red; font-size: 0.9em; margin-top: 5px; }
        .success-message { color: green; font-size: 0.9em; margin-top: 5px; padding: 10px; border: 1px solid green; background-color: #e6ffe6; }
        nav ul { list-style-type: none; padding: 0; }
        nav ul li { display: inline; margin-right: 10px; }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>">Home</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
    $sessionAvatarSrc = BASE_URL . '/assets/images/default_avatar.png'; // Avatar mặc định
    // Kiểm tra xem $_SESSION['user_avatar'] có tồn tại, không rỗng VÀ file có thực sự tồn tại không
    if (!empty($_SESSION['user_avatar']) && file_exists(PUBLIC_PATH . $_SESSION['user_avatar'])) { // Sử dụng PUBLIC_PATH
        $sessionAvatarSrc = BASE_URL . '/' . htmlspecialchars($_SESSION['user_avatar']);
    } elseif (!empty($_SESSION['user_avatar'])) { // Nếu session có path nhưng file không tồn tại (backup)
        // Log lỗi hoặc xử lý trường hợp file avatar trong session không còn tồn tại trên server
        error_log("Avatar file not found for user {$_SESSION['user_id']}: {$_SESSION['user_avatar']}");
        // Vẫn có thể thử hiển thị nếu bạn nghĩ URL có thể đúng dù file_exists báo sai (ít khả năng)
        // $sessionAvatarSrc = BASE_URL . '/' . htmlspecialchars($_SESSION['user_avatar']);
    }
    ?>
    <img src="<?php echo $sessionAvatarSrc; ?>" alt="Avatar" style="width:30px; height:30px; border-radius:50%; margin-right:5px; vertical-align:middle;">
                        
                    <li><a href="<?php echo BASE_URL; ?>/auth/logout">Logout (<?php echo htmlspecialchars($_SESSION['user_fullname'] ?? ''); ?>)</a></li>
                    <?php if ($_SESSION['user_role'] === 'Patient'): ?>
                        <li><a href="<?php echo BASE_URL; ?>/patient/dashboard">Patient Dashboard</a></li>
                    <?php elseif ($_SESSION['user_role'] === 'Doctor'): ?>
                        <li><a href="<?php echo BASE_URL; ?>/doctor/dashboard">Doctor Dashboard</a></li>
                    <?php elseif ($_SESSION['user_role'] === 'Admin'): ?>
                        <li><a href="<?php echo BASE_URL; ?>/admin/dashboard">Admin Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/admin/manageSpecializations">Manage Specializations</a></li>
                        <!-- Thêm các link Admin khác ở đây -->
                    <?php elseif ($_SESSION['user_role'] === 'Nurse'): ?>
                        <li><a href="<?php echo BASE_URL; ?>/nurse/dashboard">Nurse Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/nurse/listAppointments">Manage Appointments</a></li>
                        <?php endif; ?>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>/patient/register">Register</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/login">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <hr>
    </header>
    <main class="container"></main>