<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $make = trim($_POST['make']);
    $model = trim($_POST['model']);
    $year = (int)$_POST['year'];
    $price = (float)$_POST['price'];
    $mileage = (int)$_POST['mileage'];
    $color = trim($_POST['color']);
    $fuel_type = trim($_POST['fuel_type']);
    $transmission = trim($_POST['transmission']);
    $description = trim($_POST['description']);
    $engineSize = trim($_POST['engine_size'] ?? '');
    $engineType = trim($_POST['engine_type'] ?? '');
    $doors = !empty($_POST['doors']) ? (int)$_POST['doors'] : null;
    $seats = !empty($_POST['seats']) ? (int)$_POST['seats'] : null;
    $driveType = trim($_POST['drive_type'] ?? '');
    $vinNumber = trim($_POST['vin_number'] ?? '');
    $conditionStatus = trim($_POST['condition_status'] ?? '');
    $previousOwners = !empty($_POST['previous_owners']) ? (int)$_POST['previous_owners'] : 1;
    $accidentHistory = trim($_POST['accident_history'] ?? 'none');
    $serviceHistory = isset($_POST['service_history']) ? 1 : 0;
    $features = trim($_POST['features'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zipCode = trim($_POST['zip_code'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
    $userId = $_SESSION['user_id'];
    
    // Handle image uploads (multiple images)
    $uploadDir = 'assets/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $imagePath = ''; // Main image for backward compatibility
    $uploadedImages = [];
    
    // Handle main image (single file input for backward compatibility)
    if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK) {
        $fileExtension = pathinfo($_FILES['car_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['car_image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
            $uploadedImages[] = ['path' => $targetPath, 'type' => 'exterior', 'order' => 0];
        }
    }
    
    // Handle multiple images
    if (isset($_FILES['car_images']) && is_array($_FILES['car_images']['name'])) {
        $imageTypes = $_POST['image_types'] ?? [];
        $imageOrders = $_POST['image_orders'] ?? [];
        
        for ($i = 0; $i < count($_FILES['car_images']['name']); $i++) {
            if ($_FILES['car_images']['error'][$i] === UPLOAD_ERR_OK) {
                $fileExtension = pathinfo($_FILES['car_images']['name'][$i], PATHINFO_EXTENSION);
                $fileName = uniqid() . '_' . $i . '.' . $fileExtension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['car_images']['tmp_name'][$i], $targetPath)) {
                    $imgType = $imageTypes[$i] ?? 'exterior';
                    $imgOrder = isset($imageOrders[$i]) ? (int)$imageOrders[$i] : count($uploadedImages);
                    $uploadedImages[] = ['path' => $targetPath, 'type' => $imgType, 'order' => $imgOrder];
                    
                    // Set first image as main image if not set
                    if (empty($imagePath)) {
                        $imagePath = $targetPath;
                    }
                }
            }
        }
    }
    
    // Validate input
    if (empty($make) || empty($model) || $year < 1900 || $price <= 0 || $mileage < 0) {
        $error = 'Please fill in all required fields correctly.';
    } else {
        // Check which columns exist
        $checkLocation = $conn->query("SHOW COLUMNS FROM cars LIKE 'city'");
        $hasLocation = $checkLocation->num_rows > 0;
        
        $checkSpecs = $conn->query("SHOW COLUMNS FROM cars LIKE 'engine_size'");
        $hasSpecs = $checkSpecs->num_rows > 0;
        
        if ($hasLocation && $hasSpecs) {
            // Full version with location and specs
            $query = "INSERT INTO cars (user_id, make, model, year, price, mileage, color, fuel_type, transmission, engine_size, engine_type, doors, seats, drive_type, vin_number, condition_status, previous_owners, accident_history, service_history, features, description, image_path, city, state, zip_code, address, latitude, longitude) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('issiissssssiisssiissssssssdd', $userId, $make, $model, $year, $price, $mileage, $color, $fuel_type, $transmission, $engineSize, $engineType, $doors, $seats, $driveType, $vinNumber, $conditionStatus, $previousOwners, $accidentHistory, $serviceHistory, $features, $description, $imagePath, $city, $state, $zipCode, $address, $latitude, $longitude);
        } elseif ($hasLocation) {
            // With location but no specs
            $query = "INSERT INTO cars (user_id, make, model, year, price, mileage, color, fuel_type, transmission, description, image_path, city, state, zip_code, address, latitude, longitude) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('issiissssssssssdd', $userId, $make, $model, $year, $price, $mileage, $color, $fuel_type, $transmission, $description, $imagePath, $city, $state, $zipCode, $address, $latitude, $longitude);
        } elseif ($hasSpecs) {
            // With specs but no location
            $query = "INSERT INTO cars (user_id, make, model, year, price, mileage, color, fuel_type, transmission, engine_size, engine_type, doors, seats, drive_type, vin_number, condition_status, previous_owners, accident_history, service_history, features, description, image_path) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('issiissssssiisssiissss', $userId, $make, $model, $year, $price, $mileage, $color, $fuel_type, $transmission, $engineSize, $engineType, $doors, $seats, $driveType, $vinNumber, $conditionStatus, $previousOwners, $accidentHistory, $serviceHistory, $features, $description, $imagePath);
        } else {
            // Basic version
            $query = "INSERT INTO cars (user_id, make, model, year, price, mileage, color, fuel_type, transmission, description, image_path) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('issiissssss', $userId, $make, $model, $year, $price, $mileage, $color, $fuel_type, $transmission, $description, $imagePath);
        }
        
        if ($stmt->execute()) {
            $newCarId = $conn->insert_id;
            
            // Save multiple images if car_images table exists
            if (!empty($uploadedImages)) {
                require_once 'includes/car_images.php';
                $checkTable = $conn->query("SHOW TABLES LIKE 'car_images'");
                if ($checkTable->num_rows > 0) {
                    foreach ($uploadedImages as $img) {
                        addCarImage($newCarId, $img['path'], $img['type'], $img['order']);
                    }
                }
            }
            
            $success = 'Car listed successfully!';
            // Clear form data
            $_POST = [];
        } else {
            $error = 'Error listing car. Please try again.';
        }
        $stmt->close();
    }
}

$pageTitle = 'Sell Your Car';
include 'includes/header.php';
?>

<main class="post-car-page">
    <div class="container">
        <h1>List Your Car for Sale</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form action="post-car.php" method="POST" enctype="multipart/form-data" class="post-car-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="make">Make *</label>
                    <input type="text" id="make" name="make" required value="<?php echo isset($_POST['make']) ? htmlspecialchars($_POST['make']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="model">Model *</label>
                    <input type="text" id="model" name="model" required value="<?php echo isset($_POST['model']) ? htmlspecialchars($_POST['model']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="year">Year *</label>
                    <input type="number" id="year" name="year" required min="1900" max="<?php echo date('Y') + 1; ?>" value="<?php echo isset($_POST['year']) ? htmlspecialchars($_POST['year']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="price">Price ($) *</label>
                    <input type="number" id="price" name="price" required min="0" step="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="mileage">Mileage *</label>
                    <input type="number" id="mileage" name="mileage" required min="0" value="<?php echo isset($_POST['mileage']) ? htmlspecialchars($_POST['mileage']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" value="<?php echo isset($_POST['color']) ? htmlspecialchars($_POST['color']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="fuel_type">Fuel Type</label>
                    <select id="fuel_type" name="fuel_type">
                        <option value="">Select</option>
                        <option value="Gasoline" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'Gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                        <option value="Diesel" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                        <option value="Electric" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'Electric') ? 'selected' : ''; ?>>Electric</option>
                        <option value="Hybrid" <?php echo (isset($_POST['fuel_type']) && $_POST['fuel_type'] === 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="transmission">Transmission</label>
                    <select id="transmission" name="transmission">
                        <option value="">Select</option>
                        <option value="Automatic" <?php echo (isset($_POST['transmission']) && $_POST['transmission'] === 'Automatic') ? 'selected' : ''; ?>>Automatic</option>
                        <option value="Manual" <?php echo (isset($_POST['transmission']) && $_POST['transmission'] === 'Manual') ? 'selected' : ''; ?>>Manual</option>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <h3 style="margin-top: 1rem; margin-bottom: 0.5rem; color: var(--text-dark);">Additional Specifications</h3>
                    <p style="font-size: 0.875rem; color: var(--text-light); margin-bottom: 1rem;">Provide more details about your car</p>
                </div>
                
                <div class="form-group">
                    <label for="engine_size"><i class="fas fa-cog"></i> Engine Size</label>
                    <input type="text" id="engine_size" name="engine_size" value="<?php echo isset($_POST['engine_size']) ? htmlspecialchars($_POST['engine_size']) : ''; ?>" placeholder="e.g., 2.0L, 3.5L V6">
                </div>
                
                <div class="form-group">
                    <label for="engine_type"><i class="fas fa-tools"></i> Engine Type</label>
                    <input type="text" id="engine_type" name="engine_type" value="<?php echo isset($_POST['engine_type']) ? htmlspecialchars($_POST['engine_type']) : ''; ?>" placeholder="e.g., V6, V8, Inline-4, Turbo">
                </div>
                
                <div class="form-group">
                    <label for="doors"><i class="fas fa-door-open"></i> Number of Doors</label>
                    <select id="doors" name="doors">
                        <option value="">Select</option>
                        <option value="2" <?php echo (isset($_POST['doors']) && $_POST['doors'] == '2') ? 'selected' : ''; ?>>2 Doors</option>
                        <option value="4" <?php echo (isset($_POST['doors']) && $_POST['doors'] == '4') ? 'selected' : ''; ?>>4 Doors</option>
                        <option value="5" <?php echo (isset($_POST['doors']) && $_POST['doors'] == '5') ? 'selected' : ''; ?>>5 Doors (Hatchback)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="seats"><i class="fas fa-users"></i> Number of Seats</label>
                    <select id="seats" name="seats">
                        <option value="">Select</option>
                        <option value="2" <?php echo (isset($_POST['seats']) && $_POST['seats'] == '2') ? 'selected' : ''; ?>>2 Seats</option>
                        <option value="4" <?php echo (isset($_POST['seats']) && $_POST['seats'] == '4') ? 'selected' : ''; ?>>4 Seats</option>
                        <option value="5" <?php echo (isset($_POST['seats']) && $_POST['seats'] == '5') ? 'selected' : ''; ?>>5 Seats</option>
                        <option value="6" <?php echo (isset($_POST['seats']) && $_POST['seats'] == '6') ? 'selected' : ''; ?>>6 Seats</option>
                        <option value="7" <?php echo (isset($_POST['seats']) && $_POST['seats'] == '7') ? 'selected' : ''; ?>>7 Seats</option>
                        <option value="8" <?php echo (isset($_POST['seats']) && $_POST['seats'] == '8') ? 'selected' : ''; ?>>8+ Seats</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="drive_type"><i class="fas fa-car-side"></i> Drive Type</label>
                    <select id="drive_type" name="drive_type">
                        <option value="">Select</option>
                        <option value="FWD" <?php echo (isset($_POST['drive_type']) && $_POST['drive_type'] === 'FWD') ? 'selected' : ''; ?>>Front-Wheel Drive (FWD)</option>
                        <option value="RWD" <?php echo (isset($_POST['drive_type']) && $_POST['drive_type'] === 'RWD') ? 'selected' : ''; ?>>Rear-Wheel Drive (RWD)</option>
                        <option value="AWD" <?php echo (isset($_POST['drive_type']) && $_POST['drive_type'] === 'AWD') ? 'selected' : ''; ?>>All-Wheel Drive (AWD)</option>
                        <option value="4WD" <?php echo (isset($_POST['drive_type']) && $_POST['drive_type'] === '4WD') ? 'selected' : ''; ?>>Four-Wheel Drive (4WD)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="vin_number"><i class="fas fa-barcode"></i> VIN Number (Optional)</label>
                    <input type="text" id="vin_number" name="vin_number" maxlength="17" value="<?php echo isset($_POST['vin_number']) ? htmlspecialchars($_POST['vin_number']) : ''; ?>" placeholder="17-character VIN">
                    <small>Vehicle Identification Number</small>
                </div>
                
                <div class="form-group">
                    <label for="condition_status"><i class="fas fa-star"></i> Condition</label>
                    <select id="condition_status" name="condition_status">
                        <option value="">Select</option>
                        <option value="excellent" <?php echo (isset($_POST['condition_status']) && $_POST['condition_status'] === 'excellent') ? 'selected' : ''; ?>>Excellent</option>
                        <option value="good" <?php echo (isset($_POST['condition_status']) && $_POST['condition_status'] === 'good') ? 'selected' : ''; ?>>Good</option>
                        <option value="fair" <?php echo (isset($_POST['condition_status']) && $_POST['condition_status'] === 'fair') ? 'selected' : ''; ?>>Fair</option>
                        <option value="poor" <?php echo (isset($_POST['condition_status']) && $_POST['condition_status'] === 'poor') ? 'selected' : ''; ?>>Poor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="previous_owners"><i class="fas fa-user-friends"></i> Previous Owners</label>
                    <input type="number" id="previous_owners" name="previous_owners" min="1" value="<?php echo isset($_POST['previous_owners']) ? htmlspecialchars($_POST['previous_owners']) : '1'; ?>" placeholder="1">
                    <small>Number of previous owners (including current)</small>
                </div>
                
                <div class="form-group">
                    <label for="accident_history"><i class="fas fa-exclamation-triangle"></i> Accident History</label>
                    <select id="accident_history" name="accident_history">
                        <option value="none" <?php echo (isset($_POST['accident_history']) && $_POST['accident_history'] === 'none') ? 'selected' : ''; ?>>No Accidents</option>
                        <option value="minor" <?php echo (isset($_POST['accident_history']) && $_POST['accident_history'] === 'minor') ? 'selected' : ''; ?>>Minor Accidents</option>
                        <option value="moderate" <?php echo (isset($_POST['accident_history']) && $_POST['accident_history'] === 'moderate') ? 'selected' : ''; ?>>Moderate Accidents</option>
                        <option value="major" <?php echo (isset($_POST['accident_history']) && $_POST['accident_history'] === 'major') ? 'selected' : ''; ?>>Major Accidents</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="service_history">
                        <input type="checkbox" id="service_history" name="service_history" value="1" <?php echo (isset($_POST['service_history']) && $_POST['service_history']) ? 'checked' : ''; ?>>
                        <i class="fas fa-wrench"></i> Complete Service History Available
                    </label>
                    <small>Check if you have all service records</small>
                </div>
                
                <div class="form-group full-width">
                    <label for="features"><i class="fas fa-list"></i> Features & Amenities</label>
                    <textarea id="features" name="features" rows="4" placeholder="List features like: Sunroof, Leather Seats, Navigation System, Backup Camera, Bluetooth, etc. (one per line)"><?php echo isset($_POST['features']) ? htmlspecialchars($_POST['features']) : ''; ?></textarea>
                    <small>List key features and amenities (one per line)</small>
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" placeholder="Describe your car in detail..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label for="car_image">Main Car Image *</label>
                    <input type="file" id="car_image" name="car_image" accept="image/*" required>
                    <small>Upload the main photo of your car (JPG, PNG, etc.)</small>
                </div>
                
                <div class="form-group full-width">
                    <label>Additional Photos (Optional)</label>
                    <div id="additional-images-container">
                        <div class="image-upload-item">
                            <input type="file" name="car_images[]" accept="image/*" class="additional-image-input">
                            <select name="image_types[]" class="image-type-select">
                                <option value="exterior">Exterior</option>
                                <option value="interior">Interior</option>
                                <option value="engine">Engine</option>
                                <option value="other">Other</option>
                            </select>
                            <input type="number" name="image_orders[]" value="1" min="0" class="image-order-input" placeholder="Order">
                            <button type="button" class="btn btn-danger btn-sm remove-image-btn" onclick="this.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" id="add-image-btn" class="btn btn-secondary btn-sm">
                        <i class="fas fa-plus"></i> Add Another Photo
                    </button>
                    <small>Upload multiple photos (exterior, interior, engine, etc.)</small>
                </div>
                
                <div class="form-group full-width">
                    <h3 style="margin-top: 1rem; margin-bottom: 0.5rem; color: var(--text-dark);">Location</h3>
                    <p style="font-size: 0.875rem; color: var(--text-light); margin-bottom: 1rem;">Help buyers find your car by providing its location</p>
                </div>
                
                <div class="form-group">
                    <label for="city"><i class="fas fa-map-marker-alt"></i> City *</label>
                    <input type="text" id="city" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" placeholder="e.g., New York">
                </div>
                
                <div class="form-group">
                    <label for="state"><i class="fas fa-map"></i> State/Province *</label>
                    <input type="text" id="state" name="state" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" placeholder="e.g., NY or California">
                </div>
                
                <div class="form-group">
                    <label for="zip_code"><i class="fas fa-mail-bulk"></i> ZIP/Postal Code</label>
                    <input type="text" id="zip_code" name="zip_code" value="<?php echo isset($_POST['zip_code']) ? htmlspecialchars($_POST['zip_code']) : ''; ?>" placeholder="e.g., 10001">
                </div>
                
                <div class="form-group full-width">
                    <label for="address"><i class="fas fa-home"></i> Street Address (Optional)</label>
                    <input type="text" id="address" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" placeholder="Street address (optional, for map display)">
                    <small>You can enter a general area if you prefer not to share exact address</small>
                </div>
                
                <div class="form-group">
                    <label for="latitude"><i class="fas fa-globe"></i> Latitude (Optional)</label>
                    <input type="number" id="latitude" name="latitude" step="any" value="<?php echo isset($_POST['latitude']) ? htmlspecialchars($_POST['latitude']) : ''; ?>" placeholder="e.g., 40.7128">
                    <small>For map display - leave empty to auto-generate</small>
                </div>
                
                <div class="form-group">
                    <label for="longitude"><i class="fas fa-globe"></i> Longitude (Optional)</label>
                    <input type="number" id="longitude" name="longitude" step="any" value="<?php echo isset($_POST['longitude']) ? htmlspecialchars($_POST['longitude']) : ''; ?>" placeholder="e.g., -74.0060">
                    <small>For map display - leave empty to auto-generate</small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">List Car</button>
                <a href="listings.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<script>
// Add image upload functionality
document.getElementById('add-image-btn')?.addEventListener('click', function() {
    const container = document.getElementById('additional-images-container');
    const newItem = document.createElement('div');
    newItem.className = 'image-upload-item';
    const order = container.children.length + 1;
    newItem.innerHTML = `
        <input type="file" name="car_images[]" accept="image/*" class="additional-image-input">
        <select name="image_types[]" class="image-type-select">
            <option value="exterior">Exterior</option>
            <option value="interior">Interior</option>
            <option value="engine">Engine</option>
            <option value="other">Other</option>
        </select>
        <input type="number" name="image_orders[]" value="${order}" min="0" class="image-order-input" placeholder="Order">
        <button type="button" class="btn btn-danger btn-sm remove-image-btn" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(newItem);
});
</script>

<?php
$conn->close();
include 'includes/footer.php';
?>

