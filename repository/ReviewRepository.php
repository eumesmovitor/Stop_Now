<?php

require_once 'BaseRepository.php';

/**
 * Repository para gerenciar operações de avaliações
 */
class ReviewRepository extends BaseRepository {
    protected $table = 'reviews';
    protected $primaryKey = 'id';

    public function __construct($db) {
        parent::__construct($db);
    }

    /**
     * Busca avaliações por vaga
     */
    public function findBySpot($spotId, $limit = 10) {
        $query = "SELECT r.*, u.name as reviewer_name
                  FROM {$this->table} r
                  JOIN bookings b ON r.booking_id = b.id
                  JOIN users u ON r.reviewer_id = u.id
                  WHERE b.spot_id = :spot_id
                  ORDER BY r.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca avaliações por usuário
     */
    public function findByUser($userId) {
        $query = "SELECT r.*, ps.title as spot_title
                  FROM {$this->table} r
                  JOIN bookings b ON r.booking_id = b.id
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  WHERE r.reviewer_id = :user_id
                  ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Cria uma nova avaliação
     */
    public function createReview($data) {
        $reviewData = [
            'booking_id' => $data['booking_id'],
            'reviewer_id' => $data['reviewer_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($reviewData);
    }

    /**
     * Busca avaliação média de uma vaga
     */
    public function getAverageRating($spotId) {
        $query = "SELECT AVG(r.rating) as avg_rating, COUNT(r.id) as total_reviews
                  FROM {$this->table} r
                  JOIN bookings b ON r.booking_id = b.id
                  WHERE b.spot_id = :spot_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Busca distribuição de avaliações de uma vaga
     */
    public function getRatingDistribution($spotId) {
        $query = "SELECT 
                    rating,
                    COUNT(*) as count
                  FROM {$this->table} r
                  JOIN bookings b ON r.booking_id = b.id
                  WHERE b.spot_id = :spot_id
                  GROUP BY rating
                  ORDER BY rating DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Verifica se usuário pode avaliar uma reserva
     */
    public function canUserReview($userId, $bookingId) {
        return !$this->exists(['booking_id' => $bookingId, 'reviewer_id' => $userId]);
    }

    /**
     * Busca avaliações recentes
     */
    public function getRecentReviews($limit = 10) {
        $query = "SELECT r.*, u.name as reviewer_name, ps.title as spot_title
                  FROM {$this->table} r
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

    /**
     * Busca vagas mais bem avaliadas
     */
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

    /**
     * Busca avaliações com filtros
     */
    public function findWithFilters($filters = [], $limit = 10, $offset = 0) {
        $query = "SELECT r.*, u.name as reviewer_name, ps.title as spot_title,
                  b.start_date, b.end_date
                  FROM {$this->table} r
                  JOIN users u ON r.reviewer_id = u.id
                  JOIN bookings b ON r.booking_id = b.id
                  JOIN parking_spots ps ON b.spot_id = ps.id";
        
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['spot_id'])) {
            $whereClause[] = "b.spot_id = :spot_id";
            $params[':spot_id'] = $filters['spot_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $whereClause[] = "r.reviewer_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['rating_min'])) {
            $whereClause[] = "r.rating >= :rating_min";
            $params[':rating_min'] = $filters['rating_min'];
        }
        
        if (!empty($filters['rating_max'])) {
            $whereClause[] = "r.rating <= :rating_max";
            $params[':rating_max'] = $filters['rating_max'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause[] = "r.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause[] = "r.created_at <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($whereClause)) {
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $query .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        
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
     * Conta avaliações com filtros
     */
    public function countWithFilters($filters = []) {
        $query = "SELECT COUNT(*) as total
                  FROM {$this->table} r
                  JOIN users u ON r.reviewer_id = u.id
                  JOIN bookings b ON r.booking_id = b.id
                  JOIN parking_spots ps ON b.spot_id = ps.id";
        
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['spot_id'])) {
            $whereClause[] = "b.spot_id = :spot_id";
            $params[':spot_id'] = $filters['spot_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $whereClause[] = "r.reviewer_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['rating_min'])) {
            $whereClause[] = "r.rating >= :rating_min";
            $params[':rating_min'] = $filters['rating_min'];
        }
        
        if (!empty($filters['rating_max'])) {
            $whereClause[] = "r.rating <= :rating_max";
            $params[':rating_max'] = $filters['rating_max'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause[] = "r.created_at >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause[] = "r.created_at <= :date_to";
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
     * Busca estatísticas de avaliações
     */
    public function getStats($spotId = null) {
        $query = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as avg_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                  FROM {$this->table} r";
        
        $params = [];
        
        if ($spotId) {
            $query .= " JOIN bookings b ON r.booking_id = b.id WHERE b.spot_id = :spot_id";
            $params[':spot_id'] = $spotId;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Atualiza uma avaliação
     */
    public function updateReview($id, $data) {
        $updateData = [
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($id, $updateData);
    }

    /**
     * Busca avaliação por ID com detalhes
     */
    public function findByIdWithDetails($id) {
        $query = "SELECT r.*, u.name as reviewer_name, ps.title as spot_title,
                  b.start_date, b.end_date
                  FROM {$this->table} r
                  JOIN users u ON r.reviewer_id = u.id
                  JOIN bookings b ON r.booking_id = b.id
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  WHERE r.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
