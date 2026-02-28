<?php
require_once '../config/database.php';
require_once '../includes/currency.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
    $fromCurrency = isset($_POST['from']) ? strtoupper(trim($_POST['from'])) : 'USD';
    $toCurrency = isset($_POST['to']) ? strtoupper(trim($_POST['to'])) : getCurrentCurrency();
    
    if ($amount > 0) {
        $converted = convertCurrency($amount, $fromCurrency, $toCurrency);
        $formatted = formatCurrency($converted, $toCurrency);
        
        echo json_encode([
            'success' => true,
            'amount' => $amount,
            'from' => $fromCurrency,
            'to' => $toCurrency,
            'converted' => $converted,
            'formatted' => $formatted
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>





