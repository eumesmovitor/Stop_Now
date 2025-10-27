<?php

require_once 'BaseRepository.php';

/**
 * Repository para gerenciar operações de favoritos
 */
class FavoriteRepository extends BaseRepository {
    protected $table = 'favorites';
    protected $primaryKey = 'id';

    public function __construct($db) {
        parent::__construct($db);
    }

    /**
     * Adiciona vaga aos favoritos
     */
    public function addFavorite($userId, $spotId) {
        // Verifica se já existe
        if ($this->exists(['user_id' => $userId, 'spot_id' => $spotId])) {
            return false;
        }

        $data = [
            'user_id' => $userId,
            'spot_id' => $spotId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($data);
    }

    /**
     * Remove vaga dos favoritos
     */
    public function removeFavorite($userId, $spotId) {
        $query = "DELETE FROM {$this->table} 
                  WHERE user_id = :user_id AND spot_id = :spot_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':spot_id', $spotId);
        
        return $stmt->execute();
    }

    /**
     * Verifica se vaga está nos favoritos
     */
    public function isFavorite($userId, $spotId) {
        return $this->exists(['user_id' => $userId, 'spot_id' => $spotId]);
    }

    /**
     * Busca favoritos do usuário
     */
    public function findByUser($userId, $limit = 20, $offset = 0) {
        $query = "SELECT f.*, ps.*, u.name as owner_name, u.is_verified as owner_verified,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                  (SELECT AVG(rating) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as avg_rating
                  FROM {$this->table} f
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

    /**
     * Conta favoritos do usuário
     */
    public function countByUser($userId) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  WHERE f.user_id = :user_id AND ps.status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Alterna status de favorito
     */
    public function toggleFavorite($userId, $spotId) {
        if ($this->isFavorite($userId, $spotId)) {
            return $this->removeFavorite($userId, $spotId) ? 'removed' : false;
        } else {
            return $this->addFavorite($userId, $spotId) ? 'added' : false;
        }
    }

    /**
     * Busca favoritos com filtros
     */
    public function findWithFilters($userId, $filters = [], $limit = 20, $offset = 0) {
        $query = "SELECT f.*, ps.*, u.name as owner_name, u.is_verified as owner_verified,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                  (SELECT AVG(rating) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as avg_rating
                  FROM {$this->table} f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  JOIN users u ON ps.owner_id = u.id
                  WHERE f.user_id = :user_id AND ps.status = 'active'";
        
        $params = [':user_id' => $userId];
        
        if (!empty($filters['city'])) {
            $query .= " AND ps.city LIKE :city";
            $params[':city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['spot_type'])) {
            $query .= " AND ps.spot_type = :spot_type";
            $params[':spot_type'] = $filters['spot_type'];
        }
        
        if (!empty($filters['price_min'])) {
            $query .= " AND ps.price_daily >= :price_min";
            $params[':price_min'] = $filters['price_min'];
        }
        
        if (!empty($filters['price_max'])) {
            $query .= " AND ps.price_daily <= :price_max";
            $params[':price_max'] = $filters['price_max'];
        }
        
        if (!empty($filters['features'])) {
            foreach ($filters['features'] as $feature) {
                switch ($feature) {
                    case 'covered':
                        $query .= " AND ps.is_covered = 1";
                        break;
                    case 'security':
                        $query .= " AND ps.has_security = 1";
                        break;
                    case 'camera':
                        $query .= " AND ps.has_camera = 1";
                        break;
                    case 'lighting':
                        $query .= " AND ps.has_lighting = 1";
                        break;
                    case 'electric_charging':
                        $query .= " AND ps.has_electric_charging = 1";
                        break;
                    case 'smart_lock':
                        $query .= " AND ps.has_smart_lock = 1";
                        break;
                }
            }
        }
        
        // Ordenação
        switch($filters['sort'] ?? '') {
            case 'price':
                $query .= " ORDER BY ps.price_daily ASC";
                break;
            case 'rating':
                $query .= " ORDER BY avg_rating DESC";
                break;
            case 'date':
                $query .= " ORDER BY f.created_at DESC";
                break;
            default:
                $query .= " ORDER BY f.created_at DESC";
        }
        
        $query .= " LIMIT :limit OFFSET :offset";
        
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
     * Conta favoritos com filtros
     */
    public function countWithFilters($userId, $filters = []) {
        $query = "SELECT COUNT(*) as total
                  FROM {$this->table} f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  JOIN users u ON ps.owner_id = u.id
                  WHERE f.user_id = :user_id AND ps.status = 'active'";
        
        $params = [':user_id' => $userId];
        
        if (!empty($filters['city'])) {
            $query .= " AND ps.city LIKE :city";
            $params[':city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['spot_type'])) {
            $query .= " AND ps.spot_type = :spot_type";
            $params[':spot_type'] = $filters['spot_type'];
        }
        
        if (!empty($filters['price_min'])) {
            $query .= " AND ps.price_daily >= :price_min";
            $params[':price_min'] = $filters['price_min'];
        }
        
        if (!empty($filters['price_max'])) {
            $query .= " AND ps.price_daily <= :price_max";
            $params[':price_max'] = $filters['price_max'];
        }
        
        if (!empty($filters['features'])) {
            foreach ($filters['features'] as $feature) {
                switch ($feature) {
                    case 'covered':
                        $query .= " AND ps.is_covered = 1";
                        break;
                    case 'security':
                        $query .= " AND ps.has_security = 1";
                        break;
                    case 'camera':
                        $query .= " AND ps.has_camera = 1";
                        break;
                    case 'lighting':
                        $query .= " AND ps.has_lighting = 1";
                        break;
                    case 'electric_charging':
                        $query .= " AND ps.has_electric_charging = 1";
                        break;
                    case 'smart_lock':
                        $query .= " AND ps.has_smart_lock = 1";
                        break;
                }
            }
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
     * Remove todos os favoritos de um usuário
     */
    public function removeAllByUser($userId) {
        $query = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        
        return $stmt->execute();
    }

    /**
     * Remove favoritos de vagas inativas
     */
    public function removeInactiveSpots() {
        $query = "DELETE f FROM {$this->table} f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  WHERE ps.status != 'active'";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    /**
     * Busca estatísticas de favoritos
     */
    public function getStats($userId = null) {
        $query = "SELECT 
                    COUNT(*) as total_favorites,
                    COUNT(DISTINCT f.user_id) as users_with_favorites,
                    COUNT(DISTINCT f.spot_id) as unique_spots_favorited
                  FROM {$this->table} f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  WHERE ps.status = 'active'";
        
        $params = [];
        
        if ($userId) {
            $query .= " AND f.user_id = :user_id";
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
     * Busca favoritos recentes
     */
    public function getRecentFavorites($limit = 10) {
        $query = "SELECT f.*, ps.title as spot_title, u.name as user_name
                  FROM {$this->table} f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  JOIN users u ON f.user_id = u.id
                  WHERE ps.status = 'active'
                  ORDER BY f.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
