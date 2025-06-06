<?php
// app/helpers/csrf_helper.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validateCsrfToken')) {
    function validateCsrfToken($tokenFromForm) {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $tokenFromForm)) {
            // Token hợp lệ, có thể xóa token cũ để mỗi request POST dùng token mới (tùy chọn)
            // unset($_SESSION['csrf_token']); 
            return true;
        }
        return false;
    }
}

if (!function_exists('generateCsrfInput')) {
    function generateCsrfInput() {
        $token = generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
?>