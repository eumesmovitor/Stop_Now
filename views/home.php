<!-- Hero Section -->
<section class="bg-gradient-to-br from-primary via-dark to-primary text-white py-20">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                Encontre a Vaga <span class="text-secondary">Perfeita</span> para seu Veículo
            </h1>
            <p class="text-xl mb-8 text-gray-300">
                Alugue vagas de estacionamento de forma segura, prática e econômica. Conectamos proprietários e motoristas em todo o Brasil.
            </p>
            
            <div class="bg-white rounded-lg p-6 shadow-2xl">
                <form method="GET" action="<?php echo BASE_URL; ?>/search" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <select name="city" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent text-primary">
                        <option value="">Todas as cidades</option>
                        <?php 
                        $spotModel = new ParkingSpot($this->db ?? (new Database())->connect());
                        $cities = $spotModel->getCities();
                        foreach($cities as $cityOption): ?>
                            <option value="<?php echo htmlspecialchars($cityOption); ?>"><?php echo htmlspecialchars($cityOption); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-secondary text-primary px-6 py-3 rounded-lg font-semibold hover:bg-yellow-400 transition">
                        <i class="fas fa-search mr-2"></i>Buscar Vagas
                    </button>
                </form>
                <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>/search" class="text-white hover:text-secondary transition">
                        <i class="fas fa-filter mr-1"></i>Busca Avançada
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Featured Spots -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-primary mb-4">Vagas em Destaque</h2>
            <p class="text-gray-600 text-lg">Confira as melhores opções disponíveis agora</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
            <?php if (empty($spots)): ?>
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-parking text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-bold text-primary mb-2">Nenhuma vaga disponível</h3>
                    <p class="text-gray-600 mb-6">Ainda não há vagas cadastradas no sistema.</p>
                    <?php if(AuthController::isLoggedIn()): ?>
                        <a href="<?php echo BASE_URL; ?>/list-spot" class="bg-accent text-primary px-6 py-3 rounded-lg font-semibold hover:bg-accent-dark transition">
                            <i class="fas fa-plus mr-2"></i>Seja o primeiro a anunciar
                        </a>
                    <?php else: ?>
                        <button onclick="openRegisterModal()" class="bg-accent text-primary px-6 py-3 rounded-lg font-semibold hover:bg-accent-dark transition">
                            <i class="fas fa-plus mr-2"></i>Seja o primeiro a anunciar
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach($spots as $spot): ?>
                <a href="<?php echo BASE_URL; ?>/spot/<?php echo $spot['id']; ?>" class="block bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 group h-full">
                    <div class="flex flex-col h-full">
                        <div class="relative flex-none h-48 bg-gray-200 overflow-hidden">
                            <?php if($spot['primary_image']): ?>
                                <img src="<?php echo BASE_URL . '/' . $spot['primary_image']; ?>" alt="<?php echo htmlspecialchars($spot['title']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" onerror="this.style.display='none'">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center bg-gray-300">
                                    <i class="fas fa-parking text-6xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if($spot['has_smart_lock']): ?>
                                <div class="absolute top-3 right-3 bg-secondary text-primary px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                    <i class="fas fa-lock mr-1"></i>Inteligente
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-xl text-primary mb-2 line-clamp-1"><?php echo htmlspecialchars($spot['title']); ?></h3>
                                <p class="text-gray-600 text-sm mb-4 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2 text-accent"></i>
                                    <?php echo htmlspecialchars($spot['city']); ?>, <?php echo htmlspecialchars($spot['state']); ?>
                                </p>
                                
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <?php 
                                        $rating = $spot['avg_rating'] ?? 0;
                                        for($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <i class="fas fa-star text-sm <?php echo $i <= $rating ? 'text-secondary' : 'text-gray-300'; ?>"></i>
                                        <?php endfor; ?>
                                        <span class="text-sm text-gray-600 ml-2 font-medium"><?php echo number_format($rating, 1); ?></span>
                                    </div>
                                    
                                    <?php if($spot['owner_verified']): ?>
                                        <span class="text-green-600 text-xs font-semibold flex items-center">
                                            <i class="fas fa-check-circle mr-1"></i>Verificado
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <?php if($spot['is_covered']): ?>
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs text-gray-700"><i class="fas fa-shield-alt mr-1"></i>Coberta</span>
                                    <?php endif; ?>
                                    <?php if($spot['has_security']): ?>
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs text-gray-700"><i class="fas fa-user-shield mr-1"></i>Segurança</span>
                                    <?php endif; ?>
                                    <?php if($spot['has_camera']): ?>
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs text-gray-700"><i class="fas fa-video mr-1"></i>Câmera</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <div class="flex items-end justify-between">
                                    <div>
                                        <span class="text-3xl font-bold text-primary">
                                            R$ <?php echo number_format($spot['price_daily'], 2, ',', '.'); ?>
                                        </span>
                                        <span class="text-gray-500 text-sm ml-1">/dia</span>
                                    </div>
                                    <span class="bg-accent text-primary px-4 py-2 rounded-lg text-sm font-bold group-hover:bg-accent-dark transition-colors">
                                        Ver detalhes
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How It Works -->
<section id="como-funciona" class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-primary mb-4">Como Funciona</h2>
            <p class="text-gray-600 text-lg">Simples, rápido e seguro</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="w-20 h-20 bg-secondary rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl text-primary mb-2">1. Busque</h3>
                <p class="text-gray-600">Encontre a vaga ideal próxima ao seu destino</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-secondary rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-check text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl text-primary mb-2">2. Reserve</h3>
                <p class="text-gray-600">Escolha as datas e confirme sua reserva</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-secondary rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-credit-card text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl text-primary mb-2">3. Pague</h3>
                <p class="text-gray-600">Pagamento seguro com proteção garantida</p>
            </div>
            
            <div class="text-center">
                <div class="w-20 h-20 bg-secondary rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-car text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl text-primary mb-2">4. Estacione</h3>
                <p class="text-gray-600">Acesse sua vaga com código ou QR Code</p>
            </div>
        </div>
    </div>
</section>

<!-- Safety Features -->
<section id="seguranca" class="py-16 bg-gradient-to-br from-primary to-dark text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold mb-4">Sua Segurança é Nossa Prioridade</h2>
            <p class="text-gray-300 text-lg">Múltiplas camadas de proteção para locadores e locatários</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-lg p-6">
                <div class="w-16 h-16 bg-secondary rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-shield-alt text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl mb-3">Verificação de Identidade</h3>
                <p class="text-gray-300">Todos os usuários passam por verificação de CPF e documentos para garantir autenticidade.</p>
            </div>
            
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-lg p-6">
                <div class="w-16 h-16 bg-secondary rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-lock text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl mb-3">Pagamento Protegido</h3>
                <p class="text-gray-300">Sistema de escrow retém o pagamento até a conclusão do aluguel, protegendo ambas as partes.</p>
            </div>
            
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-lg p-6">
                <div class="w-16 h-16 bg-secondary rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-star text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl mb-3">Sistema de Avaliações</h3>
                <p class="text-gray-300">Avaliações mútuas criam um histórico de confiança e reputação na plataforma.</p>
            </div>
            
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-lg p-6">
                <div class="w-16 h-16 bg-secondary rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-qrcode text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl mb-3">Acesso Inteligente</h3>
                <p class="text-gray-300">QR Code e códigos temporários para acesso seguro sem necessidade de chaves físicas.</p>
            </div>
            
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-lg p-6">
                <div class="w-16 h-16 bg-secondary rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-file-contract text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl mb-3">Contrato Digital</h3>
                <p class="text-gray-300">Termos claros e contratos digitais protegem os direitos de locadores e locatários.</p>
            </div>
            
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-lg p-6">
                <div class="w-16 h-16 bg-secondary rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-headset text-3xl text-primary"></i>
                </div>
                <h3 class="font-bold text-xl mb-3">Suporte 24/7</h3>
                <p class="text-gray-300">Equipe de suporte disponível a qualquer momento para resolver problemas rapidamente.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-secondary">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-primary mb-4">Pronto para Começar?</h2>
        <p class="text-xl text-gray-700 mb-8">Cadastre-se agora e encontre a vaga perfeita ou comece a ganhar dinheiro com suas vagas ociosas!</p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button onclick="openRegisterModal()" class="bg-primary text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-dark transition">
                <i class="fas fa-user-plus mr-2"></i>Criar Conta Grátis
            </button>
            <?php if(AuthController::isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>/list-spot" class="bg-white text-primary px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition border-2 border-primary">
                    <i class="fas fa-plus-circle mr-2"></i>Anunciar Minha Vaga
                </a>
            <?php else: ?>
                <button onclick="openRegisterModal()" class="bg-white text-primary px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition border-2 border-primary">
                    <i class="fas fa-plus-circle mr-2"></i>Anunciar Minha Vaga
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>


