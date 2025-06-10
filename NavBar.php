<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php if (isset($_SESSION["username"])): ?>
    Hello, <?= htmlspecialchars($_SESSION["username"], ENT_QUOTES, 'UTF-8') ?> |
    <a href="logout.php">Logout</a>
<?php else: ?>
    <a href="login.php">Login</a> |
    <a href="register.php">Register</a>
<?php endif; ?>