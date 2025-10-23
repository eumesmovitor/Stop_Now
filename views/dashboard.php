<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary mb-2">
                Olá, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
            </h1>
            <p class="text-gray-600">Gerencie suas vagas e reservas</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-secondary rounded-lg flex items-center justify-center">
                        <i class="fas fa-car text-primary text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Minhas Vagas</p>
                        <p class="text-2xl font-bold text-primary"><?php echo $userStats['total_spots']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-secondary rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-primary text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Reservas</p>
                        <p class="text-2xl font-bold text-primary"><?php echo $userStats['total_bookings']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-secondary rounded-lg flex items-center justify-center">
                        <i class="fas fa-star text-primary text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Avaliações</p>
                        <p class="text-2xl font-bold text-primary"><?php echo $userStats['total_reviews']; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-secondary rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-primary text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Nota Média</p>
                        <p class="text-2xl font-bold text-primary"><?php echo $userStats['avg_rating']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-primary mb-4">Ações Rápidas</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="<?php echo BASE_URL; ?>/list-spot" 
                   class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-plus-circle text-accent text-2xl mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-primary">Anunciar Vaga</h3>
                        <p class="text-sm text-gray-600">Cadastre uma nova vaga</p>
                    </div>
                </a>

                <a href="<?php echo BASE_URL; ?>" 
                   class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-search text-accent text-2xl mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-primary">Buscar Vagas</h3>
                        <p class="text-sm text-gray-600">Encontre vagas disponíveis</p>
                    </div>
                </a>

                <a href="<?php echo BASE_URL; ?>/profile" 
                   class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-user-edit text-accent text-2xl mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-primary">Meu Perfil</h3>
                        <p class="text-sm text-gray-600">Editar informações</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- My Spots -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-primary">Minhas Vagas</h2>
                <a href="<?php echo BASE_URL; ?>/list-spot" 
                   class="bg-accent text-primary px-4 py-2 rounded-lg font-semibold hover:bg-accent-dark transition">
                    <i class="fas fa-plus mr-2"></i>Nova Vaga
                </a>
            </div>
            
            <?php if (empty($mySpots)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-parking text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 mb-4">Você ainda não tem vagas cadastradas</p>
                    <a href="<?php echo BASE_URL; ?>/list-spot" 
                       class="bg-accent text-primary px-6 py-3 rounded-lg font-semibold hover:bg-accent-dark transition">
                        Cadastrar Primeira Vaga
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
                    <?php foreach($mySpots as $spot): ?>
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
                                            <?php echo $spot['total_bookings']; ?> reservas
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="<?php echo BASE_URL; ?>/spot/edit/<?php echo $spot['id']; ?>"
                                               class="inline-flex items-center px-3 py-1 rounded text-sm font-semibold border border-gray-200 hover:bg-gray-50 transition">
                                                <i class="fas fa-edit mr-1"></i>Editar
                                            </a>

                                            <form id="deleteSpotForm-<?php echo $spot['id']; ?>" method="POST" action="<?php echo BASE_URL; ?>/spot/delete" style="display:none;">
                                                <?php echo csrfTokenInput(); ?>
                                                <input type="hidden" name="spot_id" value="<?php echo $spot['id']; ?>">
                                            </form>
                                            <button type="button" onclick="confirmDelete(<?php echo $spot['id']; ?>)"
                                                class="inline-flex items-center px-3 py-1 rounded text-sm font-semibold bg-red-50 text-red-700 hover:bg-red-100 transition">
                                                <i class="fas fa-trash mr-1"></i>Excluir
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-3 text-sm">
                                        Status: <span class="font-semibold <?php echo $spot['status'] === 'active' ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo ucfirst($spot['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Bookings -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-primary mb-4">Minhas Reservas</h2>
            
            <?php if (empty($myBookings)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">Você ainda não fez nenhuma reserva</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-primary">Vaga</th>
                                <th class="text-left py-3 px-4 font-semibold text-primary">Período</th>
                                <th class="text-left py-3 px-4 font-semibold text-primary">Valor</th>
                                <th class="text-left py-3 px-4 font-semibold text-primary">Status</th>
                                <th class="text-left py-3 px-4 font-semibold text-primary">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($myBookings as $booking): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center">
                                            <?php if($booking['spot_image']): ?>
                                                <img src="<?php echo BASE_URL . '/' . $booking['spot_image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($booking['spot_title']); ?>" 
                                                     class="w-12 h-12 object-cover rounded-lg mr-3"
                                                     onerror="this.outerHTML='<div class=\"w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center mr-3\"><i class=\"fas fa-parking text-gray-400\"></i></div>'">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-parking text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="font-semibold text-primary"><?php echo htmlspecialchars($booking['spot_title']); ?></p>
                                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['spot_address']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="text-sm">
                                            <?php echo formatDate($booking['start_date']); ?> - 
                                            <?php echo formatDate($booking['end_date']); ?>
                                        </p>
                                        <p class="text-xs text-gray-600"><?php echo $booking['total_days']; ?> dias</p>
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="font-semibold text-primary"><?php echo formatCurrency($booking['final_price']); ?></p>
                                        <p class="text-xs text-gray-600">Taxa: <?php echo formatCurrency($booking['service_fee']); ?></p>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                            <?php 
                                            switch($booking['booking_status']) {
                                                case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                                case 'active': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'completed': echo 'bg-gray-100 text-gray-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                default: echo 'bg-yellow-100 text-yellow-800';
                                            }
                                            ?>">
                                            <?php echo ucfirst($booking['booking_status']); ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if($booking['has_smart_lock'] && $booking['booking_status'] === 'active'): ?>
                                            <button onclick="unlockSpot(<?php echo $booking['id']; ?>)" 
                                                    class="bg-accent text-primary px-3 py-1 rounded text-sm font-semibold hover:bg-accent-dark transition">
                                                <i class="fas fa-unlock mr-1"></i>Desbloquear
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if($booking['access_code']): ?>
                                            <button onclick="showAccessCode('<?php echo $booking['access_code']; ?>')" 
                                                    class="bg-primary text-white px-3 py-1 rounded text-sm font-semibold hover:bg-dark transition ml-2">
                                                <i class="fas fa-key mr-1"></i>Código
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Received Bookings -->
        <?php if (!empty($receivedBookings)): ?>
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-primary mb-4">Reservas Recebidas</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-primary">Vaga</th>
                            <th class="text-left py-3 px-4 font-semibold text-primary">Locatário</th>
                            <th class="text-left py-3 px-4 font-semibold text-primary">Período</th>
                            <th class="text-left py-3 px-4 font-semibold text-primary">Valor</th>
                            <th class="text-left py-3 px-4 font-semibold text-primary">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($receivedBookings as $booking): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-primary"><?php echo htmlspecialchars($booking['spot_title']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['spot_address']); ?></p>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-primary"><?php echo htmlspecialchars($booking['renter_name']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['renter_email']); ?></p>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="text-sm">
                                        <?php echo formatDate($booking['start_date']); ?> - 
                                        <?php echo formatDate($booking['end_date']); ?>
                                    </p>
                                    <p class="text-xs text-gray-600"><?php echo $booking['total_days']; ?> dias</p>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-primary"><?php echo formatCurrency($booking['final_price']); ?></p>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                        <?php 
                                        switch($booking['booking_status']) {
                                            case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                            case 'active': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'completed': echo 'bg-gray-100 text-gray-800'; break;
                                            case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                            default: echo 'bg-yellow-100 text-yellow-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Access Code Modal -->
<div id="accessCodeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-primary mb-4">Código de Acesso</h3>
        <div class="text-center">
            <div class="text-4xl font-bold text-accent mb-4" id="accessCodeDisplay"></div>
            <p class="text-gray-600 mb-4">Use este código para acessar a vaga</p>
            <button onclick="closeAccessCodeModal()" 
                    class="bg-primary text-white px-6 py-2 rounded-lg font-semibold hover:bg-dark transition">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
function unlockSpot(bookingId) {
    fetch('<?php echo BASE_URL; ?>/booking/unlock/' + bookingId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Fechadura desbloqueada com sucesso!');
        } else {
            alert('Erro ao desbloquear fechadura: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao desbloquear fechadura');
    });
}

// access code modal functions
function showAccessCode(code) {
    document.getElementById('accessCodeDisplay').textContent = code;
    document.getElementById('accessCodeModal').classList.remove('hidden');
    document.getElementById('accessCodeModal').classList.add('flex');
}
function closeAccessCodeModal() {
    document.getElementById('accessCodeModal').classList.add('hidden');
    document.getElementById('accessCodeModal').classList.remove('flex');
}

/* New: confirm delete spot — sends POST via fetch using hidden form (keeps CSRF) */
function confirmDelete(spotId) {
    if (!spotId) return;
    if (!confirm('Tem certeza que deseja excluir esta vaga? Esta ação é irreversível.')) return;

    const form = document.getElementById('deleteSpotForm-' + spotId);
    if (!form) {
        alert('Formulário de exclusão não encontrado.');
        return;
    }

    const url = form.action || '<?php echo BASE_URL; ?>/spot/delete?ajax=1';
    const formData = new FormData(form);

    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, assume success and reload page
            window.location.reload();
            return;
        }
    })
    .then(data => {
        if (data && data.success) {
            // Remove the card from UI
            const card = form.closest('.border');
            if (card) {
                card.style.transition = 'opacity 0.3s ease';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 300);
            }
            
            // Show success message
            showMessage('Vaga excluída com sucesso!', 'success');
        } else if (data && data.message) {
            showMessage('Erro ao excluir vaga: ' + data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Erro:', err);
        showMessage('Erro ao excluir vaga. Tente novamente mais tarde.', 'error');
    });
}

// Helper function to show messages
function showMessage(message, type = 'info') {
    // Create or update message element
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







