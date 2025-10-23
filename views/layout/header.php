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
                        accent: '#fed504',
                        'accent-dark': '#e5c004',
                        dark: '#2d2a33',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-accent rounded-lg flex items-center justify-center">
                            <span class="text-primary font-bold text-xl">S</span>
                        </div>
                        <span class="text-2xl font-bold text-primary">StopNow</span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="<?php echo BASE_URL; ?>" class="text-gray-700 hover:text-primary transition">Início</a>
                    <a href="<?php echo BASE_URL; ?>/about" class="text-gray-700 hover:text-primary transition">Sobre Nós</a>
                    <a href="<?php echo BASE_URL; ?>/list-spot" class="text-gray-700 hover:text-primary transition">Anunciar Vaga</a>
                </div>

                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>/dashboard" class="text-gray-700 hover:text-primary transition">Dashboard</a>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-primary transition">
                                <div class="w-8 h-8 bg-accent rounded-full flex items-center justify-center">
                                    <span class="text-primary font-semibold text-sm"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></span>
                                </div>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
                                <a href="<?php echo BASE_URL; ?>/dashboard" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Meu Perfil</a>
                                <a href="<?php echo BASE_URL; ?>/logout" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Sair</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login" class="text-gray-700 hover:text-primary transition">Entrar</a>
                        <a href="<?php echo BASE_URL; ?>/register" class="bg-accent hover:bg-accent-dark text-primary font-semibold px-6 py-2 rounded-lg transition">Cadastrar</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
