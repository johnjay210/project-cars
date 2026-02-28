<?php
require_once 'config/database.php';
require_once 'includes/messages.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$conversationId = isset($_GET['conversation']) ? (int)$_GET['conversation'] : 0;
$selectedConversation = null;
$messages = [];
$conversations = getUserConversations($userId);
$unreadCount = getUnreadMessageCount($userId);

if ($conversationId > 0) {
    // Verify user is part of this conversation
    $conn = getDBConnection();
    $query = "SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iii', $conversationId, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $selectedConversation = $result->fetch_assoc();
        $messages = getConversationMessages($conversationId, $userId);
        
        // Get other user info
        $otherUserId = $selectedConversation['user1_id'] == $userId ? $selectedConversation['user2_id'] : $selectedConversation['user1_id'];
        $userQuery = "SELECT username, email FROM users WHERE id = ?";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param('i', $otherUserId);
        $userStmt->execute();
        $otherUser = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
        
        // Get car info if exists
        $carInfo = null;
        if ($selectedConversation['car_id']) {
            $carQuery = "SELECT * FROM cars WHERE id = ?";
            $carStmt = $conn->prepare($carQuery);
            $carStmt->bind_param('i', $selectedConversation['car_id']);
            $carStmt->execute();
            $carInfo = $carStmt->get_result()->fetch_assoc();
            $carStmt->close();
        }
        
        $stmt->close();
    }
    $conn->close();
}

$pageTitle = 'Messages';
include 'includes/header.php';
?>

<main class="messages-page">
    <div class="container">
        <h1><i class="fas fa-comments"></i> Messages</h1>
        
        <div class="messages-container">
            <!-- Conversations List -->
            <div class="conversations-sidebar">
                <div class="conversations-header">
                    <h2>Conversations</h2>
                    <?php if ($unreadCount > 0): ?>
                        <span class="unread-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($conversations)): ?>
                    <div class="no-conversations">
                        <i class="fas fa-inbox"></i>
                        <p>No conversations yet</p>
                        <p class="small">Start a conversation from a car listing!</p>
                    </div>
                <?php else: ?>
                    <div class="conversations-list">
                        <?php foreach ($conversations as $conv): ?>
                            <a href="messages.php?conversation=<?php echo $conv['id']; ?>" 
                               class="conversation-item <?php echo $conversationId == $conv['id'] ? 'active' : ''; ?>">
                                <div class="conversation-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="conversation-info">
                                    <div class="conversation-header-info">
                                        <strong><?php echo htmlspecialchars($conv['other_user_name']); ?></strong>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="unread-count"><?php echo $conv['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($conv['car_id']): ?>
                                        <div class="conversation-car">
                                            <i class="fas fa-car"></i> 
                                            <?php echo htmlspecialchars($conv['year'] . ' ' . $conv['make'] . ' ' . $conv['model']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($conv['last_message']): ?>
                                        <p class="conversation-preview"><?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)); ?>...</p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Messages Area -->
            <div class="messages-area">
                <?php if ($selectedConversation): ?>
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <div class="chat-user-info">
                            <div class="chat-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h3><?php echo htmlspecialchars($otherUser['username']); ?></h3>
                                <?php if ($carInfo): ?>
                                    <p class="chat-car-link">
                                        <a href="car-details.php?id=<?php echo $carInfo['id']; ?>">
                                            <i class="fas fa-car"></i> 
                                            <?php echo htmlspecialchars($carInfo['year'] . ' ' . $carInfo['make'] . ' ' . $carInfo['model']); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages List -->
                    <div class="messages-list" id="messages-list">
                        <?php foreach ($messages as $message): ?>
                            <div class="message-item <?php echo $message['sender_id'] == $userId ? 'sent' : 'received'; ?>">
                                <div class="message-content">
                                    <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                    <span class="message-time"><?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Message Input -->
                    <div class="message-input-container">
                        <form id="message-form" class="message-form">
                            <input type="hidden" name="conversation_id" value="<?php echo $conversationId; ?>">
                            <input type="hidden" name="receiver_id" value="<?php echo $otherUserId; ?>">
                            <input type="text" 
                                   name="message" 
                                   id="message-input" 
                                   class="message-input" 
                                   placeholder="Type your message..." 
                                   required 
                                   autocomplete="off">
                            <button type="submit" class="btn btn-primary send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="no-conversation-selected">
                        <i class="fas fa-comments"></i>
                        <h3>Select a conversation</h3>
                        <p>Choose a conversation from the list to start messaging</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
// Auto-scroll to bottom of messages
document.addEventListener('DOMContentLoaded', function() {
    const messagesList = document.getElementById('messages-list');
    if (messagesList) {
        messagesList.scrollTop = messagesList.scrollHeight;
    }
    
    // Auto-refresh messages every 5 seconds
    if (<?php echo $conversationId > 0 ? 'true' : 'false'; ?>) {
        setInterval(function() {
            refreshMessages(<?php echo $conversationId; ?>);
        }, 5000);
    }
});

// Send message
document.getElementById('message-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'send');
    
    fetch('api/messages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('message-input').value = '';
            refreshMessages(<?php echo $conversationId; ?>);
        } else {
            alert('Error sending message: ' + data.message);
        }
    });
});

function refreshMessages(conversationId) {
    fetch('api/messages.php?conversation=' + conversationId)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.messages) {
            const messagesList = document.getElementById('messages-list');
            const currentScroll = messagesList.scrollTop;
            const isAtBottom = messagesList.scrollHeight - messagesList.clientHeight <= currentScroll + 100;
            
            messagesList.innerHTML = '';
            data.messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message-item ' + (msg.sender_id == <?php echo $userId; ?> ? 'sent' : 'received');
                messageDiv.innerHTML = `
                    <div class="message-content">
                        <p>${msg.message.replace(/\n/g, '<br>')}</p>
                        <span class="message-time">${msg.created_at}</span>
                    </div>
                `;
                messagesList.appendChild(messageDiv);
            });
            
            if (isAtBottom) {
                messagesList.scrollTop = messagesList.scrollHeight;
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>




