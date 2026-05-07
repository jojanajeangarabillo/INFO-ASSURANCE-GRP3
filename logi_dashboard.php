<?php
require_once 'auth.php';
require_roles([5]); // Only logistics role can access

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
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
} else {
  $_SESSION['last_activity'] = time();
}

$timeout_ms = $timeout_minutes * 60 * 1000;

// Get logistics user info
$user_stmt = $conn->prepare("SELECT username, first_name, last_name, email FROM user WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_info = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Get dashboard statistics - FILTERED BY LOGGED-IN LOGISTICS USER
$stats = [];

// Pending Pickup (processing status from orders assigned to this logistics user)
$pending_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id) as count 
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE o.order_status = 'processing' AND dt.logistic_user_id = ?
");
$pending_stmt->bind_param("i", $user_id);
$pending_stmt->execute();
$stats['pending_pickup'] = $pending_stmt->get_result()->fetch_assoc()['count'];
$pending_stmt->close();

// In Transit (shipped status - assigned to this logistics user)
$transit_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id) as count 
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE o.order_status = 'shipped' AND dt.logistic_user_id = ?
");
$transit_stmt->bind_param("i", $user_id);
$transit_stmt->execute();
$stats['in_transit'] = $transit_stmt->get_result()->fetch_assoc()['count'];
$transit_stmt->close();

// Out for Delivery - assigned to this logistics user
$out_for_delivery_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id) as count 
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE o.order_status = 'out_for_delivery' AND dt.logistic_user_id = ?
");
$out_for_delivery_stmt->bind_param("i", $user_id);
$out_for_delivery_stmt->execute();
$stats['out_for_delivery'] = $out_for_delivery_stmt->get_result()->fetch_assoc()['count'];
$out_for_delivery_stmt->close();

// Delivered Today - assigned to this logistics user
$delivered_today_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id) as count 
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE o.order_status = 'delivered' 
    AND DATE(o.updated_at) = CURDATE()
    AND dt.logistic_user_id = ?
");
$delivered_today_stmt->bind_param("i", $user_id);
$delivered_today_stmt->execute();
$stats['delivered_today'] = $delivered_today_stmt->get_result()->fetch_assoc()['count'];
$delivered_today_stmt->close();

// Get recent deliveries with pagination - FILTERED BY LOGGED-IN LOGISTICS USER
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 5;
$offset = ($page - 1) * $items_per_page;

// Count total deliveries for pagination - only for this logistics user
$count_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id) as total
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE o.order_status IN ('processing', 'shipped', 'out_for_delivery', 'delivered')
    AND dt.logistic_user_id = ?
");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$total_deliveries = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();
$total_pages = ceil($total_deliveries / $items_per_page);

// Get recent deliveries - only for this logistics user
$deliveries_stmt = $conn->prepare("
    SELECT DISTINCT o.order_id, o.order_number, o.order_status, o.created_at, o.updated_at,
           u.username as customer_name,
           CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as customer_fullname,
           dt.status as delivery_status, dt.logistic_user_id
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id
    JOIN user u ON o.customer_id = u.user_id
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE o.order_status IN ('processing', 'shipped', 'out_for_delivery', 'delivered')
    AND dt.logistic_user_id = ?
    ORDER BY o.updated_at DESC
    LIMIT ? OFFSET ?
");
$deliveries_stmt->bind_param("iii", $user_id, $items_per_page, $offset);
$deliveries_stmt->execute();
$deliveries_result = $deliveries_stmt->get_result();

$deliveries = [];
while ($row = $deliveries_result->fetch_assoc()) {
    // Format status for display
    $status_display = [
        'processing' => 'Pending Pickup',
        'shipped' => 'In Transit',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered'
    ];
    
    $status_class = [
        'processing' => 'pending',
        'shipped' => 'in-transit',
        'out_for_delivery' => 'out-for-delivery',
        'delivered' => 'delivered'
    ];
    
    $row['status_display'] = $status_display[$row['order_status']];
    $row['status_class'] = $status_class[$row['order_status']];
    $row['customer_display'] = !empty($row['customer_fullname']) ? $row['customer_fullname'] : $row['customer_name'];
    $row['formatted_date'] = date('M d, Y', strtotime($row['created_at']));
    
    $deliveries[] = $row;
}
$deliveries_stmt->close();

// Get total assigned orders count
$total_assigned_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id) as total
    FROM orders o
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE dt.logistic_user_id = ?
");
$total_assigned_stmt->bind_param("i", $user_id);
$total_assigned_stmt->execute();
$total_assigned = $total_assigned_stmt->get_result()->fetch_assoc()['total'];
$total_assigned_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logistics Dashboard</title>

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
/* ===== MAIN CONTENT STYLES ONLY – NO SIDEBAR MODIFICATIONS ===== */
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #fdf2f6;
}

.main-content {
  margin-left: 240px;
  padding: 40px 60px;
  transition: margin-left 0.3s ease;
}

.main-content.full {
  margin-left: 70px;
}

