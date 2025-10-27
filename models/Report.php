<?php
require_once 'repository/ReportRepository.php';

class Report {
    private $conn;
    private $repository;

    public function __construct($db) {
        $this->conn = $db;
        $this->repository = new ReportRepository($db);
    }

    public function getSpotStats($spotId) {
        return $this->repository->getSpotStats($spotId);
    }

    public function getUserStats($userId) {
        return $this->repository->getUserStats($userId);
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
        return $this->repository->getAdminStats();
    }
}
?>
