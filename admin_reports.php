
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Reports</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="sidebar.css">

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
      <tr>
        <td>2023-10-18</td>
        <td>#ORD-1001</td>
        <td>Electronics</td>
        <td>₱5,400.00</td>
        <td>Completed</td>
      </tr>
      <tr>
        <td>2023-10-17</td>
        <td>#ORD-1002</td>
        <td>Fashion</td>
        <td>₱1,200.00</td>
        <td>Completed</td>
      </tr>
      <tr>
        <td>2023-10-17</td>
        <td>#ORD-1003</td>
        <td>Home</td>
        <td>₱2,800.00</td>
        <td>Pending</td>
      </tr>
    </tbody>
  </table>
</div>

</div>

<script>
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
  type: 'bar',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
      label: 'Sales (₱)',
      data: [12000, 19000, 3000, 5000, 20000, 30000],
      backgroundColor: '#610C27'
    }]
  }
});

const userCtx = document.getElementById('userChart').getContext('2d');
new Chart(userCtx, {
  type: 'line',
  data: {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
      label: 'New Users',
      data: [150, 230, 180, 400, 560, 720],
      borderColor: '#a61b4a',
      fill: false
    }]
  }
});
</script>

</body>
</html>
