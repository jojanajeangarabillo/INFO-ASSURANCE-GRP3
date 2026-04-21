
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>System Settings</title>
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

h1 {
  color: #610C27;
  font-size: 32px;
  margin-bottom: 5px;
}

.settings-layout {
  display: flex;
  gap: 30px;
  margin-top: 20px;
}

/* Sidebar Tabs */
.settings-sidebar {
  width: 260px;
  background: white;
  padding: 10px;
  border-radius: 12px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  height: fit-content;
}

.tab-btn {
  width: 100%;
  text-align: left;
  padding: 12px 15px;
  border: none;
  background: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  color: #333;
  transition: 0.3s;
  margin-bottom: 5px;
}

.tab-btn:hover {
  background: #fdf2f6;
  color: #610C27;
}

.tab-btn.active {
  background: #f9dbe5;
  color: #610C27;
  font-weight: bold;
}

/* Content Area */
.settings-content {
  flex: 1;
}

.card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  margin-bottom: 20px;
}

.card h3 {
  margin-top: 0;
  color: #610C27;
  border-bottom: 1px solid #eee;
  padding-bottom: 15px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
  max-width: 500px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: bold;
  margin-bottom: 8px;
  color: #333;
}

.form-group input, .form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}

.form-group p {
  font-size: 12px;
  color: #666;
  margin-top: 5px;
}

.btn-save {
  background: #610C27;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  float: right;
}

/* Payment Gateway Items */
.gateway-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #eee;
  border-radius: 10px;
  margin-bottom: 10px;
  background: #fcfcfc;
}

.gateway-info h4 {
  margin: 0;
  color: #333;
}

.gateway-info p {
  margin: 5px 0 0 0;
  font-size: 12px;
  color: #666;
}

.checkbox-custom {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #610C27;
}

/* Tables */
table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  font-size: 12px;
  text-transform: uppercase;
  color: #666;
}

