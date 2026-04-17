<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="sidebar.css">

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

.growth { color: #4ade80; }
.warning { color: orange; }

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
</style>
</head>

<body>

<!-- SIDEBAR -->
<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Admin</h2>
  </div>

  <a href="admin_dashboard.php"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="admin_analytics.php"><i class="fas fa-chart-line"></i><span class="text">Analytics</span></a>
  <a href="#"><i class="fas fa-users"></i><span class="text">Users</span></a>
  <a href="#"><i class="fas fa-box"></i><span class="text">Products</span></a>
  <a href="admin_orders.php"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="#"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="#"><i class="fas fa-shield-halved"></i><span class="text">Security</span></a>
  <a href="#"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  <a href="#" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>

</div>

<!-- CONTENT -->
<div class="container" id="main">

<h1>Platform Overview</h1>
<p>Global metrics and system health.</p>

<div class="grid grid-4">

  <div class="card"><small>Total Users</small><div class="flex"><h2>24,592</h2><span class="growth">+12%</span></div></div>

  <div class="card"><small>Total Sellers</small><div class="flex"><h2>1,204</h2><span class="growth">+3%</span></div></div>

  <div class="card"><small>Platform Revenue</small><div class="flex"><h2>₱845.2K</h2><span class="growth">+18%</span></div></div>

  <div class="card"><small>Total Orders</small><div class="flex"><h2>12,490</h2><span class="growth">+8%</span></div></div>

  <div class="card"><small>Active Sellers</small><div class="flex"><h2>842</h2><span class="growth">+5%</span></div></div>

  <div class="card"><small>Pending Approvals</small><div class="flex"><h2>15</h2><span class="warning">Needs action</span></div></div>

</div>

<br>

<div class="grid grid-3">

  <div class="box">
    <h3>Revenue Growth</h3>
    <canvas id="chart"></canvas>
  </div>

  <div class="box">
    <h3>Recent Activity</h3>
    <div class="activity-item"><div class="icon">👤</div>New user registered</div>
    <div class="activity-item"><div class="icon">✔</div>Seller approved</div>
  </div>

</div>

</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

new Chart(document.getElementById('chart'), {
  type: 'line',
  data: {
    labels: ['Jan','Feb','Mar','Apr','May','Jun'],
    datasets: [{
      data: [4000,3000,5000,2780,8900,12390],
      borderColor: '#610C27',
      backgroundColor: 'rgba(97,12,39,0.2)',
      fill: true
    }]
  }
});
</script>

</body>
</html>