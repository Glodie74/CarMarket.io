<?php
// This file handles deleting products
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

try {
    if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
        throw new Exception("Product ID is required.");
    }
    
    $product_id = (int)$_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    
    // First, get the product to verify ownership and get image paths
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception("Product not found or you don't have permission to delete it.");
    }
    
    // Delete the product from database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        // Delete associated images
        $upload_dir = 'uploads/';
        for ($i = 1; $i <= 5; $i++) {
            $image_field = "image$i";
            if (!empty($product[$image_field])) {
                $image_path = $upload_dir . $product[$image_field];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
        
        $message = "Product deleted successfully.";
        $message_type = "success";
        
        // Log the action
        error_log("Product deleted by user ID: " . $_SESSION['user_id'] . " - Product ID: " . $product_id);
    } else {
        throw new Exception("Failed to delete product.");
    }
    
} catch (Exception $e) {
    $message = $e->getMessage();
    $message_type = "error";
    error_log("Error deleting product: " . $e->getMessage());
}
?>