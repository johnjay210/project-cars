<?php
// Messages Helper Functions

/**
 * Check if messages tables exist
 */
function messagesTablesExist() {
    $conn = getDBConnection();
    $result = $conn->query("SHOW TABLES LIKE 'conversations'");
    $exists = $result->num_rows > 0;
    $conn->close();
    return $exists;
}

/**
 * Get or create conversation between two users about a car
 */
function getOrCreateConversation($user1Id, $user2Id, $carId = null) {
    if (!messagesTablesExist()) {
        return false;
    }
    $conn = getDBConnection();
    
    // Check if conversation exists
    $query = "SELECT id FROM conversations 
              WHERE ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
              AND (car_id = ? OR (? IS NULL AND car_id IS NULL))";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiiiii', $user1Id, $user2Id, $user2Id, $user1Id, $carId, $carId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $conversation = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $conversation['id'];
    }
    
    // Create new conversation
    $query = "INSERT INTO conversations (user1_id, user2_id, car_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iii', $user1Id, $user2Id, $carId);
    $stmt->execute();
    $conversationId = $conn->insert_id;
    $stmt->close();
    $conn->close();
    
    return $conversationId;
}

/**
 * Send a message
 */
function sendMessage($senderId, $receiverId, $message, $carId = null) {
    if (!messagesTablesExist()) {
        return false;
    }
    $conn = getDBConnection();
    
    // Get or create conversation
    $conversationId = getOrCreateConversation($senderId, $receiverId, $carId);
    
    // Insert message
    $query = "INSERT INTO messages (conversation_id, sender_id, receiver_id, car_id, message) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiiis', $conversationId, $senderId, $receiverId, $carId, $message);
    $result = $stmt->execute();
    $messageId = $conn->insert_id;
    
    // Update conversation timestamp
    $updateQuery = "UPDATE conversations SET last_message_at = CURRENT_TIMESTAMP WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('i', $conversationId);
    $updateStmt->execute();
    $updateStmt->close();
    
    $stmt->close();
    $conn->close();
    
    return $result ? $messageId : false;
}

/**
 * Get conversations for a user
 */
function getUserConversations($userId) {
    if (!messagesTablesExist()) {
        return [];
    }
    $conn = getDBConnection();
    
    $query = "SELECT c.*, 
              u1.username as user1_name, u1.id as user1_id,
              u2.username as user2_name, u2.id as user2_id,
              car.make, car.model, car.year, car.id as car_id,
              (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.receiver_id = ? AND m.is_read = 0) as unread_count,
              (SELECT message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message
              FROM conversations c
              JOIN users u1 ON c.user1_id = u1.id
              JOIN users u2 ON c.user2_id = u2.id
              LEFT JOIN cars car ON c.car_id = car.id
              WHERE c.user1_id = ? OR c.user2_id = ?
              ORDER BY c.last_message_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iii', $userId, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $conversations = [];
    while ($row = $result->fetch_assoc()) {
        // Determine the other user
        if ($row['user1_id'] == $userId) {
            $row['other_user_id'] = $row['user2_id'];
            $row['other_user_name'] = $row['user2_name'];
        } else {
            $row['other_user_id'] = $row['user1_id'];
            $row['other_user_name'] = $row['user1_name'];
        }
        $conversations[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $conversations;
}

/**
 * Get messages for a conversation
 */
function getConversationMessages($conversationId, $userId) {
    if (!messagesTablesExist()) {
        return [];
    }
    $conn = getDBConnection();
    
    // Mark messages as read
    $updateQuery = "UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('ii', $conversationId, $userId);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Get messages
    $query = "SELECT m.*, u.username as sender_name 
              FROM messages m
              JOIN users u ON m.sender_id = u.id
              WHERE m.conversation_id = ?
              ORDER BY m.created_at ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $conversationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $messages;
}

/**
 * Get unread message count for user
 */
function getUnreadMessageCount($userId) {
    if (!messagesTablesExist()) {
        return 0;
    }
    $conn = getDBConnection();
    $query = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    $conn->close();
    return $count;
}
?>

