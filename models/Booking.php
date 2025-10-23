<?php
class Booking {
    private $conn;
    private $table = 'bookings';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Check if spot is available
            $spotQuery = "SELECT status FROM parking_spots WHERE id = :spot_id";
            $spotStmt = $this->conn->prepare($spotQuery);
            $spotStmt->bindParam(':spot_id', $data['spot_id'], PDO::PARAM_INT);
            $spotStmt->execute();
            $spot = $spotStmt->fetch();
            
            if (!$spot || $spot['status'] !== 'active') {
                $this->conn->rollBack();
                return false;
            }
            
            // Create booking
            $query = "INSERT INTO " . $this->table . " 
                      (spot_id, renter_id, start_date, end_date, payment_method, booking_status) 
                      VALUES (:spot_id, :renter_id, :start_date, :end_date, :payment_method, :booking_status)";
            
            $stmt = $this->conn->prepare($query);
            
            // Map 'status' to 'booking_status' if it exists in the data
            if (isset($data['status'])) {
                $data['booking_status'] = $data['status'];
                unset($data['status']);
            }
            
            foreach($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $bookingResult = $stmt->execute();
            
            if ($bookingResult) {
                // Update spot status to occupied
                $updateSpotQuery = "UPDATE parking_spots SET status = 'occupied', updated_at = NOW() WHERE id = :spot_id";
                $updateSpotStmt = $this->conn->prepare($updateSpotQuery);
                $updateSpotStmt->bindParam(':spot_id', $data['spot_id'], PDO::PARAM_INT);
                $updateSpotResult = $updateSpotStmt->execute();
                
                if ($updateSpotResult) {
                    $this->conn->commit();
                    return true;
                } else {
                    $this->conn->rollBack();
                    return false;
                }
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function getByRenter($renterId) {
        $query = "SELECT b.*, ps.title as spot_title, ps.address as spot_address,
                  (SELECT image_url FROM spot_images WHERE spot_id = ps.id AND is_primary = 1 LIMIT 1) as spot_image,
                  ps.has_smart_lock, DATEDIFF(b.end_date, b.start_date) + 1 as total_days,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1)) as subtotal,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1) * 0.1) as service_fee,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1) * 1.1) as final_price,
                  CONCAT(SUBSTRING(MD5(RAND()), 1, 6)) as access_code
                  FROM " . $this->table . " b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  WHERE b.renter_id = :renter_id
                  ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':renter_id', $renterId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getByOwner($ownerId) {
        $query = "SELECT b.*, ps.title as spot_title, ps.address as spot_address,
                  u.name as renter_name, u.email as renter_email,
                  DATEDIFF(b.end_date, b.start_date) + 1 as total_days,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1)) as subtotal,
                  (ps.price_daily * (DATEDIFF(b.end_date, b.start_date) + 1) * 1.1) as final_price
                  FROM " . $this->table . " b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  JOIN users u ON b.renter_id = u.id
                  WHERE ps.owner_id = :owner_id
                  ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':owner_id', $ownerId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET booking_status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }
    
    public function getByStatus($status) {
        $query = "SELECT b.*, ps.title as spot_title, ps.address as spot_address,
                  u.name as renter_name, u.email as renter_email
                  FROM " . $this->table . " b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  JOIN users u ON b.renter_id = u.id
                  WHERE b.booking_status = :status
                  ORDER BY b.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getActiveBookings($spotId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE spot_id = :spot_id 
                  AND booking_status IN ('confirmed', 'active') 
                  AND end_date >= CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':spot_id', $spotId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function completeBooking($bookingId) {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Get booking details
            $booking = $this->getById($bookingId);
            if (!$booking) {
                $this->conn->rollBack();
                return false;
            }
            
            // Update booking status to completed
            $updateBookingQuery = "UPDATE " . $this->table . " SET booking_status = 'completed', updated_at = NOW() WHERE id = :booking_id";
            $updateBookingStmt = $this->conn->prepare($updateBookingQuery);
            $updateBookingStmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
            $updateBookingResult = $updateBookingStmt->execute();
            
            if ($updateBookingResult) {
                // Check if there are other active bookings for this spot
                $activeBookingsQuery = "SELECT COUNT(*) as count FROM " . $this->table . " 
                                       WHERE spot_id = :spot_id 
                                       AND booking_status IN ('confirmed', 'active') 
                                       AND end_date >= CURDATE()";
                $activeBookingsStmt = $this->conn->prepare($activeBookingsQuery);
                $activeBookingsStmt->bindParam(':spot_id', $booking['spot_id'], PDO::PARAM_INT);
                $activeBookingsStmt->execute();
                $activeBookings = $activeBookingsStmt->fetch();
                
                // If no other active bookings, set spot status back to active
                if ($activeBookings['count'] == 0) {
                    $updateSpotQuery = "UPDATE parking_spots SET status = 'active', updated_at = NOW() WHERE id = :spot_id";
                    $updateSpotStmt = $this->conn->prepare($updateSpotQuery);
                    $updateSpotStmt->bindParam(':spot_id', $booking['spot_id'], PDO::PARAM_INT);
                    $updateSpotResult = $updateSpotStmt->execute();
                    
                    if (!$updateSpotResult) {
                        $this->conn->rollBack();
                        return false;
                    }
                }
                
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    public function cancelBooking($bookingId) {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Get booking details
            $booking = $this->getById($bookingId);
            if (!$booking) {
                $this->conn->rollBack();
                return false;
            }
            
            // Update booking status to cancelled
            $updateBookingQuery = "UPDATE " . $this->table . " SET booking_status = 'cancelled', updated_at = NOW() WHERE id = :booking_id";
            $updateBookingStmt = $this->conn->prepare($updateBookingQuery);
            $updateBookingStmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
            $updateBookingResult = $updateBookingStmt->execute();
            
            if ($updateBookingResult) {
                // Check if there are other active bookings for this spot
                $activeBookingsQuery = "SELECT COUNT(*) as count FROM " . $this->table . " 
                                       WHERE spot_id = :spot_id 
                                       AND booking_status IN ('confirmed', 'active') 
                                       AND end_date >= CURDATE()";
                $activeBookingsStmt = $this->conn->prepare($activeBookingsQuery);
                $activeBookingsStmt->bindParam(':spot_id', $booking['spot_id'], PDO::PARAM_INT);
                $activeBookingsStmt->execute();
                $activeBookings = $activeBookingsStmt->fetch();
                
                // If no other active bookings, set spot status back to active
                if ($activeBookings['count'] == 0) {
                    $updateSpotQuery = "UPDATE parking_spots SET status = 'active', updated_at = NOW() WHERE id = :spot_id";
                    $updateSpotStmt = $this->conn->prepare($updateSpotQuery);
                    $updateSpotStmt->bindParam(':spot_id', $booking['spot_id'], PDO::PARAM_INT);
                    $updateSpotResult = $updateSpotStmt->execute();
                    
                    if (!$updateSpotResult) {
                        $this->conn->rollBack();
                        return false;
                    }
                }
                
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>