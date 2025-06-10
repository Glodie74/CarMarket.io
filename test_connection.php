<?php
require_once 'config/database.php';

echo "<h2>Car Marketplace Database Connection Test</h2>";

try {
    // Test PDO connection
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✓ PDO connection successful!</p>";
    
    // Test database
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    echo "<p>Connected to database: <strong>" . $result['db_name'] . "</strong></p>";
    
    // Test users table
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Users table exists</p>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "<h3>Users table structure:</h3>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li><strong>" . $column['Field'] . "</strong>: " . $column['Type'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Users table does not exist</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}
?>