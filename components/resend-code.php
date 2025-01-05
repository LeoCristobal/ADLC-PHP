<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';
require_once '../email/mail_helper.php';

if (isset($_SESSION['verify_email'])) {
    $email = $_SESSION['verify_email'];
    $verification_code = sprintf('%06d', rand(0, 999999)); // Generates 6-digit code

    // Update verification token
    $sql = "UPDATE users SET verification_token = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $verification_code, $email);

    if ($stmt->execute()) {
        $email_body = "
            <h2>Email Verification</h2>
            <p>Your verification code is: <strong>$verification_code</strong></p>
            <p>Please enter this code to verify your email address.</p>
        ";

        if (sendMail($email, "Email Verification Code", $email_body)) {
            $_SESSION['success'] = "New verification code has been sent to your email.";
        } else {
            $_SESSION['error'] = "Failed to send verification code. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Failed to generate new verification code. Please try again.";
    }
} else {
    $_SESSION['error'] = "Invalid session. Please try signing up again.";
}

header('Location: verify-code.php'); 