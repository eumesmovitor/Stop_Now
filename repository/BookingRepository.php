<?php

require_once 'BaseRepository.php';

/**
 * Repository para gerenciar operações de reservas
 */
class BookingRepository extends BaseRepository {
    protected $table = 'bookings';
    protected $primaryKey = 'id';

    public function __construct($db) {
        parent::__construct($db);
    }

    /**
     * Cria uma nova reserva
     */
    public function createBooking($data) {
        $this->beginTransaction();
        
        try {
            // Verifica se a vaga está disponível
            $spotQuery = "SELECT status FROM parking_spots WHERE id = :spot_id";
            $spotStmt = $this->conn->prepare($spotQuery);
            $spotStmt->bindParam(':spot_id', $data['spot_id'], PDO::PARAM_INT);
            $spotStmt->execute();
            $spot = $spotStmt->fetch();
            
            if (!$spot || $spot['status'] !== 'active') {
                $this->rollback();
                return false;
            }
            
            // Cria a reserva
            $bookingData = [
                'spot_id' => $data['spot_id'],
                'renter_id' => $data['renter_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'payment_method' => $data['payment_method'],
                'booking_status' => $data['booking_status'] ?? 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $bookingId = $this->create($bookingData);
            
            if ($bookingId) {
                // Atualiza status da vaga para ocupada
                $updateSpotQuery = "UPDATE parking_spots SET status = 'occupied', updated_at = NOW() WHERE id = :spot_id";
                $updateSpotStmt = $this->conn->prepare($updateSpotQuery);
                $updateSpotStmt->bindParam(':spot_id', $data['spot_id'], PDO::PARAM_INT);
                $updateSpotResult = $updateSpotStmt->execute();
                
                if ($updateSpotResult) {
                    $this->commit();
                    return $bookingId;
                } else {
                    $this->rollback();
                    return false;
                }
            } else {
                $this->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Busca reservas por locatário
     */
    public function findByRenter($renterId) {
        $query = "SELECT b.*, ps.title as spot_title, ps.address as spot_address,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as spot_image,
                  ps.has_smart_lock, DATEDIFF(b.end_date, b.start_date) + 1 as total_days,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1)) as subtotal,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1) * 0.1) as service_fee,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1) * 1.1) as final_price,
                  CONCAT(SUBSTRING(MD5(RAND()), 1, 6)) as access_code
                  FROM {$this->table} b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  WHERE b.renter_id = :renter_id
                  ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':renter_id', $renterId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca reservas por proprietário
     */
    public function findByOwner($ownerId) {
        $query = "SELECT b.*, ps.title as spot_title, ps.address as spot_address,
                  u.name as renter_name, u.email as renter_email,
                  DATEDIFF(b.end_date, b.start_date) + 1 as total_days,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1)) as subtotal,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1) * 1.1) as final_price
                  FROM {$this->table} b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  JOIN users u ON b.renter_id = u.id
                  WHERE ps.owner_id = :owner_id
                  ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $ownerId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Atualiza status da reserva
     */
    public function updateStatus($id, $status) {
        return $this->update($id, [
            'booking_status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Busca reservas por status
     */
    public function findByStatus($status) {
        $query = "SELECT b.*, ps.title as spot_title, ps.address as spot_address,
                  u.name as renter_name, u.email as renter_email
                  FROM {$this->table} b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  JOIN users u ON b.renter_id = u.id
                  WHERE b.booking_status = :status
                  ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca reservas ativas de uma vaga
     */
    public function getActiveBookings($spotId) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE spot_id = :spot_id 
                  AND booking_status IN ('confirmed', 'active') 
                  AND end_date >= CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Completa uma reserva
     */
    public function completeBooking($bookingId) {
        $this->beginTransaction();
        
        try {
            // Busca detalhes da reserva
            $booking = $this->findById($bookingId);
            if (!$booking) {
                $this->rollback();
                return false;
            }
            
            // Atualiza status da reserva para completada
            $updateResult = $this->update($bookingId, [
                'booking_status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($updateResult) {
                // Verifica se há outras reservas ativas para esta vaga
                $activeBookingsQuery = "SELECT COUNT(*) as count FROM {$this->table} 
                                       WHERE spot_id = :spot_id 
                                       AND booking_status IN ('confirmed', 'active') 
                                       AND end_date >= CURDATE()";
                $activeBookingsStmt = $this->conn->prepare($activeBookingsQuery);
                $activeBookingsStmt->bindParam(':spot_id', $booking['spot_id'], PDO::PARAM_INT);
                $activeBookingsStmt->execute();
                $activeBookings = $activeBookingsStmt->fetch();
                
                // Se não há outras reservas ativas, define vaga como ativa
                if ($activeBookings['count'] == 0) {
                    $updateSpotQuery = "UPDATE parking_spots SET status = 'active', updated_at = NOW() WHERE id = :spot_id";
                    $updateSpotStmt = $this->conn->prepare($updateSpotQuery);
                    $updateSpotStmt->bindParam(':spot_id', $booking['spot_id'], PDO::PARAM_INT);
                    $updateSpotResult = $updateSpotStmt->execute();
                    
                    if (!$updateSpotResult) {
                        $this->rollback();
                        return false;
                    }
                }
                
                $this->commit();
                return true;
            } else {
                $this->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Cancela uma reserva
     */
    public function cancelBooking($bookingId) {
        $this->beginTransaction();
        
        try {
            // Busca detalhes da reserva
            $booking = $this->findById($bookingId);
            if (!$booking) {
                $this->rollback();
                return false;
            }
            
            // Atualiza status da reserva para cancelada
            $updateResult = $this->update($bookingId, [
                'booking_status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($updateResult) {
                // Verifica se há outras reservas ativas para esta vaga
                $activeBookingsQuery = "SELECT COUNT(*) as count FROM {$this->table} 
                                       WHERE spot_id = :spot_id 
                                       AND booking_status IN ('confirmed', 'active') 
                                       AND end_date >= CURDATE()";
                $activeBookingsStmt = $this->conn->prepare($activeBookingsQuery);
                $activeBookingsStmt->bindParam(':spot_id', $booking['spot_id'], PDO::PARAM_INT);
                $activeBookingsStmt->execute();
                $activeBookings = $activeBookingsStmt->fetch();
                
                // Se não há outras reservas ativas, define vaga como ativa
                if ($activeBookings['count'] == 0) {
                    $updateSpotQuery = "UPDATE parking_spots SET status = 'active', updated_at = NOW() WHERE id = :spot_id";
                    $updateSpotStmt = $this->conn->prepare($updateSpotQuery);
                    $updateSpotStmt->bindParam(':spot_id', $booking['spot_id'], PDO::PARAM_INT);
                    $updateSpotResult = $updateSpotStmt->execute();
                    
                    if (!$updateSpotResult) {
                        $this->rollback();
                        return false;
                    }
                }
                
                $this->commit();
                return true;
            } else {
                $this->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Busca reservas com filtros e paginação
     */
    public function findWithFilters($filters = [], $limit = 10, $offset = 0) {
        $query = "SELECT b.*, ps.title as spot_title, ps.address as spot_address,
                  u.name as renter_name, u.email as renter_email,
                  DATEDIFF(b.end_date, b.start_date) + 1 as total_days
                  FROM {$this->table} b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  JOIN users u ON b.renter_id = u.id";
        
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['status'])) {
            $whereClause[] = "b.booking_status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['renter_id'])) {
            $whereClause[] = "b.renter_id = :renter_id";
            $params[':renter_id'] = $filters['renter_id'];
        }
        
        if (!empty($filters['owner_id'])) {
            $whereClause[] = "ps.owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause[] = "b.start_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause[] = "b.end_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        if (!empty($whereClause)) {
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $query .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";
        
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
     * Conta reservas com filtros
     */
    public function countWithFilters($filters = []) {
        $query = "SELECT COUNT(*) as total
                  FROM {$this->table} b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  JOIN users u ON b.renter_id = u.id";
        
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['status'])) {
            $whereClause[] = "b.booking_status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['renter_id'])) {
            $whereClause[] = "b.renter_id = :renter_id";
            $params[':renter_id'] = $filters['renter_id'];
        }
        
        if (!empty($filters['owner_id'])) {
            $whereClause[] = "ps.owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause[] = "b.start_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause[] = "b.end_date <= :date_to";
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
     * Busca estatísticas de reservas
     */
    public function getStats($userId = null, $isOwner = false) {
        $query = "SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN booking_status = 'active' THEN 1 ELSE 0 END) as active_bookings,
                    SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
                  FROM {$this->table} b";
        
        $params = [];
        
        if ($userId) {
            if ($isOwner) {
                $query .= " JOIN parking_spots ps ON b.spot_id = ps.id WHERE ps.owner_id = :user_id";
            } else {
                $query .= " WHERE b.renter_id = :user_id";
            }
            $params[':user_id'] = $userId;
        }
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch();
    }
}
