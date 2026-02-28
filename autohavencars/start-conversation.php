<?php
require_once 'config/database.php';
require_once 'includes/messages.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$receiverId = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$carId = isset($_GET['car']) ? (int)$_GET['car'] : null;

if (!$receiverId || $receiverId == $userId) {
    header('Location: messages.php');
    exit;
}

// Get or create conversation
$conversationId = getOrCreateConversation($userId, $receiverId, $carId);

// Redirect to messages page
header('Location: messages.php?conversation=' . $conversationId);
exit;
?>

