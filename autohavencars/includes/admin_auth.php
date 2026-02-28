<?php
// Admin Authentication Helper Functions

/**
 * Check if user is admin
 */
function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $conn = getDBConnection();
    $query = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $isAdmin = ($user['role'] === 'admin');
        $stmt->close();
        $conn->close();
        return $isAdmin;
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

/**
 * Require admin access - redirect if not admin
 */
function requireAdmin() {
    if (!isset($_SESSION['user_id'])) {
        // Determine correct login path based on current directory
        $loginPath = (strpos($_SERVER['PHP_SELF'], '/adminautohaven/') !== false) 
            ? '../login.php' 
            : 'login.php';
        header('Location: ' . $loginPath . '?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    if (!isAdmin()) {
        // Determine correct index path based on current directory
        $indexPath = (strpos($_SERVER['PHP_SELF'], '/adminautohaven/') !== false) 
            ? '../index.php' 
            : 'index.php';
        header('Location: ' . $indexPath . '?error=access_denied');
        exit;
    }
}

/**
 * Get admin stats
 */
function getAdminStats() {
    $conn = getDBConnection();
    $stats = [];
    
    // Total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // Total cars
    $result = $conn->query("SELECT COUNT(*) as count FROM cars");
    $stats['total_cars'] = $result->fetch_assoc()['count'];
    
    // Available cars
    $result = $conn->query("SELECT COUNT(*) as count FROM cars WHERE status = 'available'");
    $stats['available_cars'] = $result->fetch_assoc()['count'];
    
    // Sold cars
    $result = $conn->query("SELECT COUNT(*) as count FROM cars WHERE status = 'sold'");
    $stats['sold_cars'] = $result->fetch_assoc()['count'];
    
    // Total sales value
    $result = $conn->query("SELECT SUM(price) as total FROM cars WHERE status = 'sold'");
    $row = $result->fetch_assoc();
    $stats['total_sales_value'] = $row['total'] ?? 0;
    
    // Today's page views
    $result = $conn->query("SELECT COUNT(*) as count FROM analytics WHERE DATE(created_at) = CURDATE()");
    $stats['today_views'] = $result->fetch_assoc()['count'];
    
    // This week's page views
    $result = $conn->query("SELECT COUNT(*) as count FROM analytics WHERE WEEK(created_at) = WEEK(NOW())");
    $stats['week_views'] = $result->fetch_assoc()['count'];
    
    // This month's page views
    $result = $conn->query("SELECT COUNT(*) as count FROM analytics WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
    $stats['month_views'] = $result->fetch_assoc()['count'];
    
    // New users this month
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
    $stats['new_users_month'] = $result->fetch_assoc()['count'];
    
    // New listings this month
    $result = $conn->query("SELECT COUNT(*) as count FROM cars WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
    $stats['new_listings_month'] = $result->fetch_assoc()['count'];
    
    $conn->close();
    return $stats;
}

/**
 * Track page view for analytics
 */
function trackPageView($page) {
    $conn = getDBConnection();
    
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;
    
    // Check if analytics table exists
    $result = $conn->query("SHOW TABLES LIKE 'analytics'");
    if ($result->num_rows > 0) {
        $query = "INSERT INTO analytics (page, user_id, ip_address, user_agent, referrer) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        // Type string: s=string, i=integer (5 parameters: page, user_id, ip_address, user_agent, referrer)
        $stmt->bind_param('sisss', $page, $userId, $ipAddress, $userAgent, $referrer);
        $stmt->execute();
        $stmt->close();
    }
    
    $conn->close();
}
?>

