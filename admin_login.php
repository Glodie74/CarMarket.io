<?php
session_start();
include('includes/db.php');

// Check if the user is logged in as admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = htmlspecialchars(trim($_POST['status']));

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $success = "Order status updated successfully.";
    } else {
        $error = "Failed to update order status.";
    }
    $stmt->close();
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(p.price) as revenue FROM orders o JOIN products p ON o.product_id = p.id WHERE o.status = 'completed'")->fetch_assoc()['revenue'] ?? 0;

// Get recent orders
$orders_query = "
    SELECT o.*, p.title, p.price, u1.username as buyer_name, u2.username as seller_name 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    JOIN users u1 ON o.buyer_id = u1.id 
    JOIN users u2 ON p.user_id = u2.id 
    ORDER BY o.created_at DESC 
    LIMIT 20
";
$orders_result = $conn->query($orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" onclick="showSection('dashboard')">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="#orders" class="nav-item" onclick="showSection('orders')">
                    <i class="fas fa-shopping-bag"></i> Orders
                </a>
                <a href="#users" class="nav-item" onclick="showSection('users')">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="#products" class="nav-item" onclick="showSection('products')">
                    <i class="fas fa-car"></i> Products
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <div class="main-content">
            <div class="content-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, Administrator!</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_users); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_products); ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orders">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_orders); ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h2>Recent Orders</h2>
                    <div class="activity-list">
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="activity-content">
                                    <h4><?php echo htmlspecialchars($order['buyer_name']); ?> ordered <?php echo htmlspecialchars($order['title']); ?></h4>
                                    <p>From <?php echo htmlspecialchars($order['seller_name']); ?> • $<?php echo number_format($order['price'], 2); ?></p>
                                    <span class="activity-time"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="activity-status">
                                    <span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Orders Section -->
            <div id="orders" class="content-section">
                <h2>Order Management</h2>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th>Buyer</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orders_result = $conn->query($orders_query);
                            while ($order = $orders_result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['title']); ?></td>
                                    <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['seller_name']); ?></td>
                                    <td>$<?php echo number_format($order['price'], 2); ?></td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <button class="action-btn view" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f8f9fa;
    color: #333;
}

.admin-container {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 280px;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    padding: 30px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
}

.sidebar-nav {
    padding: 20px 0;
}

