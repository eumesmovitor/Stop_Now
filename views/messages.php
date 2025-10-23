<?php $pageTitle = 'Mensagens - StopNow'; ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-primary mb-2">Mensagens</h1>
                    <p class="text-gray-600">Comunique-se com outros usuários</p>
                </div>
                
                <?php if($unreadCount > 0): ?>
                    <button onclick="markAllAsRead()" class="bg-accent text-primary px-4 py-2 rounded-lg font-semibold hover:bg-accent-dark transition">
                        <i class="fas fa-check-double mr-2"></i>Marcar todas como lidas
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Conversations List -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-primary mb-4">Conversas</h2>
                    
                    <?php if (empty($conversations)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Nenhuma conversa ainda</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach($conversations as $conversation): ?>
                                <a href="<?php echo BASE_URL; ?>/messages/conversation?user_id=<?php echo $conversation['other_user_id']; ?>" 
                                   class="block p-3 rounded-lg hover:bg-gray-50 transition <?php echo $conversation['unread_count'] > 0 ? 'bg-blue-50 border-l-4 border-accent' : ''; ?>">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-primary">
                                                <?php echo htmlspecialchars($conversation['other_user_name']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars($conversation['other_user_email']); ?>
                                            </p>
                                        </div>
                                        
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">
                                                <?php echo formatDate($conversation['last_message_time']); ?>
                                            </p>
                                            <?php if($conversation['unread_count'] > 0): ?>
                                                <span class="inline-block mt-1 px-2 py-1 bg-accent text-primary text-xs rounded-full">
                                                    <?php echo $conversation['unread_count']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Messages Area -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-primary">Selecione uma conversa</h2>
                        <p class="text-gray-600">Escolha uma conversa da lista ao lado para ver as mensagens</p>
                    </div>
                    
                    <div class="p-12 text-center">
                        <i class="fas fa-comment-dots text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-600 mb-2">Nenhuma conversa selecionada</h3>
                        <p class="text-gray-500">Selecione uma conversa para começar a conversar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAllAsRead() {
    fetch('<?php echo BASE_URL; ?>/messages/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'csrf_token=' + '<?php echo $_SESSION['csrf_token']; ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove all unread indicators
            document.querySelectorAll('.bg-blue-50').forEach(el => {
                el.classList.remove('bg-blue-50', 'border-l-4', 'border-accent');
            });
            document.querySelectorAll('.bg-accent').forEach(el => el.remove());
            
            showMessage('Todas as mensagens foram marcadas como lidas', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
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
