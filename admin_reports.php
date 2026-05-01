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

// Fetch monthly sales data for the last 6 months
$salesDataStmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%b') as month,
        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as total
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), month
    ORDER BY DATE_FORMAT(created_at, '%Y-%m') ASC
");
$salesDataStmt->execute();
$salesDataResult = $salesDataStmt->get_result();
$salesMonths = [];
$salesValues = [];
while ($sale = $salesDataResult->fetch_assoc()) {
    $salesMonths[] = $sale['month'];
    $salesValues[] = (float)$sale['total'];
}
$salesDataStmt->close();

// Fetch user growth data for the last 6 months
$userGrowthStmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%b') as month,
        COUNT(*) as total
    FROM user
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m'), month
    ORDER BY DATE_FORMAT(created_at, '%Y-%m') ASC
");
$userGrowthStmt->execute();
$userGrowthResult = $userGrowthStmt->get_result();
$userMonths = [];
$userValues = [];
while ($user = $userGrowthResult->fetch_assoc()) {
    $userMonths[] = $user['month'];
    $userValues[] = (int)$user['total'];
}
$userGrowthStmt->close();

// Fetch recent orders
$recentOrdersStmt = $conn->prepare("
    SELECT 
        o.order_id,
        o.order_number,
        o.total_amount,
        o.order_status,
        o.created_at,
        p.category_gender
    FROM orders o
    LEFT JOIN order_item oi ON o.order_id = oi.order_id
    LEFT JOIN product p ON oi.product_id = p.product_id
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recentOrdersStmt->execute();
$recentOrders = $recentOrdersStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recentOrdersStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Reports</title>
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

.container {
  margin-left: 240px;
  padding: 20px;
  transition: margin-left 0.3s ease;
}

.container.full {
  margin-left: 70px;
}

.grid {
  display: grid;
  gap: 20px;
}

.grid-3 {
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.card {
  background: #610C27;
  color: white;
  padding: 20px;
  border-radius: 12px;
}

.box {
  background: #EFECE9;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background: #f9dbe5;
  color: #610C27;
}
h1 {
  color: #610C27;
}

.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
}
.success { background: #d1fae5; color: #065f46; }
.warning { background: #fef3c7; color: #92400e; }
.danger { background: #fee2e2; color: #991b1b; }
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

<h1>Analytics & Reports</h1>
<p>Detailed performance reports and sales data.</p>

<div class="grid grid-3">
  <div class="box">
    <h3>Monthly Sales Report</h3>
    <canvas id="salesChart"></canvas>
  </div>
  <div class="box">
    <h3>User Growth Report</h3>
    <canvas id="userChart"></canvas>
  </div>
</div>

<br>

<div class="box">
  <h3>Recent Sales Data</h3>
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Order ID</th>
        <th>Category</th>
        <th>Amount</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($recentOrders)): ?>
        <tr><td colspan="5" style="text-align:center; padding:20px; color:#999;">No orders found.</td></tr>
      <?php else: ?>
        <?php foreach ($recentOrders as $order): ?>
          <?php 
            $statusClass = 'warning';
            if (in_array($order['order_status'], ['delivered', 'shipped'])) $statusClass = 'success';
            elseif (in_array($order['order_status'], ['cancelled', 'returned'])) $statusClass = 'danger';
          ?>
          <tr>
            <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
            <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
            <td><?php echo htmlspecialchars($order['category_gender'] ?? 'N/A'); ?></td>
            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
            <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</div>

<script>
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($salesMonths); ?>,
    datasets: [{
      label: 'Sales (₱)',
      data: <?php echo json_encode($salesValues); ?>,
      backgroundColor: '#610C27'
    }]
  }
});

const userCtx = document.getElementById('userChart').getContext('2d');
new Chart(userCtx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($userMonths); ?>,
    datasets: [{
      label: 'New Users',
      data: <?php echo json_encode($userValues); ?>,
      borderColor: '#a61b4a',
      fill: false
    }]
  }
});
</script>

</body>
</html>