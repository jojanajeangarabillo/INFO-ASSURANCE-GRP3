<?php
require_once 'auth.php';
require_roles([3, 4]);

require_once __DIR__ . '/admin/db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conversation_id = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;
    $message_text = isset($_POST['message_text']) ? trim($_POST['message_text']) : '';

    if ($conversation_id > 0 && !empty($message_text)) {
        $seller_stmt = $conn->prepare("SELECT seller_id FROM seller WHERE user_id = ?");
        $seller_stmt->bind_param("i", $user_id);
        $seller_stmt->execute();
        $seller_result = $seller_stmt->get_result();
        $seller = $seller_result->fetch_assoc();
        $seller_id = $seller['seller_id'] ?? 0;
        $seller_stmt->close();

        $verify_stmt = $conn->prepare("SELECT conversation_id FROM conversation WHERE conversation_id = ? AND seller_id = ?");
        $verify_stmt->bind_param("ii", $conversation_id, $seller_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        $exists = $verify_result->fetch_assoc();
        $verify_stmt->close();

        if ($exists) {
            $insert_stmt = $conn->prepare("INSERT INTO message (conversation_id, sender_user_id, message_body, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)");
            $insert_stmt->bind_param("iis", $conversation_id, $user_id, $message_text);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
    }
}

header("Location: seller_chat.php?conversation_id=" . $conversation_id);
exit;