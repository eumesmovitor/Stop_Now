<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Search Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h1 class="text-3xl font-bold text-primary mb-6">Buscar Vagas</h1>
            
            <form method="GET" action="<?php echo BASE_URL; ?>/search" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Cidade</label>
                        <select name="city" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent">
                            <option value="">Todas as cidades</option>
                            <?php foreach($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city); ?>" <?php echo $city === $city ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Tipo de Vaga</label>
                        <select name="spot_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent">
                            <option value="">Todos os tipos</option>
                            <option value="covered" <?php echo $spotType === 'covered' ? 'selected' : ''; ?>>Coberta</option>
                            <option value="uncovered" <?php echo $spotType === 'uncovered' ? 'selected' : ''; ?>>Descoberta</option>
                            <option value="garage" <?php echo $spotType === 'garage' ? 'selected' : ''; ?>>Garagem</option>
                            <option value="street" <?php echo $spotType === 'street' ? 'selected' : ''; ?>>Rua</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Preço Mínimo (R$)</label>
                        <input type="number" name="price_min" value="<?php echo $priceMin; ?>" 
                               placeholder="0" step="0.01" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Preço Máximo (R$)</label>
                        <input type="number" name="price_max" value="<?php echo $priceMax; ?>" 
                               placeholder="1000" step="0.01" min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">Características</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="features[]" value="covered" <?php echo in_array('covered', $features) ? 'checked' : ''; ?>>
                            <span class="text-sm">Coberta</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="features[]" value="security" <?php echo in_array('security', $features) ? 'checked' : ''; ?>>
                            <span class="text-sm">Segurança</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="features[]" value="camera" <?php echo in_array('camera', $features) ? 'checked' : ''; ?>>
                            <span class="text-sm">Câmeras</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="features[]" value="lighting" <?php echo in_array('lighting', $features) ? 'checked' : ''; ?>>
                            <span class="text-sm">Iluminação</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="features[]" value="electric_charging" <?php echo in_array('electric_charging', $features) ? 'checked' : ''; ?>>
                            <span class="text-sm">Carregamento</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="features[]" value="smart_lock" <?php echo in_array('smart_lock', $features) ? 'checked' : ''; ?>>
                            <span class="text-sm">Acesso Inteligente</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-between items-center">
                    <div>
                        <label class="block text-sm font-semibold text-primary mb-2">Ordenar por</label>
                        <select name="sort" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent">
                            <option value="" <?php echo $sortBy === '' ? 'selected' : ''; ?>>Mais recentes</option>
                            <option value="price" <?php echo $sortBy === 'price' ? 'selected' : ''; ?>>Menor preço</option>
                            <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Melhor avaliação</option>
                            <option value="date" <?php echo $sortBy === 'date' ? 'selected' : ''; ?>>Mais antigos</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-4">
                        <a href="<?php echo BASE_URL; ?>/search/advanced" class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition">
                            Busca Avançada
                        </a>
                        <button type="submit" class="bg-accent text-primary px-6 py-3 rounded-lg font-semibold hover:bg-accent-dark transition">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-primary">
                    <?php echo count($spots); ?> vaga(s) encontrada(s)
                </h2>
                
                <div class="flex space-x-2">
                    <button onclick="toggleView('grid')" id="gridViewBtn" class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-th"></i>
                    </button>
                    <button onclick="toggleView('list')" id="listViewBtn" class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <?php if (empty($spots)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhuma vaga encontrada</h3>
                    <p class="text-gray-500 mb-6">Tente ajustar os filtros de busca</p>
                    <a href="<?php echo BASE_URL; ?>/search" class="bg-accent text-primary px-6 py-3 rounded-lg font-semibold hover:bg-accent-dark transition">
                        Limpar Filtros
                    </a>
                </div>
            <?php else: ?>
                <div id="spotsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach($spots as $spot): ?>
                        <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition h-full flex flex-col">
                            <div class="spot-image-placeholder h-48 bg-gray-200 relative">
                                <?php if($spot['primary_image']): ?>
                                    <img src="<?php echo BASE_URL . '/' . $spot['primary_image']; ?>" 
                                         alt="<?php echo htmlspecialchars($spot['title']); ?>" 
                                         class="w-full h-full object-cover"
                                         onerror="this.style.display='none'; this.closest('.spot-image-placeholder').querySelector('.placeholder').style.display='flex'">
                                    <div class="placeholder absolute inset-0 hidden flex items-center justify-center bg-gray-300">
                                        <i class="fas fa-parking text-6xl text-gray-400"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-parking text-6xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Favorite Button -->
                                <?php if (AuthController::isLoggedIn()): ?>
                                    <button onclick="toggleFavorite(<?php echo $spot['id']; ?>)" 
                                            class="absolute top-2 right-2 p-2 bg-white rounded-full shadow-lg hover:bg-gray-50 transition"
                                            id="favoriteBtn-<?php echo $spot['id']; ?>">
                                        <i class="fas fa-heart text-gray-400 hover:text-red-500 transition"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-4 flex-1 flex flex-col">
                                <div>
                                    <h3 class="font-bold text-lg text-primary mb-2">
                                        <?php echo htmlspecialchars($spot['title']); ?>
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-3">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?php echo htmlspecialchars($spot['city']); ?>, <?php echo htmlspecialchars($spot['state']); ?>
                                    </p>
                                    
                                    <?php if($spot['avg_rating'] > 0): ?>
                                        <div class="flex items-center mb-3">
                                            <div class="flex text-yellow-400">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $spot['avg_rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="ml-2 text-sm text-gray-600"><?php echo number_format($spot['avg_rating'], 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-2">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-2xl font-bold text-primary">
                                            R$ <?php echo number_format($spot['price_daily'], 2, ',', '.'); ?>
                                        </span>
                                        <span class="text-gray-600 text-sm">por dia</span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between mt-auto">
                                        <div class="text-sm text-gray-600">
                                            <?php echo ucfirst($spot['spot_type']); ?>
                                        </div>
                                        <a href="<?php echo BASE_URL; ?>/spot/<?php echo $spot['id']; ?>" 
                                           class="bg-accent text-primary px-4 py-2 rounded-lg font-semibold hover:bg-accent-dark transition">
                                            Ver Detalhes
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                    <div class="mt-8 flex justify-center">
                        <nav class="flex space-x-2">
                            <?php if($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition <?php echo $i === $page ? 'bg-accent text-primary' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if($page < $totalPages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleView(view) {
    const grid = document.getElementById('spotsGrid');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    
    if (view === 'grid') {
        grid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
        gridBtn.classList.add('bg-accent', 'text-primary');
        listBtn.classList.remove('bg-accent', 'text-primary');
    } else {
        grid.className = 'space-y-4';
        listBtn.classList.add('bg-accent', 'text-primary');
        gridBtn.classList.remove('bg-accent', 'text-primary');
    }
}

function toggleFavorite(spotId) {
    if (!<?php echo AuthController::isLoggedIn() ? 'true' : 'false'; ?>) {
        alert('Você precisa estar logado para adicionar aos favoritos');
        return;
    }
    
    const btn = document.getElementById('favoriteBtn-' + spotId);
    const icon = btn.querySelector('i');
    
    fetch('<?php echo BASE_URL; ?>/favorites/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'spot_id=' + spotId + '&csrf_token=' + '<?php echo $_SESSION['csrf_token']; ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'added') {
                icon.classList.remove('text-gray-400');
                icon.classList.add('text-red-500');
            } else {
                icon.classList.remove('text-red-500');
                icon.classList.add('text-gray-400');
            }
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao alterar favorito');
    });
}
</script>