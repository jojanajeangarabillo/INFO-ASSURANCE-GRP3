<?php
require_once 'auth.php';
require_roles([3, 4]); // Allow Seller Only (3) and Dual Role (4)

require_once __DIR__ . '/admin/db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$role_id = $_SESSION['role_id'] ?? 0;
$active_role = $_SESSION['active_role'] ?? 'seller';

// Check if user has dual role (role_id = 4) and is in seller mode
$isDualRole = ($role_id == 4);
$showSwitchButton = $isDualRole; // Only show switch button for dual role users

// Handle Switch Role action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'switch_role') {
    if ($isDualRole) {
        $_SESSION['active_role'] = 'customer';
        header("Location: customer_home.php");
        exit;
    }
}

$seller_stmt = $conn->prepare("SELECT seller_id, shop_name FROM seller WHERE user_id = ?");
$seller_stmt->bind_param("i", $user_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_id = $seller['seller_id'] ?? 0;
$shop_name = $seller['shop_name'] ?? 'My Store';
$seller_stmt->close();

$total_revenue = 0;
$revenue_stmt = $conn->prepare("
    SELECT COALESCE(SUM(oi.line_total), 0) as total_revenue 
    FROM order_item oi 
    WHERE oi.seller_id = ?
");
$revenue_stmt->bind_param("i", $seller_id);
$revenue_stmt->execute();
$revenue_result = $revenue_stmt->get_result()->fetch_assoc();
$total_revenue = $revenue_result['total_revenue'] ?? 0;
$revenue_stmt->close();

$total_orders = 0;
$orders_count_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT oi.order_id) as order_count 
    FROM order_item oi 
    WHERE oi.seller_id = ?
");
$orders_count_stmt->bind_param("i", $seller_id);
$orders_count_stmt->execute();
$orders_count_result = $orders_count_stmt->get_result()->fetch_assoc();
$total_orders = $orders_count_result['order_count'] ?? 0;
$orders_count_stmt->close();

$active_products = 0;
$products_count_stmt = $conn->prepare("
    SELECT COUNT(*) as product_count 
    FROM product 
    WHERE seller_id = ? AND status = 'active'
");
$products_count_stmt->bind_param("i", $seller_id);
$products_count_stmt->execute();
$products_count_result = $products_count_stmt->get_result()->fetch_assoc();
$active_products = $products_count_result['product_count'] ?? 0;
$products_count_stmt->close();

$store_views = 0;
/* 
$views_stmt = $conn->prepare("
    SELECT COALESCE(SUM(v.view_count), 0) as views 
    FROM product_views v 
    JOIN product p ON v.product_id = p.product_id 
    WHERE p.seller_id = ?
");
$views_stmt->bind_param("i", $seller_id);
$views_stmt->execute();
$views_result = $views_stmt->get_result()->fetch_assoc();
$store_views = $views_result['views'] ?? 0;
$views_stmt->close();
*/

$recent_products = [];
$recent_products_stmt = $conn->prepare("
    SELECT p.product_id, p.name, p.status, 
           COALESCE(SUM(pv.stock_qty), 0) as total_stock,
           COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(DISTINCT r.review_id) as review_count
    FROM product p
    LEFT JOIN product_variant pv ON p.product_id = pv.product_id
    LEFT JOIN review r ON p.product_id = r.product_id AND r.review_status = 'active'
    WHERE p.seller_id = ?
    GROUP BY p.product_id, p.name, p.status
    ORDER BY p.created_at DESC
    LIMIT 5
");
$recent_products_stmt->bind_param("i", $seller_id);
$recent_products_stmt->execute();
$recent_products_result = $recent_products_stmt->get_result();
while ($row = $recent_products_result->fetch_assoc()) {
    $recent_products[] = $row;
}
$recent_products_stmt->close();

$recent_orders = [];
$recent_orders_stmt = $conn->prepare("
    SELECT o.order_id, o.order_number, o.order_status, o.created_at,
           u.username as customer_name
    FROM orders o
    JOIN order_item oi ON o.order_id = oi.order_id
    JOIN user u ON o.customer_id = u.user_id
    WHERE oi.seller_id = ?
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$recent_orders_stmt->bind_param("i", $seller_id);
$recent_orders_stmt->execute();
$recent_orders_result = $recent_orders_stmt->get_result();
while ($row = $recent_orders_result->fetch_assoc()) {
    $recent_orders[] = $row;
}
$recent_orders_stmt->close();

$customer_insights = [];
$insights_stmt = $conn->prepare("
    SELECT u.user_id, u.username, 
           COUNT(DISTINCT oi.order_id) as order_count,
           SUM(oi.line_total) as total_spent
    FROM order_item oi
    JOIN orders o ON oi.order_id = o.order_id
    JOIN user u ON o.customer_id = u.user_id
    WHERE oi.seller_id = ? AND o.payment_status = 'paid'
    GROUP BY u.user_id, u.username
    ORDER BY total_spent DESC
    LIMIT 5
");
$insights_stmt->bind_param("i", $seller_id);
$insights_stmt->execute();
$insights_result = $insights_stmt->get_result();
while ($row = $insights_result->fetch_assoc()) {
    $customer_insights[] = $row;
}
$insights_stmt->close();

// Fetch last 7 days revenue for chart
$daily_revenue = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D', strtotime($date));
    
    $rev_stmt = $conn->prepare("
        SELECT SUM(oi.line_total) as total
        FROM order_item oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE oi.seller_id = ? 
        AND DATE(o.created_at) = ?
        AND o.payment_status = 'paid'
    ");
    $rev_stmt->bind_param("is", $seller_id, $date);
    $rev_stmt->execute();
    $rev_res = $rev_stmt->get_result()->fetch_assoc();
    $daily_revenue[] = (float)($rev_res['total'] ?? 0);
    $rev_stmt->close();
}

function formatCurrency($amount) {
    return '₱' . number_format((float)$amount, 2);
}

function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge-soft">Active</span>',
        'inactive' => '<span class="badge-red">Inactive</span>',
        'draft' => '<span class="badge-secondary">Draft</span>',
        'archived' => '<span class="badge-secondary">Archived</span>'
    ];
    return $badges[$status] ?? '<span class="badge-soft">' . ucfirst($status) . '</span>';
}

function getOrderStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge-status pending">Pending</span>',
        'paid' => '<span class="badge-status transit">Paid</span>',
        'processing' => '<span class="badge-status transit">Processing</span>',
        'packed' => '<span class="badge-status transit">Packed</span>',
        'shipped' => '<span class="badge-status transit">Shipped</span>',
        'out_for_delivery' => '<span class="badge-status transit">Out for Delivery</span>',
        'delivered' => '<span class="badge-status delivered">Delivered</span>',
        'cancelled' => '<span class="badge-status" style="background:#fde8e8;color:#dc2626;">Cancelled</span>',
        'returned' => '<span class="badge-status" style="background:#fde8e8;color:#dc2626;">Returned</span>'
    ];
    return $badges[$status] ?? '<span class="badge-status pending">' . ucfirst($status) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Store Dashboard - J3RS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
  body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }

  .main-content {
    margin-left: 240px;
    transition: 0.3s;
    padding: 20px;
  }

  .sidebar.collapsed ~ .main-content {
    margin-left: 70px;
  }

  .card {
    border-radius: 16px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  }

  .stat-card {
    border-left: 5px solid #6d0f1b;
    padding: 20px;
  }

  .badge-soft {
    background: #e6f7ef;
    color: #16a34a;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 20px;
  }

  .badge-red {
    background: #fde8e8;
    color: #dc2626;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 20px;
  }

  .btn-brand {
    background: #6d0f1b;
    color: white;
    border-radius: 10px;
    transition: all 0.3s;
  }

  .btn-brand:hover {
    background: #500b14;
    transform: translateY(-2px);
  }

  .btn-outline-brand {
    background: transparent;
    color: #6d0f1b;
    border: 2px solid #6d0f1b;
    border-radius: 10px;
    transition: all 0.3s;
  }

  .btn-outline-brand:hover {
    background: #6d0f1b;
    color: white;
    transform: translateY(-2px);
  }

  .metric-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display:flex;
    align-items:center;
    justify-content:center;
  }

  #chart {
    height: 300px !important;
  }

  .switch-role-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    background: #6d0f1b;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s;
  }

  .switch-role-btn:hover {
    background: #500b14;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
  }

  .role-badge {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    background: #6d0f1b;
    color: white;
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .role-badge i {
    margin-right: 8px;
  }

  .welcome-banner {
    background: linear-gradient(135deg, #6d0f1b 0%, #8b1a35 100%);
    color: white;
    border-radius: 16px;
    padding: 20px 25px;
    margin-bottom: 25px;
  }

  .welcome-banner h2 {
    margin: 0;
    font-size: 24px;
  }

  .welcome-banner p {
    margin: 5px 0 0;
    opacity: 0.9;
  }
</style>
</head>

<body>

<!-- ROLE BADGE (Only for Dual Role users) -->
<?php if ($isDualRole): ?>
<div class="role-badge">
  <i class="bi bi-arrow-left-right"></i>
  Dual Role Mode: <strong>Seller View</strong>
</div>
<?php endif; ?>

<!-- SWITCH TO CUSTOMER BUTTON (Only for Dual Role users) -->
<?php if ($showSwitchButton): ?>
<form method="POST" class="switch-role-btn">
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
  <input type="hidden" name="action" value="switch_role">
  <button type="submit" style="background: none; border: none; color: white; width: 100%;">
    <i class="bi bi-arrow-repeat me-2"></i>
    Switch to Customer Mode
  </button>
</form>
<?php endif; ?>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="logo-text">Seller</div>
  </div>

  <a href="seller_dashboard.php" class="active"><i class="bi bi-speedometer2"></i><span class="text">Dashboard</span></a>
  <a href="seller_products.php"><i class="bi bi-box-seam"></i><span class="text">Products</span></a>
  <a href="seller_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
  <a href="seller_inventory.php"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
  <a href="seller_reviews.php"><i class="bi bi-star"></i><span class="text">Reviews</span></a>
  <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
  <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<!-- MAIN -->
<div class="main-content" id="main">

<div class="container-fluid">

<!-- Welcome Banner -->
<div class="welcome-banner">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h2>Welcome back, <?php echo htmlspecialchars($shop_name); ?>!</h2>
      <p>Here's what's happening with your store today.</p>
    </div>
    <?php if ($isDualRole): ?>
    <div class="text-end">
      <span class="badge bg-light text-dark rounded-pill px-3 py-2">
        <i class="bi bi-person-bounding-box me-1"></i> Dual Role Active
      </span>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- STATS -->
<div class="row g-3 mb-4">

  <div class="col-md-3">
    <div class="card stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <p class="text-muted mb-1">Total Revenue</p>
          <h3 class="mb-0"><?php echo formatCurrency($total_revenue); ?></h3>
        </div>
        <div class="metric-icon bg-success bg-opacity-10 text-success">
          <i class="bi bi-currency-dollar fs-4"></i>
        </div>
      </div>
      <small class="text-muted mt-2">Lifetime sales</small>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <p class="text-muted mb-1">Total Orders</p>
          <h3 class="mb-0"><?php echo $total_orders; ?></h3>
        </div>
        <div class="metric-icon bg-primary bg-opacity-10 text-primary">
          <i class="bi bi-bag-check fs-4"></i>
        </div>
      </div>
      <small class="text-muted mt-2">All time orders</small>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <p class="text-muted mb-1">Active Products</p>
          <h3 class="mb-0"><?php echo $active_products; ?></h3>
        </div>
        <div class="metric-icon bg-warning bg-opacity-10 text-warning">
          <i class="bi bi-box-seam fs-4"></i>
        </div>
      </div>
      <small class="text-muted mt-2">Currently selling</small>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <p class="text-muted mb-1">Store Views</p>
          <h3 class="mb-0"><?php echo number_format($store_views); ?></h3>
        </div>
        <div class="metric-icon bg-info bg-opacity-10 text-info">
          <i class="bi bi-eye fs-4"></i>
        </div>
      </div>
      <small class="text-muted mt-2">Total visits</small>
    </div>
  </div>

</div>

<!-- ANALYTICS -->
<div class="row g-4 mb-4">

  <div class="col-lg-8">
    <div class="card p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Weekly Revenue Trend</h5>
        <span class="badge-soft">Last 7 days</span>
      </div>
      <canvas id="chart" style="height: 300px;"></canvas>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card p-4">
      <h5 class="fw-bold mb-3">Quick Add Product</h5>

      <form action="seller_products.php" method="POST">
        <input name="name" class="form-control mb-2" placeholder="Product Name" required>

        <div class="row g-2 mb-2">
          <div class="col">
            <input name="price" class="form-control" placeholder="Price" type="number" step="0.01" required>
          </div>
          <div class="col">
            <input name="stock" class="form-control" placeholder="Stock" type="number" required>
          </div>
        </div>

        <textarea name="description" class="form-control mb-3" placeholder="Short description" rows="2" required></textarea>

        <button type="submit" name="add_product" class="btn btn-brand w-100">
          <i class="bi bi-plus-circle me-2"></i>Add Product
        </button>
      </form>

      <hr class="my-3">
      
      <div class="text-center">
        <a href="seller_products.php" class="btn btn-outline-brand w-100">
          <i class="bi bi-grid-3x3-gap-fill me-2"></i>Manage All Products
        </a>
      </div>
    </div>
  </div>

</div>

<!-- TABLES -->
<div class="row g-4">

<!-- PRODUCTS -->
<div class="col-lg-6">
<div class="card p-3">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-bold mb-0">Recent Products</h5>
  <a href="seller_products.php" class="text-decoration-none small">View all <i class="bi bi-arrow-right"></i></a>
</div>

<div class="table-responsive">
<table class="table table-hover">
<thead class="table-light">
<tr>
<th>Product</th>
<th>Stock</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php if (empty($recent_products)): ?>
  <tr><td colspan="3" class="text-center text-muted py-4">No products found.</td></tr>
<?php else: ?>
  <?php foreach ($recent_products as $p): ?>
  <tr>
    <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
    <td><?php echo $p['total_stock']; ?> units</td>
    <td><?php echo getStatusBadge($p['status']); ?></td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- ORDERS -->
<div class="col-lg-6">
<div class="card p-3">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-bold mb-0">Recent Orders</h5>
  <a href="seller_orders.php" class="text-decoration-none small">View all <i class="bi bi-arrow-right"></i></a>
</div>

<div class="table-responsive">
<table class="table table-hover">
<thead class="table-light">
<tr>
<th>Order #</th>
<th>Customer</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php if (empty($recent_orders)): ?>
  <tr><td colspan="3" class="text-center text-muted py-4">No orders found.</td></tr>
<?php else: ?>
  <?php foreach ($recent_orders as $o): ?>
  <tr>
    <td><strong><?php echo htmlspecialchars($o['order_number']); ?></strong></td>
    <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
    <td><?php echo getOrderStatusBadge($o['order_status']); ?></td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- CUSTOMER INSIGHTS -->
<div class="col-lg-6">
<div class="card p-3">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-bold mb-0">Top Customers</h5>
  <span class="badge-soft">By total spent</span>
</div>

<div class="table-responsive">
<table class="table table-hover">
<thead class="table-light">
<tr>
<th>Customer</th>
<th>Orders</th>
<th>Total Spent</th>
</tr>
</thead>
<tbody>
<?php if (empty($customer_insights)): ?>
  <tr><td colspan="3" class="text-center text-muted py-4">No customer data yet.</td></tr>
<?php else: ?>
  <?php foreach ($customer_insights as $c): ?>
  <tr>
    <td><i class="bi bi-person-circle me-2"></i><?php echo htmlspecialchars($c['username']); ?></td>
    <td><?php echo $c['order_count']; ?></td>
    <td class="text-success fw-bold"><?php echo formatCurrency($c['total_spent']); ?></td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- PERFORMANCE METRICS -->
<div class="col-lg-6">
<div class="card p-4">

<h5 class="fw-bold mb-4">Store Performance Metrics</h5>

<div class="d-flex justify-content-between mb-4">
  <div class="d-flex gap-3">
    <div class="metric-icon bg-primary text-white">
      <i class="bi bi-clock-history"></i>
    </div>
    <div>
      <strong>Avg Response Time</strong><br>
      <small class="text-muted">Customer inquiry response</small>
    </div>
  </div>
  <h4 class="mb-0">2.5 hrs</h4>
</div>

<div class="d-flex justify-content-between mb-4">
  <div class="d-flex gap-3">
    <div class="metric-icon bg-success text-white">
      <i class="bi bi-check-lg"></i>
    </div>
    <div>
      <strong>Fulfillment Rate</strong><br>
      <small class="text-muted">Orders delivered on time</small>
    </div>
  </div>
  <h4 class="mb-0 text-success">98.5%</h4>
</div>

<div class="d-flex justify-content-between mb-4">
  <div class="d-flex gap-3">
    <div class="metric-icon bg-info text-white">
      <i class="bi bi-star-fill"></i>
    </div>
    <div>
      <strong>Avg Customer Rating</strong><br>
      <small class="text-muted">Based on customer reviews</small>
    </div>
  </div>
  <h4 class="mb-0 text-warning">4.8 ★</h4>
</div>

<div class="d-flex justify-content-between">
  <div class="d-flex gap-3">
    <div class="metric-icon bg-danger text-white">
      <i class="bi bi-arrow-return-left"></i>
    </div>
    <div>
      <strong>Return Rate</strong><br>
      <small class="text-muted">Items returned by customers</small>
    </div>
  </div>
  <h4 class="mb-0 text-danger">1.2%</h4>
</div>

</div>
</div>

</div>
</div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
}

const ctx = document.getElementById('chart').getContext('2d');

new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($labels); ?>,
    datasets: [{
      label: 'Revenue (₱)',
      data: <?php echo json_encode($daily_revenue); ?>,
      borderColor: '#6d0f1b',
      backgroundColor: 'rgba(109,15,27,0.1)',
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#6d0f1b',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6
    }]
  },
  options: {
    plugins: { 
      legend: { 
        display: true,
        position: 'top',
        labels: {
          font: { size: 12 }
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return 'Revenue: ₱' + context.parsed.y.toLocaleString();
          }
        }
      }
    },
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return '₱' + value.toLocaleString();
          }
        }
      }
    }
  }
});

// Auto-dismiss any alerts if present
setTimeout(function() {
  let alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(alert) {
    let bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);
</script>

</body>
</html>