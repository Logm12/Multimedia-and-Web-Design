<?php
// public/index.php
session_start(); // Đảm bảo session đã được bắt đầu
// NẠP AUTOLOAD CỦA COMPOSER
require_once __DIR__ . '/../vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
define('PUBLIC_PATH', __DIR__ . '/');

if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Tạo token ngẫu nhiên mạnh
    } catch (Exception $e) {
        // Xử lý lỗi nếu random_bytes không thành công (rất hiếm)
        // Có thể dùng một phương pháp tạo token khác yếu hơn làm dự phòng
        $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
        error_log("CSRF token generation failed with random_bytes: " . $e->getMessage());
    }
}

// Hàm helper để tạo input hidden chứa CSRF token cho form
function generateCsrfInput() {
    if (isset($_SESSION['csrf_token'])) {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }
    return ''; // Trả về chuỗi rỗng nếu token không có trong session
}

// Hàm helper để lấy giá trị CSRF token (dùng cho AJAX header)
function getCsrfToken() {
    return $_SESSION['csrf_token'] ?? null;
}

// Hàm helper để validate CSRF token
function validateCsrfToken($submittedToken) {
    if (!isset($_SESSION['csrf_token']) || empty($submittedToken)) {
        error_log("CSRF validation failed: Session token or submitted token is missing.");
        return false;
    }
    // Sử dụng hash_equals để so sánh an toàn, chống timing attacks
    if (hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        // Tùy chọn: Tạo token mới sau mỗi lần validate thành công (One-Time Token)
        // Điều này tăng cường bảo mật nhưng cần cẩn thận với các request AJAX song song hoặc nút back của trình duyệt.
        // Nếu dùng one-time token, bạn cần tạo lại token ngay sau khi validate thành công.
        // unset($_SESSION['csrf_token']); // Xóa token cũ
        // $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Tạo token mới
        return true;
    }
    error_log("CSRF validation failed: Submitted token does not match session token.");
    return false;
}

// --- ĐỊNH NGHĨA BASE_URL --- // Đảm bảo nó ở đây
// --- ĐỊNH NGHĨA BASE_URL ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
// Lấy đường dẫn của script hiện tại (index.php trong public)
// Ví dụ: /web_final/public/index.php -> dirname sẽ là /web_final/public
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Loại bỏ dấu / ở cuối nếu có (trừ khi nó là thư mục gốc /)
$basePath = ($scriptPath == '/') ? '' : rtrim($scriptPath, '/');

define('BASE_URL', $protocol . $domainName . $basePath);
// Ví dụ: BASE_URL bây giờ sẽ là http://localhost:8080/web_final/public

// Autoload các class (cơ bản, có thể cải thiện sau này với spl_autoload_register)
// Giả sử các class nằm trong app/core, app/controllers, app/models
function basic_autoloader($className) {
    $paths = [
        __DIR__ . '/../app/core/' . $className . '.php',
        __DIR__ . '/../app/controllers/' . $className . '.php',
        __DIR__ . '/../app/models/' . $className . '.php',
        __DIR__ . '/../app/services/' . $className . '.php',
        __DIR__ . '/../app/helpers/' . $className . '.php',
        __DIR__ . '/../app/services/' . $className . '.php', // Thêm dòng này
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
}
spl_autoload_register('basic_autoloader');


// --- ROUTING CƠ BẢN ---
// Lấy URL path (đơn giản hóa, cần cải thiện với .htaccess để có URL đẹp hơn)
// Ví dụ: index.php?url=controller/action/param1/param2
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = explode('/', $url);

// Xác định controller, action và params
// Controller mặc định là 'HomeController', action mặc định là 'index'
$controllerName = !empty($urlParts[0]) ? ucfirst($urlParts[0]) . 'Controller' : 'HomeController';
$actionName = !empty($urlParts[1]) ? $urlParts[1] : 'index';
$params = array_slice($urlParts, 2);

// Kiểm tra file controller có tồn tại không
if (file_exists(__DIR__ . '/../app/controllers/' . $controllerName . '.php')) {
    // require_once __DIR__ . '/../app/controllers/' . $controllerName . '.php'; // Đã autoload
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $actionName)) {
            // Gọi action với các params (nếu có)
            call_user_func_array([$controller, $actionName], $params);
        } else {
            // Action không tồn tại
            echo "Error: Action '{$actionName}' not found in controller '{$controllerName}'.";
            // Hoặc gọi một trang lỗi 404
        }
    } else {
        // Class controller không tồn tại
        echo "Error: Controller class '{$controllerName}' not found.";
        // Hoặc gọi một trang lỗi 404
    }
} else {
    // File controller không tồn tại
    // Nếu không có controller nào được gọi, hoặc controller mặc định là HomeController
    if ($controllerName === 'HomeController' && file_exists(__DIR__ . '/../app/controllers/HomeController.php')) {
        // require_once __DIR__ . '/../app/controllers/HomeController.php'; // Đã autoload
        if (class_exists('HomeController')) {
            $homeController = new HomeController();
            if (method_exists($homeController, 'index')) {
                $homeController->index();
            } else {
                 echo "Error: Action 'index' not found in HomeController.";
            }
        } else {
            echo "Error: HomeController class not found.";
        }
    } else {
        echo "Error: Controller file '{$controllerName}.php' not found.";
        // Hoặc gọi một trang lỗi 404
    }
}

?>