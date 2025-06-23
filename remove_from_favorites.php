<?php
session_start();
require_once 'config/database.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to manage favorites']);
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
    // Remove from favorites
    $delete_stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $delete_stmt->execute([$_SESSION['user_id'], $product_id]);
    
    if ($delete_stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Removed from favorites successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found in favorites']);
    }
    
} catch (PDOException $e) {
    error_log("Error removing from favorites: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>