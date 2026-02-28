<?php
require_once 'config/database.php';
require_once 'includes/wishlist_cart.php';

$pageTitle = 'My Wishlist';
include 'includes/header.php';

$items = getWishlistItems();
?>

<main class="wishlist-page">
    <div class="container">
        <h1><i class="fas fa-heart"></i> My Wishlist</h1>
        
        <?php if (empty($items)): ?>
            <div class="no-results">
                <i class="fas fa-heart"></i>
                <h3>Your wishlist is empty</h3>
                <p>Start adding cars to your wishlist to save them for later!</p>
                <a href="listings.php" class="btn btn-primary">Browse Cars</a>
            </div>
        <?php else: ?>
            <div class="results-info">
                <p>You have <strong><?php echo count($items); ?></strong> car(s) in your wishlist</p>
            </div>
            
            <div class="cars-grid">
                <?php foreach ($items as $car): ?>
                    <div class="car-card" data-car-id="<?php echo $car['id']; ?>">
                        <div class="car-image">
                            <?php if ($car['image_path'] && file_exists($car['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                            <?php else: ?>
                                <img src="assets/images/placeholder-car.jpg" alt="Car placeholder">
                            <?php endif; ?>
                            <span class="car-price">$<?php echo number_format($car['price'], 2); ?></span>
                            <button class="wishlist-btn remove-wishlist active" data-car-id="<?php echo $car['id']; ?>" title="Remove from wishlist">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        <div class="car-info">
                            <h3><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></h3>
                            <div class="car-details">
                                <span><i class="fas fa-tachometer-alt"></i> <?php echo number_format($car['mileage']); ?> miles</span>
                                <span><i class="fas fa-palette"></i> <?php echo htmlspecialchars($car['color']); ?></span>
                                <span><i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['transmission']); ?></span>
                            </div>
                            <div class="car-actions">
                                <a href="car-details.php?id=<?php echo $car['id']; ?>" class="btn btn-outline">View Details</a>
                                <button class="btn btn-primary add-cart-btn" data-car-id="<?php echo $car['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>




