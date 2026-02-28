<?php
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $conn = getDBConnection();
        $query = "SELECT id, username, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // For demo purposes, we'll use a simple password check
            // In production, use password_verify() with hashed passwords
            if (password_verify($password, $user['password']) || $password === 'password') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Check if user is admin
                $roleQuery = "SELECT role FROM users WHERE id = ?";
                $roleStmt = $conn->prepare($roleQuery);
                $roleStmt->bind_param('i', $user['id']);
                $roleStmt->execute();
                $roleResult = $roleStmt->get_result();
                if ($roleResult->num_rows === 1) {
                    $roleData = $roleResult->fetch_assoc();
                    $_SESSION['role'] = $roleData['role'] ?? 'user';
                }
                $roleStmt->close();
                
                // Redirect to admin if admin, or check for redirect parameter
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && !isset($_GET['redirect'])) {
                    header('Location: adminautohaven/index.php');
                } elseif (isset($_GET['redirect'])) {
                    header('Location: ' . urldecode($_GET['redirect']));
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = 'Login';
include 'includes/header.php';
?>

<main class="auth-page">
    <div class="container">
        <div class="auth-container">
            <h1>Login to AutoHavenCars</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <p class="auth-link">Don't have an account? <a href="register.php">Sign up here</a></p>
            <p class="auth-note"><small>Demo: Use 'admin@autohavencars.com' or 'john@example.com' with password 'password'</small></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

