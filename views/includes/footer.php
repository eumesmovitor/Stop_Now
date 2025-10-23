<footer class="bg-primary text-white mt-20">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 bg-secondary rounded-lg flex items-center justify-center">
                            <i class="fas fa-parking text-primary text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold">Stop<span class="text-secondary">Now</span></span>
                    </div>
                    <p class="text-gray-300">Conectando proprietários e motoristas de forma segura e prática.</p>
                </div>
                
                <div>
                    <h3 class="font-bold text-lg mb-4">Links Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-gray-300 hover:text-secondary transition">Início</a></li>
                        <li><a href="<?php echo BASE_URL; ?>#como-funciona" class="text-gray-300 hover:text-secondary transition">Como Funciona</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/list-spot" class="text-gray-300 hover:text-secondary transition">Anunciar Vaga</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold text-lg mb-4">Suporte</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-secondary transition">Central de Ajuda</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-secondary transition">Termos de Uso</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-secondary transition">Política de Privacidade</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold text-lg mb-4">Contato</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><i class="fas fa-envelope mr-2"></i>contato@stopnow.com.br</li>
                        <li><i class="fas fa-phone mr-2"></i>(11) 3000-0000</li>
                        <li class="flex space-x-4 mt-4">
                            <a href="#" class="hover:text-secondary transition"><i class="fab fa-facebook text-2xl"></i></a>
                            <a href="#" class="hover:text-secondary transition"><i class="fab fa-instagram text-2xl"></i></a>
                            <a href="#" class="hover:text-secondary transition"><i class="fab fa-twitter text-2xl"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; <?php echo date('Y'); ?> StopNow. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-primary">Entrar</h2>
                <button onclick="closeLoginModal()" class="text-gray-500 hover:text-primary">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="<?php echo BASE_URL; ?>/login" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                </div>
                
                <button type="submit" class="w-full bg-secondary text-primary py-3 rounded-lg font-semibold hover:bg-yellow-400 transition">
                    Entrar
                </button>
            </form>
            
            <p class="text-center mt-4 text-gray-600">
                Não tem conta? <button onclick="switchToRegister()" class="text-secondary font-semibold hover:underline">Cadastre-se</button>
            </p>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-primary">Cadastrar</h2>
                <button onclick="closeRegisterModal()" class="text-gray-500 hover:text-primary">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form action="<?php echo BASE_URL; ?>/register" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="tel" name="phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                    <input type="text" name="cpf" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input type="password" name="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                </div>
                
                <button type="submit" class="w-full bg-secondary text-primary py-3 rounded-lg font-semibold hover:bg-yellow-400 transition">
                    Cadastrar
                </button>
            </form>
            
            <p class="text-center mt-4 text-gray-600">
                Já tem conta? <button onclick="switchToLogin()" class="text-secondary font-semibold hover:underline">Entrar</button>
            </p>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        }
        
        function openLoginModal() {
            document.getElementById('loginModal').classList.remove('hidden');
        }
        
        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
        }
        
        function openRegisterModal() {
            document.getElementById('registerModal').classList.remove('hidden');
        }
        
        function closeRegisterModal() {
            document.getElementById('registerModal').classList.add('hidden');
        }
        
        function switchToRegister() {
            closeLoginModal();
            openRegisterModal();
        }
        
        function switchToLogin() {
            closeRegisterModal();
            openLoginModal();
        }
    </script>
</body>
</html>
