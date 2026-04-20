<?php
require_once 'auth.php';
require_role(1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management</title>

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
.mb-4 { margin-bottom: 1rem; }
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

<!-- MAIN -->
<div class="container" id="main">

<h1 class="page-title">User Management</h1>
<p>Manage all platform users, roles, and permissions.</p>

<!-- ADD BUTTON -->
<button class="btn btn-primary mb-4">+ Add User</button>

<!-- TABS -->
<div class="tabs mb-4">
  <button class="active" onclick="showTab('users')">All Users</button>
  <button onclick="showTab('pending')">Pending Sellers</button>
</div>

<!-- USERS TAB -->
<div id="users" class="tab-content">

<div class="card">
  <input type="text" placeholder="Search users..." style="width:60%; padding:8px;">
  <select style="padding:8px;">
    <option>All Roles</option>
    <option>Admin</option>
    <option>Seller</option>
    <option>Customer</option>
  </select>
</div>

<div class="card">
<table>
<thead>
<tr>
  <th>Username</th>
  <th>Role</th>
  <th>Status</th>
  <th>Compliance</th>
  <th>Joined</th>
  <th>Actions</th>
</tr>
</thead>

<tbody>

<tr>
  <td>Alice Admin<br><small>alice@admin.com</small></td>
  <td>Admin</td>
  <td><span class="badge success">Active</span></td>
  <td><span class="badge success">Good</span></td>
  <td>Oct 12, 2023</td>
  <td><button class="btn">Block</button></td>
</tr>

<tr>
  <td>Bob Seller<br><small>bob@store.com</small></td>
  <td>Seller</td>
  <td><span class="badge success">Active</span></td>
  <td><span class="badge success">Good</span></td>
  <td>Sep 05, 2023</td>
  <td><button class="btn">Block</button></td>
</tr>

<tr>
  <td>Diana Suspended<br><small>diana@email.com</small></td>
  <td>Customer</td>
  <td><span class="badge danger">Blocked</span></td>
  <td><span class="badge danger">Critical</span></td>
  <td>Jul 15, 2023</td>
  <td><button class="btn btn-primary">Unblock</button></td>
</tr>

</tbody>
</table>
</div>

<h2>Dispute Handling</h2>

<div class="card">
<table>
<tr>
  <th>Dispute ID</th>
  <th>Order ID</th>
  <th>Customer</th>
  <th>Seller</th>
  <th>Reason</th>
  <th>Action</th>
</tr>

<tr>
  <td>DSP-001</td>
  <td>ORD-8943</td>
  <td>Charlie Customer</td>
  <td>TechStore 1</td>
  <td>Item not as described</td>
  <td><button class="btn">Resolve</button></td>
</tr>

<tr>
  <td>DSP-002</td>
  <td>ORD-8820</td>
  <td>Diana Suspended</td>
  <td>Fashion Hub</td>
  <td>Never received</td>
  <td><button class="btn">Resolve</button></td>
</tr>

</table>
</div>

</div>

<!-- PENDING TAB -->
<div id="pending" class="tab-content" style="display:none;">

<div class="card">
<table>
<tr>
  <th>Applicant</th>
  <th>Shop</th>
  <th>Type</th>
  <th>Docs</th>
  <th>Action</th>
</tr>

<tr>
  <td>John Doe</td>
  <td>TechGadgets</td>
  <td>Corporation</td>
  <td>2/2</td>
  <td>
    <button class="btn">Reject</button>
    <button class="btn btn-primary">Approve</button>
  </td>
</tr>

</table>
</div>

</div>

</div>

<!-- SCRIPTS -->
<script>
function showTab(tab) {
  document.getElementById('users').style.display = 'none';
  document.getElementById('pending').style.display = 'none';

  document.getElementById(tab).style.display = 'block';

  document.querySelectorAll('.tabs button').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
}
</script>

</body>
</html>