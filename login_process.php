<?php
session_start();

// Check if database config exists
if (!file_exists('../config/database.php')) {
    $_SESSION['error'] = 'Database configuration not found. Please contact administrator.';
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']) ? true : false;

// Validate input
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Please fill in all fields';
    header('Location: ../login.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address';
    header('Location: ../login.php');
    exit;
}

try {
    // Ensure database connection exists
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        
        // Check if account is active
        if (isset($user['status']) && $user['status'] !== 'active') {
            $_SESSION['error'] = 'Your account is not active. Please contact support.';
            header('Location: ../login.php');
            exit;
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'] ?? $user['email'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'] ?? 'buyer';
        $_SESSION['first_name'] = $user['first_name'] ?? '';
        $_SESSION['last_name'] = $user['last_name'] ?? '';
        
        // Update last login
        try {
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
        } catch (PDOException $e) {
            error_log("Could not update last_login: " . $e->getMessage());
        }

        // Handle Remember Me functionality
        if ($remember_me) {
            try {
                // Generate secure token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Clean up old expired tokens
                $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND expires_at < NOW()")->execute([$user['id']]);
                
                // Limit tokens per user to 5
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM remember_tokens WHERE user_id = ?");
                $countStmt->execute([$user['id']]);
                $tokenCount = $countStmt->fetchColumn();
                
                if ($tokenCount >= 5) {
                    $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ? ORDER BY created_at ASC LIMIT ?")->execute([$user['id'], $tokenCount - 4]);
                }
                
                // Store new token
                $tokenStmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $tokenStmt->execute([$user['id'], $token, $expires]);
                
                // Set cookies
                $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', $secure, true);
                setcookie('remembered_email', $email, time() + (30 * 24 * 60 * 60), '/', '', $secure, true);
                
            } catch (PDOException $e) {
                error_log("Remember me error: " . $e->getMessage());
            }
        } else {
            // Clear existing remember tokens
            if (isset($_COOKIE['remember_token'])) {
                try {
                    $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?")->execute([$_COOKIE['remember_token']]);
                } catch (PDOException $e) {
                    error_log("Token cleanup error: " . $e->getMessage());
                }
            }
            
            // Clear cookies
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('remembered_email', '', time() - 3600, '/');
        }

        // Success message
        $firstName = !empty($user['first_name']) ? $user['first_name'] : 'User';
        $_SESSION['success'] = 'Welcome back, ' . htmlspecialchars($firstName) . '!';
        
        // Redirect based on role
        $userRole = $user['role'] ?? 'buyer';
        switch ($userRole) {
            case 'admin':
                header('Location: ../admin_dashboard.php');
                break;
            case 'seller':
                header('Location: ../Sellers_Dash.php');
                break;
            case 'buyer':
            default:
                header('Location: ../buyer_dashboard.php');
                break;
        }
        exit;
        
    } else {
        $_SESSION['error'] = 'Invalid email or password';
        header('Location: ../login.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Database error in login: " . $e->getMessage());
    $_SESSION['error'] = 'Database error occurred. Please try again later.';
    header('Location: ../login.php');
    exit;
} catch (Exception $e) {
    error_log("General error in login: " . $e->getMessage());
    $_SESSION['error'] = 'An unexpected error occurred. Please try again.';
    header('Location: ../login.php');
    exit;
}
?>
