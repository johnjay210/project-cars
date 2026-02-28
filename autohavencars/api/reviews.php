<?php
require_once '../config/database.php';
require_once '../includes/reviews.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
        exit;
    }
    
    $reviewerId = $_SESSION['user_id'];
    $sellerId = isset($_POST['seller_id']) ? (int)$_POST['seller_id'] : 0;
    $carId = isset($_POST['car_id']) && !empty($_POST['car_id']) ? (int)$_POST['car_id'] : null;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    if (!$sellerId || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
        exit;
    }
    
    if ($reviewerId == $sellerId) {
        echo json_encode(['success' => false, 'message' => 'You cannot review yourself']);
        exit;
    }
    
    if (hasUserReviewed($reviewerId, $sellerId, $carId)) {
        echo json_encode(['success' => false, 'message' => 'You have already reviewed this seller']);
        exit;
    }
    
    if (submitReview($reviewerId, $sellerId, $rating, $title, $comment, $carId)) {
        $ratingData = getSellerRating($sellerId);
        echo json_encode([
            'success' => true, 
            'message' => 'Review submitted successfully',
            'rating' => $ratingData
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>






