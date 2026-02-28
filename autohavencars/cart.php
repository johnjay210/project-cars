<?php
require_once 'config/database.php';
require_once 'includes/wishlist_cart.php';

$pageTitle = 'My Cart';
include 'includes/header.php';

$items = getCartItems();
$totalPrice = array_sum(array_column($items, 'price'));
?>

<main class="cart-page">
    <div class="container">
        <h1><i class="fas fa-shopping-cart"></i> My Cart</h1>
        
        <?php if (empty($items)): ?>
            <div class="no-results">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Add cars to your cart to compare or contact sellers!</p>
                <a href="listings.php" class="btn btn-primary">Browse Cars</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <div class="results-info">
                        <p>You have <strong><?php echo count($items); ?></strong> car(s) in your cart</p>
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
                                    <button class="cart-remove-btn" data-car-id="<?php echo $car['id']; ?>" title="Remove from cart">
                                        <i class="fas fa-times"></i>
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
                                        <button class="btn btn-secondary add-wishlist-btn" data-car-id="<?php echo $car['id']; ?>">
                                            <i class="fas fa-heart"></i> Add to Wishlist
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Cart Summary</h3>
                        <div class="summary-item">
                            <span>Total Cars:</span>
                            <strong><?php echo count($items); ?></strong>
                        </div>
                        <div class="summary-item">
                            <span>Total Value:</span>
                            <strong class="total-price">$<?php echo number_format($totalPrice, 2); ?></strong>
                        </div>
                        <div class="summary-actions">
                            <a href="listings.php" class="btn btn-outline btn-block">Continue Shopping</a>
                            <button class="btn btn-primary btn-block" onclick="alert('Contact sellers individually from car details pages to proceed with purchase.')">
                                <i class="fas fa-envelope"></i> Contact Sellers
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>




