<?php
require_once 'repository/NotificationRepository.php';

class Notification {
    private $conn;
    private $repository;
    private $table = 'notifications';

    public function __construct($db) {
        $this->conn = $db;
        $this->repository = new NotificationRepository($db);
    }

    public function create($data) {
        return $this->repository->createNotification($data);
    }

    public function getByUser($userId, $limit = 20, $offset = 0) {
        return $this->repository->findByUser($userId, $limit, $offset);
    }

    public function getUnreadCount($userId) {
        return $this->repository->getUnreadCount($userId);
    }

    public function markAsRead($id, $userId) {
        return $this->repository->markAsRead($id, $userId);
    }

    public function markAllAsRead($userId) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1, read_at = NOW() 
                  WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    public function delete($id, $userId) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    // Notification types
    public static function bookingCreated($userId, $bookingData) {
        $notification = new self((new Database())->connect());
        
        return $notification->create([
            'user_id' => $userId,
            'type' => 'booking_created',
            'title' => 'Nova Reserva',
            'message' => 'Você recebeu uma nova reserva para sua vaga.',
            'data' => $bookingData
        ]);
    }

    public static function bookingConfirmed($userId, $bookingData) {
        $notification = new self((new Database())->connect());
        
        return $notification->create([
            'user_id' => $userId,
            'type' => 'booking_confirmed',
            'title' => 'Reserva Confirmada',
            'message' => 'Sua reserva foi confirmada.',
            'data' => $bookingData
        ]);
    }

    public static function bookingCancelled($userId, $bookingData) {
        $notification = new self((new Database())->connect());
        
        return $notification->create([
            'user_id' => $userId,
            'type' => 'booking_cancelled',
            'title' => 'Reserva Cancelada',
            'message' => 'Uma reserva foi cancelada.',
            'data' => $bookingData
        ]);
    }

    public static function paymentReceived($userId, $paymentData) {
        $notification = new self((new Database())->connect());
        
        return $notification->create([
            'user_id' => $userId,
            'type' => 'payment_received',
            'title' => 'Pagamento Recebido',
            'message' => 'Você recebeu um pagamento de R$ ' . number_format($paymentData['amount'], 2, ',', '.'),
            'data' => $paymentData
        ]);
    }

    public static function reviewReceived($userId, $reviewData) {
        $notification = new self((new Database())->connect());
        
        return $notification->create([
            'user_id' => $userId,
            'type' => 'review_received',
            'title' => 'Nova Avaliação',
            'message' => 'Você recebeu uma nova avaliação de ' . $reviewData['rating'] . ' estrelas.',
            'data' => $reviewData
        ]);
    }
}
?>
