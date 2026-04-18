<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logistics Settings</title>
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

.grid {
  display: grid;
  gap: 20px;
}

.grid-2-1 {
  grid-template-columns: 2fr 1fr;
}

@media (max-width: 1024px) {
  .grid-2-1 {
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

.courier-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #eee;
  border-radius: 10px;
  margin-bottom: 10px;
  background: #fcfcfc;
}

.courier-info h4 {
  margin: 0;
  color: #333;
}

.courier-info p {
  margin: 5px 0 0 0;
  font-size: 12px;
  color: #666;
}

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
  background: #f9dbe5;
}

.checkbox-custom {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #610C27;
}

.btn-outline {
  padding: 6px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: white;
  cursor: pointer;
  font-size: 12px;
}

.notification-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-info h4 {
  margin: 0 0 5px 0;
  color: #610C27;
}

.notification-info p {
  margin: 0;
  font-size: 12px;
  color: #666;
}
</style>
</head>

<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Logistics</h2>
  </div>

  <a href="logi_dashboard.php" class="<?php echo $current_page == 'logi_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="logi_orders.php" class="<?php echo $current_page == 'logi_orders.php' ? 'active' : ''; ?>"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="logi_tracking.php" class="<?php echo $current_page == 'logi_tracking.php' ? 'active' : ''; ?>"><i class="fas fa-truck-fast"></i><span class="text">Tracking</span></a>
  <a href="logi_reports.php" class="<?php echo $current_page == 'logi_reports.php' ? 'active' : ''; ?>"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="logi_settings.php" class="<?php echo $current_page == 'logi_settings.php' ? 'active' : ''; ?>"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  
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

<h1>Logistics Settings</h1>
<p>Configure couriers, shipping zones, and rates.</p>

<div class="grid grid-2-1">
  
  <!-- LEFT COLUMN -->
  <div class="left-col">
    
    <div class="card">
      <h2 class="section-title">Courier Integrations</h2>
      <div class="courier-item">
        <div class="courier-info">
          <h4>J&T Express</h4>
          <p>Standard nationwide delivery</p>
        </div>
        <input type="checkbox" checked class="checkbox-custom">
      </div>
      <div class="courier-item">
        <div class="courier-info">
          <h4>LBC</h4>
          <p>Premium nationwide delivery</p>
        </div>
        <input type="checkbox" checked class="checkbox-custom">
      </div>
      <div class="courier-item">
        <div class="courier-info">
          <h4>Ninja Van</h4>
          <p>Standard delivery</p>
        </div>
        <input type="checkbox" checked class="checkbox-custom">
      </div>
      <div class="courier-item">
        <div class="courier-info">
          <h4>Grab Express</h4>
          <p>Same-day delivery (Metro Manila only)</p>
        </div>
        <input type="checkbox" class="checkbox-custom">
      </div>
    </div>

    <div class="card" style="padding: 0;">
      <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
        <h2 class="section-title" style="margin: 0;">Shipping Zones & Rates</h2>
        <button class="btn-outline">Add Zone</button>
      </div>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>Zone Name</th>
              <th>Areas Covered</th>
              <th>Base Rate</th>
              <th>Addt'l per kg</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>Metro Manila</strong></td>
              <td>NCR</td>
              <td>₱80</td>
              <td>₱40</td>
            </tr>
            <tr>
              <td><strong>Luzon</strong></td>
              <td>Regions 1-5, CAR</td>
              <td>₱120</td>
              <td>₱60</td>
            </tr>
            <tr>
              <td><strong>Visayas</strong></td>
              <td>Regions 6-8</td>
              <td>₱150</td>
              <td>₱80</td>
            </tr>
            <tr>
              <td><strong>Mindanao</strong></td>
              <td>Regions 9-13, BARMM</td>
              <td>₱180</td>
              <td>₱100</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- RIGHT COLUMN -->
  <div class="right-col">
    <div class="card">
      <h2 class="section-title">Notifications</h2>
      
      <div class="notification-item">
        <div class="notification-info">
          <h4>Customer SMS</h4>
          <p>Send SMS updates for 'Out for Delivery' and 'Delivered' statuses.</p>
        </div>
        <input type="checkbox" checked class="checkbox-custom">
      </div>

      <div class="notification-item">
        <div class="notification-info">
          <h4>Customer Email</h4>
          <p>Send email updates for all status changes.</p>
        </div>
        <input type="checkbox" checked class="checkbox-custom">
      </div>

      <div class="notification-item">
        <div class="notification-info">
          <h4>Seller Alerts</h4>
          <p>Notify sellers of failed delivery attempts or returns.</p>
        </div>
        <input type="checkbox" checked class="checkbox-custom">
      </div>

    </div>
  </div>

</div>

</div>

</body>
</html>
