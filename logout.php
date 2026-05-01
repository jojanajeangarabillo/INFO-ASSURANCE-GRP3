<?php
session_start();
require 'admin/db.connect.php';

// Update logout time if we have a current login id
if (isset($_SESSION['current_login_id'])) {
    $stmt = $conn->prepare("UPDATE login_history SET logout_time = NOW() WHERE login_id = ?");
    $stmt->bind_param("i", $_SESSION['current_login_id']);
    $stmt->execute();
}

session_unset();
session_destroy();
header("Location: login.php");
exit;
?>
