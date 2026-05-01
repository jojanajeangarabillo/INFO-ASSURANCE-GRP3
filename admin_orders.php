<?php
require_once 'auth.php';
require_roles([1]);
require_once 'admin/db.connect.php';
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

// Calculate timeout in milliseconds for client-side auto-logout
$timeout_ms = $timeout_minutes * 60 * 1000;

// Fetch order statistics
$stats_query = "
    SELECT 
        COUNT(*) AS total_orders,
        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) AS total_revenue,
        COUNT(CASE WHEN order_status = 'pending' OR order_status = 'processing' OR order_status = 'packed' THEN 1 END) AS pending_fulfillment
    FROM orders
";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Fetch all orders with customer details
$orders_query = "
    SELECT 
        o.*,
        u.username,
        u.email AS customer_email
    FROM orders o
    LEFT JOIN user u ON o.customer_id = u.user_id
    ORDER BY o.created_at DESC
";
$orders_result = mysqli_query($conn, $orders_query);
$orders = [];
while ($order = mysqli_fetch_assoc($orders_result)) {
    $orders[] = $order;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Management</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="sidebar.css">
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
  margin: 0;
  font-family: Arial, sans-serif;
  background: #fdf2f6;
}

/* SAME LAYOUT */
.container {
  margin-left: 240px;
  padding: 20px;
  transition: 0.3s;
}

.container.full {
  margin-left: 70px;
}

/* GRID */
.grid {
  display: grid;
  gap: 20px;
}

.grid-3 {
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

/* CARDS (MATCH DASHBOARD STYLE) */
.card {
  background: #610C27;
  color: white;
  padding: 20px;
  border-radius: 12px;
}

.card small {
  opacity: 0.8;
}

/* SEARCH */
.search-box {
  background: white;
  padding: 12px;
  border-radius: 10px;
  margin-bottom: 20px;
}

.search-box input {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #ddd;
}

/* TABLE */
table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

th, td {
  padding: 12px;
  text-align: left;
}

th {
  background: #EFECE9;
  font-size: 12px;
}

tr:hover {
  background: #fdf2f6;
}

/* BADGES */
.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
}

.success { background: #d1fae5; color: #065f46; }
.warning { background: #fef3c7; color: #92400e; }
.danger { background: #fee2e2; color: #991b1b; }
.info { background: #dbeafe; color: #1e40af; }

/* BUTTON */
.btn {
  border: 1px solid #ccc;
  padding: 5px 10px;
  border-radius: 6px;
  background: white;
  cursor: pointer;
}
h1 {
  color: #610C27;
}
</style>
</head>

<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Admin</h2>
  </div>

  <a href="admin_dashboard.php" class="<?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="admin_analytics.php" class="<?php echo $current_page == 'admin_analytics.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i><span class="text">Analytics</span></a>
  <a href="admin_users.php" class="<?php echo $current_page == 'admin_users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i><span class="text">Users</span></a>
  <a href="admin_auditlogs.php" class="<?php echo $current_page == 'admin_auditlogs.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i><span class="text">Audit Logs</span></a>
  <a href="admin_orders.php" class="<?php echo $current_page == 'admin_orders.php' ? 'active' : ''; ?>"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="admin_reports.php" class="<?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  
  <a href="admin_settings.php" class="<?php echo $current_page == 'admin_settings.php' ? 'active' : ''; ?>"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  
  <a href="login.php" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
</div>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const main = document.getElementById("main");
  if (sidebar) sidebar.classList.toggle("collapsed");
  if (main) main.classList.toggle("full");
}
</script>

<!-- CONTENT -->
<div class="container" id="main">

<h1>Platform Orders</h1>
<p>Track and monitor all orders across the platform.</p>

<br>

<!-- STATS -->
<div class="grid grid-3">

  <div class="card">
    <small>Total Orders</small>
    <h2><?php echo number_format($stats['total_orders']); ?></h2>
  </div>

  <div class="card">
    <small>Total Revenue</small>
    <h2>₱<?php echo number_format($stats['total_revenue'], 2); ?></h2>
  </div>

  <div class="card">
    <small>Pending Fulfillment</small>
    <h2><?php echo number_format($stats['pending_fulfillment']); ?></h2>
  </div>

</div>

<br>

<!-- SEARCH -->
<div class="search-box">
  <input type="text" id="searchInput" placeholder="Search Order ID or Customer Email">
</div>

<!-- TABLE -->
<table id="ordersTable">
<thead>
<tr>
  <th>Order ID</th>
  <th>Customer</th>
  <th>Total</th>
  <th>Payment</th>
  <th>Fraud</th>
  <th>Status</th>
  <th>Action</th>
</tr>
</thead>

<tbody>
<?php if (empty($orders)): ?>
  <tr>
    <td colspan="7" style="text-align:center; padding:40px; color:#999;">No orders found.</td>
  </tr>
<?php else: ?>
  <?php foreach ($orders as $order): ?>
    <?php
      // Determine payment status badge
      $payment_class = 'warning';
      $payment_text = ucfirst($order['payment_status']);
      if ($order['payment_status'] == 'paid') {
          $payment_class = 'success';
      } elseif ($order['payment_status'] == 'failed') {
          $payment_class = 'danger';
      }

      // Determine order status badge
      $order_class = 'warning';
      $order_text = ucfirst($order['order_status']);
      if (in_array($order['order_status'], ['delivered', 'shipped'])) {
          $order_class = 'success';
      } elseif (in_array($order['order_status'], ['cancelled', 'returned'])) {
          $order_class = 'danger';
      }

      // Fraud status (placeholder - set to Safe for now)
      $fraud_class = 'success';
      $fraud_text = 'Safe';
    ?>
    <tr>
      <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
      <td><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></td>
      <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
      <td><span class="badge <?php echo $payment_class; ?>"><?php echo $payment_text; ?></span></td>
      <td><span class="badge <?php echo $fraud_class; ?>"><?php echo $fraud_text; ?></span></td>
      <td><span class="badge <?php echo $order_class; ?>"><?php echo $order_text; ?></span></td>
      <td><button class="btn">Invoice</button></td>
    </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>

</div>

<script>
function updateStatus(orderId) {
  alert('Update status for order: ' + orderId);
}

// Simple search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#ordersTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length > 1) {
            const orderId = row.cells[0].textContent.toLowerCase();
            const customer = row.cells[1].textContent.toLowerCase();
            
            if (orderId.includes(searchTerm) || customer.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
});
</script>

</body>
</html>