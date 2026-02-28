<?php
require_once 'config/database.php';
require_once 'includes/wishlist_cart.php';
require_once 'includes/reviews.php';
require_once 'includes/admin_auth.php';
require_once 'includes/car_images.php';
require_once 'includes/currency.php';

// Track page view
trackPageView('car_details');

$conn = getDBConnection();

if (!isset($_GET['id'])) {
    header('Location: listings.php');
    exit;
}

$carId = (int)$_GET['id'];
$query = "SELECT c.*, u.username, u.email, u.phone FROM cars c 
          JOIN users u ON c.user_id = u.id 
          WHERE c.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $carId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: listings.php');
    exit;
}

$car = $result->fetch_assoc();
$pageTitle = $car['year'] . ' ' . $car['make'] . ' ' . $car['model'];
include 'includes/header.php';
?>

<main class="car-details-page">
    <div class="container">
        <a href="listings.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Listings</a>
        
        <div class="car-details-container">
            <!-- Car Images Gallery -->
            <div class="car-images-section">
                <?php 
                $carImages = getCarImages($carId);
                
                // If no images in car_images table but main image exists, add it
                if (empty($carImages) && !empty($car['image_path']) && file_exists($car['image_path'])) {
                    $carImages = [['image_path' => $car['image_path'], 'image_type' => 'exterior', 'display_order' => 0]];
                }
                
                if (!empty($carImages)): 
                    // Group images by type
                    $imagesByType = [];
                    foreach ($carImages as $img) {
                        $type = $img['image_type'] ?? 'exterior';
                        if (!isset($imagesByType[$type])) {
                            $imagesByType[$type] = [];
                        }
                        $imagesByType[$type][] = $img;
                    }
                ?>
                    <div class="car-image-gallery">
                        <div class="main-image-container">
                            <?php 
                            $mainImage = $carImages[0];
                            $mainImagePath = $mainImage['image_path'];
                            ?>
                            <img id="main-car-image" src="<?php echo htmlspecialchars($mainImagePath); ?>" 
                                 alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>"
                                 onclick="openImageModal('<?php echo htmlspecialchars($mainImagePath); ?>')">
                            <div class="image-counter">
                                <i class="fas fa-images"></i> 
                                <span id="current-image-num">1</span> / <span id="total-images"><?php echo count($carImages); ?></span>
                            </div>
                        </div>
                        
                        <?php if (count($carImages) > 1): ?>
                            <div class="image-thumbnails">
                                <?php foreach ($carImages as $index => $img): ?>
                                    <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                         onclick="changeMainImage('<?php echo htmlspecialchars($img['image_path']); ?>', <?php echo $index + 1; ?>, this)">
                                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($img['image_type'] ?? 'car image'); ?>">
                                        <span class="thumbnail-type"><?php echo ucfirst($img['image_type'] ?? 'exterior'); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($imagesByType)): ?>
                            <div class="image-categories">
                                <?php foreach ($imagesByType as $type => $images): ?>
                                    <div class="image-category">
                                        <h4><i class="fas fa-<?php echo $type === 'interior' ? 'couch' : ($type === 'engine' ? 'cog' : 'car'); ?>"></i> 
                                            <?php echo ucfirst($type); ?> (<?php echo count($images); ?>)</h4>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($car['image_path'] && file_exists($car['image_path'])): ?>
                    <div class="car-image-gallery">
                        <div class="main-image-container">
                            <img id="main-car-image" src="<?php echo htmlspecialchars($car['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>"
                                 onclick="openImageModal('<?php echo htmlspecialchars($car['image_path']); ?>')">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="car-image-gallery">
                        <div class="main-image-container">
                            <img src="assets/images/placeholder-car.jpg" alt="Car placeholder">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="car-details-info">
                <h1><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></h1>
                <div class="price-section">
                    <span class="price" data-price="<?php echo $car['price']; ?>" data-currency="USD">
                        <?php 
                        $convertedPrice = convertCurrency($car['price'], 'USD', getCurrentCurrency());
                        echo formatCurrency($convertedPrice, getCurrentCurrency());
                        ?>
                    </span>
                    <span class="status-badge <?php echo $car['status']; ?>"><?php echo ucfirst($car['status']); ?></span>
                </div>
                
                <div class="car-action-buttons">
                    <button class="btn btn-primary wishlist-btn-detail <?php echo isInWishlist($car['id']) ? 'active' : ''; ?>" data-car-id="<?php echo $car['id']; ?>">
                        <i class="fas fa-heart"></i> 
                        <span><?php echo isInWishlist($car['id']) ? 'Remove from Wishlist' : 'Add to Wishlist'; ?></span>
                    </button>
                    <button class="btn btn-secondary cart-btn-detail <?php echo isInCart($car['id']) ? 'active' : ''; ?>" data-car-id="<?php echo $car['id']; ?>">
                        <i class="fas fa-shopping-cart"></i> 
                        <span><?php echo isInCart($car['id']) ? 'Remove from Cart' : 'Add to Cart'; ?></span>
                    </button>
                </div>
                
                <div class="specifications">
                    <h3><i class="fas fa-info-circle"></i> Specifications</h3>
                    <div class="spec-grid">
                        <div class="spec-item">
                            <i class="fas fa-calendar"></i>
                            <span><strong>Year:</strong> <?php echo htmlspecialchars($car['year']); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <span><strong>Mileage:</strong> <?php echo number_format($car['mileage']); ?> miles</span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-palette"></i>
                            <span><strong>Color:</strong> <?php echo htmlspecialchars($car['color'] ?? '-'); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-gas-pump"></i>
                            <span><strong>Fuel Type:</strong> <?php echo htmlspecialchars($car['fuel_type'] ?? '-'); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-cog"></i>
                            <span><strong>Transmission:</strong> <?php echo htmlspecialchars($car['transmission'] ?? '-'); ?></span>
                        </div>
                        <?php if (!empty($car['engine_size'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-cog"></i>
                                <span><strong>Engine Size:</strong> <?php echo htmlspecialchars($car['engine_size']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($car['engine_type'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-tools"></i>
                                <span><strong>Engine Type:</strong> <?php echo htmlspecialchars($car['engine_type']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($car['doors'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-door-open"></i>
                                <span><strong>Doors:</strong> <?php echo $car['doors']; ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($car['seats'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-users"></i>
                                <span><strong>Seats:</strong> <?php echo $car['seats']; ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($car['drive_type'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-car-side"></i>
                                <span><strong>Drive Type:</strong> 
                                    <?php 
                                    $driveTypes = ['FWD' => 'Front-Wheel Drive', 'RWD' => 'Rear-Wheel Drive', 'AWD' => 'All-Wheel Drive', '4WD' => 'Four-Wheel Drive'];
                                    echo htmlspecialchars($driveTypes[$car['drive_type']] ?? $car['drive_type']);
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($car['condition_status'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-star"></i>
                                <span><strong>Condition:</strong> 
                                    <span class="condition-badge condition-<?php echo $car['condition_status']; ?>">
                                        <?php echo ucfirst($car['condition_status']); ?>
                                    </span>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($car['previous_owners'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-user-friends"></i>
                                <span><strong>Previous Owners:</strong> <?php echo $car['previous_owners']; ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($car['accident_history']) && $car['accident_history'] !== 'none'): ?>
                            <div class="spec-item">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span><strong>Accident History:</strong> 
                                    <span class="accident-badge accident-<?php echo $car['accident_history']; ?>">
                                        <?php echo ucfirst($car['accident_history']); ?>
                                    </span>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($car['service_history'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-wrench"></i>
                                <span><strong>Service History:</strong> <span class="service-badge">Available</span></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($car['vin_number'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-barcode"></i>
                                <span><strong>VIN:</strong> <?php echo htmlspecialchars($car['vin_number']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="spec-item">
                            <i class="fas fa-user"></i>
                            <span><strong>Seller:</strong> 
                                <a href="seller-profile.php?id=<?php echo $car['user_id']; ?>" class="seller-link">
                                    <?php echo htmlspecialchars($car['username']); ?>
                                </a>
                                <?php 
                                $sellerRating = getSellerRating($car['user_id']);
                                if ($sellerRating['count'] > 0): 
                                ?>
                                    <span class="seller-rating-badge">
                                        <?php echo renderStars($sellerRating['average'], 'small'); ?>
                                        <span>(<?php echo $sellerRating['count']; ?>)</span>
                                    </span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if (!empty($car['city']) || !empty($car['state'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><strong>Location:</strong> 
                                    <?php 
                                    $locationParts = array_filter([$car['city'], $car['state'], $car['zip_code']]);
                                    echo htmlspecialchars(implode(', ', $locationParts));
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($car['features'])): ?>
                    <div class="features-section">
                        <h3><i class="fas fa-list"></i> Features & Amenities</h3>
                        <div class="features-list">
                            <?php 
                            $features = explode("\n", $car['features']);
                            foreach ($features as $feature):
                                $feature = trim($feature);
                                if (!empty($feature)):
                            ?>
                                <span class="feature-tag">
                                    <i class="fas fa-check"></i> <?php echo htmlspecialchars($feature); ?>
                                </span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($car['city']) || !empty($car['address'])): ?>
                    <div class="location-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Location</h3>
                        <div class="location-info">
                            <?php if (!empty($car['address'])): ?>
                                <p><i class="fas fa-home"></i> <strong>Address:</strong> <?php echo htmlspecialchars($car['address']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($car['city'])): ?>
                                <p><i class="fas fa-city"></i> <strong>City:</strong> <?php echo htmlspecialchars($car['city']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($car['state'])): ?>
                                <p><i class="fas fa-map"></i> <strong>State:</strong> <?php echo htmlspecialchars($car['state']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($car['zip_code'])): ?>
                                <p><i class="fas fa-mail-bulk"></i> <strong>ZIP Code:</strong> <?php echo htmlspecialchars($car['zip_code']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ((!empty($car['latitude']) && !empty($car['longitude'])) || (!empty($car['city']) && !empty($car['state']))): ?>
                                <div class="map-container" id="map-container">
                                    <div id="map" style="width: 100%; height: 300px; border-radius: 0.5rem; margin-top: 1rem;"></div>
                                    <input type="hidden" id="car-latitude" value="<?php echo htmlspecialchars($car['latitude'] ?? ''); ?>">
                                    <input type="hidden" id="car-longitude" value="<?php echo htmlspecialchars($car['longitude'] ?? ''); ?>">
                                    <input type="hidden" id="car-city" value="<?php echo htmlspecialchars($car['city'] ?? ''); ?>">
                                    <input type="hidden" id="car-state" value="<?php echo htmlspecialchars($car['state'] ?? ''); ?>">
                                    <input type="hidden" id="car-address" value="<?php echo htmlspecialchars($car['address'] ?? ''); ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="description">
                    <h3><i class="fas fa-file-alt"></i> Full Description</h3>
                    <?php if (!empty($car['description'])): ?>
                        <div class="description-content">
                            <?php echo nl2br(htmlspecialchars($car['description'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="no-description">No description provided by the seller.</p>
                    <?php endif; ?>
                </div>
                
                <div class="contact-seller">
                    <h3><i class="fas fa-user"></i> Seller Information</h3>
                    <div class="seller-card">
                        <div class="seller-header">
                            <h4>
                                <a href="seller-profile.php?id=<?php echo $car['user_id']; ?>" class="seller-link">
                                    <?php echo htmlspecialchars($car['username']); ?>
                                </a>
                                <?php 
                                $sellerRating = getSellerRating($car['user_id']);
                                if ($sellerRating['count'] > 0): 
                                ?>
                                    <span class="seller-rating-badge">
                                        <?php echo renderStars($sellerRating['average'], 'small'); ?>
                                        <span>(<?php echo $sellerRating['count']; ?>)</span>
                                    </span>
                                <?php endif; ?>
                            </h4>
                        </div>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <strong>Email:</strong>
                                    <a href="mailto:<?php echo htmlspecialchars($car['email']); ?>">
                                        <?php echo htmlspecialchars($car['email']); ?>
                                    </a>
                                </div>
                            </div>
                            <?php if ($car['phone']): ?>
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <div>
                                        <strong>Phone:</strong>
                                        <a href="tel:<?php echo htmlspecialchars($car['phone']); ?>">
                                            <?php echo htmlspecialchars($car['phone']); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $car['user_id']): ?>
                        <div class="contact-actions">
                            <a href="messages.php?start=<?php echo $car['user_id']; ?>&car=<?php echo $car['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-comments"></i> Send Message
                            </a>
                            <a href="seller-profile.php?id=<?php echo $car['user_id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-user"></i> View Seller Profile
                            </a>
                        </div>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <div class="contact-actions">
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login to Message Seller
                            </a>
                            <a href="seller-profile.php?id=<?php echo $car['user_id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-user"></i> View Seller Profile
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Similar Cars for Comparison -->
        <?php 
        $similarCars = getSimilarCars($carId, $car['make'], $car['model'], $car['year'], 4);
        if (!empty($similarCars)): 
        ?>
            <div class="similar-cars-section">
                <h2><i class="fas fa-balance-scale"></i> Compare Similar Cars</h2>
                <p class="section-description">Compare this car with similar listings to make the best decision</p>
                
                <div class="comparison-container">
                    <div class="comparison-table-wrapper">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <th class="current-car">
                                        <strong><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></strong>
                                        <a href="car-details.php?id=<?php echo $car['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    </th>
                                    <?php foreach ($similarCars as $similarCar): ?>
                                        <th>
                                            <strong><?php echo htmlspecialchars($similarCar['year'] . ' ' . $similarCar['make'] . ' ' . $similarCar['model']); ?></strong>
                                            <a href="car-details.php?id=<?php echo $similarCar['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Price</strong></td>
                                    <td class="current-car" data-price="<?php echo $car['price']; ?>" data-currency="USD">
                                        <?php 
                                        $convertedPrice = convertCurrency($car['price'], 'USD', getCurrentCurrency());
                                        echo formatCurrency($convertedPrice, getCurrentCurrency());
                                        ?>
                                    </td>
                                    <?php foreach ($similarCars as $similarCar): ?>
                                        <td data-price="<?php echo $similarCar['price']; ?>" data-currency="USD">
                                            <?php 
                                            $convertedPrice = convertCurrency($similarCar['price'], 'USD', getCurrentCurrency());
                                            echo formatCurrency($convertedPrice, getCurrentCurrency());
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td><strong>Year</strong></td>
                                    <td class="current-car"><?php echo $car['year']; ?></td>
                                    <?php foreach ($similarCars as $similarCar): ?>
                                        <td><?php echo $similarCar['year']; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td><strong>Mileage</strong></td>
                                    <td class="current-car"><?php echo number_format($car['mileage']); ?> miles</td>
                                    <?php foreach ($similarCars as $similarCar): ?>
                                        <td><?php echo number_format($similarCar['mileage']); ?> miles</td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td><strong>Color</strong></td>
                                    <td class="current-car"><?php echo htmlspecialchars($car['color'] ?? '-'); ?></td>
                                    <?php foreach ($similarCars as $similarCar): ?>
                                        <td><?php echo htmlspecialchars($similarCar['color'] ?? '-'); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td><strong>Fuel Type</strong></td>
                                    <td class="current-car"><?php echo htmlspecialchars($car['fuel_type'] ?? '-'); ?></td>
                                    <?php foreach ($similarCars as $similarCar): ?>
                                        <td><?php echo htmlspecialchars($similarCar['fuel_type'] ?? '-'); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td><strong>Transmission</strong></td>
                                    <td class="current-car"><?php echo htmlspecialchars($car['transmission'] ?? '-'); ?></td>
                                    <?php foreach ($similarCars as $similarCar): ?>
                                        <td><?php echo htmlspecialchars($similarCar['transmission'] ?? '-'); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td><strong>Location</strong></td>
                                    <td class="current-car">
                                        <?php 
                                        $locationParts = array_filter([$car['city'], $car['state']]);
                                        echo !empty($locationParts) ? htmlspecialchars(implode(', ', $locationParts)) : '-';
                                        ?>
                                    </td>
                                    <?php foreach ($similarCars as $similarCar): ?>
                                        <td>
                                            <?php 
                                            $locParts = array_filter([$similarCar['city'] ?? '', $similarCar['state'] ?? '']);
                                            echo !empty($locParts) ? htmlspecialchars(implode(', ', $locParts)) : '-';
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Image Modal -->
<div id="imageModal" class="image-modal" onclick="closeImageModal()">
    <span class="modal-close">&times;</span>
    <img class="modal-image" id="modalImage" src="" alt="Car image">
    <div class="modal-nav">
        <button class="modal-nav-btn" onclick="event.stopPropagation(); navigateImage(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="modal-nav-btn" onclick="event.stopPropagation(); navigateImage(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

<script>
// Image gallery functionality
let currentImageIndex = 0;
let allImages = <?php echo json_encode(array_map(function($img) { return $img['image_path']; }, $carImages ?: [])); ?>;
if (allImages.length === 0 && '<?php echo $car['image_path'] ?? ''; ?>') {
    allImages = ['<?php echo htmlspecialchars($car['image_path']); ?>'];
}

function changeMainImage(imagePath, index, element) {
    document.getElementById('main-car-image').src = imagePath;
    document.getElementById('current-image-num').textContent = index;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.classList.remove('active');
    });
    if (element) {
        element.classList.add('active');
    }
    
    currentImageIndex = index - 1;
}

function openImageModal(imagePath) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modalImg.src = imagePath;
    modal.style.display = 'flex';
    currentImageIndex = allImages.indexOf(imagePath);
    if (currentImageIndex === -1) currentImageIndex = 0;
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

function navigateImage(direction) {
    if (allImages.length === 0) return;
    
    currentImageIndex += direction;
    if (currentImageIndex < 0) currentImageIndex = allImages.length - 1;
    if (currentImageIndex >= allImages.length) currentImageIndex = 0;
    
    document.getElementById('modalImage').src = allImages[currentImageIndex];
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>

