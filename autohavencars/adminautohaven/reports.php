<?php
require_once '../config/database.php';
require_once '../includes/admin_auth.php';

requireAdmin();

trackPageView('admin_reports');

$conn = getDBConnection();

// Get date range
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Today

// Sales Report
$salesQuery = "SELECT 
                COUNT(*) as total_sales,
                SUM(price) as total_revenue,
                AVG(price) as avg_price
               FROM cars 
               WHERE status = 'sold' 
               AND DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($salesQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$salesData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Sales by Make
$salesByMakeQuery = "SELECT make, COUNT(*) as count, SUM(price) as revenue, AVG(price) as avg_price
                     FROM cars 
                     WHERE status = 'sold' 
                     AND DATE(created_at) BETWEEN ? AND ?
                     GROUP BY make 
                     ORDER BY revenue DESC";
$stmt = $conn->prepare($salesByMakeQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$salesByMake = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// User Activity
$userActivityQuery = "SELECT 
                      COUNT(DISTINCT user_id) as active_users,
                      COUNT(*) as total_actions
                     FROM analytics 
                     WHERE DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($userActivityQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$activityData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Top Pages
$topPagesQuery = "SELECT page, COUNT(*) as views
                  FROM analytics 
                  WHERE DATE(created_at) BETWEEN ? AND ?
                  GROUP BY page 
                  ORDER BY views DESC 
                  LIMIT 10";
$stmt = $conn->prepare($topPagesQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$topPages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Daily Sales Trend
$dailySalesQuery = "SELECT DATE(created_at) as date, COUNT(*) as sales, SUM(price) as revenue
                    FROM cars 
                    WHERE status = 'sold' 
                    AND DATE(created_at) BETWEEN ? AND ?
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
$stmt = $conn->prepare($dailySalesQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$dailySales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

$pageTitle = 'Reports';
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2><i class="fas fa-chart-bar"></i> Reports</h2>
</div>

<!-- Date Range Filter -->
<div class="admin-filters">
    <form method="GET" class="filter-form">
        <div class="filter-group">
            <label>Start Date:</label>
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
        </div>
        <div class="filter-group">
            <label>End Date:</label>
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Generate Report</button>
        <a href="reports.php" class="btn btn-secondary">Reset</a>
    </form>
</div>

<!-- Sales Summary -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon sales">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($salesData['total_sales'] ?? 0); ?></h3>
            <p>Total Sales</p>
            <small>Period: <?php echo date('M d', strtotime($startDate)); ?> - <?php echo date('M d, Y', strtotime($endDate)); ?></small>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon revenue">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-content">
            <h3>$<?php echo number_format($salesData['total_revenue'] ?? 0, 2); ?></h3>
            <p>Total Revenue</p>
            <small>Average: $<?php echo number_format($salesData['avg_price'] ?? 0, 2); ?></small>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon users">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($activityData['active_users'] ?? 0); ?></h3>
            <p>Active Users</p>
            <small><?php echo number_format($activityData['total_actions'] ?? 0); ?> actions</small>
        </div>
    </div>
</div>

<!-- Sales by Make -->
<div class="dashboard-grid">
    <div class="dashboard-card">
        <h2><i class="fas fa-car"></i> Sales by Make</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Make</th>
                        <th>Units Sold</th>
                        <th>Total Revenue</th>
                        <th>Avg Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($salesByMake)): ?>
                        <?php foreach ($salesByMake as $make): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($make['make']); ?></strong></td>
                                <td><?php echo $make['count']; ?></td>
                                <td>$<?php echo number_format($make['revenue'], 2); ?></td>
                                <td>$<?php echo number_format($make['avg_price'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No sales data for this period</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Top Pages -->
    <div class="dashboard-card">
        <h2><i class="fas fa-file-alt"></i> Top Pages</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Views</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($topPages)): ?>
                        <?php foreach ($topPages as $page): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($page['page']); ?></td>
                                <td><?php echo number_format($page['views']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center">No data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sales Trend Chart -->
<div class="dashboard-card">
    <h2><i class="fas fa-chart-line"></i> Daily Sales Trend</h2>
    <div class="chart-container">
        <canvas id="salesChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const salesData = <?php echo json_encode($dailySales); ?>;
const labels = salesData.map(item => {
    const date = new Date(item.date);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});
const salesCount = salesData.map(item => parseInt(item.sales));
const revenueData = salesData.map(item => parseFloat(item.revenue));

const ctx = document.getElementById('salesChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales Count',
                data: salesCount,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Revenue ($)',
                data: revenueData,
                type: 'line',
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left'
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}
</script>

<?php include '../includes/admin_footer.php'; ?>

