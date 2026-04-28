<?php
require_once 'auth.php';
require_roles([2, 4]);
require_once 'admin/db.connect.php';
require_once 'Controllers/Paymentcontroller.php';

$customerId = (int) $_SESSION['user_id'];

// 1. Fetch Cart Items
$cartStmt = $conn->prepare("
    SELECT 
        ci.cart_item_id,
        ci.variant_id,
        ci.quantity,
        pv.price,
        p.product_id,
        p.name as product_name,
        p.seller_id
    FROM cart_item ci
    INNER JOIN cart c ON ci.cart_id = c.cart_id
    INNER JOIN product_variant pv ON ci.variant_id = pv.variant_id
    INNER JOIN product p ON pv.product_id = p.product_id
    WHERE c.user_id = ? AND p.status = 'active'
");
$cartStmt->bind_param("i", $customerId);
$cartStmt->execute();
$cartItems = $cartStmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cartItems)) {
    header("Location: customer_cart.php");
    exit;
}

// 2. Calculate Total and Prepare Items for PayMongo
$totalAmount = 0;
$paymongoItems = [];
foreach ($cartItems as $item) {
    $itemTotal = $item['price'] * $item['quantity'];
    $totalAmount += $itemTotal;
    $paymongoItems[] = [
        'name' => $item['product_name'],
        'quantity' => $item['quantity'],
        'price' => $item['price']
    ];
}

// 3. Get Customer Info for Shipping
$custStmt = $conn->prepare("SELECT * FROM customer WHERE user_id = ?");
$custStmt->bind_param("i", $customerId);
$custStmt->execute();
$customerInfo = $custStmt->get_result()->fetch_assoc();

$fullName = $customerInfo['full_name'] ?? 'Guest Customer';
$phone = $customerInfo['contact_number'] ?? '';
$address = $customerInfo['address_line'] ?? '';
$city = $customerInfo['city'] ?? '';
$region = $customerInfo['region'] ?? '';
$postalCode = $customerInfo['postal_code'] ?? '';

// 4. Create Order in Database
$orderNumber = 'ORD-' . strtoupper(uniqid());
$conn->begin_transaction();

try {
    $orderStmt = $conn->prepare("
        INSERT INTO orders (
            order_number, customer_id, order_status, payment_status, 
            subtotal_amount, total_amount, 
            shipping_full_name, shipping_phone, shipping_address_line, 
            shipping_city, shipping_region, shipping_postal_code
        ) VALUES (?, ?, 'pending', 'unpaid', ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $orderStmt->bind_param(
        "siddssssss", 
        $orderNumber, $customerId, $totalAmount, $totalAmount,
        $fullName, $phone, $address, $city, $region, $postalCode
    );
    $orderStmt->execute();
    $orderId = $conn->insert_id;

    // Insert Order Items
    $itemStmt = $conn->prepare("
        INSERT INTO order_item (
            order_id, product_id, variant_id, seller_id, 
            quantity, unit_price, line_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?) 
    ");
    foreach ($cartItems as $item) {
        $lineTotal = $item['price'] * $item['quantity'];
        $itemStmt->bind_param(
            "iiiiddd", 
            $orderId, $item['product_id'], $item['variant_id'], $item['seller_id'],
            $item['quantity'], $item['price'], $lineTotal
        );
        $itemStmt->execute();
    }

    // 5. Initialize PayMongo Payment
    $paymentController = new PaymentController();
    $checkoutUrl = $paymentController->createCheckoutSession($totalAmount, $paymongoItems, "Payment for Order #$orderNumber");

    if ($checkoutUrl) {
        // Store order info in session for success page
        $_SESSION['last_order_id'] = $orderId;
        $_SESSION['last_order_number'] = $orderNumber;
        
        $conn->commit();
        
        // Clear cart (Optional: you might want to wait until payment is successful)
        // But usually, checkout redirect means they are committed.
        // We'll clear it on success page instead to be safe.
        
        header("Location: " . $checkoutUrl);
        exit;
    } else {
        throw new Exception("Failed to create PayMongo checkout session.");
    }

} catch (Exception $e) {
    $conn->rollback();
    die("Error during checkout: " . $e->getMessage());
}
