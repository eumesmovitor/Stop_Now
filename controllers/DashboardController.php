<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/User.php';
require_once 'models/Booking.php';
require_once 'models/ParkingSpot.php';
require_once 'models/Review.php';

class DashboardController {
    private $db;
    private $user;
    private $booking;
    private $spot;
    private $review;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
        $this->booking = new Booking($this->db);
        $this->spot = new ParkingSpot($this->db);
        $this->review = new Review($this->db);
    }

    public function index() {
        AuthController::requireLogin();
        
        $userId = $_SESSION['user_id'];
        $userStats = $this->user->getStats($userId);
        $myBookings = $this->booking->getByRenter($userId);
        $mySpots = $this->spot->getByOwner($userId);
        $receivedBookings = $this->booking->getByOwner($userId);
        $myReviews = $this->review->getByUser($userId);
        
        $pageTitle = 'Dashboard - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/dashboard.php';
        require_once 'views/includes/footer.php';
    }
}
?>
