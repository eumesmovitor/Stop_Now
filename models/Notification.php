<?php
class Notification {
    private $conn;
    private $table = 'notifications';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, type, title, message, data, is_read, created_at)
                  VALUES 
                  (:user_id, :type, :title, :message, :data, :is_read, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':type', $data['type']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':data', json_encode($data['data'] ?? []));
        $stmt->bindParam(':is_read', $data['is_read'] ?? 0, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }

    public function getByUser($userId, $limit = 20, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function markAsRead($id, $userId) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1, read_at = NOW() 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
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
