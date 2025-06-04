<?php
// config/database.php

define('DB_HOST', 'localhost'); // Hoặc IP của server MySQL nếu khác
define('DB_USER', 'root'); // Thay bằng username của bạn
define('DB_PASS', ''); // Thay bằng password của bạn
define('DB_NAME', 'healthcare_system'); // Tên database bạn đã tạo
define('DB_CHARSET', 'utf8mb4');

// Data Source Name (DSN) for PDO
// !!! SỬA LẠI DÒNG NÀY CHO ĐÚNG !!!
// KHÔNG CẦN $dsn và $options ở đây nữa.
// $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
// $options = [
//     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
//     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//     PDO::ATTR_EMULATE_PREPARES   => false,
// ];

?>