
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Analytics & Reports</title>

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
  <a href="admin_approvals.php" class="<?php echo $current_page == 'admin_approvals.php' ? 'active' : ''; ?>"><i class="fas fa-user-check"></i><span class="text">Approvals</span></a>
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
<p>Platform-wide performance and customer behavior insights.</p>

<br>

<div class="grid grid-3">

  <div class="card">
    <small>Top Category</small>
    <h2>Electronics</h2>
  </div>

  <div class="card">
    <small>Avg Order Value</small>
    <h2>₱3,450</h2>
  </div>

  <div class="card">
    <small>Repeat Customers</small>
    <h2>42%</h2>
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
      <tr><td>Seller A</td><td>₱12,000</td><td>120</td><td>4.5</td></tr>
      <tr><td>Seller B</td><td>₱9,500</td><td>98</td><td>4.2</td></tr>
    </tbody>
  </table>

</div>

</div>

<script>
new Chart(document.getElementById('revenueChart'), {
</script>

</body>
</html>