<?php
require_once '../config/database.php';
require_once '../includes/admin_auth.php';

requireAdmin();

trackPageView('admin_listings');

$conn = getDBConnection();
$message = '';
$error = '';

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($action === 'delete' && $carId) {
        $query = "DELETE FROM cars WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $carId);
        if ($stmt->execute()) {
            $message = 'Listing deleted successfully.';
        } else {
            $error = 'Failed to delete listing.';
        }
        $stmt->close();
    } elseif ($action === 'update_status' && $carId && isset($_GET['status'])) {
        $status = $_GET['status'];
        if (in_array($status, ['available', 'sold', 'pending'])) {
            $query = "UPDATE cars SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('si', $status, $carId);
            if ($stmt->execute()) {
                $message = 'Listing status updated successfully.';
            } else {
                $error = 'Failed to update listing status.';
            }
            $stmt->close();
        }
    }
}

// Get search/filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$make = isset($_GET['make']) ? $_GET['make'] : '';

// Build query
$query = "SELECT c.*, u.username, u.email FROM cars c 
          JOIN users u ON c.user_id = u.id 
          WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (c.make LIKE ? OR c.model LIKE ? OR u.username LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

if (!empty($status)) {
    $query .= " AND c.status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($make)) {
    $query .= " AND c.make = ?";
    $params[] = $make;
    $types .= 's';
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$listings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique makes for filter
$makesQuery = "SELECT DISTINCT make FROM cars ORDER BY make";
$makes = $conn->query($makesQuery)->fetch_all(MYSQLI_ASSOC);

$conn->close();

$pageTitle = 'Listing Management';
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2><i class="fas fa-car"></i> Listing Management</h2>
    <div class="page-actions">
        <span class="stat-badge">Total: <?php echo count($listings); ?> listings</span>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Filters -->
<div class="admin-filters">
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <input type="text" name="search" placeholder="Search by make, model, or seller..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="filter-group">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="available" <?php echo $status === 'available' ? 'selected' : ''; ?>>Available</option>
                <option value="sold" <?php echo $status === 'sold' ? 'selected' : ''; ?>>Sold</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="make">
                <option value="">All Makes</option>
                <?php foreach ($makes as $makeOption): ?>
                    <option value="<?php echo htmlspecialchars($makeOption['make']); ?>" 
                            <?php echo $make === $makeOption['make'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($makeOption['make']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="listings.php" class="btn btn-secondary">Clear</a>
    </form>
</div>

<!-- Listings Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Car</th>
                    <th>Seller</th>
                    <th>Price</th>
                    <th>Mileage</th>
                    <th>Status</th>
                    <th>Listed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($listings)): ?>
                    <?php foreach ($listings as $car): ?>
                        <tr>
                            <td><?php echo $car['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></strong>
                            </td>
                            <td>
                                <a href="user-details.php?id=<?php echo $car['user_id']; ?>">
                                    <?php echo htmlspecialchars($car['username']); ?>
                                </a>
                            </td>
                            <td>$<?php echo number_format($car['price'], 2); ?></td>
                            <td><?php echo number_format($car['mileage']); ?> mi</td>
                            <td>
                                <select class="status-select" onchange="updateStatus(<?php echo $car['id']; ?>, this.value)">
                                    <option value="available" <?php echo $car['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="sold" <?php echo $car['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                    <option value="pending" <?php echo $car['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($car['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="../car-details.php?id=<?php echo $car['id']; ?>" target="_blank" class="btn btn-sm btn-primary">View</a>
                                    <a href="listings.php?action=delete&id=<?php echo $car['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this listing?')">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No listings found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateStatus(carId, status) {
    if (confirm('Update listing status to ' + status + '?')) {
        window.location.href = 'listings.php?action=update_status&id=' + carId + '&status=' + status;
    }
}
</script>

<?php include '../includes/admin_footer.php'; ?>

