<?php
// public/index.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('PUBLIC_PATH', __DIR__ . '/');

// --- CSRF Token Handling ---
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = md5(uniqid(microtime(), true));
        error_log("CSRF token generation failed: " . $e->getMessage());
    }
}

function generateCsrfInput() {
    if (isset($_SESSION['csrf_token'])) {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
    }
    return '';
}

function getCsrfToken() {
    return $_SESSION['csrf_token'] ?? null;
}

function validateCsrfToken($submittedToken) {
    if (!isset($_SESSION['csrf_token']) || empty($submittedToken)) {
        error_log("CSRF: Session or submitted token missing.");
        return false;
    }
    if (hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        // Optional: Regenerate token after successful validation for one-time use.
        // unset($_SESSION['csrf_token']);
        // $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }
    error_log("CSRF: Token mismatch.");
    return false;
}

// --- Define BASE_URL ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = ($scriptPath == '/' || $scriptPath == '\\') ? '' : rtrim($scriptPath, '/');
define('BASE_URL', $protocol . $domainName . $basePath);

// --- Basic Autoloader ---
spl_autoload_register(function ($className) {
    $baseDir = __DIR__ . '/../app/';
    $paths = [
        $baseDir . 'core/' . $className . '.php',
        $baseDir . 'controllers/' . $className . '.php',
        $baseDir . 'models/' . $className . '.php',
        $baseDir . 'services/' . $className . '.php',
        $baseDir . 'helpers/' . $className . '.php', // For any helper classes
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// --- Styled Error Display Function ---
function show_lovely_error($title, $message) {
    if (ob_get_level() > 0) { ob_end_clean(); }
    http_response_code(404); 
    // Minified HTML and CSS for the error page
    echo <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Oops! Something went a bit sideways</title><style>body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-color:#fce4ec;color:#333;margin:0;padding:0;display:flex;justify-content:center;align-items:center;min-height:100vh;text-align:center}.error-container-cutie{background-color:#fff;border-left:7px solid #e91e63;margin:20px;padding:30px 40px;border-radius:12px;box-shadow:0 8px 25px rgba(0,0,0,.15);max-width:600px;animation:fadeInCute .5s ease-out}@keyframes fadeInCute{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}.error-container-cutie h3{color:#c2185b;margin-top:0;font-size:1.8em;font-weight:600}.error-container-cutie p{color:#5c5c5c;font-size:1.1em;line-height:1.7;margin-bottom:0}.error-container-cutie p code{background-color:#fce4ec;padding:3px 6px;border-radius:5px;font-family:'Consolas','Courier New',monospace;color:#ad1457}.error-home-link{display:inline-block;margin-top:25px;padding:10px 20px;background-color:#e91e63;color:#fff;text-decoration:none;border-radius:25px;font-weight:500;transition:background-color .3s ease,transform .2s ease}.error-home-link:hover,.error-home-link:focus{background-color:#c2185b;transform:translateY(-2px);box-shadow:0 4px 10px rgba(0,0,0,.1)}</style></head><body><div class='error-container-cutie'><h3>{$title} - Oopsie! A little hiccup here...</h3><p>{$message}</p><a href="{BASE_URL}" class="error-home-link">Back to Homepage!</a></div></body></html>
HTML;
    exit;
}

// --- Basic Routing ---
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = explode('/', $url);

$controllerName = !empty($urlParts[0]) ? ucfirst(strtolower($urlParts[0])) . 'Controller' : 'HomeController';
$actionName = !empty($urlParts[1]) ? strtolower($urlParts[1]) : 'index';
$params = array_slice($urlParts, 2);

$controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    // Autoloader should have loaded the class if the file exists and class name matches file name
    if (class_exists($controllerName)) {
        $controllerInstance = new $controllerName();
        if (method_exists($controllerInstance, $actionName)) {
            call_user_func_array([$controllerInstance, $actionName], $params);
        } else {
            show_lovely_error("Routing Error", "Action <code>" . htmlspecialchars($actionName, ENT_QUOTES, 'UTF-8') . "</code> not found in controller <code>" . htmlspecialchars($controllerName, ENT_QUOTES, 'UTF-8') . "</code>.");
        }
    } else {
        // This case might indicate a naming mismatch between file and class, or autoloader issue for this specific class.
        show_lovely_error("Controller Error", "Controller class <code>" . htmlspecialchars($controllerName, ENT_QUOTES, 'UTF-8') . "</code> not found, though file exists. Check naming, sweetie!");
    }
} else {
    // Handle default HomeController separately if no specific controller was requested or if 'home' was explicitly in URL but file not found
    if ($controllerName === 'HomeController') { 
        // Attempt to load HomeController even if $urlParts[0] was empty or 'home'
        $homeControllerFile = __DIR__ . '/../app/controllers/HomeController.php';
        if (file_exists($homeControllerFile)) {
            if (class_exists('HomeController')) {
                $homeController = new HomeController();
                if (method_exists($homeController, 'index')) {
                    $homeController->index(); // Default action for HomeController
                } else {
                    show_lovely_error("Homepage Error", "Default action <code>index</code> not found in <code>HomeController</code>.");
                }
            } else {
                 show_lovely_error("Critical Homepage Error", "<code>HomeController</code> class not found, though file exists.");
            }
        } else {
            // This means even the default HomeController.php is missing
            show_lovely_error("Critical System Error", "Default <code>HomeController.php</code> is missing. Our homepage is lost!");
        }
    } else {
        // A specific controller was requested, but its file was not found
        show_lovely_error("Controller Not Found", "Controller file <code>" . htmlspecialchars($controllerName, ENT_QUOTES, 'UTF-8') . ".php</code> not found.");
    }
}
?>