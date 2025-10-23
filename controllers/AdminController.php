<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/User.php';
require_once 'models/ParkingSpot.php';
require_once 'models/Booking.php';
require_once 'models/Review.php';

class AdminController {
    private $db;
    private $user;
    private $spot;
    private $booking;
    private $review;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
        $this->spot = new ParkingSpot($this->db);
        $this->booking = new Booking($this->db);
        $this->review = new Review($this->db);
    }

    public function index() {
        AuthController::requireLogin();
        
        // Get admin statistics
        $stats = $this->getAdminStats();
        
        $pageTitle = 'Painel Administrativo - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/admin/dashboard.php';
        require_once 'views/includes/footer.php';
    }

    public function users() {
        AuthController::requireLogin();
        
        $users = $this->getAllUsers();
        
        $pageTitle = 'Gerenciar Usuários - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/admin/users.php';
        require_once 'views/includes/footer.php';
    }

    public function spots() {
        AuthController::requireLogin();
        
        $spots = $this->getAllSpots();
        
        $pageTitle = 'Gerenciar Vagas - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/admin/spots.php';
        require_once 'views/includes/footer.php';
    }

    public function bookings() {
        AuthController::requireLogin();
        
        $bookings = $this->getAllBookings();
        
        $pageTitle = 'Gerenciar Reservas - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/admin/bookings.php';
        require_once 'views/includes/footer.php';
    }

    public function reports() {
        AuthController::requireLogin();
        
        $reports = $this->getReports();
        
        $pageTitle = 'Relatórios - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/admin/reports.php';
        require_once 'views/includes/footer.php';
    }

    private function getAdminStats() {
        $stats = [
            'total_users' => 0,
            'total_spots' => 0,
            'total_bookings' => 0,
            'total_revenue' => 0,
            'active_users' => 0,
            'pending_spots' => 0
        ];

        // Total users
        $query = "SELECT COUNT(*) as total FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch()['total'];

        // Total spots
        $query = "SELECT COUNT(*) as total FROM parking_spots";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_spots'] = $stmt->fetch()['total'];

        // Total bookings
        $query = "SELECT COUNT(*) as total FROM bookings";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_bookings'] = $stmt->fetch()['total'];

        // Total revenue
        $query = "SELECT SUM(final_price) as total FROM bookings WHERE payment_status = 'released'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

        // Active users (logged in last 30 days)
        $query = "SELECT COUNT(*) as total FROM users WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['active_users'] = $stmt->fetch()['total'];

        // Pending spots
        $query = "SELECT COUNT(*) as total FROM parking_spots WHERE status = 'inactive'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['pending_spots'] = $stmt->fetch()['total'];

        return $stats;
    }

    private function getAllUsers() {
        $query = "SELECT u.*, 
                  (SELECT COUNT(*) FROM parking_spots WHERE owner_id = u.id) as total_spots,
                  (SELECT COUNT(*) FROM bookings WHERE renter_id = u.id) as total_bookings
                  FROM users u 
                  ORDER BY u.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    private function getAllSpots() {
        $query = "SELECT ps.*, u.name as owner_name,
                  (SELECT COUNT(*) FROM bookings WHERE spot_id = ps.id) as total_bookings
                  FROM parking_spots ps
                  JOIN users u ON ps.owner_id = u.id
                  ORDER BY ps.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    private function getAllBookings() {
        $query = "SELECT b.*, ps.title as spot_title, 
                  u1.name as renter_name, u2.name as owner_name
                  FROM bookings b
                  JOIN parking_spots ps ON b.spot_id = ps.id
                  JOIN users u1 ON b.renter_id = u1.id
                  JOIN users u2 ON ps.owner_id = u2.id
                  ORDER BY b.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    private function getReports() {
        return [
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'user_registrations' => $this->getUserRegistrations(),
            'spot_activity' => $this->getSpotActivity(),
            'booking_stats' => $this->getBookingStats()
        ];
    }

    private function getMonthlyRevenue() {
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                  SUM(final_price) as revenue
                  FROM bookings 
                  WHERE payment_status = 'released' 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    private function getUserRegistrations() {
        $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                  COUNT(*) as registrations
                  FROM users 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    private function getSpotActivity() {
        $query = "SELECT ps.city, COUNT(*) as spots, 
                  SUM(CASE WHEN ps.status = 'active' THEN 1 ELSE 0 END) as active_spots
                  FROM parking_spots ps
                  GROUP BY ps.city
                  ORDER BY spots DESC
                  LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    private function getBookingStats() {
        $query = "SELECT 
                  COUNT(*) as total_bookings,
                  AVG(final_price) as avg_price,
                  SUM(final_price) as total_revenue
                  FROM bookings 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>





