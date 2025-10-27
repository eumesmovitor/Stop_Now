<?php

require_once 'BaseRepository.php';
require_once 'UserRepository.php';
require_once 'ParkingSpotRepository.php';
require_once 'BookingRepository.php';
require_once 'ReviewRepository.php';
require_once 'FavoriteRepository.php';
require_once 'MessageRepository.php';
require_once 'NotificationRepository.php';
require_once 'ReportRepository.php';

/**
 * Gerenciador de repositories - Facilita o acesso aos repositories
 */
class RepositoryManager {
    private $db;
    private $repositories = [];

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Obtém o repository de usuários
     */
    public function users() {
        if (!isset($this->repositories['users'])) {
            $this->repositories['users'] = new UserRepository($this->db);
        }
        return $this->repositories['users'];
    }

    /**
     * Obtém o repository de vagas de estacionamento
     */
    public function parkingSpots() {
        if (!isset($this->repositories['parkingSpots'])) {
            $this->repositories['parkingSpots'] = new ParkingSpotRepository($this->db);
        }
        return $this->repositories['parkingSpots'];
    }

    /**
     * Obtém o repository de reservas
     */
    public function bookings() {
        if (!isset($this->repositories['bookings'])) {
            $this->repositories['bookings'] = new BookingRepository($this->db);
        }
        return $this->repositories['bookings'];
    }

    /**
     * Obtém o repository de avaliações
     */
    public function reviews() {
        if (!isset($this->repositories['reviews'])) {
            $this->repositories['reviews'] = new ReviewRepository($this->db);
        }
        return $this->repositories['reviews'];
    }

    /**
     * Obtém o repository de favoritos
     */
    public function favorites() {
        if (!isset($this->repositories['favorites'])) {
            $this->repositories['favorites'] = new FavoriteRepository($this->db);
        }
        return $this->repositories['favorites'];
    }

    /**
     * Obtém o repository de mensagens
     */
    public function messages() {
        if (!isset($this->repositories['messages'])) {
            $this->repositories['messages'] = new MessageRepository($this->db);
        }
        return $this->repositories['messages'];
    }

    /**
     * Obtém o repository de notificações
     */
    public function notifications() {
        if (!isset($this->repositories['notifications'])) {
            $this->repositories['notifications'] = new NotificationRepository($this->db);
        }
        return $this->repositories['notifications'];
    }

    /**
     * Obtém o repository de relatórios
     */
    public function reports() {
        if (!isset($this->repositories['reports'])) {
            $this->repositories['reports'] = new ReportRepository($this->db);
        }
        return $this->repositories['reports'];
    }

    /**
     * Obtém a conexão com o banco de dados
     */
    public function getConnection() {
        return $this->db;
    }

    /**
     * Inicia uma transação
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    /**
     * Confirma uma transação
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * Desfaz uma transação
     */
    public function rollback() {
        return $this->db->rollback();
    }

    /**
     * Executa uma transação com callback
     */
    public function transaction($callback) {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
