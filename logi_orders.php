<?php
require_once 'auth.php';
require_roles([5]); // Only logistics role can access

require_once __DIR__ . '/admin/db.connect.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$user_id = $_SESSION['user_id'] ?? 0;

// Fetch session timeout from database
$query = "SELECT session_timeout_minutes FROM system_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$timeout_minutes = $row ? $row['session_timeout_minutes'] : 30;

// Check session timeout
if (!isset($_SESSION['last_activity'])) {
  $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout_minutes * 60) {
  // Session expired, logout
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
} else {
  // Update last activity
  $_SESSION['last_activity'] = time();
}

$timeout_ms = $timeout_minutes * 60 * 1000;


// Handle bulk status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        header("Location: logi_orders.php");
        exit;
    }
    
    if (isset($_POST['bulk_action']) && isset($_POST['selected_orders'])) {
        $action = $_POST['bulk_action'];
        $selected_orders = $_POST['selected_orders'];
        
        foreach ($selected_orders as $order_id) {
            $order_id = intval($order_id);
            
            // Get current order status
            $status_stmt = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ?");
            $status_stmt->bind_param("i", $order_id);
            $status_stmt->execute();
            $current_status = $status_stmt->get_result()->fetch_assoc()['order_status'];
            $status_stmt->close();
            
            $new_status = '';
            $delivery_status = '';
            
            // Determine new status based on action
            switch ($action) {
                case 'pickup':
                    if ($current_status == 'processing') {
                        $new_status = 'shipped';
                        $delivery_status = 'picked_up';
                    }
                    break;
                case 'out_for_delivery':
                    if ($current_status == 'shipped') {
                        $new_status = 'out_for_delivery';
                        $delivery_status = 'out_for_delivery';
                    }
                    break;
                case 'deliver':
                    if ($current_status == 'out_for_delivery') {
                        $new_status = 'delivered';
                        $delivery_status = 'delivered';
                    }
                    break;
            }
            
            if ($new_status) {
                // Update order status
                $update_order = $conn->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE order_id = ?");
                $update_order->bind_param("si", $new_status, $order_id);
                $update_order->execute();
                $update_order->close();
                
                // Update delivery tracking
                $update_tracking = $conn->prepare("
                    UPDATE delivery_tracking 
                    SET status = ?, updated_by_user_id = ? 
                    WHERE order_id = ?
                ");
                $update_tracking->bind_param("sii", $delivery_status, $user_id, $order_id);
                $update_tracking->execute();
                $update_tracking->close();
                
                log_audit_action('update', 'Logistics Orders', 'Order #' . $order_id . ' status updated to ' . $new_status);
                
                // Send notification to customer
                $order_details_stmt = $conn->prepare("
                    SELECT o.order_number, o.customer_id, u.username, u.role_id
                    FROM orders o
                    JOIN user u ON o.customer_id = u.user_id
                    WHERE o.order_id = ?
                ");
                $order_details_stmt->bind_param("i", $order_id);
                $order_details_stmt->execute();
                $order_details = $order_details_stmt->get_result()->fetch_assoc();
                $order_details_stmt->close();
                
                $customer_title = "Order Status Update - Order #{$order_details['order_number']}";
                $status_messages = [
                    'shipped' => "has been picked up and is now in transit to your location.",
                    'out_for_delivery' => "is out for delivery! Your package will arrive soon.",
                    'delivered' => "has been delivered. Thank you for shopping with us!"
                ];
                $customer_message = "Your order #{$order_details['order_number']} " . $status_messages[$new_status];
                
                $notif_stmt = $conn->prepare("
                    INSERT INTO notification (user_id, title, message, notification_type, reference_id, is_read, created_at) 
                    VALUES (?, ?, ?, 'order_update', ?, 0, NOW())
                ");
                $notif_stmt->bind_param("issi", $order_details['customer_id'], $customer_title, $customer_message, $order_id);
                $notif_stmt->execute();
                $notif_stmt->close();
                
                // If dual role, send additional notification
                if ($order_details['role_id'] == 4) {
                    $dual_title = "Order Update - Order #{$order_details['order_number']}";
                    $dual_message = "Order #{$order_details['order_number']} status has been updated to: " . ucfirst(str_replace('_', ' ', $new_status));
                    $notif_stmt2 = $conn->prepare("
                        INSERT INTO notification (user_id, title, message, notification_type, reference_id, is_read, created_at) 
                        VALUES (?, ?, ?, 'order_update', ?, 0, NOW())
                    ");
                    $notif_stmt2->bind_param("issi", $order_details['customer_id'], $dual_title, $dual_message, $order_id);
                    $notif_stmt2->execute();
                    $notif_stmt2->close();
                }
            }
        }
        
        $success_message = count($selected_orders) . " order(s) updated successfully!";
    }
}

// Get orders for logistics view
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$orders = [];

$orders_query = "
    SELECT DISTINCT o.order_id, o.order_number, o.order_status, o.created_at,
           u.username as customer_name,
           dt.status as delivery_status, dt.created_at as tracking_date,
           CONCAT(COALESCE(logistic_user.first_name, ''), ' ', COALESCE(logistic_user.last_name, '')) as logistic_name
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id
    JOIN user u ON o.customer_id = u.user_id
    LEFT JOIN (
        SELECT order_id, status, created_at, logistic_user_id
        FROM delivery_tracking dt1
        WHERE delivery_tracking_id = (
            SELECT MAX(delivery_tracking_id)
            FROM delivery_tracking dt2
            WHERE dt2.order_id = dt1.order_id
        )
    ) dt ON o.order_id = dt.order_id
    LEFT JOIN user logistic_user ON dt.logistic_user_id = logistic_user.user_id AND logistic_user.role_id = 5
    WHERE o.order_status IN ('processing', 'shipped', 'out_for_delivery', 'delivered')
";

$params = [];
$types = "";

if ($status_filter !== 'all') {
    $orders_query .= " AND o.order_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $orders_query .= " AND (o.order_number LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

$orders_query .= " ORDER BY o.created_at DESC";

$orders_stmt = $conn->prepare($orders_query);
if (!empty($params)) {
    $orders_stmt->bind_param($types, ...$params);
}
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}
$orders_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logistics Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="sidebar.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<script>
    const timeoutMs = <?php echo $timeout_ms; ?>;
    let logoutTimer;

    function resetTimer() {
      clearTimeout(logoutTimer);
      logoutTimer = setTimeout(function() {
        alert("Session expired due to inactivity. You will be logged out.");
        window.location.href = "logout.php";
      }, timeoutMs);
    }

    document.addEventListener("mousemove", resetTimer);
    document.addEventListener("keypress", resetTimer);
    document.addEventListener("click", resetTimer);
    document.addEventListener("scroll", resetTimer);

    resetTimer();
  </script>

<style>
body { margin: 0; font-family: 'Inter', Arial, sans-serif; background: #fdf2f6; }
.main-content { margin-left: 240px; padding: 40px 60px; transition: margin-left 0.3s ease; }
.main-content.full { margin-left: 70px; }
h1 { color: #610C27; margin-bottom: 5px; font-size: 32px; }
.sub-header { color: #777; margin-bottom: 30px; }

.tab-nav { display: flex; gap: 25px; border-bottom: 1px solid #ddd; margin-bottom: 25px; flex-wrap: wrap; }
.tab-nav a { text-decoration: none; color: #888; padding-bottom: 10px; font-size: 14px; font-weight: 600; position: relative; cursor: pointer; }
.tab-nav a.active { color: #610C27; }
.tab-nav a.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; background: #610C27; }

.search-container { background: white; padding: 15px 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px; border: 1px solid #eee; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
.search-container input { flex: 1; border: none; outline: none; font-size: 14px; }
.search-container input:focus { outline: none; }

.table-card { background: white; border-radius: 15px; overflow-x: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
table { width: 100%; border-collapse: collapse; min-width: 800px; }
th { text-align: left; padding: 15px 20px; font-size: 12px; text-transform: uppercase; color: #aaa; background: #fcfcfc; }
td { padding: 18px 20px; border-top: 1px solid #f5f5f5; font-size: 14px; color: #444; }

.badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
.status-processing { background: #fff8e6; color: #ffa000; border: 1px solid #ffecc2; }
.status-shipped { background: #eef4ff; color: #3b82f6; border: 1px solid #dbeafe; }
.status-out_for_delivery { background: #fff0f6; color: #ec4899; border: 1px solid #ffdeeb; }
.status-delivered { background: #ecfdf5; color: #10b981; border: 1px solid #d1fae5; }

.btn-action { background: white; color: #610C27; border: 1.5px solid #610C27; padding: 6px 14px; border-radius: 6px; font-size: 13px; cursor: pointer; transition: 0.2s; font-weight: bold; }
.btn-action:hover:not(:disabled) { background: #610C27; color: white; }
.btn-action:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-bulk { background: #610C27; color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; margin-right: 10px; }
.btn-bulk:hover { background: #8a1423; }

.checkbox-col { width: 40px; text-align: center; }
input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }

.alert-custom { border-radius: 12px; margin-bottom: 20px; padding: 12px 20px; }

.bulk-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

.select-all { margin-left: 20px; }
</style>
</head>

<body>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Logistics</h2>
  </div>
  <a href="logi_dashboard.php"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="logi_orders.php" class="active"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  
  <a href="logi_reports.php"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="logi_settings.php"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  <a href="logout.php" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
</div>

<div class="main-content" id="main">
    <header>
        <h1>Delivery Management</h1>
        <p class="sub-header">Manage order fulfillment and delivery status.</p>
    </header>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="tab-nav">
        <a class="<?php echo $status_filter == 'all' ? 'active' : ''; ?>" onclick="filterTable('all', this)">All</a>
        <a class="<?php echo $status_filter == 'processing' ? 'active' : ''; ?>" onclick="filterTable('processing', this)">Pending Pickup</a>
        <a class="<?php echo $status_filter == 'shipped' ? 'active' : ''; ?>" onclick="filterTable('shipped', this)">In Transit</a>
        <a class="<?php echo $status_filter == 'out_for_delivery' ? 'active' : ''; ?>" onclick="filterTable('out_for_delivery', this)">Out for Delivery</a>
        <a class="<?php echo $status_filter == 'delivered' ? 'active' : ''; ?>" onclick="filterTable('delivered', this)">Delivered</a>
    </div>

    <div class="search-container">
        <i class="fas fa-search" style="color: #ccc;"></i>
        <input type="text" id="searchInput" onkeyup="searchOrders()" placeholder="Search by Order ID or Customer..." value="<?php echo htmlspecialchars($search); ?>">
    </div>

    <form method="POST" id="bulkForm" onsubmit="return confirmBulkAction()">
        <div class="search-container" style="background: #f8f9fa;">
            <div class="bulk-actions">
                <span class="fw-bold">Bulk Actions:</span>
                <button type="submit" name="bulk_action" value="pickup" class="btn-action" id="bulkPickupBtn" disabled>📦 Mark as Picked Up</button>
                <button type="submit" name="bulk_action" value="out_for_delivery" class="btn-action" id="bulkOutForDeliveryBtn" disabled>🚚 Mark as Out for Delivery</button>
                <button type="submit" name="bulk_action" value="deliver" class="btn-action" id="bulkDeliverBtn" disabled>✅ Mark as Delivered</button>
            </div>
            <div class="select-all">
                <label>
                    <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll()"> Select All
                </label>
            </div>
        </div>

        <div class="table-card">
            <table id="deliveryTable">
                <thead>
                    <tr>
                        <th class="checkbox-col"><input type="checkbox" disabled></th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Assigned Logistic</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4">No orders found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr data-status="<?php echo $order['order_status']; ?>">
                            <td class="checkbox-col">
                                <input type="checkbox" name="selected_orders[]" value="<?php echo $order['order_id']; ?>" 
                                    class="order-checkbox" onclick="updateBulkButtons()"
                                    <?php echo ($order['order_status'] == 'delivered') ? 'disabled' : ''; ?>>
                            </td>
                            <td style="font-weight: bold;"><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td>
                                <?php echo !empty($order['logistic_name']) ? htmlspecialchars($order['logistic_name']) : '<span class="text-muted">Not assigned</span>'; ?>
                            </td>
                            <td>
                                <span class="badge status-<?php echo $order['order_status']; ?>">
                                    <?php 
                                        $status_labels = [
                                            'processing' => 'Pending Pickup',
                                            'shipped' => 'In Transit',
                                            'out_for_delivery' => 'Out for Delivery',
                                            'delivered' => 'Delivered'
                                        ];
                                        echo $status_labels[$order['order_status']] ?? ucfirst($order['order_status']);
                                    ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: #888;">
                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                            </td>
                            <td>
                                <?php if ($order['order_status'] == 'processing'): ?>
                                    <button type="button" class="btn-action" onclick="singleAction(<?php echo $order['order_id']; ?>, 'pickup')">
                                        📦 Pick Up
                                    </button>
                                <?php elseif ($order['order_status'] == 'shipped'): ?>
                                    <button type="button" class="btn-action" onclick="singleAction(<?php echo $order['order_id']; ?>, 'out_for_delivery')">
                                        🚚 Out for Delivery
                                    </button>
                                <?php elseif ($order['order_status'] == 'out_for_delivery'): ?>
                                    <button type="button" class="btn-action" onclick="singleAction(<?php echo $order['order_id']; ?>, 'deliver')">
                                        ✅ Deliver
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn-action" disabled>Completed</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
    document.getElementById("main").classList.toggle("full");
}

function searchOrders() {
    let input = document.getElementById("searchInput").value.toUpperCase();
    let rows = document.getElementById("deliveryTable").getElementsByTagName("tr");
    for (let i = 1; i < rows.length; i++) {
        let text = rows[i].textContent || rows[i].innerText;
        rows[i].style.display = text.toUpperCase().indexOf(input) > -1 ? "" : "none";
    }
}

function filterTable(status, element) {
    let url = new URL(window.location.href);
    url.searchParams.set('status', status);
    window.location.href = url.toString();
}

function updateBulkButtons() {
    let checkboxes = document.querySelectorAll('.order-checkbox:checked');
    let count = checkboxes.length;
    
    let pickupBtn = document.getElementById('bulkPickupBtn');
    let outForDeliveryBtn = document.getElementById('bulkOutForDeliveryBtn');
    let deliverBtn = document.getElementById('bulkDeliverBtn');
    
    // Check which statuses are selected
    let hasProcessing = false;
    let hasShipped = false;
    let hasOutForDelivery = false;
    
    checkboxes.forEach(cb => {
        let row = cb.closest('tr');
        let status = row.getAttribute('data-status');
        if (status === 'processing') hasProcessing = true;
        if (status === 'shipped') hasShipped = true;
        if (status === 'out_for_delivery') hasOutForDelivery = true;
    });
    
    pickupBtn.disabled = !hasProcessing || count === 0;
    outForDeliveryBtn.disabled = !hasShipped || count === 0;
    deliverBtn.disabled = !hasOutForDelivery || count === 0;
    
    // Update button text
    pickupBtn.innerHTML = hasProcessing ? `📦 Mark as Picked Up (${count})` : '📦 Mark as Picked Up';
    outForDeliveryBtn.innerHTML = hasShipped ? `🚚 Mark as Out for Delivery (${count})` : '🚚 Mark as Out for Delivery';
    deliverBtn.innerHTML = hasOutForDelivery ? `✅ Mark as Delivered (${count})` : '✅ Mark as Delivered';
}

function toggleSelectAll() {
    let selectAll = document.getElementById('selectAllCheckbox');
    let checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(cb => {
        if (!cb.disabled) {
            cb.checked = selectAll.checked;
        }
    });
    updateBulkButtons();
}

function confirmBulkAction() {
    let checkboxes = document.querySelectorAll('.order-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one order.');
        return false;
    }
    
    let activeButton = document.querySelector('button[name="bulk_action"]:focus');
    if (!activeButton) return false;
    
    let action = activeButton.value;
    let actionText = '';
    if (action === 'pickup') actionText = 'mark as picked up';
    else if (action === 'out_for_delivery') actionText = 'mark as out for delivery';
    else if (action === 'deliver') actionText = 'mark as delivered';
    
    return confirm(`Are you sure you want to ${actionText} ${checkboxes.length} order(s)?`);
}

function singleAction(orderId, action) {
    let actionText = '';
    let confirmMsg = '';
    
    if (action === 'pickup') {
        actionText = 'picked up';
        confirmMsg = 'Mark this order as picked up? This will change status to "In Transit".';
    } else if (action === 'out_for_delivery') {
        actionText = 'out for delivery';
        confirmMsg = 'Mark this order as out for delivery?';
    } else if (action === 'deliver') {
        actionText = 'delivered';
        confirmMsg = 'Mark this order as delivered?';
    }
    
    if (confirm(confirmMsg)) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        let actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'bulk_action';
        actionInput.value = action;
        
        let orderInput = document.createElement('input');
        orderInput.type = 'hidden';
        orderInput.name = 'selected_orders[]';
        orderInput.value = orderId;
        
        form.appendChild(actionInput);
        form.appendChild(orderInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize bulk buttons on page load
document.addEventListener('DOMContentLoaded', function() {
    updateBulkButtons();
});
</script>

</body>
</html>