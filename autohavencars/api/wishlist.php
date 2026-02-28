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
        if (addToWishlist($carId, $userId)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Added to wishlist',
                'count' => getWishlistCount($userId),
                'in_wishlist' => true
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist (database not ready)']);
        }
    } elseif ($action === 'remove') {
        if (removeFromWishlist($carId, $userId)) {
            echo json_encode([
                'success' => true, 
                'message' => 'Removed from wishlist',
                'count' => getWishlistCount($userId),
                'in_wishlist' => false
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist (database not ready)']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>




