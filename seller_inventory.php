<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Clothing Inventory Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body {
  background: #f5f5f5;
  font-family: 'Segoe UI', sans-serif;
}

/* MAIN LAYOUT */
.main-content {
  margin-left: 240px;
  transition: 0.3s;
  padding: 20px;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 70px;
}

/* CARDS */
.card {
  border-radius: 16px;
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

/* BUTTON */
.btn-brand {
  background: #6d0f1b;
  color: white;
  border-radius: 10px;
}

.btn-brand:hover {
  background: #500b14;
}

/* BADGES */
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
    <a href="seller_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
    <a href="seller_inventory.php" class="active"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
    <a href="seller_reviews.php"><i class="bi bi-star"></i><span class="text">Reviews</span></a>
    <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
    <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>
  
    <a href="#" class="logout">
      <i class="bi bi-box-arrow-right"></i>
      <span class="text">Logout</span>
  </a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

<div class="container-fluid">

<h2 class="fw-bold">Inventory Management</h2>
<p class="text-muted">Manage stock levels, restock products, and track inventory history.</p>

<!-- HEADER ACTIONS -->
<div class="d-flex justify-content-between mb-3">
  <input class="form-control w-50" placeholder="Search clothing inventory...">

  <button class="btn btn-brand">
    <i class="bi bi-plus-lg"></i> Restock Product
  </button>
</div>

<!-- ================= CURRENT STOCK ================= -->
<div class="card p-3 mb-3">

<h5 class="fw-bold">Current Stock</h5>

<table class="table">
<thead>
<tr>
<th>Product</th>
<th>SKU</th>
<th>Stock</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<tr>
<td>Oversized Hoodie</td>
<td>CLOTH-001</td>
<td>40</td>
<td><span class="badge-soft">In Stock</span></td>
</tr>

<tr>
<td>Streetwear Shirt</td>
<td>CLOTH-002</td>
<td>12</td>
<td><span class="badge-red">Low Stock</span></td>
</tr>

<tr>
<td>Baggy Jeans</td>
<td>CLOTH-003</td>
<td>0</td>
<td><span class="badge-red">Out of Stock</span></td>
</tr>

</tbody>
</table>

</div>

<!-- ================= RESTOCK HISTORY ================= -->
<div class="card p-3">

<h5 class="fw-bold">Restock History</h5>

<table class="table">
<thead>
<tr>
<th>Restock ID</th>
<th>Product</th>
<th>Quantity Added</th>
<th>Date</th>
<th>Staff</th>
<th>Notes</th>
</tr>
</thead>

<tbody>

<tr>
<td>RST-001</td>
<td>Oversized Hoodie</td>
<td>25</td>
<td>Oct 20, 2023</td>
<td>Admin</td>
<td>Supplier delivery</td>
</tr>

<tr>
<td>RST-002</td>
<td>Streetwear Shirt</td>
<td>30</td>
<td>Oct 18, 2023</td>
<td>Manager</td>
<td>Batch refill</td>
</tr>

<tr>
<td>RST-003</td>
<td>Baggy Jeans</td>
<td>50</td>
<td>Oct 15, 2023</td>
<td>Admin</td>
<td>Bulk purchase</td>
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