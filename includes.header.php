<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarMarket.com - Used Car Marketplace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-badge {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1 class="logo">CarMarket<span>.com</span></h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="cars.php">Browse Cars</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="cart.php">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <?php
                                // Get cart count
                                try {
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    $cart_count = $stmt->fetchColumn();
                                    if ($cart_count > 0): ?>
                                        <span class="cart-badge"><?= $cart_count ?></span>
                                    <?php endif;
                                } catch (Exception $e) { }
                                ?>
                            </a>
                        </li>
                        <?php if ($_SESSION['role'] === 'seller' || $_SESSION['role'] === 'admin'): ?>
                            <li><a href="Sellers_Dash.php">Seller Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="my_orders.php">My Orders</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">
            <h2>Welcome to CarMarket.com</h2>
            <p>Your trusted marketplace for buying and selling quality used cars.</p>
            <a href="search.php" class="btn">Browse Cars</a>
        </div>