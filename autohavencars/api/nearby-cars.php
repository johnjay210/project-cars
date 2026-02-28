<?php
require_once '../config/database.php';
require_once '../includes/location.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $latitude = isset($_GET['latitude']) ? (float)$_GET['latitude'] : 0;
    $longitude = isset($_GET['longitude']) ? (float)$_GET['longitude'] : 0;
    $radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 50;
    
    if (!$latitude || !$longitude || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
        echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
        exit;
    }
    
    $filters = [];
    if (isset($_GET['make']) && !empty($_GET['make'])) {
        $filters['make'] = trim($_GET['make']);
    }
    if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
        $filters['min_price'] = (float)$_GET['min_price'];
    }
    if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
        $filters['max_price'] = (float)$_GET['max_price'];
    }
    if (isset($_GET['year']) && !empty($_GET['year'])) {
        $filters['year'] = (int)$_GET['year'];
    }
    
    $cars = getCarsNearby($latitude, $longitude, $radius, $filters);
    
    echo json_encode([
        'success' => true,
        'cars' => $cars,
        'count' => count($cars)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>






