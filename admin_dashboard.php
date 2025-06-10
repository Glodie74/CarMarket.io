<?php
session_start();
include('includes/db.php');

// Check if the user is logged in as admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $new_status = htmlspecialchars(trim($_POST['status']));

    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $success_message = "Order status updated successfully.";
    } else {
        $error_message = "Failed to update order status. Please try again.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Order Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <i class="fas fa-chart-bar"></i> Dashboard
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-car"></i> Products
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Order Management Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, Admin</span>
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Orders</h3>
                            <p class="stat-number">
                                <?php
                                $total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
                                echo $total_orders;
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Pending Orders</h3>
                            <p class="stat-number">
                                <?php
                                $pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'")->fetch_assoc()['count'];
                                echo $pending_orders;
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon shipped">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Shipped Orders</h3>
                            <p class="stat-number">
                                <?php
                                $shipped_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Shipped'")->fetch_assoc()['count'];
                                echo $shipped_orders;
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon delivered">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Delivered Orders</h3>
                            <p class="stat-number">
                                <?php
                                $delivered_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Delivered'")->fetch_assoc()['count'];
                                echo $delivered_orders;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="orders-section">
                    <h2><i class="fas fa-list"></i> Recent Orders</h2>
                    <div class="table-container">
                        <?php
                        // Fetch orders with a prepared statement
                        $query = "
                            SELECT orders.id, products.title, users.username, orders.status, orders.order_date
                            FROM orders 
                            JOIN products ON orders.product_id = products.id 
                            JOIN users ON orders.user_id = users.id 
                            ORDER BY orders.order_date DESC
                        ";
                        $result = $conn->query($query);

                        if ($result && $result->num_rows > 0) {
                            echo "<table class='orders-table'>";
                            echo "<thead>";
                            echo "<tr>";
                            echo "<th>Order ID</th>";
                            echo "<th>Product</th>";
                            echo "<th>Customer</th>";
                            echo "<th>Date</th>";
                            echo "<th>Status</th>";
                            echo "<th>Action</th>";
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";

                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><span class='order-id'>#" . $row['id'] . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td>" . htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td>" . date('M d, Y', strtotime($row['order_date'])) . "</td>";
                                echo "<td>";
                                echo "<form method='POST' class='status-form'>";
                                echo "<input type='hidden' name='order_id' value='" . $row['id'] . "'>";
                                echo "<select name='status' class='status-select status-" . strtolower($row['status']) . "'>";
                                echo "<option value='Pending'" . ($row['status'] === 'Pending' ? ' selected' : '') . ">Pending</option>";
                                echo "<option value='Shipped'" . ($row['status'] === 'Shipped' ? ' selected' : '') . ">Shipped</option>";
                                echo "<option value='Delivered'" . ($row['status'] === 'Delivered' ? ' selected' : '') . ">Delivered</option>";
                                echo "</select>";
                                echo "</td>";
                                echo "<td>";
                                echo "<button type='submit' class='btn btn-update'>";
                                echo "<i class='fas fa-save'></i> Update";
                                echo "</button>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }

                            echo "</tbody>";
                            echo "</table>";
                        } else {
                            echo "<div class='no-orders'>";
                            echo "<i class='fas fa-inbox'></i>";
                            echo "<h3>No orders found</h3>";
                            echo "<p>There are no orders in the system yet.</p>";
                            echo "</div>";
                        }

                        $conn->close();
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-item:hover,
        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #ffd700;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .dashboard-header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-header h1 {
            color: #333;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .dashboard-header h1 i {
            color: #667eea;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #666;
            font-weight: 600;
        }

        .user-avatar i {
            font-size: 2rem;
            color: #667eea;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 1.5rem;
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);
        }

        .stat-icon.shipped {
            background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
        }

        .stat-icon.delivered {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }

        .stat-info h3 {
            color: #666;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-number {
            color: #333;
            font-size: 2rem;
            font-weight: 700;
        }

        .orders-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .orders-section h2 {
            color: #333;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .orders-section h2 i {
            color: #667eea;
        }

        .table-container {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .orders-table th {
            background-color: #f8f9fa;
            color: #333;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .orders-table tr:hover {
            background-color: #f8f9fa;
        }

        .order-id {
            background: #667eea;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-select {
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            min-width: 120px;
        }

        .status-select.status-pending {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }

        .status-select.status-shipped {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        .status-select.status-delivered {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-orders i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .no-orders h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #333;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .orders-table {
                font-size: 0.85rem;
            }

            .status-form {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</body>
</html>