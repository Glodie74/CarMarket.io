<?php
session_start();
require_once 'config/database.php'; // Use main config

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate user inputs
    $username = htmlspecialchars(trim($_POST["username"]), ENT_QUOTES, 'UTF-8');
    $new_password = trim($_POST["new_password"]);

    // Validate password length
    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Hash the new password securely
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Prepare and execute the SQL statement using PDO
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->execute([$hashed_password, $username]);

            // Check if the password was updated
            if ($stmt->rowCount() > 0) {
                $success = "Password reset successfully.";
            } else {
                $error = "Username not found or password unchanged.";
            }
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            $error = "Database error occurred. Please try again.";
        }
    }
}

include 'includes/header.php';
?>
<!-- Keep existing HTML and styling -->