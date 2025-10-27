<?php
require_once 'repository/FavoriteRepository.php';

class Favorite {
    private $conn;
    private $repository;
    private $table = 'favorites';

    public function __construct($db) {
        $this->conn = $db;
        $this->repository = new FavoriteRepository($db);
    }

    public function add($userId, $spotId) {
        return $this->repository->addFavorite($userId, $spotId);
    }

    public function remove($userId, $spotId) {
        return $this->repository->removeFavorite($userId, $spotId);
    }

    public function exists($userId, $spotId) {
        return $this->repository->isFavorite($userId, $spotId);
    }

    public function getByUser($userId, $limit = 20, $offset = 0) {
        return $this->repository->findByUser($userId, $limit, $offset);
    }

    public function getCountByUser($userId) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " f
                  JOIN parking_spots ps ON f.spot_id = ps.id
                  WHERE f.user_id = :user_id AND ps.status = 'active'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function toggle($userId, $spotId) {
        return $this->repository->toggleFavorite($userId, $spotId);
    }
}
?>
