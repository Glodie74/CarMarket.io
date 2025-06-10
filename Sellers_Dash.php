<?php
session_start();

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'seller') {
    // Redirect to appropriate dashboard based on role
    if ($_SESSION['role'] === 'buyer') {
        header("Location: buyer_dashboard.php");
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

require 'config/database.php';

// Handle product addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $price = floatval($_POST['price']);
    $year = intval($_POST['year']);
    $mileage = intval($_POST['mileage']);
    $brand = htmlspecialchars(trim($_POST['brand']));
    $make = htmlspecialchars(trim($_POST['make']));
    $transmission = htmlspecialchars(trim($_POST['transmission']));
    
    // Create uploads directory if it doesn't exist
    if (!file_exists("uploads")) {
        mkdir("uploads", 0777, true);
    }
    
    // Handle file uploads
    $image1 = $_FILES['image1']['name'];
    $image2 = $_FILES['image2']['name'];
    
    // Generate unique names for uploaded files
    $image1_name = time() . '_1_' . $image1;
    $image2_name = time() . '_2_' . $image2;
    
    if (move_uploaded_file($_FILES['image1']['tmp_name'], "uploads/" . $image1_name) &&
        move_uploaded_file($_FILES['image2']['tmp_name'], "uploads/" . $image2_name)) {
        
        $stmt = $pdo->prepare("INSERT INTO products (user_id, title, description, price, year, mileage, brand, make, transmission, image1, image2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $title, $description, $price, $year, $mileage, $brand, $make, $transmission, $image1_name, $image2_name])) {
            $success = "Product added successfully!";
        } else {
            $error = "Failed to add product.";
        }
    } else {
        $error = "Failed to upload images.";
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);
    
    // Get product details to delete images
    $stmt = $pdo->prepare("SELECT image1, image2 FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$product_id, $_SESSION['user_id']]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Delete the product from database
        $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
        if ($deleteStmt->execute([$product_id, $_SESSION['user_id']])) {
            // Delete image files
            if (file_exists("uploads/" . $product['image1'])) {
                unlink("uploads/" . $product['image1']);
            }
            if (file_exists("uploads/" . $product['image2'])) {
                unlink("uploads/" . $product['image2']);
            }
            $success = "Product deleted successfully!";
        } else {
            $error = "Failed to delete product.";
        }
    } else {
        $error = "Product not found.";
    }
}

// Get seller's products
$stmt = $pdo->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();

// Get seller's orders (need to create orders table first)
try {
    $stmt = $pdo->prepare("SELECT o.*, p.title, p.price, u.username as buyer_name FROM orders o JOIN products p ON o.product_id = p.id JOIN users u ON o.buyer_id = u.id WHERE p.user_id = ? ORDER BY o.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    // Orders table might not exist yet
    $orders = [];
}
?>

<!-- Rest of your existing HTML code stays the same -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Eden's CarShop</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="container">
            <div class="dashboard-header">
                <div>
                    <h1><i class="fas fa-store"></i> Seller Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-home"></i> Home</a>
                    <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Add Product Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
                <form method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title"><i class="fas fa-tag"></i> Product Title</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="price"><i class="fas fa-dollar-sign"></i> Price</label>
                            <input type="number" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="brand"><i class="fas fa-industry"></i> Brand</label>
                            <input type="text" id="brand" name="brand" required>
                        </div>
                        <div class="form-group">
                            <label for="make"><i class="fas fa-car"></i> Make</label>
                            <input type="text" id="make" name="make" required>
                        </div>
                        <div class="form-group">
                            <label for="year"><i class="fas fa-calendar"></i> Year</label>
                            <input type="number" id="year" name="year" min="1900" max="2024" required>
                        </div>
                        <div class="form-group">
                            <label for="mileage"><i class="fas fa-road"></i> Mileage (km)</label>
                            <input type="number" id="mileage" name="mileage" required>
                        </div>
                        <div class="form-group">
                            <label for="transmission"><i class="fas fa-cogs"></i> Transmission</label>
                            <select id="transmission" name="transmission" required>
                                <option value="">Select Transmission</option>
                                <option value="Manual">Manual</option>
                                <option value="Automatic">Automatic</option>
                                <option value="CVT">CVT</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label for="description"><i class="fas fa-align-left"></i> Description</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="form-group-images">
                        <div class="form-group">
                            <label for="image1"><i class="fas fa-image"></i> Image 1</label>
                            <input type="file" id="image1" name="image1" accept="image/*" required>
                        </div>
                        <div class="form-group">
                            <label for="image2"><i class="fas fa-image"></i> Image 2</label>
                            <input type="file" id="image2" name="image2" accept="image/*" required>
                        </div>
                    </div>
                    <button type="submit" name="add_product" class="btn btn-primary btn-large">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </form>
            </div>

            <!-- My Products Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-box"></i> My Products (<?php echo count($products); ?>)</h2>
                <?php if (count($products) > 0): ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="uploads/<?php echo htmlspecialchars($product['image1']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    <div class="product-actions">
                                        <button class="btn-icon btn-edit" title="Edit Product">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="delete_product" class="btn-icon btn-delete" title="Delete Product">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="product-details">
                                    <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                                    <div class="car-meta">
                                        <span><i class="fas fa-calendar"></i> <?php echo $product['year']; ?></span>
                                        <span><i class="fas fa-road"></i> <?php echo number_format($product['mileage']); ?> km</span>
                                        <span><i class="fas fa-cogs"></i> <?php echo $product['transmission']; ?></span>
                                    </div>
                                    <p class="description"><?php echo substr(htmlspecialchars($product['description']), 0, 100); ?>...</p>
                                    <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                                    <p class="date-added"><i class="fas fa-clock"></i> Added: <?php echo date('M d, Y', strtotime($product['created_at'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-items">
                        <i class="fas fa-box-open"></i>
                        <p>You haven't added any products yet.</p>
                        <p>Add your first product using the form above!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Orders Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-receipt"></i> Received Orders (<?php echo count($orders); ?>)</h2>
                <?php if (count($orders) > 0): ?>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Product</th>
                                    <th>Buyer</th>
                                    <th>Price</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                                        <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                        <td>$<?php echo number_format($order['price'], 2); ?></td>
                                        <td><span class="payment-method"><?php echo ucfirst($order['payment_method']); ?></span></td>
                                        <td><span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <button class="btn-small btn-primary">Update Status</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-items">
                        <i class="fas fa-receipt"></i>
                        <p>No orders received yet.</p>
                        <p>Orders will appear here when customers purchase your products.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<!-- Include your existing styles here -->
<style>
/* Dashboard Container */
.dashboard-container {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
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

/* Product Form */
.product-form {
    background: #f0f8f0;
    padding: 30px;
    border-radius: 12px;
    border: 2px solid #c8e6c9;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group-images {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    color: #1a4d3a;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #c8e6c9;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background-color: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #2d7d32;
    box-shadow: 0 0 0 3px rgba(45, 125, 50, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
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

.btn-danger {
    background: linear-gradient(135deg, #d32f2f, #c62828);
    color: white;
}

.btn-large {
    padding: 15px 30px;
    font-size: 1.1rem;
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

.btn-edit {
    background: linear-gradient(135deg, #2d7d32, #388e3c);
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #d32f2f, #c62828);
    color: white;
}

/* Product Grid */
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

.product-details {
    padding: 20px;
}

.product-details h3 {
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

.description {
    color: #2d5a45;
    margin: 15px 0;
    line-height: 1.5;
}

.price {
    font-size: 1.4rem;
    font-weight: bold;
    color: #2d7d32;
    margin: 10px 0;
}

.date-added {
    color: #2d5a45;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Orders Table */
.orders-table {
    overflow-x: auto;
}

.orders-table table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.orders-table th,
.orders-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #c8e6c9;
}

.orders-table th {
    background: linear-gradient(135deg, #2d7d32, #388e3c);
    color: white;
    font-weight: 600;
    position: sticky;
    top: 0;
}

.orders-table tr:hover {
    background-color: #f0f8f0;
}

.payment-method {
    background: #e8f5e8;
    color: #2d7d32;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
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
    margin-bottom: 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-group-images {
        grid-template-columns: 1fr;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .orders-table {
        font-size: 0.9rem;
    }
    
    .orders-table th,
    .orders-table td {
        padding: 10px 8px;
    }
}
</style>

</body>
</html>