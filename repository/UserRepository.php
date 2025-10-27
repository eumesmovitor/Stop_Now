<?php

require_once 'BaseRepository.php';

/**
 * Repository para gerenciar operações de usuários
 */
class UserRepository extends BaseRepository {
    protected $table = 'users';
    protected $primaryKey = 'id';

    public function __construct($db) {
        parent::__construct($db);
    }

    /**
     * Registra um novo usuário
     */
    public function register($userData) {
        $data = [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => password_hash($userData['password'], HASH_ALGO, ['cost' => HASH_COST]),
            'phone' => $userData['phone'],
            'cpf' => $userData['cpf'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($data);
    }

    /**
     * Busca usuário por email
     */
    public function findByEmail($email) {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Verifica se email já existe
     */
    public function emailExists($email) {
        return $this->exists(['email' => $email]);
    }

    /**
     * Autentica usuário
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    /**
     * Atualiza perfil do usuário
     */
    public function updateProfile($userId, $data) {
        $allowedFields = ['name', 'phone', 'cpf', 'profile_image'];
        $updateData = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updateData[$field] = $value;
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($userId, $updateData);
    }

    /**
     * Altera senha do usuário
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->findById($userId);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        $newPasswordHash = password_hash($newPassword, HASH_ALGO, ['cost' => HASH_COST]);
        
        return $this->update($userId, [
            'password' => $newPasswordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Busca estatísticas do usuário
     */
    public function getStats($userId) {
        $stats = [
            'total_bookings' => 0,
            'total_spots' => 0,
            'total_reviews' => 0,
            'avg_rating' => 0
        ];

        // Total bookings
        $query = "SELECT COUNT(*) as total FROM bookings WHERE renter_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $stats['total_bookings'] = $stmt->fetch()['total'];

        // Total spots
        $query = "SELECT COUNT(*) as total FROM parking_spots WHERE owner_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $stats['total_spots'] = $stmt->fetch()['total'];

        // Reviews and rating
        $query = "SELECT COUNT(*) as total, AVG(rating) as avg_rating 
                  FROM reviews WHERE reviewed_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        $stats['total_reviews'] = $result['total'];
        $stats['avg_rating'] = $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;

        return $stats;
    }

    /**
     * Verifica se usuário está verificado
     */
    public function isVerified($userId) {
        $user = $this->findById($userId);
        return $user && $user['is_verified'] == 1;
    }

    /**
     * Marca usuário como verificado
     */
    public function verifyUser($userId) {
        return $this->update($userId, [
            'is_verified' => 1,
            'verification_date' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Busca usuários por status de verificação
     */
    public function findByVerificationStatus($isVerified) {
        return $this->findBy(['is_verified' => $isVerified ? 1 : 0]);
    }

    /**
     * Busca usuários com paginação
     */
    public function findWithPagination($limit = 10, $offset = 0, $filters = []) {
        $query = "SELECT id, name, email, phone, is_verified, created_at FROM {$this->table}";
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['search'])) {
            $whereClause[] = "(name LIKE :search OR email LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (isset($filters['is_verified'])) {
            $whereClause[] = "is_verified = :is_verified";
            $params[':is_verified'] = $filters['is_verified'] ? 1 : 0;
        }
        
        if (!empty($whereClause)) {
            $query .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
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
     * Conta usuários com filtros
     */
    public function countWithFilters($filters = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        $whereClause = [];
        
        if (!empty($filters['search'])) {
            $whereClause[] = "(name LIKE :search OR email LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (isset($filters['is_verified'])) {
            $whereClause[] = "is_verified = :is_verified";
            $params[':is_verified'] = $filters['is_verified'] ? 1 : 0;
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
}
