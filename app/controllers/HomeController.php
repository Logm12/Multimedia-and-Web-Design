<?php
// app/controllers/HomeController.php

class HomeController {
    public function index() {
        // Sau này sẽ load view trang chủ
        echo "<h1>Welcome to the Healthcare Management System!</h1>";
        echo "<p><a href='patient/register'>Register (Patient)</a></p>"; // Bỏ BASE_URL// Đảm bảo BASE_URL đã được định nghĩa
        echo "<p><a href='auth/login'>Login</a></p>";   
        // Ví dụ kiểm tra kết nối DB
        $db = Database::getInstance();
        if ($db) {
             echo "<p>Database connection successful!</p>";
         } else {
             echo "<p>Database connection failed!</p>";
         }
    }
}
?>