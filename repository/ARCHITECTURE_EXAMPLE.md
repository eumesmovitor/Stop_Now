# Arquitetura Controller → Model → Repository → Database

Este documento demonstra como a arquitetura implementada funciona no sistema StopNow.

## Fluxo de Dados

```
Controller → Model → Repository → Database
     ↓         ↓         ↓          ↓
  Lógica    Lógica    Acesso    Persistência
  Negócio   Dados     Dados     Dados
```

## Exemplo Prático

### 1. Controller (AuthController.php)

```php
<?php
class AuthController {
    public function login() {
        // Recebe dados do formulário
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Cria instância do modelo
        $user = new User($db);
        
        // Chama método do modelo
        if ($user->login($email, $password)) {
            // Lógica de negócio: definir sessão
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_name'] = $user->name;
            
            // Resposta
            return ['success' => true, 'message' => 'Login realizado com sucesso'];
        } else {
            return ['success' => false, 'message' => 'Credenciais inválidas'];
        }
    }
}
```

### 2. Model (User.php)

```php
<?php
require_once 'repository/UserRepository.php';

class User {
    private $conn;
    private $repository;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->repository = new UserRepository($db);
    }
    
    public function login($email, $password) {
        // Delega para o repository
        $user = $this->repository->authenticate($email, $password);
        
        if ($user) {
            // Popula propriedades do modelo
            $this->id = $user['id'];
            $this->name = $user['name'];
            $this->email = $user['email'];
            // ... outras propriedades
            
            return true;
        }
        return false;
    }
}
```

### 3. Repository (UserRepository.php)

```php
<?php
class UserRepository extends BaseRepository {
    public function authenticate($email, $password) {
        // Busca usuário por email
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    public function findByEmail($email) {
        return $this->findOneBy(['email' => $email]);
    }
}
```

### 4. BaseRepository (BaseRepository.php)

```php
<?php
abstract class BaseRepository {
    protected $conn;
    
    public function findOneBy($conditions) {
        $whereClause = [];
        $params = [];
        
        foreach ($conditions as $field => $value) {
            $whereClause[] = "{$field} = :{$field}";
            $params[":{$field}"] = $value;
        }
        
        $query = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause) . " LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetch();
    }
}
```

## Vantagens desta Arquitetura

### 1. **Separação de Responsabilidades**
- **Controller**: Lógica de negócio e controle de fluxo
- **Model**: Representação dos dados e regras de domínio
- **Repository**: Acesso e persistência de dados
- **Database**: Armazenamento físico

### 2. **Manutenibilidade**
- Mudanças no banco de dados afetam apenas os repositories
- Mudanças na lógica de negócio afetam apenas os controllers
- Código mais organizado e fácil de entender

### 3. **Testabilidade**
- Cada camada pode ser testada independentemente
- Repositories podem ser mockados para testes unitários
- Controllers podem ser testados sem acesso ao banco

### 4. **Reutilização**
- Repositories podem ser reutilizados em diferentes contexts
- Métodos comuns centralizados na BaseRepository
- Lógica de dados compartilhada entre diferentes models

### 5. **Flexibilidade**
- Fácil troca de banco de dados (apenas mudar repositories)
- Diferentes implementações de acesso a dados
- Migração gradual sem quebrar o sistema existente

## Exemplos de Uso

### Buscar Vaga de Estacionamento

```php
// Controller
$spotController = new SpotController();
$spots = $spotController->search($filters);

// Model
class ParkingSpot {
    public function search($filters) {
        return $this->repository->search($filters);
    }
}

// Repository
class ParkingSpotRepository {
    public function search($filters) {
        // Lógica complexa de busca
        // JOINs, WHEREs, ORDER BY, etc.
    }
}
```

### Criar Reserva

```php
// Controller
$bookingController = new BookingController();
$result = $bookingController->create($bookingData);

// Model
class Booking {
    public function create($data) {
        // Validações de negócio
        if ($this->validateBookingData($data)) {
            return $this->repository->createBooking($data);
        }
        return false;
    }
}

// Repository
class BookingRepository {
    public function createBooking($data) {
        // Transação complexa
        // Criação da reserva + atualização do status da vaga
    }
}
```

## Migração dos Controllers Existentes

Os controllers existentes **não precisam ser alterados**! A interface dos models permanece a mesma:

```php
// Antes (funcionava)
$user = new User($db);
$user->login($email, $password);

// Depois (continua funcionando)
$user = new User($db);
$user->login($email, $password);
```

A única diferença é que internamente o model agora usa o repository, mas a interface pública permanece idêntica.

## Benefícios Imediatos

1. **Código mais limpo**: Queries complexas movidas para repositories
2. **Melhor organização**: Separação clara de responsabilidades
3. **Facilita manutenção**: Mudanças isoladas por camada
4. **Prepara para o futuro**: Base sólida para novas funcionalidades
5. **Zero breaking changes**: Controllers continuam funcionando normalmente

Esta arquitetura mantém a compatibilidade total com o código existente enquanto oferece uma base sólida e organizada para o desenvolvimento futuro.