/* metrics & recent deliveries styling */
.card-box {
  background: white;
  border-radius: 18px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  margin-bottom: 25px;
  transition: transform 0.25s ease, box-shadow 0.3s ease;
}

.card-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 18px 30px -8px rgba(97, 12, 39, 0.15);
}

.center-box {
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
}

.flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

h1 {
  color: #610C27;
  margin-bottom: 5px;
  font-size: 32px;
}

p {
  font-size: 16px;
}

/* metrics grid */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-bottom: 40px;
}

.metric-card {
  background: white;
  border-radius: 18px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: transform 0.25s ease, box-shadow 0.3s ease;
  cursor: default;
}

.metric-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 28px -12px rgba(97, 12, 39, 0.2);
}

.metric-icon {
  width: 50px;
  height: 50px;
  background: #fdf2f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #610C27;
}

.metric-label {
  font-size: 14px;
  color: #6b4a5c;
  margin-bottom: 4px;
}

.metric-value {
  font-size: 28px;
  font-weight: bold;
  color: #1e0a12;
}

/* recent deliveries table - 4 columns */
.delivery-header {
  display: grid;
  grid-template-columns: 1fr 1.4fr 1fr 1.2fr;
  gap: 16px;
  padding: 12px 0;
  border-bottom: 2px solid #f0e0e7;
  margin-top: 20px;
  font-weight: bold;
  font-size: 14px;
  color: #8e6b7c;
  text-transform: uppercase;
}

.delivery-row {
  display: grid;
  grid-template-columns: 1fr 1.4fr 1fr 1.2fr;
  gap: 16px;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid #f3e6ec;
  transition: background 0.15s;
}

.delivery-row:hover {
  background: #fefafc;
  border-radius: 12px;
  margin: 0 -4px;
  padding: 14px 4px;
}

.order-id {
  font-weight: 700;
  color: #2c0f1b;
}

.order-id a {
  color: #610C27;
  text-decoration: none;
  font-weight: 700;
}

.order-id a:hover {
  text-decoration: underline;
}

.customer-name {
  font-weight: 500;
  color: #2c3a4b;
}

.delivery-date {
  font-size: 13px;
  color: #6b4a5c;
  background: #faf0f4;
  padding: 4px 12px;
  border-radius: 40px;
  display: inline-block;
  width: fit-content;
}

.status {
  font-size: 13px;
  font-weight: 600;
  padding: 5px 14px;
  border-radius: 100px;
  text-align: center;
  width: fit-content;
}

.status.delivered {
  background: #e6f4ea;
  color: #1e6f3f;
}

.status.out-for-delivery {
  background: #e3f2fd;
  color: #0b5e9e;
}

.status.in-transit {
  background: #fff1e0;
  color: #c45100;
}

.status.pending {
  background: #ffe8ed;
  color: #bc2c4e;
}

/* pagination */
.pagination-wrapper {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
  gap: 12px;
  align-items: center;
}

.page-btn {
  background: #fdf2f6;
  border: none;
  padding: 6px 16px;
  border-radius: 30px;
  font-weight: 600;
  color: #610C27;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
}

