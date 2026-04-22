<?php
require_once 'auth.php';
require_roles([3, 4]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products</title>

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
    <a href="seller_products.php" class="active"><i class="bi bi-box-seam"></i><span class="text">Products</span></a>
    <a href="seller_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
    <a href="seller_inventory.php"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
    <a href="seller_reviews.php"><i class="bi bi-star"></i><span class="text">Reviews</span></a>
    <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
    <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

  <a href="#" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<!-- MAIN -->
<div class="main-content" id="main">

<div class="container-fluid">

<h2 class="fw-bold">Products</h2>
<p class="text-muted">Manage your store inventory and listings.</p>

<!-- ACTION BUTTONS -->
<div class="d-flex justify-content-between mb-3">
  <button class="btn btn-outline-secondary">
    <i class="bi bi-upload"></i> Bulk Upload
  </button>
  <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#addProductModal">
    <i class="bi bi-plus-lg"></i> Add Product
  </button>
</div>

<!-- ADD PRODUCT MODAL -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background: #6d0f1b; color: white;">
        <h5 class="modal-title">Add New Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
      </div>
      <div class="modal-body">
        <form id="addProductForm">
          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select">
              <option>Electronics</option>
              <option>Home</option>
              <option>Clothing</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-brand">Add Product</button>
      </div>
    </div>
  </div>
</div>

<!-- FILTER -->
<div class="card p-3 mb-4 d-flex flex-wrap gap-3">

  <input class="form-control w-50" placeholder="Search products by name...">

  <select class="form-select w-auto">
    <option>All Categories</option>
    <option>Electronics</option>
    <option>Home</option>
  </select>

  <select class="form-select w-auto">
    <option>All Tags</option>
    <option>Best Seller</option>
    <option>New Arrival</option>
  </select>

</div>

<!-- PRODUCT TABLE -->
<div class="card p-3 mb-4">

<h5 class="fw-bold mb-3">Product List</h5>

<table class="table">
<thead>
<tr>
<th>Product</th>
<th>Price</th>
<th>Stock</th>
<th>Reviews</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<tr>
<td>Classic White T-Shirt</td>
<td>₱16,999</td>
<td>45</td>
<td>⭐ 4.9 (850)</td>
<td><span class="badge-soft">Active</span></td>
</tr>

<tr>
<td>Minimalist Hoodie</td>
<td>₱5,200</td>
<td>12</td>
<td>⭐ 4.7 (420)</td>
<td><span class="badge-soft">Active</span></td>
</tr>

<tr>
<td>Slim Fit Jeans</td>
<td>₱1,200</td>
<td>0</td>
<td>⭐ 4.3 (210)</td>
<td><span class="badge-red">Out of Stock</span></td>
</tr>

</tbody>
</table>

</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS -->
<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}
</script>

</body>
</html> 