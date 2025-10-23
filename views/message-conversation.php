<?php $pageTitle = 'Conversa - StopNow'; ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="<?php echo BASE_URL; ?>/messages" class="mr-4 text-gray-600 hover:text-primary transition">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-primary">
                            Conversa com <?php echo htmlspecialchars($otherUser['name']); ?>
                        </h1>
                        <p class="text-gray-600"><?php echo htmlspecialchars($otherUser['email']); ?></p>
                    </div>
                </div>
                
                <div class="flex space-x-2">
                    <button onclick="markAllAsRead()" class="bg-gray-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-600 transition">
                        <i class="fas fa-check-double mr-2"></i>Marcar como lidas
                    </button>
                </div>
            </div>
        </div>

        <!-- Messages Container -->
        <div class="bg-white rounded-lg shadow-lg">
            <!-- Messages List -->
            <div id="messagesContainer" class="h-96 overflow-y-auto p-6 space-y-4">
                <?php if (empty($messages)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-comment-dots text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">Nenhuma mensagem ainda</p>
                        <p class="text-sm text-gray-400">Seja o primeiro a enviar uma mensagem!</p>
                    </div>
                <?php else: ?>
                    <?php foreach($messages as $message): ?>
                        <div class="flex <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start'; ?>">
                            <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'bg-accent text-primary' : 'bg-gray-200 text-gray-800'; ?>">
                                <div class="flex items-center mb-1">
                                    <span class="font-semibold text-sm">
                                        <?php echo htmlspecialchars($message['sender_name']); ?>
                                    </span>
                                    <span class="ml-2 text-xs opacity-75">
                                        <?php echo formatDate($message['created_at']); ?>
                                    </span>
                                </div>
                                
                                <?php if($message['subject']): ?>
                                    <p class="font-semibold text-sm mb-1"><?php echo htmlspecialchars($message['subject']); ?></p>
                                <?php endif; ?>
                                
                                <p class="text-sm"><?php echo htmlspecialchars($message['message']); ?></p>
                                
                                <div class="flex justify-between items-center mt-2">
                                    <?php if($message['sender_id'] == $_SESSION['user_id']): ?>
                                        <button onclick="deleteMessage(<?php echo $message['id']; ?>)" 
                                                class="text-xs opacity-75 hover:opacity-100 transition">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="markAsRead(<?php echo $message['id']; ?>)" 
                                                class="text-xs opacity-75 hover:opacity-100 transition">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Message Input -->
            <div class="border-t border-gray-200 p-6">
                <form id="messageForm" class="flex space-x-4">
                    <?php echo csrfTokenInput(); ?>
                    <input type="hidden" name="receiver_id" value="<?php echo $otherUser['id']; ?>">
                    <input type="hidden" name="booking_id" value="<?php echo $_GET['booking_id'] ?? ''; ?>">
                    
                    <div class="flex-1">
                        <input type="text" name="subject" placeholder="Assunto (opcional)" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-2 focus:ring-2 focus:ring-accent focus:border-accent">
                        <textarea name="message" placeholder="Digite sua mensagem..." 
                                  rows="3" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent"></textarea>
                    </div>
                    
                    <button type="submit" class="bg-accent text-primary px-6 py-2 rounded-lg font-semibold hover:bg-accent-dark transition self-end">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-scroll to bottom
function scrollToBottom() {
    const container = document.getElementById('messagesContainer');
    container.scrollTop = container.scrollHeight;
}

// Scroll to bottom on page load
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
});

// Handle form submission
document.getElementById('messageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?php echo BASE_URL; ?>/messages/send', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the page to show the new message
            window.location.reload();
        } else {
            alert('Erro ao enviar mensagem: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao enviar mensagem');
    });
});

function markAsRead(messageId) {
    fetch('<?php echo BASE_URL; ?>/messages/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'message_id=' + messageId + '&csrf_token=' + '<?php echo $_SESSION['csrf_token']; ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the message from UI or mark as read
            const message = document.querySelector('[onclick="markAsRead(' + messageId + ')"]').closest('.flex');
            if (message) {
                message.style.opacity = '0.7';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

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
            showMessage('Todas as mensagens foram marcadas como lidas', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function deleteMessage(messageId) {
    if (!confirm('Tem certeza que deseja excluir esta mensagem?')) {
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/messages/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'message_id=' + messageId + '&csrf_token=' + '<?php echo $_SESSION['csrf_token']; ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the message from UI
            const message = document.querySelector('[onclick="deleteMessage(' + messageId + ')"]').closest('.flex');
            if (message) {
                message.style.transition = 'opacity 0.3s ease';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            }
            
            showMessage('Mensagem excluída', 'success');
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao excluir mensagem');
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
