<?php
/**
 * Advanced Router System for StopNow
 */

class AdvancedRouter {
    private $routes = [];
    private $middlewares = [];
    private $groupMiddleware = [];
    private $currentGroup = '';
    
    public function __construct() {
        $this->middlewares = [
            'auth' => function() {
                if (!AuthController::isLoggedIn()) {
                    redirectWithMessage(BASE_URL, 'Você precisa estar logado para acessar esta página');
                }
            },
            'guest' => function() {
                if (AuthController::isLoggedIn()) {
                    header('Location: ' . BASE_URL . '/dashboard');
                    exit;
                }
            },
            'admin' => function() {
                if (!AuthController::isLoggedIn()) {
                    redirectWithMessage(BASE_URL, 'Você precisa estar logado');
                }
                // Add admin check here
            },
            'api' => function() {
                header('Content-Type: application/json');
            },
            'cors' => function() {
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: Content-Type, Authorization');
            }
        ];
    }
    
    public function group($prefix, $callback) {
        $this->currentGroup = $prefix;
        $callback($this);
        $this->currentGroup = '';
    }
    
    public function middleware($middleware) {
        $this->groupMiddleware[] = $middleware;
        return $this;
    }
    
    public function get($path, $handler, $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post($path, $handler, $middleware = []) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put($path, $handler, $middleware = []) {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function delete($path, $handler, $middleware = []) {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    public function patch($path, $handler, $middleware = []) {
        $this->addRoute('PATCH', $path, $handler, $middleware);
    }
    
    public function options($path, $handler, $middleware = []) {
        $this->addRoute('OPTIONS', $path, $handler, $middleware);
    }
    
    public function any($path, $handler, $middleware = []) {
        $this->addRoute('ANY', $path, $handler, $middleware);
    }
    
    private function addRoute($method, $path, $handler, $middleware = []) {
        $fullPath = $this->currentGroup . $path;
        $allMiddleware = array_merge($this->groupMiddleware, $middleware);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $allMiddleware
        ];
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $this->getCurrentPath();
        
        // Handle OPTIONS for CORS
        if ($method === 'OPTIONS') {
            $this->handleCORS();
            return;
        }
        
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $method, $path)) {
                $this->executeRoute($route, $path);
                return;
            }
        }
        
        $this->handle404();
    }
    
    private function getCurrentPath() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
        $baseUrl = str_replace(['http://', 'https://'], '', BASE_URL ?? '');
        $baseUrl = str_replace($_SERVER['HTTP_HOST'] ?? '', '', $baseUrl);
        $path = str_replace($baseUrl, '', $path);
        return rtrim($path, '/') ?: '/';
    }
    
    private function matchRoute($route, $method, $path) {
        // Check method
        if ($route['method'] !== 'ANY' && $route['method'] !== $method) {
            return false;
        }
        
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['path']);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $path);
    }
    
    private function executeRoute($route, $path) {
        // Execute middleware
        foreach ($route['middleware'] as $middlewareName) {
            if (isset($this->middlewares[$middlewareName])) {
                $this->middlewares[$middlewareName]();
            }
        }
        
        // Extract parameters
        $params = $this->extractParams($route['path'], $path);
        
        // Execute handler
        $this->executeHandler($route['handler'], $params);
    }
    
    private function extractParams($route, $path) {
        $routeParts = explode('/', $route);
        $pathParts = explode('/', $path);
        $params = [];
        
        for ($i = 0; $i < count($routeParts); $i++) {
            if (isset($routeParts[$i]) && preg_match('/\{([^}]+)\}/', $routeParts[$i], $matches)) {
                $paramName = $matches[1];
                $params[$paramName] = $pathParts[$i] ?? null;
            }
        }
        
        return $params;
    }
    
    private function executeHandler($handler, $params = []) {
        if (is_callable($handler)) {
            call_user_func($handler, $params);
        } elseif (is_string($handler)) {
            $parts = explode('@', $handler);
            if (count($parts) === 2) {
                $controllerName = $parts[0];
                $methodName = $parts[1];
                
                // Load controller
                $controllerFile = "controllers/{$controllerName}.php";
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    
                    if (class_exists($controllerName)) {
                        $controller = new $controllerName();
                        if (method_exists($controller, $methodName)) {
                            if (empty($params)) {
                                $controller->$methodName();
                            } else {
                                $controller->$methodName($params);
                            }
                        } else {
                            $this->handle404();
                        }
                    } else {
                        $this->handle404();
                    }
                } else {
                    $this->handle404();
                }
            } else {
                $this->handle404();
            }
        } else {
            $this->handle404();
        }
    }
    
    private function handleCORS() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        http_response_code(200);
    }
    
    private function handle404() {
        http_response_code(404);
        $pageTitle = '404 - Página não encontrada';
        require_once 'views/includes/header.php';
        echo '<div class="min-h-screen bg-gray-50 flex items-center justify-center">';
        echo '<div class="text-center">';
        echo '<h1 class="text-6xl font-bold text-primary mb-4">404</h1>';
        echo '<p class="text-xl text-gray-600 mb-8">Página não encontrada</p>';
        echo '<a href="' . BASE_URL . '" class="bg-accent text-primary px-6 py-3 rounded-lg font-semibold hover:bg-accent-dark transition">';
        echo 'Voltar ao Início</a>';
        echo '</div></div>';
        require_once 'views/includes/footer.php';
    }
    
    public function getRoutes() {
        return $this->routes;
    }
    
    public function listRoutes() {
        echo "<h2>Registered Routes:</h2>\n";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Method</th><th>Path</th><th>Handler</th><th>Middleware</th></tr>\n";
        
        foreach ($this->routes as $route) {
            echo "<tr>";
            echo "<td>{$route['method']}</td>";
            echo "<td>{$route['path']}</td>";
            echo "<td>" . (is_string($route['handler']) ? $route['handler'] : 'Closure') . "</td>";
            echo "<td>" . implode(', ', $route['middleware']) . "</td>";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
    }
}
?>





