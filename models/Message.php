<?php
class Message {
    private $conn;
    private $table = 'messages';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (sender_id, receiver_id, subject, message, booking_id, is_read, created_at)
                  VALUES 
                  (:sender_id, :receiver_id, :subject, :message, :booking_id, :is_read, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sender_id', $data['sender_id']);
        $stmt->bindParam(':receiver_id', $data['receiver_id']);
        $stmt->bindParam(':subject', $data['subject']);
        $stmt->bindParam(':message', $data['message']);
        $stmt->bindParam(':booking_id', $data['booking_id'] ?? null);
        $stmt->bindParam(':is_read', $data['is_read'] ?? 0, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }

    public function getByUser($userId, $limit = 20, $offset = 0) {
        $query = "SELECT m.*, 
                    s.name as sender_name, s.email as sender_email,
                    r.name as receiver_name, r.email as receiver_email,
                    b.spot_id, ps.title as spot_title
                  FROM " . $this->table . " m
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

    public function getConversation($userId1, $userId2, $bookingId = null) {
        $query = "SELECT m.*, 
                    s.name as sender_name, s.email as sender_email,
                    r.name as receiver_name, r.email as receiver_email
                  FROM " . $this->table . " m
                  JOIN users s ON m.sender_id = s.id
                  JOIN users r ON m.receiver_id = r.id
                  WHERE ((m.sender_id = :user_id1 AND m.receiver_id = :user_id2) 
                         OR (m.sender_id = :user_id2 AND m.receiver_id = :user_id1))";
        
        if ($bookingId) {
            $query .= " AND m.booking_id = :booking_id";
        }
        
        $query .= " ORDER BY m.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id1', $userId1);
        $stmt->bindParam(':user_id2', $userId2);
        
        if ($bookingId) {
            $stmt->bindParam(':booking_id', $bookingId);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE receiver_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function markAsRead($id, $userId) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1, read_at = NOW() 
                  WHERE id = :id AND receiver_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    public function markAllAsRead($userId) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1, read_at = NOW() 
                  WHERE receiver_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    public function delete($id, $userId) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :id AND (sender_id = :user_id OR receiver_id = :user_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

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
                  FROM " . $this->table . " m
                  JOIN users u ON (CASE WHEN m.sender_id = :user_id THEN m.receiver_id ELSE m.sender_id END) = u.id
                  WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                  GROUP BY other_user_id, u.name, u.email
                  ORDER BY last_message_time DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>
