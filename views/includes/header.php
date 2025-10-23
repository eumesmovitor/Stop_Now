<?php
// Include necessary files for header
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}
if (!function_exists('csrfTokenInput')) {
    require_once dirname(__DIR__, 2) . '/config/utils.php';
}
if (!class_exists('AuthController')) {
    require_once dirname(__DIR__, 2) . '/controllers/AuthController.php';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'StopNow - Aluguel de Vagas'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#46424d',
                        secondary: '#fed504',
                        dark: '#2a2730',
                        light: '#f5f5f5'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <nav class="bg-primary text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="<?php echo BASE_URL; ?>" class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-secondary rounded-lg flex items-center justify-center">
                        <i class="fas fa-parking text-primary text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold">Stop<span class="text-secondary">Now</span></span>
                </a>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?php echo BASE_URL; ?>" class="hover:text-secondary transition">Início</a>
                    <a href="<?php echo BASE_URL; ?>/search" class="hover:text-secondary transition">Buscar Vagas</a>
                    <a href="<?php echo BASE_URL; ?>#como-funciona" class="hover:text-secondary transition">Como Funciona</a>
                    <a href="<?php echo BASE_URL; ?>#seguranca" class="hover:text-secondary transition">Segurança</a>
                    
                    <?php if(AuthController::isLoggedIn()): ?>
                        <a href="<?php echo BASE_URL; ?>/list-spot" class="hover:text-secondary transition">Anunciar Vaga</a>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 hover:text-secondary transition">
                                <div class="w-8 h-8 bg-secondary rounded-full flex items-center justify-center">
                                    <span class="text-primary font-bold"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></span>
                                </div>
                                <span><?php echo $_SESSION['user_name']; ?></span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                                <a href="<?php echo BASE_URL; ?>/dashboard" class="block px-4 py-2 text-primary hover:bg-secondary hover:text-primary transition">
                                    <i class="fas fa-dashboard mr-2"></i>Dashboard
                                </a>
                                <a href="<?php echo BASE_URL; ?>/logout" class="block px-4 py-2 text-primary hover:bg-secondary hover:text-primary transition">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Sair
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <button onclick="openLoginModal()" class="hover:text-secondary transition">Entrar</button>
                        <button onclick="openRegisterModal()" class="bg-secondary text-primary px-6 py-2 rounded-lg font-semibold hover:bg-yellow-400 transition">
                            Cadastrar
                        </button>
                    <?php endif; ?>
                </div>
                
                <button class="md:hidden text-white" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-dark">
            <div class="px-4 py-3 space-y-3">
                <a href="<?php echo BASE_URL; ?>" class="block hover:text-secondary transition">Início</a>
                <a href="<?php echo BASE_URL; ?>/search" class="block hover:text-secondary transition">Buscar Vagas</a>
                <a href="<?php echo BASE_URL; ?>#como-funciona" class="block hover:text-secondary transition">Como Funciona</a>
                <a href="<?php echo BASE_URL; ?>#seguranca" class="block hover:text-secondary transition">Segurança</a>
                <?php if(AuthController::isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>/list-spot" class="block hover:text-secondary transition">Anunciar Vaga</a>
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="block hover:text-secondary transition">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/logout" class="block hover:text-secondary transition">Sair</a>
                <?php else: ?>
                    <button onclick="openLoginModal()" class="block w-full text-left hover:text-secondary transition">Entrar</button>
                    <button onclick="openRegisterModal()" class="block w-full bg-secondary text-primary px-4 py-2 rounded-lg font-semibold">
                        Cadastrar
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Login Modal -->
    <div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-primary">Entrar</h3>
                <button onclick="closeLoginModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/login" class="space-y-4">
                <?php echo csrfTokenInput(); ?>
                
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">Email</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">Senha</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
                
                <button type="submit" 
                        class="w-full bg-accent text-primary py-3 rounded-lg font-semibold hover:bg-accent-dark transition">
                    <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                </button>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-gray-600">Não tem conta? 
                    <button onclick="closeLoginModal(); openRegisterModal();" class="text-accent hover:text-accent-dark font-semibold">
                        Cadastre-se
                    </button>
                </p>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-primary">Cadastrar</h3>
                <button onclick="closeRegisterModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="<?php echo BASE_URL; ?>/register" class="space-y-4">
                <?php echo csrfTokenInput(); ?>
                
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">Nome Completo *</label>
                    <input type="text" name="name" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">Email *</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">Senha *</label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">Confirmar Senha *</label>
                    <input type="password" name="confirm_password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">Telefone</label>
                    <input type="tel" name="phone" placeholder="(11) 99999-9999"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">CPF</label>
                    <input type="text" name="cpf" placeholder="000.000.000-00"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent">
                </div>
                
                <button type="submit" 
                        class="w-full bg-accent text-primary py-3 rounded-lg font-semibold hover:bg-accent-dark transition">
                    <i class="fas fa-user-plus mr-2"></i>Cadastrar
                </button>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-gray-600">Já tem conta? 
                    <button onclick="closeRegisterModal(); openLoginModal();" class="text-accent hover:text-accent-dark font-semibold">
                        Entrar
                    </button>
                </p>
            </div>
        </div>
    </div>

    <script>
    function openLoginModal() {
        document.getElementById('loginModal').classList.remove('hidden');
        document.getElementById('loginModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeLoginModal() {
        document.getElementById('loginModal').classList.add('hidden');
        document.getElementById('loginModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    function openRegisterModal() {
        document.getElementById('registerModal').classList.remove('hidden');
        document.getElementById('registerModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeRegisterModal() {
        document.getElementById('registerModal').classList.add('hidden');
        document.getElementById('registerModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
    }

    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.id === 'loginModal') {
            closeLoginModal();
        }
        if (e.target.id === 'registerModal') {
            closeRegisterModal();
        }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLoginModal();
            closeRegisterModal();
        }
    });
    </script>
