<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for remember me token if user is not logged in
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../config/database.php';
    
    $token = $_COOKIE['remember_token'];
    try {
        $stmt = $pdo->prepare("SELECT u.*, rt.user_id FROM users u 
                              JOIN remember_tokens rt ON u.id = rt.user_id 
                              WHERE rt.token = ? AND rt.expires_at > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user && isset($user->status) && $user->status === 'active') {
            $_SESSION['user_id'] = $user->id ?? $user['id'] ?? null;
            $_SESSION['username'] = $user->username ?? $user['username'] ?? null;
            $_SESSION['email'] = $user->email ?? $user['email'] ?? null;
            $_SESSION['role'] = $user->role ?? $user['role'] ?? null;
            $_SESSION['first_name'] = $user->first_name ?? $user['first_name'] ?? null;
            $_SESSION['last_name'] = $user->last_name ?? $user['last_name'] ?? null;
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user->id ?? $user['id'] ?? null]);
        } else {
            // Invalid or expired token, clear cookie
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('remembered_email', '', time() - 3600, '/');
        }
    } catch (PDOException $e) {
        error_log("Remember token check error: " . $e->getMessage());
        // Clear potentially corrupted cookies
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('remembered_email', '', time() - 3600, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eden's CarShop - Premium Used Car Marketplace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="contact-info">
                    <span><i class="fas fa-phone"></i> +1 (555) 123-4567</span>
                    <span><i class="fas fa-envelope"></i> info@edenscarshop.com</span>
                    <span><i class="fas fa-clock"></i> Mon-Fri: 9AM-8PM</span>
                </div>
                <div class="top-bar-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <a href="index.php">
                        <h1>Eden's<span>CarShop</span></h1>
                        <p>Premium Used Cars</p>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="header-search">
                    <form action="search.php" method="GET" class="search-form">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" name="query" placeholder="Search cars by brand, model, or type...">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                        </div>
                    </form>
                </div>

                <!-- User Actions -->
                <div class="header-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-menu">
                            <div class="user-avatar">
                                <div class="avatar-circle">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="user-info">
                                    <span class="greeting">Hello,</span>
                                    <span class="username"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                                </div>
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </div>
                            <div class="dropdown-menu">
                                <div class="dropdown-header">
                                    <div class="user-details">
                                        <strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong>
                                        <small><?= htmlspecialchars($_SESSION['email'] ?? '') ?></small>
                                    </div>
                                </div>
                                <div class="dropdown-body">
                                    <?php if ($_SESSION['role'] === 'buyer'): ?>
                                        <a href="buyer_dashboard.php">
                                            <i class="fas fa-tachometer-alt"></i> My Dashboard
                                        </a>
                                        <a href="profile.php">
                                            <i class="fas fa-user-edit"></i> Profile Settings
                                        </a>
                                        <a href="buyer_dashboard.php#favorites">
                                            <i class="fas fa-heart"></i> My Favorites
                                        </a>
                                        <a href="messages.php">
                                            <i class="fas fa-envelope"></i> Messages
                                        </a>
                                    <?php elseif ($_SESSION['role'] === 'seller'): ?>
                                        <a href="Sellers_Dash.php">
                                            <i class="fas fa-store"></i> Seller Dashboard
                                        </a>
                                        <a href="add_product.php">
                                            <i class="fas fa-plus-circle"></i> Add New Car
                                        </a>
                                        <a href="profile.php">
                                            <i class="fas fa-user-edit"></i> Profile Settings
                                        </a>
                                        <a href="my-cars.php">
                                            <i class="fas fa-car"></i> My Cars
                                        </a>
                                        <a href="messages.php">
                                            <i class="fas fa-envelope"></i> Messages
                                        </a>
                                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                                        <a href="admin_dashboard.php">
                                            <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                                        </a>
                                        <a href="profile.php">
                                            <i class="fas fa-user-edit"></i> Profile Settings
                                        </a>
                                        <a href="admin_dashboard.php#users">
                                            <i class="fas fa-users"></i> Manage Users
                                        </a>
                                        <a href="admin_dashboard.php#products">
                                            <i class="fas fa-car"></i> Manage Cars
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="dropdown-footer">
                                    <a href="logout.php" class="logout-link">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">
                            <i class="fas fa-sign-in-alt"></i> 
                            <span>Login</span>
                        </a>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> 
                            <span>Register</span>
                        </a>
                    <?php endif; ?>
                    
                    <a href="cart.php" class="cart-link" title="Shopping Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">
        <?php
        if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer') {
            try {
                $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $cart_count = $stmt->fetchColumn() ?: 0;
                echo $cart_count;
            } catch (PDOException $e) {
                echo '0';
            }
        } else {
            echo '0';
        }
        ?>
    </span>
                    </a>
                </div>

                <!-- Mobile Menu Toggle -->
                <div class="mobile-toggle" id="mobileToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <ul class="nav-menu">
                <li>
                    <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> 
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="search.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : '' ?>">
                        <i class="fas fa-car"></i> 
                        <span>Browse Cars</span>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="nav-link">
                        <i class="fas fa-list"></i> 
                        <span>Categories</span> 
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="search.php?category=sedan">
                            <i class="fas fa-car"></i> Sedans
                        </a>
                        <a href="search.php?category=suv">
                            <i class="fas fa-truck"></i> SUVs
                        </a>
                        <a href="search.php?category=truck">
                            <i class="fas fa-truck-pickup"></i> Trucks
                        </a>
                        <a href="search.php?category=coupe">
                            <i class="fas fa-car-side"></i> Coupes
                        </a>
                        <a href="search.php?category=convertible">
                            <i class="fas fa-car-crash"></i> Convertibles
                        </a>
                        <a href="search.php?category=hatchback">
                            <i class="fas fa-car-alt"></i> Hatchbacks
                        </a>
                    </div>
                </li>
                <?php if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer'): ?>
                    <!-- Only show "Sell Your Car" if user is not logged in or is not a buyer -->
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="register.php?role=seller" class="nav-link highlight-nav">
                                <i class="fas fa-plus-circle"></i> 
                                <span>Sell Your Car</span>
                            </a>
                        </li>
                    <?php elseif ($_SESSION['role'] === 'seller'): ?>
                        <li>
                            <a href="add_product.php" class="nav-link highlight-nav">
                                <i class="fas fa-plus-circle"></i> 
                                <span>Add New Car</span>
                            </a>
                        </li>
                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                        <li>
                            <a href="admin_dashboard.php" class="nav-link highlight-nav">
                                <i class="fas fa-tachometer-alt"></i> 
                                <span>Admin Panel</span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- For buyers, show dashboard link instead -->
                    <li>
                        <a href="buyer_dashboard.php" class="nav-link highlight-nav">
                            <i class="fas fa-tachometer-alt"></i> 
                            <span>My Dashboard</span>
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="about.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">
                        <i class="fas fa-info-circle"></i> 
                        <span>About</span>
                    </a>
                </li>
                <li>
                    <a href="contact.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">
                        <i class="fas fa-phone"></i> 
                        <span>Contact</span>
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li>
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <style>
    /* Dark Green Color Variables */
    :root {
        --primary-dark-green: #0d4f0f;
        --primary-green: #1a5c2a;
        --medium-green: #2d7d32;
        --light-green: #388e3c;
        --accent-green: #4caf50;
        --pale-green: #66bb6a;
        --card-green: #134016;
        --text-light-green: #81c784;
        --text-white-green: #e8f5e9;
        --border-green: #2e7d32;
        --hover-green: #1b5e20;
        --success-green: #43a047;
        --warning-green: #689f38;
        --gradient-green: linear-gradient(135deg, var(--primary-dark-green) 0%, var(--primary-green) 50%, var(--medium-green) 100%);
        --gradient-green-reverse: linear-gradient(135deg, var(--medium-green) 0%, var(--primary-green) 50%, var(--primary-dark-green) 100%);
        
        /* White background variables */
        --bg-white: #ffffff;
        --bg-light-gray: #f8f9fa;
        --text-dark: #333333;
        --text-gray: #666666;
        --border-light: #e0e0e0;
    }

    /* General Styles - White Background */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        line-height: 1.6;
        color: var(--text-dark);
        background: var(--bg-white);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Top Bar - Green Theme */
    .top-bar {
        background: var(--gradient-green);
        color: var(--text-white-green);
        padding: 10px 0;
        font-size: 0.85rem;
        border-bottom: 2px solid var(--border-green);
        box-shadow: 0 2px 10px rgba(13, 79, 15, 0.3);
    }

    .top-bar-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .contact-info {
        display: flex;
        gap: 30px;
    }

    .contact-info span {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .contact-info span:hover {
        color: var(--pale-green);
        transform: translateY(-1px);
    }

    .contact-info i {
        color: var(--pale-green);
        font-size: 0.9rem;
    }

    .top-bar-links {
        display: flex;
        gap: 15px;
    }

    .top-bar-links a {
        color: var(--text-white-green);
        text-decoration: none;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
        background: rgba(102, 187, 106, 0.1);
        border: 1px solid rgba(102, 187, 106, 0.3);
    }

    .top-bar-links a:hover {
        background: var(--pale-green);
        color: var(--primary-dark-green);
        transform: translateY(-2px) scale(1.1);
        box-shadow: 0 5px 15px rgba(102, 187, 106, 0.4);
    }

    /* Main Header - Green Theme with White Background */
    .main-header {
        background: linear-gradient(135deg, var(--card-green) 0%, var(--primary-dark-green) 100%);
        box-shadow: 0 4px 25px rgba(13, 79, 15, 0.4);
        padding: 20px 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        border-bottom: 3px solid var(--border-green);
        backdrop-filter: blur(10px);
    }

    .header-content {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 40px;
        align-items: center;
    }

    /* Logo - Green Theme */
    .logo a {
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
    }

    .logo a:hover {
        transform: scale(1.02);
    }

    .logo h1 {
        font-size: 2.2rem;
        font-weight: 900;
        color: var(--text-white-green);
        margin-bottom: -5px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .logo span {
        background: linear-gradient(135deg, var(--accent-green) 0%, var(--pale-green) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .logo p {
        font-size: 0.8rem;
        color: var(--text-light-green);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.9;
    }

    /* Header Search - Green Theme */
    .header-search {
        max-width: 500px;
        width: 100%;
    }

    .search-input-wrapper {
        display: flex;
        background: var(--bg-white);
        border: 2px solid var(--border-green);
        border-radius: 50px;
        padding: 5px;
        align-items: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(13, 79, 15, 0.1);
    }

    .search-input-wrapper:focus-within {
        border-color: var(--accent-green);
        box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.2);
        transform: translateY(-2px);
    }

    .search-input-wrapper i {
        color: var(--medium-green);
        margin-left: 20px;
        margin-right: 12px;
        font-size: 1.1rem;
    }

    .search-input-wrapper input {
        flex: 1;
        border: none;
        outline: none;
        padding: 14px 0;
        background: transparent;
        font-size: 0.95rem;
        color: var(--text-dark);
        font-weight: 500;
    }

    .search-input-wrapper input::placeholder {
        color: var(--text-gray);
        opacity: 0.8;
    }

    .search-btn {
        background: linear-gradient(135deg, var(--medium-green), var(--accent-green));
        color: var(--text-white-green);
        border: none;
        padding: 14px 25px;
        border-radius: 50px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 2px solid var(--border-green);
    }

    .search-btn:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, var(--primary-green), var(--medium-green));
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
    }

    /* Header Actions - Green Theme */
    .header-actions {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 24px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        border: 2px solid;
        transition: all 0.3s ease;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        overflow: hidden;
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .btn:hover::before {
        left: 100%;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--medium-green), var(--light-green));
        color: var(--text-white-green);
        border-color: var(--border-green);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-green), var(--medium-green));
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(45, 125, 50, 0.4);
    }

    .btn-outline {
        background: var(--bg-white);
        color: var(--medium-green);
        border-color: var(--border-green);
    }

    .btn-outline:hover {
        background: var(--medium-green);
        color: var(--text-white-green);
        border-color: var(--accent-green);
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(46, 125, 50, 0.4);
    }

    /* User Menu - Green Theme */
    .user-menu {
        position: relative;
    }

    .user-avatar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        background: var(--bg-white);
        border: 2px solid var(--border-green);
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(13, 79, 15, 0.1);
    }

    .user-avatar:hover {
        background: var(--bg-light-gray);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
        border-color: var(--accent-green);
    }

    .avatar-circle {
        width: 35px;
        height: 35px;
        background: linear-gradient(135deg, var(--accent-green), var(--pale-green));
        color: var(--text-white-green);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }

    .user-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .greeting {
        font-size: 0.7rem;
        color: var(--text-gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .username {
        font-size: 0.9rem;
        color: var(--text-dark);
        font-weight: 700;
    }

    .dropdown-arrow {
        color: var(--medium-green);
        font-size: 0.8rem;
        transition: transform 0.3s ease;
    }

    .user-menu:hover .dropdown-arrow {
        transform: rotate(180deg);
    }

    .user-menu .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: var(--bg-white);
        border: 2px solid var(--border-light);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        min-width: 280px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-15px);
        transition: all 0.3s ease;
        z-index: 1001;
        overflow: hidden;
    }

    .user-menu:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown-header {
        padding: 20px 25px 15px;
        border-bottom: 1px solid var(--border-light);
        background: var(--bg-light-gray);
    }

    .user-details strong {
        display: block;
        color: var(--text-dark);
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 2px;
    }

    .user-details small {
        color: var(--text-gray);
        font-size: 0.8rem;
        opacity: 0.9;
    }

    .dropdown-body {
        padding: 10px 0;
    }

    .dropdown-body a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 25px;
        color: var(--text-dark);
        text-decoration: none;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .dropdown-body a:hover {
        background: rgba(76, 175, 80, 0.1);
        color: var(--medium-green);
        padding-left: 30px;
    }

    .dropdown-body a i {
        color: var(--medium-green);
        width: 16px;
        text-align: center;
    }

    .dropdown-footer {
        border-top: 1px solid var(--border-light);
        padding: 15px 25px;
        background: var(--bg-light-gray);
    }

    .logout-link {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #dc3545 !important;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        padding: 8px 15px;
        border-radius: 10px;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .logout-link:hover {
        background: rgba(220, 53, 69, 0.1);
        transform: translateX(5px);
    }

    /* Cart Link - Green Theme */
    .cart-link {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background: var(--bg-white);
        color: var(--medium-green);
        border: 2px solid var(--border-green);
        border-radius: 50%;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 1.2rem;
        box-shadow: 0 2px 10px rgba(13, 79, 15, 0.1);
    }

    .cart-link:hover {
        background: linear-gradient(135deg, var(--medium-green), var(--accent-green));
        color: var(--text-white-green);
        transform: scale(1.1) translateY(-2px);
        box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
    }

    .cart-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background: linear-gradient(135deg, #ff6b6b, #ee5a52);
        color: white;
        font-size: 0.7rem;
        font-weight: 700;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--bg-white);
        box-shadow: 0 3px 10px rgba(255, 107, 107, 0.4);
    }

    /* Mobile Toggle */
    .mobile-toggle {
        display: none;
        flex-direction: column;
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        transition: all 0.3s ease;
        background: var(--bg-white);
        border: 2px solid var(--border-green);
    }

    .mobile-toggle:hover {
        background: var(--bg-light-gray);
    }

    .mobile-toggle span {
        width: 25px;
        height: 3px;
        background: var(--medium-green);
        margin: 3px 0;
        transition: 0.3s;
        border-radius: 2px;
    }

    .mobile-toggle.active span:nth-child(1) {
        transform: rotate(-45deg) translate(-5px, 6px);
    }

    .mobile-toggle.active span:nth-child(2) {
        opacity: 0;
    }

    .mobile-toggle.active span:nth-child(3) {
        transform: rotate(45deg) translate(-5px, -6px);
    }

    /* Main Navigation - Green Theme */
    .main-nav {
        background: linear-gradient(135deg, var(--bg-white), var(--bg-light-gray));
        border-bottom: 2px solid var(--border-light);
        padding: 0;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    }

    .nav-menu {
        list-style: none;
        display: flex;
        align-items: center;
        margin: 0;
        padding: 0;
    }

    .nav-menu li {
        position: relative;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 18px 25px;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(76, 175, 80, 0.1), transparent);
        transition: left 0.5s ease;
    }

    .nav-link:hover::before {
        left: 100%;
    }

    .nav-link:hover,
    .nav-link.active {
        color: var(--medium-green);
        border-bottom-color: var(--accent-green);
        background: rgba(76, 175, 80, 0.05);
        transform: translateY(-2px);
    }

    .nav-link i {
        font-size: 1rem;
        color: var(--medium-green);
    }

    .highlight-nav {
        background: linear-gradient(135deg, var(--accent-green), var(--pale-green)) !important;
        color: var(--text-white-green) !important;
        border-radius: 25px !important;
        margin: 0 10px !important;
        border: 2px solid var(--border-green) !important;
    }

    .highlight-nav:hover {
        background: linear-gradient(135deg, var(--medium-green), var(--accent-green)) !important;
        transform: translateY(-3px) scale(1.05) !important;
        box-shadow: 0 10px 30px rgba(76, 175, 80, 0.4) !important;
    }

    .highlight-nav i {
        color: var(--text-white-green) !important;
    }

    /* Dropdown in Navigation */
    .dropdown .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        background: var(--bg-white);
        border: 2px solid var(--border-light);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        min-width: 220px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-15px);
        transition: all 0.3s ease;
        z-index: 1001;
        overflow: hidden;
    }

    .dropdown:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown .dropdown-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        color: var(--text-dark);
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 0;
        font-weight: 500;
    }

    .dropdown .dropdown-menu a:hover {
        background: rgba(76, 175, 80, 0.1);
        color: var(--medium-green);
        padding-left: 25px;
    }

    .dropdown .dropdown-menu a i {
        color: var(--medium-green);
        width: 16px;
        text-align: center;
    }

    /* Mobile Menu */
    .mobile-menu {
        position: fixed;
        top: 0;
        right: -100%;
        width: 300px;
        height: 100vh;
        background: var(--bg-white);
        border-left: 2px solid var(--border-light);
        transition: right 0.3s ease;
        z-index: 1002;
        overflow-y: auto;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
    }

    .mobile-menu.active {
        right: 0;
    }

    .mobile-menu-content {
        padding: 80px 30px 30px;
    }

    .mobile-search {
        margin-bottom: 30px;
    }

    .mobile-search form {
        display: flex;
        background: var(--bg-light-gray);
        border: 2px solid var(--border-light);
        border-radius: 25px;
        overflow: hidden;
    }

    .mobile-search input {
        flex: 1;
        padding: 12px 20px;
        border: none;
        outline: none;
        background: transparent;
        color: var(--text-dark);
    }

    .mobile-search button {
        background: var(--accent-green);
        color: var(--text-white-green);
        border: none;
        padding: 12px 20px;
        cursor: pointer;
    }

    .mobile-nav {
        display: flex;
        flex-direction: column;
    }

    .mobile-nav a {
        color: var(--text-dark);
        text-decoration: none;
        padding: 15px 0;
        border-bottom: 1px solid var(--border-light);
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .mobile-nav a:hover {
        color: var(--medium-green);
        padding-left: 10px;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .header-content {
            grid-template-columns: auto 1fr auto auto;
            gap: 25px;
        }

        .header-search {
            max-width: 300px;
        }

        .search-btn span {
            display: none;
        }

        .nav-link span {
            display: none;
        }

        .nav-link {
            padding: 18px 15px;
        }
    }

    @media (max-width: 768px) {
        .top-bar {
            display: none;
        }

        .header-content {
            grid-template-columns: auto 1fr auto;
            gap: 20px;
        }

        .header-search {
            display: none;
        }

        .header-actions .btn span {
            display: none;
        }

        .header-actions .btn {
            padding: 10px 15px;
            font-size: 0.8rem;
        }

        .mobile-toggle {
            display: flex;
        }

        .main-nav {
            display: none;
        }

        .logo h1 {
            font-size: 1.8rem;
        }

        .user-info span {
            display: none;
        }

        .user-avatar {
            padding: 10px 15px;
        }
    }

    @media (max-width: 480px) {
        .logo h1 {
            font-size: 1.5rem;
        }

        .header-actions {
            gap: 10px;
        }

        .header-actions .btn {
            padding: 8px 12px;
        }

        .cart-link {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .main-header {
            padding: 15px 0;
        }
    }

    /* Animations */
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-menu {
        animation: slideInDown 0.3s ease-out;
    }

    /* Enhanced Hover Effects */
    .search-input-wrapper:hover {
        border-color: var(--accent-green);
    }

    .logo:hover h1 {
        text-shadow: 0 0 20px rgba(102, 187, 106, 0.5);
    }

    /* Loading States */
    .search-btn.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    .search-btn.loading::after {
        content: '';
        width: 16px;
        height: 16px;
        border: 2px solid transparent;
        border-top: 2px solid var(--text-white-green);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-left: 8px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Focus States for Accessibility */
    .nav-link:focus,
    .btn:focus,
    .search-btn:focus {
        outline: 3px solid var(--accent-green);
        outline-offset: 2px;
    }

    /* Smooth Transitions */
    * {
        transition: color 0.3s ease, background 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
    }
    </style>

    <script>
    // Enhanced Mobile menu toggle with animations
    document.addEventListener('DOMContentLoaded', function() {
        const mobileToggle = document.getElementById('mobileToggle');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        if (mobileToggle && mobileMenu) {
            mobileToggle.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                this.classList.toggle('active');
                
                // Prevent body scroll when mobile menu is open
                if (mobileMenu.classList.contains('active')) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (mobileMenu && mobileMenu.classList.contains('active')) {
                if (!mobileMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
                    mobileMenu.classList.remove('active');
                    mobileToggle.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });

        // Enhanced search functionality
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const query = this.querySelector('input[name="query"]').value.trim();
                if (!query) {
                    e.preventDefault();
                    showNotification('Please enter a search term', 'warning');
                    return;
                }
                
                const submitBtn = this.querySelector('.search-btn');
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            });
        }

        // Cart functionality (placeholder)
        updateCartCount();
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Header scroll effect
        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            const header = document.querySelector('.main-header');
            
            if (currentScroll > lastScroll && currentScroll > 100) {
                // Scrolling down
                header.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up
                header.style.transform = 'translateY(0)';
            }
            
            lastScroll = currentScroll;
        });
    });

    // Utility Functions
    function updateCartCount() {
        // This would typically fetch from server/localStorage
        const cartCount = localStorage.getItem('cartCount') || 0;
        const cartCountElement = document.querySelector('.cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = cartCount;
            cartCountElement.style.display = cartCount > 0 ? 'flex' : 'none';
        }
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation-triangle' : 'info'}-circle"></i>
            ${message}
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--accent-green), var(--pale-green));
            color: var(--text-white-green);
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
            z-index: 1003;
            transform: translateX(300px);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            max-width: 300px;
            border: 2px solid var(--border-green);
        `;
        
        if (type === 'warning') {
            notification.style.background = 'linear-gradient(135deg, var(--warning-green), #827717)';
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(300px)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Search enhancement
    function performSearch(query) {
        if (!query.trim()) return;
        
        // Add to search history
        let searchHistory = JSON.parse(localStorage.getItem('searchHistory') || '[]');
        if (!searchHistory.includes(query)) {
            searchHistory.unshift(query);
            searchHistory = searchHistory.slice(0, 5); // Keep only last 5 searches
            localStorage.setItem('searchHistory', JSON.stringify(searchHistory));
        }
        
        // Redirect to search page
        window.location.href = `search.php?query=${encodeURIComponent(query)}`;
    }

    // Add to cart with AJAX
function addToCart(productId, button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    button.disabled = true;
    
    fetch('cart_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check"></i> Added!';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            
            // Update cart count
            updateCartCount();
            
            // Show success message
            showNotification('Product added to cart!', 'success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
                button.disabled = false;
            }, 2000);
        } else {
            throw new Error(data.message || 'Failed to add to cart');
        }
    })
    .catch(error => {
        button.innerHTML = originalText;
        button.disabled = false;
        showNotification(error.message, 'error');
    });
}

// Update cart count
function updateCartCount() {
    fetch('cart_ajax.php?action=count')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = data.count;
                cartCount.classList.add('updated');
                setTimeout(() => cartCount.classList.remove('updated'), 500);
            }
        }
    });
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
    </script>
</body>
</html>
