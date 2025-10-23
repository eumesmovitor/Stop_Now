<?php
// Start session only if not already started and not in CLI
if (session_status() === PHP_SESSION_NONE && php_sapi_name() !== 'cli') {
    // Configure session settings
    ini_set('session.cookie_lifetime', 0); // Session cookie expires when browser closes
    ini_set('session.gc_maxlifetime', 3600); // 1 hour
    ini_set('session.cookie_httponly', 1); // Security
    ini_set('session.use_strict_mode', 1); // Security
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
    
    session_start();
}

// Base URL - Simple detection
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host);
} else {
    define('BASE_URL', 'http://localhost:8000');
}

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'stopnow');
define('DB_USER', 'root');
define('DB_PASS', '');

// App settings
define('APP_NAME', 'StopNow');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, production

// Security
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 12);
define('SESSION_TIMEOUT', 7200); // 2 hours

// File upload settings
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', 'logs/error.log');
}

// Security headers (only for web requests)
if (php_sapi_name() !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load additional systems
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/logger.php';
?>
