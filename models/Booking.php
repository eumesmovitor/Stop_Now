<?php
require_once 'repository/BookingRepository.php';

class Booking {
    private $conn;
    private $repository;
    private $table = 'bookings';

    public function __construct($db) {
        $this->conn = $db;
        $this->repository = new BookingRepository($db);
    }

    public function create($data) {
        // Map 'status' to 'booking_status' if it exists in the data
        if (isset($data['status'])) {
            $data['booking_status'] = $data['status'];
            unset($data['status']);
        }
        
        $bookingId = $this->repository->createBooking($data);
        return $bookingId ? true : false;
    }

    public function getById($id) {
        return $this->repository->findById($id);
    }

    public function getByRenter($renterId) {
        return $this->repository->findByRenter($renterId);
    }

    public function getByOwner($ownerId) {
        return $this->repository->findByOwner($ownerId);
    }
    
    public function updateStatus($id, $status) {
        return $this->repository->updateStatus($id, $status);
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