<?php
require_once '../config/database.php';
require_once '../includes/currency.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currency = isset($_POST['currency']) ? strtoupper(trim($_POST['currency'])) : 'USD';
    
    // Validate currency
    $availableCurrencies = getAvailableCurrencies();
    if (isset($availableCurrencies[$currency])) {
        setCurrency($currency);
        echo json_encode(['success' => true, 'message' => 'Currency changed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid currency']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>





