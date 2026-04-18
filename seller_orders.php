<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Seller Orders</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body {
  background: #f5f5f5;
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
}

/* SIDEBAR */
.sidebar {
  width: 240px;
  position: fixed;
  height: 100%;
  top: 0;
  left: 0;
}

.sidebar.collapsed {
  width: 70px;
}

/* MAIN CONTENT (MATCH DASHBOARD) */
.main-content {
  margin-left: 240px;
  transition: 0.3s;
  padding: 20px;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 70px;
}

/* CARD STYLE */
.container-box {
  background: #fff;
  border-radius: 16px;
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  padding: 20px;
}

/* THEME */
:root {
  --brand: #6d0f1b;
}

/* TABS */
.tabs span {
  margin-right: 20px;
  cursor: pointer;
  padding-bottom: 6px;
}

.tabs .active {
  border-bottom: 2px solid var(--brand);
  font-weight: bold;
  color: var(--brand);
}

/* BADGES */
.badge-status {
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 20px;
}

.pending { background:#fff3cd; color:#856404; }
.transit { background:#e0ecff; color:#0d6efd; }
.delivered { background:#d1e7dd; color:#198754; }

/* BUTTON */
.btn-outline {
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 6px 12px;
  background: white;
}

.btn-outline:hover {
  border-color: var(--brand);
  color: var(--brand);
}

/* TABLE */
.table th {
  font-size: 12px;
  color: gray;
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
  <a href="seller_orders.php" class="active"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
  <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Store Profile</span></a>
  <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

  <a href="#" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<!-- MAIN CONTENT -->
<div class="main-content" id="main">

<div class="container-fluid">

<h3 class="fw-bold">Delivery Management</h3>
<p class="text-muted">Manage order fulfillment and courier assignments.</p>

<!-- TABS -->
<div class="tabs mb-3">
  <span class="active">All</span>
  <span>Pending Pickup</span>
  <span>In Transit</span>
  <span>Delivered</span>
</div>

<hr>

<!-- SEARCH -->
<div class="container-box mb-4">
  <input class="form-control" placeholder="Search by Order ID or Customer...">
</div>

<!-- TABLE -->
<div class="container-box">
<table class="table align-middle">

<thead>
<tr>
  <th>ORDER ID</th>
  <th>CUSTOMER</th>
  <th>COURIER</th>
  <th>STATUS</th>
  <th>DATES</th>
  <th>ACTION</th>
</tr>
</thead>

<tbody>

<tr>
  <td><strong>ORD-9021</strong></td>
  <td>Jane Doe</td>
  <td>
    <select class="form-select form-select-sm">
      <option selected>J&T Express</option>
      <option>LBC</option>
      <option>Ninja Van</option>
    </select>
  </td>
  <td><span class="badge-status pending">Pending Pickup</span></td>
  <td>Pickup: - <br> Delivery: -</td>
  <td><button class="btn-outline">📅 Schedule</button></td>
</tr>

<tr>
  <td><strong>ORD-8943</strong></td>
  <td>John Smith</td>
  <td>
    <select class="form-select form-select-sm">
      <option>J&T Express</option>
      <option selected>LBC</option>
      <option>Ninja Van</option>
    </select>
  </td>
  <td><span class="badge-status transit">In Transit</span></td>
  <td>Pickup: Oct 24 <br> Delivery: Est. Oct 26</td>
  <td></td>
</tr>

<tr>
  <td><strong>ORD-8820</strong></td>
  <td>Alice Brown</td>
  <td>
    <select class="form-select form-select-sm">
      <option>J&T Express</option>
      <option>LBC</option>
      <option selected>Ninja Van</option>
    </select>
  </td>
  <td><span class="badge-status delivered">Delivered</span></td>
  <td>Pickup: Oct 22 <br> Delivery: Oct 24</td>
  <td></td>
</tr>

</tbody>

</table>
</div>

</div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}
</script>


</body>
</html>