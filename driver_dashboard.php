<?php
// driver_dashboard.php
require_once 'auth.php';
require_roles([6]); // Driver role only

require_once __DIR__ . '/admin/db.connect.php';

$user_id = $_SESSION['user_id'];

// Fetch driver info
$driverQuery = $conn->prepare("
    SELECT d.*, u.first_name, u.last_name, u.email, u.user_id as user_account_id
    FROM driver d 
    JOIN user u ON d.user_id = u.user_id 
    WHERE d.user_id = ?
");
$driverQuery->bind_param("i", $user_id);
$driverQuery->execute();
$driver = $driverQuery->get_result()->fetch_assoc();
$driverQuery->close();

if (!$driver) {
    die("Driver profile not found.");
}

$driver_id = $driver['driver_id'];
$driver_user_id = $driver['user_account_id'];

// Function to send notification (in-app)
function sendNotification($conn, $user_id, $title, $message, $notification_type, $reference_id = null) {
    $insert_notif = $conn->prepare("
        INSERT INTO notification (user_id, title, message, notification_type, reference_id, is_read, created_at) 
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");
    $insert_notif->bind_param("issii", $user_id, $title, $message, $notification_type, $reference_id);
    return $insert_notif->execute();
}

// Fetch unread notifications for driver
$unreadNotifQuery = $conn->prepare("
    SELECT COUNT(*) as unread_count 
    FROM notification 
    WHERE user_id = ? AND is_read = 0
");
$unreadNotifQuery->bind_param("i", $driver_user_id);
$unreadNotifQuery->execute();
$unreadCount = $unreadNotifQuery->get_result()->fetch_assoc()['unread_count'];
$unreadNotifQuery->close();

// Fetch recent notifications
$recentNotifQuery = $conn->prepare("
    SELECT * FROM notification 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$recentNotifQuery->bind_param("i", $driver_user_id);
$recentNotifQuery->execute();
$recentNotifications = $recentNotifQuery->get_result();
$recentNotifQuery->close();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Mark notification as read
    if ($action === 'mark_notification_read') {
        $notif_id = $_POST['notification_id'];
        $markStmt = $conn->prepare("UPDATE notification SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        $markStmt->bind_param("ii", $notif_id, $driver_user_id);
        $markStmt->execute();
        $markStmt->close();
        header("Location: driver_dashboard.php");
        exit;
    }
    
    // Mark all notifications as read
    if ($action === 'mark_all_read') {
        $markStmt = $conn->prepare("UPDATE notification SET is_read = 1 WHERE user_id = ?");
        $markStmt->bind_param("i", $driver_user_id);
        $markStmt->execute();
        $markStmt->close();
        header("Location: driver_dashboard.php");
        exit;
    }
    
    if ($action === 'accept_assignment') {
        $assignment_id = $_POST['assignment_id'];
        
        // Get order_id first
        $order_stmt = $conn->prepare("SELECT order_id FROM driver_assignment WHERE assignment_id = ?");
        $order_stmt->bind_param("i", $assignment_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result()->fetch_assoc();
        $order_id = $order_result['order_id'];
        $order_stmt->close();
        
        // Get order details for notification
        $orderDetailsStmt = $conn->prepare("
            SELECT o.order_number, o.customer_id, u.username as customer_name
            FROM orders o
            JOIN user u ON o.customer_id = u.user_id
            WHERE o.order_id = ?
        ");
        $orderDetailsStmt->bind_param("i", $order_id);
        $orderDetailsStmt->execute();
        $orderInfo = $orderDetailsStmt->get_result()->fetch_assoc();
        $orderDetailsStmt->close();
        
        // Update driver assignment status
        $stmt = $conn->prepare("
            UPDATE driver_assignment 
            SET status = 'accepted', accepted_at = NOW() 
            WHERE assignment_id = ? AND driver_id = ?
        ");
        $stmt->bind_param("ii", $assignment_id, $driver_id);
        $stmt->execute();
        $stmt->close();
        
        // Update order status to 'shipped' (in transit)
        $update_order = $conn->prepare("UPDATE orders SET order_status = 'shipped', updated_at = NOW() WHERE order_id = ?");
        $update_order->bind_param("i", $order_id);
        $update_order->execute();
        $update_order->close();
        
        // Update delivery tracking
        $update_tracking = $conn->prepare("
            UPDATE delivery_tracking 
            SET status = 'picked_up', updated_by_user_id = ? 
            WHERE order_id = ?
        ");
        $update_tracking->bind_param("ii", $driver_user_id, $order_id);
        $update_tracking->execute();
        $update_tracking->close();
        
        // Send IN-APP notification to driver (confirmation)
        $driver_title = "Delivery Accepted - Order #{$orderInfo['order_number']}";
        $driver_message = "You have successfully accepted delivery for Order #{$orderInfo['order_number']} to {$orderInfo['customer_name']}. Please proceed to pick up the package.";
        sendNotification($conn, $driver_user_id, $driver_title, $driver_message, 'driver_assignment', $assignment_id);
        
        // Send IN-APP notification to customer
        $customer_title = "Order Status Update - Order #{$orderInfo['order_number']}";
        $customer_message = "Your order #{$orderInfo['order_number']} has been accepted by driver " . htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) . " and is now out for delivery!";
        sendNotification($conn, $orderInfo['customer_id'], $customer_title, $customer_message, 'order_update', $order_id);
        
        header("Location: driver_dashboard.php");
        exit;
    }
    
    if ($action === 'update_status') {
        $assignment_id = $_POST['assignment_id'];
        $status = $_POST['status'];
        
        // Get order_id first
        $order_stmt = $conn->prepare("SELECT order_id FROM driver_assignment WHERE assignment_id = ?");
        $order_stmt->bind_param("i", $assignment_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result()->fetch_assoc();
        $order_id = $order_result['order_id'];
        $order_stmt->close();
        
        // Get order details
        $orderDetailsStmt = $conn->prepare("SELECT order_number FROM orders WHERE order_id = ?");
        $orderDetailsStmt->bind_param("i", $order_id);
        $orderDetailsStmt->execute();
        $orderNumber = $orderDetailsStmt->get_result()->fetch_assoc()['order_number'];
        $orderDetailsStmt->close();
        
        // Update driver assignment status
        $stmt = $conn->prepare("
            UPDATE driver_assignment 
            SET status = ? 
            WHERE assignment_id = ? AND driver_id = ?
        ");
        $stmt->bind_param("sii", $status, $assignment_id, $driver_id);
        $stmt->execute();
        $stmt->close();
        
        // Update order status based on driver status
        $order_status = '';
        $delivery_status = '';
        $status_message = '';
        
        if ($status == 'picked_up') {
            $order_status = 'shipped';
            $delivery_status = 'picked_up';
            $status_message = "picked up";
        } elseif ($status == 'in_transit') {
            $order_status = 'out_for_delivery';
            $delivery_status = 'in_transit';
            $status_message = "in transit";
        }
        
        if ($order_status) {
            $update_order = $conn->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE order_id = ?");
            $update_order->bind_param("si", $order_status, $order_id);
            $update_order->execute();
            $update_order->close();
            
            $update_tracking = $conn->prepare("
                UPDATE delivery_tracking 
                SET status = ?, updated_by_user_id = ? 
                WHERE order_id = ?
            ");
            $update_tracking->bind_param("sii", $delivery_status, $driver_user_id, $order_id);
            $update_tracking->execute();
            $update_tracking->close();
            
            // Send IN-APP notification to driver (status update confirmation)
            $driver_title = "Status Updated - Order #{$orderNumber}";
            $driver_message = "Order #{$orderNumber} has been marked as {$status_message}.";
            sendNotification($conn, $driver_user_id, $driver_title, $driver_message, 'driver_assignment', $assignment_id);
        }
        
        header("Location: driver_dashboard.php");
        exit;
    }
    
    if ($action === 'complete_delivery') {
        $assignment_id = $_POST['assignment_id'];
        $notes = $_POST['notes'] ?? '';
        
        // Get order_id first
        $order_stmt = $conn->prepare("SELECT order_id FROM driver_assignment WHERE assignment_id = ?");
        $order_stmt->bind_param("i", $assignment_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result()->fetch_assoc();
        $order_id = $order_result['order_id'];
        $order_stmt->close();
        
        // Get order details
        $orderDetailsStmt = $conn->prepare("
            SELECT o.order_number, o.customer_id, u.username as customer_name, u.role_id, u.email as customer_email
            FROM orders o
            JOIN user u ON o.customer_id = u.user_id
            WHERE o.order_id = ?
        ");
        $orderDetailsStmt->bind_param("i", $order_id);
        $orderDetailsStmt->execute();
        $orderInfo = $orderDetailsStmt->get_result()->fetch_assoc();
        $orderDetailsStmt->close();
        
        // Update driver assignment - mark as delivered
        $stmt = $conn->prepare("
            UPDATE driver_assignment 
            SET status = 'delivered', delivered_at = NOW(), notes = ? 
            WHERE assignment_id = ? AND driver_id = ?
        ");
        $stmt->bind_param("sii", $notes, $assignment_id, $driver_id);
        $stmt->execute();
        $stmt->close();
        
        // Update order status to 'delivered'
        $update_order = $conn->prepare("UPDATE orders SET order_status = 'delivered', updated_at = NOW() WHERE order_id = ?");
        $update_order->bind_param("i", $order_id);
        $update_order->execute();
        $update_order->close();
        
        // Update delivery tracking
        $update_tracking = $conn->prepare("
            UPDATE delivery_tracking 
            SET status = 'delivered', updated_by_user_id = ? 
            WHERE order_id = ?
        ");
        $update_tracking->bind_param("ii", $driver_user_id, $order_id);
        $update_tracking->execute();
        $update_tracking->close();
        
        // Update driver's total deliveries count
        $update_driver = $conn->prepare("UPDATE driver SET total_deliveries = total_deliveries + 1 WHERE driver_id = ?");
        $update_driver->bind_param("i", $driver_id);
        $update_driver->execute();
        $update_driver->close();
        
        // Send IN-APP notification to driver (completion confirmation)
        $driver_title = "Delivery Completed! - Order #{$orderInfo['order_number']}";
        $driver_message = "Congratulations! You have successfully delivered Order #{$orderInfo['order_number']} to {$orderInfo['customer_name']}. Great job!";
        sendNotification($conn, $driver_user_id, $driver_title, $driver_message, 'driver_assignment', $assignment_id);
        
        // Send IN-APP notification to customer
        $customer_title = "Order Delivered - Order #{$orderInfo['order_number']}";
        $customer_message = "Your order #{$orderInfo['order_number']} has been successfully delivered by " . htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) . ". Thank you for shopping with us!";
        sendNotification($conn, $orderInfo['customer_id'], $customer_title, $customer_message, 'order_update', $order_id);
        
        // If dual role, send additional notification
        if ($orderInfo['role_id'] == 4) {
            $dual_title = "Order Delivery Confirmation - Order #{$orderInfo['order_number']}";
            $dual_message = "Your order #{$orderInfo['order_number']} has been delivered. Thank you for your purchase!";
            sendNotification($conn, $orderInfo['customer_id'], $dual_title, $dual_message, 'order_update', $order_id);
        }
        
        header("Location: driver_dashboard.php");
        exit;
    }
}

// Fetch active assignments with order status sync
$assignmentsQuery = $conn->prepare("
    SELECT da.*, o.order_id, o.order_number, o.total_amount, o.created_at, o.order_status,
           o.shipping_full_name as customer_name, 
           o.shipping_address_line, o.shipping_city, o.shipping_region, 
           o.shipping_phone as contact_number
    FROM driver_assignment da
    JOIN orders o ON da.order_id = o.order_id
    WHERE da.driver_id = ? AND da.status NOT IN ('delivered', 'cancelled')
    ORDER BY FIELD(da.status, 'pending', 'accepted', 'picked_up', 'in_transit'), da.assigned_at ASC
");
$assignmentsQuery->bind_param("i", $driver_id);
$assignmentsQuery->execute();
$activeAssignments = $assignmentsQuery->get_result();
$assignmentsQuery->close();

// Fetch delivery history
$historyQuery = $conn->prepare("
    SELECT da.*, o.order_number, o.total_amount, o.shipping_full_name as customer_name
    FROM driver_assignment da
    JOIN orders o ON da.order_id = o.order_id
    WHERE da.driver_id = ? AND da.status = 'delivered'
    ORDER BY da.delivered_at DESC
    LIMIT 20
");
$historyQuery->bind_param("i", $driver_id);
$historyQuery->execute();
$deliveryHistory = $historyQuery->get_result();
$historyQuery->close();

// Get statistics
$statsQuery = $conn->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'delivered' THEN 1 END) as total_delivered,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN status = 'accepted' THEN 1 END) as accepted,
        COUNT(CASE WHEN status = 'in_transit' THEN 1 END) as in_transit
    FROM driver_assignment
    WHERE driver_id = ?
");
$statsQuery->bind_param("i", $driver_id);
$statsQuery->execute();
$stats = $statsQuery->get_result()->fetch_assoc();
$statsQuery->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Driver Dashboard - J3RS Logistics</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { background: #fdf2f6; font-family: 'Inter', Arial, sans-serif; }

.driver-header {
    background: linear-gradient(135deg, #610C27 0%, #8a1423 100%);
    color: white;
    padding: 30px;
    border-radius: 0 0 30px 30px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    height: 100%;
    transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-3px); }
.stat-number { font-size: 36px; font-weight: bold; color: #610C27; }
.stat-label { color: #666; font-size: 14px; margin-top: 5px; }

.assignment-card {
    background: white;
    border-radius: 16px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}
.assignment-card:hover { transform: translateY(-2px); }

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-pending { background: #fef3c7; color: #d97706; }
.status-accepted { background: #dbeafe; color: #2563eb; }
.status-picked_up { background: #e0e7ff; color: #4338ca; }
.status-in_transit { background: #c7d2fe; color: #3730a3; }
.status-delivered { background: #d1fae5; color: #059669; }

.btn-action {
    background: #610C27;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: 0.2s;
}
.btn-action:hover { background: #8a1423; color: white; }
.btn-outline-action {
    background: white;
    color: #610C27;
    border: 2px solid #610C27;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 500;
}

.container-custom { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.tracking-timeline {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    margin-top: 15px;
}
.timeline-step {
    display: inline-block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    border-radius: 50%;
    background: #ddd;
    margin-right: 10px;
}
.timeline-step.completed { background: #28a745; color: white; }
.timeline-step.active { background: #ffc107; color: #000; }

/* Notification Bell Styles */
.notification-bell {
    position: relative;
    cursor: pointer;
    margin-right: 20px;
}
.notification-bell .badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 4px 6px;
    font-size: 10px;
}
.notification-dropdown {
    position: absolute;
    top: 50px;
    right: 20px;
    width: 350px;
    max-height: 400px;
    overflow-y: auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
}
.notification-dropdown.show {
    display: block;
}
.notification-item {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background 0.2s;
}
.notification-item:hover {
    background: #f8f9fa;
}
.notification-item.unread {
    background: #f0f8ff;
    border-left: 3px solid #0d6efd;
}
.notification-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
}
.notification-message {
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
}
.notification-time {
    font-size: 10px;
    color: #999;
}
.mark-read-btn {
    font-size: 10px;
    color: #0d6efd;
    text-decoration: none;
}
</style>
</head>
<body>

<div class="driver-header">
    <div class="container-custom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0">🚚 <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></h1>
                <p class="mb-0 opacity-75">Driver ID: <?php echo $driver['driver_id']; ?> | Vehicle: <?php echo htmlspecialchars($driver['vehicle_assigned'] ?: 'Not assigned'); ?></p>
            </div>
            <div class="d-flex align-items-center">
                <!-- Notification Bell -->
                <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="fas fa-bell fs-4"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </div>
                <a href="logout.php" class="btn btn-light ms-3">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Notification Dropdown -->
<div id="notificationDropdown" class="notification-dropdown">
    <div class="p-2 border-bottom d-flex justify-content-between align-items-center">
        <strong><i class="fas fa-bell"></i> Notifications</strong>
        <?php if ($unreadCount > 0): ?>
            <form method="POST" class="m-0">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn btn-sm btn-link mark-read-btn">Mark all as read</button>
            </form>
        <?php endif; ?>
    </div>
    <div class="notification-list">
        <?php if ($recentNotifications->num_rows == 0): ?>
            <div class="text-center p-4 text-muted">
                <i class="fas fa-bell-slash"></i>
                <p class="mt-2 mb-0">No notifications yet</p>
            </div>
        <?php else: 
            while($notif = $recentNotifications->fetch_assoc()): ?>
            <div class="notification-item <?php echo $notif['is_read'] == 0 ? 'unread' : ''; ?>">
                <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="notification-time">
                        <?php echo date('M d, H:i', strtotime($notif['created_at'])); ?>
                    </div>
                    <?php if ($notif['is_read'] == 0): ?>
                        <form method="POST" class="m-0">
                            <input type="hidden" name="action" value="mark_notification_read">
                            <input type="hidden" name="notification_id" value="<?php echo $notif['notification_id']; ?>">
                            <button type="submit" class="btn btn-sm btn-link mark-read-btn">Mark as read</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; endif; ?>
    </div>
</div>

<div class="container-custom pb-5">
    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_delivered'] ?? 0; ?></div>
                <div class="stat-label"><i class="fas fa-check-circle text-success"></i> Total Delivered</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending'] ?? 0; ?></div>
                <div class="stat-label"><i class="fas fa-clock text-warning"></i> Pending</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['accepted'] ?? 0; ?></div>
                <div class="stat-label"><i class="fas fa-check text-info"></i> Accepted</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['in_transit'] ?? 0; ?></div>
                <div class="stat-label"><i class="fas fa-truck text-primary"></i> In Transit</div>
            </div>
        </div>
    </div>

    <!-- Active Deliveries -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0"><i class="fas fa-tasks"></i> Active Deliveries</h3>
        <span class="badge bg-primary"><?php echo $activeAssignments->num_rows; ?> active</span>
    </div>

    <?php if($activeAssignments->num_rows == 0): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> No active deliveries. You're all caught up!
        </div>
    <?php else: 
        while($assignment = $activeAssignments->fetch_assoc()): ?>
        <div class="assignment-card">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">Order #<?php echo $assignment['order_number']; ?></h5>
                        <span class="status-badge status-<?php echo $assignment['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $assignment['status'])); ?>
                        </span>
                        <span class="badge bg-secondary ms-2">
                            Order Status: <?php echo ucfirst($assignment['order_status']); ?>
                        </span>
                    </div>
                    <div class="text-end">
                        <strong>₱<?php echo number_format($assignment['total_amount'], 2); ?></strong>
                        <br>
                        <small class="text-muted">Ordered: <?php echo date('M d, Y', strtotime($assignment['created_at'])); ?></small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><i class="fas fa-user"></i> <strong>Customer:</strong> <?php echo htmlspecialchars($assignment['customer_name']); ?></p>
                        <p><i class="fas fa-phone"></i> <strong>Contact:</strong> <?php echo htmlspecialchars($assignment['contact_number']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Delivery Address:</strong></p>
                        <p class="text-muted"><?php echo htmlspecialchars($assignment['shipping_address_line'] . ', ' . $assignment['shipping_city'] . ', ' . $assignment['shipping_region']); ?></p>
                    </div>
                </div>
                
                <!-- Tracking Timeline -->
                <div class="tracking-timeline">
                    <strong><i class="fas fa-timeline"></i> Delivery Progress:</strong>
                    <div class="mt-2">
                        <span class="timeline-step <?php echo in_array($assignment['status'], ['accepted', 'picked_up', 'in_transit', 'delivered']) ? 'completed' : ($assignment['status'] == 'pending' ? 'active' : ''); ?>">1</span>
                        Accept
                        <i class="fas fa-arrow-right mx-2"></i>
                        <span class="timeline-step <?php echo in_array($assignment['status'], ['picked_up', 'in_transit', 'delivered']) ? 'completed' : ($assignment['status'] == 'accepted' ? 'active' : ''); ?>">2</span>
                        Pick Up
                        <i class="fas fa-arrow-right mx-2"></i>
                        <span class="timeline-step <?php echo in_array($assignment['status'], ['in_transit', 'delivered']) ? 'completed' : ($assignment['status'] == 'picked_up' ? 'active' : ''); ?>">3</span>
                        Deliver
                    </div>
                </div>
                
                <div class="action-buttons d-flex gap-2 mt-3">
                    <?php if($assignment['status'] == 'pending'): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="accept_assignment">
                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                            <button type="submit" class="btn-action">
                                <i class="fas fa-check-circle"></i> Accept & Start Delivery
                            </button>
                        </form>
                        <button class="btn-outline-action" onclick="showDeclineModal(<?php echo $assignment['assignment_id']; ?>)">
                            <i class="fas fa-times-circle"></i> Decline
                        </button>
                    <?php elseif($assignment['status'] == 'accepted'): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                            <input type="hidden" name="status" value="picked_up">
                            <button type="submit" class="btn-action">
                                <i class="fas fa-box"></i> Mark as Picked Up
                            </button>
                        </form>
                    <?php elseif($assignment['status'] == 'picked_up'): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                            <input type="hidden" name="status" value="in_transit">
                            <button type="submit" class="btn-action">
                                <i class="fas fa-truck"></i> Start Delivery
                            </button>
                        </form>
                    <?php elseif($assignment['status'] == 'in_transit'): ?>
                        <button class="btn-action" onclick="showCompleteModal(<?php echo $assignment['assignment_id']; ?>)">
                            <i class="fas fa-check-circle"></i> Mark as Delivered
                        </button>
                    <?php endif; ?>
                    
                    <button class="btn-outline-action" onclick="window.open('https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($assignment['shipping_address_line'] . ', ' . $assignment['shipping_city']); ?>')">
                        <i class="fas fa-directions"></i> Get Directions
                    </button>
                </div>
            </div>
        </div>
        <?php endwhile; 
    endif; ?>

    <!-- Delivery History -->
    <?php if($deliveryHistory->num_rows > 0): ?>
    <div class="mt-5">
        <h3 class="fw-bold mb-3"><i class="fas fa-history"></i> Recent Deliveries</h3>
        <div class="table-card" style="background: white; border-radius: 16px; overflow: hidden;">
            <table class="table table-hover mb-0">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Delivered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($history = $deliveryHistory->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $history['order_number']; ?></td>
                        <td><?php echo htmlspecialchars($history['customer_name']); ?></td>
                        <td>₱<?php echo number_format($history['total_amount'], 2); ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($history['delivered_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Complete Delivery Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #610C27; color: white;">
                <h5 class="modal-title">Complete Delivery</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="complete_delivery" id="completeAction">
                    <input type="hidden" name="assignment_id" id="completeAssignmentId">
                    <div class="mb-3">
                        <label class="form-label">Delivery Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any issues or special notes about this delivery..."></textarea>
                    </div>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Confirm that the order has been delivered to the customer.
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> The customer will receive a notification about the delivery.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-action">Confirm Delivery</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showCompleteModal(assignmentId) {
    document.getElementById('completeAssignmentId').value = assignmentId;
    new bootstrap.Modal(document.getElementById('completeModal')).show();
}

function showDeclineModal(assignmentId) {
    if (confirm("Are you sure you want to decline this delivery? This will notify the logistics manager.")) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="assignment_id" value="${assignmentId}">
            <input type="hidden" name="status" value="cancelled">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Notification dropdown toggle
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('show');
}

// Close notification dropdown when clicking outside
document.addEventListener('click', function(event) {
    const bell = document.querySelector('.notification-bell');
    const dropdown = document.getElementById('notificationDropdown');
    if (!bell.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>
</body>
</html>