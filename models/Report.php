<?php
class Report {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getSpotStats($spotId) {
        $query = "SELECT 
                    COUNT(b.id) as total_bookings,
                    SUM(b.final_price) as total_revenue,
                    AVG(r.rating) as avg_rating,
                    COUNT(r.id) as total_reviews
                  FROM parking_spots ps
                  LEFT JOIN bookings b ON ps.id = b.spot_id
                  LEFT JOIN reviews r ON b.id = r.booking_id
                  WHERE ps.id = :spot_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function getUserStats($userId) {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM parking_spots WHERE owner_id = :user_id) as total_spots,
                    (SELECT COUNT(*) FROM bookings WHERE renter_id = :user_id) as total_bookings_as_renter,
                    (SELECT COUNT(*) FROM bookings b JOIN parking_spots ps ON b.spot_id = ps.id WHERE ps.owner_id = :user_id) as total_bookings_as_owner,
                    (SELECT SUM(final_price) FROM bookings b JOIN parking_spots ps ON b.spot_id = ps.id WHERE ps.owner_id = :user_id) as total_earnings,
                    (SELECT AVG(rating) FROM reviews r JOIN bookings b ON r.booking_id = b.id JOIN parking_spots ps ON b.spot_id = ps.id WHERE ps.owner_id = :user_id) as avg_rating_as_owner";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function getMonthlyRevenue($userId, $year = null) {
        if (!$year) {
            $year = date('Y');
        }

        $query = "SELECT 
                    MONTH(b.created_at) as month,
                    SUM(b.final_price) as revenue,
                    COUNT(b.id) as bookings_count
                  FROM bookings b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  WHERE ps.owner_id = :user_id 
                  AND YEAR(b.created_at) = :year
                  AND b.booking_status IN ('confirmed', 'active', 'completed')
                  GROUP BY MONTH(b.created_at)
                  ORDER BY month";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getPopularSpots($limit = 10) {
        $query = "SELECT 
                    ps.*,
                    u.name as owner_name,
                    COUNT(b.id) as booking_count,
                    SUM(b.final_price) as total_revenue,
                    AVG(r.rating) as avg_rating
                  FROM parking_spots ps
                  JOIN users u ON ps.owner_id = u.id
                  LEFT JOIN bookings b ON ps.id = b.spot_id
                  LEFT JOIN reviews r ON b.id = r.booking_id
                  WHERE ps.status = 'active'
                  GROUP BY ps.id
                  ORDER BY booking_count DESC, total_revenue DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getBookingTrends($days = 30) {
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as bookings_count,
                    SUM(final_price) as daily_revenue
                  FROM bookings
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getTopCities() {
        $query = "SELECT 
                    city,
                    COUNT(*) as spots_count,
                    AVG(price_daily) as avg_price
                  FROM parking_spots
                  WHERE status = 'active'
                  GROUP BY city
                  ORDER BY spots_count DESC
                  LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getRevenueBySpotType() {
        $query = "SELECT 
                    ps.spot_type,
                    COUNT(b.id) as bookings_count,
                    SUM(b.final_price) as total_revenue,
                    AVG(b.final_price) as avg_booking_value
                  FROM parking_spots ps
                  LEFT JOIN bookings b ON ps.id = b.spot_id
                  WHERE ps.status = 'active'
                  GROUP BY ps.spot_type
                  ORDER BY total_revenue DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getAdminStats() {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM users) as total_users,
                    (SELECT COUNT(*) FROM parking_spots WHERE status = 'active') as total_spots,
                    (SELECT COUNT(*) FROM bookings) as total_bookings,
                    (SELECT SUM(final_price) FROM bookings WHERE booking_status IN ('confirmed', 'active', 'completed')) as total_revenue,
                    (SELECT AVG(rating) FROM reviews) as avg_rating,
                    (SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_users_30d,
                    (SELECT COUNT(*) FROM parking_spots WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_spots_30d,
                    (SELECT COUNT(*) FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as new_bookings_30d";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>
