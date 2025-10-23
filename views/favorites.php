<?php $pageTitle = 'Meus Favoritos - StopNow'; ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary mb-2">Meus Favoritos</h1>
            <p class="text-gray-600">Vagas que você salvou para mais tarde</p>
        </div>

        <?php if (empty($favorites)): ?>
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhum favorito ainda</h3>
                <p class="text-gray-500 mb-6">Comece a explorar vagas e adicione suas favoritas</p>
                <a href="<?php echo BASE_URL; ?>/search" class="bg-accent text-primary px-6 py-3 rounded-lg font-semibold hover:bg-accent-dark transition">
                    <i class="fas fa-search mr-2"></i>Buscar Vagas
                </a>
            </div>
        <?php else: ?>
            <!-- Favorites Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($favorites as $spot): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition h-full flex flex-col">
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
                            
                            <!-- Remove from Favorites Button -->
                            <button onclick="removeFavorite(<?php echo $spot['id']; ?>)" 
                                    class="absolute top-2 right-2 p-2 bg-white rounded-full shadow-lg hover:bg-red-50 transition"
                                    title="Remover dos favoritos">
                                <i class="fas fa-heart text-red-500"></i>
                            </button>
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
                                    <div class="flex space-x-2">
                                        <a href="<?php echo BASE_URL; ?>/spot/<?php echo $spot['id']; ?>" 
                                           class="bg-accent text-primary px-4 py-2 rounded-lg font-semibold hover:bg-accent-dark transition">
                                            Ver Detalhes
                                        </a>
                                    </div>
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
                            <a href="?page=<?php echo $page - 1; ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition <?php echo $i === $page ? 'bg-accent text-primary' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" 
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

<script>
function removeFavorite(spotId) {
    if (!confirm('Tem certeza que deseja remover esta vaga dos favoritos?')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/favorites/remove', {
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
            // Remove the card from UI
            const card = document.querySelector('[data-spot-id="' + spotId + '"]') || 
                        document.querySelector('button[onclick="removeFavorite(' + spotId + ')"]').closest('.bg-white');
            if (card) {
                card.style.transition = 'opacity 0.3s ease';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
            
            // Show success message
            showMessage('Vaga removida dos favoritos', 'success');
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao remover dos favoritos');
    });
}

// Helper function to show messages
function showMessage(message, type = 'info') {
    let messageEl = document.getElementById('message-toast');
    if (!messageEl) {
        messageEl = document.createElement('div');
        messageEl.id = 'message-toast';
        messageEl.className = 'fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300';
        document.body.appendChild(messageEl);
    }
    
    messageEl.textContent = message;
    messageEl.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (messageEl) {
            messageEl.style.opacity = '0';
            setTimeout(() => messageEl.remove(), 300);
        }
    }, 3000);
}
</script>
