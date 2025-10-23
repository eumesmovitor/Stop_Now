<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
    }

    public function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Protection
            if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de segurança inválido';
                header('Location: ' . BASE_URL);
                exit;
            }

            $email = sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validation
            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Email e senha são obrigatórios';
                header('Location: ' . BASE_URL);
                exit;
            }

            if (!validateEmail($email)) {
                $_SESSION['error'] = 'Email inválido';
                header('Location: ' . BASE_URL);
                exit;
            }

            if($this->user->login($email, $password)) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['user_name'] = $this->user->name;
                $_SESSION['user_email'] = $this->user->email;
                $_SESSION['is_verified'] = $this->user->is_verified;
                $_SESSION['login_time'] = time();
                
                // Log activity
                logActivity($this->user->id, 'LOGIN', 'User logged in successfully');
                
                redirectWithMessage(BASE_URL . '/dashboard', 'Login realizado com sucesso!');
            } else {
                // Log failed attempt
                logActivity(0, 'LOGIN_FAILED', "Failed login attempt for email: $email");
                
                $_SESSION['error'] = 'Email ou senha inválidos';
                header('Location: ' . BASE_URL);
                exit;
            }
        }
    }

    public function register() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Protection
            if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de segurança inválido';
                header('Location: ' . BASE_URL);
                exit;
            }

            $name = sanitizeInput($_POST['name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $phone = sanitizeInput($_POST['phone'] ?? '');
            $cpf = sanitizeInput($_POST['cpf'] ?? '');

            // Validation
            $errors = [];

            if (empty($name) || strlen($name) < 2) {
                $errors[] = 'Nome deve ter pelo menos 2 caracteres';
            }

            if (empty($email) || !validateEmail($email)) {
                $errors[] = 'Email inválido';
            }

            if (empty($password) || strlen($password) < 6) {
                $errors[] = 'Senha deve ter pelo menos 6 caracteres';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Senhas não coincidem';
            }

            if (!empty($phone) && !validatePhone($phone)) {
                $errors[] = 'Telefone inválido';
            }

            if (!empty($cpf) && !validateCPF($cpf)) {
                $errors[] = 'CPF inválido';
            }

            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
                header('Location: ' . BASE_URL);
                exit;
            }

            // Check if email already exists
            if ($this->user->emailExists($email)) {
                $_SESSION['error'] = 'Email já cadastrado';
                header('Location: ' . BASE_URL);
                exit;
            }

            $this->user->name = $name;
            $this->user->email = $email;
            $this->user->password = $password;
            $this->user->phone = $phone;
            $this->user->cpf = $cpf;

            if($this->user->register()) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $this->user->id;
                $_SESSION['user_name'] = $this->user->name;
                $_SESSION['user_email'] = $this->user->email;
                $_SESSION['is_verified'] = false;
                $_SESSION['login_time'] = time();
                
                // Log activity
                logActivity($this->user->id, 'REGISTER', 'User registered successfully');
                
                redirectWithMessage(BASE_URL . '/dashboard', 'Cadastro realizado com sucesso!');
            } else {
                $_SESSION['error'] = 'Erro ao realizar cadastro';
                header('Location: ' . BASE_URL);
                exit;
            }
        }
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            // Log activity
            logActivity($_SESSION['user_id'], 'LOGOUT', 'User logged out');
        }
        
        session_destroy();
        header('Location: ' . BASE_URL);
        exit;
    }

    public static function isLoggedIn() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
            session_destroy();
            return false;
        }
        
        // Renew session if it's been active for more than half the timeout
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > (SESSION_TIMEOUT / 2)) {
            $_SESSION['login_time'] = time();
        }
        
        return true;
    }

    public static function requireLogin() {
        if(!self::isLoggedIn()) {
            redirectWithMessage(BASE_URL, 'Você precisa estar logado para acessar esta página');
        }
    }

    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'is_verified' => $_SESSION['is_verified']
        ];
    }

    public function showLogin() {
        if (AuthController::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $pageTitle = 'Entrar - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/auth/login.php';
        require_once 'views/includes/footer.php';
    }

    public function showRegister() {
        if (AuthController::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $pageTitle = 'Cadastrar - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/auth/register.php';
        require_once 'views/includes/footer.php';
    }
}
?>
