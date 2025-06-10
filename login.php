<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once 'config/database.php';
    
    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare("SELECT u.*, rt.user_id FROM users u 
                          JOIN remember_tokens rt ON u.id = rt.user_id 
                          WHERE rt.token = ? AND rt.expires_at > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php');
        exit;
    }
}

include 'includes/header.php';
?>

<div class="login-container">
    <div class="container">
        <div class="login-wrapper">
            <div class="login-image">
                <img src="assets/images/login-bg.jpg" alt="Login Background">
                <div class="login-overlay">
                    <h2>Welcome Back!</h2>
                    <p>Sign in to access your account and manage your car listings</p>
                </div>
            </div>
            
            <div class="login-form-section">
                <div class="login-form-wrapper">
                    <div class="login-header">
                        <h1>Sign In</h1>
                        <p>Enter your credentials to access your account</p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($_SESSION['success']) ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form class="login-form" action="auth/login_process.php" method="POST" id="loginForm">
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Email Address
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   placeholder="Enter your email"
                                   required
                                   value="<?= isset($_COOKIE['remembered_email']) ? htmlspecialchars($_COOKIE['remembered_email']) : '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <div class="password-input">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter your password"
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" 
                                       id="remember_me" 
                                       name="remember_me" 
                                       value="1"
                                       <?= isset($_COOKIE['remembered_email']) ? 'checked' : '' ?>>
                                <label for="remember_me">
                                    <span class="checkmark"></span>
                                    Remember me for 30 days
                                </label>
                            </div>
                            
                            <a href="forgot-password.php" class="forgot-password">
                                Forgot Password?
                            </a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In
                        </button>

                        <div class="login-divider">
                            <span>or continue with</span>
                        </div>

                        <div class="social-login">
                            <button type="button" class="btn btn-social btn-google">
                                <i class="fab fa-google"></i>
                                Google
                            </button>
                            <button type="button" class="btn btn-social btn-facebook">
                                <i class="fab fa-facebook-f"></i>
                                Facebook
                            </button>
                        </div>

                        <div class="signup-link">
                            <p>Don't have an account? <a href="register.php">Create one now</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dark Green Color Variables */
:root {
    --primary-dark-green: #0d4f0f;
    --primary-green: #1a5c2a;
    --medium-green: #2d7d32;
    --light-green: #388e3c;
    --accent-green: #4caf50;
    --pale-green: #66bb6a;
    --background-green: #0a2e0d;
    --card-green: #134016;
    --text-light-green: #81c784;
    --text-white-green: #e8f5e9;
    --border-green: #2e7d32;
    --hover-green: #1b5e20;
    --success-green: #43a047;
    --warning-green: #689f38;
    --danger-red: #388e3c;
}

/* Login Page Styles */
.login-container {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--background-green) 0%, var(--primary-dark-green) 50%, var(--primary-green) 100%);
    padding: 20px 0;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.login-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, var(--medium-green) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, var(--light-green) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, var(--accent-green) 0%, transparent 50%);
    opacity: 0.1;
    z-index: 1;
}

.container {
    position: relative;
    z-index: 2;
}

.login-wrapper {
    background: linear-gradient(135deg, var(--card-green) 0%, var(--primary-dark-green) 100%);
    border: 2px solid var(--border-green);
    border-radius: 25px;
    box-shadow: 
        0 25px 80px rgba(13, 79, 15, 0.4),
        inset 0 1px 0 rgba(102, 187, 106, 0.2);
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1100px;
    margin: 0 auto;
    min-height: 650px;
    backdrop-filter: blur(10px);
}

.login-image {
    position: relative;
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--medium-green) 50%, var(--light-green) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.login-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        linear-gradient(45deg, transparent 30%, var(--accent-green) 30%, var(--accent-green) 32%, transparent 32%),
        linear-gradient(-45deg, transparent 30%, var(--pale-green) 30%, var(--pale-green) 32%, transparent 32%);
    opacity: 0.1;
    z-index: 1;
}

.login-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.2;
    filter: sepia(100%) hue-rotate(90deg) saturate(200%);
}

.login-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: var(--text-white-green);
    z-index: 3;
    padding: 30px;
}

.login-overlay h2 {
    font-size: 3rem;
    margin-bottom: 20px;
    font-weight: 900;
    text-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
    color: var(--text-white-green);
}

