<?php
// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>AutoHavenCars Debug Page</h1>";
echo "<p>This page helps identify what's causing the blank page.</p>";

// Test 1: PHP is working
echo "<h2>✓ Test 1: PHP is working</h2>";
echo "<p>If you see this, PHP is functioning.</p>";

// Test 2: Database connection
echo "<h2>Test 2: Database Connection</h2>";
try {
    require_once 'config/database.php';
    $conn = getDBConnection();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test 3: Check if tables exist
    echo "<h2>Test 3: Database Tables</h2>";
    $tables = ['users', 'cars'];
    $allExist = true;
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' does NOT exist - You need to run database/schema.sql</p>";
            $allExist = false;
        }
    }
    
    $conn->close();
    
    if (!$allExist) {
        echo "<p style='color: red;'><strong>Action Required:</strong> Run the SQL from database/schema.sql in phpMyAdmin</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Check your database configuration in config/database.php</p>";
}

// Test 4: File includes
echo "<h2>Test 4: Required Files</h2>";
$files = [
    'config/database.php',
    'includes/header.php',
    'includes/footer.php'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $file is MISSING</p>";
    }
}

// Test 5: Try loading index.php
echo "<h2>Test 5: Loading index.php</h2>";
echo "<p>Attempting to include index.php...</p>";
echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
ob_start();
try {
    include 'index.php';
    $output = ob_get_clean();
    if (!empty($output)) {
        echo "<p style='color: green;'>✓ index.php loaded successfully</p>";
        echo "<p>Output length: " . strlen($output) . " characters</p>";
    } else {
        echo "<p style='color: red;'>✗ index.php produced no output</p>";
    }
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color: red;'>✗ Error loading index.php: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace:</p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ul>";
echo "<li>If database tables are missing, run database/schema.sql in phpMyAdmin</li>";
echo "<li>If files are missing, check your file structure</li>";
echo "<li>If index.php has errors, check the error messages above</li>";
echo "<li>Check Apache error logs: C:\\xampp\\apache\\logs\\error.log</li>";
echo "</ul>";
?>