.nav-item {
    display: block;
    padding: 15px 25px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.nav-item:hover,
.nav-item.active {
    background: rgba(255,255,255,0.1);
    color: white;
    border-left-color: #3498db;
}

.nav-item.logout {
    margin-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.nav-item.logout:hover {
    background: rgba(231, 76, 60, 0.2);
    border-left-color: #e74c3c;
}

.nav-item i {
    margin-right: 10px;
    width: 20px;
}

.main-content {
    margin-left: 280px;
    padding: 30px;
    width: calc(100% - 280px);
}

.content-header {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.content-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #3498db 0%, #2980b9 100%);
}

.content-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    color: #2c3e50;
}

.content-header p {
    color: #666;
    font-size: 1.1rem;
}

.content-section {
    display: none;
}

.content-section.active {
    display: block;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
}

.stat-icon.users {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.stat-icon.products {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.stat-icon.orders {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.stat-icon.revenue {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
}

.stat-info h3 {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stat-info p {
    color: #666;
    font-size: 0.95rem;
}

.recent-activity {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.recent-activity h2 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.5rem;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
    display: inline-block;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.3s ease;
}

.activity-item:hover {
    background-color: #f8f9fa;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
}

.activity-content {
    flex: 1;
}

.activity-content h4 {
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 1rem;
}

.activity-content p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.activity-time {
    font-size: 0.8rem;
    color: #999;
}

.activity-status {
    margin-left: auto;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.pending {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
}

.status-badge.completed {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.status-badge.cancelled {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
}

.table-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
}

.admin-table td {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9rem;
}

.admin-table tr:hover {
    background-color: #f8f9fa;
}

.status-form select {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: white;
    font-size: 0.85rem;
}

.action-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    margin: 0 2px;
}

.action-btn.view {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.action-btn:hover {
    transform: scale(1.1);
}

.alert {
    padding: 15px 20px;
    margin: 20px 0;<?php
session_start();
include('includes/db.php');

// Check if the user is logged in as admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = htmlspecialchars(trim($_POST['status']));

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $success = "Order status updated successfully.";
    } else {
        $error = "Failed to update order status.";
    }
    $stmt->close();
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(p.price) as revenue FROM orders o JOIN products p ON o.product_id = p.id WHERE o.status = 'completed'")->fetch_assoc()['revenue'] ?? 0;

// Get recent orders
$orders_query = "
    SELECT o.*, p.title, p.price, u1.username as buyer_name, u2.username as seller_name 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    JOIN users u1 ON o.buyer_id = u1.id 
    JOIN users u2 ON p.user_id = u2.id 
    ORDER BY o.created_at DESC 
    LIMIT 20
";
$orders_result = $conn->query($orders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" onclick="showSection('dashboard')">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="#orders" class="nav-item" onclick="showSection('orders')">
                    <i class="fas fa-shopping-bag"></i> Orders
                </a>
                <a href="#users" class="nav-item" onclick="showSection('users')">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="#products" class="nav-item" onclick="showSection('products')">
                    <i class="fas fa-car"></i> Products
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <div class="main-content">
            <div class="content-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, Administrator!</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_users); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_products); ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orders">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_orders); ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>

                <div class="recent-activity">
                    <h2>Recent Orders</h2>
                    <div class="activity-list">
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="activity-content">
                                    <h4><?php echo htmlspecialchars($order['buyer_name']); ?> ordered <?php echo htmlspecialchars($order['title']); ?></h4>
                                    <p>From <?php echo htmlspecialchars($order['seller_name']); ?> • $<?php echo number_format($order['price'], 2); ?></p>
                                    <span class="activity-time"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="activity-status">
                                    <span class="status-badge <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Orders Section -->
            <div id="orders" class="content-section">
                <h2>Order Management</h2>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th>Buyer</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orders_result = $conn->query($orders_query);
                            while ($order = $orders_result->fetch_assoc()):
                            ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['title']); ?></td>
                                    <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['seller_name']); ?></td>
                                    <td>$<?php echo number_format($order['price'], 2); ?></td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <button class="action-btn view" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f8f9fa;
    color: #333;
}

.admin-container {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 280px;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    padding: 30px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
}

.sidebar-nav {
    padding: 20px 0;
}

.nav-item {
    display: block;
    padding: 15px 25px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.nav-item:hover,
.nav-item.active {
    background: rgba(255,255,255,0.1);
    color: white;
    border-left-color: #3498db;
}

.nav-item.logout {
    margin-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.nav-item.logout:hover {
    background: rgba(231, 76, 60, 0.2);
    border-left-color: #e74c3c;
}

.nav-item i {
    margin-right: 10px;
    width: 20px;
}

.main-content {
    margin-left: 280px;
    padding: 30px;
    width: calc(100% - 280px);
}

.content-header {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.content-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #3498db 0%, #2980b9 100%);
}

.content-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
    color: #2c3e50;
}

.content-header p {
    color: #666;
    font-size: 1.1rem;
}

.content-section {
    display: none;
}

.content-section.active {
    display: block;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
}

.stat-icon.users {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

.stat-icon.products {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.stat-icon.orders {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.stat-icon.revenue {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
}

.stat-info h3 {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stat-info p {
    color: #666;
    font-size: 0.95rem;
}

.recent-activity {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.recent-activity h2 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.5rem;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
    display: inline-block;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.3s ease;
}

.activity-item:hover {
    background-color: #f8f9fa;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
}

.activity-content {
    flex: 1;
}

.activity-content h4 {
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 1rem;
}

.activity-content p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.activity-time {
    font-size: 0.8rem;
    color: #999;
}

.activity-status {
    margin-left: auto;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.pending {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
}

.status-badge.completed {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.status-badge.cancelled {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
}

.table-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
}

.admin-table td {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9rem;
}

.admin-table tr:hover {
    background-color: #f8f9fa;
}

.status-form select {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: white;
    font-size: 0.85rem;
}

.action-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    margin: 0 2px;
}

.action-btn.view {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
}

.action-btn:hover {
    transform: scale(1.1);
}

.alert {
    padding: 15px 20px;
    margin: 20px 0;