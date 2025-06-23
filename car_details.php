<?php
// filepath: c:\wamp64\www\Edensshop\car_details.php
session_start();
require 'config/database.php';

$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    header('Location: cars.php');
    exit();
}

// Get product details with seller information
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.username as seller_name, u.email as seller_email, u.phone as seller_phone
        FROM products p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ? AND p.status = 'active'
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: cars.php');
        exit();
    }
    
} catch (PDOException $e) {
    header('Location: cars.php');
    exit();
}

$page_title = htmlspecialchars($product['title']) . " - Eden's CarShop";

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Add your car details styles here -->
</head>
<body>
    <div class="container">
        <div class="car-details">
            <h1><?= htmlspecialchars($product['title']) ?></h1>
            
            <!-- Car Image -->
            <div class="car-image">
                <?php if (!empty($product['image1']) && file_exists("uploads/" . $product['image1'])): ?>
                    <img src="uploads/<?= htmlspecialchars($product['image1']) ?>" 
                         alt="<?= htmlspecialchars($product['title']) ?>">
                <?php else: ?>
                    <div class="no-image">No Image Available</div>
                <?php endif; ?>
            </div>
            
            <!-- Car Details -->
            <div class="car-info">
                <h2>$<?= $product['price'] ? number_format($product['price'], 2) : '0.00' ?></h2>
                <p><strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?></p>
                <?php if (!empty($product['model'])): ?>
                    <p><strong>Model:</strong> <?= htmlspecialchars($product['model']) ?></p>
                <?php endif; ?>
                <p><strong>Year:</strong> <?= $product['year'] ?></p>
                <p><strong>Description:</strong></p>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>
            
            <!-- Seller Info -->
            <div class="seller-info">
                <h3>Seller Information</h3>
                <p><strong>Seller:</strong> <?= htmlspecialchars($product['seller_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($product['seller_email']) ?></p>
                <?php if (!empty($product['seller_phone'])): ?>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($product['seller_phone']) ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Contact Actions -->
            <div class="contact-actions">
                <a href="mailto:<?= htmlspecialchars($product['seller_email']) ?>?subject=Interest in <?= urlencode($product['title']) ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-envelope"></i> Contact Seller
                </a>
                <a href="cars.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Browse
                </a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>