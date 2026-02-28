<?php
require_once __DIR__ . '/translations.php';
require_once __DIR__ . '/currency.php';
$currentLang = getCurrentLanguage();
$currentCurrency = getCurrentCurrency();
$availableLanguages = getAvailableLanguages();
$availableCurrencies = getAvailableCurrencies();
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>AutoHavenCars - Buy & Sell Cars</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">
                    <i class="fas fa-car"></i>
                    <span>AutoHavenCars</span>
                </a>
            </div>
            <ul class="nav-menu">
                <!-- Language & Currency Switchers -->
                <li class="nav-dropdown">
                    <a href="#" class="nav-icon-link" onclick="event.preventDefault(); toggleDropdown('lang-dropdown');">
                        <i class="fas fa-language"></i>
                        <span class="nav-label"><?php echo $availableLanguages[$currentLang]['flag'] ?? '🌐'; ?></span>
                    </a>
                    <div class="dropdown-menu" id="lang-dropdown">
                        <div class="dropdown-header"><?php echo t('nav_language', 'Language'); ?></div>
                        <?php foreach ($availableLanguages as $code => $lang): ?>
                            <a href="#" class="dropdown-item <?php echo $code === $currentLang ? 'active' : ''; ?>" 
                               onclick="event.preventDefault(); setLanguage('<?php echo $code; ?>');">
                                <span class="flag"><?php echo $lang['flag']; ?></span>
                                <span><?php echo $lang['name']; ?></span>
                                <?php if ($code === $currentLang): ?>
                                    <i class="fas fa-check"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <li class="nav-dropdown">
                    <a href="#" class="nav-icon-link" onclick="event.preventDefault(); toggleDropdown('currency-dropdown');">
                        <i class="fas fa-dollar-sign"></i>
                        <span class="nav-label"><?php echo getCurrencySymbol($currentCurrency); ?></span>
                    </a>
                    <div class="dropdown-menu" id="currency-dropdown">
                        <div class="dropdown-header"><?php echo t('currency', 'Currency'); ?></div>
                        <?php foreach ($availableCurrencies as $code => $currency): ?>
                            <a href="#" class="dropdown-item <?php echo $code === $currentCurrency ? 'active' : ''; ?>" 
                               onclick="event.preventDefault(); setCurrency('<?php echo $code; ?>');">
                                <span class="flag"><?php echo $currency['flag']; ?></span>
                                <span><?php echo $currency['name']; ?></span>
                                <span class="currency-symbol"><?php echo $currency['symbol']; ?></span>
                                <?php if ($code === $currentCurrency): ?>
                                    <i class="fas fa-check"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <li><a href="index.php"><?php echo t('nav_home', 'Home'); ?></a></li>
                <li><a href="listings.php"><?php echo t('nav_listings', 'Browse Cars'); ?></a></li>
                <li><a href="post-car.php"><?php echo t('nav_post_car', 'Sell Your Car'); ?></a></li>
                <?php 
                $wishlistCount = 0;
                $cartCount = 0;
                if (file_exists(__DIR__ . '/wishlist_cart.php')) {
                    require_once __DIR__ . '/wishlist_cart.php';
                    try {
                        $wishlistCount = getWishlistCount();
                        $cartCount = getCartCount();
                    } catch (Exception $e) {
                        // Silently fail if tables don't exist yet
                    }
                }
                ?>
                <li>
                    <a href="wishlist.php" class="nav-icon-link" title="<?php echo t('nav_wishlist', 'Wishlist'); ?>">
                        <i class="fas fa-heart"></i>
                        <?php if ($wishlistCount > 0): ?>
                            <span class="nav-badge"><?php echo $wishlistCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="cart.php" class="nav-icon-link" title="<?php echo t('nav_cart', 'Cart'); ?>">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="nav-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                    $unreadCount = 0;
                    if (file_exists(__DIR__ . '/messages.php')) {
                        require_once __DIR__ . '/messages.php';
                        try {
                            $unreadCount = getUnreadMessageCount($_SESSION['user_id']);
                        } catch (Exception $e) {
                            // Silently fail if messages table doesn't exist yet
                            $unreadCount = 0;
                        }
                    }
                    ?>
                    <li>
                        <a href="messages.php" class="nav-icon-link" title="Messages">
                            <i class="fas fa-comments"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="nav-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="my-cars.php"><?php echo t('nav_my_cars', 'My Cars'); ?></a></li>
                    <?php 
                    // Check if user is admin
                    if (isset($_SESSION['user_id'])) {
                        $conn = getDBConnection();
                        $roleQuery = "SELECT role FROM users WHERE id = ?";
                        $roleStmt = $conn->prepare($roleQuery);
                        $roleStmt->bind_param('i', $_SESSION['user_id']);
                        $roleStmt->execute();
                        $roleResult = $roleStmt->get_result();
                        if ($roleResult->num_rows === 1) {
                            $roleData = $roleResult->fetch_assoc();
                            if (($roleData['role'] ?? 'user') === 'admin') {
                                echo '<li><a href="adminautohaven/index.php" class="admin-link"><i class="fas fa-shield-alt"></i> ' . t('nav_admin', 'Admin') . '</a></li>';
                            }
                        }
                        $roleStmt->close();
                        $conn->close();
                    }
                    ?>
                    <li><a href="logout.php"><?php echo t('nav_logout', 'Logout'); ?></a></li>
                <?php else: ?>
                    <li><a href="login.php"><?php echo t('nav_login', 'Login'); ?></a></li>
                    <li><a href="register.php" class="btn-primary"><?php echo t('nav_register', 'Sign Up'); ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

