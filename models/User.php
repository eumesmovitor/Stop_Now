<?php
require_once 'repository/UserRepository.php';

class User {
    private $conn;
    private $repository;
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
        $this->repository = new UserRepository($db);
    }

    public function register() {
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'phone' => $this->phone,
            'cpf' => $this->cpf
        ];

        $userId = $this->repository->register($userData);
        
        if ($userId) {
            $this->id = $userId;
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        $user = $this->repository->authenticate($email, $password);
        
        if ($user) {
            $this->id = $user['id'];
            $this->name = $user['name'];
            $this->email = $user['email'];
            $this->phone = $user['phone'];
            $this->cpf = $user['cpf'];
            $this->is_verified = $user['is_verified'];
            return true;
        }
        return false;
    }

    public function getById($id) {
        return $this->repository->findById($id);
    }

    public function emailExists($email) {
        return $this->repository->emailExists($email);
    }

    public function updateProfile($userId, $data) {
        return $this->repository->updateProfile($userId, $data);
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        return $this->repository->changePassword($userId, $currentPassword, $newPassword);
    }

    public function getStats($userId) {
        return $this->repository->getStats($userId);
    }
}
?>
