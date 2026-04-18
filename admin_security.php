<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Security & Access</title>
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
  transition: margin-left 0.3s ease;
}

.container.full {
  margin-left: 70px;
}

.grid {
  display: grid;
  gap: 20px;
}

.grid-3-1 {
  grid-template-columns: 2fr 1fr;
}

@media (max-width: 1024px) {
  .grid-3-1 {
    grid-template-columns: 1fr;
  }
}

.card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  margin-bottom: 20px;
}

.section-title {
  font-size: 20px;
  font-weight: bold;
  color: #610C27;
  margin-bottom: 15px;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background: #f9dbe5;
  color: #610C27;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.text-center { text-align: center; }

.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
}

.alert-row {
  background: rgba(255, 0, 0, 0.05);
}

.policy-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
}

.policy-item:last-child {
  border-bottom: none;
}

.policy-info h4 {
  margin: 0 0 5px 0;
  color: #610C27;
}

.policy-info p {
  margin: 0;
  font-size: 12px;
  color: #666;
}

.checkbox-custom {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #610C27;
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

<!-- CONTENT -->
<div class="container" id="main">

<h1>Security & Access</h1>
<p>Manage roles, permissions, and view audit logs.</p>

<div class="grid grid-3-1">
  
  <!-- LEFT COLUMN -->
  <div class="left-col">
    
    <div class="section-title">Roles & Permissions</div>
    <div class="card">
      <table>
        <thead>
          <tr>
            <th>Permission</th>
            <th class="text-center">Super Admin</th>
            <th class="text-center">Admin</th>
            <th class="text-center">Moderator</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Manage Users</td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" class="checkbox-custom"></td>
          </tr>
          <tr>
            <td>Approve Sellers</td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
          </tr>
          <tr>
            <td>Manage Products</td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
          </tr>
          <tr>
            <td>System Settings</td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" class="checkbox-custom"></td>
          </tr>
          <tr>
            <td>Financial Reports</td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" checked class="checkbox-custom"></td>
            <td class="text-center"><input type="checkbox" class="checkbox-custom"></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="section-title">Audit Logs</div>
    <div class="card">
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Action</th>
            <th>IP Address</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>admin@j3rs.com</strong></td>
            <td>Updated System Settings</td>
            <td><code>192.168.1.1</code></td>
            <td>10 mins ago</td>
          </tr>
          <tr>
            <td><strong>mod1@j3rs.com</strong></td>
            <td>Approved Seller "TechGadgets"</td>
            <td><code>10.0.0.5</code></td>
            <td>1 hour ago</td>
          </tr>
          <tr>
            <td><strong>admin@j3rs.com</strong></td>
            <td>Exported Financial Report</td>
            <td><code>192.168.1.1</code></td>
            <td>2 hours ago</td>
          </tr>
          <tr class="alert-row">
            <td><strong>system</strong></td>
            <td><i class="fas fa-triangle-exclamation" style="color:red"></i> Failed login attempt (3x)</td>
            <td><code>45.22.11.9</code></td>
            <td>5 hours ago</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

  <!-- RIGHT COLUMN -->
  <div class="right-col">
    <div class="section-title">Policy Enforcement</div>
    <div class="card">
      <div class="policy-item">
        <div class="policy-info">
          <h4>Require 2FA for Admins</h4>
          <p>Force two-factor authentication for all admin and moderator accounts.</p>
        </div>
        <input type="checkbox" checked class="checkbox-custom">
      </div>

      <div class="policy-item">
        <div class="policy-info">
          <h4>Strict Password Policy</h4>
          <p>Require 12+ chars, uppercase, lowercase, numbers, and symbols.</p>
        </div>
        <input type="checkbox" checked class="checkbox-custom">
      </div>

      <div class="policy-item">
        <div class="policy-info">
          <h4>Session Timeout</h4>
          <p>Automatically log out inactive admin sessions after 30 minutes.</p>
        </div>
        <input type="checkbox" checked class="checkbox-custom">
      </div>

      <div class="policy-item">
        <div class="policy-info">
          <h4>IP Whitelisting</h4>
          <p>Restrict admin access to specific IP addresses only.</p>
        </div>
        <input type="checkbox" class="checkbox-custom">
      </div>
    </div>
  </div>

</div>

</div>

</body>
</html>
