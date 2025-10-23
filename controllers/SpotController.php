<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/ParkingSpot.php';
require_once 'models/Review.php';

class SpotController {
    private $db;
    private $spot;
    private $review;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->spot = new ParkingSpot($this->db);
        $this->review = new Review($this->db);
    }

    public function index() {
        try {
            $spots = $this->spot->getAll(20);
            if (empty($spots)) {
                $spots = [];
            }
            $pageTitle = 'StopNow - Aluguel de Vagas de Estacionamento';
            $db = $this->db;
            require_once 'views/includes/header.php';
            require_once 'views/home.php';
            require_once 'views/includes/footer.php';
        } catch (Exception $e) {
            error_log('Error in SpotController::index: ' . $e->getMessage());
            $spots = [];
            $pageTitle = 'StopNow - Aluguel de Vagas de Estacionamento';
            $db = $this->db;
            require_once 'views/includes/header.php';
            require_once 'views/home.php';
            require_once 'views/includes/footer.php';
        }
    }

    public function show($params = null) {
        $id = is_array($params) ? ($params['id'] ?? $params[0] ?? null) : $params;
        $spot = $this->spot->getById($id);
        if(!$spot) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $images = $this->spot->getImages($id);
        $reviews = $this->review->getBySpot($id);
        
        $pageTitle = htmlspecialchars($spot['title']) . ' - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/spot-details.php';
        require_once 'views/includes/footer.php';
    }

    public function create() {
        AuthController::requireLogin();
        
        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            $pageTitle = 'Anunciar Vaga - StopNow';
            require_once 'views/includes/header.php';
            require_once 'views/list-spot.php';
            require_once 'views/includes/footer.php';
        } else {
            // CSRF Protection
            if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
                redirectWithMessage(BASE_URL . '/list-spot', 'Token de segurança inválido', 'error');
            }

            // Sanitize and validate input
            $title = sanitizeInput($_POST['title'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            $address = sanitizeInput($_POST['address'] ?? '');
            $city = sanitizeInput($_POST['city'] ?? '');
            $state = sanitizeInput($_POST['state'] ?? '');
            $zipCode = sanitizeInput($_POST['zip_code'] ?? '');
            $priceDaily = floatval($_POST['price_daily'] ?? 0);
            $priceWeekly = floatval($_POST['price_weekly'] ?? 0);
            $priceMonthly = floatval($_POST['price_monthly'] ?? 0);
            $priceAnnual = floatval($_POST['price_annual'] ?? 0);
            $spotType = sanitizeInput($_POST['spot_type'] ?? 'uncovered');
            $maxHeight = floatval($_POST['max_height'] ?? 0);
            $maxWidth = floatval($_POST['max_width'] ?? 0);

            // Validation
            $errors = [];

            if (empty($title) || strlen($title) < 5) {
                $errors[] = 'Título deve ter pelo menos 5 caracteres';
            }

            if (empty($address) || strlen($address) < 10) {
                $errors[] = 'Endereço deve ter pelo menos 10 caracteres';
            }

            if (empty($city) || strlen($city) < 2) {
                $errors[] = 'Cidade é obrigatória';
            }

            if (empty($state)) {
                $errors[] = 'Estado é obrigatório';
            }

            if (empty($zipCode) || !preg_match('/^\d{5}-?\d{3}$/', $zipCode)) {
                $errors[] = 'CEP inválido';
            }

            if ($priceDaily <= 0) {
                $errors[] = 'Preço diário deve ser maior que zero';
            }

            if (!in_array($spotType, ['covered', 'uncovered', 'garage', 'street'])) {
                $errors[] = 'Tipo de vaga inválido';
            }

            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
                header('Location: ' . BASE_URL . '/list-spot');
                exit;
            }

            $data = [
                'owner_id' => $_SESSION['user_id'],
                'title' => $title,
                'description' => $description,
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'zip_code' => $zipCode,
                'price_daily' => $priceDaily,
                'price_weekly' => $priceWeekly > 0 ? $priceWeekly : null,
                'price_monthly' => $priceMonthly > 0 ? $priceMonthly : null,
                'price_annual' => $priceAnnual > 0 ? $priceAnnual : null,
                'spot_type' => $spotType,
                'is_covered' => isset($_POST['is_covered']) ? 1 : 0,
                'has_security' => isset($_POST['has_security']) ? 1 : 0,
                'has_camera' => isset($_POST['has_camera']) ? 1 : 0,
                'has_lighting' => isset($_POST['has_lighting']) ? 1 : 0,
                'has_electric_charging' => isset($_POST['has_electric_charging']) ? 1 : 0,
                'has_smart_lock' => isset($_POST['has_smart_lock']) ? 1 : 0,
                'smart_lock_type' => !empty($_POST['smart_lock_type']) ? sanitizeInput($_POST['smart_lock_type']) : null,
                'max_height' => $maxHeight > 0 ? $maxHeight : null,
                'max_width' => $maxWidth > 0 ? $maxWidth : null
            ];

            $spotId = $this->spot->create($data);
            
            if($spotId) {
                // Handle image uploads
                if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                    $this->handleImageUploads($spotId, $_FILES['images']);
                }
                
                // Log activity
                logActivity($_SESSION['user_id'], 'SPOT_CREATED', "Created spot: $title");
                
                redirectWithMessage(BASE_URL . '/dashboard', 'Vaga cadastrada com sucesso!');
            } else {
                redirectWithMessage(BASE_URL . '/list-spot', 'Erro ao cadastrar vaga', 'error');
            }
        }
    }

    public function search() {
        $searchQuery = sanitizeInput($_GET['q'] ?? $_POST['q'] ?? '');
        $city = sanitizeInput($_GET['city'] ?? $_POST['city'] ?? '');
        $priceMin = floatval($_GET['price_min'] ?? $_POST['price_min'] ?? 0);
        $priceMax = floatval($_GET['price_max'] ?? $_POST['price_max'] ?? 0);
        $spotType = sanitizeInput($_GET['spot_type'] ?? $_POST['spot_type'] ?? '');
        $features = $_GET['features'] ?? $_POST['features'] ?? [];
        $sortBy = sanitizeInput($_GET['sort'] ?? '');
        
        $cities = $this->spot->getCities();
        $spots = $this->spot->search($searchQuery, $city, $priceMin, $priceMax, $spotType, $features, $sortBy);
        
        // If AJAX request, return JSON
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode($spots);
            return;
        }
        
        $pageTitle = 'Buscar Vagas - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/search.php';
        require_once 'views/includes/footer.php';
    }

    public function searchByCity($params) {
        $city = $params['city'] ?? '';
        $spots = $this->spot->searchByCity($city);
        
        $pageTitle = "Vagas em {$city} - StopNow";
        require_once 'views/includes/header.php';
        require_once 'views/search.php';
        require_once 'views/includes/footer.php';
    }

    public function filter() {
        $filters = $_GET;
        $spots = $this->spot->filter($filters);
        
        $pageTitle = 'Filtrar Vagas - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/search.php';
        require_once 'views/includes/footer.php';
    }
    
    public function suggestions() {
        header('Content-Type: application/json');
        $query = sanitizeInput($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }
        
        $suggestions = $this->spot->searchSuggestions($query, 8);
        echo json_encode($suggestions);
    }
    
    public function edit($params = null) {
        AuthController::requireLogin();
        
        $id = is_array($params) ? ($params['id'] ?? $params[0] ?? null) : $params;
        $spot = $this->spot->getById($id);
        
        if (!$spot) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Vaga não encontrada', 'error');
        }
        
        // Check if user owns this spot
        if ($spot['owner_id'] != $_SESSION['user_id']) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Você não tem permissão para editar esta vaga', 'error');
        }
        
        $images = $this->spot->getImages($id);
        
        $pageTitle = 'Editar Vaga - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/spot-edit.php';
        require_once 'views/includes/footer.php';
    }
    
    public function update($params = null) {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirectWithMessage(BASE_URL . '/dashboard', 'Método não permitido', 'error');
        }
        
        // CSRF Protection
        if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Token de segurança inválido', 'error');
        }
        
        $spotId = intval($_POST['spot_id'] ?? 0);
        if (!$spotId) {
            redirectWithMessage(BASE_URL . '/dashboard', 'ID da vaga inválido', 'error');
        }
        
        // Check if spot exists and user owns it
        $spot = $this->spot->getById($spotId);
        if (!$spot) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Vaga não encontrada', 'error');
        }
        
        if ($spot['owner_id'] != $_SESSION['user_id']) {
            redirectWithMessage(BASE_URL . '/dashboard', 'Você não tem permissão para editar esta vaga', 'error');
        }
        
        // Sanitize and validate input
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $city = sanitizeInput($_POST['city'] ?? '');
        $state = sanitizeInput($_POST['state'] ?? '');
        $zipCode = sanitizeInput($_POST['zip_code'] ?? '');
        $priceDaily = floatval($_POST['price_daily'] ?? 0);
        $priceWeekly = floatval($_POST['price_weekly'] ?? 0);
        $priceMonthly = floatval($_POST['price_monthly'] ?? 0);
        $priceAnnual = floatval($_POST['price_annual'] ?? 0);
        $spotType = sanitizeInput($_POST['spot_type'] ?? 'uncovered');
        $maxHeight = floatval($_POST['max_height'] ?? 0);
        $maxWidth = floatval($_POST['max_width'] ?? 0);

        // Validation
        $errors = [];

        if (empty($title) || strlen($title) < 5) {
            $errors[] = 'Título deve ter pelo menos 5 caracteres';
        }

        if (empty($address) || strlen($address) < 10) {
            $errors[] = 'Endereço deve ter pelo menos 10 caracteres';
        }

        if (empty($city) || strlen($city) < 2) {
            $errors[] = 'Cidade é obrigatória';
        }

        if (empty($state)) {
            $errors[] = 'Estado é obrigatório';
        }

        if (empty($zipCode) || !preg_match('/^\d{5}-?\d{3}$/', $zipCode)) {
            $errors[] = 'CEP inválido';
        }

        if ($priceDaily <= 0) {
            $errors[] = 'Preço diário deve ser maior que zero';
        }

        if (!in_array($spotType, ['covered', 'uncovered', 'garage', 'street'])) {
            $errors[] = 'Tipo de vaga inválido';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . BASE_URL . '/spot/edit/' . $spotId);
            exit;
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip_code' => $zipCode,
            'price_daily' => $priceDaily,
            'price_weekly' => $priceWeekly > 0 ? $priceWeekly : null,
            'price_monthly' => $priceMonthly > 0 ? $priceMonthly : null,
            'price_annual' => $priceAnnual > 0 ? $priceAnnual : null,
            'spot_type' => $spotType,
            'is_covered' => isset($_POST['is_covered']) ? 1 : 0,
            'has_security' => isset($_POST['has_security']) ? 1 : 0,
            'has_camera' => isset($_POST['has_camera']) ? 1 : 0,
            'has_lighting' => isset($_POST['has_lighting']) ? 1 : 0,
            'has_electric_charging' => isset($_POST['has_electric_charging']) ? 1 : 0,
            'has_smart_lock' => isset($_POST['has_smart_lock']) ? 1 : 0,
            'smart_lock_type' => !empty($_POST['smart_lock_type']) ? sanitizeInput($_POST['smart_lock_type']) : null,
            'max_height' => $maxHeight > 0 ? $maxHeight : null,
            'max_width' => $maxWidth > 0 ? $maxWidth : null
        ];

        if ($this->spot->update($spotId, $data)) {
            // Handle image uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $this->handleImageUploads($spotId, $_FILES['images']);
            }
            
            // Handle image removal
            if (isset($_POST['remove_images']) && is_array($_POST['remove_images'])) {
                foreach ($_POST['remove_images'] as $imageId) {
                    $this->spot->removeImage($imageId);
                }
            }
            
            // Log activity
            logActivity($_SESSION['user_id'], 'SPOT_UPDATED', "Updated spot: $title");
            
            redirectWithMessage(BASE_URL . '/dashboard', 'Vaga atualizada com sucesso!');
        } else {
            redirectWithMessage(BASE_URL . '/spot/edit/' . $spotId, 'Erro ao atualizar vaga', 'error');
        }
    }
    
    public function delete($params = null) {
        AuthController::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Método não permitido']);
                return;
            }
            redirectWithMessage(BASE_URL . '/dashboard', 'Método não permitido', 'error');
        }
        
        // CSRF Protection
        if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Token de segurança inválido']);
                return;
            }
            redirectWithMessage(BASE_URL . '/dashboard', 'Token de segurança inválido', 'error');
        }
        
        $spotId = intval($_POST['spot_id'] ?? 0);
        if (!$spotId) {
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID da vaga inválido']);
                return;
            }
            redirectWithMessage(BASE_URL . '/dashboard', 'ID da vaga inválido', 'error');
        }
        
        // Check if spot exists and user owns it
        $spot = $this->spot->getById($spotId);
        if (!$spot) {
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vaga não encontrada']);
                return;
            }
            redirectWithMessage(BASE_URL . '/dashboard', 'Vaga não encontrada', 'error');
        }
        
        if ($spot['owner_id'] != $_SESSION['user_id']) {
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para excluir esta vaga']);
                return;
            }
            redirectWithMessage(BASE_URL . '/dashboard', 'Você não tem permissão para excluir esta vaga', 'error');
        }
        
        // Check if spot has active bookings
        $activeBookings = $this->spot->getActiveBookings($spotId);
        if (!empty($activeBookings)) {
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Não é possível excluir vaga com reservas ativas']);
                return;
            }
            redirectWithMessage(BASE_URL . '/dashboard', 'Não é possível excluir vaga com reservas ativas', 'error');
        }
        
        if ($this->spot->delete($spotId)) {
            // Log activity
            logActivity($_SESSION['user_id'], 'SPOT_DELETED', "Deleted spot: {$spot['title']}");
            
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Vaga excluída com sucesso']);
                return;
            }
            redirectWithMessage(BASE_URL . '/dashboard', 'Vaga excluída com sucesso!');
        } else {
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erro ao excluir vaga']);
                return;
            }
            redirectWithMessage(BASE_URL . '/dashboard', 'Erro ao excluir vaga', 'error');
        }
    }
    
    public function checkAvailability($params = null) {
        header('Content-Type: application/json');
        
        $spotId = intval($_GET['spot_id'] ?? 0);
        $startDate = sanitizeInput($_GET['start_date'] ?? '');
        $endDate = sanitizeInput($_GET['end_date'] ?? '');
        
        if (!$spotId) {
            echo json_encode(['available' => false, 'message' => 'ID da vaga inválido']);
            return;
        }
        
        if (empty($startDate) || empty($endDate)) {
            echo json_encode(['available' => false, 'message' => 'Datas são obrigatórias']);
            return;
        }
        
        // Validate dates
        if (strtotime($startDate) < strtotime('today')) {
            echo json_encode(['available' => false, 'message' => 'Data de início não pode ser no passado']);
            return;
        }
        
        if (strtotime($endDate) < strtotime($startDate)) {
            echo json_encode(['available' => false, 'message' => 'Data de fim deve ser posterior à data de início']);
            return;
        }
        
        $isAvailable = $this->spot->isAvailable($spotId, $startDate, $endDate);
        
        if ($isAvailable) {
            echo json_encode(['available' => true, 'message' => 'Vaga disponível para o período selecionado']);
        } else {
            echo json_encode(['available' => false, 'message' => 'Vaga não está disponível para o período selecionado']);
        }
    }
    
    public function getUnavailableDates($params = null) {
        header('Content-Type: application/json');
        
        $spotId = intval($_GET['spot_id'] ?? 0);
        $startDate = sanitizeInput($_GET['start_date'] ?? '');
        $endDate = sanitizeInput($_GET['end_date'] ?? '');
        
        if (!$spotId) {
            echo json_encode(['unavailable_dates' => []]);
            return;
        }
        
        $unavailableDates = $this->spot->getUnavailableDates($spotId, $startDate, $endDate);
        
        echo json_encode(['unavailable_dates' => $unavailableDates]);
    }
    
    public function checkDateAvailability($params = null) {
        header('Content-Type: application/json');
        
        $spotId = intval($_GET['spot_id'] ?? 0);
        $date = sanitizeInput($_GET['date'] ?? '');
        
        if (!$spotId || empty($date)) {
            echo json_encode(['available' => false]);
            return;
        }
        
        $isAvailable = $this->spot->checkDateAvailability($spotId, $date);
        
        echo json_encode(['available' => $isAvailable]);
    }
    
    private function handleImageUploads($spotId, $files) {
        $uploadDir = 'uploads/';
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $maxFiles = 5;
        
        $uploadedCount = 0;
        
        for ($i = 0; $i < min(count($files['name']), $maxFiles); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $files['name'][$i];
                $fileSize = $files['size'][$i];
                $fileTmp = $files['tmp_name'][$i];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                if (in_array($fileExt, $allowedTypes) && $fileSize <= $maxSize) {
                    $newFileName = $spotId . '_' . time() . '_' . $i . '.' . $fileExt;
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        $this->spot->addImage($spotId, $uploadPath, $uploadedCount === 0);
                        $uploadedCount++;
                    }
                }
            }
        }
    }
}
?>
