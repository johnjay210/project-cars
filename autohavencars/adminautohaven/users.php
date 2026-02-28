<?php
require_once '../config/database.php';
require_once '../includes/admin_auth.php';

requireAdmin();

trackPageView('admin_users');

$conn = getDBConnection();
$message = '';
$error = '';

// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($action === 'delete' && $userId) {
        if ($userId == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $message = 'User deleted successfully.';
            } else {
                $error = 'Failed to delete user.';
            }
            $stmt->close();
        }
    } elseif ($action === 'toggle_admin' && $userId) {
        if ($userId == $_SESSION['user_id']) {
            $error = 'You cannot change your own role.';
        } else {
            $query = "UPDATE users SET role = IF(role = 'admin', 'user', 'admin') WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $message = 'User role updated successfully.';
            } else {
                $error = 'Failed to update user role.';
            }
            $stmt->close();
        }
    }
}

// Get search/filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (username LIKE ? OR email LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

if (!empty($role)) {
    $query .= " AND role = ?";
    $params[] = $role;
    $types .= 's';
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get stats
$totalUsers = count($users);
$adminCount = count(array_filter(array_column($users, 'role'), function($role) { 
    return $role === 'admin'; 
}));

$conn->close();

$pageTitle = 'User Management';
include '../includes/admin_header.php';
?>

<div class="admin-page-header">
    <h2><i class="fas fa-users"></i> User Management</h2>
    <div class="page-actions">
        <span class="stat-badge">Total: <?php echo $totalUsers; ?> users</span>
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
            <input type="text" name="search" placeholder="Search by username or email..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="filter-group">
            <select name="role">
                <option value="">All Roles</option>
                <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>Users</option>
                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admins</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="users.php" class="btn btn-secondary">Clear</a>
    </form>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                            <td>
                                <span class="role-badge <?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="user-details.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="users.php?action=toggle_admin&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-warning"
                                           onclick="return confirm('Change user role?')">
                                            <?php echo $user['role'] === 'admin' ? 'Remove Admin' : 'Make Admin'; ?>
                                        </a>
                                        <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            Delete
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>

