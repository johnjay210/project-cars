<?php
// Error checking script - run this to see what errors are occurring
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Error Check</h1>";

// Check database connection
echo "<h2>1. Database Connection</h2>";
require_once 'config/database.php';
try {
    $conn = getDBConnection();
    echo "✓ Database connection successful<br>";
    $conn->close();
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

// Check if tables exist
echo "<h2>2. Database Tables</h2>";
$conn = getDBConnection();
$tables = ['users', 'cars'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✓ Table '$table' exists<br>";
    } else {
        echo "✗ Table '$table' does NOT exist<br>";
    }
}
$conn->close();

// Check file includes
echo "<h2>3. File Includes</h2>";
$files = [
    'includes/header.php',
    'includes/footer.php',
    'config/database.php'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists<br>";
    } else {
        echo "✗ $file does NOT exist<br>";
    }
}

// Check PHP version
echo "<h2>4. PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";

// Check session
echo "<h2>5. Session</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✓ Session is active<br>";
} else {
    echo "✗ Session is not active<br>";
}

echo "<hr>";
echo "<p>If you see errors above, fix them first. If everything shows ✓, the issue might be in the page itself.</p>";
?>