.login-overlay p {
    font-size: 1.2rem;
    opacity: 0.95;
    max-width: 350px;
    line-height: 1.6;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.login-form-section {
    padding: 70px 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--card-green) 0%, rgba(19, 64, 22, 0.8) 100%);
}

.login-form-wrapper {
    width: 100%;
    max-width: 420px;
}

.login-header {
    text-align: center;
    margin-bottom: 45px;
}

.login-header h1 {
    font-size: 2.8rem;
    color: var(--text-white-green);
    margin-bottom: 15px;
    font-weight: 800;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.login-header p {
    color: var(--text-light-green);
    font-size: 1.1rem;
    opacity: 0.9;
}

.alert {
    padding: 18px 25px;
    border-radius: 15px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    border: 1px solid;
}

.alert-error {
    background: linear-gradient(135deg, var(--danger-red) 0%, var(--medium-green) 100%);
    color: var(--text-white-green);
    border-color: var(--border-green);
    box-shadow: 0 4px 15px rgba(56, 142, 60, 0.3);
}

.alert-success {
    background: linear-gradient(135deg, var(--success-green) 0%, var(--accent-green) 100%);
    color: var(--text-white-green);
    border-color: var(--success-green);
    box-shadow: 0 4px 15px rgba(67, 160, 71, 0.3);
}

.form-group {
    margin-bottom: 30px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-white-green);
    font-weight: 700;
    margin-bottom: 12px;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group label i {
    color: var(--pale-green);
}

.form-group input {
    width: 100%;
    padding: 18px 25px;
    border: 2px solid var(--border-green);
    border-radius: 15px;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, var(--card-green) 0%, rgba(19, 64, 22, 0.8) 100%);
    color: var(--text-white-green);
    font-weight: 500;
}

.form-group input::placeholder {
    color: var(--text-light-green);
    opacity: 0.7;
}

.form-group input:focus {
    outline: none;
    border-color: var(--accent-green);
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--card-green) 100%);
    box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.2);
    transform: translateY(-2px);
}

.password-input {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-light-green);
    cursor: pointer;
    padding: 8px;
    transition: all 0.3s ease;
    border-radius: 50%;
}

.password-toggle:hover {
    color: var(--pale-green);
    background: rgba(76, 175, 80, 0.1);
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    flex-wrap: wrap;
    gap: 15px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 10px;
}

.remember-me input[type="checkbox"] {
    display: none;
}

.remember-me label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 0.95rem;
    color: var(--text-light-green);
    font-weight: 600;
    text-transform: none;
    letter-spacing: normal;
}

.checkmark {
    width: 22px;
    height: 22px;
    border: 2px solid var(--border-green);
    border-radius: 6px;
    position: relative;
    transition: all 0.3s ease;
    background: var(--card-green);
}

.remember-me input[type="checkbox"]:checked + label .checkmark {
    background: linear-gradient(135deg, var(--accent-green), var(--pale-green));
    border-color: var(--accent-green);
    transform: scale(1.1);
}

.remember-me input[type="checkbox"]:checked + label .checkmark::after {
    content: '✓';
    position: absolute;
    color: var(--text-white-green);
    font-size: 14px;
    font-weight: bold;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.forgot-password {
    color: var(--pale-green);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 5px 10px;
    border-radius: 8px;
}

.forgot-password:hover {
    color: var(--text-white-green);
    background: rgba(76, 175, 80, 0.1);
    transform: translateY(-1px);
}

.btn-login {
    width: 100%;
    padding: 20px;
    font-size: 1.2rem;
    font-weight: 700;
    border-radius: 15px;
    margin-bottom: 30px;
    background: linear-gradient(135deg, var(--medium-green), var(--light-green), var(--accent-green));
    color: var(--text-white-green);
    border: 2px solid var(--border-green);
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 8px 25px rgba(45, 125, 50, 0.3);
}

.btn-login:hover {
    background: linear-gradient(135deg, var(--primary-green), var(--medium-green), var(--light-green));
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(27, 94, 32, 0.4);
}

.btn-login:active {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(27, 94, 32, 0.3);
}

.login-divider {
    text-align: center;
    margin: 30px 0;
    position: relative;
}

.login-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--border-green), transparent);
}

