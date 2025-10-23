<?php
class Favorite {
    private $conn;
    private $table = 'favorites';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function add($userId, $spotId) {
        // Check if already exists
        if ($this->exists($userId, $spotId)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " (user_id, spot_id, created_at) 
                  VALUES (:user_id, :spot_id, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':spot_id', $spotId);
        
        return $stmt->execute();
    }

    public function remove($userId, $spotId) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE user_id = :user_id AND spot_id = :spot_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':spot_id', $spotId);
        
        return $stmt->execute();
    }

    public function exists($userId, $spotId) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE user_id = :user_id AND spot_id = :spot_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    public function getByUser($userId, $limit = 20, $offset = 0) {
        $query = "SELECT f.*, ps.*, u.name as owner_name, u.is_verified as owner_verified,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                  (SELECT AVG(rating) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as avg_rating
                  FROM " . $this->table . " f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  JOIN users u ON ps.owner_id = u.id
                  WHERE f.user_id = :user_id AND ps.status = 'active'
                  ORDER BY f.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getCountByUser($userId) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  WHERE f.user_id = :user_id AND ps.status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function toggle($userId, $spotId) {
        if ($this->exists($userId, $spotId)) {
            return $this->remove($userId, $spotId) ? 'removed' : false;
        } else {
            return $this->add($userId, $spotId) ? 'added' : false;
        }
    }
}
?>
