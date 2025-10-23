<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/Favorite.php';

class FavoriteController {
    private $db;
    private $favorite;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->favorite = new Favorite($this->db);
    }

    public function index() {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        $favorites = $this->favorite->getByUser($userId, $limit, $offset);
        $totalFavorites = $this->favorite->getCountByUser($userId);
        $totalPages = ceil($totalFavorites / $limit);
        
        $pageTitle = 'Meus Favoritos - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/favorites.php';
        require_once 'views/includes/footer.php';
    }

    public function toggle() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        // CSRF Protection
        if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token de segurança inválido']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $spotId = intval($_POST['spot_id'] ?? 0);
        
        if (!$spotId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID da vaga inválido']);
            return;
        }
        
        $result = $this->favorite->toggle($userId, $spotId);
        
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'action' => $result,
                'message' => $result === 'added' ? 'Adicionado aos favoritos' : 'Removido dos favoritos'
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar favorito']);
        }
    }

    public function add() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        // CSRF Protection
        if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token de segurança inválido']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $spotId = intval($_POST['spot_id'] ?? 0);
        
        if (!$spotId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID da vaga inválido']);
            return;
        }
        
        if ($this->favorite->add($userId, $spotId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Adicionado aos favoritos']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao adicionar aos favoritos']);
        }
    }

    public function remove() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        // CSRF Protection
        if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token de segurança inválido']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $spotId = intval($_POST['spot_id'] ?? 0);
        
        if (!$spotId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID da vaga inválido']);
            return;
        }
        
        if ($this->favorite->remove($userId, $spotId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Removido dos favoritos']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao remover dos favoritos']);
        }
    }

    public function check() {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        $spotId = intval($_GET['spot_id'] ?? 0);
        
        if (!$spotId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID da vaga inválido']);
            return;
        }
        
        $isFavorite = $this->favorite->exists($userId, $spotId);
        
        header('Content-Type: application/json');
        echo json_encode(['is_favorite' => $isFavorite]);
    }
}
?>
