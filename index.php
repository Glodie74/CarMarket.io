<?php
session_start();
require_once 'config/database.php';

// Get featured products
try {
    $stmt = $pdo->prepare("SELECT p.*, u.username as seller_name FROM products p 
                          JOIN users u ON p.user_id = u.id 
                          WHERE p.status = 'active' 
                          ORDER BY p.created_at DESC LIMIT 8");
    $stmt->execute();
    $featured_products = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_products = [];
}

// Get total stats
try {
    $total_cars = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    $total_sellers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
    $total_buyers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'buyer'")->fetchColumn();
} catch (PDOException $e) {
    $total_cars = 0;
    $total_sellers = 0;
    $total_buyers = 0;
}

include 'includes/header.php';
?>

<style>
/* Color Variables */
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
    
    /* White background variables */
    --bg-white: #ffffff;
    --bg-light-gray: #f8f9fa;
    --bg-section: #f5f7fa;
    --text-dark: #333333;
    --text-gray: #666666;
    --text-light-gray: #999999;
    --border-light: #e0e0e0;
    --shadow-light: rgba(0, 0, 0, 0.1);
}

/* Global Styles - White Background */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: var(--text-dark);
    background: var(--bg-white);
    overflow-x: hidden;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Hero Section - Green Theme */
.hero-section {
    min-height: 100vh;
    position: relative;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: var(--gradient-green);
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, var(--accent-green) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, var(--light-green) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, var(--medium-green) 0%, transparent 50%);
    opacity: 0.1;
}

.hero-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        linear-gradient(45deg, transparent 30%, rgba(76, 175, 80, 0.1) 30%, rgba(76, 175, 80, 0.1) 32%, transparent 32%),
        linear-gradient(-45deg, transparent 30%, rgba(102, 187, 106, 0.1) 30%, rgba(102, 187, 106, 0.1) 32%, transparent 32%);
    background-size: 60px 60px;
}

.hero-content {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    padding: 80px 0;
}

.hero-title {
    font-size: 4rem;
    font-weight: 900;
    margin-bottom: 25px;
    line-height: 1.1;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    color: var(--text-white-green);
}

.highlight {
    background: linear-gradient(135deg, var(--accent-green), var(--pale-green));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.3rem;
    margin-bottom: 40px;
    opacity: 0.9;
    color: var(--text-light-green);
    line-height: 1.6;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    margin-bottom: 50px;
    flex-wrap: wrap;
}

.hero-stats {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2.5rem;
    font-weight: 900;
    color: var(--pale-green);
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-light-green);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.hero-image {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

.hero-car-showcase {
    position: relative;
    width: 100%;
    max-width: 500px;
}

.car-card-float {
    background: linear-gradient(135deg, var(--card-green), rgba(19, 64, 22, 0.9));
    border: 2px solid var(--border-green);
    border-radius: 25px;
    padding: 20px;
    box-shadow: 
        0 25px 80px rgba(13, 79, 15, 0.4),
        inset 0 1px 0 rgba(102, 187, 106, 0.2);
    animation: float 6s ease-in-out infinite;
    backdrop-filter: blur(10px);
}

.car-card-float img {
    width: 100%;
    border-radius: 15px;
    margin-bottom: 15px;
}

.car-info h4 {
    font-size: 1.3rem;
    color: var(--text-white-green);
    margin-bottom: 5px;
}

.car-info p {
    color: var(--text-light-green);
    font-size: 1rem;
}

.hero-scroll-indicator {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    color: var(--text-light-green);
    animation: bounce 2s infinite;
    z-index: 2;
}

.hero-scroll-indicator span {
    display: block;
    font-size: 0.9rem;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Search Section - White Background with Green Accents */
.search-section {
    padding: 80px 0;
    background: var(--bg-white);
    border-bottom: 1px solid var(--border-light);
}

.search-wrapper {
    background: var(--bg-white);
    border: 2px solid var(--border-green);
    border-radius: 25px;
    padding: 50px;
    box-shadow: 0 10px 30px var(--shadow-light);
}

.search-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 35px;
}

.search-group {
    display: flex;
    flex-direction: column;
}

.search-group label {
    color: var(--text-dark);
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.9rem;
}

.search-group label i {
    color: var(--medium-green);
}

.search-group select {
    padding: 15px 20px;
    border: 2px solid var(--border-light);
    border-radius: 12px;
    background: var(--bg-white);
    color: var(--text-dark);
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
}

.search-group select:focus {
    outline: none;
    border-color: var(--accent-green);
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
    transform: translateY(-2px);
}

.btn-search {
    width: 100%;
    padding: 18px;
    font-size: 1.2rem;
    border-radius: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Section Styles - White Background */
.section-title {
    font-size: 3rem;
    font-weight: 800;
    text-align: center;
    margin-bottom: 15px;
    color: var(--text-dark);
}

.section-subtitle {
    font-size: 1.2rem;
    text-align: center;
    color: var(--text-gray);
    margin-bottom: 50px;
    opacity: 0.9;
}

.section-header {
    margin-bottom: 60px;
}

/* Featured Cars Section - White Background */
.featured-cars-section {
    padding: 100px 0;
    background: var(--bg-section);
    position: relative;
}

.cars-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
    position: relative;
    z-index: 2;
}

.car-card {
    background: var(--bg-white);
    border: 2px solid var(--border-light);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s ease;
    box-shadow: 0 5px 20px var(--shadow-light);
}

.car-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 15px 40px rgba(76, 175, 80, 0.2);
    border-color: var(--accent-green);
}

