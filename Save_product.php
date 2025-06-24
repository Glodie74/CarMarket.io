<?php
// filepath: c:\wamp64\www\Edensshop\includes\Save_product.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Use the same database connection as the main application
require_once '../config/database.php';

// Save product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    try {
        $user_id = $_SESSION['user_id'];
        $title = trim($_POST['title'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        
        // Optional fields
        $mileage = !empty($_POST['mileage']) ? intval($_POST['mileage']) : null;
        $transmission = !empty($_POST['transmission']) ? trim($_POST['transmission']) : null;
        $fuel_type = !empty($_POST['fuel_type']) ? trim($_POST['fuel_type']) : null;
        $engine_size = !empty($_POST['engine_size']) ? trim($_POST['engine_size']) : null;
        $color = !empty($_POST['color']) ? trim($_POST['color']) : null;
        $condition_status = !empty($_POST['condition_status']) ? trim($_POST['condition_status']) : null;
        $body_type = !empty($_POST['body_type']) ? trim($_POST['body_type']) : null;

        // Validate required fields
        if (empty($title) || empty($brand) || empty($model) || $year <= 0 || $price <= 0 || empty($description)) {
            throw new Exception("Please fill in all required fields.");
        }

        // Handle image upload
        $image1 = null;
        if (isset($_FILES['image1']) && $_FILES['image1']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Check file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $fileExtension = strtolower(pathinfo($_FILES['image1']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedTypes)) {
                throw new Exception("Invalid file type. Please upload a valid image file.");
            }
            
            // Check file size (5MB limit)
            if ($_FILES['image1']['size'] > 5 * 1024 * 1024) {
                throw new Exception("File size too large. Maximum 5MB allowed.");
            }
            
            $fileName = 'car_' . $user_id . '_' . time() . '_1.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image1']['tmp_name'], $uploadPath)) {
                $image1 = $fileName;
            } else {
                throw new Exception("Failed to upload image.");
            }
        } else {
            throw new Exception("At least one image is required.");
        }

        // Insert into database using PDO
        $sql = "INSERT INTO products (user_id, title, brand, model, year, price, description, mileage, transmission, fuel_type, engine_size, color, condition_status, body_type, image1, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $user_id,
            $title,
            $brand,
            $model,
            $year,
            $price,
            $description,
            $mileage,
            $transmission,
            $fuel_type,
            $engine_size,
            $color,
            $condition_status,
            $body_type,
            $image1
        ]);

        if ($result) {
            header('Location: ../Sellers_Dash.php?success=' . urlencode('Product added successfully!'));
            exit();
        } else {
            throw new Exception("Database insert failed.");
        }

    } catch (Exception $e) {
        header('Location: ../Sellers_Dash.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: ../Sellers_Dash.php?error=' . urlencode('Invalid request.'));
    exit();
}
?>