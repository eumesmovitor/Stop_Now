<?php
class ParkingSpot {
    private $conn;
    private $table = 'parking_spots';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = 10, $offset = 0) {
        $query = "SELECT ps.*, u.name as owner_name, u.is_verified as owner_verified,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                  (SELECT AVG(rating) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as avg_rating
                  FROM " . $this->table . " ps
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

    public function getById($id) {
        $query = "SELECT ps.*, u.name as owner_name, u.email as owner_email, 
                  u.phone as owner_phone, u.is_verified as owner_verified,
                  (SELECT AVG(rating) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as avg_rating,
                  (SELECT COUNT(*) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as review_count
                  FROM " . $this->table . " ps
                  JOIN users u ON ps.owner_id = u.id
                  WHERE ps.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function getImages($spotId) {
        $query = "SELECT * FROM spot_images WHERE spot_id = :spot_id ORDER BY is_primary DESC, display_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getByOwner($ownerId) {
        $query = "SELECT ps.*,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                  (SELECT COUNT(*) FROM bookings WHERE spot_id = ps.id) as total_bookings
                  FROM " . $this->table . " ps
                  WHERE ps.owner_id = :owner_id
                  ORDER BY ps.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $ownerId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (owner_id, title, description, address, city, state, zip_code, 
                   price_daily, price_weekly, price_monthly, price_annual, spot_type, is_covered, 
                   has_security, has_camera, has_lighting, has_electric_charging, 
                   has_smart_lock, smart_lock_type, max_height, max_width)
                  VALUES 
                  (:owner_id, :title, :description, :address, :city, :state, :zip_code,
                   :price_daily, :price_weekly, :price_monthly, :price_annual, :spot_type, :is_covered,
                   :has_security, :has_camera, :has_lighting, :has_electric_charging,
                   :has_smart_lock, :smart_lock_type, :max_height, :max_width)";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

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

    public function search($query = '', $city = '', $priceMin = 0, $priceMax = 0, $spotType = '', $features = [], $sortBy = '', $limit = 0, $offset = 0) {
        // Ensure features is always an array
        if (!is_array($features)) {
            $features = [];
        }
        
        // Ensure numeric values
        $priceMin = floatval($priceMin);
        $priceMax = floatval($priceMax);
        $limit = intval($limit);
        $offset = intval($offset);
        $sql = "SELECT ps.*, u.name as owner_name, u.is_verified as owner_verified,
                (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                COALESCE((SELECT AVG(rating) FROM reviews r 
                 JOIN bookings b ON r.booking_id = b.id 
                 WHERE b.spot_id = ps.id), 0) as avg_rating
                FROM " . $this->table . " ps
                JOIN users u ON ps.owner_id = u.id
                WHERE ps.status = 'active'";
        
        $params = [];
        
        if (!empty($query)) {
            $sql .= " AND (ps.title LIKE :query OR ps.description LIKE :query OR ps.address LIKE :query)";
            $params[':query'] = "%{$query}%";
        }
        
        if (!empty($city)) {
            $sql .= " AND ps.city LIKE :city";
            $params[':city'] = "%{$city}%";
        }
        
        if ($priceMin > 0) {
            $sql .= " AND ps.price_daily >= :price_min";
            $params[':price_min'] = $priceMin;
        }
        
        if ($priceMax > 0) {
            $sql .= " AND ps.price_daily <= :price_max";
            $params[':price_max'] = $priceMax;
        }
        
        if (!empty($spotType)) {
            $sql .= " AND ps.spot_type = :spot_type";
            $params[':spot_type'] = $spotType;
        }
        
        if (!empty($features)) {
            foreach ($features as $feature) {
                switch ($feature) {
                    case 'covered':
                        $sql .= " AND ps.is_covered = 1";
                        break;
                    case 'security':
                        $sql .= " AND ps.has_security = 1";
                        break;
                    case 'camera':
                        $sql .= " AND ps.has_camera = 1";
                        break;
                    case 'lighting':
                        $sql .= " AND ps.has_lighting = 1";
                        break;
                    case 'electric_charging':
                        $sql .= " AND ps.has_electric_charging = 1";
                        break;
                    case 'smart_lock':
                        $sql .= " AND ps.has_smart_lock = 1";
                        break;
                }
            }
        }
        
        // Add sorting
        switch($sortBy) {
            case 'price':
                $sql .= " ORDER BY ps.price_daily ASC";
                break;
            case 'rating':
                $sql .= " ORDER BY avg_rating DESC";
                break;
            case 'date':
                $sql .= " ORDER BY ps.created_at DESC";
                break;
            default:
                $sql .= " ORDER BY ps.created_at DESC";
        }
        
        // Add pagination
        if ($limit > 0) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset > 0) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log("SQL Error in search method: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            throw $e;
        }
        
        return $stmt->fetchAll();
    }

    public function getSearchCount($query = '', $city = '', $priceMin = 0, $priceMax = 0, $spotType = '', $features = []) {
        // Ensure features is always an array
        if (!is_array($features)) {
            $features = [];
        }
        
        // Ensure numeric values
        $priceMin = floatval($priceMin);
        $priceMax = floatval($priceMax);
        $sql = "SELECT COUNT(*) as total
                FROM " . $this->table . " ps
                JOIN users u ON ps.owner_id = u.id
                WHERE ps.status = 'active'";
        
        $params = [];
        
        if (!empty($query)) {
            $sql .= " AND (ps.title LIKE :query OR ps.description LIKE :query OR ps.address LIKE :query)";
            $params[':query'] = "%{$query}%";
        }
        
        if (!empty($city)) {
            $sql .= " AND ps.city LIKE :city";
            $params[':city'] = "%{$city}%";
        }
        
        if ($priceMin > 0) {
            $sql .= " AND ps.price_daily >= :price_min";
            $params[':price_min'] = $priceMin;
        }
        
        if ($priceMax > 0) {
            $sql .= " AND ps.price_daily <= :price_max";
            $params[':price_max'] = $priceMax;
        }
        
        if (!empty($spotType)) {
            $sql .= " AND ps.spot_type = :spot_type";
            $params[':spot_type'] = $spotType;
        }
        
        if (!empty($features)) {
            foreach ($features as $feature) {
                switch ($feature) {
                    case 'covered':
                        $sql .= " AND ps.is_covered = 1";
                        break;
                    case 'security':
                        $sql .= " AND ps.has_security = 1";
                        break;
                    case 'camera':
                        $sql .= " AND ps.has_camera = 1";
                        break;
                    case 'lighting':
                        $sql .= " AND ps.has_lighting = 1";
                        break;
                    case 'electric_charging':
                        $sql .= " AND ps.has_electric_charging = 1";
                        break;
                    case 'smart_lock':
                        $sql .= " AND ps.has_smart_lock = 1";
                        break;
                }
            }
        }
        
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function searchByCity($city) {
        $query = "SELECT ps.*, u.name as owner_name, u.is_verified as owner_verified,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                  (SELECT AVG(rating) FROM reviews r 
                   JOIN bookings b ON r.booking_id = b.id 
                   WHERE b.spot_id = ps.id) as avg_rating
                  FROM " . $this->table . " ps
                  JOIN users u ON ps.owner_id = u.id
                  WHERE ps.status = 'active' AND ps.city LIKE :city
                  ORDER BY ps.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':city', "%{$city}%");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function filter($filters) {
        $sql = "SELECT ps.*, u.name as owner_name, u.is_verified as owner_verified,
                (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews r 
                 JOIN bookings b ON r.booking_id = b.id 
                 WHERE b.spot_id = ps.id) as avg_rating
                FROM " . $this->table . " ps
                JOIN users u ON ps.owner_id = u.id
                WHERE ps.status = 'active'";
        
        $params = [];
        
        foreach ($filters as $key => $value) {
            if (empty($value)) continue;
            
            switch ($key) {
                case 'city':
                    $sql .= " AND ps.city LIKE :city";
                    $params[':city'] = "%{$value}%";
                    break;
                case 'price_min':
                    $sql .= " AND ps.price_daily >= :price_min";
                    $params[':price_min'] = floatval($value);
                    break;
                case 'price_max':
                    $sql .= " AND ps.price_daily <= :price_max";
                    $params[':price_max'] = floatval($value);
                    break;
                case 'spot_type':
                    $sql .= " AND ps.spot_type = :spot_type";
                    $params[':spot_type'] = $value;
                    break;
            }
        }
        
        $sql .= " ORDER BY ps.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getCities() {
        $query = "SELECT DISTINCT city FROM " . $this->table . " WHERE status = 'active' ORDER BY city ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function searchSuggestions($query, $limit = 10) {
        $sql = "SELECT DISTINCT title FROM " . $this->table . " 
                WHERE status = 'active' AND title LIKE :query 
                ORDER BY 
                    CASE WHEN title LIKE :exact THEN 1 ELSE 2 END,
                    LENGTH(title),
                    title
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->bindValue(':exact', "{$query}%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET 
                  title = :title, description = :description, address = :address, 
                  city = :city, state = :state, zip_code = :zip_code,
                  price_daily = :price_daily, price_weekly = :price_weekly, 
                  price_monthly = :price_monthly, price_annual = :price_annual,
                  spot_type = :spot_type, is_covered = :is_covered,
                  has_security = :has_security, has_camera = :has_camera,
                  has_lighting = :has_lighting, has_electric_charging = :has_electric_charging,
                  has_smart_lock = :has_smart_lock, smart_lock_type = :smart_lock_type,
                  max_height = :max_height, max_width = :max_width,
                  updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        foreach($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        // First, delete associated images
        $this->deleteImages($id);
        
        // Then delete the spot
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    public function removeImage($imageId) {
        // Get image info before deletion
        $query = "SELECT image_url FROM spot_images WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
        $stmt->execute();
        $image = $stmt->fetch();
        
        if ($image) {
            // Delete from database
            $query = "DELETE FROM spot_images WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            // Delete physical file
            if ($result && file_exists($image['image_url'])) {
                unlink($image['image_url']);
            }
            
            return $result;
        }
        
        return false;
    }
    
    public function getActiveBookings($spotId) {
        $query = "SELECT * FROM bookings 
                  WHERE spot_id = :spot_id 
                  AND booking_status IN ('confirmed', 'active') 
                  AND end_date >= CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    private function deleteImages($spotId) {
        // Get all images for this spot
        $query = "SELECT image_url FROM spot_images WHERE spot_id = :spot_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->execute();
        $images = $stmt->fetchAll();
        
        // Delete physical files
        foreach ($images as $image) {
            if (file_exists($image['image_url'])) {
                unlink($image['image_url']);
            }
        }
        
        // Delete from database
        $query = "DELETE FROM spot_images WHERE spot_id = :spot_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    public function getStates() {
        $query = "SELECT DISTINCT state FROM " . $this->table . " WHERE status = 'active' ORDER BY state ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function advancedSearch($filters) {
        $sql = "SELECT ps.*, u.name as owner_name, u.is_verified as owner_verified,
                (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image,
                COALESCE((SELECT AVG(rating) FROM reviews r 
                 JOIN bookings b ON r.booking_id = b.id 
                 WHERE b.spot_id = ps.id), 0) as avg_rating
                FROM " . $this->table . " ps
                JOIN users u ON ps.owner_id = u.id
                WHERE ps.status = 'active'";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (ps.title LIKE :search OR ps.description LIKE :search OR ps.address LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['city'])) {
            $sql .= " AND ps.city LIKE :city";
            $params[':city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['state'])) {
            $sql .= " AND ps.state = :state";
            $params[':state'] = $filters['state'];
        }
        
        if (!empty($filters['price_min'])) {
            $sql .= " AND ps.price_daily >= :price_min";
            $params[':price_min'] = floatval($filters['price_min']);
        }
        
        if (!empty($filters['price_max'])) {
            $sql .= " AND ps.price_daily <= :price_max";
            $params[':price_max'] = floatval($filters['price_max']);
        }
        
        if (!empty($filters['spot_type'])) {
            $sql .= " AND ps.spot_type = :spot_type";
            $params[':spot_type'] = $filters['spot_type'];
        }
        
        if (!empty($filters['features'])) {
            foreach ($filters['features'] as $feature) {
                switch ($feature) {
                    case 'covered':
                        $sql .= " AND ps.is_covered = 1";
                        break;
                    case 'security':
                        $sql .= " AND ps.has_security = 1";
                        break;
                    case 'camera':
                        $sql .= " AND ps.has_camera = 1";
                        break;
                    case 'lighting':
                        $sql .= " AND ps.has_lighting = 1";
                        break;
                    case 'electric_charging':
                        $sql .= " AND ps.has_electric_charging = 1";
                        break;
                    case 'smart_lock':
                        $sql .= " AND ps.has_smart_lock = 1";
                        break;
                }
            }
        }
        
        // Add sorting
        switch($filters['sort'] ?? '') {
            case 'price':
                $sql .= " ORDER BY ps.price_daily ASC";
                break;
            case 'rating':
                $sql .= " ORDER BY avg_rating DESC";
                break;
            case 'date':
                $sql .= " ORDER BY ps.created_at DESC";
                break;
            default:
                $sql .= " ORDER BY ps.created_at DESC";
        }
        
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getAllForMap() {
        $query = "SELECT ps.id, ps.title, ps.address, ps.city, ps.state, ps.latitude, ps.longitude, 
                  ps.price_daily, ps.spot_type, ps.is_covered,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image
                  FROM " . $this->table . " ps
                  WHERE ps.status = 'active' AND ps.latitude IS NOT NULL AND ps.longitude IS NOT NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getAutocompleteSpots($query) {
        $sql = "SELECT id, title, address, city, state, price_daily,
                (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM " . $this->table . " ps
                WHERE status = 'active' AND (title LIKE :query OR address LIKE :query OR city LIKE :query)
                ORDER BY 
                    CASE WHEN title LIKE :exact THEN 1 ELSE 2 END,
                    LENGTH(title),
                    title
                LIMIT 5";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->bindValue(':exact', "{$query}%");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getAutocompleteCities($query) {
        $sql = "SELECT DISTINCT city, state FROM " . $this->table . " 
                WHERE status = 'active' AND city LIKE :query 
                ORDER BY 
                    CASE WHEN city LIKE :exact THEN 1 ELSE 2 END,
                    LENGTH(city),
                    city
                LIMIT 5";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
        $stmt->bindValue(':exact', "{$query}%");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function isAvailable($spotId, $startDate, $endDate) {
        // Check if spot exists and is active
        $spotQuery = "SELECT status FROM " . $this->table . " WHERE id = :spot_id";
        $spotStmt = $this->conn->prepare($spotQuery);
        $spotStmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $spotStmt->execute();
        $spot = $spotStmt->fetch();
        
        if (!$spot || $spot['status'] !== 'active') {
            return false;
        }
        
        // Check for any overlapping bookings (more strict validation)
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
    
    public function getUnavailableDates($spotId, $startDate = null, $endDate = null) {
        // If no date range provided, get next 3 months
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
    
    public function checkDateAvailability($spotId, $date) {
        $query = "SELECT COUNT(*) as count FROM bookings 
                  WHERE spot_id = :spot_id 
                  AND booking_status IN ('confirmed', 'active')
                  AND :date BETWEEN start_date AND end_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] == 0;
    }
    
    public function getAvailabilityStatus($spotId) {
        $query = "SELECT ps.status, 
                         COUNT(b.id) as active_bookings,
                         MAX(b.end_date) as last_booking_end
                  FROM " . $this->table . " ps
                  LEFT JOIN bookings b ON ps.id = b.spot_id 
                      AND b.booking_status IN ('confirmed', 'active')
                      AND b.end_date >= CURDATE()
                  WHERE ps.id = :spot_id
                  GROUP BY ps.id, ps.status";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>