.page-btn:hover:not(:disabled) {
  background: #610C27;
  color: white;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-indicator {
  font-size: 14px;
  background: #fff4f8;
  padding: 4px 12px;
  border-radius: 30px;
}

.view-all {
  font-size: 14px;
  font-weight: 600;
  color: #610C27;
  text-decoration: none;
  background: #fdf2f6;
  padding: 6px 16px;
  border-radius: 40px;
}

.view-all:hover {
  background: #610C27;
  color: white;
}

/* welcome banner */
.welcome-banner {
  background: linear-gradient(135deg, #610C27 0%, #8a1423 100%);
  color: white;
  padding: 20px 25px;
  border-radius: 18px;
  margin-bottom: 30px;
}

.welcome-banner h2 {
  margin: 0 0 5px 0;
  font-size: 24px;
}

.welcome-banner p {
  margin: 0;
  opacity: 0.9;
}

/* refresh button */
.refresh-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.2s;
}

.refresh-btn:hover {
  background: rgba(255,255,255,0.3);
}

.alert-custom {
  border-radius: 12px;
  margin-bottom: 20px;
}

.info-badge {
  background-color: #e7f3ff;
  border-left: 4px solid #0d6efd;
  padding: 10px 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}
</style>
</head>
<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<!-- SIDEBAR – UNCHANGED, EXACTLY AS ORIGINAL -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Logistics</h2>
  </div>

  <a href="logi_dashboard.php" class="<?= $current_page == 'logi_dashboard.php' ? 'active' : '' ?>">
    <i class="fas fa-table-columns"></i><span class="text">Dashboard</span>
  </a>

  <a href="logi_orders.php" class="<?= $current_page == 'logi_orders.php' ? 'active' : '' ?>">
    <i class="fas fa-cart-shopping"></i><span class="text">Orders</span>
  </a>

  <a href="logi_drivers.php" class="<?= $current_page == 'logi_drivers.php' ? 'active' : '' ?>">
    <i class="fas fa-truck"></i><span class="text">Drivers</span>
  </a>

  <a href="logi_reports.php" class="<?= $current_page == 'logi_reports.php' ? 'active' : '' ?>">
    <i class="fas fa-file-lines"></i><span class="text">Reports</span>
  </a>

  <a href="logi_settings.php" class="<?= $current_page == 'logi_settings.php' ? 'active' : '' ?>">
    <i class="fas fa-gear"></i><span class="text">Settings</span>
  </a>

  <a href="logout.php" class="logout">
    <i class="fas fa-right-from-bracket"></i><span class="text">Logout</span>
  </a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content" id="main">
  <div class="center-box">
  
    <!-- Welcome Banner with User Info -->
    <div class="welcome-banner">
      <div class="flex">
        <div>
          <h2>Welcome back, <?php echo htmlspecialchars($user_info['first_name'] ?: $user_info['username']); ?>!</h2>
          <p>Here's your delivery overview for today</p>
          <small><i class="fas fa-user"></i> Logistics ID: <?php echo $user_id; ?> | <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user_info['email']); ?></small>
        </div>
        <button class="refresh-btn" onclick="window.location.reload()">
          <i class="fas fa-sync-alt"></i> Refresh
        </button>
      </div>
    </div>
    
    <!-- Info Banner -->
    <div class="info-badge">
      <i class="fas fa-info-circle"></i> 
      <strong>Showing orders assigned to you:</strong> Total assigned orders: <?php echo $total_assigned; ?>
    </div>
    
    <!-- 4 metric cards with real data (filtered by logistics user) -->
    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-icon"><i class="fas fa-box-open"></i></div>
        <div class="metric-info">
          <div class="metric-label">Pending Pickup</div>
          <div class="metric-value"><?php echo number_format($stats['pending_pickup']); ?></div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon"><i class="fas fa-truck-ramp-box"></i></div>
        <div class="metric-info">
          <div class="metric-label">In Transit</div>
          <div class="metric-value"><?php echo number_format($stats['in_transit']); ?></div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon"><i class="fas fa-person-walking-arrow-right"></i></div>
        <div class="metric-info">
          <div class="metric-label">Out for Delivery</div>
          <div class="metric-value"><?php echo number_format($stats['out_for_delivery']); ?></div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon"><i class="fas fa-circle-check"></i></div>
        <div class="metric-info">
          <div class="metric-label">Delivered Today</div>
          <div class="metric-value"><?php echo number_format($stats['delivered_today']); ?></div>
        </div>
      </div>
    </div>

    <!-- Recent Deliveries (filtered by logistics user) -->
    <div class="card-box">
      <div class="flex">
        <h3>Recent Deliveries</h3>
        <a href="logi_orders.php" class="view-all">
          View All Orders <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <!-- Updated column titles: Order ID, Customer, Date, Status -->
      <div class="delivery-header">
        <span>Order ID</span>
        <span>Customer</span>
        <span>Order Date</span>
        <span>Status</span>
      </div>

      <div id="deliveryRowsContainer">
        <?php if (empty($deliveries)): ?>
          <div class="delivery-row">
            <div colspan="4" style="text-align: center; padding: 40px; color: #999;">
              No deliveries assigned to you yet.
            </div>
          </div>
        <?php else: ?>
          <?php foreach ($deliveries as $delivery): ?>
            <div class="delivery-row">
              <div class="order-id">
                <a href="logi_order_details.php?order_id=<?php echo $delivery['order_id']; ?>">
                  <?php echo htmlspecialchars($delivery['order_number']); ?>
                </a>
              </div>
              <div class="customer-name"><?php echo htmlspecialchars($delivery['customer_display']); ?></div>
              <div><span class="delivery-date"><?php echo $delivery['formatted_date']; ?></span></div>
              <div class="status <?php echo $delivery['status_class']; ?>"><?php echo $delivery['status_display']; ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if ($total_pages > 1): ?>
      <div class="pagination-wrapper">
        <?php if ($page > 1): ?>
          <a href="?page=<?php echo $page - 1; ?>" class="page-btn">
            <i class="fas fa-chevron-left"></i> Previous
          </a>
        <?php else: ?>
          <button class="page-btn" disabled><i class="fas fa-chevron-left"></i> Previous</button>
        <?php endif; ?>
        
        <span class="page-indicator">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
        
        <?php if ($page < $total_pages): ?>
          <a href="?page=<?php echo $page + 1; ?>" class="page-btn">
            Next <i class="fas fa-chevron-right"></i>
          </a>
        <?php else: ?>
          <button class="page-btn" disabled>Next <i class="fas fa-chevron-right"></i></button>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
// ========== ORIGINAL SIDEBAR TOGGLE (UNCHANGED) ==========
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

// Auto-refresh every 30 seconds (optional)
setInterval(function() {
    location.reload();
}, 30000);
</script>

</body>
</html>