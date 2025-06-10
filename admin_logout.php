<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Unset all session variables
$_SESSION = [];

// Destroy the session completely
session_destroy();

// Redirect to the admin login page
header("Location: admin_login.php");
exit();
?>