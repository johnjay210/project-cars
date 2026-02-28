<?php
// Currency Converter System

// Get user's preferred currency from session or default to USD
function getCurrentCurrency() {
    return isset($_SESSION['currency']) ? $_SESSION['currency'] : 'USD';
}

// Set user's preferred currency
function setCurrency($currency) {
    $_SESSION['currency'] = $currency;
}

// Get currency symbol
function getCurrencySymbol($currency = null) {
    if ($currency === null) {
        $currency = getCurrentCurrency();
    }
    
    $symbols = [
        'USD' => '$',
        'KES' => 'KSh',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'CNY' => '¥',
        'INR' => '₹',
        'ZAR' => 'R',
        'NGN' => '₦',
        'GHS' => '₵',
    ];
    
    return $symbols[$currency] ?? $currency;
}

// Get available currencies
function getAvailableCurrencies() {
    return [
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'flag' => '🇺🇸'],
        'KES' => ['name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'flag' => '🇰🇪'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'flag' => '🇪🇺'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'flag' => '🇬🇧'],
        'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'flag' => '🇯🇵'],
        'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥', 'flag' => '🇨🇳'],
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'flag' => '🇮🇳'],
        'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R', 'flag' => '🇿🇦'],
        'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦', 'flag' => '🇳🇬'],
        'GHS' => ['name' => 'Ghanaian Cedi', 'symbol' => '₵', 'flag' => '🇬🇭'],
    ];
}

// Convert currency (using approximate exchange rates)
// In production, you should use a real-time API like exchangerate-api.com or fixer.io
function convertCurrency($amount, $fromCurrency = 'USD', $toCurrency = null) {
    if ($toCurrency === null) {
        $toCurrency = getCurrentCurrency();
    }
    
    if ($fromCurrency === $toCurrency) {
        return $amount;
    }
    
    // Exchange rates (approximate - update these or use an API)
    $exchangeRates = [
        'USD' => 1.0,
        'KES' => 130.0,  // 1 USD = ~130 KES
        'EUR' => 0.92,
        'GBP' => 0.79,
        'JPY' => 150.0,
        'CNY' => 7.2,
        'INR' => 83.0,
        'ZAR' => 18.5,
        'NGN' => 1600.0,
        'GHS' => 12.0,
    ];
    
    // Convert to USD first, then to target currency
    if (!isset($exchangeRates[$fromCurrency]) || !isset($exchangeRates[$toCurrency])) {
        return $amount; // Return original if currency not found
    }
    
    $amountInUSD = $amount / $exchangeRates[$fromCurrency];
    $convertedAmount = $amountInUSD * $exchangeRates[$toCurrency];
    
    return round($convertedAmount, 2);
}

// Format currency
function formatCurrency($amount, $currency = null, $showSymbol = true) {
    if ($currency === null) {
        $currency = getCurrentCurrency();
    }
    
    $symbol = $showSymbol ? getCurrencySymbol($currency) : '';
    $formatted = number_format($amount, 2);
    
    return $symbol . $formatted;
}
?>





