<?php
class Review {
    private $conn;
    private $table = 'reviews';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getBySpot($spotId) {
        $query = "SELECT r.*, u.name as reviewer_name
                  FROM " . $this->table . " r
                  JOIN bookings b ON r.booking_id = b.id
                  JOIN users u ON r.reviewer_id = u.id
                  WHERE b.spot_id = :spot_id
                  ORDER BY r.created_at DESC
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getByUser($userId) {
        $query = "SELECT r.*, ps.title as spot_title
                  FROM " . $this->table . " r
                  JOIN bookings b ON r.booking_id = b.id
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  WHERE r.reviewer_id = :user_id
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (booking_id, reviewer_id, rating, comment, created_at)
                  VALUES 
                  (:booking_id, :reviewer_id, :rating, :comment, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':booking_id', $data['booking_id']);
        $stmt->bindParam(':reviewer_id', $data['reviewer_id']);
        $stmt->bindParam(':rating', $data['rating']);
        $stmt->bindParam(':comment', $data['comment']);
        
        return $stmt->execute();
    }

    public function getAverageRating($spotId) {
        $query = "SELECT AVG(r.rating) as avg_rating, COUNT(r.id) as total_reviews
                  FROM " . $this->table . " r
                  JOIN bookings b ON r.booking_id = b.id
                  WHERE b.spot_id = :spot_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function getRatingDistribution($spotId) {
        $query = "SELECT 
                    rating,
                    COUNT(*) as count
                  FROM " . $this->table . " r
                  JOIN bookings b ON r.booking_id = b.id
                  WHERE b.spot_id = :spot_id
                  GROUP BY rating
                  ORDER BY rating DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function canUserReview($userId, $bookingId) {
        // Check if user has already reviewed this booking
        $query = "SELECT COUNT(*) FROM " . $this->table . " 
                  WHERE booking_id = :booking_id AND reviewer_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':booking_id', $bookingId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchColumn() == 0;
    }

    public function getRecentReviews($limit = 10) {
        $query = "SELECT r.*, u.name as reviewer_name, ps.title as spot_title
                  FROM " . $this->table . " r
                  JOIN users u ON r.reviewer_id = u.id
                  JOIN bookings b ON r.booking_id = b.id
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  ORDER BY r.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getTopRatedSpots($limit = 10) {
        $query = "SELECT 
                    ps.*,
                    AVG(r.rating) as avg_rating,
                    COUNT(r.id) as review_count
                  FROM parking_spots ps
                  LEFT JOIN bookings b ON ps.id = b.spot_id
                  LEFT JOIN reviews r ON b.id = r.booking_id
                  WHERE ps.status = 'active'
                  GROUP BY ps.id
                  HAVING review_count > 0
                  ORDER BY avg_rating DESC, review_count DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>