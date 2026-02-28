<?php
require_once 'config/database.php';
require_once 'includes/wishlist_cart.php';
require_once 'includes/admin_auth.php';
require_once 'includes/currency.php';

// Track page view
trackPageView('homepage');

$pageTitle = 'Home';
include 'includes/header.php';

$conn = getDBConnection();

// Get featured cars (latest 6)
$featuredQuery = "SELECT c.*, u.username FROM cars c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.status = 'available' 
                  ORDER BY c.created_at DESC 
                  LIMIT 6";
$featuredResult = $conn->query($featuredQuery);
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Find Your Perfect Car at AutoHavenCars</h1>
            <p>Browse thousands of quality vehicles or sell your car with ease</p>
            <div class="hero-buttons">
                <a href="listings.php" class="btn btn-primary">Browse Cars</a>
                <a href="post-car.php" class="btn btn-secondary">Sell Your Car</a>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <h2>Search for Your Dream Car</h2>
            <form action="listings.php" method="GET" class="search-form">
                <div class="search-grid">
                    <select name="make" id="make">
                        <option value="">All Makes</option>
                        <option value="Toyota">Toyota</option>
                        <option value="Honda">Honda</option>
                        <option value="Ford">Ford</option>
                        <option value="Tesla">Tesla</option>
                        <option value="BMW">BMW</option>
                        <option value="Mercedes-Benz">Mercedes-Benz</option>
                        <option value="Audi">Audi</option>
                        <option value="Chevrolet">Chevrolet</option>
                    </select>
                    <input type="number" name="min_price" placeholder="Min Price" min="0">
                    <input type="number" name="max_price" placeholder="Max Price" min="0">
                    <input type="number" name="year" placeholder="Year" min="1900" max="<?php echo date('Y') + 1; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Featured Cars -->
    <section class="featured-cars">
        <div class="container">
            <h2>Featured Listings</h2>
            <div class="cars-grid">
                <?php if ($featuredResult && $featuredResult->num_rows > 0): ?>
                    <?php while ($car = $featuredResult->fetch_assoc()): ?>
                        <div class="car-card" data-car-id="<?php echo $car['id']; ?>">
                            <div class="car-image">
                                <?php if ($car['image_path'] && file_exists($car['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                                <?php else: ?>
                                    <img src="assets/images/placeholder-car.jpg" alt="Car placeholder">
                                <?php endif; ?>
                                <span class="car-price" data-price="<?php echo $car['price']; ?>" data-currency="USD">
                                <?php 
                                $convertedPrice = convertCurrency($car['price'], 'USD', getCurrentCurrency());
                                echo formatCurrency($convertedPrice, getCurrentCurrency());
                                ?>
                            </span>
                                <div class="car-quick-actions">
                                    <button class="wishlist-btn <?php echo isInWishlist($car['id']) ? 'active' : ''; ?>" data-car-id="<?php echo $car['id']; ?>" title="<?php echo isInWishlist($car['id']) ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <button class="cart-btn <?php echo isInCart($car['id']) ? 'active' : ''; ?>" data-car-id="<?php echo $car['id']; ?>" title="<?php echo isInCart($car['id']) ? 'Remove from cart' : 'Add to cart'; ?>">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="car-info">
                                <h3><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></h3>
                                <div class="car-details">
                                    <span><i class="fas fa-tachometer-alt"></i> <?php echo number_format($car['mileage']); ?> miles</span>
                                    <span><i class="fas fa-palette"></i> <?php echo htmlspecialchars($car['color'] ?? '-'); ?></span>
                                    <span><i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['transmission'] ?? '-'); ?></span>
                                    <?php if (!empty($car['engine_size'])): ?>
                                        <span><i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['engine_size']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($car['drive_type'])): ?>
                                        <span><i class="fas fa-car-side"></i> <?php echo htmlspecialchars($car['drive_type']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($car['condition_status'])): ?>
                                        <span class="condition-badge-small condition-<?php echo $car['condition_status']; ?>">
                                            <i class="fas fa-star"></i> <?php echo ucfirst($car['condition_status']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($car['city']) || !empty($car['state'])): ?>
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(trim(($car['city'] ?? '') . ', ' . ($car['state'] ?? ''), ', ')); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="car-details.php?id=<?php echo $car['id']; ?>" class="btn btn-outline">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-results">No cars available at the moment. Be the first to <a href="post-car.php">list your car</a>!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose-us">
        <div class="container">
            <h2>Why Choose AutoHavenCars?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Trusted Platform</h3>
                    <p>Verified sellers and quality vehicles you can trust</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-dollar-sign"></i>
                    <h3>Best Prices</h3>
                    <p>Competitive pricing and great deals on all vehicles</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-search"></i>
                    <h3>Easy Search</h3>
                    <p>Find exactly what you're looking for with advanced filters</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-headset"></i>
                    <h3>24/7 Support</h3>
                    <p>Our team is here to help you every step of the way</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
$conn->close();
include 'includes/footer.php';
?>

