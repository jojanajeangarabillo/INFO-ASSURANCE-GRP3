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

// Fetch dashboard metrics
$totalUsersStmt = $conn->prepare("SELECT COUNT(*) as count FROM user");
$totalUsersStmt->execute();
$totalUsers = $totalUsersStmt->get_result()->fetch_assoc()['count'];
$totalUsersStmt->close();

$totalSellersStmt = $conn->prepare("SELECT COUNT(*) as count FROM seller");
$totalSellersStmt->execute();
$totalSellers = $totalSellersStmt->get_result()->fetch_assoc()['count'];
$totalSellersStmt->close();

$totalOrdersStmt = $conn->prepare("SELECT COUNT(*) as count FROM orders");
$totalOrdersStmt->execute();
$totalOrders = $totalOrdersStmt->get_result()->fetch_assoc()['count'];
$totalOrdersStmt->close();

$revenueStmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'paid'");
$revenueStmt->execute();
$platformRevenue = $revenueStmt->get_result()->fetch_assoc()['total'];
$revenueStmt->close();

$activeSellersStmt = $conn->prepare("SELECT COUNT(*) as count FROM seller WHERE is_approved = 1");
$activeSellersStmt->execute();
$activeSellers = $activeSellersStmt->get_result()->fetch_assoc()['count'];
$activeSellersStmt->close();

$pendingApprovalsStmt = $conn->prepare("SELECT COUNT(*) as count FROM seller WHERE is_approved = 0");
$pendingApprovalsStmt->execute();
$pendingApprovals = $pendingApprovalsStmt->get_result()->fetch_assoc()['count'];
$pendingApprovalsStmt->close();

$recentActivityStmt = $conn->prepare("SELECT al.*, u.username FROM audit_log al LEFT JOIN user u ON al.user_id = u.user_id ORDER BY al.created_at DESC LIMIT 10");
$recentActivityStmt->execute();
$recentActivity = $recentActivityStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recentActivityStmt->close();

$chartStmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COALESCE(SUM(total_amount), 0) as revenue
    FROM orders 
    WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$chartStmt->execute();
$chartData = $chartStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$chartStmt->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
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
    /* ONLY NON-SIDEBAR STYLES HERE */
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #fdf2f6;
    }

    /* CONTENT */
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

    .grid-4 {
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .grid-3 {
      grid-template-columns: 2fr 1fr;
    }

    /* CARDS */
    .card {
      background: #610C27;
      color: white;
      padding: 20px;
      border-radius: 12px;
    }

    .flex {
      display: flex;
      justify-content: space-between;
    }

    .growth {
      color: #4ade80;
    }

    .warning {
      color: orange;
    }

    /* BOX */
    .box {
      background: #EFECE9;
      padding: 20px;
      border-radius: 12px;
    }

    /* ACTIVITY */
    .activity-item {
      display: flex;
      gap: 10px;
      padding: 10px 0;
      border-bottom: 1px solid #ddd;
    }

    .icon {
      width: 30px;
      text-align: center;
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

    <a href="admin_dashboard.php" class="<?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>"><i
        class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
    <a href="admin_analytics.php" class="<?php echo $current_page == 'admin_analytics.php' ? 'active' : ''; ?>"><i
        class="fas fa-chart-line"></i><span class="text">Analytics</span></a>
    <a href="admin_users.php" class="<?php echo $current_page == 'admin_users.php' ? 'active' : ''; ?>"><i
        class="fas fa-users"></i><span class="text">Users</span></a>
    <a href="admin_auditlogs.php" class="<?php echo $current_page == 'admin_auditlogs.php' ? 'active' : ''; ?>"><i
        class="fas fa-history"></i><span class="text">Audit Logs</span></a>
    <a href="admin_orders.php" class="<?php echo $current_page == 'admin_orders.php' ? 'active' : ''; ?>"><i
        class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
    <a href="admin_reports.php" class="<?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>"><i
        class="fas fa-file-lines"></i><span class="text">Reports</span></a>

    <a href="admin_settings.php" class="<?php echo $current_page == 'admin_settings.php' ? 'active' : ''; ?>"><i
        class="fas fa-gear"></i><span class="text">Settings</span></a>

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

    <h1>Platform Overview</h1>
    <p>Global metrics and system health.</p>

    <div class="grid grid-4">

      <div class="card"><small>Total Users</small>
        <div class="flex">
          <h2><?php echo number_format($totalUsers); ?></h2>
        </div>
      </div>

      <div class="card"><small>Total Sellers</small>
        <div class="flex">
          <h2><?php echo number_format($totalSellers); ?></h2>
        </div>
      </div>

      <div class="card"><small>Platform Revenue</small>
        <div class="flex">
          <h2>₱<?php echo number_format($platformRevenue, 2); ?></h2>
        </div>
      </div>

      <div class="card"><small>Total Orders</small>
        <div class="flex">
          <h2><?php echo number_format($totalOrders); ?></h2>
        </div>
      </div>

      <div class="card"><small>Active Sellers</small>
        <div class="flex">
          <h2><?php echo number_format($activeSellers); ?></h2>
        </div>
      </div>

      <div class="card"><small>Pending Approvals</small>
        <div class="flex">
          <h2><?php echo number_format($pendingApprovals); ?></h2><span class="warning">Needs action</span>
        </div>
      </div>

    </div>

    <br>

    <div class="grid grid-3">

      <div class="box">
        <h3>Revenue Growth</h3>
        <canvas id="chart"></canvas>
      </div>

      <div class="box">
        <h3>Recent Activity</h3>
        <?php if (empty($recentActivity)): ?>
          <p class="text-muted">No recent activity</p>
        <?php else: ?>
          <?php foreach ($recentActivity as $activity): ?>
            <div class="activity-item">
              <div class="icon">👤</div>
              <div>
                <strong><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></strong>
                <small class="text-muted"><?php echo htmlspecialchars($activity['action']); ?></small>
                <div><?php echo htmlspecialchars($activity['description'] ?? $activity['module']); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>

  </div>

  <script>
    const chartLabels = <?php echo json_encode(array_column($chartData, 'month')); ?>;
    const chartValues = <?php echo json_encode(array_column($chartData, 'revenue')); ?>;

    new Chart(document.getElementById('chart'), {
      type: 'line',
      data: {
        labels: chartLabels.map(l => l.substring(5)),
        datasets: [{
          data: chartValues,
          borderColor: '#610C27',
          backgroundColor: 'rgba(97,12,39,0.2)',
          fill: true
        }]
      }
    });
  </script>

</body>

</html>