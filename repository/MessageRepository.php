<?php

require_once 'BaseRepository.php';

/**
 * Repository para gerenciar operações de mensagens
 */
class MessageRepository extends BaseRepository {
    protected $table = 'messages';
    protected $primaryKey = 'id';

    public function __construct($db) {
        parent::__construct($db);
    }

    /**
     * Cria uma nova mensagem
     */
    public function createMessage($data) {
        $messageData = [
            'sender_id' => $data['sender_id'],
            'receiver_id' => $data['receiver_id'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'booking_id' => $data['booking_id'] ?? null,
            'is_read' => $data['is_read'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($messageData);
    }

    /**
     * Busca mensagens por usuário
     */
    public function findByUser($userId, $limit = 20, $offset = 0) {
        $query = "SELECT m.*, 
                    s.name as sender_name, s.email as sender_email,
                    r.name as receiver_name, r.email as receiver_email,
                    b.spot_id, ps.title as spot_title
                  FROM {$this->table} m
                  JOIN users s ON m.sender_id = s.id
                  JOIN users r ON m.receiver_id = r.id
                  LEFT JOIN bookings b ON m.booking_id = b.id
                  LEFT JOIN parking_spots ps ON b.spot_id = ps.id
                  WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                  ORDER BY m.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca conversa entre dois usuários
     */
    public function getConversation($userId1, $userId2, $bookingId = null) {
        $query = "SELECT m.*, 
                    s.name as sender_name, s.email as sender_email,
                    r.name as receiver_name, r.email as receiver_email
                  FROM {$this->table} m
                  JOIN users s ON m.sender_id = s.id
                  JOIN users r ON m.receiver_id = r.id
                  WHERE ((m.sender_id = :user_id1 AND m.receiver_id = :user_id2) 
                         OR (m.sender_id = :user_id2 AND m.receiver_id = :user_id1))";
        
        $params = [
            ':user_id1' => $userId1,
            ':user_id2' => $userId2
        ];
        
        if ($bookingId) {
            $query .= " AND m.booking_id = :booking_id";
            $params[':booking_id'] = $bookingId;
        }
        
        $query .= " ORDER BY m.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca contagem de mensagens não lidas
     */
    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} 
                  WHERE receiver_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Marca mensagem como lida
     */
    public function markAsRead($id, $userId) {
        return $this->update($id, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marca todas as mensagens como lidas
     */
    public function markAllAsRead($userId) {
        $query = "UPDATE {$this->table} 
                  SET is_read = 1, read_at = NOW() 
                  WHERE receiver_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Remove mensagem
     */
    public function deleteMessage($id, $userId) {
        $query = "DELETE FROM {$this->table} 
                  WHERE id = :id AND (sender_id = :user_id OR receiver_id = :user_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Busca conversas do usuário
     */
    public function getConversations($userId) {
        $query = "SELECT DISTINCT 
                    CASE 
                        WHEN m.sender_id = :user_id THEN m.receiver_id 
                        ELSE m.sender_id 
                    END as other_user_id,
                    u.name as other_user_name,
                    u.email as other_user_email,
                    MAX(m.created_at) as last_message_time,
                    COUNT(CASE WHEN m.receiver_id = :user_id AND m.is_read = 0 THEN 1 END) as unread_count
                  FROM {$this->table} m
                  JOIN users u ON (CASE WHEN m.sender_id = :user_id THEN m.receiver_id ELSE m.sender_id END) = u.id
                  WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                  GROUP BY other_user_id, u.name, u.email
                  ORDER BY last_message_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca mensagens com filtros
     */
    public function findWithFilters($filters = [], $limit = 20, $offset = 0) {
        $query = "SELECT m.*, 
                    s.name as sender_name, s.email as sender_email,
                    r.name as receiver_name, r.email as receiver_email,
                    b.spot_id, ps.title as spot_title
                  FROM {$this->table} m
                  JOIN users s ON m.sender_id = s.id
                  JOIN users r ON m.receiver_id = r.id
                  LEFT JOIN bookings b ON m.booking_id = b.id
                  LEFT JOIN parking_spots ps ON b.spot_id = ps.id";
        
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['user_id'])) {
            $whereClause[] = "(m.sender_id = :user_id OR m.receiver_id = :user_id)";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['sender_id'])) {
            $whereClause[] = "m.sender_id = :sender_id";
            $params[':sender_id'] = $filters['sender_id'];
        }
        
        if (!empty($filters['receiver_id'])) {
            $whereClause[] = "m.receiver_id = :receiver_id";
            $params[':receiver_id'] = $filters['receiver_id'];
        }
        
        if (!empty($filters['booking_id'])) {
            $whereClause[] = "m.booking_id = :booking_id";
            $params[':booking_id'] = $filters['booking_id'];
        }
        
        if (isset($filters['is_read'])) {
            $whereClause[] = "m.is_read = :is_read";
            $params[':is_read'] = $filters['is_read'] ? 1 : 0;
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause[] = "m.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause[] = "m.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($whereClause)) {
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $query .= " ORDER BY m.created_at DESC LIMIT :limit OFFSET :offset";
        
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
     * Conta mensagens com filtros
     */
    public function countWithFilters($filters = []) {
        $query = "SELECT COUNT(*) as total
                  FROM {$this->table} m
                  JOIN users s ON m.sender_id = s.id
                  JOIN users r ON m.receiver_id = r.id
                  LEFT JOIN bookings b ON m.booking_id = b.id
                  LEFT JOIN parking_spots ps ON b.spot_id = ps.id";
        
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['user_id'])) {
            $whereClause[] = "(m.sender_id = :user_id OR m.receiver_id = :user_id)";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['sender_id'])) {
            $whereClause[] = "m.sender_id = :sender_id";
            $params[':sender_id'] = $filters['sender_id'];
        }
        
        if (!empty($filters['receiver_id'])) {
            $whereClause[] = "m.receiver_id = :receiver_id";
            $params[':receiver_id'] = $filters['receiver_id'];
        }
        
        if (!empty($filters['booking_id'])) {
            $whereClause[] = "m.booking_id = :booking_id";
            $params[':booking_id'] = $filters['booking_id'];
        }
        
        if (isset($filters['is_read'])) {
            $whereClause[] = "m.is_read = :is_read";
            $params[':is_read'] = $filters['is_read'] ? 1 : 0;
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause[] = "m.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause[] = "m.created_at <= :date_to";
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
     * Busca estatísticas de mensagens
     */
    public function getStats($userId = null) {
        $query = "SELECT 
                    COUNT(*) as total_messages,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_messages,
                    COUNT(DISTINCT sender_id) as unique_senders,
                    COUNT(DISTINCT receiver_id) as unique_receivers
                  FROM {$this->table}";
        
        $params = [];
        
        if ($userId) {
            $query .= " WHERE sender_id = :user_id OR receiver_id = :user_id";
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
     * Busca mensagens recentes
     */
    public function getRecentMessages($limit = 10) {
        $query = "SELECT m.*, 
                    s.name as sender_name, s.email as sender_email,
                    r.name as receiver_name, r.email as receiver_email
                  FROM {$this->table} m
                  JOIN users s ON m.sender_id = s.id
                  JOIN users r ON m.receiver_id = r.id
                  ORDER BY m.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
