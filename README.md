# StopNow - Sistema Completo de Aluguel de Vagas de Estacionamento

Sistema completo de aluguel de vagas de estacionamento desenvolvido em PHP com arquitetura moderna e funcionalidades avançadas.

## 🚀 Funcionalidades

### 🔐 Autenticação e Usuários
- Cadastro e login de usuários
- Perfil de usuário com verificação
- Sistema de administração
- Controle de permissões

### 🅿️ Gestão de Vagas
- Cadastro completo de vagas com imagens
- Edição e exclusão de vagas
- Múltiplos tipos de vagas (coberta, descoberta, garagem, rua)
- Características avançadas (segurança, câmeras, iluminação, carregamento elétrico, acesso inteligente)
- Preços flexíveis (diário, semanal, mensal, anual)

### 🔍 Sistema de Busca Avançada
- Busca por texto, cidade, preço, tipo de vaga
- Filtros por características
- Ordenação por preço, avaliação, data
- Busca em mapa
- Autocompletar e sugestões

### ❤️ Sistema de Favoritos
- Salvar vagas favoritas
- Lista de favoritos organizada
- Sincronização em tempo real

### 📱 Sistema de Mensagens
- Mensagens entre usuários
- Conversas organizadas
- Notificações de mensagens
- Histórico de conversas

### 🔔 Sistema de Notificações
- Notificações em tempo real
- Diferentes tipos de notificação
- Marcar como lida
- Histórico de notificações

### 📊 Sistema de Reservas
- Reservas com confirmação
- Códigos de acesso
- Fechaduras inteligentes
- Histórico de reservas

### ⭐ Sistema de Avaliações
- Avaliações de vagas
- Sistema de estrelas
- Comentários detalhados
- Estatísticas de avaliação

### 📈 Relatórios e Analytics
- Dashboard com estatísticas
- Relatórios de receita
- Análise de popularidade
- Métricas de uso

## 🏗️ Arquitetura

### Estrutura de Pastas
```
StopNow/
├── config/                 # Configurações do sistema
│   ├── config.php         # Configuração principal
│   ├── database.php       # Conexão com banco
│   ├── router.php         # Sistema de rotas
│   ├── middleware.php     # Sistema de middleware
│   ├── validation.php     # Sistema de validação
│   ├── response.php       # Sistema de respostas
│   ├── cache.php          # Sistema de cache
│   └── logger.php         # Sistema de logs
├── controllers/            # Controladores
├── models/                # Modelos de dados
├── views/                 # Views (templates)
├── database/              # Scripts de banco
├── public/                # Arquivos públicos
├── uploads/               # Uploads de imagens
└── logs/                  # Arquivos de log
```

### Tecnologias Utilizadas
- **Backend**: PHP 7.4+ com arquitetura MVC
- **Banco de Dados**: MySQL com índices otimizados
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Tailwind CSS
- **Segurança**: CSRF Protection, Validação de dados, Sanitização
- **Cache**: Sistema de cache simples
- **Logs**: Sistema de logs estruturado

## 🚀 Instalação

### Pré-requisitos
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)

### Passos de Instalação

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/stopnow.git
   cd stopnow
   ```

2. **Configure o banco de dados**
   - Edite o arquivo `config/database.php`
   - Configure as credenciais do MySQL

3. **Execute as migrações**
   ```sql
   -- Execute primeiro o schema principal
   source database/schema.sql
   
   -- Depois execute as migrações
   source database/migrations.sql
   ```

4. **Configure as permissões**
   ```bash
   chmod 755 uploads/
   chmod 755 cache/
   chmod 755 logs/
   ```

5. **Acesse o sistema**
   - Abra o navegador e acesse a URL do projeto
   - Faça login com: admin@stopnow.com / admin123

## 🔧 Configuração

### Variáveis de Ambiente
Edite o arquivo `config/config.php` para configurar:

```php
// Base URL
define('BASE_URL', 'http://localhost:8000');

// Banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'stopnow');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações da aplicação
define('APP_NAME', 'StopNow');
define('APP_ENV', 'development'); // development, production
```

### Configurações de Upload
```php
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
```

## 📱 API Endpoints

### Vagas
- `GET /api/spots` - Listar vagas
- `GET /api/spots/{id}` - Detalhes da vaga
- `POST /api/spots` - Criar vaga
- `PUT /api/spots/{id}` - Atualizar vaga
- `DELETE /api/spots/{id}` - Excluir vaga

### Busca
- `GET /search` - Buscar vagas
- `GET /search/advanced` - Busca avançada
- `GET /search/map` - Busca em mapa
- `GET /search/suggestions` - Sugestões de busca

### Favoritos
- `GET /favorites` - Listar favoritos
- `POST /favorites/toggle` - Alternar favorito
- `POST /favorites/add` - Adicionar favorito
- `POST /favorites/remove` - Remover favorito

### Mensagens
- `GET /messages` - Listar conversas
- `GET /messages/conversation` - Ver conversa
- `POST /messages/send` - Enviar mensagem
- `POST /messages/mark-read` - Marcar como lida

### Notificações
- `GET /notifications` - Listar notificações
- `GET /notifications/unread` - Contar não lidas
- `POST /notifications/mark-read` - Marcar como lida

## 🔒 Segurança

### Recursos de Segurança
- **CSRF Protection**: Tokens em todos os formulários
- **Validação de Dados**: Validação rigorosa de entrada
- **Sanitização**: Limpeza de dados de entrada
- **Rate Limiting**: Proteção contra spam
- **Headers de Segurança**: XSS, CSRF, Clickjacking
- **Logs de Segurança**: Monitoramento de atividades

### Middleware de Segurança
```php
// Autenticação obrigatória
$router->get('/dashboard', 'DashboardController@index', ['auth']);

// Apenas para visitantes
$router->get('/login', 'AuthController@showLogin', ['guest']);

// Apenas administradores
$router->get('/admin', 'AdminController@index', ['admin']);

// Rate limiting
$router->post('/api/endpoint', 'Controller@method', ['rate_limit']);
```

## 📊 Monitoramento

### Sistema de Logs
```php
// Diferentes níveis de log
Logger::info('User logged in', ['user_id' => $userId]);
Logger::warning('Suspicious activity detected');
Logger::error('Database connection failed');
Logger::debug('Query executed', ['sql' => $query]);
```

### Cache System
```php
// Cache simples
Cache::set('key', $data, 3600); // 1 hora
$data = Cache::get('key');
Cache::delete('key');
```

## 🚀 Performance

### Otimizações Implementadas
- **Índices de Banco**: Otimização de consultas
- **Cache**: Sistema de cache para consultas frequentes
- **Lazy Loading**: Carregamento sob demanda
- **Compressão**: Minificação de assets
- **CDN Ready**: Preparado para CDN

### Métricas de Performance
- Tempo de resposta < 200ms
- Cache hit rate > 80%
- Consultas otimizadas
- Lazy loading de imagens

## 🤝 Contribuição

### Como Contribuir
1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

### Padrões de Código
- PSR-4 Autoloading
- PSR-12 Coding Standards
- Documentação em português
- Testes unitários

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 🆘 Suporte

### Documentação
- [Guia de Instalação](docs/installation.md)
- [Guia de Desenvolvimento](docs/development.md)
- [API Documentation](docs/api.md)

### Contato
- Email: suporte@stopnow.com
- GitHub Issues: [Issues](https://github.com/seu-usuario/stopnow/issues)

## 🔄 Changelog

### v2.0.0 (Atual)
- ✅ Sistema de edição e exclusão de vagas
- ✅ Arquitetura melhorada com middleware
- ✅ Sistema de notificações
- ✅ Sistema de mensagens
- ✅ Sistema de favoritos
- ✅ Busca avançada
- ✅ Sistema de cache
- ✅ Logs estruturados
- ✅ Validação robusta
- ✅ API RESTful

### v1.0.0
- ✅ Sistema básico de vagas
- ✅ Autenticação
- ✅ Dashboard
- ✅ Busca simples