<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/Notification.php';

class NotificationController {
    private $db;
    private $notification;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->notification = new Notification($this->db);
    }

    public function index() {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        $notifications = $this->notification->getByUser($userId);
        $unreadCount = $this->notification->getUnreadCount($userId);
        
        $pageTitle = 'Notificações - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/notifications.php';
        require_once 'views/includes/footer.php';
    }

    public function getUnread() {
        AuthController::requireLogin();
        
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user_id'];
        $count = $this->notification->getUnreadCount($userId);
        
        echo json_encode(['count' => $count]);
    }

    public function markAsRead() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        $notificationId = intval($_POST['notification_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        if ($this->notification->markAsRead($notificationId, $userId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao marcar como lida']);
        }
    }

    public function markAllAsRead() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        if ($this->notification->markAllAsRead($userId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao marcar todas como lidas']);
        }
    }

    public function delete() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        $notificationId = intval($_POST['notification_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        if ($this->notification->delete($notificationId, $userId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir notificação']);
        }
    }

    public function getRecent() {
        AuthController::requireLogin();
        
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user_id'];
        $notifications = $this->notification->getByUser($userId, 5);
        
        echo json_encode($notifications);
    }
}
?>
