<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Product Management</title>

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- SIDEBAR CSS -->
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
  transition: 0.3s;
}
.container.full {
  margin-left: 70px;
}

/* title */
.page-title {
  font-size: 36px;
  font-weight: bold;
  color: #610C27;
}

/* tabs */
.tabs button {
  padding: 10px;
  border: none;
  background: none;
  cursor: pointer;
  border-bottom: 2px solid transparent;
}
.tabs button.active {
  border-bottom: 2px solid #a61b4a;
  color: #a61b4a;
  font-weight: bold;
}

/* card */
.card {
  background: white;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
}

/* table */
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
}
th {
  background: #f9dbe5;
  font-size: 12px;
}

/* badges */
.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
}
.success { background: #d1fae5; }
.warning { background: #fef3c7; }

/* buttons */
.btn {
  padding: 5px 10px;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid #ccc;
}
.btn-primary {
  background: #a61b4a;
  color: white;
  border: none;
}
.btn-danger {
  color: red;
  border-color: red;
}
.tag {
  background: #f9dbe5;
  padding: 5px 10px;
  border-radius: 20px;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <span class="toggle-btn" onclick="toggleSidebar()">☰</span>
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

<!-- MAIN -->
<div class="container" id="main">

<h1 style="font-size: 32px; font-weight: bold; color: #610C27; margin-bottom: 5px;">
  Global Products</h1>
<p>Monitor and moderate all products across the platform.</p>

<!-- TABS -->
<div class="tabs mb-4">
  <button class="active" onclick="showTab('products')">All Products</button>
  <button onclick="showTab('pending')">Pending Approval</button>
  <button onclick="showTab('reviews')">Flagged Reviews</button>
</div>

<!-- ALL PRODUCTS -->
<div id="products" class="tab-content">

<div class="card">
  <input type="text" placeholder="Search products..." style="width:60%; padding:8px;">
</div>

<div class="card">
<table>
<tr>
  <th>Product</th>
  <th>Seller</th>
  <th>Status</th>
  <th>Actions</th>
</tr>

<tr>
  <td>Premium Wireless Headphones</td>
  <td>TechStore 1</td>
  <td><span class="badge warning">Pending Review</span></td>
  <td><button class="btn btn-danger">Remove</button></td>
</tr>

<tr>
  <td>Mechanical Keyboard v2</td>
  <td>TechStore 2</td>
  <td><span class="badge success">Active</span></td>
  <td><button class="btn btn-danger">Remove</button></td>
</tr>

</table>
</div>

<h2>Categories & Tags Management</h2>

<div class="card">
  <input type="text" placeholder="New Category..." style="padding:8px;">
  <button class="btn btn-primary">Add</button>

  <div style="margin-top:10px;">
    <span class="tag">Electronics ✖</span>
    <span class="tag">Clothing ✖</span>
    <span class="tag">Sports ✖</span>
  </div>
</div>

</div>

<!-- PENDING -->
<div id="pending" class="tab-content" style="display:none;">

<div class="card">
<table>
<tr>
  <th>Product</th>
  <th>Seller</th>
  <th>Category</th>
  <th>Price</th>
  <th>Actions</th>
</tr>

<tr>
  <td>Smart Watch Series 8</td>
  <td>TechStore 1</td>
  <td>Electronics</td>
  <td>₱22,500</td>
  <td>
    <button class="btn btn-danger">Reject</button>
    <button class="btn btn-primary">Approve</button>
  </td>
</tr>

</table>
</div>

</div>

<!-- REVIEWS -->
<div id="reviews" class="tab-content" style="display:none;">

<div class="card">
<table>
<tr>
  <th>Product</th>
  <th>Review</th>
  <th>Reason</th>
  <th>Actions</th>
</tr>

<tr>
  <td>Keyboard v2</td>
  <td>Terrible product</td>
  <td>Inappropriate</td>
  <td>
    <button class="btn">Ignore</button>
    <button class="btn btn-danger">Delete</button>
  </td>
</tr>

</table>
</div>

</div>

</div>

<!-- SCRIPT -->
<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

function showTab(tab) {
  document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
  document.getElementById(tab).style.display = 'block';

  document.querySelectorAll('.tabs button').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
}
</script>

</body>
</html>