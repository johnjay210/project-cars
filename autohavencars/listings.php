<?php
require_once 'config/database.php';
require_once 'includes/wishlist_cart.php';
require_once 'includes/location.php';
require_once 'includes/admin_auth.php';

// Track page view
trackPageView('listings');

$pageTitle = 'Browse Cars';
include 'includes/header.php';

$conn = getDBConnection();

// Get filter parameters
$make = isset($_GET['make']) ? $_GET['make'] : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$city = isset($_GET['city']) ? trim($_GET['city']) : '';
$state = isset($_GET['state']) ? trim($_GET['state']) : '';

// Build query
$query = "SELECT c.*, u.username FROM cars c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.status = 'available'";
$params = [];

if (!empty($make)) {
    $query .= " AND c.make = ?";
    $params[] = $make;
}
if ($minPrice > 0) {
    $query .= " AND c.price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice > 0) {
    $query .= " AND c.price <= ?";
    $params[] = $maxPrice;
}
if ($year > 0) {
    $query .= " AND c.year = ?";
    $params[] = $year;
}
if (!empty($city)) {
    $query .= " AND c.city LIKE ?";
    $params[] = '%' . $city . '%';
}
if (!empty($state)) {
    $query .= " AND c.state LIKE ?";
    $params[] = '%' . $state . '%';
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<main class="listings-page">
    <div class="container">
        <h1>Browse All Cars</h1>
        
        <!-- Nearby Search Toggle -->
        <div class="nearby-search-toggle-container">
            <button type="button" id="nearby-search-toggle" class="btn btn-secondary">
                <i class="fas fa-map-marker-alt"></i> Search Nearby Cars
            </button>
        </div>
        
        <!-- Nearby Search Panel -->
        <div id="nearby-search-panel" class="nearby-search-panel">
            <div class="nearby-search-content">
                <h3><i class="fas fa-search-location"></i> Find Cars Near You</h3>
                <form id="search-nearby-form" class="nearby-search-form">
                    <div class="form-group">
                        <button type="button" id="use-current-location" class="btn btn-primary">
                            <i class="fas fa-crosshairs"></i> Use Current Location
                        </button>
                        <span id="location-status" class="location-status"></span>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nearby-city">City</label>
                            <input type="text" id="nearby-city" name="city" placeholder="e.g., New York">
                        </div>
                        <div class="form-group">
                            <label for="nearby-state">State</label>
                            <input type="text" id="nearby-state" name="state" placeholder="e.g., NY">
                        </div>
                        <div class="form-group">
                            <label for="nearby-zip">ZIP Code</label>
                            <input type="text" id="nearby-zip" name="zip_code" placeholder="e.g., 10001">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nearby-latitude">Latitude</label>
                            <input type="number" id="nearby-latitude" name="latitude" step="any" placeholder="e.g., 40.7128">
                        </div>
                        <div class="form-group">
                            <label for="nearby-longitude">Longitude</label>
                            <input type="number" id="nearby-longitude" name="longitude" step="any" placeholder="e.g., -74.0060">
                        </div>
                        <div class="form-group">
                            <label for="nearby-radius">Radius (miles)</label>
                            <select id="nearby-radius" name="radius">
                                <option value="10">10 miles</option>
                                <option value="25" selected>25 miles</option>
                                <option value="50">50 miles</option>
                                <option value="100">100 miles</option>
                                <option value="200">200 miles</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nearby-make">Make (Optional)</label>
                            <select id="nearby-make" name="make">
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
                        </div>
                        <div class="form-group">
                            <label for="nearby-min-price">Min Price</label>
                            <input type="number" id="nearby-min-price" name="min_price" min="0" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label for="nearby-max-price">Max Price</label>
                            <input type="number" id="nearby-max-price" name="max_price" min="0" placeholder="Any">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Nearby
                    </button>
                </form>
                
                <div id="nearby-results" class="nearby-results"></div>
                <div id="nearby-map" class="nearby-map"></div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-section">
            <form action="listings.php" method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="make">Make:</label>
                    <select name="make" id="make">
                        <option value="">All Makes</option>
                        <option value="Toyota" <?php echo $make === 'Toyota' ? 'selected' : ''; ?>>Toyota</option>
                        <option value="Honda" <?php echo $make === 'Honda' ? 'selected' : ''; ?>>Honda</option>
                        <option value="Ford" <?php echo $make === 'Ford' ? 'selected' : ''; ?>>Ford</option>
                        <option value="Tesla" <?php echo $make === 'Tesla' ? 'selected' : ''; ?>>Tesla</option>
                        <option value="BMW" <?php echo $make === 'BMW' ? 'selected' : ''; ?>>BMW</option>
                        <option value="Mercedes-Benz" <?php echo $make === 'Mercedes-Benz' ? 'selected' : ''; ?>>Mercedes-Benz</option>
                        <option value="Audi" <?php echo $make === 'Audi' ? 'selected' : ''; ?>>Audi</option>
                        <option value="Chevrolet" <?php echo $make === 'Chevrolet' ? 'selected' : ''; ?>>Chevrolet</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="min_price">Min Price:</label>
                    <input type="number" name="min_price" id="min_price" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>" min="0" placeholder="0">
                </div>
                <div class="filter-group">
                    <label for="max_price">Max Price:</label>
                    <input type="number" name="max_price" id="max_price" value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>" min="0" placeholder="Any">
                </div>
                <div class="filter-group">
                    <label for="year">Year:</label>
                    <input type="number" name="year" id="year" value="<?php echo $year > 0 ? $year : ''; ?>" min="1900" max="<?php echo date('Y') + 1; ?>" placeholder="Any">
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="listings.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="results-info">
            <p>Found <strong><?php echo $result->num_rows; ?></strong> car(s)</p>
        </div>

        <div class="cars-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($car = $result->fetch_assoc()): ?>
                    <div class="car-card" data-car-id="<?php echo $car['id']; ?>">
                        <div class="car-image">
                            <?php if ($car['image_path'] && file_exists($car['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                            <?php else: ?>
                                <img src="assets/images/placeholder-car.jpg" alt="Car placeholder">
                            <?php endif; ?>
                            <span class="car-price" data-price="<?php echo $car['price']; ?>" data-currency="USD">
                                <?php 
                                require_once 'includes/currency.php';
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
                            <p class="car-description"><?php echo htmlspecialchars(substr($car['description'], 0, 100)) . '...'; ?></p>
                            <a href="car-details.php?id=<?php echo $car['id']; ?>" class="btn btn-outline">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No cars found</h3>
                    <p>Try adjusting your filters or <a href="post-car.php">list your car</a> to get started!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>

