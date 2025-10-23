<?php $pageTitle = 'Notificações - StopNow'; ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-primary mb-2">Notificações</h1>
                    <p class="text-gray-600">Mantenha-se atualizado com as últimas atividades</p>
                </div>
                
                <?php if($unreadCount > 0): ?>
                    <button onclick="markAllAsRead()" class="bg-accent text-primary px-4 py-2 rounded-lg font-semibold hover:bg-accent-dark transition">
                        <i class="fas fa-check-double mr-2"></i>Marcar todas como lidas
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <i class="fas fa-bell text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhuma notificação</h3>
                <p class="text-gray-500">Você receberá notificações sobre suas vagas e reservas aqui</p>
            </div>
        <?php else: ?>
            <!-- Notifications List -->
            <div class="space-y-4">
                <?php foreach($notifications as $notification): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 <?php echo !$notification['is_read'] ? 'border-l-4 border-accent' : ''; ?>">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <h3 class="font-bold text-lg text-primary">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </h3>
                                    <?php if(!$notification['is_read']): ?>
                                        <span class="ml-2 w-2 h-2 bg-accent rounded-full"></span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-gray-600 mb-3">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </p>
                                
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?php echo formatDate($notification['created_at']); ?>
                                    
                                    <?php if($notification['type']): ?>
                                        <span class="ml-4 px-2 py-1 bg-gray-100 rounded-full text-xs">
                                            <?php echo ucfirst(str_replace('_', ' ', $notification['type'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if($notification['data']): ?>
                                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                        <pre class="text-sm text-gray-600"><?php echo htmlspecialchars(json_encode(json_decode($notification['data']), JSON_PRETTY_PRINT)); ?></pre>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex space-x-2 ml-4">
                                <?php if(!$notification['is_read']): ?>
                                    <button onclick="markAsRead(<?php echo $notification['id']; ?>)" 
                                            class="p-2 text-gray-400 hover:text-accent transition"
                                            title="Marcar como lida">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <button onclick="deleteNotification(<?php echo $notification['id']; ?>)" 
                                        class="p-2 text-gray-400 hover:text-red-500 transition"
                                        title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch('<?php echo BASE_URL; ?>/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'notification_id=' + notificationId + '&csrf_token=' + '<?php echo $_SESSION['csrf_token']; ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the unread indicator
            const notification = document.querySelector('[onclick="markAsRead(' + notificationId + ')"]').closest('.bg-white');
            notification.classList.remove('border-l-4', 'border-accent');
            notification.querySelector('.w-2.h-2').remove();
            notification.querySelector('[onclick="markAsRead(' + notificationId + ')"]').remove();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function markAllAsRead() {
    fetch('<?php echo BASE_URL; ?>/notifications/mark-all-read', {
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
            document.querySelectorAll('.border-l-4.border-accent').forEach(el => {
                el.classList.remove('border-l-4', 'border-accent');
            });
            document.querySelectorAll('.w-2.h-2').forEach(el => el.remove());
            document.querySelectorAll('[onclick^="markAsRead"]').forEach(el => el.remove());
            
            showMessage('Todas as notificações foram marcadas como lidas', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function deleteNotification(notificationId) {
    if (!confirm('Tem certeza que deseja excluir esta notificação?')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/notifications/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'notification_id=' + notificationId + '&csrf_token=' + '<?php echo $_SESSION['csrf_token']; ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the notification from UI
            const notification = document.querySelector('[onclick="deleteNotification(' + notificationId + ')"]').closest('.bg-white');
            if (notification) {
                notification.style.transition = 'opacity 0.3s ease';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }
            
            showMessage('Notificação excluída', 'success');
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao excluir notificação');
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