.login-divider span {
    background: var(--card-green);
    padding: 0 25px;
    color: var(--text-light-green);
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.social-login {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 35px;
}

.btn-social {
    padding: 15px;
    border: 2px solid var(--border-green);
    background: linear-gradient(135deg, var(--card-green) 0%, rgba(19, 64, 22, 0.8) 100%);
    border-radius: 15px;
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    color: var(--text-white-green);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-google:hover {
    border-color: var(--accent-green);
    background: linear-gradient(135deg, var(--medium-green), var(--accent-green));
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
}

.btn-facebook:hover {
    border-color: var(--pale-green);
    background: linear-gradient(135deg, var(--light-green), var(--pale-green));
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 187, 106, 0.3);
}

.signup-link {
    text-align: center;
    color: var(--text-light-green);
    font-size: 1rem;
}

.signup-link a {
    color: var(--pale-green);
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s ease;
    padding: 5px 10px;
    border-radius: 8px;
}

.signup-link a:hover {
    color: var(--text-white-green);
    background: rgba(76, 175, 80, 0.1);
    transform: translateY(-1px);
}

/* Enhanced Button Styles */
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

.btn-secondary {
    background: linear-gradient(135deg, var(--accent-green), var(--pale-green));
    color: var(--text-white-green);
    border-color: var(--accent-green);
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-wrapper {
        grid-template-columns: 1fr;
        margin: 20px;
        min-height: auto;
    }
    
    .login-image {
        min-height: 200px;
    }
    
    .login-overlay h2 {
        font-size: 2rem;
    }
    
    .login-form-section {
        padding: 50px 40px;
    }
    
    .login-header h1 {
        font-size: 2.2rem;
    }
    
    .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    
    .social-login {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-group input {
        padding: 15px 20px;
        font-size: 1rem;
    }
    
    .btn-login {
        padding: 18px;
        font-size: 1.1rem;
    }
}

@media (max-width: 480px) {
    .login-container {
        padding: 10px 0;
    }
    
    .login-wrapper {
        margin: 10px;
    }
    
    .login-form-section {
        padding: 40px 30px;
    }
    
    .login-header h1 {
        font-size: 2rem;
    }
    
    .login-overlay h2 {
        font-size: 1.8rem;
    }
    
    .login-overlay p {
        font-size: 1rem;
    }
}

/* Loading Animation */
.btn-login.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-login.loading::after {
    content: '';
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-top: 2px solid var(--text-white-green);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Form Focus Animations */
.form-group {
    position: relative;
}

.form-group input:focus + label,
.form-group input:not(:placeholder-shown) + label {
    transform: translateY(-25px) scale(0.8);
    color: var(--pale-green);
}

/* Success/Error Message Animations */
.alert {
    animation: slideInDown 0.5s ease-out;
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

/* Hover Effects for Interactive Elements */
.form-group input:hover {
    border-color: var(--pale-green);
}

.checkmark:hover {
    border-color: var(--pale-green);
    transform: scale(1.05);
}

/* Background Pattern */
.login-container::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(circle at 25% 25%, var(--medium-green) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, var(--light-green) 0%, transparent 50%);
    opacity: 0.05;
    z-index: 1;
}
</style>

<script>
function togglePassword() {
    const passwordField = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        passwordIcon.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        passwordIcon.className = 'fas fa-eye';
    }
}

// Enhanced form validation with loading state
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const submitBtn = document.querySelector('.btn-login');
    
    if (!email || !password) {
        e.preventDefault();
        showAlert('Please fill in all fields', 'error');
        return;
    }
    
    if (!isValidEmail(email)) {
        e.preventDefault();
        showAlert('Please enter a valid email address', 'error');
        return;
    }
    
    // Add loading state
    submitBtn.classList.add('loading');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    const form = document.querySelector('.login-form');
    form.insertBefore(alertDiv, form.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Add input animations
document.querySelectorAll('.form-group input').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
    });
    
    input.addEventListener('blur', function() {
        if (!this.value) {
            this.parentElement.classList.remove('focused');
        }
    });
});

// Smooth scrolling and entrance animations
window.addEventListener('load', function() {
    document.querySelector('.login-wrapper').style.animation = 'slideInUp 0.8s ease-out';
});

// Add CSS for entrance animation
const style = document.createElement('style');
style.textContent = `
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