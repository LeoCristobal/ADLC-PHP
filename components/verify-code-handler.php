<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';

if (isset($_POST['code']) && is_array($_POST['code'])) {
    $verification_code = implode('', $_POST['code']);
    $email = $_SESSION['verify_email'];

    // Verify the code
    $sql = "SELECT id FROM users WHERE email = ? AND verification_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update user as verified
        $update_sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("s", $email);
        
        if ($update_stmt->execute()) {
            unset($_SESSION['verify_email']);
            $_SESSION['success'] = "Email verified successfully! You can now login.";
            header('Location: ../Pages/LoginPage.php');
        } else {
            $_SESSION['error'] = "Verification failed. Please try again.";
            header('Location: verify-code.php');
        }
    } else {
        $_SESSION['error'] = "Invalid verification code.";
        header('Location: ../Pages/verify-code.php');
    }
} else {
    $_SESSION['error'] = "Please enter the verification code.";
    header('Location: ../Pages/verify-code.php');
} 