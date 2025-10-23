<?php
/**
 * Response System for StopNow
 */

class Response {
    private $data = [];
    private $status = 200;
    private $headers = [];
    
    public function __construct($data = [], $status = 200) {
        $this->data = $data;
        $this->status = $status;
    }
    
    public function json($data = null, $status = null) {
        if ($data !== null) {
            $this->data = $data;
        }
        if ($status !== null) {
            $this->status = $status;
        }
        
        http_response_code($this->status);
        header('Content-Type: application/json');
        
        echo json_encode($this->data);
        exit;
    }
    
    public function success($message = 'Success', $data = []) {
        $this->data = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        $this->status = 200;
        return $this;
    }
    
    public function error($message = 'Error', $data = [], $status = 400) {
        $this->data = [
            'success' => false,
            'message' => $message,
            'data' => $data
        ];
        $this->status = $status;
        return $this;
    }
    
    public function validationError($errors = []) {
        $this->data = [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ];
        $this->status = 422;
        return $this;
    }
    
    public function notFound($message = 'Not found') {
        $this->data = [
            'success' => false,
            'message' => $message
        ];
        $this->status = 404;
        return $this;
    }
    
    public function unauthorized($message = 'Unauthorized') {
        $this->data = [
            'success' => false,
            'message' => $message
        ];
        $this->status = 401;
        return $this;
    }
    
    public function forbidden($message = 'Forbidden') {
        $this->data = [
            'success' => false,
            'message' => $message
        ];
        $this->status = 403;
        return $this;
    }
    
    public function serverError($message = 'Internal server error') {
        $this->data = [
            'success' => false,
            'message' => $message
        ];
        $this->status = 500;
        return $this;
    }
    
    public function redirect($url, $message = null, $type = 'info') {
        if ($message) {
            $_SESSION['message'] = $message;
            $_SESSION['message_type'] = $type;
        }
        header('Location: ' . $url);
        exit;
    }
    
    public function view($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
        exit;
    }
}

// Helper functions for common responses
function apiSuccess($message = 'Success', $data = []) {
    return (new Response())->success($message, $data)->json();
}

function apiError($message = 'Error', $data = [], $status = 400) {
    return (new Response())->error($message, $data, $status)->json();
}

function apiValidationError($errors = []) {
    return (new Response())->validationError($errors)->json();
}

function apiNotFound($message = 'Not found') {
    return (new Response())->notFound($message)->json();
}

function apiUnauthorized($message = 'Unauthorized') {
    return (new Response())->unauthorized($message)->json();
}

function apiForbidden($message = 'Forbidden') {
    return (new Response())->forbidden($message)->json();
}

function apiServerError($message = 'Internal server error') {
    return (new Response())->serverError($message)->json();
}
?>
