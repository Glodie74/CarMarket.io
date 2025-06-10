<?php
session_start();
require_once 'config/database.php';

// Delete remember token from database if exists
if (isset($_COOKIE['remember_token'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
    } catch (PDOException $e) {
        error_log("Logout error: " . $e->getMessage());
    }
    
    // Clear remember me cookies
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('remembered_email', '', time() - 3600, '/');
}

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

$_SESSION = array();
session_start();
$_SESSION['success'] = 'You have been logged out successfully.';

header('Location: login.php');
exit;
?>