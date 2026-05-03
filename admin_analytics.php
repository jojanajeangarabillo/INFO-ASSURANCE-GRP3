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
  // Session expired, logout
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
} else {
  // Update last activity
  $_SESSION['last_activity'] = time();
}

// Calculate timeout in milliseconds for client-side auto-logout
$timeout_ms = $timeout_minutes * 60 * 1000;

// Fetch analytics metrics based on actual database schema
$topGenderStmt = $conn->prepare("
    SELECT 
        p.category_gender, 
        COUNT(oi.order_item_id) as order_count
    FROM product p
    LEFT JOIN order_item oi ON p.product_id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.order_id AND o.payment_status = 'paid'
    GROUP BY p.category_gender
    ORDER BY order_count DESC
    LIMIT 1
");
$topGenderStmt->execute();
$topGenderResult = $topGenderStmt->get_result()->fetch_assoc();
$topCategory = $topGenderResult ? $topGenderResult['category_gender'] : 'N/A';
$topGenderStmt->close();

// Average order value
$avgOrderStmt = $conn->prepare("SELECT COALESCE(AVG(total_amount), 0) as avg_order FROM orders WHERE payment_status = 'paid'");
$avgOrderStmt->execute();
$avgOrderValue = $avgOrderStmt->get_result()->fetch_assoc()['avg_order'];
$avgOrderStmt->close();

// Repeat customers
$repeatCustomersStmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT customer_id) as total_customers,
        SUM(CASE WHEN order_count > 1 THEN 1 ELSE 0 END) as repeat_customers
    FROM (
        SELECT customer_id, COUNT(*) as order_count 
        FROM orders 
        WHERE payment_status = 'paid' AND customer_id IS NOT NULL
        GROUP BY customer_id
    ) as customer_orders
");
$repeatCustomersStmt->execute();
$repeatResult = $repeatCustomersStmt->get_result()->fetch_assoc();
$repeatCustomers = $repeatResult['total_customers'] > 0 
    ? round(($repeatResult['repeat_customers'] / $repeatResult['total_customers']) * 100) 
    : 0;
$repeatCustomersStmt->close();

// Seller performance
$sellerPerformanceStmt = $conn->prepare("
    SELECT 
        s.shop_name,
        COALESCE(SUM(oi.line_total), 0) as revenue,
        COUNT(DISTINCT o.order_id) as order_count
    FROM seller s
    LEFT JOIN order_item oi ON s.seller_id = oi.seller_id
    LEFT JOIN orders o ON oi.order_id = o.order_id AND o.payment_status = 'paid'
    WHERE s.is_approved = 1
    GROUP BY s.seller_id
    ORDER BY revenue DESC
    LIMIT 10
");
$sellerPerformanceStmt->execute();
$sellerPerformance = $sellerPerformanceStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$sellerPerformanceStmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Analytics & Reports</title>

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
  transition: margin-left 0.3s ease;
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

/* CARDS */
.card {
  background: #610C27;
  color: white;
  padding: 20px;
  border-radius: 12px;
}

/* BOX */
.box {
  background: #EFECE9;
  padding: 20px;
  border-radius: 12px;
}
h1 {
  color: #610C27;
}

/* TABLE */
table {
  width: 100%;
  border-collapse: collapse;
}

th {
  background: #eee;
  padding: 10px;
  text-align: left;
}

td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
}

.text-muted {
  color: #999;
  text-align: center;
  padding: 20px;
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
  
  <a href="logout.php" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
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

<h1>Analytics & Reports</h1>
<p>Platform-wide performance and customer behavior insights.</p>

<br>

<div class="grid grid-3">

  <div class="card">
    <small>Top Category</small>
    <h2><?php echo htmlspecialchars($topCategory); ?></h2>
  </div>

  <div class="card">
    <small>Avg Order Value</small>
    <h2>₱<?php echo number_format($avgOrderValue, 2); ?></h2>
  </div>

  <div class="card">
    <small>Repeat Customers</small>
    <h2><?php echo number_format($repeatCustomers); ?>%</h2>
  </div>

</div>

<br>

<div class="box">
  <h3>Seller Performance</h3>

  <table>
    <thead>
      <tr>
        <th>Seller</th>
        <th>Revenue</th>
        <th>Orders</th>
        <th>Rating</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($sellerPerformance)): ?>
        <tr><td colspan="4" class="text-muted">No seller data available</td></tr>
      <?php else: ?>
        <?php foreach ($sellerPerformance as $seller): ?>
          <tr>
            <td><?php echo htmlspecialchars($seller['shop_name']); ?></td>
            <td>₱<?php echo number_format($seller['revenue'], 2); ?></td>
            <td><?php echo number_format($seller['order_count']); ?></td>
            <td>N/A</td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

</div>

</div>

</body>
</html>