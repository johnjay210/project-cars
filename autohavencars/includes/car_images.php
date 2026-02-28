<?php
// Car Images Helper Functions

/**
 * Check if car_images table exists
 */
function carImagesTableExists() {
    $conn = getDBConnection();
    $result = $conn->query("SHOW TABLES LIKE 'car_images'");
    $exists = $result->num_rows > 0;
    $conn->close();
    return $exists;
}

/**
 * Get all images for a car
 */
function getCarImages($carId) {
    if (!carImagesTableExists()) {
        return [];
    }
    
    $conn = getDBConnection();
    $query = "SELECT * FROM car_images WHERE car_id = ? ORDER BY display_order ASC, created_at ASC";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $conn->close();
        return [];
    }
    
    $stmt->bind_param('i', $carId);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $images;
}

/**
 * Add image to car
 */
function addCarImage($carId, $imagePath, $imageType = 'exterior', $displayOrder = 0) {
    if (!carImagesTableExists()) {
        return false;
    }
    
    $conn = getDBConnection();
    $query = "INSERT INTO car_images (car_id, image_path, image_type, display_order) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $conn->close();
        return false;
    }
    
    $stmt->bind_param('issi', $carId, $imagePath, $imageType, $displayOrder);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

/**
 * Delete car image
 */
function deleteCarImage($imageId) {
    if (!carImagesTableExists()) {
        return false;
    }
    
    $conn = getDBConnection();
    
    // Get image path first
    $query = "SELECT image_path FROM car_images WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $conn->close();
        return false;
    }
    
    $stmt->bind_param('i', $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $image = $result->fetch_assoc();
        $imagePath = $image['image_path'];
        
        // Delete from database
        $deleteQuery = "DELETE FROM car_images WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        if ($deleteStmt) {
            $deleteStmt->bind_param('i', $imageId);
            $deleteStmt->execute();
            $deleteStmt->close();
        }
        
        // Delete file
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $stmt->close();
    $conn->close();
    return true;
}

/**
 * Get similar cars for comparison
 */
function getSimilarCars($carId, $make = null, $model = null, $year = null, $limit = 5) {
    $conn = getDBConnection();
    
    $query = "SELECT c.*, u.username FROM cars c 
              JOIN users u ON c.user_id = u.id 
              WHERE c.id != ? AND c.status = 'available'";
    $params = [$carId];
    $types = 'i';
    
    if ($make) {
        $query .= " AND c.make = ?";
        $params[] = $make;
        $types .= 's';
    }
    
    if ($model) {
        $query .= " AND c.model = ?";
        $params[] = $model;
        $types .= 's';
    }
    
    if ($year) {
        // Find cars within 3 years
        $query .= " AND c.year BETWEEN ? AND ?";
        $params[] = $year - 3;
        $params[] = $year + 3;
        $types .= 'ii';
    }
    
    // Build ORDER BY clause
    $orderBy = [];
    if ($make) {
        $orderBy[] = "CASE WHEN c.make = ? THEN 1 ELSE 2 END";
        $params[] = $make;
        $types .= 's';
    }
    if ($model) {
        $orderBy[] = "CASE WHEN c.model = ? THEN 1 ELSE 2 END";
        $params[] = $model;
        $types .= 's';
    }
    if ($year) {
        $orderBy[] = "ABS(c.year - ?) ASC";
        $params[] = $year;
        $types .= 'i';
    }
    $orderBy[] = "c.price ASC";
    
    $query .= " ORDER BY " . implode(', ', $orderBy) . " LIMIT ?";
    $params[] = $limit;
    $types .= 'i';
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $conn->close();
        return [];
    }
    
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
?>

