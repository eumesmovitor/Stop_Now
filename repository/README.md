# Repository Pattern - StopNow

Este diretório contém a implementação do padrão Repository para gerenciar a comunicação com o banco de dados do sistema StopNow.

## Estrutura

- `BaseRepository.php` - Classe base com operações CRUD comuns
- `UserRepository.php` - Repository para usuários
- `ParkingSpotRepository.php` - Repository para vagas de estacionamento
- `BookingRepository.php` - Repository para reservas
- `ReviewRepository.php` - Repository para avaliações
- `FavoriteRepository.php` - Repository para favoritos
- `MessageRepository.php` - Repository para mensagens
- `NotificationRepository.php` - Repository para notificações
- `ReportRepository.php` - Repository para relatórios e estatísticas
- `RepositoryManager.php` - Gerenciador centralizado dos repositories

## Como usar

### 1. Configuração inicial

```php
<?php
require_once 'config/database.php';
require_once 'repository/RepositoryManager.php';

// Conectar ao banco de dados
$database = new Database();
$db = $database->connect();

// Criar o gerenciador de repositories
$repo = new RepositoryManager($db);
?>
```

### 2. Exemplos de uso

#### Usuários

```php
// Buscar usuário por ID
$user = $repo->users()->findById(1);

// Buscar usuário por email
$user = $repo->users()->findByEmail('usuario@email.com');

// Criar novo usuário
$userId = $repo->users()->register([
    'name' => 'João Silva',
    'email' => 'joao@email.com',
    'password' => 'senha123',
    'phone' => '11999999999',
    'cpf' => '12345678901'
]);

// Atualizar perfil
$repo->users()->updateProfile($userId, [
    'name' => 'João Silva Santos',
    'phone' => '11988888888'
]);
```

#### Vagas de Estacionamento

```php
// Buscar todas as vagas
$spots = $repo->parkingSpots()->getAllWithOwner(10, 0);

// Buscar vaga por ID
$spot = $repo->parkingSpots()->findByIdWithOwner(1);

// Criar nova vaga
$spotId = $repo->parkingSpots()->createSpot([
    'owner_id' => 1,
    'title' => 'Vaga Coberta Centro',
    'description' => 'Vaga coberta no centro da cidade',
    'address' => 'Rua das Flores, 123',
    'city' => 'São Paulo',
    'state' => 'SP',
    'price_daily' => 25.00,
    'spot_type' => 'covered'
]);

// Buscar vagas com filtros
$spots = $repo->parkingSpots()->search([
    'city' => 'São Paulo',
    'price_min' => 20,
    'price_max' => 50,
    'features' => ['covered', 'security'],
    'sort' => 'price',
    'limit' => 10
]);
```

#### Reservas

```php
// Criar nova reserva
$bookingId = $repo->bookings()->createBooking([
    'spot_id' => 1,
    'renter_id' => 2,
    'start_date' => '2024-01-15',
    'end_date' => '2024-01-20',
    'payment_method' => 'credit_card',
    'booking_status' => 'pending'
]);

// Buscar reservas do usuário
$bookings = $repo->bookings()->findByRenter(2);

// Atualizar status da reserva
$repo->bookings()->updateStatus($bookingId, 'confirmed');

// Completar reserva
$repo->bookings()->completeBooking($bookingId);
```

#### Avaliações

```php
// Criar avaliação
$repo->reviews()->createReview([
    'booking_id' => 1,
    'reviewer_id' => 2,
    'rating' => 5,
    'comment' => 'Excelente vaga, muito bem localizada!'
]);

// Buscar avaliações de uma vaga
$reviews = $repo->reviews()->findBySpot(1);

// Buscar avaliação média
$avgRating = $repo->reviews()->getAverageRating(1);
```

#### Favoritos

```php
// Adicionar aos favoritos
$repo->favorites()->addFavorite(1, 2); // user_id, spot_id

// Remover dos favoritos
$repo->favorites()->removeFavorite(1, 2);

// Buscar favoritos do usuário
$favorites = $repo->favorites()->findByUser(1);

// Alternar favorito
$result = $repo->favorites()->toggleFavorite(1, 2); // 'added' ou 'removed'
```

#### Mensagens

```php
// Enviar mensagem
$repo->messages()->createMessage([
    'sender_id' => 1,
    'receiver_id' => 2,
    'subject' => 'Sobre a reserva',
    'message' => 'Olá, gostaria de saber mais detalhes sobre a vaga.',
    'booking_id' => 1
]);

// Buscar conversa
$messages = $repo->messages()->getConversation(1, 2);

// Marcar como lida
$repo->messages()->markAsRead($messageId, $userId);
```

#### Notificações

```php
// Criar notificação
$repo->notifications()->createNotification([
    'user_id' => 1,
    'type' => 'booking_created',
    'title' => 'Nova Reserva',
    'message' => 'Você recebeu uma nova reserva!',
    'data' => ['booking_id' => 1]
]);

// Buscar notificações do usuário
$notifications = $repo->notifications()->findByUser(1);

// Marcar como lida
$repo->notifications()->markAsRead($notificationId, $userId);
```

#### Relatórios

```php
// Estatísticas de uma vaga
$stats = $repo->reports()->getSpotStats(1);

// Estatísticas do usuário
$userStats = $repo->reports()->getUserStats(1);

// Receita mensal
$monthlyRevenue = $repo->reports()->getMonthlyRevenue(1, 2024);

// Estatísticas administrativas
$adminStats = $repo->reports()->getAdminStats();
```

### 3. Transações

```php
// Usar transação
$result = $repo->transaction(function($repo) {
    // Criar vaga
    $spotId = $repo->parkingSpots()->createSpot($spotData);
    
    // Adicionar imagem
    $repo->parkingSpots()->addImage($spotId, $imageUrl, true);
    
    // Criar notificação
    $repo->notifications()->createNotification([
        'user_id' => $ownerId,
        'type' => 'spot_created',
        'title' => 'Vaga Criada',
        'message' => 'Sua vaga foi criada com sucesso!'
    ]);
    
    return $spotId;
});
```

## Vantagens do Padrão Repository

1. **Separação de responsabilidades**: Lógica de acesso a dados separada da lógica de negócio
2. **Facilita testes**: Pode ser facilmente mockado para testes unitários
3. **Flexibilidade**: Permite trocar a implementação de acesso a dados sem afetar o resto do código
4. **Reutilização**: Métodos comuns podem ser reutilizados em diferentes partes da aplicação
5. **Manutenibilidade**: Código mais organizado e fácil de manter
6. **Consistência**: Padrão uniforme para todas as operações de banco de dados

## Migração dos Controllers

Para migrar os controllers existentes para usar os repositories:

1. Substitua instanciações diretas dos modelos pelo RepositoryManager
2. Use os métodos dos repositories em vez dos métodos dos modelos
3. Mantenha a mesma lógica de negócio, apenas mude a forma de acessar os dados
4. Aproveite os métodos de filtro e paginação dos repositories

Exemplo de migração:

```php
// Antes (usando modelo diretamente)
$user = new User($db);
$userData = $user->getById($userId);

// Depois (usando repository)
$repo = new RepositoryManager($db);
$userData = $repo->users()->findById($userId);
```
