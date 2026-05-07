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

if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout_minutes * 60) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['last_activity'] = time();

$current_page = basename($_SERVER['PHP_SELF']);

// Get logistics user info
$logistics_stmt = $conn->prepare("
    SELECT username, first_name, last_name, email 
    FROM user WHERE user_id = ? AND role_id = 5
");
$logistics_stmt->bind_param("i", $user_id);
$logistics_stmt->execute();
$logistics_user = $logistics_stmt->get_result()->fetch_assoc();
$logistics_stmt->close();

// Date filters
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');

// Get orders assigned to this logistics user
$orders_query = $conn->prepare("
    SELECT o.order_id, o.order_number, o.order_status, o.created_at, o.updated_at,
           o.total_amount, o.shipping_amount,
           dt.status as delivery_status, dt.created_at as tracking_date,
           da.driver_id,
           CONCAT(COALESCE(driver_user.first_name, ''), ' ', COALESCE(driver_user.last_name, '')) as driver_name,
           da.status as driver_status, da.assigned_at, da.accepted_at, da.delivered_at
    FROM orders o
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    LEFT JOIN driver_assignment da ON o.order_id = da.order_id AND da.status != 'cancelled'
    LEFT JOIN driver d ON da.driver_id = d.driver_id
    LEFT JOIN user driver_user ON d.user_id = driver_user.user_id
    WHERE dt.logistic_user_id = ?
    AND DATE(o.created_at) BETWEEN ? AND ?
    ORDER BY o.created_at DESC
");
$orders_query->bind_param("iss", $user_id, $date_from, $date_to);
$orders_query->execute();
$orders_result = $orders_query->get_result();

$orders = [];
$total_orders = 0;
$total_delivered = 0;
$total_shipping = 0;
$total_value = 0;
$delivery_times = [];

while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
    $total_orders++;
    $total_value += $row['total_amount'];
    $total_shipping += $row['shipping_amount'];
    
    if ($row['order_status'] == 'delivered') {
        $total_delivered++;
        
        // Calculate delivery time in hours (using assigned_at to delivered_at)
        if ($row['assigned_at'] && $row['delivered_at']) {
            $assigned = strtotime($row['assigned_at']);
            $delivered = strtotime($row['delivered_at']);
            $hours = round(($delivered - $assigned) / 3600, 1);
            $delivery_times[] = $hours;
        }
    }
}

// Calculate average delivery time
$avg_delivery_time = !empty($delivery_times) ? round(array_sum($delivery_times) / count($delivery_times), 1) : 0;

// Get order status breakdown
$status_query = $conn->prepare("
    SELECT o.order_status, COUNT(*) as count
    FROM orders o
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE dt.logistic_user_id = ?
    AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY o.order_status
");
$status_query->bind_param("iss", $user_id, $date_from, $date_to);
$status_query->execute();
$status_result = $status_query->get_result();

$status_breakdown = [
    'pending' => 0,
    'processing' => 0,
    'shipped' => 0,
    'out_for_delivery' => 0,
    'delivered' => 0,
    'cancelled' => 0
];

while ($row = $status_result->fetch_assoc()) {
    if (isset($status_breakdown[$row['order_status']])) {
        $status_breakdown[$row['order_status']] = $row['count'];
    }
}
$status_query->close();

// Get daily delivery trends
$daily_query = $conn->prepare("
    SELECT DATE(o.created_at) as date, 
           COUNT(*) as total_orders,
           SUM(CASE WHEN o.order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
    FROM orders o
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE dt.logistic_user_id = ?
    AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY DATE(o.created_at)
    ORDER BY date ASC
");
$daily_query->bind_param("iss", $user_id, $date_from, $date_to);
$daily_query->execute();
$daily_result = $daily_query->get_result();

$chart_dates = [];
$chart_orders = [];
$chart_delivered = [];

while ($row = $daily_result->fetch_assoc()) {
    $chart_dates[] = date('M d', strtotime($row['date']));
    $chart_orders[] = $row['total_orders'];
    $chart_delivered[] = $row['delivered_orders'];
}
$daily_query->close();

// Get driver performance
$driver_query = $conn->prepare("
    SELECT 
        CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as driver_name,
        u.username,
        COUNT(da.assignment_id) as total_deliveries,
        SUM(CASE WHEN da.status = 'delivered' THEN 1 ELSE 0 END) as completed_deliveries,
        ROUND(AVG(CASE WHEN da.delivered_at IS NOT NULL AND da.assigned_at IS NOT NULL 
            THEN TIMESTAMPDIFF(HOUR, da.assigned_at, da.delivered_at) 
            ELSE NULL END), 1) as avg_delivery_hours
    FROM driver d
    JOIN user u ON d.user_id = u.user_id
    LEFT JOIN driver_assignment da ON d.driver_id = da.driver_id
    LEFT JOIN orders o ON da.order_id = o.order_id
    LEFT JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE d.logistics_id = ?
    AND dt.logistic_user_id = ?
    AND (da.assigned_at BETWEEN ? AND ? OR da.assigned_at IS NULL)
    GROUP BY d.driver_id
    ORDER BY completed_deliveries DESC
");

// FIX
$date_from_datetime = $date_from . ' 00:00:00';
$date_to_datetime = $date_to . ' 23:59:59';

$driver_query->bind_param(
    "iiss",
    $user_id,
    $user_id,
    $date_from_datetime,
    $date_to_datetime
);

$driver_query->execute();
$drivers = $driver_query->get_result();
$driver_query->close();

// Delivery completion rate
$completion_rate = $total_orders > 0 ? round(($total_delivered / $total_orders) * 100, 1) : 0;

// Get recent deliveries for table
$recent_query = $conn->prepare("
    SELECT o.order_number, 
           CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as customer_name,
           o.order_status, o.created_at,
           CONCAT(COALESCE(driver_user.first_name, ''), ' ', COALESCE(driver_user.last_name, '')) as driver_name,
           CASE 
               WHEN da.delivered_at IS NOT NULL AND da.assigned_at IS NOT NULL 
               THEN TIMESTAMPDIFF(HOUR, da.assigned_at, da.delivered_at)
               ELSE NULL 
           END as delivery_hours
    FROM orders o
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    JOIN user u ON o.customer_id = u.user_id
    LEFT JOIN driver_assignment da ON o.order_id = da.order_id
    LEFT JOIN driver d ON da.driver_id = d.driver_id
    LEFT JOIN user driver_user ON d.user_id = driver_user.user_id
    WHERE dt.logistic_user_id = ?
    AND DATE(o.created_at) BETWEEN ? AND ?
    ORDER BY o.created_at DESC
    LIMIT 20
");
$recent_query->bind_param("iss", $user_id, $date_from, $date_to);
$recent_query->execute();
$recent_orders = $recent_query->get_result();
$recent_query->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logistics Reports</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="sidebar.css">

<style>
body {
  background: #fdf2f6;
  font-family: Arial, sans-serif;
}

.main-content {
  margin-left: 240px;
  padding: 40px 60px;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 70px;
}

.text-brand { color: #610C27; }
.bg-brand { background: #610C27; color: #fff; }

.card {
  border-radius: 16px;
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.progress {
  height: 8px;
  border-radius: 10px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-3px); }
.stat-number { font-size: 32px; font-weight: bold; color: #610C27; }
.stat-label { color: #666; font-size: 13px; margin-top: 5px; }

.filter-bar {
  background: white;
  border-radius: 12px;
  padding: 15px 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.table th {
  font-size: 12px;
  color: #666;
  text-transform: uppercase;
}

.btn-brand {
  background-color: #610C27;
  color: white;
  border: none;
}
.btn-brand:hover {
  background-color: #8a1423;
  color: white;
}

.badge-status {
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}
.badge-delivered { background: #d1fae5; color: #059669; }
.badge-processing { background: #fff8e6; color: #ffa000; }
.badge-shipped { background: #eef4ff; color: #3b82f6; }
.badge-out_for_delivery { background: #fff0f6; color: #ec4899; }
.badge-pending { background: #fef3c7; color: #d97706; }
.badge-cancelled { background: #fee2e2; color: #dc2626; }
</style>
</head>

<body>

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

  <a href="logi_drivers.php"><i class="fas fa-truck"></i><span class="text">Drivers</span></a>

  <a href="logi_reports.php" class="active">
    <i class="fas fa-file-lines"></i><span class="text">Reports</span>
  </a>

  <a href="logi_settings.php">
    <i class="fas fa-gear"></i><span class="text">Settings</span>
  </a>
  
  <a href="logout.php" class="logout">
    <i class="fas fa-right-from-bracket"></i><span class="text">Logout</span>
  </a>
</div>

<div class="main-content" id="main">

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold text-brand">Logistics Reports</h2>
    <p class="text-muted mb-0">Analyze delivery performance and metrics for <?php echo htmlspecialchars($logistics_user['first_name'] ?: $logistics_user['username']); ?></p>
  </div>
</div>

<!-- Date Filter -->
<div class="filter-bar">
  <form method="GET" class="row g-3 align-items-end">
    <div class="col-md-4">
      <label class="form-label fw-bold small">Date From</label>
      <input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label fw-bold small">Date To</label>
      <input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>">
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn btn-brand w-100">
        <i class="fas fa-filter"></i> Apply Filter
      </button>
    </div>
  </form>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-number"><?php echo number_format($total_orders); ?></div>
      <div class="stat-label"><i class="fas fa-box"></i> Total Orders</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-number"><?php echo number_format($total_delivered); ?></div>
      <div class="stat-label"><i class="fas fa-check-circle text-success"></i> Delivered</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-number"><?php echo $completion_rate; ?>%</div>
      <div class="stat-label"><i class="fas fa-chart-line"></i> Completion Rate</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-number"><?php echo $avg_delivery_time; ?></div>
      <div class="stat-label"><i class="fas fa-clock"></i> Avg Delivery (Hours)</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Delivery Trend Chart -->
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="fw-bold text-brand mb-4">
          <i class="fas fa-chart-line"></i> Delivery Trends
        </h5>
        <canvas id="trendChart" height="250"></canvas>
      </div>
    </div>
  </div>

  <!-- Status Breakdown -->
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="fw-bold text-brand mb-4">
          <i class="fas fa-chart-pie"></i> Order Status
        </h5>
        <canvas id="statusChart" height="250"></canvas>
        <div class="mt-3">
          <?php foreach ($status_breakdown as $status => $count): 
            if ($count > 0): ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
              <span class="fw-bold"><?php echo $count; ?> (<?php echo $total_orders > 0 ? round(($count / $total_orders) * 100, 1) : 0; ?>%)</span>
            </div>
            <div class="progress mb-2">
              <div class="progress-bar bg-brand" style="width: <?php echo $total_orders > 0 ? ($count / $total_orders) * 100 : 0; ?>%"></div>
            </div>
          <?php endif; endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Driver Performance Table -->
<div class="card shadow-sm mt-4">
  <div class="card-body">
    <h5 class="fw-bold text-brand mb-4">
      <i class="fas fa-truck"></i> Driver Performance
    </h5>
    <div class="table-responsive">
      <table class="table">
        <thead class="table-light">
          <tr>
            <th>Driver Name</th>
            <th>Username</th>
            <th>Total Deliveries</th>
            <th>Completed</th>
            <th>Completion Rate</th>
            <th>Avg Delivery Time</th>
          <tr>
        </thead>
        <tbody>
          <?php if ($drivers->num_rows == 0): ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No driver data found for this period.</td>
            </tr>
          <?php else: 
            while($driver = $drivers->fetch_assoc()): 
              $driver_rate = $driver['total_deliveries'] > 0 ? round(($driver['completed_deliveries'] / $driver['total_deliveries']) * 100, 1) : 0;
          ?>
            <tr>
              <td><?php echo htmlspecialchars($driver['driver_name'] ?: $driver['username']); ?></td>
              <td><code><?php echo htmlspecialchars($driver['username']); ?></code></td>
              <td><?php echo number_format($driver['total_deliveries']); ?></td>
              <td><?php echo number_format($driver['completed_deliveries']); ?></td>
              <td class="<?php echo $driver_rate >= 90 ? 'text-success' : ($driver_rate >= 70 ? 'text-warning' : 'text-danger'); ?> fw-bold">
                <?php echo $driver_rate; ?>%
               </td>
              <td><?php echo $driver['avg_delivery_hours'] ?: 'N/A'; ?> hrs</td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
       </table>
    </div>
  </div>
</div>

<!-- Recent Deliveries -->
<div class="card shadow-sm mt-4">
  <div class="card-body">
    <h5 class="fw-bold text-brand mb-4">
      <i class="fas fa-clock"></i> Recent Deliveries
    </h5>
    <div class="table-responsive">
      <table class="table">
        <thead class="table-light">
          <tr>
            <th>Order #</th>
            <th>Customer</th>
            <th>Driver</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Delivery Time</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($recent_orders->num_rows == 0): ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No orders found for this period.</td>
            </tr>
          <?php else: 
            while($order = $recent_orders->fetch_assoc()): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
              <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
              <td><?php echo htmlspecialchars($order['driver_name'] ?: 'Not assigned'); ?></td>
              <td>
                <span class="badge-status badge-<?php echo str_replace('_', '', $order['order_status']); ?>">
                  <?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?>
                </span>
                </td>
              <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
              <td>
                <?php if ($order['delivery_hours']): ?>
                  <?php echo $order['delivery_hours']; ?> hours
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
                </td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
       </table>
    </div>
  </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Toggle sidebar
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

// Delivery Trend Chart
new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: <?php echo json_encode($chart_dates); ?>,
    datasets: [
      {
        label: 'Total Orders',
        data: <?php echo json_encode($chart_orders); ?>,
        borderColor: '#610C27',
        backgroundColor: 'rgba(97, 12, 39, 0.1)',
        fill: true,
        tension: 0.4
      },
      {
        label: 'Delivered',
        data: <?php echo json_encode($chart_delivered); ?>,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        fill: true,
        tension: 0.4
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: { position: 'top' }
    }
  }
});

// Status Breakdown Pie Chart
new Chart(document.getElementById('statusChart'), {
  type: 'pie',
  data: {
    labels: ['Pending', 'Processing', 'In Transit', 'Out for Delivery', 'Delivered', 'Cancelled'],
    datasets: [{
      data: [
        <?php echo $status_breakdown['pending']; ?>,
        <?php echo $status_breakdown['processing']; ?>,
        <?php echo $status_breakdown['shipped']; ?>,
        <?php echo $status_breakdown['out_for_delivery']; ?>,
        <?php echo $status_breakdown['delivered']; ?>,
        <?php echo $status_breakdown['cancelled']; ?>
      ],
      backgroundColor: ['#fef3c7', '#fff8e6', '#eef4ff', '#fff0f6', '#d1fae5', '#fee2e2'],
      borderColor: '#fff',
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});
</script>

</body>
</html>