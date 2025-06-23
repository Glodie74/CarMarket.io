<?php
// filepath: c:\wamp64\www\Edensshop\cart.php
session_start();

// Use config/database.php
require 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=cart.php');
    exit();
}

$page_title = "Shopping Cart - Eden's CarShop";
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_quantity':
                    $cart_id = intval($_POST['cart_id']);
                    $quantity = intval($_POST['quantity']);
                    
                    if ($quantity > 0) {
                        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                        $stmt->execute([$quantity, $cart_id, $user_id]);
                        $success = "Cart updated successfully!";
                    } else {
                        throw new Exception("Invalid quantity");
                    }
                    break;
                    
                case 'remove_item':
                    $cart_id = intval($_POST['cart_id']);
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                    $stmt->execute([$cart_id, $user_id]);
                    $success = "Item removed from cart!";
                    break;
                    
                case 'clear_cart':
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $success = "Cart cleared successfully!";
                    break;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?redirect=cars.php');
        exit();
    }
    
    try {
        $product_id = intval($_POST['product_id']);
        $user_id = $_SESSION['user_id'];
        
        // Check if product exists and is active
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            throw new Exception("Product not found or unavailable");
        }
        
        // Check if user is not the seller
        if ($product['user_id'] == $user_id) {
            throw new Exception("You cannot add your own product to cart");
        }
        
        // Check if item already in cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->fetch()) {
            // Update quantity
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } else {
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$user_id, $product_id]);
        }
        
        header('Location: cart.php?success=1');
        exit();
        
    } catch (Exception $e) {
        $cart_error = $e->getMessage();
    }
}

// Get cart items
$cart_items = [];
$total_amount = 0;

try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.title, p.brand, p.model, p.year, p.price, p.image1, p.status,
               u.username as seller_name, u.email as seller_email
        FROM cart c
        JOIN products p ON c.product_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE c.user_id = ? AND p.status = 'active'
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total
    foreach ($cart_items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
    
} catch (PDOException $e) {
    $error = "Error loading cart: " . $e->getMessage();
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #1a5c2a;
            --accent-green: #4caf50;
            --light-green: #e8f5e9;
            --bg-gray: #f8f9fa;
            --text-dark: #333;
            --text-gray: #666;
            --border-light: #e0e0e0;
            --shadow: rgba(0, 0, 0, 0.1);
        }

        body {
            background: var(--bg-gray);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 25px var(--shadow);
            text-align: center;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .cart-items {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 25px var(--shadow);
        }

        .cart-item {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 120px;
            height: 90px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image {
            width: 100%;
            height: 100%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }

        .item-details {
            flex: 1;
        }

        .item-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .item-meta {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .item-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent-green);
        }

        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-input {
            width: 60px;
            padding: 5px;
            text-align: center;
            border: 1px solid var(--border-light);
            border-radius: 5px;
        }

        .btn-small {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .btn-update {
            background: var(--accent-green);
            color: white;
        }

        .btn-remove {
            background: #dc3545;
            color: white;
        }

        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 25px var(--shadow);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .summary-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-green);
            border-top: 2px solid var(--border-light);
            margin-top: 10px;
            padding-top: 15px;
        }

        .btn-checkout {
            width: 100%;
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s ease;
        }

        .btn-checkout:hover {
            background: var(--primary-green);
        }

        .btn-clear {
            width: 100%;
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }

        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px var(--shadow);
        }

        .empty-cart i {
            font-size: 4rem;
            color: var(--border-light);
            margin-bottom: 20px;
        }

        .btn-primary {
            background: var(--accent-green);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            display: inline-block;
            margin-top: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
            }
            
            .item-actions {
                flex-direction: row;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
            <p>Review your selected vehicles</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($cart_items)): ?>
            <div class="cart-content">
                <!-- Cart Items -->
                <div class="cart-items">
                    <h3><i class="fas fa-list"></i> Cart Items (<?= count($cart_items) ?>)</h3>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <?php if (!empty($item['image1']) && file_exists("uploads/" . $item['image1'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($item['image1']) ?>" 
                                         alt="<?= htmlspecialchars($item['title']) ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-car"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-details">
                                <div class="item-title"><?= htmlspecialchars($item['title']) ?></div>
                                <div class="item-meta">
                                    <?= htmlspecialchars($item['brand']) ?> 
                                    <?= !empty($item['model']) ? '• ' . htmlspecialchars($item['model']) : '' ?>
                                    • <?= $item['year'] ?>
                                    <br>
                                    Seller: <?= htmlspecialchars($item['seller_name']) ?>
                                </div>
                                <div class="item-price">$<?= number_format($item['price'] ?? 0, 2) ?></div>
                            </div>
                            
                            <div class="item-actions">
                                <form method="POST" class="quantity-controls">
                                    <input type="hidden" name="action" value="update_quantity">
                                    <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                                           min="1" max="10" class="quantity-input">
                                    <button type="submit" class="btn-small btn-update">Update</button>
                                </form>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="remove_item">
                                    <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn-small btn-remove" 
                                            onclick="return confirm('Remove this item from cart?')">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3><i class="fas fa-calculator"></i> Order Summary</h3>
                    
                    <div class="summary-row">
                        <span>Subtotal (<?= count($cart_items) ?> items):</span>
                        <span>$<?= number_format($total_amount, 2) ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Calculated at checkout</span>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span>$<?= number_format($total_amount, 2) ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn-checkout">
                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                    </a>
                    
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="clear_cart">
                        <button type="submit" class="btn-clear" 
                                onclick="return confirm('Clear entire cart?')">
                            <i class="fas fa-trash"></i> Clear Cart
                        </button>
                    </form>
                    
                    <a href="cars.php" class="btn-primary" style="text-align: center; margin-top: 15px;">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Start shopping to add items to your cart.</p>
                <a href="cars.php" class="btn-primary">
                    <i class="fas fa-car"></i> Browse Cars
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
