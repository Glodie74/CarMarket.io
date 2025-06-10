<?php include 'includes/header.php'; ?>

<section class="cart-section">
    <div class="container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
            <div class="cart-summary">
                <span class="item-count">0 items</span>
            </div>
        </div>

        <div class="cart-content">
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any cars to your cart yet.</p>
                <a href="search.php" class="btn btn-primary">
                    <i class="fas fa-car"></i> Browse Cars
                </a>
            </div>
            
            <!-- Future cart items will be displayed here -->
        </div>
    </div>
</section>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        color: #333;
    }

    .cart-section {
        padding: 40px 0;
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .cart-header {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-header h1 {
        color: #333;
        font-size: 2.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .cart-header h1 i {
        color: #667eea;
    }

    .cart-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 25px;
        border-radius: 25px;
        font-weight: 600;
    }

    .cart-content {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 40px;
        text-align: center;
    }

    .empty-cart {
        max-width: 400px;
        margin: 0 auto;
    }

    .empty-cart-icon {
        margin-bottom: 30px;
    }

    .empty-cart-icon i {
        font-size: 5rem;
        color: #e0e0e0;
        opacity: 0.7;
    }

    .empty-cart h2 {
        color: #333;
        font-size: 1.8rem;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .empty-cart p {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 15px 30px;
        border: none;
        border-radius: 25px;
        font-size: 1.1rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
    }

    .btn i {
        font-size: 1rem;
    }

    /* Future cart items styling */
    .cart-items {
        display: none; /* Will be shown when items are added */
    }

    .cart-item {
        display: flex;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #eee;
        transition: background-color 0.3s ease;
    }

    .cart-item:hover {
        background-color: #f8f9fa;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .item-image {
        width: 100px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 20px;
    }

    .item-details {
        flex: 1;
    }

    .item-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .item-price {
        font-size: 1.1rem;
        color: #667eea;
        font-weight: 700;
    }

    .item-actions {
        display: flex;
        gap: 10px;
    }

    .btn-small {
        padding: 8px 15px;
        font-size: 0.9rem;
        border-radius: 20px;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
        transform: translateY(-2px);
    }

    .cart-total {
        margin-top: 30px;
        padding-top: 30px;
        border-top: 2px solid #eee;
        text-align: right;
    }

    .total-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 20px;
    }

    .checkout-btn {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 15px 40px;
        font-size: 1.2rem;
    }

    .checkout-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(40, 167, 69, 0.4);
    }

    @media (max-width: 768px) {
        .cart-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }

        .cart-header h1 {
            font-size: 1.8rem;
        }

        .cart-content {
            padding: 20px;
        }

        .empty-cart-icon i {
            font-size: 3.5rem;
        }

        .empty-cart h2 {
            font-size: 1.5rem;
        }

        .cart-item {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }

        .item-image {
            margin-right: 0;
        }

        .cart-total {
            text-align: center;
        }
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<?php include 'includes/footer.php'; ?>
