<?php
/**
 * Validation System for StopNow
 */

class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    public function validate($rules) {
        $this->errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $value = $this->data[$field] ?? null;
            $rules = explode('|', $ruleSet);
            
            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    private function applyRule($field, $value, $rule) {
        $params = explode(':', $rule);
        $ruleName = $params[0];
        $ruleValue = $params[1] ?? null;
        
        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' é obrigatório';
                }
                break;
                
            case 'min':
                if (strlen($value) < $ruleValue) {
                    $this->errors[$field][] = ucfirst($field) . ' deve ter pelo menos ' . $ruleValue . ' caracteres';
                }
                break;
                
            case 'max':
                if (strlen($value) > $ruleValue) {
                    $this->errors[$field][] = ucfirst($field) . ' deve ter no máximo ' . $ruleValue . ' caracteres';
                }
                break;
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = ucfirst($field) . ' deve ser um email válido';
                }
                break;
                
            case 'numeric':
                if (!is_numeric($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' deve ser um número';
                }
                break;
                
            case 'min_value':
                if ($value < $ruleValue) {
                    $this->errors[$field][] = ucfirst($field) . ' deve ser maior que ' . $ruleValue;
                }
                break;
                
            case 'max_value':
                if ($value > $ruleValue) {
                    $this->errors[$field][] = ucfirst($field) . ' deve ser menor que ' . $ruleValue;
                }
                break;
                
            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if (!in_array($value, $allowedValues)) {
                    $this->errors[$field][] = ucfirst($field) . ' deve ser um dos valores: ' . implode(', ', $allowedValues);
                }
                break;
                
            case 'regex':
                if (!preg_match($ruleValue, $value)) {
                    $this->errors[$field][] = ucfirst($field) . ' tem formato inválido';
                }
                break;
                
            case 'unique':
                // This would need to be implemented based on your database structure
                break;
        }
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getFirstError($field = null) {
        if ($field) {
            return $this->errors[$field][0] ?? null;
        }
        
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        
        return null;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    public function getAllErrors() {
        $allErrors = [];
        foreach ($this->errors as $field => $errors) {
            $allErrors = array_merge($allErrors, $errors);
        }
        return $allErrors;
    }
}

// Validation rules for different entities
class ValidationRules {
    public static function spot() {
        return [
            'title' => 'required|min:5|max:100',
            'description' => 'max:1000',
            'address' => 'required|min:10|max:200',
            'city' => 'required|min:2|max:50',
            'state' => 'required|min:2|max:50',
            'zip_code' => 'required|regex:/^\d{5}-?\d{3}$/',
            'price_daily' => 'required|numeric|min_value:0.01',
            'price_weekly' => 'numeric|min_value:0',
            'price_monthly' => 'numeric|min_value:0',
            'price_annual' => 'numeric|min_value:0',
            'spot_type' => 'required|in:covered,uncovered,garage,street',
            'max_height' => 'numeric|min_value:0',
            'max_width' => 'numeric|min_value:0'
        ];
    }
    
    public static function user() {
        return [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|min:10|max:20',
            'password' => 'required|min:6',
            'confirm_password' => 'required|min:6'
        ];
    }
    
    public static function booking() {
        return [
            'spot_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'total_days' => 'required|numeric|min_value:1'
        ];
    }
}
?>
