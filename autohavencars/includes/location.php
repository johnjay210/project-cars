<?php
// Location Helper Functions

/**
 * Calculate distance between two coordinates using Haversine formula
 * Returns distance in miles
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    if (empty($lat1) || empty($lon1) || empty($lat2) || empty($lon2)) {
        return null;
    }
    
    $earthRadius = 3959; // Earth's radius in miles
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
}

/**
 * Get cars within radius of a location
 */
function getCarsNearby($latitude, $longitude, $radiusMiles = 50, $filters = []) {
    $conn = getDBConnection();
    
    // Base query
    $query = "SELECT c.*, u.username,
              (3959 * acos(cos(radians(?)) * cos(radians(COALESCE(c.latitude, 0))) * 
              cos(radians(COALESCE(c.longitude, 0)) - radians(?)) + 
              sin(radians(?)) * sin(radians(COALESCE(c.latitude, 0))))) AS distance
              FROM cars c
              JOIN users u ON c.user_id = u.id
              WHERE c.status = 'available' 
              AND c.latitude IS NOT NULL 
              AND c.longitude IS NOT NULL
              AND c.latitude != 0 
              AND c.longitude != 0";
    
    $params = [$latitude, $longitude, $latitude];
    $types = 'ddd';
    
    // Apply additional filters
    if (!empty($filters['make'])) {
        $query .= " AND c.make = ?";
        $params[] = $filters['make'];
        $types .= 's';
    }
    
    if (!empty($filters['min_price'])) {
        $query .= " AND c.price >= ?";
        $params[] = $filters['min_price'];
        $types .= 'd';
    }
    
    if (!empty($filters['max_price'])) {
        $query .= " AND c.price <= ?";
        $params[] = $filters['max_price'];
        $types .= 'd';
    }
    
    if (!empty($filters['year'])) {
        $query .= " AND c.year = ?";
        $params[] = $filters['year'];
        $types .= 'i';
    }
    
    // Filter by radius
    $query .= " HAVING distance <= ?
               ORDER BY distance ASC";
    $params[] = $radiusMiles;
    $types .= 'd';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cars = [];
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    
    return $cars;
}

/**
 * Geocode address to coordinates (using Nominatim - free, no API key)
 * Note: For production, consider using Google Geocoding API
 */
function geocodeAddress($address, $city = '', $state = '', $zipCode = '') {
    $fullAddress = trim(implode(', ', array_filter([$address, $city, $state, $zipCode])));
    
    if (empty($fullAddress)) {
        return null;
    }
    
    // Use Nominatim (OpenStreetMap geocoding) - free, no API key
    $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($fullAddress) . '&limit=1';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'AutoHavenCars/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
        return [
            'latitude' => (float)$data[0]['lat'],
            'longitude' => (float)$data[0]['lon']
        ];
    }
    
    return null;
}
?>






