<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../includes/db.php';

// Handle product approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_product'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $success = "Product approved successfully!";
        }
    } elseif (isset($_POST['reject_product'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("UPDATE products SET status = 'rejected' WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $success = "Product rejected successfully!";
        }
    } elseif (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $success = "Product deleted successfully!";
        }
    }
}

// Get all products
$stmt = $pdo->prepare("SELECT p.*, u.username as seller_name FROM products p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
$stmt->execute();
$products = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => count($products),
    'pending' => count(array_filter($products, fn($p) => $p['status'] === 'pending')),
    'approved' => count(array_filter($products, fn($p) => $p['status'] === 'approved')),
    'rejected' => count(array_filter($products, fn($p) => $p['status'] === 'rejected'))
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <div class="admin-header">
                <h1><i class="fas fa-cogs"></i> Product Management</h1>
                <div class="header-actions">
                    <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-dashboard"></i> Dashboard</a>
                    <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                <div class="stat-card stat-pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p>Pending Review</p>
                    </div>
                </div>
                <div class="stat-card stat-approved">
                    <div class="stat-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['approved']; ?></h3>
                        <p>Approved</p>
                    </div>
                </div>
                <div class="stat-card stat-rejected">
                    <div class="stat-icon">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['rejected']; ?></h3>
                        <p>Rejected</p>
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="filter-section">
                <h2><i class="fas fa-filter"></i> Filter Products</h2>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All Products</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="approved">Approved</button>
                    <button class="filter-btn" data-filter="rejected">Rejected</button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-section">
                <h2><i class="fas fa-list"></i> All Products</h2>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-status="<?php echo $product['status']; ?>">
                            <div class="product-image">
                                <img src="../uploads/<?php echo htmlspecialchars($product['image1']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                <div class="product-status status-<?php echo $product['status']; ?>">
                                    <i class="fas fa-circle"></i> <?php echo ucfirst($product['status']); ?>
                                </div>
                            </div>
                            <div class="product-details">
                                <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                                <div class="product-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($product['seller_name']); ?></span>
                                    <span><i class="fas fa-dollar-sign"></i> <?php echo number_format($product['price'], 2); ?></span>
                                </div>
                                <div class="car-details">
                                    <span><i class="fas fa-calendar"></i> <?php echo $product['year']; ?></span>
                                    <span><i class="fas fa-road"></i> <?php echo number_format($product['mileage']); ?> km</span>
                                    <span><i class="fas fa-industry"></i> <?php echo $product['brand']; ?></span>
                                </div>
                                <p class="description"><?php echo substr(htmlspecialchars($product['description']), 0, 100); ?>...</p>
                                <p class="date-added"><i class="fas fa-clock"></i> <?php echo date('M d, Y H:i', strtotime($product['created_at'])); ?></p>
                                
                                <div class="product-actions">
                                    <?php if ($product['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="approve_product" class="btn btn-success btn-small">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="reject_product" class="btn btn-warning btn-small">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="delete_product" class="btn btn-danger btn-small">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    <button class="btn btn-info btn-small" onclick="viewProduct(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

<style>
/* Admin Container */
.admin-container {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    min-height: 100vh;
    padding: 20px 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Admin Header */
.admin-header {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-header h1 {
    color: #2c3e50;
    font-size: 2.5rem;
    margin: 0;
    font-weight: 700;
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
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

/* Statistics Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-total .stat-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
.stat-pending .stat-icon { background: linear-gradient(135deg, #f093fb, #f5576c); }
.stat-approved .stat-icon { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.stat-rejected .stat-icon { background: linear-gradient(135deg, #fa709a, #fee140); }

.stat-content h3 {
    font-size: 2rem;
    margin: 0;
    color: #2c3e50;
    font-weight: 700;
}

.stat-content p {
    margin: 5px 0 0 0;
    color: #6c757d;
    font-weight: 500;
}

/* Filter Section */
.filter-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.filter-section h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid #e9ecef;
    background: white;
    color: #6c757d;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.filter-btn:hover,
.filter-btn.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-color: transparent;
}

/* Products Section */
.products-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.products-section h2 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

.product-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.product-image {
    position: relative;
}

.product-image img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.product-status {
    position: absolute;
    top: 15px;
    left: 15px;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.status-pending {
    background: rgba(255, 193, 7, 0.9);
    color: #856404;
}

.status-approved {
    background: rgba(40, 167, 69, 0.9);
    color: white;
}

.status-rejected {
    background: rgba(220, 53, 69, 0.9);
    color: white;
}

.product-details {
    padding: 20px;
}

.product-details h3 {
    margin-bottom: 15px;
    color: #2c3e50;
    font-size: 1.2rem;
    font-weight: 600;
}

.product-meta,
.car-details {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin: 10px 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.product-meta span,
.car-details span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.description {
    color: #6c757d;
    margin: 15px 0;
    line-height: 1.5;
}

.date-added {
    color: #6c757d;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 15px;
}

.product-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
.btn-secondary { background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: white; }
.btn-success { background: linear-gradient(135deg, #27ae60, #229954); color: white; }
.btn-warning { background: linear-gradient(135deg, #f39c12, #e67e22<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../includes/db.php';

// Handle product approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_product'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $success = "Product approved successfully!";
        }
    } elseif (isset($_POST['reject_product'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("UPDATE products SET status = 'rejected' WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $success = "Product rejected successfully!";
        }
    } elseif (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$product_id])) {
            $success = "Product deleted successfully!";
        }
    }
}

// Get all products
$stmt = $pdo->prepare("SELECT p.*, u.username as seller_name FROM products p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
$stmt->execute();
$products = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => count($products),
    'pending' => count(array_filter($products, fn($p) => $p['status'] === 'pending')),
    'approved' => count(array_filter($products, fn($p) => $p['status'] === 'approved')),
    'rejected' => count(array_filter($products, fn($p) => $p['status'] === 'rejected'))
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <div class="container">
            <div class="admin-header">
                <h1><i class="fas fa-cogs"></i> Product Management</h1>
                <div class="header-actions">
                    <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-dashboard"></i> Dashboard</a>
                    <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                <div class="stat-card stat-pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p>Pending Review</p>
                    </div>
                </div>
                <div class="stat-card stat-approved">
                    <div class="stat-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['approved']; ?></h3>
                        <p>Approved</p>
                    </div>
                </div>
                <div class="stat-card stat-rejected">
                    <div class="stat-icon">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['rejected']; ?></h3>
                        <p>Rejected</p>
                    </div>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="filter-section">
                <h2><i class="fas fa-filter"></i> Filter Products</h2>
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All Products</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="approved">Approved</button>
                    <button class="filter-btn" data-filter="rejected">Rejected</button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-section">
                <h2><i class="fas fa-list"></i> All Products</h2>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-status="<?php echo $product['status']; ?>">
                            <div class="product-image">
                                <img src="../uploads/<?php echo htmlspecialchars($product['image1']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                <div class="product-status status-<?php echo $product['status']; ?>">
                                    <i class="fas fa-circle"></i> <?php echo ucfirst($product['status']); ?>
                                </div>
                            </div>
                            <div class="product-details">
                                <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                                <div class="product-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($product['seller_name']); ?></span>
                                    <span><i class="fas fa-dollar-sign"></i> <?php echo number_format($product['price'], 2); ?></span>
                                </div>
                                <div class="car-details">
                                    <span><i class="fas fa-calendar"></i> <?php echo $product['year']; ?></span>
                                    <span><i class="fas fa-road"></i> <?php echo number_format($product['mileage']); ?> km</span>
                                    <span><i class="fas fa-industry"></i> <?php echo $product['brand']; ?></span>
                                </div>
                                <p class="description"><?php echo substr(htmlspecialchars($product['description']), 0, 100); ?>...</p>
                                <p class="date-added"><i class="fas fa-clock"></i> <?php echo date('M d, Y H:i', strtotime($product['created_at'])); ?></p>
                                
                                <div class="product-actions">
                                    <?php if ($product['status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="approve_product" class="btn btn-success btn-small">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" name="reject_product" class="btn btn-warning btn-small">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="delete_product" class="btn btn-danger btn-small">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    <button class="btn btn-info btn-small" onclick="viewProduct(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

<style>
/* Admin Container */
.admin-container {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    min-height: 100vh;
    padding: 20px 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Admin Header */
.admin-header {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-header h1 {
    color: #2c3e50;
    font-size: 2.5rem;
    margin: 0;
    font-weight: 700;
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
    background-color: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

/* Statistics Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-total .stat-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
.stat-pending .stat-icon { background: linear-gradient(135deg, #f093fb, #f5576c); }
.stat-approved .stat-icon { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.stat-rejected .stat-icon { background: linear-gradient(135deg, #fa709a, #fee140); }

.stat-content h3 {
    font-size: 2rem;
    margin: 0;
    color: #2c3e50;
    font-weight: 700;
}

.stat-content p {
    margin: 5px 0 0 0;
    color: #6c757d;
    font-weight: 500;
}

/* Filter Section */
.filter-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.filter-section h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 10px 20px;
    border: 2px solid #e9ecef;
    background: white;
    color: #6c757d;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.filter-btn:hover,
.filter-btn.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-color: transparent;
}

/* Products Section */
.products-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.products-section h2 {
    color: #2c3e50;
    margin-bottom: 25px;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

.product-card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.product-image {
    position: relative;
}

.product-image img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.product-status {
    position: absolute;
    top: 15px;
    left: 15px;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
}

.status-pending {
    background: rgba(255, 193, 7, 0.9);
    color: #856404;
}

.status-approved {
    background: rgba(40, 167, 69, 0.9);
    color: white;
}

.status-rejected {
    background: rgba(220, 53, 69, 0.9);
    color: white;
}

.product-details {
    padding: 20px;
}

.product-details h3 {
    margin-bottom: 15px;
    color: #2c3e50;
    font-size: 1.2rem;
    font-weight: 600;
}

.product-meta,
.car-details {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin: 10px 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.product-meta span,
.car-details span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.description {
    color: #6c757d;
    margin: 15px 0;
    line-height: 1.5;
}

.date-added {
    color: #6c757d;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 15px;
}

.product-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
.btn-secondary { background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: white; }
.btn-success { background: linear-gradient(135deg, #27ae60, #229954); color: white; }
.btn-warning { background: linear-gradient(135deg, #f39c12, #e67e22