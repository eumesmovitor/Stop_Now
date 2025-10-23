<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/Booking.php';

class BookingController {
    private $db;
    private $booking;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->booking = new Booking($this->db);
    }

    public function create() {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
                redirectWithMessage(BASE_URL, 'Token de segurança inválido', 'error');
            }

            $spotId = intval($_POST['spot_id'] ?? 0);
            $startDate = sanitizeInput($_POST['start_date'] ?? '');
            $endDate = sanitizeInput($_POST['end_date'] ?? '');
            $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');

            // Validate dates
            if (empty($startDate) || empty($endDate)) {
                redirectWithMessage(BASE_URL . "/spot/{$spotId}", 'Datas de início e fim são obrigatórias', 'error');
            }

            if (strtotime($startDate) < strtotime('today')) {
                redirectWithMessage(BASE_URL . "/spot/{$spotId}", 'Data de início não pode ser no passado', 'error');
            }

            if (strtotime($endDate) < strtotime($startDate)) {
                redirectWithMessage(BASE_URL . "/spot/{$spotId}", 'Data de fim deve ser posterior à data de início', 'error');
            }

            // Check if spot is available
            require_once 'models/ParkingSpot.php';
            $spotModel = new ParkingSpot($this->db);
            
            // First check if spot exists and is active
            $spot = $spotModel->getById($spotId);
            if (!$spot) {
                redirectWithMessage(BASE_URL . "/spot/{$spotId}", 'Vaga não encontrada', 'error');
            }
            
            if ($spot['status'] !== 'active') {
                redirectWithMessage(BASE_URL . "/spot/{$spotId}", 'Esta vaga não está disponível para reservas', 'error');
            }
            
            // Check for date conflicts
            if (!$spotModel->isAvailable($spotId, $startDate, $endDate)) {
                // Get unavailable dates to show user
                $unavailableDates = $spotModel->getUnavailableDates($spotId, $startDate, $endDate);
                $conflictDates = array_intersect(
                    $unavailableDates,
                    array_map(function($date) {
                        $current = strtotime($startDate);
                        $end = strtotime($endDate);
                        $dates = [];
                        while ($current <= $end) {
                            $dates[] = date('Y-m-d', $current);
                            $current = strtotime('+1 day', $current);
                        }
                        return $dates;
                    }, [$startDate, $endDate])
                );
                
                $conflictDatesStr = implode(', ', array_slice($conflictDates, 0, 5));
                if (count($conflictDates) > 5) {
                    $conflictDatesStr .= ' e mais...';
                }
                
                redirectWithMessage(BASE_URL . "/spot/{$spotId}", "Vaga não está disponível. Conflitos nas datas: {$conflictDatesStr}", 'error');
            }

            $data = [
                'spot_id' => $spotId,
                'renter_id' => $_SESSION['user_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_method' => $paymentMethod,
                'status' => 'confirmed'
            ];

            if ($this->booking->create($data)) {
                redirectWithMessage(BASE_URL . '/dashboard', 'Reserva realizada com sucesso! A vaga agora está ocupada.');
            } else {
                redirectWithMessage(BASE_URL . "/spot/{$spotId}", 'Erro ao realizar reserva', 'error');
            }
        }
    }

    public function unlockRemote($bookingId) {
        AuthController::requireLogin();
        
        header('Content-Type: application/json');
        
        $booking = $this->booking->getById($bookingId);
        if (!$booking || $booking['renter_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Reserva não encontrada']);
            return;
        }

        // Simulate smart lock unlock
        echo json_encode(['success' => true, 'message' => 'Fechadura desbloqueada com sucesso']);
    }
    
    public function complete($bookingId) {
        AuthController::requireLogin();
        
        $bookingId = intval($bookingId);
        $booking = $this->booking->getById($bookingId);
        
        if (!$booking) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Reserva não encontrada', 'error');
        }
        
        // Check if user is the renter or the spot owner
        require_once 'models/ParkingSpot.php';
        $spotModel = new ParkingSpot($this->db);
        $spot = $spotModel->getById($booking['spot_id']);
        
        if ($booking['renter_id'] != $_SESSION['user_id'] && $spot['owner_id'] != $_SESSION['user_id']) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Você não tem permissão para completar esta reserva', 'error');
        }
        
        if ($this->booking->completeBooking($bookingId)) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Reserva finalizada com sucesso! A vaga está disponível novamente.');
        } else {
            redirectWithMessage(BASE_URL . '/dashboard', 'Erro ao finalizar reserva', 'error');
        }
    }
    
    public function cancel($bookingId) {
        AuthController::requireLogin();
        
        $bookingId = intval($bookingId);
        $booking = $this->booking->getById($bookingId);
        
        if (!$booking) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Reserva não encontrada', 'error');
        }
        
        // Check if user is the renter or the spot owner
        require_once 'models/ParkingSpot.php';
        $spotModel = new ParkingSpot($this->db);
        $spot = $spotModel->getById($booking['spot_id']);
        
        if ($booking['renter_id'] != $_SESSION['user_id'] && $spot['owner_id'] != $_SESSION['user_id']) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Você não tem permissão para cancelar esta reserva', 'error');
        }
        
        // Check if booking can be cancelled (not already completed or cancelled)
        if (in_array($booking['booking_status'], ['completed', 'cancelled'])) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Esta reserva não pode ser cancelada', 'error');
        }
        
        if ($this->booking->cancelBooking($bookingId)) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Reserva cancelada com sucesso! A vaga está disponível novamente.');
        } else {
            redirectWithMessage(BASE_URL . '/dashboard', 'Erro ao cancelar reserva', 'error');
        }
    }
}
?>