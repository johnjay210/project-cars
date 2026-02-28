<?php
require_once '../config/database.php';
require_once '../includes/location.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = isset($_REQUEST['address']) ? trim($_REQUEST['address']) : '';
    $city = isset($_REQUEST['city']) ? trim($_REQUEST['city']) : '';
    $state = isset($_REQUEST['state']) ? trim($_REQUEST['state']) : '';
    $zipCode = isset($_REQUEST['zip_code']) ? trim($_REQUEST['zip_code']) : '';
    
    if (empty($address) && empty($city) && empty($state)) {
        echo json_encode(['success' => false, 'message' => 'Please provide at least city or address']);
        exit;
    }
    
    $coordinates = geocodeAddress($address, $city, $state, $zipCode);
    
    if ($coordinates) {
        echo json_encode([
            'success' => true,
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not find location']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>






