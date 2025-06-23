<?php
// filepath: c:\wamp64\www\Edensshop\cars.php
session_start();
require 'config/database.php';

$page_title = "Browse Cars - Eden's CarShop";

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
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$user_id, $product_id]);
        }
        
        header('Location: cart.php?success=1');
        exit();
        
    } catch (Exception $e) {
        $cart_error = $e->getMessage();
    }
}

// Get and clean search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : 0;
$year = isset($_GET['year']) && $_GET['year'] !== '' ? (int)$_GET['year'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Initialize arrays
$products = [];
$available_brands = [];
$available_years = [];
$total_products = 0;

try {
    // Build the base query
    $base_query = "FROM products p JOIN users u ON p.user_id = u.id WHERE p.status = 'active'";
    $where_conditions = [];
    $params = [];
    
    // Add search condition
    if (!empty($search)) {
        $where_conditions[] = "(p.title LIKE ? OR p.brand LIKE ? OR p.model LIKE ? OR p.description LIKE ?)";
        $search_term = '%' . $search . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    // Add brand filter
    if (!empty($brand)) {
        $where_conditions[] = "p.brand = ?";
        $params[] = $brand;
    }
    
    // Add price filters
    if ($min_price > 0) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $min_price;
    }
    
    if ($max_price > 0) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $max_price;
    }
    
    // Add year filter
    if ($year > 0) {
        $where_conditions[] = "p.year = ?";
        $params[] = $year;
    }
    
    // Combine all conditions
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = ' AND ' . implode(' AND ', $where_conditions);
    }
    
    // Set up sorting
    $order_clause = "ORDER BY ";
    switch ($sort) {
        case 'price_low':
            $order_clause .= "p.price ASC";
            break;
        case 'price_high':
            $order_clause .= "p.price DESC";
            break;
        case 'year_new':
            $order_clause .= "p.year DESC";
            break;
        case 'year_old':
            $order_clause .= "p.year ASC";
            break;
        case 'title_az':
            $order_clause .= "p.title ASC";
            break;
        case 'title_za':
            $order_clause .= "p.title DESC";
            break;
        case 'oldest':
            $order_clause .= "p.created_at ASC";
            break;
        default: // newest
            $order_clause .= "p.created_at DESC";
            break;
    }
    
    // Get the products
    $sql = "SELECT p.*, u.username as seller_name, u.email as seller_email " . $base_query . $where_clause . " " . $order_clause;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_products = count($products);
    
    // Get available brands for filter
    $brands_sql = "SELECT DISTINCT p.brand FROM products p WHERE p.status = 'active' ORDER BY p.brand ASC";
    $brands_stmt = $pdo->query($brands_sql);
    $available_brands = $brands_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get available years for filter
    $years_sql = "SELECT DISTINCT p.year FROM products p WHERE p.status = 'active' ORDER BY p.year DESC";
    $years_stmt = $pdo->query($years_sql);
    $available_years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    error_log("Database error in cars.php: " . $e->getMessage());
    $products = [];
    $available_brands = [];
    $available_years = [];
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
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

        .browse-container {
            max-width: 1400px;
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

        .page-header h1 {
            color: var(--primary-green);
            margin: 0 0 10px 0;
            font-size: 2.5rem;
        }

        .page-header p {
            color: var(--text-gray);
            margin: 0;
            font-size: 1.1rem;
        }

        /* Search and Filter Section */
        .search-filters {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 25px var(--shadow);
        }

        .search-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .search-main {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }

        .search-input-group {
            flex: 1;
            position: relative;
        }

        .search-input-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .search-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid var(--border-light);
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent-green);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            bottom: 15px;
            color: var(--text-gray);
        }

        .btn-search {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            height: fit-content;
        }

        .btn-search:hover {
            background: var(--primary-green);
        }

        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .filter-group select,
        .filter-group input[type="number"] {
            padding: 10px;
            border: 2px solid var(--border-light);
            border-radius: 8px;
            font-size: 14px;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--accent-green);
        }

        .filter-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 15px;
        }

        .btn-clear {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
        }

        /* Results Section */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px var(--shadow);
        }

        .results-count {
            font-weight: 600;
            color: var(--text-dark);
        }

        .search-term {
            color: var(--accent-green);
            font-weight: 700;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 25px var(--shadow);
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .product-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image-placeholder {
            width: 100%;
            height: 100%;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #999;
        }

        .no-image-placeholder i {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--accent-green);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .product-details {
            padding: 25px;
        }

        .product-details h3 {
            margin: 0 0 15px 0;
            color: var(--text-dark);
            font-size: 1.3rem;
        }

        .car-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: var(--text-gray);
        }

        .car-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .description {
            color: var(--text-gray);
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .price {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--accent-green);
            margin: 15px 0;
        }

        .seller-info {
            font-size: 0.85rem;
            color: var(--text-gray);
            margin-bottom: 20px;
        }

        .seller-name {
            font-weight: 600;
            color: var(--primary-green);
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .btn-view, .btn-cart {
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-view {
            background: var(--primary-green);
            color: white;
        }

        .btn-view:hover {
            background: var(--accent-green);
        }

        .btn-cart {
            background: var(--accent-green);
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-cart:hover {
            background: var(--primary-green);
        }

        .btn-cart:disabled {
            background: #ccc;
            color: #666;
            cursor: not-allowed;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px var(--shadow);
        }

        .no-results i {
            font-size: 4rem;
            color: var(--border-light);
            margin-bottom: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .search-main {
                flex-direction: column;
            }
            
            .filters-row {
                grid-template-columns: 1fr;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .results-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="browse-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-car"></i> Browse Our Cars</h1>
            <p>Find your perfect vehicle from our marketplace</p>
        </div>

        <!-- Search and Filters -->
        <div class="search-filters">
            <form method="GET" action="cars.php" class="search-form">
                <!-- Main Search -->
                <div class="search-main">
                    <div class="search-input-group">
                        <label for="search">Search Cars</label>
                        <div style="position: relative;">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   id="search"
                                   name="search" 
                                   class="search-input"
                                   value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Search by name, brand, model, or description...">
                        </div>
                    </div>
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>

                <!-- Filters -->
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="brand">Brand</label>
                        <select id="brand" name="brand">
                            <option value="">All Brands</option>
                            <?php foreach ($available_brands as $available_brand): ?>
                                <option value="<?= htmlspecialchars($available_brand) ?>" 
                                        <?= $brand === $available_brand ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($available_brand) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="min_price">Min Price ($)</label>
                        <input type="number" 
                               id="min_price" 
                               name="min_price" 
                               value="<?= $min_price > 0 ? $min_price : '' ?>"
                               placeholder="0"
                               min="0">
                    </div>
                    
                    <div class="filter-group">
                        <label for="max_price">Max Price ($)</label>
                        <input type="number" 
                               id="max_price" 
                               name="max_price" 
                               value="<?= $max_price > 0 ? $max_price : '' ?>"
                               placeholder="No limit"
                               min="0">
                    </div>
                    
                    <div class="filter-group">
                        <label for="year">Year</label>
                        <select id="year" name="year">
                            <option value="">All Years</option>
                            <?php foreach ($available_years as $available_year): ?>
                                <option value="<?= $available_year ?>" 
                                        <?= $year === $available_year ? 'selected' : '' ?>>
                                    <?= $available_year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort">Sort By</label>
                        <select id="sort" name="sort">
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="year_new" <?= $sort === 'year_new' ? 'selected' : '' ?>>Year: Newest</option>
                            <option value="year_old" <?= $sort === 'year_old' ? 'selected' : '' ?>>Year: Oldest</option>
                            <option value="title_az" <?= $sort === 'title_az' ? 'selected' : '' ?>>Name: A-Z</option>
                            <option value="title_za" <?= $sort === 'title_za' ? 'selected' : '' ?>>Name: Z-A</option>
                        </select>
                    </div>
                </div>

                <!-- Filter Actions -->
                <?php if (!empty($search) || !empty($brand) || $min_price > 0 || $max_price > 0 || $year > 0): ?>
                    <div class="filter-actions">
                        <a href="cars.php" class="btn-clear">
                            <i class="fas fa-times"></i> Clear All Filters
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Results Header -->
        <div class="results-header">
            <div class="results-count">
                <strong><?= $total_products ?></strong> vehicles found
                <?php if (!empty($search)): ?>
                    for "<span class="search-term"><?= htmlspecialchars($search) ?></span>"
                <?php endif; ?>
            </div>
        </div>

        <!-- Products Grid -->
        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image1']) && file_exists("uploads/" . $product['image1'])): ?>
                                <img src="uploads/<?= htmlspecialchars($product['image1']) ?>" 
                                     alt="<?= htmlspecialchars($product['title']) ?>">
                            <?php else: ?>
                                <div class="no-image-placeholder">
                                    <i class="fas fa-car"></i>
                                    <span>No Image Available</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-badge">
                                <?= ucfirst($product['status']) ?>
                            </div>
                        </div>
                        
                        <div class="product-details">
                            <h3><?= htmlspecialchars($product['title']) ?></h3>
                            
                            <div class="car-meta">
                                <span><i class="fas fa-industry"></i> <?= htmlspecialchars($product['brand']) ?></span>
                                <?php if (!empty($product['model'])): ?>
                                    <span><i class="fas fa-car"></i> <?= htmlspecialchars($product['model']) ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-calendar"></i> <?= $product['year'] ?></span>
                            </div>
                            
                            <div class="description">
                                <?= htmlspecialchars(substr($product['description'], 0, 150)) ?>
                                <?= strlen($product['description']) > 150 ? '...' : '' ?>
                            </div>
                            
                            <div class="price">$<?= number_format($product['price'] ?? 0, 2) ?></div>
                            
                            <div class="seller-info">
                                Sold by: <span class="seller-name"><?= htmlspecialchars($product['seller_name']) ?></span>
                            </div>
                            
                            <div class="product-actions">
                                <a href="car_details.php?id=<?= $product['id'] ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['user_id']): ?>
                                    <form method="POST" style="flex: 1;">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" name="add_to_cart" class="btn-cart">
                                            <i class="fas fa-shopping-cart"></i> Add to Cart
                                        </button>
                                    </form>
                                <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <a href="login.php?redirect=cars.php" class="btn-cart">
                                        <i class="fas fa-sign-in-alt"></i> Login to Buy
                                    </a>
                                <?php else: ?>
                                    <button class="btn-cart" disabled>
                                        <i class="fas fa-user"></i> Your Product
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No vehicles found</h3>
                <?php if (!empty($search) || !empty($brand) || $min_price > 0 || $max_price > 0 || $year > 0): ?>
                    <p>Try adjusting your search criteria or clear the filters to see all available cars.</p>
                    <a href="cars.php" class="btn-clear" style="display: inline-block; margin-top: 15px;">
                        <i class="fas fa-times"></i> Clear All Filters
                    </a>
                <?php else: ?>
                    <p>No cars are currently available. Check back soon for new listings!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
