<?php
require_once '../config/database.php';
require_once '../includes/admin_auth.php';

requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$userId = (int)$_GET['id'];
$conn = getDBConnection();

// Get user details
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: users.php');
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Get user's cars
$carsQuery = "SELECT * FROM cars WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($carsQuery);
$stmt->bind_param('i', $userId);
$stmt->execute();
$carsResult = $stmt->get_result();
$userCars = $carsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get user's reviews (as reviewer)
$reviewsQuery = "SELECT r.*, u.username as seller_name, c.make, c.model, c.year 
                 FROM reviews r
                 JOIN users u ON r.seller_id = u.id
                 LEFT JOIN cars c ON r.car_id = c.id
                 WHERE r.reviewer_id = ?
                 ORDER BY r.created_at DESC
                 LIMIT 10";
$stmt = $conn->prepare($reviewsQuery);
$stmt->bind_param('i', $userId);
$stmt->execute();
$reviewsResult = $stmt->get_result();
$userReviews = $reviewsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

$pageTitle = 'User Details - ' . $user['username'];
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2><i class="fas fa-user"></i> User Details</h2>
    <div class="page-actions">
        <a href="users.php" class="btn btn-secondary">Back to Users</a>
    </div>
</div>

<div class="dashboard-grid">
    <!-- User Info -->
    <div class="dashboard-card">
        <h2>User Information</h2>
        <table class="admin-table">
            <tr>
                <th>ID</th>
                <td><?php echo $user['id']; ?></td>
            </tr>
            <tr>
                <th>Username</th>
                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Role</th>
                <td>
                    <span class="role-badge <?php echo $user['role'] ?? 'user'; ?>">
                        <?php echo ucfirst($user['role'] ?? 'user'); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Joined</th>
                <td><?php echo date('F d, Y', strtotime($user['created_at'])); ?></td>
            </tr>
        </table>
        
        <?php if ($user['id'] != $_SESSION['user_id']): ?>
            <div class="card-footer">
                <a href="users.php?action=toggle_admin&id=<?php echo $user['id']; ?>" 
                   class="btn btn-warning"
                   onclick="return confirm('Change user role?')">
                    <?php echo ($user['role'] ?? 'user') === 'admin' ? 'Remove Admin' : 'Make Admin'; ?>
                </a>
                <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Are you sure you want to delete this user?')">
                    Delete User
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- User Stats -->
    <div class="dashboard-card">
        <h2>User Statistics</h2>
        <div class="stats-grid" style="grid-template-columns: 1fr;">
            <div class="stat-card">
                <div class="stat-icon cars">
                    <i class="fas fa-car"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($userCars); ?></h3>
                    <p>Total Listings</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon views">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($userReviews); ?></h3>
                    <p>Reviews Given</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User's Cars -->
<div class="dashboard-card">
    <h2>User's Listings (<?php echo count($userCars); ?>)</h2>
    <?php if (!empty($userCars)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Car</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Listed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userCars as $car): ?>
                        <tr>
                            <td><?php echo $car['id']; ?></td>
                            <td><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></td>
                            <td>$<?php echo number_format($car['price'], 2); ?></td>
                            <td><span class="status-badge <?php echo $car['status']; ?>"><?php echo ucfirst($car['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($car['created_at'])); ?></td>
                            <td>
                                <a href="../car-details.php?id=<?php echo $car['id']; ?>" target="_blank" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center">This user has no listings.</p>
    <?php endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>

