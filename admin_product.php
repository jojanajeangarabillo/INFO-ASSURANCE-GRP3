
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Product Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
  font-size: 32px;
  font-weight: bold;
  color: #610C27;
  margin-bottom: 5px;
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
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

/* table */
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 12px;
  border-bottom: 1px solid #ddd;
  text-align: left;
}
th {
  background: #f9dbe5;
  font-size: 13px;
  color: #610C27;
}

/* badges */
.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
}
.success { background: #d1fae5; color: #065f46; }
.warning { background: #fef3c7; color: #92400e; }
.danger { background: #fee2e2; color: #991b1b; }

/* buttons */
.btn {
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid #ccc;
  font-size: 14px;
}
.btn-primary {
  background: #a61b4a;
  color: white;
  border: none;
}
.btn-danger {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}
.mb-4 { margin-bottom: 1rem; }

.tag {
  background: #EFECE9;
  padding: 4px 10px;
  border-radius: 15px;
  margin-right: 5px;
  font-size: 12px;
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

<!-- MAIN -->
<div class="container" id="main">

<h1 class="page-title">Global Products</h1>
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
function showTab(tab) {
  document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
  document.getElementById(tab).style.display = 'block';

  document.querySelectorAll('.tabs button').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
}
</script>

</body>
</html>