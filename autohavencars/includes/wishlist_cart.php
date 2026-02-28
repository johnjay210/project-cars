<?php
// Wishlist and Cart Helper Functions

/**
 * Check if tables exist
 */
function wishlistCartTablesExist() {
    $conn = getDBConnection();
    $result = $conn->query("SHOW TABLES LIKE 'wishlist'");
    $exists = $result && $result->num_rows > 0;
    $conn->close();
    return $exists;
}

/**
 * Create wishlist/cart tables if they do not exist.
 * Returns true if tables exist (created or already there).
 */
function ensureWishlistCartTables() {
    if (wishlistCartTablesExist()) {
        return true;
    }

    $conn = getDBConnection();
    $sql = "
        CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            car_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
            UNIQUE KEY unique_wishlist (user_id, car_id),
            INDEX idx_user_id (user_id)
        );
        CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            car_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
            UNIQUE KEY unique_cart (user_id, car_id),
            INDEX idx_user_id (user_id)
        );
    ";

    $result = $conn->multi_query($sql);
    // Flush remaining results if any
    while ($conn->more_results() && $conn->next_result()) { /* no-op */ }
    $conn->close();

    return $result && wishlistCartTablesExist();
}

/**
 * Get wishlist count for user
 */
function getWishlistCount($userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        return isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
    }
    
    if (!ensureWishlistCartTables()) {
        return 0;
    }
    
    $conn = getDBConnection();
    $query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    $conn->close();
    return $count;
}

/**
 * Get cart count for user
 */
function getCartCount($userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    }
    
    if (!ensureWishlistCartTables()) {
        return 0;
    }
    
    $conn = getDBConnection();
    $query = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    $conn->close();
    return $count;
}

/**
 * Check if car is in wishlist
 */
function isInWishlist($carId, $userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        return isset($_SESSION['wishlist']) && in_array($carId, $_SESSION['wishlist']);
    }
    
    if (!ensureWishlistCartTables()) {
        return false;
    }
    
    $conn = getDBConnection();
    $query = "SELECT id FROM wishlist WHERE user_id = ? AND car_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $userId, $carId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    $conn->close();
    return $exists;
}

/**
 * Check if car is in cart
 */
function isInCart($carId, $userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        return isset($_SESSION['cart']) && in_array($carId, $_SESSION['cart']);
    }
    
    if (!ensureWishlistCartTables()) {
        return false;
    }
    
    $conn = getDBConnection();
    $query = "SELECT id FROM cart WHERE user_id = ? AND car_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $userId, $carId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    $conn->close();
    return $exists;
}

/**
 * Add to wishlist
 */
function addToWishlist($carId, $userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }
        if (!in_array($carId, $_SESSION['wishlist'])) {
            $_SESSION['wishlist'][] = $carId;
        }
        return true;
    }
    
    if (!ensureWishlistCartTables()) {
        return false;
    }
    
    $conn = getDBConnection();
    $query = "INSERT IGNORE INTO wishlist (user_id, car_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $userId, $carId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

/**
 * Remove from wishlist
 */
function removeFromWishlist($carId, $userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        if (isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = array_values(array_diff($_SESSION['wishlist'], [$carId]));
        }
        return true;
    }
    
    if (!wishlistCartTablesExist()) {
        return false;
    }
    
    $conn = getDBConnection();
    $query = "DELETE FROM wishlist WHERE user_id = ? AND car_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $userId, $carId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

/**
 * Add to cart
 */
function addToCart($carId, $userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (!in_array($carId, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $carId;
        }
        return true;
    }
    
    if (!wishlistCartTablesExist()) {
        return false;
    }
    
    $conn = getDBConnection();
    $query = "INSERT IGNORE INTO cart (user_id, car_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $userId, $carId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

/**
 * Remove from cart
 */
function removeFromCart($carId, $userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        if (isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array_values(array_diff($_SESSION['cart'], [$carId]));
        }
        return true;
    }
    
    if (!wishlistCartTablesExist()) {
        return false;
    }
    
    $conn = getDBConnection();
    $query = "DELETE FROM cart WHERE user_id = ? AND car_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $userId, $carId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

/**
 * Get wishlist items
 */
function getWishlistItems($userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        if (!isset($_SESSION['wishlist']) || empty($_SESSION['wishlist'])) {
            return [];
        }
        $carIds = $_SESSION['wishlist'];
        if (empty($carIds)) {
            return [];
        }
        $conn = getDBConnection();
        $placeholders = str_repeat('?,', count($carIds) - 1) . '?';
        $query = "SELECT c.*, u.username FROM cars c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.id IN ($placeholders) AND c.status = 'available'";
        $stmt = $conn->prepare($query);
        $types = str_repeat('i', count($carIds));
        $stmt->bind_param($types, ...$carIds);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        $conn->close();
        return $items;
    }
    
    if (!ensureWishlistCartTables()) {
        return [];
    }
    
    $conn = getDBConnection();
    $query = "SELECT c.*, u.username FROM cars c 
              JOIN users u ON c.user_id = u.id 
              JOIN wishlist w ON c.id = w.car_id 
              WHERE w.user_id = ? AND c.status = 'available'
              ORDER BY w.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $items;
}

/**
 * Get cart items
 */
function getCartItems($userId = null) {
    if (!$userId && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return [];
        }
        $carIds = $_SESSION['cart'];
        if (empty($carIds)) {
            return [];
        }
        $conn = getDBConnection();
        $placeholders = str_repeat('?,', count($carIds) - 1) . '?';
        $query = "SELECT c.*, u.username FROM cars c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.id IN ($placeholders) AND c.status = 'available'";
        $stmt = $conn->prepare($query);
        $types = str_repeat('i', count($carIds));
        $stmt->bind_param($types, ...$carIds);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        $conn->close();
        return $items;
    }
    
    if (!ensureWishlistCartTables()) {
        return [];
    }
    
    $conn = getDBConnection();
    $query = "SELECT c.*, u.username FROM cars c 
              JOIN users u ON c.user_id = u.id 
              JOIN cart ct ON c.id = ct.car_id 
              WHERE ct.user_id = ? AND c.status = 'available'
              ORDER BY ct.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $items;
}
?>




