<?php
require_once 'auth.php';
require_role(1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Management</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="sidebar.css">

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
  <a href="admin_product.php" class="<?php echo $current_page == 'admin_product.php' ? 'active' : ''; ?>"><i class="fas fa-box"></i><span class="text">Products</span></a>
  <a href="admin_orders.php" class="<?php echo $current_page == 'admin_orders.php' ? 'active' : ''; ?>"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="admin_reports.php" class="<?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="admin_security.php" class="<?php echo $current_page == 'admin_security.php' ? 'active' : ''; ?>"><i class="fas fa-shield-halved"></i><span class="text">Security</span></a>
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
    <h2>12,490</h2>
  </div>

  <div class="card">
    <small>Total Revenue</small>
    <h2>₱845.2K</h2>
  </div>

  <div class="card">
    <small>Pending Fulfillment</small>
    <h2>342</h2>
  </div>

</div>

<br>

<!-- SEARCH -->
<div class="search-box">
  <input type="text" placeholder="Search Order ID or Customer Email">
</div>

<!-- TABLE -->
<table>
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

<tr>
  <td>#GLB-991</td>
  <td>user1@email.com</td>
  <td>₱245.00</td>
  <td><span class="badge success">Paid</span></td>
  <td><span class="badge success">Safe</span></td>
  <td><span class="badge warning">Processing</span></td>
  <td><button class="btn">Invoice</button></td>
</tr>

<tr>
  <td>#GLB-992</td>
  <td>user2@email.com</td>
  <td>₱89.50</td>
  <td><span class="badge success">Paid</span></td>
  <td><span class="badge success">Safe</span></td>
  <td><span class="badge success">Delivered</span></td>
  <td><button class="btn">Invoice</button></td>
</tr>

<tr style="background:#fee2e2;">
  <td>#GLB-993</td>
  <td>suspicious@email.com</td>
  <td>₱45,000.00</td>
  <td><span class="badge warning">Pending</span></td>
  <td><span class="badge danger">Suspicious</span></td>
  <td><span class="badge warning">Pending</span></td>
  <td><button class="btn">Invoice</button></td>
</tr>

</tbody>
</table>

</div>

<script>
function updateStatus(orderId) {
  alert('Update status for order: ' + orderId);
}
</script>

</body>
</html>