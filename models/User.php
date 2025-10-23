<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $phone;
    public $cpf;
    public $profile_image;
    public $is_verified;
    public $verification_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table . " 
                  (name, email, password, phone, cpf) 
                  VALUES (:name, :email, :password, :phone, :cpf)";

        $stmt = $this->conn->prepare($query);

        $this->password = password_hash($this->password, HASH_ALGO, ['cost' => HASH_COST]);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':cpf', $this->cpf);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if(password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->email = $row['email'];
                $this->phone = $row['phone'];
                $this->cpf = $row['cpf'];
                $this->is_verified = $row['is_verified'];
                return true;
            }
        }
        return false;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return null;
    }

    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function updateProfile($userId, $data) {
        $allowedFields = ['name', 'phone', 'cpf'];
        $updateFields = [];
        $values = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updateFields[] = "$field = :$field";
                $values[$field] = $value;
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $query = "UPDATE " . $this->table . " SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        foreach ($values as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        $stmt->bindParam(':id', $userId);

        return $stmt->execute();
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        // Get current password hash
        $query = "SELECT password FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        $user = $stmt->fetch();
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }

        // Update password
        $newPasswordHash = password_hash($newPassword, HASH_ALGO, ['cost' => HASH_COST]);
        $query = "UPDATE " . $this->table . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $newPasswordHash);
        $stmt->bindParam(':id', $userId);

        return $stmt->execute();
    }

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
}
?>
