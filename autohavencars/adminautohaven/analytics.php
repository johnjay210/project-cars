<?php
require_once '../config/database.php';
require_once '../includes/admin_auth.php';

requireAdmin();

trackPageView('admin_analytics');

$conn = getDBConnection();

// Get date range
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Traffic Overview
$trafficQuery = "SELECT 
                  COUNT(*) as total_views,
                  COUNT(DISTINCT user_id) as unique_users,
                  COUNT(DISTINCT ip_address) as unique_visitors
                 FROM analytics 
                 WHERE DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($trafficQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$trafficData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Page Views Over Time
$viewsOverTimeQuery = "SELECT DATE(created_at) as date, COUNT(*) as views
                       FROM analytics 
                       WHERE DATE(created_at) BETWEEN ? AND ?
                       GROUP BY DATE(created_at)
                       ORDER BY date ASC";
$stmt = $conn->prepare($viewsOverTimeQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$viewsOverTime = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Top Referrers
$referrersQuery = "SELECT referrer, COUNT(*) as count
                   FROM analytics 
                   WHERE DATE(created_at) BETWEEN ? AND ?
                   AND referrer IS NOT NULL
                   AND referrer != ''
                   GROUP BY referrer
                   ORDER BY count DESC
                   LIMIT 10";
$stmt = $conn->prepare($referrersQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$referrers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Hourly Traffic Pattern
$hourlyQuery = "SELECT HOUR(created_at) as hour, COUNT(*) as views
                FROM analytics 
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY HOUR(created_at)
                ORDER BY hour ASC";
$stmt = $conn->prepare($hourlyQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$hourlyData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// User vs Guest Traffic
$userTrafficQuery = "SELECT 
                      COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) as logged_in,
                      COUNT(CASE WHEN user_id IS NULL THEN 1 END) as guests
                     FROM analytics 
                     WHERE DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($userTrafficQuery);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$userTraffic = $stmt->get_result()->fetch_assoc();
$stmt->close();

$conn->close();

$pageTitle = 'Analytics';
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2><i class="fas fa-chart-line"></i> Traffic Analytics</h2>
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
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="analytics.php" class="btn btn-secondary">Reset</a>
    </form>
</div>

<!-- Traffic Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon views">
            <i class="fas fa-eye"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($trafficData['total_views'] ?? 0); ?></h3>
            <p>Total Page Views</p>
            <small>Period: <?php echo date('M d', strtotime($startDate)); ?> - <?php echo date('M d, Y', strtotime($endDate)); ?></small>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon users">
            <i class="fas fa-user-friends"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($trafficData['unique_users'] ?? 0); ?></h3>
            <p>Logged-in Users</p>
            <small><?php echo number_format($trafficData['unique_visitors'] ?? 0); ?> unique visitors</small>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon traffic">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo number_format($userTraffic['logged_in'] ?? 0); ?></h3>
            <p>Logged-in Views</p>
            <small><?php echo number_format($userTraffic['guests'] ?? 0); ?> guest views</small>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="dashboard-grid">
    <!-- Page Views Over Time -->
    <div class="dashboard-card">
        <h2><i class="fas fa-chart-area"></i> Page Views Over Time</h2>
        <div class="chart-container">
            <canvas id="viewsChart"></canvas>
        </div>
    </div>
    
    <!-- Hourly Traffic Pattern -->
    <div class="dashboard-card">
        <h2><i class="fas fa-clock"></i> Hourly Traffic Pattern</h2>
        <div class="chart-container">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Referrers -->
<div class="dashboard-card">
    <h2><i class="fas fa-external-link-alt"></i> Top Referrers</h2>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Referrer</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($referrers)): ?>
                    <?php foreach ($referrers as $referrer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($referrer['referrer']); ?></td>
                            <td><?php echo number_format($referrer['count']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center">No referrer data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Page Views Chart
const viewsData = <?php echo json_encode($viewsOverTime); ?>;
const viewsLabels = viewsData.map(item => {
    const date = new Date(item.date);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});
const viewsCount = viewsData.map(item => parseInt(item.views));

const viewsCtx = document.getElementById('viewsChart');
if (viewsCtx) {
    new Chart(viewsCtx, {
        type: 'line',
        data: {
            labels: viewsLabels,
            datasets: [{
                label: 'Page Views',
                data: viewsCount,
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

// Hourly Traffic Chart
const hourlyData = <?php echo json_encode($hourlyData); ?>;
const hours = Array.from({length: 24}, (_, i) => i);
const hourlyViews = hours.map(hour => {
    const found = hourlyData.find(item => parseInt(item.hour) === hour);
    return found ? parseInt(found.views) : 0;
});

const hourlyCtx = document.getElementById('hourlyChart');
if (hourlyCtx) {
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: hours.map(h => h + ':00'),
            datasets: [{
                label: 'Views',
                data: hourlyViews,
                backgroundColor: 'rgba(153, 102, 255, 0.5)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
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