.car-image {
    position: relative;
    overflow: hidden;
}

.car-image img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.car-card:hover .car-image img {
    transform: scale(1.1);
}

.car-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: linear-gradient(135deg, var(--accent-green), var(--pale-green));
    color: var(--text-white-green);
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.car-actions {
    position: absolute;
    top: 15px;
    right: 15px;
    display: flex;
    gap: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.car-card:hover .car-actions {
    opacity: 1;
}

.btn-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    backdrop-filter: blur(10px);
}

.btn-favorite {
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: 2px solid rgba(220, 53, 69, 0.3);
}

.btn-share {
    background: rgba(76, 175, 80, 0.9);
    color: white;
    border: 2px solid rgba(76, 175, 80, 0.3);
}

.btn-icon:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.car-content {
    padding: 25px;
}

.car-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.car-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
    line-height: 1.3;
}

.car-price {
    font-size: 1.5rem;
    font-weight: 900;
    color: var(--medium-green);
}

.car-details {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 15px;
    background: var(--bg-light-gray);
    border-radius: 12px;
    border: 1px solid var(--border-light);
}

.detail-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    flex: 1;
}

.detail-item i {
    color: var(--medium-green);
    font-size: 1.1rem;
}

.detail-item span {
    font-size: 0.9rem;
    color: var(--text-gray);
    font-weight: 600;
}

.car-seller {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 25px;
    color: var(--text-gray);
    font-size: 0.9rem;
    padding: 10px 15px;
    background: var(--bg-light-gray);
    border-radius: 8px;
    border: 1px solid var(--border-light);
}

.car-seller i {
    color: var(--medium-green);
}

.car-footer {
    display: flex;
    gap: 15px;
}

/* Features Section - White Background */
.features-section {
    padding: 100px 0;
    background: var(--bg-white);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
}

.feature-card {
    background: var(--bg-white);
    border: 2px solid var(--border-light);
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    transition: all 0.4s ease;
    box-shadow: 0 5px 20px var(--shadow-light);
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(76, 175, 80, 0.2);
    border-color: var(--accent-green);
}

.feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 25px;
    background: linear-gradient(135deg, var(--accent-green), var(--pale-green));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--text-white-green);
    box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
}

.feature-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 15px;
}

.feature-content p {
    color: var(--text-gray);
    line-height: 1.6;
    font-size: 1rem;
}

/* CTA Section - Green Theme */
.cta-section {
    padding: 100px 0;
    position: relative;
    background: var(--gradient-green);
    overflow: hidden;
}

.cta-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.cta-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 25% 75%, var(--accent-green) 0%, transparent 50%),
        radial-gradient(circle at 75% 25%, var(--light-green) 0%, transparent 50%);
    opacity: 0.2;
}

