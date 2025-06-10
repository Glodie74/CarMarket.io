<?php
session_start();

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'buyer') {
    // Redirect to appropriate dashboard based on role
    if ($_SESSION['role'] === 'seller') {
        header("Location: Sellers_Dash.php");
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

require 'config/database.php';

// Get buyer's orders
try {
    $stmt = $pdo->prepare("SELECT o.*, p.title, p.image1, p.brand, p.make, p.year, u.username as seller_name FROM orders o JOIN products p ON o.product_id = p.id JOIN users u ON p.user_id = u.id WHERE o.buyer_id = ? ORDER BY o.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    // Orders table might not exist yet
    $orders = [];
}

// Get favorite products (if wishlist table exists)
try {
    $stmt = $pdo->prepare("SELECT p.*, u.username as seller_name FROM wishlist w JOIN products p ON w.product_id = p.id JOIN users u ON p.user_id = u.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $favorites = $stmt->fetchAll();
} catch (PDOException $e) {
    // Wishlist table might not exist yet
    $favorites = [];
}

// Get recently viewed products (if views table exists)
try {
    $stmt = $pdo->prepare("SELECT p.*, u.username as seller_name, v.viewed_at FROM product_views v JOIN products p ON v.product_id = p.id JOIN users u ON p.user_id = u.id WHERE v.user_id = ? ORDER BY v.viewed_at DESC LIMIT 10");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_views = $stmt->fetchAll();
} catch (PDOException $e) {
    // Views table might not exist yet
    $recent_views = [];
}

// Get latest products for recommendations
try {
    $stmt = $pdo->prepare("SELECT p.*, u.username as seller_name FROM products p JOIN users u ON p.user_id = u.id WHERE p.user_id != ? ORDER BY p.created_at DESC LIMIT 8");
    $stmt->execute([$_SESSION['user_id']]);
    $recommended_products = $stmt->fetchAll();
} catch (PDOException $e) {
    $recommended_products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - Eden's CarShop</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="container">
            <div class="dashboard-header">
                <div>
                    <h1><i class="fas fa-user"></i> Buyer Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-home"></i> Browse Cars</a>
                    <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($orders); ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($favorites); ?></h3>
                        <p>Favorites</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($recent_views); ?></h3>
                        <p>Recently Viewed</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($recommended_products); ?></h3>
                        <p>Recommendations</p>
                    </div>
                </div>
            </div>

            <!-- My Orders Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-shopping-bag"></i> My Orders (<?php echo count($orders); ?>)</h2>
                <?php if (count($orders) > 0): ?>
                    <div class="orders-grid">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-image">
                                    <img src="uploads/<?php echo htmlspecialchars($order['image1']); ?>" alt="<?php echo htmlspecialchars($order['title']); ?>">
                                    <div class="order-status">
                                        <span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                                    </div>
                                </div>
                                <div class="order-details">
                                    <h3><?php echo htmlspecialchars($order['title']); ?></h3>
                                    <div class="car-meta">
                                        <span><i class="fas fa-calendar"></i> <?php echo $order['year']; ?></span>
                                        <span><i class="fas fa-industry"></i> <?php echo htmlspecialchars($order['brand']); ?></span>
                                        <span><i class="fas fa-car"></i> <?php echo htmlspecialchars($order['make']); ?></span>
                                    </div>
                                    <p class="seller">Seller: <?php echo htmlspecialchars($order['seller_name']); ?></p>
                                    <p class="price">$<?php echo number_format($order['total_amount'], 2); ?></p>
                                    <p class="order-date"><i class="fas fa-clock"></i> Ordered: <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                                    <div class="order-actions">
                                        <button class="btn btn-small btn-primary">View Details</button>
                                        <button class="btn btn-small btn-secondary">Track Order</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-items">
                        <i class="fas fa-shopping-cart"></i>
                        <p>You haven't placed any orders yet.</p>
                        <p><a href="index.php" class="btn btn-primary">Start Shopping</a></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Favorites Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-heart"></i> My Favorites (<?php echo count($favorites); ?>)</h2>
                <?php if (count($favorites) > 0): ?>
                    <div class="products-grid">
                        <?php foreach ($favorites as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="uploads/<?php echo htmlspecialchars($product['image1']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    <div class="product-actions">
                                        <button class="btn-icon btn-remove" title="Remove from Favorites">
                                            <i class="fas fa-heart-broken"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-details">
                                    <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                                    <div class="car-meta">
                                        <span><i class="fas fa-calendar"></i> <?php echo $product['year']; ?></span>
                                        <span><i class="fas fa-road"></i> <?php echo number_format($product['mileage']); ?> km</span>
                                        <span><i class="fas fa-cogs"></i> <?php echo $product['transmission']; ?></span>
                                    </div>
                                    <p class="seller">Seller: <?php echo htmlspecialchars($product['seller_name']); ?></p>
                                    <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                                    <div class="product-actions-bottom">
                                        <button class="btn btn-small btn-primary">View Details</button>
                                        <button class="btn btn-small btn-success">Contact Seller</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-items">
                        <i class="fas fa-heart"></i>
                        <p>You haven't added any favorites yet.</p>
                        <p><a href="index.php" class="btn btn-primary">Browse Cars</a></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recommended Cars Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-star"></i> Recommended for You</h2>
                <?php if (count($recommended_products) > 0): ?>
                    <div class="products-grid">
                        <?php foreach (array_slice($recommended_products, 0, 4) as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="uploads/<?php echo htmlspecialchars($product['image1']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    <div class="product-actions">
                                        <button class="btn-icon btn-favorite" title="Add to Favorites">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-details">
                                    <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                                    <div class="car-meta">
                                        <span><i class="fas fa-calendar"></i> <?php echo $product['year']; ?></span>
                                        <span><i class="fas fa-road"></i> <?php echo number_format($product['mileage']); ?> km</span>
                                        <span><i class="fas fa-cogs"></i> <?php echo $product['transmission']; ?></span>
                                    </div>
                                    <p class="seller">Seller: <?php echo htmlspecialchars($product['seller_name']); ?></p>
                                    <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                                    <div class="product-actions-bottom">
                                        <button class="btn btn-small btn-primary">View Details</button>
                                        <button class="btn btn-small btn-success">Contact Seller</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="index.php" class="btn btn-primary">View All Cars</a>
                    </div>
                <?php else: ?>
                    <div class="no-items">
                        <i class="fas fa-star"></i>
                        <p>No recommendations available yet.</p>
                        <p><a href="index.php" class="btn btn-primary">Browse Cars</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<style>
/* Dashboard Container */
.dashboard-container {
    background: linear-gradient(135deg,rgb(6, 71, 11) 0%, #2e7d32 100%);
    min-height: 100vh;
    padding: 20px 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Dashboard Header */
.dashboard-header {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-header h1 {
    color: #1a4d3a;
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.dashboard-header p {
    color: #2d5a45;
    font-size: 1.1rem;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 15px;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.alert-success {
    background-color: #d4f6d4;
    color: #1a4d3a;
    border-left: 4px solid #2d7d32;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2d7d32, #388e3c);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    color: #1a4d3a;
    margin: 0 0 5px 0;
}

.stat-content p {
    color: #2d5a45;
    margin: 0;
    font-weight: 500;
}

/* Dashboard Sections */
.dashboard-section {
    background: white;
    padding: 30px;
    margin-bottom: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.dashboard-section h2 {
    color: #1a4d3a;
    margin-bottom: 25px;
    font-size: 1.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.btn-primary {
    background: linear-gradient(135deg, #2d7d32, #388e3c);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1b5e20, #2e7d32);
    transform: translateY(-2px);
}

.btn-secondary {
    background: linear-gradient(135deg, #4caf50, #66bb6a);
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #43a047, #66bb6a);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #d32f2f, #c62828);
    color: white;
}

.btn-small {
    padding: 8px 15px;
    font-size: 0.85rem;
}

.btn-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-favorite {
    background: linear-gradient(135deg, #d32f2f, #c62828);
    color: white;
}

.btn-remove {
    background: linear-gradient(135deg, #4caf50, #66bb6a);
    color: white;
}

/* Orders Grid */
.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

.order-card {
    border: 1px solid #c8e6c9;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(45, 125, 50, 0.2);
}

.order-image {
    position: relative;
}

.order-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.order-status {
    position: absolute;
    top: 10px;
    left: 10px;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.product-card {
    border: 1px solid #c8e6c9;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(45, 125, 50, 0.2);
}

.product-image {
    position: relative;
}

.product-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.product-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    gap: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-actions {
    opacity: 1;
}

.product-details,
.order-details {
    padding: 20px;
}

.product-details h3,
.order-details h3 {
    margin-bottom: 15px;
    color: #1a4d3a;
    font-size: 1.2rem;
    font-weight: 600;
}

.car-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin: 15px 0;
    color: #2d5a45;
    font-size: 0.9rem;
}

.car-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.seller {
    color: #2d5a45;
    margin: 10px 0;
    font-style: italic;
}

.price {
    font-size: 1.4rem;
    font-weight: bold;
    color: #2d7d32;
    margin: 10px 0;
}

.order-date {
    color: #2d5a45;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 15px;
}

.order-actions,
.product-actions-bottom {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.status {
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pending {
    background-color: #fff9c4;
    color: #f57f17;
}

.status-completed {
    background-color: #e8f5e8;
    color: #2d7d32;
}

.status-shipped {
    background-color: #e3f2fd;
    color: #1976d2;
}

.status-processing {
    background-color: #f0f4c3;
    color: #827717;
}

/* No Items State */
.no-items {
    text-align: center;
    padding: 60px 20px;
    color: #2d5a45;
}

.no-items i {
    font-size: 4rem;
    color: #a5d6a7;
    margin-bottom: 20px;
}

.no-items p {
    font-size: 1.1rem;
    margin-bottom: 15px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .orders-grid,
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .order-actions,
    .product-actions-bottom {
        flex-direction: column;
    }
}
</style>

</body>
</html>
