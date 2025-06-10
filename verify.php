<?php
include('includes/db.php'); // Corrected the include statement
session_start();

if (isset($_GET['user'])) {
    // Sanitize the input to prevent SQL injection
    $username = htmlspecialchars(trim($_GET['user']), ENT_QUOTES, 'UTF-8');

    // Use a prepared statement to update the database
    $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE username = ?");
    if (!$stmt) {
        die("<p style='color:red;'>Database error: " . htmlspecialchars($conn->error) . "</p>");
    }
    $stmt->bind_param("s", $username);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "<p style='color:green;'>Account verified successfully. <a href='login.php'>Login</a></p>";
    } else {
        echo "<p style='color:red;'>Invalid verification link or account already verified.</p>";
    }

    $stmt->close();
} else {
    echo "<p style='color:red;'>No user specified for verification.</p>";
}
?>

<h2>Account Verification</h2>
<p>If your account is verified, you can now <a href="login.php">login</a>.</p>