<?php
// Reviews and Ratings Helper Functions

/**
 * Check if reviews table exists
 */
function reviewsTableExists() {
    $conn = getDBConnection();
    $result = $conn->query("SHOW TABLES LIKE 'reviews'");
    $exists = $result->num_rows > 0;
    $conn->close();
    return $exists;
}

/**
 * Get average rating for a seller
 */
function getSellerRating($sellerId) {
    if (!reviewsTableExists()) {
        return ['average' => 0, 'count' => 0];
    }
    
    $conn = getDBConnection();
    $query = "SELECT AVG(rating) as average, COUNT(*) as count FROM reviews WHERE seller_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $sellerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return [
        'average' => round($row['average'] ?? 0, 1),
        'count' => $row['count'] ?? 0
    ];
}

/**
 * Get reviews for a seller
 */
function getSellerReviews($sellerId, $limit = 10) {
    if (!reviewsTableExists()) {
        return [];
    }
    
    $conn = getDBConnection();
    $query = "SELECT r.*, u.username as reviewer_name, c.make, c.model, c.year 
              FROM reviews r
              JOIN users u ON r.reviewer_id = u.id
              LEFT JOIN cars c ON r.car_id = c.id
              WHERE r.seller_id = ?
              ORDER BY r.created_at DESC
              LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $sellerId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    $stmt->close();
    $conn->close();
    
    return $reviews;
}

/**
 * Check if user has already reviewed seller for a specific car
 */
function hasUserReviewed($reviewerId, $sellerId, $carId = null) {
    if (!reviewsTableExists()) {
        return false;
    }
    
    $conn = getDBConnection();
    if ($carId) {
        $query = "SELECT id FROM reviews WHERE reviewer_id = ? AND seller_id = ? AND car_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iii', $reviewerId, $sellerId, $carId);
    } else {
        $query = "SELECT id FROM reviews WHERE reviewer_id = ? AND seller_id = ? AND car_id IS NULL";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $reviewerId, $sellerId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    $conn->close();
    
    return $exists;
}

/**
 * Submit a review
 */
function submitReview($reviewerId, $sellerId, $rating, $title = '', $comment = '', $carId = null) {
    if (!reviewsTableExists()) {
        return false;
    }
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        return false;
    }
    
    // Check if already reviewed
    if (hasUserReviewed($reviewerId, $sellerId, $carId)) {
        return false;
    }
    
    $conn = getDBConnection();
    $query = "INSERT INTO reviews (reviewer_id, seller_id, car_id, rating, title, comment) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiiiss', $reviewerId, $sellerId, $carId, $rating, $title, $comment);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}

/**
 * Get rating distribution for a seller
 */
function getRatingDistribution($sellerId) {
    if (!reviewsTableExists()) {
        return ['5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0];
    }
    
    $conn = getDBConnection();
    $query = "SELECT rating, COUNT(*) as count FROM reviews WHERE seller_id = ? GROUP BY rating";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $sellerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $distribution = ['5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0];
    while ($row = $result->fetch_assoc()) {
        $distribution[$row['rating']] = $row['count'];
    }
    $stmt->close();
    $conn->close();
    
    return $distribution;
}

/**
 * Generate star rating HTML
 */
function renderStars($rating, $size = 'normal') {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    
    $sizeClass = $size === 'small' ? 'fa-sm' : ($size === 'large' ? 'fa-lg' : '');
    
    $html = '<div class="star-rating">';
    
    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star ' . $sizeClass . '"></i>';
    }
    
    // Half star
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt ' . $sizeClass . '"></i>';
    }
    
    // Empty stars
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star ' . $sizeClass . '"></i>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>






