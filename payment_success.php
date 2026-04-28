<?php
require_once 'auth.php';
require_roles([2, 4]);
require_once 'admin/db.connect.php';

$customerId = (int) $_SESSION['user_id'];
$orderId = $_SESSION['last_order_id'] ?? null;
$orderNumber = $_SESSION['last_order_number'] ?? null;

if (!$orderId) {
    header("Location: customer_home.php");
    exit;
}

// 1. Update Order Status
$updateStmt = $conn->prepare("UPDATE orders SET order_status = 'pending', payment_status = 'paid' WHERE order_id = ? AND customer_id = ?");
$updateStmt->bind_param("ii", $orderId, $customerId);
$updateStmt->execute();

// 2. Also insert into payment table
$paymentStmt = $conn->prepare("INSERT INTO payment (order_id, payment_method, payment_status, amount) SELECT order_id, 'GCash', 'paid', total_amount FROM orders WHERE order_id = ?");
$paymentStmt->bind_param("i", $orderId);
$paymentStmt->execute();

// 3. Clear Cart
$cartIdStmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$cartIdStmt->bind_param("i", $customerId);
$cartIdStmt->execute();
$cartResult = $cartIdStmt->get_result()->fetch_assoc();
if ($cartResult) {
    $cartId = $cartResult['cart_id'];
    $clearStmt = $conn->prepare("DELETE FROM cart_item WHERE cart_id = ?");
    $clearStmt->bind_param("i", $cartId);
    $clearStmt->execute();
}

// Clear session variables
unset($_SESSION['last_order_id']);
unset($_SESSION['last_order_number']);
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful - J3RS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f5f1ee; font-family: 'Inter', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .success-card { background: white; padding: 3rem; border-radius: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); text-align: center; max-width: 500px; width: 100%; }
        .success-icon { font-size: 5rem; color: #2b5e2f; margin-bottom: 1.5rem; }
        .btn-home { background: #2b5e2f; color: white; border-radius: 60px; padding: 12px 30px; border: none; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.2s; }
        .btn-home:hover { background: #6e0f25; color: white; transform: scale(0.98); }
    </style>
</head>
<body>
    <div class="success-card">
        <i class="bi bi-check-circle-fill success-icon"></i>
        <h2 class="fw-bold mb-3">Payment Successful!</h2>
        <p class="text-muted mb-4">Your order <strong>#<?php echo htmlspecialchars($orderNumber); ?></strong> has been placed successfully and is now being processed.</p>
        <div class="d-grid gap-2">
            <a href="customer_orders.php" class="btn-home">View My Orders</a>
            <a href="customer_home.php" class="text-muted text-decoration-none small mt-2">Back to Home</a>
        </div>
    </div>
</body>
</html>
