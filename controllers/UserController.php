<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/User.php';

class UserController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
    }

    public function show($params = []) {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        $userData = $this->user->getById($userId);
        
        $pageTitle = 'Meu Perfil - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/profile.php';
        require_once 'views/includes/footer.php';
    }

    public function edit($params = []) {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        $userData = $this->user->getById($userId);
        
        $pageTitle = 'Editar Perfil - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/profile-edit.php';
        require_once 'views/includes/footer.php';
    }

    public function update($params = []) {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
                redirectWithMessage(BASE_URL . '/profile', 'Token de segurança inválido', 'error');
            }

            $userId = $_SESSION['user_id'];
            $data = [
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'cpf' => sanitizeInput($_POST['cpf'] ?? '')
            ];

            if ($this->user->updateProfile($userId, $data)) {
                $_SESSION['user_name'] = $data['name'];
                redirectWithMessage(BASE_URL . '/profile', 'Perfil atualizado com sucesso!');
            } else {
                redirectWithMessage(BASE_URL . '/profile', 'Erro ao atualizar perfil', 'error');
            }
        }
    }
}
?>




