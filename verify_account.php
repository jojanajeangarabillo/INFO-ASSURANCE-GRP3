<?php
session_start();
require 'admin/db.connect.php';

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

if (empty($token) || empty($email)) {
    die("Invalid verification link.");
}

$stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? AND verification_token = ? AND token_expiry > NOW()");
$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Token is valid, activate account
    $update_stmt = $conn->prepare("UPDATE user SET is_activated = 1, verification_token = NULL, token_expiry = NULL WHERE email = ?");
    $update_stmt->bind_param("s", $email);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Account verified successfully! You can now log in.";
        header("Location: login.php");
        exit;
    } else {
        die("Activation failed. Please try again later.");
    }
} else {
    die("Invalid or expired verification link.");
}
?>