@media (max-width: 768px) {
  .settings-layout {
    flex-direction: column;
  }
  .settings-sidebar {
    width: 100%;
  }
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

<!-- CONTENT -->
<div class="container" id="main">

<div style="display: flex; justify-content: space-between; align-items: center;">
  <div>
    <h1>System Settings</h1>
    <p>Global configuration affecting all users and stores.</p>
  </div>
  <button class="btn-save">Save All Changes</button>
</div>

<div class="settings-layout">
  
  <!-- SETTINGS TABS -->
  <div class="settings-sidebar">
    <button class="tab-btn active" onclick="openTab(event, 'General')">General</button>
    <button class="tab-btn" onclick="openTab(event, 'Security')">Security</button>
    <button class="tab-btn" onclick="openTab(event, 'Payments')">Payment Gateways</button>
    <button class="tab-btn" onclick="openTab(event, 'Roles')">Roles & Permissions</button>
    <button class="tab-btn" onclick="openTab(event, 'Commission')">Commission Rules</button>
    <button class="tab-btn" onclick="openTab(event, 'Shipping')">Shipping Settings</button>
  </div>

  <!-- SETTINGS CONTENT -->
  <div class="settings-content">
    
    <!-- General -->
    <div id="General" class="tab-content">
      <div class="card">
        <h3>General Settings</h3>
        <div class="form-group">
          <label>Site Name</label>
          <input type="text" value="J3RS" placeholder="J3RS E-Commerce">
        </div>
        <div class="form-group">
          <label>Default Language</label>
          <select>
            <option>English (US)</option>
            <option>Tagalog</option>
          </select>
        </div>
        <div class="form-group">
          <label>Default Currency</label>
          <select>
            <option>PHP (₱)</option>
            <option>USD ($)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Timezone</label>
          <select>
            <option>Asia/Manila (GMT+8)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Security -->
    <div id="Security" class="tab-content" style="display:none;">
      <div class="card">
        <h3>Security Settings</h3>
        <div class="form-group">
          <label>Maximum Login Attempts</label>
          <input type="number" value="3">
          <p>Number of failed login attempts before an account is temporarily locked.</p>
        </div>
        <div class="policy-item" style="border-top: 1px solid #eee; padding-top: 20px;">
          <div class="policy-info">
            <h4>3 failed logins → 15 min lockout</h4>
            <p>Automatically lock accounts after consecutive failed attempts.</p>
          </div>
          <input type="checkbox" checked class="checkbox-custom">
        </div>
        <div class="policy-item" style="border-top: 1px solid #eee; padding-top: 20px;">
          <div class="policy-info">
            <h4>Require 2FA for Admins</h4>
            <p>Force two-factor authentication for all admin accounts.</p>
          </div>
          <input type="checkbox" checked class="checkbox-custom">
        </div>
      </div>
    </div>

    <!-- Payments -->
    <div id="Payments" class="tab-content" style="display:none;">
      <div class="card">
        <h3>Payment Gateways</h3>
        <div class="gateway-item">
          <div class="gateway-info">
            <h4>Cash on Delivery (COD)</h4>
            <p>Configure API keys and settings</p>
          </div>
          <div style="display: flex; gap: 10px; align-items: center;">
            <button class="tab-btn" style="padding: 5px 10px; border: 1px solid #ddd;">Configure</button>
            <input type="checkbox" checked class="checkbox-custom">
          </div>
        </div>
        <div class="gateway-item">
          <div class="gateway-info">
            <h4>Bank Transfer</h4>
            <p>Configure API keys and settings</p>
          </div>
          <div style="display: flex; gap: 10px; align-items: center;">
            <button class="tab-btn" style="padding: 5px 10px; border: 1px solid #ddd;">Configure</button>
            <input type="checkbox" checked class="checkbox-custom">
          </div>
        </div>
        <div class="gateway-item">
          <div class="gateway-info">
            <h4>GCash</h4>
            <p>Configure API keys and settings</p>
          </div>
          <div style="display: flex; gap: 10px; align-items: center;">
            <button class="tab-btn" style="padding: 5px 10px; border: 1px solid #ddd;">Configure</button>
            <input type="checkbox" checked class="checkbox-custom">
          </div>
        </div>
      </div>
    </div>

    <!-- Roles -->
    <div id="Roles" class="tab-content" style="display:none;">
      <div class="card">
        <h3>Roles & Permissions</h3>
        <div class="gateway-item">
          <h4>Super Admin</h4>
          <button class="tab-btn" style="width: auto; padding: 5px 10px; border: 1px solid #ddd;">Edit Permissions</button>
        </div>
        <div class="gateway-item">
          <h4>Admin</h4>
          <button class="tab-btn" style="width: auto; padding: 5px 10px; border: 1px solid #ddd;">Edit Permissions</button>
        </div>
        <div class="gateway-item">
          <h4>Moderator</h4>
          <button class="tab-btn" style="width: auto; padding: 5px 10px; border: 1px solid #ddd;">Edit Permissions</button>
        </div>
        <button class="tab-btn" style="width: 100%; border: 1px dashed #610C27; color: #610C27; margin-top: 20px;">+ Create Custom Role</button>
      </div>
    </div>

    <!-- Commission -->
    <div id="Commission" class="tab-content" style="display:none;">
      <div class="card">
        <h3>Commission Rates</h3>
        <div class="form-group" style="display: flex; align-items: center; gap: 15px;">
          <label style="flex: 1; margin-bottom: 0;">Electronics</label>
          <input type="number" value="5" style="width: 80px;">
          <span>%</span>
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 15px;">
          <label style="flex: 1; margin-bottom: 0;">Clothing & Apparel</label>
          <input type="number" value="8" style="width: 80px;">
          <span>%</span>
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 15px;">
          <label style="flex: 1; margin-bottom: 0;">Default (Other)</label>
          <input type="number" value="10" style="width: 80px;">
          <span>%</span>
        </div>
      </div>
    </div>

    <!-- Shipping -->
    <div id="Shipping" class="tab-content" style="display:none;">
      <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h3 style="border: none; padding: 0; margin: 0;">Shipping Zones & Rates</h3>
          <button class="tab-btn" style="width: auto; padding: 5px 15px; border: 1px solid #ddd;">Add Zone</button>
        </div>
        <table>
          <thead>
            <tr>
              <th>Zone Name</th>
              <th>Base Rate</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Metro Manila</strong></td>
              <td>₱80.00</td>
              <td style="text-align: right;"><button class="tab-btn" style="width: auto; padding: 5px 10px;">Edit</button></td>
            </tr>
            <tr>
              <td><strong>Luzon</strong></td>
              <td>₱120.00</td>
              <td style="text-align: right;"><button class="tab-btn" style="width: auto; padding: 5px 10px;">Edit</button></td>
            </tr>
            <tr>
              <td><strong>Visayas</strong></td>
              <td>₱150.00</td>
              <td style="text-align: right;"><button class="tab-btn" style="width: auto; padding: 5px 10px;">Edit</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div>

</div>

<script>
function openTab(evt, tabName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tab-content");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tab-btn");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>

</body>
</html>
