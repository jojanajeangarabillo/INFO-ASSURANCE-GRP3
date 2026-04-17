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

  <a href="seller_dashboard.php"><i class="bi bi-speedometer2"></i><span class="text">Dashboard</span></a>
  <a href="seller_products.php"><i class="bi bi-box-seam"></i><span class="text">Products</span></a>
  <a href="#"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
  <a href="#"><i class="bi bi-shop"></i><span class="text">Store Profile</span></a>
  <a href="#"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

  <a href="#" class="logout">
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
      <span class="badge-soft float-end">+14.5%</span>
      <p class="text-muted mb-1">Total Revenue</p>
      <h3>₱46,560</h3>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card">
      <span class="badge-soft float-end">+5.2%</span>
      <p class="text-muted mb-1">Orders</p>
      <h3>292</h3>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card">
      <span class="badge-soft float-end">Stable</span>
      <p class="text-muted mb-1">Active Products</p>
      <h3>48</h3>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card stat-card">
      <span class="badge-soft float-end">+22.1%</span>
      <p class="text-muted mb-1">Store Views</p>
      <h3>12,450</h3>
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

      <form>
        <input class="form-control mb-2" placeholder="Product Name">

        <div class="row g-2 mb-2">
          <div class="col">
            <input class="form-control" placeholder="Price">
          </div>
          <div class="col">
            <input class="form-control" placeholder="Stock">
          </div>
        </div>

        <input class="form-control mb-3" placeholder="Image URL">

        <button class="btn btn-brand w-100">+ Add Product</button>
      </form>

    </div>
  </div>

</div>

<!-- TABLES -->
<div class="row g-4">

<!-- PRODUCTS -->
<div class="col-lg-6">
<div class="card p-3">
<h5 class="fw-bold mb-3">Product List</h5>

<table class="table">
<thead>
<tr>
<th>Product</th>
<th>Stock</th>
<th>Status</th>
</tr>
</thead>
<tbody>

<tr>
  <td>Classic White T-Shirt</td>
  <td>45</td>
  <td><span class="badge-soft">Active</span></td>
</tr>

<tr>
  <td>Minimalist Hoodie</td>
  <td>12</td>
  <td><span class="badge-soft">Active</span></td>
</tr>

<tr>
  <td>Slim Fit Jeans</td>
  <td>0</td>
  <td><span class="badge-red">Out of Stock</span></td>
</tr>

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

<tr>
  <td>ORD-001</td>
  <td>Alice Smith</td>
  <td>
    <select class="form-select">
      <option selected>Pending</option>
      <option>Shipped</option>
      <option>Delivered</option>
    </select>
  </td>
</tr>

<tr>
  <td>ORD-002</td>
  <td>Bob Johnson</td>
  <td>
    <select class="form-select">
      <option>Pending</option>
      <option selected>Shipped</option>
      <option>Delivered</option>
    </select>
  </td>
</tr>

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

<tr>
  <td>Alice Smith</td>
  <td>12</td>
  <td class="text-success">₱45,200</td>
</tr>

<tr>
  <td>Bob Johnson</td>
  <td>8</td>
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
    labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
    datasets: [{
      label: 'Revenue',
      data: [3500, 2800, 4500, 2600, 8000, 12000, 10500],
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