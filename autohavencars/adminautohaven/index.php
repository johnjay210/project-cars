<?php
require_once '../config/database.php';
require_once '../includes/admin_auth.php';

requireAdmin();

// Track page view
trackPageView('admin_dashboard');

$stats = getAdminStats();

// Get recent activity
$conn = getDBConnection();

// Recent users
$recentUsersQuery = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recentUsers = $conn->query($recentUsersQuery)->fetch_all(MYSQLI_ASSOC);

// Recent listings
$recentListingsQuery = "SELECT c.*, u.username FROM cars c 
                       JOIN users u ON c.user_id = u.id 
                       ORDER BY c.created_at DESC LIMIT 5";
$recentListings = $conn->query($recentListingsQuery)->fetch_all(MYSQLI_ASSOC);

// Top selling makes
$topMakesQuery = "SELECT make, COUNT(*) as count, SUM(price) as total_value 
                  FROM cars 
                  WHERE status = 'sold' 
                  GROUP BY make 
                  ORDER BY count DESC 
                  LIMIT 5";
$topMakes = $conn->query($topMakesQuery)->fetch_all(MYSQLI_ASSOC);

// Page views over last 7 days
$viewsQuery = "SELECT DATE(created_at) as date, COUNT(*) as count 
               FROM analytics 
               WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
               GROUP BY DATE(created_at)
               ORDER BY date ASC";
$dailyViews = $conn->query($viewsQuery)->fetch_all(MYSQLI_ASSOC);

$conn->close();

$pageTitle = 'Admin Dashboard';
include '../includes/admin_header.php';
?>

<main class="admin-dashboard">
    <div class="admin-container">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_users']); ?></h3>
                    <p>Total Users</p>
                    <small><?php echo $stats['new_users_month']; ?> new this month</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon cars">
                    <i class="fas fa-car"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_cars']); ?></h3>
                    <p>Total Listings</p>
                    <small><?php echo $stats['new_listings_month']; ?> new this month</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon available">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['available_cars']); ?></h3>
                    <p>Available Cars</p>
                    <small><?php echo $stats['sold_cars']; ?> sold</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon sales">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>$<?php echo number_format($stats['total_sales_value'], 2); ?></h3>
                    <p>Total Sales Value</p>
                    <small>All time</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon views">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['today_views']); ?></h3>
                    <p>Today's Views</p>
                    <small><?php echo number_format($stats['week_views']); ?> this week</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon traffic">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['month_views']); ?></h3>
                    <p>Monthly Views</p>
                    <small>This month</small>
                </div>
            </div>
        </div>
        
        <!-- Charts and Data -->
        <div class="dashboard-grid">
            <!-- Recent Activity -->
            <div class="dashboard-card">
                <h2><i class="fas fa-clock"></i> Recent Users</h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="users.php?action=view&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="users.php" class="btn btn-secondary">View All Users</a>
                </div>
            </div>
            
            <!-- Recent Listings -->
            <div class="dashboard-card">
                <h2><i class="fas fa-list"></i> Recent Listings</h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Car</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentListings as $car): ?>
                                <tr>
                                    <td><?php echo $car['id']; ?></td>
                                    <td><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></td>
                                    <td><?php echo htmlspecialchars($car['username']); ?></td>
                                    <td>$<?php echo number_format($car['price'], 2); ?></td>
                                    <td><span class="status-badge <?php echo $car['status']; ?>"><?php echo ucfirst($car['status']); ?></span></td>
                                    <td>
                                        <a href="listings.php?action=view&id=<?php echo $car['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="listings.php" class="btn btn-secondary">View All Listings</a>
                </div>
            </div>
        </div>
        
        <!-- Analytics Chart -->
        <div class="dashboard-card">
            <h2><i class="fas fa-chart-area"></i> Page Views (Last 7 Days)</h2>
            <div class="chart-container">
                <canvas id="viewsChart"></canvas>
            </div>
        </div>
        
        <!-- Top Selling Makes -->
        <div class="dashboard-card">
            <h2><i class="fas fa-trophy"></i> Top Selling Makes</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Make</th>
                            <th>Sold Count</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topMakes)): ?>
                            <?php foreach ($topMakes as $make): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($make['make']); ?></strong></td>
                                    <td><?php echo $make['count']; ?></td>
                                    <td>$<?php echo number_format($make['total_value'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No sales data yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Page Views Chart
const viewsData = <?php echo json_encode($dailyViews); ?>;
const labels = viewsData.map(item => {
    const date = new Date(item.date);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});
const data = viewsData.map(item => parseInt(item.count));

const ctx = document.getElementById('viewsChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Page Views',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
</script>

<?php include '../includes/admin_footer.php'; ?>

