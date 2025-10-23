<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold text-primary">Meu Perfil</h1>
                <a href="<?php echo BASE_URL; ?>/profile/edit" class="bg-accent text-primary px-4 py-2 rounded-lg font-semibold hover:bg-accent-dark transition">
                    <i class="fas fa-edit mr-2"></i>Editar Perfil
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-bold text-primary mb-4">Informações Pessoais</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nome Completo</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($userData['name']); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($userData['email']); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Telefone</label>
                            <p class="text-gray-900"><?php echo $userData['phone'] ? htmlspecialchars($userData['phone']) : 'Não informado'; ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">CPF</label>
                            <p class="text-gray-900"><?php echo $userData['cpf'] ? htmlspecialchars($userData['cpf']) : 'Não informado'; ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Status da Conta</label>
                            <p class="text-gray-900">
                                <?php if($userData['is_verified']): ?>
                                    <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Verificada</span>
                                <?php else: ?>
                                    <span class="text-yellow-600"><i class="fas fa-clock mr-1"></i>Pendente de verificação</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Membro desde</label>
                            <p class="text-gray-900"><?php echo date('d/m/Y', strtotime($userData['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-primary mb-4">Estatísticas</h2>
                    
                    <?php 
                    $stats = $this->user->getStats($_SESSION['user_id']);
                    ?>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-primary"><?php echo $stats['total_spots']; ?></div>
                            <div class="text-sm text-gray-600">Vagas Cadastradas</div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-primary"><?php echo $stats['total_bookings']; ?></div>
                            <div class="text-sm text-gray-600">Reservas Feitas</div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-primary"><?php echo $stats['total_reviews']; ?></div>
                            <div class="text-sm text-gray-600">Avaliações</div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <div class="text-2xl font-bold text-primary"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                            <div class="text-sm text-gray-600">Nota Média</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t">
                <h2 class="text-xl font-bold text-primary mb-4">Ações da Conta</h2>
                
                <div class="flex flex-wrap gap-4">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-dark transition">
                        <i class="fas fa-dashboard mr-2"></i>Dashboard
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>/list-spot" class="bg-accent text-primary px-4 py-2 rounded-lg hover:bg-accent-dark transition">
                        <i class="fas fa-plus mr-2"></i>Anunciar Vaga
                    </a>
                    
                    <button onclick="changePassword()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-key mr-2"></i>Alterar Senha
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changePassword() {
    alert('Funcionalidade de alteração de senha será implementada em breve.');
}
</script>