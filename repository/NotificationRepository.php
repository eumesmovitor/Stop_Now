<?php

require_once 'BaseRepository.php';

/**
 * Repository para gerenciar operações de notificações
 */
class NotificationRepository extends BaseRepository {
    protected $table = 'notifications';
    protected $primaryKey = 'id';

    public function __construct($db) {
        parent::__construct($db);
    }

    /**
     * Cria uma nova notificação
     */
    public function createNotification($data) {
        $notificationData = [
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => json_encode($data['data'] ?? []),
            'is_read' => $data['is_read'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($notificationData);
    }

    /**
     * Busca notificações por usuário
     */
    public function findByUser($userId, $limit = 20, $offset = 0) {
        $query = "SELECT * FROM {$this->table} 
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

    /**
     * Busca contagem de notificações não lidas
     */
    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} 
                  WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Marca notificação como lida
     */
    public function markAsRead($id, $userId) {
        return $this->update($id, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marca todas as notificações como lidas
     */
    public function markAllAsRead($userId) {
        $query = "UPDATE {$this->table} 
                  SET is_read = 1, read_at = NOW() 
                  WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Remove notificação
     */
    public function deleteNotification($id, $userId) {
        $query = "DELETE FROM {$this->table} 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Busca notificações por tipo
     */
    public function findByType($userId, $type, $limit = 20, $offset = 0) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE user_id = :user_id AND type = :type
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca notificações com filtros
     */
    public function findWithFilters($filters = [], $limit = 20, $offset = 0) {
        $query = "SELECT * FROM {$this->table}";
        
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['user_id'])) {
            $whereClause[] = "user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['type'])) {
            $whereClause[] = "type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if (isset($filters['is_read'])) {
            $whereClause[] = "is_read = :is_read";
            $params[':is_read'] = $filters['is_read'] ? 1 : 0;
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause[] = "created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause[] = "created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($whereClause)) {
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Conta notificações com filtros
     */
    public function countWithFilters($filters = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['user_id'])) {
            $whereClause[] = "user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['type'])) {
            $whereClause[] = "type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if (isset($filters['is_read'])) {
            $whereClause[] = "is_read = :is_read";
            $params[':is_read'] = $filters['is_read'] ? 1 : 0;
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause[] = "created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause[] = "created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($whereClause)) {
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['total'];
    }

    /**
     * Busca estatísticas de notificações
     */
    public function getStats($userId = null) {
        $query = "SELECT 
                    COUNT(*) as total_notifications,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications,
                    COUNT(DISTINCT type) as unique_types
                  FROM {$this->table}";
        
        $params = [];
        
        if ($userId) {
            $query .= " WHERE user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Busca notificações recentes
     */
    public function getRecentNotifications($limit = 10) {
        $query = "SELECT n.*, u.name as user_name
                  FROM {$this->table} n
                  JOIN users u ON n.user_id = u.id
                  ORDER BY n.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Remove notificações antigas
     */
    public function deleteOldNotifications($days = 30) {
        $query = "DELETE FROM {$this->table} 
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // Métodos estáticos para criar notificações específicas

    /**
     * Cria notificação de nova reserva
     */
    public static function createBookingNotification($db, $userId, $bookingData) {
        $repo = new self($db);
        
        return $repo->createNotification([
            'user_id' => $userId,
            'type' => 'booking_created',
            'title' => 'Nova Reserva',
            'message' => 'Você recebeu uma nova reserva para sua vaga.',
            'data' => $bookingData
        ]);
    }

    /**
     * Cria notificação de reserva confirmada
     */
    public static function createBookingConfirmedNotification($db, $userId, $bookingData) {
        $repo = new self($db);
        
        return $repo->createNotification([
            'user_id' => $userId,
            'type' => 'booking_confirmed',
            'title' => 'Reserva Confirmada',
            'message' => 'Sua reserva foi confirmada.',
            'data' => $bookingData
        ]);
    }

    /**
     * Cria notificação de reserva cancelada
     */
    public static function createBookingCancelledNotification($db, $userId, $bookingData) {
        $repo = new self($db);
        
        return $repo->createNotification([
            'user_id' => $userId,
            'type' => 'booking_cancelled',
            'title' => 'Reserva Cancelada',
            'message' => 'Uma reserva foi cancelada.',
            'data' => $bookingData
        ]);
    }

    /**
     * Cria notificação de pagamento recebido
     */
    public static function createPaymentReceivedNotification($db, $userId, $paymentData) {
        $repo = new self($db);
        
        return $repo->createNotification([
            'user_id' => $userId,
            'type' => 'payment_received',
            'title' => 'Pagamento Recebido',
            'message' => 'Você recebeu um pagamento de R$ ' . number_format($paymentData['amount'], 2, ',', '.'),
            'data' => $paymentData
        ]);
    }

    /**
     * Cria notificação de nova avaliação
     */
    public static function createReviewReceivedNotification($db, $userId, $reviewData) {
        $repo = new self($db);
        
        return $repo->createNotification([
            'user_id' => $userId,
            'type' => 'review_received',
            'title' => 'Nova Avaliação',
            'message' => 'Você recebeu uma nova avaliação de ' . $reviewData['rating'] . ' estrelas.',
            'data' => $reviewData
        ]);
    }
}
