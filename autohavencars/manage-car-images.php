<?php
require_once 'config/database.php';
require_once 'includes/car_images.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Get car ID
$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($carId <= 0) {
    header('Location: my-cars.php');
    exit;
}

// Verify car belongs to user
$query = "SELECT * FROM cars WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $carId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();
$stmt->close();

if (!$car) {
    header('Location: my-cars.php');
    exit;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_image') {
    $uploadDir = 'assets/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExtension = strtolower(pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetPath)) {
                $imageType = $_POST['image_type'] ?? 'exterior';
                $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 999;
                
                if (addCarImage($carId, $targetPath, $imageType, $displayOrder)) {
                    $success = 'Image added successfully!';
                    
                    // Update main image if this is the first image
                    if (empty($car['image_path'])) {
                        $updateQuery = "UPDATE cars SET image_path = ? WHERE id = ?";
                        $updateStmt = $conn->prepare($updateQuery);
                        $updateStmt->bind_param('si', $targetPath, $carId);
                        $updateStmt->execute();
                        $updateStmt->close();
                        $car['image_path'] = $targetPath;
                    }
                } else {
                    $error = 'Failed to save image to database.';
                }
            } else {
                $error = 'Failed to upload image file.';
            }
        } else {
            $error = 'Invalid file type. Please upload JPG, PNG, GIF, or WEBP images.';
        }
    } else {
        $error = 'Please select an image file.';
    }
}

// Handle image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_image') {
    $imageId = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
    if ($imageId > 0) {
        if (deleteCarImage($imageId)) {
            $success = 'Image deleted successfully!';
        } else {
            $error = 'Failed to delete image.';
        }
    }
}

// Get all images for this car
$carImages = getCarImages($carId);

$pageTitle = 'Manage Car Images';
include 'includes/header.php';
?>

<main class="manage-images-page">
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-images"></i> Manage Car Images</h1>
            <a href="my-cars.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to My Cars
            </a>
        </div>
        
        <div class="car-info-card">
            <h2><?php echo htmlspecialchars($car['year'] . ' ' . $car['make'] . ' ' . $car['model']); ?></h2>
            <p class="car-price">$<?php echo number_format($car['price'], 2); ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- Add New Image Form -->
        <div class="add-image-section">
            <h3><i class="fas fa-plus-circle"></i> Add New Image</h3>
            <form action="manage-car-images.php?id=<?php echo $carId; ?>" method="POST" enctype="multipart/form-data" class="add-image-form">
                <input type="hidden" name="action" value="add_image">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_image"><i class="fas fa-image"></i> Select Image</label>
                        <input type="file" id="new_image" name="new_image" accept="image/*" required>
                        <small>JPG, PNG, GIF, or WEBP (Max 5MB)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_type"><i class="fas fa-tag"></i> Image Type</label>
                        <select id="image_type" name="image_type" required>
                            <option value="exterior">Exterior</option>
                            <option value="interior">Interior</option>
                            <option value="engine">Engine</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order"><i class="fas fa-sort-numeric-down"></i> Display Order</label>
                        <input type="number" id="display_order" name="display_order" min="0" value="<?php echo count($carImages); ?>" placeholder="0">
                        <small>Lower numbers appear first</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Image
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Existing Images -->
        <div class="images-section">
            <h3><i class="fas fa-images"></i> Current Images (<?php echo count($carImages); ?>)</h3>
            
            <?php if (empty($carImages)): ?>
                <div class="no-images">
                    <i class="fas fa-image"></i>
                    <p>No images uploaded yet. Add your first image above!</p>
                </div>
            <?php else: ?>
                <div class="images-grid">
                    <?php foreach ($carImages as $index => $image): ?>
                        <div class="image-item">
                            <div class="image-preview">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($image['image_type'] ?? 'car image'); ?>">
                                <div class="image-overlay">
                                    <a href="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                       target="_blank" 
                                       class="btn-icon" 
                                       title="View Full Size">
                                        <i class="fas fa-expand"></i>
                                    </a>
                                    <form action="manage-car-images.php?id=<?php echo $carId; ?>" 
                                          method="POST" 
                                          class="delete-form"
                                          onsubmit="return confirm('Are you sure you want to delete this image?');">
                                        <input type="hidden" name="action" value="delete_image">
                                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                        <button type="submit" class="btn-icon btn-danger" title="Delete Image">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="image-info">
                                <span class="image-type-badge type-<?php echo htmlspecialchars($image['image_type'] ?? 'exterior'); ?>">
                                    <i class="fas fa-<?php 
                                        echo $image['image_type'] === 'interior' ? 'couch' : 
                                            ($image['image_type'] === 'engine' ? 'cog' : 'car'); 
                                    ?>"></i>
                                    <?php echo ucfirst($image['image_type'] ?? 'exterior'); ?>
                                </span>
                                <span class="image-order">Order: <?php echo $image['display_order']; ?></span>
                                <?php if ($image['image_path'] === $car['image_path']): ?>
                                    <span class="main-image-badge">
                                        <i class="fas fa-star"></i> Main Image
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="help-section">
            <h4><i class="fas fa-info-circle"></i> Tips for Better Images</h4>
            <ul>
                <li>Upload high-quality images (at least 800x600 pixels)</li>
                <li>Use natural lighting for exterior photos</li>
                <li>Take photos from multiple angles</li>
                <li>Include interior, engine, and detail shots</li>
                <li>Keep file sizes under 5MB for faster loading</li>
            </ul>
        </div>
    </div>
</main>

<?php
$conn->close();
include 'includes/footer.php';
?>





