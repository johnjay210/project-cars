<?php
require_once '../config/database.php';
require_once '../includes/messages.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'send') {
        $receiverId = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
        $message = trim($_POST['message'] ?? '');
        $carId = isset($_POST['car_id']) ? (int)$_POST['car_id'] : null;
        
        if (!$receiverId || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }
        
        if (sendMessage($userId, $receiverId, $message, $carId)) {
            echo json_encode(['success' => true, 'message' => 'Message sent']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['conversation'])) {
    $conversationId = (int)$_GET['conversation'];
    
    // Verify user is part of conversation
    $conn = getDBConnection();
    $query = "SELECT * FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iii', $conversationId, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $messages = getConversationMessages($conversationId, $userId);
        
        // Format messages for JSON
        $formattedMessages = [];
        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'id' => $msg['id'],
                'sender_id' => $msg['sender_id'],
                'message' => htmlspecialchars($msg['message']),
                'created_at' => date('M d, Y h:i A', strtotime($msg['created_at']))
            ];
        }
        
        echo json_encode(['success' => true, 'messages' => $formattedMessages]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Conversation not found']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>




