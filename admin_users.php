<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management</title>

<!-- Sidebar CSS -->
<link rel="stylesheet" href="sidebar.css">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
  margin: 0;
  font-family: Arial;
  background: #fdf2f6;
}

/* CONTENT */
.container {
  margin-left: 240px;
  padding: 20px;
  transition: 0.3s;
}
.container.full { margin-left: 70px; }

/* BUTTON */
.btn {
  padding: 6px 12px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}
.primary { background:#a61b4a; color:white; }
.outline { border:1px solid #ccc; background:white; }

/* TABS */
.tabs {
  display: flex;
  gap: 20px;
  border-bottom: 1px solid #ddd;
  margin-bottom: 20px;
}
.tab {
  padding-bottom: 10px;
  cursor: pointer;
}
.active-tab {
  border-bottom: 2px solid #a61b4a;
  color: #a61b4a;
}

/* CARD */
.card {
  background: white;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
}

/* TABLE */
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 10px;
}
th {
  background: #f9dbe5;
}
tr:hover { background: #fdf2f6; }

/* BADGES */
.badge {
  padding: 4px 8px;
  border-radius: 10px;
  font-size: 12px;
}
.success { background:#d1fae5; color:#065f46; }
.warning { background:#fef3c7; color:#92400e; }
.danger { background:#fee2e2; color:#991b1b; }

/* AVATAR */
.avatar {
  width: 30px;
  height: 30px;
  background: #a61b4a;
  color: white;
  border-radius: 50%;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size: 14px;
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

  <a href="#"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="#"><i class="fas fa-chart-line"></i><span class="text">Analytics</span></a>
  <a href="#"><i class="fas fa-users"></i><span class="text">Users</span></a>
  <a href="#"><i class="fas fa-box"></i><span class="text">Products</span></a>
  <a href="#"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="#"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="#"><i class="fas fa-shield-halved"></i><span class="text">Security</span></a>
  <a href="#"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  <a href="#"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>

</div>

<!-- MAIN -->
<div class="container" id="main">

<h1>User Management</h1>
<p>Manage all platform users</p>

<button class="btn primary">+ Add User</button>

<!-- TABS -->
<div class="tabs">
  <div class="tab active-tab" onclick="showTab('users')">All Users</div>
  <div class="tab" onclick="showTab('pending')">Pending Sellers</div>
</div>

<!-- USERS TABLE -->
<div id="usersTab">

<div class="card">
<input type="text" placeholder="Search users..." style="width:100%;padding:10px;">
</div>

<div class="card">
<table>
<thead>
<tr>
<th>User</th><th>Role</th><th>Status</th><th>Compliance</th><th>Joined</th><th>Action</th>
</tr>
</thead>
<tbody>

<tr>
<td><div class="avatar">A</div> Alice Admin</td>
<td>Admin</td>
<td><span class="badge success">Active</span></td>
<td><span class="badge success">Good</span></td>
<td>Oct 12, 2023</td>
<td><button class="btn outline">Block</button></td>
</tr>

<tr>
<td><div class="avatar">B</div> Bob Seller</td>
<td>Seller</td>
<td><span class="badge success">Active</span></td>
<td><span class="badge success">Good</span></td>
<td>Sep 05, 2023</td>
<td><button class="btn outline">Block</button></td>
</tr>

<tr>
<td><div class="avatar">C</div> Charlie</td>
<td>Customer</td>
<td><span class="badge success">Active</span></td>
<td><span class="badge warning">Warning</span></td>
<td>Aug 20, 2023</td>
<td><button class="btn outline">Block</button></td>
</tr>

<tr>
<td><div class="avatar">D</div> Diana</td>
<td>Customer</td>
<td><span class="badge danger">Blocked</span></td>
<td><span class="badge danger">Critical</span></td>
<td>Jul 15, 2023</td>
<td><button class="btn primary">Unblock</button></td>
</tr>

</tbody>
</table>
</div>

<!-- DISPUTES -->
<h2>Dispute Handling</h2>
<div class="card">
<table>
<tr><th>ID</th><th>Order</th><th>Customer</th><th>Seller</th><th>Reason</th><th>Action</th></tr>
<tr>
<td>DSP-001</td>
<td>ORD-8943</td>
<td>Charlie</td>
<td>TechStore</td>
<td>Item not as described</td>
<td><button class="btn outline">Resolve</button></td>
</tr>
</table>
</div>

</div>

<!-- PENDING SELLERS -->
<div id="pendingTab" style="display:none;">
<div class="card">
<table>
<tr><th>Name</th><th>Shop</th><th>Type</th><th>Docs</th><th>Action</th></tr>
<tr>
<td>John Doe</td>
<td>TechGadgets</td>
<td>Corporation</td>
<td>2/2</td>
<td>
<button class="btn outline">Reject</button>
<button class="btn primary">Approve</button>
</td>
</tr>
</table>
</div>
</div>

</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

function showTab(tab) {
  document.getElementById("usersTab").style.display = tab === 'users' ? 'block' : 'none';
  document.getElementById("pendingTab").style.display = tab === 'pending' ? 'block' : 'none';

  document.querySelectorAll(".tab").forEach(t => t.classList.remove("active-tab"));
  event.target.classList.add("active-tab");
}
</script>

</body>
</html>