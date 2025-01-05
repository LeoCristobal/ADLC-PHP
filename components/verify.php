<?php
session_start();
require_once 'config.php';

if (isset($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);
    
    // Check if token exists and verify the user
    $sql = "SELECT id FROM users WHERE verification_token = '$token' AND is_verified = 0";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Update user as verified
        $update_sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = '$token'";
        
        if ($conn->query($update_sql)) {
            $_SESSION['success'] = "Email verified successfully!";
        } else {
            $_SESSION['error'] = "Verification failed. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Invalid verification token or account already verified.";
    }
} else {
    $_SESSION['error'] = "Invalid verification link.";
}

// Redirect to login page
header('Location: dashboard.php');
exit();
?> 