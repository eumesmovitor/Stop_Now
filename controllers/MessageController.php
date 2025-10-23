<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/Message.php';
require_once 'models/User.php';

class MessageController {
    private $db;
    private $message;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->message = new Message($this->db);
        $this->user = new User($this->db);
    }

    public function index() {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        $conversations = $this->message->getConversations($userId);
        $unreadCount = $this->message->getUnreadCount($userId);
        
        $pageTitle = 'Mensagens - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/messages.php';
        require_once 'views/includes/footer.php';
    }

    public function conversation() {
        AuthController::requireLogin();
        
        $otherUserId = intval($_GET['user_id'] ?? 0);
        $bookingId = intval($_GET['booking_id'] ?? 0);
        
        if (!$otherUserId) {
            redirectWithMessage(BASE_URL . '/messages', 'Usuário não encontrado', 'error');
        }
        
        $userId = $_SESSION['user_id'];
        $otherUser = $this->user->getById($otherUserId);
        
        if (!$otherUser) {
            redirectWithMessage(BASE_URL . '/messages', 'Usuário não encontrado', 'error');
        }
        
        $messages = $this->message->getConversation($userId, $otherUserId, $bookingId ?: null);
        
        $pageTitle = 'Conversa - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/message-conversation.php';
        require_once 'views/includes/footer.php';
    }

    public function send() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectWithMessage(BASE_URL . '/messages', 'Método não permitido', 'error');
        }
        
        // CSRF Protection
        if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
            redirectWithMessage(BASE_URL . '/messages', 'Token de segurança inválido', 'error');
        }
        
        $senderId = $_SESSION['user_id'];
        $receiverId = intval($_POST['receiver_id'] ?? 0);
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');
        $bookingId = intval($_POST['booking_id'] ?? 0);
        
        if (!$receiverId || empty($message)) {
            redirectWithMessage(BASE_URL . '/messages', 'Dados inválidos', 'error');
        }
        
        $data = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'subject' => $subject,
            'message' => $message,
            'booking_id' => $bookingId ?: null
        ];
        
        if ($this->message->create($data)) {
            redirectWithMessage(BASE_URL . '/messages/conversation?user_id=' . $receiverId . ($bookingId ? '&booking_id=' . $bookingId : ''), 'Mensagem enviada com sucesso!');
        } else {
            redirectWithMessage(BASE_URL . '/messages', 'Erro ao enviar mensagem', 'error');
        }
    }

    public function markAsRead() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        $messageId = intval($_POST['message_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        if ($this->message->markAsRead($messageId, $userId)) {
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
        
        if ($this->message->markAllAsRead($userId)) {
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
        
        $messageId = intval($_POST['message_id'] ?? 0);
        $userId = $_SESSION['user_id'];
        
        if ($this->message->delete($messageId, $userId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir mensagem']);
        }
    }

    public function getUnreadCount() {
        AuthController::requireLogin();
        
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user_id'];
        $count = $this->message->getUnreadCount($userId);
        
        echo json_encode(['count' => $count]);
    }
}
?>
