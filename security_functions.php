<?php

function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function hashToken($token) {
    return hash('sha256', $token);
}

function validateTokenSecurity($user_id, $token) {
    // Add additional security checks
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // You can store and validate these for additional security
    return true; // Implement your security logic here
}

function limitRememberTokens($user_id, $max_tokens = 5) {
    global $pdo;
    
    try {
        // Count existing tokens for user
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM remember_tokens WHERE user_id = ?");
        $countStmt->execute([$user_id]);
        $count = $countStmt->fetchColumn();
        
        // If user has too many tokens, delete oldest ones
        if ($count >= $max_tokens) {
            $deleteStmt = $pdo->prepare("DELETE FROM remember_tokens 
                                        WHERE user_id = ? 
                                        ORDER BY created_at ASC 
                                        LIMIT ?");
            $deleteStmt->execute([$user_id, $count - $max_tokens + 1]);
        }
    } catch (PDOException $e) {
        error_log("Token limit error: " . $e->getMessage());
    }
}
?>