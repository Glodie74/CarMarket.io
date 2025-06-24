<?php
// filepath: c:\wamp64\www\Edensshop\cart_ajax.php
session_start();
require 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $product_id = intval($_POST['product_id']);
            
            // Check if product exists and is active
            $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND status = 'active'");
            $stmt->execute([$product_id]);
            
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Product not found or unavailable']);
                exit();
            }
            
            // Add to cart
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) 
                                   VALUES (?, ?, 1) 
                                   ON DUPLICATE KEY UPDATE quantity = quantity + 1");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            
            echo json_encode(['success' => true, 'message' => 'Product added to cart']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        if ($action === 'count') {
            $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $count = $stmt->fetchColumn() ?: 0;
            
            echo json_encode(['success' => true, 'count' => $count]);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>