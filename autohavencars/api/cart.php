<?php
require_once '../config/database.php';
require_once '../includes/wishlist_cart.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $carId = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;
    
    if (!$carId) {
        echo json_encode(['success' => false, 'message' => 'Invalid car ID']);
        exit;
    }
    
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    if ($action === 'add') {
        if (addToCart($carId, $userId)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Added to cart',
                'count' => getCartCount($userId),
                'in_cart' => true
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
        }
    } elseif ($action === 'remove') {
        if (removeFromCart($carId, $userId)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Removed from cart',
                'count' => getCartCount($userId),
                'in_cart' => false
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from cart']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>




