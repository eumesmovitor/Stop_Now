<?php
require_once 'config/database.php';
require_once 'config/utils.php';
require_once 'models/ParkingSpot.php';
require_once 'models/Favorite.php';

class SearchController {
    private $db;
    private $spot;
    private $favorite;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->spot = new ParkingSpot($this->db);
        $this->favorite = new Favorite($this->db);
    }

    public function index() {
        $city = sanitizeInput($_GET['city'] ?? '');
        $priceMin = floatval($_GET['price_min'] ?? 0);
        $priceMax = floatval($_GET['price_max'] ?? 0);
        $spotType = sanitizeInput($_GET['spot_type'] ?? '');
        $features = $_GET['features'] ?? [];
        if (!is_array($features)) {
            $features = [];
        }
        $sortBy = sanitizeInput($_GET['sort'] ?? '');
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        $cities = $this->spot->getCities();
        $spots = $this->spot->search('', $city, $priceMin, $priceMax, $spotType, $features, $sortBy, $limit, $offset);
        $totalSpots = $this->spot->getSearchCount('', $city, $priceMin, $priceMax, $spotType, $features);
        $totalPages = ceil($totalSpots / $limit);
        
        // Get user favorites if logged in
        $userFavorites = [];
        if (AuthController::isLoggedIn()) {
            $userFavorites = $this->favorite->getByUser($_SESSION['user_id'], 1000);
            $userFavorites = array_column($userFavorites, 'id');
        }
        
        $pageTitle = 'Buscar Vagas - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/search.php';
        require_once 'views/includes/footer.php';
    }

    public function advanced() {
        $filters = [
            'search' => sanitizeInput($_GET['search'] ?? ''),
            'city' => sanitizeInput($_GET['city'] ?? ''),
            'state' => sanitizeInput($_GET['state'] ?? ''),
            'price_min' => floatval($_GET['price_min'] ?? 0),
            'price_max' => floatval($_GET['price_max'] ?? 0),
            'spot_type' => sanitizeInput($_GET['spot_type'] ?? ''),
            'features' => $_GET['features'] ?? [],
            'sort' => sanitizeInput($_GET['sort'] ?? ''),
            'date_from' => sanitizeInput($_GET['date_from'] ?? ''),
            'date_to' => sanitizeInput($_GET['date_to'] ?? ''),
            'availability' => sanitizeInput($_GET['availability'] ?? '')
        ];
        
        $cities = $this->spot->getCities();
        $states = $this->spot->getStates();
        $spots = $this->spot->advancedSearch($filters);
        
        $pageTitle = 'Busca Avançada - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/search-advanced.php';
        require_once 'views/includes/footer.php';
    }

    public function map() {
        $spots = $this->spot->getAllForMap();
        
        $pageTitle = 'Mapa de Vagas - StopNow';
        require_once 'views/includes/header.php';
        require_once 'views/search-map.php';
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

    public function autocomplete() {
        header('Content-Type: application/json');
        $query = sanitizeInput($_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }
        
        $results = [
            'spots' => $this->spot->getAutocompleteSpots($query),
            'cities' => $this->spot->getAutocompleteCities($query)
        ];
        
        echo json_encode($results);
    }

    public function filters() {
        header('Content-Type: application/json');
        
        $filters = [
            'cities' => $this->spot->getCities(),
            'states' => $this->spot->getStates(),
            'spot_types' => ['covered', 'uncovered', 'garage', 'street'],
            'features' => [
                'covered' => 'Coberta',
                'security' => 'Segurança',
                'camera' => 'Câmeras',
                'lighting' => 'Iluminação',
                'electric_charging' => 'Carregamento Elétrico',
                'smart_lock' => 'Acesso Inteligente'
            ],
            'price_ranges' => [
                ['min' => 0, 'max' => 20, 'label' => 'Até R$ 20'],
                ['min' => 20, 'max' => 50, 'label' => 'R$ 20 - R$ 50'],
                ['min' => 50, 'max' => 100, 'label' => 'R$ 50 - R$ 100'],
                ['min' => 100, 'max' => 0, 'label' => 'Acima de R$ 100']
            ]
        ];
        
        echo json_encode($filters);
    }
}
?>
