<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

$query = "SELECT * FROM cars WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$pageTitle = 'My Cars';
include 'includes/header.php';
?>

<main class="my-cars-page">
    <div class="container">
        <h1>My Listed Cars</h1>
        <a href="post-car.php" class="btn btn-primary">List New Car</a>
        
        <div class="cars-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($car = $result->fetch_assoc()): ?>
                    <div class="car-card">
                        <div class="car-image">
                            <?php if ($car['image_path'] && file_exists($car['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="<?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>">
                            <?php else: ?>
                                <img src="assets/images/placeholder-car.jpg" alt="Car placeholder">
                            <?php endif; ?>
                            <span class="car-price">$<?php echo number_format($car['price'], 2); ?></span>
                            <span class="status-badge <?php echo $car['status']; ?>"><?php echo ucfirst($car['status']); ?></span>
                        </div>
                        <div class="car-info">
                            <h3><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></h3>
                            <div class="car-details">
                                <span><i class="fas fa-tachometer-alt"></i> <?php echo number_format($car['mileage']); ?> miles</span>
                                <span><i class="fas fa-palette"></i> <?php echo htmlspecialchars($car['color']); ?></span>
                            </div>
                            <div class="car-actions">
                                <a href="car-details.php?id=<?php echo $car['id']; ?>" class="btn btn-outline">View</a>
                                <a href="manage-car-images.php?id=<?php echo $car['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-images"></i> Manage Images
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-car"></i>
                    <h3>No cars listed yet</h3>
                    <p><a href="post-car.php">List your first car</a> to get started!</p>
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

