<?php
/**
 * Production Configuration for StopNow
 * Copy this file to config/config.php and adjust the settings
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL - Set your production domain
define('BASE_URL', 'https://yourdomain.com');

// Database credentials - Use environment variables in production
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'stopnow');
define('DB_USER', $_ENV['DB_USER'] ?? 'your_db_user');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'your_secure_password');

// App settings
define('APP_NAME', 'StopNow');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'production'); // Set to production

// Security - Use stronger settings for production
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 14); // Increased for production
define('SESSION_TIMEOUT', 1800); // 30 minutes

// File upload settings
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Error reporting - Disabled for production
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/error.log');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' cdnjs.cloudflare.com; img-src \'self\' data:; font-src \'self\' cdnjs.cloudflare.com;');

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rate limiting (basic implementation)
if (!isset($_SESSION['last_request'])) {
    $_SESSION['last_request'] = time();
} else {
    $time_diff = time() - $_SESSION['last_request'];
    if ($time_diff < 1) { // Minimum 1 second between requests
        http_response_code(429);
        die('Too many requests. Please slow down.');
    }
    $_SESSION['last_request'] = time();
}
?>





