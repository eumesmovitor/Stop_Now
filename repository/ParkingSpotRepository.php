<?php

require_once 'BaseRepository.php';

/**
 * Repository para gerenciar operações de vagas de estacionamento
 */
class ParkingSpotRepository extends BaseRepository {
    protected $table = 'parking_spots';
    protected $primaryKey = 'id';

    public function __construct($db) {
        parent::__construct($db);
    }

    /**
     * Busca todas as vagas com informações do proprietário
     */
    public function getAllWithOwner($limit = 10, $offset = 0) {
        $query = "SELECT ps.*, u.name as owner_name, u.is_verified as owner_verified,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                  (SELECT AVG(rating) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as avg_rating
                  FROM {$this->table} ps
                  JOIN users u ON ps.owner_id = u.id
                  WHERE ps.status = 'active'
                  ORDER BY ps.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca vaga por ID com informações do proprietário
     */
    public function findByIdWithOwner($id) {
        $query = "SELECT ps.*, u.name as owner_name, u.email as owner_email, 
                  u.phone as owner_phone, u.is_verified as owner_verified,
                  (SELECT AVG(rating) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as avg_rating,
                  (SELECT COUNT(*) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as review_count
                  FROM {$this->table} ps
                  JOIN users u ON ps.owner_id = u.id
                  WHERE ps.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Busca imagens de uma vaga
     */
    public function getImages($spotId) {
        $query = "SELECT * FROM spot_images WHERE spot_id = :spot_id ORDER BY is_primary DESC, display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca vagas por proprietário
     */
    public function findByOwner($ownerId) {
        $query = "SELECT ps.*,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                  (SELECT COUNT(*) FROM bookings WHERE spot_id = ps.id) as total_bookings
                  FROM {$this->table} ps
                  WHERE ps.owner_id = :owner_id
                  ORDER BY ps.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $ownerId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Cria uma nova vaga
     */
    public function createSpot($data) {
        $spotData = [
            'owner_id' => $data['owner_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip_code' => $data['zip_code'],
            'price_daily' => $data['price_daily'],
            'price_weekly' => $data['price_weekly'],
            'price_monthly' => $data['price_monthly'],
            'price_annual' => $data['price_annual'],
            'spot_type' => $data['spot_type'],
            'is_covered' => $data['is_covered'],
            'has_security' => $data['has_security'],
            'has_camera' => $data['has_camera'],
            'has_lighting' => $data['has_lighting'],
            'has_electric_charging' => $data['has_electric_charging'],
            'has_smart_lock' => $data['has_smart_lock'],
            'smart_lock_type' => $data['smart_lock_type'],
            'max_height' => $data['max_height'],
            'max_width' => $data['max_width'],
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($spotData);
    }

    /**
     * Adiciona imagem à vaga
     */
    public function addImage($spotId, $imageUrl, $isPrimary = false, $order = 0) {
        $query = "INSERT INTO spot_images (spot_id, image_url, is_primary, display_order) 
                  VALUES (:spot_id, :image_url, :is_primary, :display_order)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->bindParam(':image_url', $imageUrl);
        $stmt->bindParam(':is_primary', $isPrimary, PDO::PARAM_BOOL);
        $stmt->bindParam(':display_order', $order);
        
        return $stmt->execute();
    }

    /**
     * Busca vagas com filtros avançados
     */
    public function search($filters = []) {
        $query = "SELECT ps.*, u.name as owner_name, u.is_verified as owner_verified,
                (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                COALESCE((SELECT AVG(rating) FROM reviews r 
                 JOIN bookings b ON r.booking_id = b.id 
                 WHERE b.spot_id = ps.id), 0) as avg_rating
                FROM {$this->table} ps
                JOIN users u ON ps.owner_id = u.id
                WHERE ps.status = 'active'";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $query .= " AND (ps.title LIKE :search OR ps.description LIKE :search OR ps.address LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['city'])) {
            $query .= " AND ps.city LIKE :city";
            $params[':city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['state'])) {
            $query .= " AND ps.state = :state";
            $params[':state'] = $filters['state'];
        }
        
        if (!empty($filters['price_min'])) {
            $query .= " AND ps.price_daily >= :price_min";
            $params[':price_min'] = floatval($filters['price_min']);
        }
        
        if (!empty($filters['price_max'])) {
            $query .= " AND ps.price_daily <= :price_max";
            $params[':price_max'] = floatval($filters['price_max']);
        }
        
        if (!empty($filters['spot_type'])) {
            $query .= " AND ps.spot_type = :spot_type";
            $params[':spot_type'] = $filters['spot_type'];
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
                $query .= " ORDER BY ps.created_at DESC";
                break;
            default:
                $query .= " ORDER BY ps.created_at DESC";
        }
        
        // Paginação
        if (!empty($filters['limit'])) {
            $query .= " LIMIT :limit";
            $params[':limit'] = intval($filters['limit']);
            
            if (!empty($filters['offset'])) {
                $query .= " OFFSET :offset";
                $params[':offset'] = intval($filters['offset']);
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Conta resultados da busca
     */
    public function countSearchResults($filters = []) {
        $query = "SELECT COUNT(*) as total
                FROM {$this->table} ps
                JOIN users u ON ps.owner_id = u.id
                WHERE ps.status = 'active'";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $query .= " AND (ps.title LIKE :search OR ps.description LIKE :search OR ps.address LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['city'])) {
            $query .= " AND ps.city LIKE :city";
            $params[':city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['state'])) {
            $query .= " AND ps.state = :state";
            $params[':state'] = $filters['state'];
        }
        
        if (!empty($filters['price_min'])) {
            $query .= " AND ps.price_daily >= :price_min";
            $params[':price_min'] = floatval($filters['price_min']);
        }
        
        if (!empty($filters['price_max'])) {
            $query .= " AND ps.price_daily <= :price_max";
            $params[':price_max'] = floatval($filters['price_max']);
        }
        
        if (!empty($filters['spot_type'])) {
            $query .= " AND ps.spot_type = :spot_type";
            $params[':spot_type'] = $filters['spot_type'];
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
     * Busca cidades disponíveis
     */
    public function getCities() {
        $query = "SELECT DISTINCT city FROM {$this->table} WHERE status = 'active' ORDER BY city ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Busca estados disponíveis
     */
    public function getStates() {
        $query = "SELECT DISTINCT state FROM {$this->table} WHERE status = 'active' ORDER BY state ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Verifica disponibilidade da vaga
     */
    public function isAvailable($spotId, $startDate, $endDate) {
        // Verifica se a vaga existe e está ativa
        $spotQuery = "SELECT status FROM {$this->table} WHERE id = :spot_id";
        $spotStmt = $this->conn->prepare($spotQuery);
        $spotStmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $spotStmt->execute();
        $spot = $spotStmt->fetch();
        
        if (!$spot || $spot['status'] !== 'active') {
            return false;
        }
        
        // Verifica reservas sobrepostas
        $bookingQuery = "SELECT COUNT(*) as count FROM bookings 
                         WHERE spot_id = :spot_id 
                         AND booking_status IN ('confirmed', 'active')
                         AND NOT (
                             end_date < :start_date OR 
                             start_date > :end_date
                         )";
        
        $bookingStmt = $this->conn->prepare($bookingQuery);
        $bookingStmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $bookingStmt->bindParam(':start_date', $startDate);
        $bookingStmt->bindParam(':end_date', $endDate);
        $bookingStmt->execute();
        $overlapping = $bookingStmt->fetch();
        
        return $overlapping['count'] == 0;
    }

    /**
     * Busca datas indisponíveis
     */
    public function getUnavailableDates($spotId, $startDate = null, $endDate = null) {
        if (!$startDate) {
            $startDate = date('Y-m-d');
        }
        if (!$endDate) {
            $endDate = date('Y-m-d', strtotime('+3 months'));
        }
        
        $query = "SELECT start_date, end_date FROM bookings 
                  WHERE spot_id = :spot_id 
                  AND booking_status IN ('confirmed', 'active')
                  AND NOT (end_date < :range_start OR start_date > :range_end)
                  ORDER BY start_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->bindParam(':range_start', $startDate);
        $stmt->bindParam(':range_end', $endDate);
        $stmt->execute();
        
        $bookings = $stmt->fetchAll();
        $unavailableDates = [];
        
        foreach ($bookings as $booking) {
            $current = strtotime($booking['start_date']);
            $end = strtotime($booking['end_date']);
            
            while ($current <= $end) {
                $unavailableDates[] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
            }
        }
        
        return array_unique($unavailableDates);
    }

    /**
     * Remove imagem da vaga
     */
    public function removeImage($imageId) {
        // Busca informações da imagem antes de deletar
        $query = "SELECT image_url FROM spot_images WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
        $stmt->execute();
        $image = $stmt->fetch();
        
        if ($image) {
            // Remove do banco de dados
            $query = "DELETE FROM spot_images WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            // Remove arquivo físico
            if ($result && file_exists($image['image_url'])) {
                unlink($image['image_url']);
            }
            
            return $result;
        }
        
        return false;
    }

    /**
     * Atualiza vaga
     */
    public function updateSpot($id, $data) {
        $updateData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip_code' => $data['zip_code'],
            'price_daily' => $data['price_daily'],
            'price_weekly' => $data['price_weekly'],
            'price_monthly' => $data['price_monthly'],
            'price_annual' => $data['price_annual'],
            'spot_type' => $data['spot_type'],
            'is_covered' => $data['is_covered'],
            'has_security' => $data['has_security'],
            'has_camera' => $data['has_camera'],
            'has_lighting' => $data['has_lighting'],
            'has_electric_charging' => $data['has_electric_charging'],
            'has_smart_lock' => $data['has_smart_lock'],
            'smart_lock_type' => $data['smart_lock_type'],
            'max_height' => $data['max_height'],
            'max_width' => $data['max_width'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($id, $updateData);
    }

    /**
     * Remove vaga e suas imagens
     */
    public function deleteSpot($id) {
        // Remove imagens associadas
        $this->deleteImages($id);
        
        // Remove a vaga
        return $this->delete($id);
    }

    /**
     * Remove imagens de uma vaga
     */
    private function deleteImages($spotId) {
        // Busca todas as imagens da vaga
        $query = "SELECT image_url FROM spot_images WHERE spot_id = :spot_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->execute();
        $images = $stmt->fetchAll();
        
        // Remove arquivos físicos
        foreach ($images as $image) {
            if (file_exists($image['image_url'])) {
                unlink($image['image_url']);
            }
        }
        
        // Remove do banco de dados
        $query = "DELETE FROM spot_images WHERE spot_id = :spot_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Busca vagas para mapa
     */
    public function getAllForMap() {
        $query = "SELECT ps.id, ps.title, ps.address, ps.city, ps.state, ps.latitude, ps.longitude, 
                  ps.price_daily, ps.spot_type, ps.is_covered,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image
                  FROM {$this->table} ps
                  WHERE ps.status = 'active' AND ps.latitude IS NOT NULL AND ps.longitude IS NOT NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
