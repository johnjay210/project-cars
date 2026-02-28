<?php
// Translation System

// Get user's preferred language from session or default to English
function getCurrentLanguage() {
    return isset($_SESSION['language']) ? $_SESSION['language'] : 'en';
}

// Set user's preferred language
function setLanguage($lang) {
    $_SESSION['language'] = $lang;
}

// Translation function
function t($key, $default = null) {
    $lang = getCurrentLanguage();
    $translations = getTranslations($lang);
    
    if (isset($translations[$key])) {
        return $translations[$key];
    }
    
    // Fallback to English if translation not found
    if ($lang !== 'en') {
        $enTranslations = getTranslations('en');
        if (isset($enTranslations[$key])) {
            return $enTranslations[$key];
        }
    }
    
    return $default !== null ? $default : $key;
}

// Get translations for a language
function getTranslations($lang) {
    static $cache = [];
    
    if (isset($cache[$lang])) {
        return $cache[$lang];
    }
    
    $file = __DIR__ . "/../languages/{$lang}.php";
    if (file_exists($file)) {
        $cache[$lang] = require $file;
        return $cache[$lang];
    }
    
    // Fallback to English
    $enFile = __DIR__ . "/../languages/en.php";
    if (file_exists($enFile)) {
        $cache['en'] = require $enFile;
        return $cache['en'];
    }
    
    return [];
}

// Get available languages
function getAvailableLanguages() {
    return [
        'en' => ['name' => 'English', 'flag' => '🇺🇸'],
        'sw' => ['name' => 'Kiswahili', 'flag' => '🇰🇪'],
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
        'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
        'ar' => ['name' => 'العربية', 'flag' => '🇸🇦'],
    ];
}
?>





