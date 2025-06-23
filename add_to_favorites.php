<?php
session_start();
require_once 'config/database.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add favorites']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

try {
    // Create wishlist table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id)
    )");
    
    // Check if product exists and is active
    $product_check = $pdo->prepare("SELECT id, user_id FROM products WHERE id = ? AND status = 'active'");
    $product_check->execute([$product_id]);
    $product = $product_check->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found or unavailable']);
        exit;
    }
    
    // Prevent users from adding their own products to favorites
    if ($product['user_id'] == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'You cannot add your own product to favorites']);
        exit;
    }
    
    // Check if already in favorites
    $check_stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $check_stmt->execute([$_SESSION['user_id'], $product_id]);
    
    if ($check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Product already in favorites']);
        exit;
    }
    
    // Add to favorites
    $insert_stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $insert_stmt->execute([$_SESSION['user_id'], $product_id]);
    
    echo json_encode(['success' => true, 'message' => 'Added to favorites successfully']);
    
} catch (PDOException $e) {
    error_log("Error adding to favorites: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>