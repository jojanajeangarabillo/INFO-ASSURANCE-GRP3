<?php
require_once 'auth.php';
require_roles([3, 4]);

require_once __DIR__ . '/admin/db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;

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
<title>Store Dashboard</title>

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
  }

  .btn-brand:hover {
    background: #500b14;
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
</style>
</head>

<body>

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

<h2 class="fw-bold">Store Dashboard</h2>
<p class="text-muted">Here's what's happening with your store today.</p>

<!-- STATS -->
<div class="row g-3 mb-4">

  <div class="col-md-3">
    <div class="card stat-card">
      <span class="badge-soft float-end">Total</span>
      <p class="text-muted mb-1">Total Revenue</p>
      <h3><?php echo formatCurrency($total_revenue); ?></h3>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card">
      <span class="badge-soft float-end">All</span>
      <p class="text-muted mb-1">Orders</p>
      <h3><?php echo $total_orders; ?></h3>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card">
      <span class="badge-soft float-end">Live</span>
      <p class="text-muted mb-1">Active Products</p>
      <h3><?php echo $active_products; ?></h3>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card">
      <span class="badge-soft float-end">Total</span>
      <p class="text-muted mb-1">Store Views</p>
      <h3><?php echo number_format($store_views); ?></h3>
    </div>
  </div>

</div>

<!-- ANALYTICS -->
<div class="row g-4 mb-4">

  <div class="col-lg-8">
    <div class="card p-4">
      <h5 class="fw-bold mb-3">Product Analytics</h5>
      <canvas id="chart"></canvas>
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

        <button type="submit" name="add_product" class="btn btn-brand w-100">+ Add Product</button>
      </form>

    </div>
  </div>

</div>

<!-- TABLES -->
<div class="row g-4">

<!-- PRODUCTS -->
<div class="col-lg-6">
<div class="card p-3">
<h5 class="fw-bold mb-3">Recent Products</h5>

<table class="table">
<thead>
<tr>
<th>Product</th>
<th>Stock</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php if (empty($recent_products)): ?>
  <tr><td colspan="3" class="text-center text-muted">No products found.</td></tr>
<?php else: ?>
  <?php foreach ($recent_products as $p): ?>
  <tr>
    <td><?php echo htmlspecialchars($p['name']); ?></td>
    <td><?php echo $p['total_stock']; ?></td>
    <td><?php echo getStatusBadge($p['status']); ?></td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<!-- ORDERS -->
<div class="col-lg-6">
<div class="card p-3">
<h5 class="fw-bold mb-3">Order Management</h5>

<table class="table">
<thead>
<tr>
<th>Order</th>
<th>Customer</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php if (empty($recent_orders)): ?>
  <tr><td colspan="3" class="text-center text-muted">No orders found.</td></tr>
<?php else: ?>
  <?php foreach ($recent_orders as $o): ?>
  <tr>
    <td><?php echo htmlspecialchars($o['order_number']); ?></td>
    <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
    <td><?php echo getOrderStatusBadge($o['order_status']); ?></td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<!-- CUSTOMERS -->
<div class="col-lg-6">
<div class="card p-3">
<h5 class="fw-bold mb-3">Customer Insights</h5>

<table class="table">
<thead>
<tr>
<th>Name</th>
<th>Orders</th>
<th>Spent</th>
</tr>
</thead>
<tbody>
<?php if (empty($customer_insights)): ?>
  <tr><td colspan="3" class="text-center text-muted">No customer data yet.</td></tr>
<?php else: ?>
  <?php foreach ($customer_insights as $c): ?>
  <tr>
    <td><?php echo htmlspecialchars($c['username']); ?></td>
    <td><?php echo $c['order_count']; ?></td>
    <td class="text-success"><?php echo formatCurrency($c['total_spent']); ?></td>
  </tr>
  <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>  <td>8</td>
  <td class="text-success">₱28,400</td>
</tr>

</tbody>
</table>
</div>
</div>

<!-- METRICS -->
<div class="col-lg-6">
<div class="card p-4">

<h5 class="fw-bold mb-4">Performance Metrics</h5>

<div class="d-flex justify-content-between mb-4">
  <div class="d-flex gap-3">
    <div class="metric-icon bg-primary text-white">⏱</div>
    <div>
      <strong>Response Time</strong><br>
      <small>Average reply time</small>
    </div>
  </div>
  <h4>2 hrs</h4>
</div>

<div class="d-flex justify-content-between mb-4">
  <div class="d-flex gap-3">
    <div class="metric-icon bg-success text-white">✔</div>
    <div>
      <strong>Fulfillment Rate</strong><br>
      <small>Delivered orders</small>
    </div>
  </div>
  <h4 class="text-success">98.5%</h4>
</div>

<div class="d-flex justify-content-between">
  <div class="d-flex gap-3">
    <div class="metric-icon bg-warning text-white">🔄</div>
    <div>
      <strong>Return Rate</strong><br>
      <small>Returned items</small>
    </div>
  </div>
  <h4 class="text-warning">1.2%</h4>
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
  document.getElementById("main").classList.toggle("full");
}

const ctx = document.getElementById('chart').getContext('2d');

new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($labels); ?>,
    datasets: [{
      label: 'Revenue',
      data: <?php echo json_encode($daily_revenue); ?>,
      borderColor: '#6d0f1b',
      backgroundColor: 'rgba(109,15,27,0.1)',
      fill: true,
      tension: 0.4
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    responsive: true,
    maintainAspectRatio: false
  }
});
</script>

</body>
</html>