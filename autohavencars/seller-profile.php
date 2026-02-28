<?php
require_once 'config/database.php';
require_once 'includes/reviews.php';

if (!isset($_GET['id'])) {
    header('Location: listings.php');
    exit;
}

$sellerId = (int)$_GET['id'];
$conn = getDBConnection();

// Get seller info
$query = "SELECT id, username, email, phone, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $sellerId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: listings.php');
    exit;
}

$seller = $result->fetch_assoc();
$stmt->close();

// Get seller's cars
$query = "SELECT * FROM cars WHERE user_id = ? AND status = 'available' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $sellerId);
$stmt->execute();
$carsResult = $stmt->get_result();
$stmt->close();

// Get rating and reviews
$ratingData = getSellerRating($sellerId);
$reviews = getSellerReviews($sellerId, 20);
$ratingDistribution = getRatingDistribution($sellerId);

$pageTitle = $seller['username'] . ' - Seller Profile';
include 'includes/header.php';
?>

<main class="seller-profile-page">
    <div class="container">
        <div class="seller-header">
            <div class="seller-info">
                <h1><i class="fas fa-user"></i> <?php echo htmlspecialchars($seller['username']); ?></h1>
                <p class="seller-joined">Member since <?php echo date('F Y', strtotime($seller['created_at'])); ?></p>
                
                <?php if ($ratingData['count'] > 0): ?>
                    <div class="seller-rating">
                        <div class="rating-display">
                            <?php echo renderStars($ratingData['average'], 'large'); ?>
                            <div class="rating-text">
                                <span class="rating-value"><?php echo number_format($ratingData['average'], 1); ?></span>
                                <span class="rating-count">(<?php echo $ratingData['count']; ?> review<?php echo $ratingData['count'] != 1 ? 's' : ''; ?>)</span>
                            </div>
                        </div>
                        
                        <div class="rating-breakdown">
                            <h4>Rating Breakdown</h4>
                            <?php 
                            $totalReviews = array_sum($ratingDistribution);
                            for ($i = 5; $i >= 1; $i--): 
                                $percentage = $totalReviews > 0 ? ($ratingDistribution[$i] / $totalReviews) * 100 : 0;
                            ?>
                                <div class="rating-bar-item">
                                    <span><?php echo $i; ?> <i class="fas fa-star"></i></span>
                                    <div class="rating-bar">
                                        <div class="rating-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span><?php echo $ratingDistribution[$i]; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-ratings">
                        <p><i class="fas fa-star"></i> No ratings yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="seller-content">
            <div class="seller-cars-section">
                <h2><i class="fas fa-car"></i> Cars for Sale (<?php echo $carsResult->num_rows; ?>)</h2>
                <?php if ($carsResult->num_rows > 0): ?>
                    <div class="cars-grid">
                        <?php while ($car = $carsResult->fetch_assoc()): ?>
                            <div class="car-card">
                                <div class="car-image">
                                    <?php if ($car['image_path'] && file_exists($car['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                                    <?php else: ?>
                                        <img src="assets/images/placeholder-car.jpg" alt="Car placeholder">
                                    <?php endif; ?>
                                    <span class="car-price">$<?php echo number_format($car['price'], 2); ?></span>
                                </div>
                                <div class="car-info">
                                    <h3><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></h3>
                                    <div class="car-details">
                                        <span><i class="fas fa-tachometer-alt"></i> <?php echo number_format($car['mileage']); ?> miles</span>
                                        <span><i class="fas fa-palette"></i> <?php echo htmlspecialchars($car['color']); ?></span>
                                    </div>
                                    <a href="car-details.php?id=<?php echo $car['id']; ?>" class="btn btn-outline">View Details</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="no-results">This seller has no cars listed at the moment.</p>
                <?php endif; ?>
            </div>
            
            <div class="seller-reviews-section">
                <h2><i class="fas fa-comments"></i> Reviews (<?php echo count($reviews); ?>)</h2>
                
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $sellerId && !hasUserReviewed($_SESSION['user_id'], $sellerId)): ?>
                    <div class="review-form-container">
                        <h3>Write a Review</h3>
                        <form id="review-form" class="review-form">
                            <input type="hidden" name="seller_id" value="<?php echo $sellerId; ?>">
                            
                            <div class="form-group">
                                <label>Rating *</label>
                                <div class="rating-input">
                                    <input type="radio" name="rating" id="rating5" value="5" required>
                                    <label for="rating5" class="star-label"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="rating4" value="4">
                                    <label for="rating4" class="star-label"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="rating3" value="3">
                                    <label for="rating3" class="star-label"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="rating2" value="2">
                                    <label for="rating2" class="star-label"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="rating1" value="1">
                                    <label for="rating1" class="star-label"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="review-title">Title (Optional)</label>
                                <input type="text" id="review-title" name="title" placeholder="Brief summary of your experience">
                            </div>
                            
                            <div class="form-group">
                                <label for="review-comment">Your Review *</label>
                                <textarea id="review-comment" name="comment" rows="5" required placeholder="Share your experience with this seller..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $sellerId): ?>
                    <p class="info-message">You cannot review yourself.</p>
                <?php elseif (isset($_SESSION['user_id']) && hasUserReviewed($_SESSION['user_id'], $sellerId)): ?>
                    <p class="info-message">You have already reviewed this seller.</p>
                <?php else: ?>
                    <p class="info-message"><a href="login.php">Login</a> to write a review.</p>
                <?php endif; ?>
                
                <div class="reviews-list">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong>
                                        <?php if ($review['car_id']): ?>
                                            <span class="review-car">Reviewed for: <?php echo htmlspecialchars($review['year'] . ' ' . $review['make'] . ' ' . $review['model']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="review-meta">
                                        <?php echo renderStars($review['rating']); ?>
                                        <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($review['title'])): ?>
                                    <h4 class="review-title"><?php echo htmlspecialchars($review['title']); ?></h4>
                                <?php endif; ?>
                                
                                <?php if (!empty($review['comment'])): ?>
                                    <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-results">No reviews yet. Be the first to review this seller!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$conn->close();
include 'includes/footer.php';
?>






