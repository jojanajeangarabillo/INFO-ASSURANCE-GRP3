<?php
require_once 'auth.php';
require_roles([3, 4]);

require_once __DIR__ . '/admin/db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;

$seller_stmt = $conn->prepare("SELECT seller_id FROM seller WHERE user_id = ?");
$seller_stmt->bind_param("i", $user_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_id = $seller['seller_id'] ?? 0;
$seller_stmt->close();

$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$orders = [];
$orders_query = "
    SELECT o.order_id, o.order_number, o.order_status,
           o.created_at, u.username as customer_name,
           oi.line_total as total_amount,
           dt.courier_name, dt.status as tracking_status, dt.created_at as tracking_date
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id
    JOIN user u ON o.customer_id = u.user_id
    LEFT JOIN (
        SELECT order_id, courier_name, status, created_at
        FROM delivery_tracking dt1
        WHERE delivery_tracking_id = (
            SELECT MAX(delivery_tracking_id)
            FROM delivery_tracking dt2
            WHERE dt2.order_id = dt1.order_id
        )
    ) dt ON o.order_id = dt.order_id
    WHERE oi.seller_id = ?
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
$orders_stmt->bind_param($types, ...$params);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}
$orders_stmt->close();

$couriers = ['J&T Express', 'LBC', 'Ninja Van', 'Flash Express', '2GO'];

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
<title>Seller Orders</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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
    <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<!-- MAIN CONTENT -->
<div class="main-content" id="main">

<div class="container-fluid">

<h3 class="fw-bold">Delivery Management</h3>
<p class="text-muted">Manage order fulfillment and courier assignments.</p>

<!-- TABS -->
<div class="tabs mb-3">
  <a href="?status=All" class="text-decoration-none <?php echo $status_filter === 'All' || $status_filter === '' ? 'active' : ''; ?>">All</a>
  <a href="?status=pending" class="text-decoration-none <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Pending Pickup</a>
  <a href="?status=shipped" class="text-decoration-none <?php echo $status_filter === 'shipped' ? 'active' : ''; ?>">In Transit</a>
  <a href="?status=delivered" class="text-decoration-none <?php echo $status_filter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
</div>

<hr>

<!-- SEARCH -->
<div class="container-box mb-4">
  <form action="" method="GET" class="d-flex gap-2">
    <?php if (!empty($status_filter)): ?>
      <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
    <?php endif; ?>
    <input name="search" class="form-control" placeholder="Search by Order ID or Customer..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="btn btn-brand text-white">Search</button>
  </form>
</div>

<!-- TABLE -->
<div class="container-box">
<table class="table align-middle">

<thead>
<tr>
  <th>ORDER ID</th>
  <th>CUSTOMER</th>
  <th>COURIER</th>
  <th>STATUS</th>
  <th>TOTAL</th>
  <th>DATES</th>
  <th>ACTION</th>
</tr>
</thead>

<tbody>
<?php if (empty($orders)): ?>
  <tr><td colspan="7" class="text-center text-muted p-4">No orders found.</td></tr>
<?php else: ?>
  <?php foreach ($orders as $order): ?>
  <tr>
    <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
    <td>
      <select class="form-select form-select-sm" style="max-width: 150px;">
        <option value="">Select Courier</option>
        <?php foreach ($couriers as $courier): ?>
          <option value="<?php echo $courier; ?>" <?php echo $order['courier_name'] === $courier ? 'selected' : ''; ?>>
            <?php echo $courier; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </td>
    <td><?php echo getOrderStatusBadge($order['order_status']); ?></td>
    <td><?php echo formatCurrency($order['total_amount']); ?></td>
    <td style="font-size: 0.85rem;">
      Created: <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
      <?php if ($order['tracking_date']): ?>
        <br>Updated: <?php echo date('M d, Y', strtotime($order['tracking_date'])); ?>
      <?php endif; ?>
    </td>
    <td>
      <div class="dropdown">
        <button class="btn-outline dropdown-toggle" type="button" data-bs-toggle="dropdown">
          Manage
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#">View Details</a></li>
          <li><a class="dropdown-item" href="#">Update Status</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="#">Cancel Order</a></li>
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
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}
</script>


</body>
</html>