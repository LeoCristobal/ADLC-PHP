<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../email/mail_helper.php';

// Handle Login
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['is_verified']) {
                // Store all necessary user data in session
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: ../Pages/Dashboard.php');
                exit();
            } else {
                $_SESSION['error'] = "Please verify your email first.";
                header('Location: ../Pages/LoginPage.php');
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid credentials.";
            header('Location: ../Pages/LoginPage.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid credentials.";
        header('Location: ../Pages/LoginPage.php');
        exit();
    }
}

// Handle Signup
if (isset($_POST['signup'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header('Location: ../Pages/SignupPage.php');
        exit();
    }

    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already exists.";
        header('Location: ../Pages/SignupPage.php');
        exit();
    }

    $verification_code = sprintf('%06d', rand(0, 999999));
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password, verification_token) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $verification_code);

    if ($stmt->execute()) {
        $email_body = "
            <h2>Email Verification</h2>
            <p>Your verification code is: <strong>$verification_code</strong></p>
            <p>Please enter this code to verify your email address.</p>
        ";

        if (sendMail($email, "Email Verification Code", $email_body)) {
            $_SESSION['verify_email'] = $email;
            header('Location: ../components/verify-code.php');
            exit();
        } else {
            $_SESSION['error'] = "Failed to send verification email. Please try again.";
            header('Location: ../Pages/SignupPage.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header('Location: ../Pages/SignupPage.php');
        exit();
    }

   
}

// Handle Forgot Password
if (isset($_POST['forgot_password'])) {
    $email = $conn->real_escape_string($_POST['email']);
    
    try {
        // Drop existing columns if they exist (to avoid conflicts)
        $conn->query("ALTER TABLE users DROP COLUMN IF EXISTS reset_token");
        $conn->query("ALTER TABLE users DROP COLUMN IF EXISTS reset_token_expires");
        
        // Add the columns fresh
        $conn->query("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
        $conn->query("ALTER TABLE users ADD COLUMN reset_token_expires DATETIME DEFAULT NULL");
        
        // Now proceed with the password reset logic
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $reset_token = bin2hex(random_bytes(32));
            
            // Update user with reset token
            $update_sql = "UPDATE users 
                          SET reset_token = ?,
                              reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                          WHERE email = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $reset_token, $email);
            
            if ($update_stmt->execute()) {
                $reset_link = "http://localhost/ADLC/components/reset-password.php?token=" . $reset_token;
                $email_body = "
                    <h2>Password Reset</h2>
                    <p>Please click the link below to reset your password:</p>
                    <p><a href='$reset_link'>Reset Password</a></p>
                    <p>This link will expire in 1 hour.</p>
                ";

                if (sendMail($email, "Password Reset Request", $email_body)) {
                    $_SESSION['success'] = "Password reset link has been sent to your email.";
                    header('Location: ../components/forgot-password.php');
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to send reset email. Please try again.";
                    header('Location: ../components/forgot-password.php');
                    exit();
                }
            } else {
                throw new Exception("Failed to update reset token");
            }
        } else {
            $_SESSION['error'] = "Email not found.";
            header('Location: ../components/forgot-password.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
        header('Location: ../components/forgot-password.php');
        exit();
    }
}

// Handle Reset Password
if (isset($_POST['reset_password'])) {
    try {
        $token = $_POST['token'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate passwords match
        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: ../components/reset-password.php?token=" . $token);
            exit();
        }

        // Check if token is valid and not expired
        $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update password and remove reset token
            $update_sql = "UPDATE users 
                          SET password = ?,
                              reset_token = NULL,
                              reset_token_expires = NULL
                          WHERE reset_token = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $hashed_password, $token);

            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Password has been reset successfully. You can now login.";
                header('Location: ../Pages/LoginPage.php');
                exit();
            } else {
                throw new Exception("Failed to update password");
            }
        } else {
            $_SESSION['error'] = "Invalid or expired reset link.";
            header('Location: ../components/forgot-password.php');
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
        header('Location: ../components/forgot-password.php');
        exit();
    }
} 