<?php

require_once 'BaseRepository.php';

/**
 * Repository para gerenciar operações de relatórios e estatísticas
 */
class ReportRepository extends BaseRepository {
    protected $table = 'reports';
    protected $primaryKey = 'id';

    public function __construct($db) {
        parent::__construct($db);
    }

    /**
     * Busca estatísticas de uma vaga
     */
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

    /**
     * Busca estatísticas de um usuário
     */
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

    /**
     * Busca receita mensal
     */
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

    /**
     * Busca vagas mais populares
     */
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

    /**
     * Busca tendências de reservas
     */
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

    /**
     * Busca cidades mais populares
     */
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

    /**
     * Busca receita por tipo de vaga
     */
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

    /**
     * Busca estatísticas administrativas
     */
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

    /**
     * Busca estatísticas de reservas por status
     */
    public function getBookingStatsByStatus() {
        $query = "SELECT 
                    booking_status,
                    COUNT(*) as count,
                    SUM(final_price) as total_revenue
                  FROM bookings
                  GROUP BY booking_status
                  ORDER BY count DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca estatísticas de usuários por período
     */
    public function getUserStatsByPeriod($days = 30) {
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as new_users
                  FROM users
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca estatísticas de vagas por período
     */
    public function getSpotStatsByPeriod($days = 30) {
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as new_spots
                  FROM parking_spots
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca estatísticas de avaliações
     */
    public function getReviewStats() {
        $query = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as avg_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                  FROM reviews";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Busca estatísticas de favoritos
     */
    public function getFavoriteStats() {
        $query = "SELECT 
                    COUNT(*) as total_favorites,
                    COUNT(DISTINCT user_id) as users_with_favorites,
                    COUNT(DISTINCT spot_id) as unique_spots_favorited
                  FROM favorites f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  WHERE ps.status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Busca estatísticas de mensagens
     */
    public function getMessageStats() {
        $query = "SELECT 
                    COUNT(*) as total_messages,
                    COUNT(DISTINCT sender_id) as unique_senders,
                    COUNT(DISTINCT receiver_id) as unique_receivers
                  FROM messages";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Busca estatísticas de notificações
     */
    public function getNotificationStats() {
        $query = "SELECT 
                    COUNT(*) as total_notifications,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications,
                    COUNT(DISTINCT type) as unique_types
                  FROM notifications";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Busca relatório de performance por vaga
     */
    public function getSpotPerformanceReport($spotId, $startDate = null, $endDate = null) {
        $query = "SELECT 
                    ps.title,
                    ps.address,
                    ps.city,
                    ps.price_daily,
                    COUNT(b.id) as total_bookings,
                    SUM(b.final_price) as total_revenue,
                    AVG(r.rating) as avg_rating,
                    COUNT(r.id) as total_reviews,
                    AVG(DATEDIFF(b.end_date, b.start_date) + 1) as avg_booking_duration
                  FROM parking_spots ps
                  LEFT JOIN bookings b ON ps.id = b.spot_id";
        
        $params = [':spot_id' => $spotId];
        
        if ($startDate) {
            $query .= " AND b.created_at >= :start_date";
            $params[':start_date'] = $startDate;
        }
        
        if ($endDate) {
            $query .= " AND b.created_at <= :end_date";
            $params[':end_date'] = $endDate;
        }
        
        $query .= " LEFT JOIN reviews r ON b.id = r.booking_id
                    WHERE ps.id = :spot_id
                    GROUP BY ps.id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Busca relatório de performance por usuário
     */
    public function getUserPerformanceReport($userId, $startDate = null, $endDate = null) {
        $query = "SELECT 
                    u.name,
                    u.email,
                    COUNT(DISTINCT ps.id) as total_spots,
                    COUNT(b.id) as total_bookings,
                    SUM(b.final_price) as total_revenue,
                    AVG(r.rating) as avg_rating,
                    COUNT(r.id) as total_reviews
                  FROM users u
                  LEFT JOIN parking_spots ps ON u.id = ps.owner_id";
        
        $params = [':user_id' => $userId];
        
        if ($startDate || $endDate) {
            $query .= " LEFT JOIN bookings b ON ps.id = b.spot_id";
            
            if ($startDate) {
                $query .= " AND b.created_at >= :start_date";
                $params[':start_date'] = $startDate;
            }
            
            if ($endDate) {
                $query .= " AND b.created_at <= :end_date";
                $params[':end_date'] = $endDate;
            }
        } else {
            $query .= " LEFT JOIN bookings b ON ps.id = b.spot_id";
        }
        
        $query .= " LEFT JOIN reviews r ON b.id = r.booking_id
                    WHERE u.id = :user_id
                    GROUP BY u.id";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
