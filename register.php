
<?php
session_start();
require_once 'config/database.php'; // Use main config

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate inputs
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $password = trim($_POST['password']);
    $role = htmlspecialchars(trim($_POST['role']));
    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''));
    $last_name = htmlspecialchars(trim($_POST['last_name'] ?? ''));
    $username = htmlspecialchars(trim($_POST['username'] ?? ''));

    // Validate required fields
    if (!$email) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (empty($first_name) || empty($last_name) || empty($username)) {
        $error = "First name, last name, and username are required.";
    } elseif (empty($phone) || empty($address) || empty($role)) {
        $error = "Phone, address, and role are required.";
    } else {
        try {
            // Check if email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {
                $error = "Email address already exists.";
            } else {
                // Check if username already exists
                $checkUserStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $checkUserStmt->execute([$username]);
                
                if ($checkUserStmt->rowCount() > 0) {
                    $error = "Username already exists.";
                } else {
                    // Hash the password securely
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user - using simple approach first
                    try {
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, phone, address, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
                        
                        if ($stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $phone, $address, $role])) {
                            $success = "Registration successful! You can now login.";
                            // Clear form data on success
                            $_POST = array();
                        } else {
                            $error = "Error: Unable to register. Please try again.";
                        }
                        
                    } catch (PDOException $e) {
                        error_log("Registration insert error: " . $e->getMessage());
                        
                        // Try with minimal fields if full insert fails
                        try {
                            $simpleStmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
                            if ($simpleStmt->execute([$email, $hashed_password, $role])) {
                                $success = "Registration successful! You can now login.";
                                $_POST = array();
                            } else {
                                $error = "Database error during registration. Please contact support.";
                            }
                        } catch (PDOException $e2) {
                            error_log("Simple insert error: " . $e2->getMessage());
                            $error = "Database configuration error. Please contact support.";
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error = "Database connection error. Please try again later.";
        }
    }
}

include 'includes/header.php';
?>

<!-- Keep all the existing HTML and CSS from the previous version -->
<style>
/* All the existing CSS styles remain the same */
:root {
    --primary-dark-green: #0d4f0f;
    --primary-green: #1a5c2a;
    --medium-green: #2d7d32;
    --light-green: #388e3c;
    --accent-green: #4caf50;
    --pale-green: #66bb6a;
    --text-light-green: #81c784;
    --text-white-green: #e8f5e9;
    --border-green: #2e7d32;
    --gradient-green: linear-gradient(135deg, var(--primary-dark-green) 0%, var(--primary-green) 50%, var(--medium-green) 100%);
    
    --bg-white: #ffffff;
    --bg-light-gray: #f8f9fa;
    --text-dark: #333333;
    --text-gray: #666666;
    --border-light: #e0e0e0;
    --shadow-light: rgba(0, 0, 0, 0.1);
}

body {
    background: var(--bg-white);
    color: var(--text-dark);
    min-height: 100vh;
    padding-top: 0;
}

.register-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px 40px;
    background: var(--bg-light-gray);
    position: relative;
}

.register-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, rgba(76, 175, 80, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(56, 142, 60, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.container {
    max-width: 600px;
    width: 100%;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

.register-form {
    background: var(--bg-white);
    padding: 50px;
    border-radius: 25px;
    box-shadow: 
        0 25px 80px var(--shadow-light),
        0 0 0 1px rgba(76, 175, 80, 0.1);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.register-form::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: var(--gradient-green);
}

.form-header {
    text-align: center;
    margin-bottom: 40px;
    position: relative;
    z-index: 2;
}

.form-header h2 {
    color: var(--text-dark);
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.form-header h2 i {
    color: var(--medium-green);
    font-size: 2.2rem;
}

.alert {
    padding: 18px 25px;
    border-radius: 15px;
    margin-bottom: 30px;
    border: 2px solid;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    animation: slideInDown 0.4s ease-out;
    position: relative;
    z-index: 2;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
    border-color: var(--accent-green);
}

.alert-error {
    background: linear-gradient(135deg, #f8d7da, #f1c2c7);
    color: #721c24;
    border-color: #dc3545;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-dark);
    font-weight: 700;
    margin-bottom: 10px;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group input {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    font-size: 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    background: var(--bg-white);
    color: var(--text-dark);
}

.form-group input:focus {
    outline: none;
    border-color: var(--accent-green);
    box-shadow: 
        0 0 0 4px rgba(76, 175, 80, 0.1),
        0 8px 25px rgba(76, 175, 80, 0.15);
    transform: translateY(-2px);
}

.role-selection {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 25px;
}

.role-option input[type="radio"] {
    display: none;
}

.role-option label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 25px 20px;
    border: 2px solid var(--border-light);
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--bg-white);
    text-align: center;
    font-weight: 600;
    text-transform: none;
    letter-spacing: normal;
}

.role-option label i {
    font-size: 2.5rem;
    color: var(--text-gray);
    transition: all 0.3s ease;
}

.role-option input[type="radio"]:checked + label {
    border-color: var(--accent-green);
    background: rgba(76, 175, 80, 0.05);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(76, 175, 80, 0.2);
}

.role-option input[type="radio"]:checked + label i {
    color: var(--accent-green);
    transform: scale(1.1);
}

.btn-register {
    width: 100%;
    padding: 20px;
    background: var(--gradient-green);
    color: var(--text-white-green);
    border: none;
    border-radius: 15px;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.4s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
    margin-top: 10px;
}

.btn-register:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(76, 175, 80, 0.4);
    background: linear-gradient(135deg, var(--medium-green), var(--light-green));
}

.auth-links {
    text-align: center;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid var(--bg-light-gray);
}

.auth-links a {
    color: var(--medium-green);
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
    padding: 5px 10px;
    border-radius: 8px;
}

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

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .role-selection {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="register-section">
    <div class="container">
        <div class="register-form">
            <div class="form-header">
                <h2>
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </h2>
                <p>Join Eden'sCarshop and start your journey</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?> 
                    <a href="login.php">Login here</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">
                            <i class="fas fa-user"></i>
                            First Name
                        </label>
                        <input type="text" id="first_name" name="first_name" required 
                               placeholder="Enter your first name"
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">
                            <i class="fas fa-user"></i>
                            Last Name
                        </label>
                        <input type="text" id="last_name" name="last_name" required 
                               placeholder="Enter your last name"
                               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-at"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Choose a unique username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your email address"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone"></i>
                            Phone Number
                        </label>
                        <input type="tel" id="phone" name="phone" required 
                               placeholder="Enter your phone number"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Create a strong password" minlength="6">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">
                        <i class="fas fa-map-marker-alt"></i>
                        Address
                    </label>
                    <input type="text" id="address" name="address" required 
                           placeholder="Enter your full address"
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-user-tag"></i>
                        Choose Your Role
                    </label>
                    <div class="role-selection">
                        <div class="role-option">
                            <input type="radio" id="buyer" name="role" value="buyer" required
                                   <?= (($_POST['role'] ?? '') === 'buyer') ? 'checked' : '' ?>>
                            <label for="buyer">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Buyer</span>
                                <small>Browse and purchase cars</small>
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="seller" name="role" value="seller" required
                                   <?= (($_POST['role'] ?? '') === 'seller') ? 'checked' : '' ?>>
                            <label for="seller">
                                <i class="fas fa-car"></i>
                                <span>Seller</span>
                                <small>List and sell your cars</small>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-register" id="submitBtn">
                    <i class="fas fa-user-check"></i>
                    Create Account
                </button>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        submitBtn.disabled = true;
    });
});
</script>

<?php include 'includes/footer.php'; ?>