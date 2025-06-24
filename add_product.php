<?php
// filepath: c:\wamp64\www\Edensshop\Sellers_Dash.php
session_start();


// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'seller' && $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

require 'config/database.php';

// This file handles adding new products
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

try {
    // Validate required fields
    $required_fields = ['title', 'brand', 'model', 'year', 'price', 'mileage', 'fuel_type', 'transmission'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Please fill in all required fields.");
        }
    }
    
    // Validate and sanitize data
    $title = trim($_POST['title']);
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $year = (int)$_POST['year'];
    $price = (float)$_POST['price'];
    $mileage = (int)$_POST['mileage'];
    $fuel_type = trim($_POST['fuel_type']);
    $transmission = trim($_POST['transmission']);
    $body_type = trim($_POST['body_type'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validate year range
    $current_year = date('Y');
    if ($year < 1990 || $year > $current_year + 1) {
        throw new Exception("Please enter a valid year between 1990 and " . ($current_year + 1));
    }
    
    // Validate price and mileage
    if ($price <= 0) {
        throw new Exception("Price must be greater than 0");
    }
    
    if ($mileage < 0) {
        throw new Exception("Mileage cannot be negative");
    }
    
    // Handle image uploads
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $image_paths = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Process up to 5 images
    for ($i = 1; $i <= 5; $i++) {
        $image_key = "image$i";
        
        if (isset($_FILES[$image_key]) && $_FILES[$image_key]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$image_key];
            
            // Validate file type
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception("Image $i: Please upload only JPEG, PNG, GIF, or WebP images.");
            }
            
            // Validate file size
            if ($file['size'] > $max_size) {
                throw new Exception("Image $i: File size must be less than 5MB.");
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $image_paths[$image_key] = $filename;
            } else {
                throw new Exception("Failed to upload image $i. Please try again.");
            }
        } elseif ($i === 1) {
            // First image is required
            throw new Exception("Please upload at least one image of your car.");
        }
    }
    
    // Insert product into database
    $sql = "INSERT INTO products (
        user_id, title, brand, model, year, price, mileage, 
        fuel_type, transmission, body_type, color, description,
 image1, image2, image3, image4, image5, status, created_at
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_SESSION['user_id'],
        $title,
        $brand,
        $model,
        $year,
        $price,
        $mileage,
        $fuel_type,
        $transmission,
        $body_type,
        $color,
        $description,
        $image_paths['image1'] ?? null,
        $image_paths['image2'] ?? null,
        $image_paths['image3'] ?? null,
        $image_paths['image4'] ?? null,
        $image_paths['image5'] ?? null
    ]);
    
    $message = "Product added successfully! It will be reviewed and activated shortly.";
    $message_type = "success";
    
    // Log the action
    error_log("Product added by user ID: " . $_SESSION['user_id'] . " - Title: " . $title);
    
} catch (Exception $e) {
    $message = $e->getMessage();
    $message_type = "error";
    
    // Clean up uploaded images if there was an error
    if (isset($image_paths)) {
        foreach ($image_paths as $image_path) {
            if (file_exists($upload_dir . $image_path)) {
                unlink($upload_dir . $image_path);
            }
        }
    }
    
    error_log("Error adding product: " . $e->getMessage());
}
?>