.cta-content {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.cta-text h2 {
    font-size: 3.5rem;
    font-weight: 900;
    color: var(--text-white-green);
    margin-bottom: 20px;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.cta-text p {
    font-size: 1.3rem;
    color: var(--text-light-green);
    margin-bottom: 40px;
    line-height: 1.6;
}

.cta-buttons {
    display: flex;
    gap: 25px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 30px;
    border: 2px solid;
    border-radius: 15px;
    text-decoration: none;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
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

.btn-secondary {
    background: linear-gradient(135deg, var(--accent-green), var(--pale-green));
    color: var(--text-white-green);
    border-color: var(--accent-green);
}

.btn-secondary:hover {
    background: linear-gradient(135deg, var(--light-green), var(--accent-green));
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(76, 175, 80, 0.4);
}

.btn-outline {
    background: transparent;
    color: var(--pale-green);
    border-color: var(--border-green);
}

.btn-outline:hover {
    background: var(--medium-green);
    color: var(--text-white-green);
    border-color: var(--accent-green);
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(46, 125, 50, 0.4);
}

.btn-large {
    padding: 20px 40px;
    font-size: 1.2rem;
}

/* Section Footer */
.section-footer {
    text-align: center;
    position: relative;
    z-index: 2;
}

/* No Cars State */
.no-cars {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-gray);
    position: relative;
    z-index: 2;
}

.no-cars i {
    font-size: 5rem;
    color: var(--medium-green);
    margin-bottom: 25px;
    opacity: 0.7;
}

.no-cars h3 {
    font-size: 2rem;
    color: var(--text-dark);
    margin-bottom: 15px;
    font-weight: 700;
}

.no-cars p {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

/* Animations */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
    40% { transform: translateX(-50%) translateY(-10px); }
    60% { transform: translateX(-50%) translateY(-5px); }
}

/* Responsive Design */
@media (max-width: 1024px) {
    .hero-content {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }
    
    .hero-title {
        font-size: 3.5rem;
    }
    
    .features-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
    
    .cars-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.8rem;
    }
    
    .section-title {
        font-size: 2.2rem;
    }
    
    .cta-text h2 {
        font-size: 2.5rem;
    }
    
    .search-grid {
        grid-template-columns: 1fr;
    }
    
    .hero-buttons,
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .hero-stats {
        justify-content: center;
        gap: 30px;
    }
    
    .car-footer {
        flex-direction: column;
    }
    
    .search-wrapper {
        padding: 30px 25px;
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 2.2rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .cta-text h2 {
        font-size: 2rem;
    }
    
    .hero-subtitle,
    .section-subtitle,
    .cta-text p {
        font-size: 1rem;
    }
    
    .search-wrapper,
    .car-content,
    .feature-card {
        padding: 20px;
    }
    
    .btn-large {
        padding: 15px 25px;
        font-size: 1rem;
    }
}

/* Enhanced Hover Effects */
.car-card:hover .car-title {
    color: var(--medium-green);
}

.feature-card:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
}

.feature-card:hover h3 {
    color: var(--medium-green);
}

/* Smooth Scrolling */
html {
    scroll-behavior: smooth;
}

/* Loading States */
.cars-grid.loading {
    opacity: 0.7;
    pointer-events: none;
}

.car-card.loading {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-background">
        <div class="hero-overlay"></div>
        <div class="hero-pattern"></div>
    </div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Find Your Perfect <span class="highlight">Dream Car</span>
                </h1>
                <p class="hero-subtitle">
                    Discover thousands of quality vehicles from trusted sellers. 
                    Your next car adventure starts here at Eden's CarShop.
                </p>
                <div class="hero-buttons">
                    <a href="#featured-cars" class="btn btn-primary btn-large">
                        <i class="fas fa-car"></i>
                        Browse Cars
                    </a>
                    <a href="register.php" class="btn btn-secondary btn-large">
                        <i class="fas fa-user-plus"></i>
                        Join Now
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($total_cars) ?>+</span>
                        <span class="stat-label">Cars Available</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($total_sellers) ?>+</span>
                        <span class="stat-label">Trusted Sellers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($total_buyers) ?>+</span>
                        <span class="stat-label">Happy Buyers</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-car-showcase">
                    <div class="car-card-float">
                        <img src="assets/images/hero-car.jpg" alt="Featured Car" onerror="this.src='https://via.placeholder.com/400x250/2d7d32/ffffff?text=Dream+Car'">
                        <div class="car-info">
                            <h4>Premium Selection</h4>
                            <p>Quality Guaranteed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-scroll-indicator">
        <span>Scroll to explore</span>
        <i class="fas fa-chevron-down"></i>
    </div>
</section>

<!-- Search Section -->
<section class="search-section">
    <div class="container">
        <div class="search-wrapper">
            <h2 class="section-title">Find Your Perfect Car</h2>
            <form class="search-form" action="search.php" method="GET">
                <div class="search-grid">
                    <div class="search-group">
                        <label for="brand"><i class="fas fa-industry"></i> Brand</label>
                        <select id="brand" name="brand">
                            <option value="">Any Brand</option>
                            <option value="Toyota">Toyota</option>
                            <option value="Honda">Honda</option>
                            <option value="BMW">BMW</option>
                            <option value="Mercedes">Mercedes</option>
                            <option value="Audi">Audi</option>
                            <option value="Ford">Ford</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <label for="price-range"><i class="fas fa-dollar-sign"></i> Price Range</label>
                        <select id="price-range" name="price_range">
                            <option value="">Any Price</option>
                            <option value="0-10000">Under $10,000</option>
                            <option value="10000-25000">$10,000 - $25,000</option>
                            <option value="25000-50000">$25,000 - $50,000</option>
                            <option value="50000-100000">$50,000 - $100,000</option>
                            <option value="100000+">Over $100,000</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <label for="year"><i class="fas fa-calendar"></i> Year</label>
                        <select id="year" name="year">
                            <option value="">Any Year</option>
                            <?php for($year = date('Y'); $year >= 2000; $year--): ?>
                                <option value="<?= $year ?>"><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="search-group">
                        <label for="transmission"><i class="fas fa-cogs"></i> Transmission</label>
                        <select id="transmission" name="transmission">
                            <option value="">Any Transmission</option>
                            <option value="Manual">Manual</option>
                            <option value="Automatic">Automatic</option>
                            <option value="CVT">CVT</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-search">
                    <i class="fas fa-search"></i>
                    Search Cars
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Featured Cars Section -->
<section class="featured-cars-section" id="featured-cars">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Cars</h2>
            <p class="section-subtitle">Discover our handpicked selection of premium vehicles</p>
        </div>
        
        <?php if (count($featured_products) > 0): ?>
            <div class="cars-grid">
                <?php foreach ($featured_products as $car): ?>
                    <div class="car-card">
                        <div class="car-image">
                            <img src="uploads/<?= htmlspecialchars($car['image1']) ?>" 
                                 alt="<?= htmlspecialchars($car['title']) ?>"
                                 onerror="this.src='https://via.placeholder.com/350x250/2d7d32/ffffff?text=Car+Image'">
                            <div class="car-badge">Featured</div>
                            <div class="car-actions">
                                <button class="btn-icon btn-favorite" title="Add to Favorites" data-car-id="<?= $car['id'] ?>">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="btn-icon btn-share" title="Share" data-car-title="<?= htmlspecialchars($car['title']) ?>">
                                    <i class="fas fa-share"></i>
                                </button>
                            </div>
                        </div>
                        <div class="car-content">
                            <div class="car-header">
                                <h3 class="car-title"><?= htmlspecialchars($car['title']) ?></h3>
                                <span class="car-price">$<?= number_format($car['price'], 2) ?></span>
                            </div>
                            <div class="car-details">
                                <div class="detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?= htmlspecialchars($car['year']) ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-road"></i>
                                    <span><?= number_format($car['mileage']) ?> km</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-cogs"></i>
                                    <span><?= htmlspecialchars($car['transmission']) ?></span>
                                </div>
                            </div>
                            <div class="car-seller">
                                <i class="fas fa-user"></i>
                                <span>Sold by <?= htmlspecialchars($car['seller_name']) ?></span>
                            </div>
                            <div class="car-footer">
                                <a href="car-details.php?id=<?= $car['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                                <a href="contact-seller.php?id=<?= $car['id'] ?>" class="btn btn-secondary">
                                    <i class="fas fa-envelope"></i>
                                    Contact
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="cars.php" class="btn btn-outline btn-large">
                    <i class="fas fa-th"></i>
                    View All Cars
                </a>
            </div>
        <?php else: ?>
            <div class="no-cars">
                <i class="fas fa-car"></i>
                <h3>No Cars Available</h3>
                <p>Be the first to list your car for sale!</p>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Start Selling
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Why Choose Eden's CarShop?</h2>
            <p class="section-subtitle">Your trusted partner in finding the perfect vehicle</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="feature-content">
                    <h3>Verified Sellers</h3>
                    <p>All our sellers are verified and trusted. Buy with confidence knowing you're dealing with legitimate dealers.</p>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="feature-content">
                    <h3>Advanced Search</h3>
                    <p>Find exactly what you're looking for with our powerful search filters and sorting options.</p>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="feature-content">
                    <h3>Secure Transactions</h3>
                    <p>Safe and secure payment processing with buyer protection and satisfaction guarantee.</p>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="feature-content">
                    <h3>24/7 Support</h3>
                    <p>Our dedicated support team is here to help you every step of the way, day or night.</p>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <div class="feature-content">
                    <h3>Quality Assured</h3>
                    <p>Every vehicle undergoes thorough inspection to ensure quality and reliability standards.</p>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="feature-content">
                    <h3>Mobile Friendly</h3>
                    <p>Browse and buy cars on any device with our responsive and user-friendly interface.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="cta-background">
        <div class="cta-overlay"></div>
    </div>
    <div class="container">
        <div class="cta-content">
            <div class="cta-text">
                <h2>Ready to Find Your Dream Car?</h2>
                <p>Join thousands of satisfied customers who found their perfect vehicle with us. Start your journey today!</p>
            </div>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary btn-large">
                    <i class="fas fa-user-plus"></i>
                    Get Started
                </a>
                <a href="cars.php" class="btn btn-outline btn-large">
                    <i class="fas fa-search"></i>
                    Browse Cars
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// Enhanced JavaScript for better user experience
document.addEventListener('DOMContentLoaded', function() {
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

    // Add loading state to search form
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('.btn-search');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            submitBtn.disabled = true;
        });
    }

    // Enhanced favorite button functionality
    document.querySelectorAll('.btn-favorite').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('favorited');
            const icon = this.querySelector('i');
            
            if (this.classList.contains('favorited')) {
                icon.className = 'fas fa-heart';
                this.style.background = 'linear-gradient(135deg, #e91e63, #f06292)';
                showNotification('Added to favorites!', 'success');
            } else {
                icon.className = 'far fa-heart';
                this.style.background = 'rgba(220, 53, 69, 0.9)';
                showNotification('Removed from favorites!', 'info');
            }
        });
    });

    // Share button functionality
    document.querySelectorAll('.btn-share').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const carTitle = this.getAttribute('data-car-title');
            
            if (navigator.share) {
                navigator.share({
                    title: carTitle,
                    text: `Check out this car: ${carTitle}`,
                    url: window.location.href
                }).catch(console.error);
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    showNotification('Link copied to clipboard!', 'success');
                }).catch(() => {
                    showNotification('Failed to copy link', 'error');
                });
            }
        });
    });

    // Search form enhancements
    const searchInputs = document.querySelectorAll('.search-group select');
    searchInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.style.borderColor = this.value ? 'var(--accent-green)' : 'var(--border-light)';
        });
    });

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);

    // Observe all car cards and feature cards
    document.querySelectorAll('.car-card, .feature-card').forEach(card => {
        observer.observe(card);
    });
});

// Notification function
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const iconClass = type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info';
    notification.innerHTML = `
        <i class="fas fa-${iconClass}-circle"></i>
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
        z-index: 1000;
        transform: translateX(300px);
        transition: transform 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        max-width: 300px;
    `;
    
    if (type === 'error') {
        notification.style.background = 'linear-gradient(135deg, #d32f2f, #f44336)';
    }
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Animate out
    setTimeout(() => {
        notification.style.transform = 'translateX(300px)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
.animate-in {
    animation: slideInUp 0.8s ease-out forwards;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
`;
document.head.appendChild(style);
</script>

<?php include 'includes/footer.php'; ?>