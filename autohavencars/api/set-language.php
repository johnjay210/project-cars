<?php
require_once '../config/database.php';
require_once '../includes/translations.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lang = isset($_POST['language']) ? trim($_POST['language']) : 'en';
    
    // Validate language
    $availableLanguages = getAvailableLanguages();
    if (isset($availableLanguages[$lang])) {
        setLanguage($lang);
        echo json_encode(['success' => true, 'message' => 'Language changed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid language']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>





