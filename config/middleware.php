<?php
/**
 * Middleware System for StopNow
 */

class Middleware {
    private static $middlewares = [];
    
    public static function register($name, $callback) {
        self::$middlewares[$name] = $callback;
    }
    
    public static function execute($middlewareName) {
        if (isset(self::$middlewares[$middlewareName])) {
            return self::$middlewares[$middlewareName]();
        }
        return false;
    }
    
    public static function auth() {
        if (!AuthController::isLoggedIn()) {
            redirectWithMessage(BASE_URL, 'Você precisa estar logado para acessar esta página');
        }
        return true;
    }
    
    public static function guest() {
        if (AuthController::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        return true;
    }
    
    public static function admin() {
        if (!AuthController::isLoggedIn()) {
            redirectWithMessage(BASE_URL, 'Você precisa estar logado');
        }
        
        // Check if user is admin (you can implement this based on your user system)
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Acesso negado. Apenas administradores podem acessar esta página');
        }
        return true;
    }
    
    public static function api() {
        header('Content-Type: application/json');
        return true;
    }
    
    public static function cors() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        return true;
    }
    
    public static function csrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
                if (isset($_GET['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Token de segurança inválido']);
                    exit;
                }
                redirectWithMessage(BASE_URL, 'Token de segurança inválido', 'error');
            }
        }
        return true;
    }
    
    public static function rateLimit($requests = 100, $window = 3600) {
        $key = 'rate_limit_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $current = $_SESSION[$key] ?? 0;
        $time = time();
        
        if (!isset($_SESSION[$key . '_time']) || $time - $_SESSION[$key . '_time'] > $window) {
            $_SESSION[$key] = 1;
            $_SESSION[$key . '_time'] = $time;
        } else {
            $_SESSION[$key]++;
            if ($_SESSION[$key] > $requests) {
                if (isset($_GET['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Muitas requisições. Tente novamente mais tarde.']);
                    exit;
                }
                redirectWithMessage(BASE_URL, 'Muitas requisições. Tente novamente mais tarde.', 'error');
            }
        }
        return true;
    }
}

// Register default middlewares
Middleware::register('auth', [Middleware::class, 'auth']);
Middleware::register('guest', [Middleware::class, 'guest']);
Middleware::register('admin', [Middleware::class, 'admin']);
Middleware::register('api', [Middleware::class, 'api']);
Middleware::register('cors', [Middleware::class, 'cors']);
Middleware::register('csrf', [Middleware::class, 'csrf']);
Middleware::register('rate_limit', [Middleware::class, 'rateLimit']);
?>
