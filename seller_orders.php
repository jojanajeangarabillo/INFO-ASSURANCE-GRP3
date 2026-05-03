<?php
require_once 'auth.php';
require_roles([3, 4]);

require_once __DIR__ . '/admin/db.connect.php';

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

$seller_stmt = $conn->prepare("SELECT seller_id FROM seller WHERE user_id = ?");
$seller_stmt->bind_param("i", $user_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_id = $seller['seller_id'] ?? 0;
$seller_stmt->close();

// Function to send notification
function sendNotification($conn, $user_id, $title, $message, $notification_type, $reference_id = null) {
    $insert_notif = $conn->prepare("
        INSERT INTO notification (user_id, title, message, notification_type, reference_id, is_read, created_at) 
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");
    $insert_notif->bind_param("issii", $user_id, $title, $message, $notification_type, $reference_id);
    return $insert_notif->execute();
}

// Handle logistics assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_logistics'])) {
    $order_id = intval($_POST['order_id']);
    $logistic_user_id = intval($_POST['logistic_user_id']);
    
    // First verify that this order belongs to the current seller
    $verify_stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM order_item 
        WHERE order_id = ? AND seller_id = ?
    ");
    $verify_stmt->bind_param("ii", $order_id, $seller_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result()->fetch_assoc();
    $verify_stmt->close();
    
    if ($verify_result['count'] == 0) {
        $error_message = "You don't have permission to assign logistics for this order.";
    } else {
        // Get order details for notification
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
        
        // Get logistic personnel name
        $logistic_stmt = $conn->prepare("
            SELECT CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name, username
            FROM user WHERE user_id = ?
        ");
        $logistic_stmt->bind_param("i", $logistic_user_id);
        $logistic_stmt->execute();
        $logistic = $logistic_stmt->get_result()->fetch_assoc();
        $logistic_stmt->close();
        
        $logistic_name = trim($logistic['full_name']) ?: $logistic['username'];
        
        // Check if a record already exists for this order
        $check_stmt = $conn->prepare("SELECT delivery_tracking_id FROM delivery_tracking WHERE order_id = ?");
        $check_stmt->bind_param("i", $order_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        if ($existing) {
            // Update existing record
            $update_tracking = $conn->prepare("
                UPDATE delivery_tracking 
                SET logistic_user_id = ?, status = 'pending_pickup' 
                WHERE order_id = ?
            ");
            $update_tracking->bind_param("ii", $logistic_user_id, $order_id);
            $insert_success = $update_tracking->execute();
            $update_tracking->close();
        } else {
            // Insert new record
            $insert_tracking = $conn->prepare("
                INSERT INTO delivery_tracking (order_id, status, logistic_user_id, created_at) 
                VALUES (?, 'pending_pickup', ?, NOW())
            ");
            $insert_tracking->bind_param("ii", $order_id, $logistic_user_id);
            $insert_success = $insert_tracking->execute();
            $insert_tracking->close();
        }
        
        if ($insert_success) {
            // Update order status to 'processing' when logistics is assigned
            $update_order = $conn->prepare("UPDATE orders SET order_status = 'processing', updated_at = NOW() WHERE order_id = ?");
            $update_order->bind_param("i", $order_id);
            $update_order->execute();
            $update_order->close();
            
            // Send notification to customer
            $customer_title = "Order Status Update - Order #{$order_details['order_number']}";
            $customer_message = "Great news! Your order #{$order_details['order_number']} has been assigned to our logistics partner ({$logistic_name}) and is now being processed for delivery. You can track your order status in your account.";
            sendNotification($conn, $order_details['customer_id'], $customer_title, $customer_message, 'order_update', $order_id);
            
            // If customer has Dual role (role_id = 4), also send notification to their seller dashboard
            if ($order_details['role_id'] == 4) {
                $dual_title = "Order Assignment Notification - Order #{$order_details['order_number']}";
                $dual_message = "An order you placed as a customer (Order #{$order_details['order_number']}) has been assigned to logistics personnel ({$logistic_name}). The order is now being processed.";
                sendNotification($conn, $order_details['customer_id'], $dual_title, $dual_message, 'order_update', $order_id);
            }
            
            $success_message = "Logistics personnel assigned successfully! Customer has been notified.";
        } else {
            $error_message = "Failed to assign logistics. Please try again.";
        }
    }
}

$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$orders = [];

// Updated query to only show orders that belong to the current seller
// and calculate the correct total for this seller's portion of the order
$orders_query = "
    SELECT o.order_id, o.order_number, o.order_status,
           o.created_at, o.customer_id, u.username as customer_name, u.role_id as customer_role,
           SUM(oi.line_total) as total_amount,
           dt.status as tracking_status, dt.created_at as tracking_date,
           dt.logistic_user_id,
           CONCAT(COALESCE(logistic_user.first_name, ''), ' ', COALESCE(logistic_user.last_name, '')) as logistic_name
    FROM orders o
    INNER JOIN order_item oi ON o.order_id = oi.order_id AND oi.seller_id = ?
    INNER JOIN user u ON o.customer_id = u.user_id
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
    WHERE 1=1
";

$params = [$seller_id];
$types = "i";

if (!empty($status_filter) && $status_filter !== 'All') {
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

$orders_query .= " GROUP BY o.order_id ORDER BY o.created_at DESC";

$orders_stmt = $conn->prepare($orders_query);
if ($orders_stmt) {
    $orders_stmt->bind_param($types, ...$params);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
    $orders_stmt->close();
} else {
    error_log("SQL Error: " . $conn->error);
}

// Fetch logistic users (role_id = 5)
$logistic_stmt = $conn->prepare("
    SELECT u.user_id, u.username, 
           CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as full_name
    FROM user u
    WHERE u.role_id = 5
    ORDER BY u.username
");
$logistic_stmt->execute();
$logistic_users = $logistic_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$logistic_stmt->close();

function getOrderStatusBadge($status) {
    $status = strtolower($status);
    $badges = [
        'pending' => '<span class="badge-status pending">Pending</span>',
        'processing' => '<span class="badge-status transit">Processing</span>',
        'shipped' => '<span class="badge-status transit">Shipped</span>',
        'delivered' => '<span class="badge-status delivered">Delivered</span>',
        'cancelled' => '<span class="badge-status" style="background:#fde8e8; color:#dc2626;">Cancelled</span>',
        'returned' => '<span class="badge-status" style="background:#f3f4f6; color:#374151;">Returned</span>'
    ];
    return $badges[$status] ?? '<span class="badge-status">' . ucfirst($status) . '</span>';
}

function formatCurrency($amount) {
    return '₱' . number_format((float)$amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Seller Orders - Logistics Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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
body {
  background: #f5f5f5;
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
}

/* SIDEBAR */
.sidebar {
  width: 240px;
  position: fixed;
  height: 100%;
  top: 0;
  left: 0;
}

.sidebar.collapsed {
  width: 70px;
}

/* MAIN CONTENT (MATCH DASHBOARD) */
.main-content {
  margin-left: 240px;
  transition: 0.3s;
  padding: 20px;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 70px;
}

/* CARD STYLE */
.container-box {
  background: #fff;
  border-radius: 16px;
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  padding: 20px;
}

/* THEME */
:root {
  --brand: #6d0f1b;
}

.btn-brand {
  background-color: var(--brand);
  color: white;
}

.btn-brand:hover {
  background-color: #8a1423;
  color: white;
}

/* TABS */
.tabs {
  display: flex;
  gap: 50px;
  padding-bottom: 2px;
}

.tabs a {
  cursor: pointer;
  padding-bottom: 12px;
  color: gray;
  font-size: 18px;
  transition: all 0.2s;
  border-bottom: 3px solid transparent;
  text-decoration: none;
}

.tabs a:hover {
  color: var(--brand);
}

.tabs .active {
  border-bottom: 3px solid var(--brand);
  font-weight: bold;
  color: var(--brand) !important;
}

/* BADGES */
.badge-status {
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 20px;
  display: inline-block;
}

.pending { background:#fff3cd; color:#856404; }
.transit { background:#e0ecff; color:#0d6efd; }
.delivered { background:#d1e7dd; color:#198754; }

/* BUTTON */
.btn-outline {
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 6px 12px;
  background: white;
}

.btn-outline:hover {
  border-color: var(--brand);
  color: var(--brand);
}

/* TABLE */
.table th {
  font-size: 12px;
  color: gray;
}

.assign-form {
  min-width: 200px;
}

.alert-custom {
  border-radius: 12px;
  margin-bottom: 20px;
}

.badge-dual {
  background-color: #6f42c1;
  color: white;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 5px;
}

.info-banner {
    background-color: #e7f3ff;
    border-left: 4px solid #0d6efd;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="logo-text">Seller</div>
  </div>

    <a href="seller_dashboard.php"><i class="bi bi-speedometer2"></i><span class="text">Dashboard</span></a>
    <a href="seller_products.php"><i class="bi bi-box-seam"></i><span class="text">Products</span></a>
    <a href="seller_orders.php" class="active"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
    <a href="seller_inventory.php"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
    <a href="seller_reviews.php"><i class="bi bi-star"></i><span class="text">Reviews</span></a>
    <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
    
  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<!-- MAIN CONTENT -->
<div class="main-content" id="main">

<div class="container-fluid">

<h3 class="fw-bold">Delivery Management</h3>
<p class="text-muted">Manage order fulfillment and assign logistics personnel (Role 5).</p>

<div class="info-banner">
    <i class="bi bi-shop"></i> <strong>Seller ID:</strong> <?php echo $seller_id; ?> | 
    <i class="bi bi-box-seam"></i> <strong>Showing orders that contain products from your store only</strong>
</div>

<!-- Success/Error Messages -->
<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- TABS -->
<div class="tabs mb-3">
  <a href="?status=All" class="text-decoration-none <?php echo $status_filter === 'All' || $status_filter === '' ? 'active' : ''; ?>">All</a>
  <a href="?status=pending" class="text-decoration-none <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Pending Pickup</a>
  <a href="?status=processing" class="text-decoration-none <?php echo $status_filter === 'processing' ? 'active' : ''; ?>">Processing</a>
  <a href="?status=shipped" class="text-decoration-none <?php echo $status_filter === 'shipped' ? 'active' : ''; ?>">In Transit</a>
  <a href="?status=delivered" class="text-decoration-none <?php echo $status_filter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
</div>

<hr>

<!-- SEARCH -->
<div class="container-box mb-4">
  <form action="" method="GET" class="d-flex gap-2">
    <?php if (!empty($status_filter) && $status_filter !== 'All'): ?>
      <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
    <?php endif; ?>
    <input name="search" class="form-control" placeholder="Search by Order ID or Customer..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="btn btn-brand">Search</button>
    <?php if (!empty($search) || (!empty($status_filter) && $status_filter !== 'All')): ?>
      <a href="seller_orders.php" class="btn btn-outline-secondary">Clear Filters</a>
    <?php endif; ?>
  </form>
</div>

<!-- TABLE -->
<div class="container-box">
<div class="table-responsive">
<table class="table align-middle">

<thead>
<tr>
  <th>ORDER ID</th>
  <th>CUSTOMER</th>
  <th>ASSIGN LOGISTICS (Role 5)</th>
  <th>ASSIGNED LOGISTIC</th>
  <th>STATUS</th>
  <th>TOTAL (Your Share)</th>
  <th>DATES</th>
  <th>ACTION</th>
</tr>
</thead>

<tbody>
<?php if (empty($orders)): ?>
  <tr><td colspan="8" class="text-center text-muted p-4">No orders found for your store.</td></tr>
<?php else: ?>
  <?php foreach ($orders as $order): ?>
  <tr>
    <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
    <td>
      <?php echo htmlspecialchars($order['customer_name']); ?>
      <?php if ($order['customer_role'] == 4): ?>
        <span class="badge-dual">Dual Role</span>
      <?php endif; ?>
    </td>
    <td>
      <form method="POST" class="assign-form" onsubmit="return confirm('Assign this order to selected logistics personnel? The customer will be notified.')">
        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
        <div class="d-flex gap-2 align-items-center">
          <select name="logistic_user_id" class="form-select form-select-sm" style="min-width: 200px;" required>
            <option value="">Select Logistics Personnel</option>
            <?php foreach ($logistic_users as $logistic): ?>
              <option value="<?php echo $logistic['user_id']; ?>" 
                <?php echo (!empty($order['logistic_user_id']) && $order['logistic_user_id'] == $logistic['user_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($logistic['full_name'] ?: $logistic['username']); ?> (<?php echo htmlspecialchars($logistic['username']); ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" name="assign_logistics" class="btn btn-sm btn-brand" 
            <?php echo ($order['order_status'] == 'delivered' || $order['order_status'] == 'cancelled') ? 'disabled' : ''; ?>>
            Assign
          </button>
        </div>
      </form>
    </td>
    <td>
      <?php if (!empty($order['logistic_name'])): ?>
        <span class="badge bg-secondary"><?php echo htmlspecialchars($order['logistic_name']); ?></span>
      <?php else: ?>
        <span class="text-muted">Not assigned</span>
      <?php endif; ?>
    </td>
    <td><?php echo getOrderStatusBadge($order['order_status']); ?></td>
    <td><?php echo formatCurrency($order['total_amount']); ?></td>
    <td style="font-size: 0.85rem;">
      Created: <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
      <?php if (!empty($order['tracking_date'])): ?>
        <br>Updated: <?php echo date('M d, Y', strtotime($order['tracking_date'])); ?>
      <?php endif; ?>
    </td>
    <td>
      <div class="dropdown">
        <button class="btn-outline dropdown-toggle" type="button" data-bs-toggle="dropdown">
          Manage
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="seller_order_details.php?order_id=<?php echo $order['order_id']; ?>">View Details</a></li>
          <?php if ($order['order_status'] != 'delivered' && $order['order_status'] != 'cancelled'): ?>
            <li><a class="dropdown-item" href="seller_update_order_status.php?order_id=<?php echo $order['order_id']; ?>&status=processing">Mark as Processing</a></li>
            <li><a class="dropdown-item" href="seller_update_order_status.php?order_id=<?php echo $order['order_id']; ?>&status=shipped">Mark as Shipped</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="seller_cancel_order.php?order_id=<?php echo $order['order_id']; ?>" onclick="return confirm('Are you sure you want to cancel this order?')">Cancel Order</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>

</table>
</div>
</div>

<!-- Legend -->
<div class="container-box mt-3">
  <small class="text-muted">
    <i class="bi bi-info-circle"></i> <strong>Note:</strong> 
    <ul class="mt-2 mb-0">
      <li>Only orders containing products from YOUR store are displayed here.</li>
      <li>The "Total" column shows only YOUR share of the order total (products from your store only).</li>
      <li>Assigning a logistics personnel (Role 5) will automatically update the order status to "Processing".</li>
      <li><i class="bi bi-bell-fill text-primary"></i> <strong>Notifications:</strong> The customer will receive a notification about the logistics assignment.</li>
      <li><i class="bi bi-star-fill text-warning"></i> <strong>Dual Role Users:</strong> Customers with Dual role (role_id = 4) receive notifications in both their customer and seller views.</li>
    </ul>
    <?php if (empty($logistic_users)): ?>
      <br><i class="bi bi-exclamation-triangle"></i> <strong class="text-warning">No logistics personnel found. Please add users with Role 5 (Logistic) first.</strong>
    <?php endif; ?>
  </small>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}
</script>

</body>
</html>