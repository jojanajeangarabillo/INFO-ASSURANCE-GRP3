<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logistics Tracking</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="sidebar.css">

<style>
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #fdf2f6;
}

/* ===== MAIN LAYOUT ===== */
.main-content {
  margin-left: 240px;
  padding: 40px 60px;
  transition: margin-left 0.3s ease;
}

.main-content.full {
  margin-left: 70px;
}

/* ===== HEADER ===== */
h1 {
  color: #610C27;
  margin-bottom: 5px;
  font-size: 32px;
}

p {
  font-size: 16px;
}

/* ===== CARD ===== */
.card-box {
  background: white;
  border-radius: 18px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  margin-bottom: 25px;
}

/* CENTER (WIDER NOW) */
.center-box {
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
}

/* ===== FLEX ===== */
.flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.flex-start {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.text-end {
  text-align: right;
}

.text-muted {
  color: #777;
  font-size: 13px;
}

.text-success {
  color: green;
}

/* ===== SEARCH ===== */
.search-bar {
  display: flex;
  gap: 12px;
}

.search-bar input {
  flex: 1;
  padding: 12px;
  font-size: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
}

/* BUTTON */
button {
  padding: 12px 18px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
}

.search-bar button {
  background: #610C27;
  color: white;
}

.search-bar button:hover {
  background: #4a0a1e;
}

/* OUTLINE BUTTON */
.btn-outline-dark {
  border: 2px solid #610C27;
  background: transparent;
  color: #610C27;
}

.btn-outline-dark:hover {
  background: #610C27;
  color: white;
}

/* ===== TIMELINE ===== */
.timeline {
  position: relative;
  padding-left: 70px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 32px;
  top: 10px;
  bottom: 10px;
  width: 3px;
  background: #ddd;
}

.step {
  position: relative;
  margin-bottom: 45px;
}

/* ICON */
.step-icon {
  position: absolute;
  left: -4px;
  top: 0;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #ccc;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
}

.step.active .step-icon {
  background: #610C27;
}

.step.inactive {
  opacity: 0.5;
}

/* CONTENT */
.step-content {
  margin-left: 60px;
}

.step-title {
  font-weight: bold;
  font-size: 16px;
}

.text-accent {
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
    <h2 class="logo-text">Logistics</h2>
  </div>

  <a href="logi_dashboard.php" class="<?= $current_page == 'logi_dashboard.php' ? 'active' : '' ?>">
    <i class="fas fa-table-columns"></i><span class="text">Dashboard</span>
  </a>

  <a href="logi_orders.php" class="<?= $current_page == 'logi_orders.php' ? 'active' : '' ?>">
    <i class="fas fa-cart-shopping"></i><span class="text">Orders</span>
  </a>

  <a href="logi_tracking.php" class="<?= $current_page == 'logi_tracking.php' ? 'active' : '' ?>">
    <i class="fas fa-truck-fast"></i><span class="text">Tracking</span>
  </a>

  <a href="logi_reports.php" class="<?= $current_page == 'logi_reports.php' ? 'active' : '' ?>">
    <i class="fas fa-file-lines"></i><span class="text">Reports</span>
  </a>

  <a href="logi_settings.php" class="<?= $current_page == 'logi_settings.php' ? 'active' : '' ?>">
    <i class="fas fa-gear"></i><span class="text">Settings</span>
  </a>

  <a href="#" class="logout">
    <i class="fas fa-right-from-bracket"></i><span class="text">Logout</span>
  </a>
</div>

<!-- MAIN -->
<div class="main-content" id="main">

<h1>Order Tracking</h1>
<p>Monitor shipment progress and delivery status in real time.</p>

<!-- SEARCH -->
<div class="card-box center-box">
  <div class="search-bar">
    <input placeholder="Enter tracking number or order ID" value="ORD-9021">
    <button>Track</button>
  </div>
</div>

<!-- TRACKING -->
<div class="card-box center-box">

  <div class="flex" style="border-bottom:1px solid #eee; padding-bottom:15px; margin-bottom:25px;">
    <div>
      <strong>Tracking ID: ORD-9021</strong><br>
      <small class="text-muted">Courier Service: J&T Express</small>
    </div>
    <div class="text-end">
      <small><strong>Estimated Delivery</strong></small><br>
      <span class="text-success">Oct 26, 2023</span>
    </div>
  </div>

  <!-- TIMELINE -->
  <div class="timeline">

    <div class="step active">
      <div class="step-icon"><i class="fas fa-box"></i></div>
      <div class="step-content flex-start">
        <div>
          <div class="step-title">Package Received</div>
          <small class="text-muted">Seller Hub, Manila</small>
        </div>
        <small>Oct 24, 10:00 AM</small>
      </div>
    </div>

    <div class="step active">
      <div class="step-icon"><i class="fas fa-truck"></i></div>
      <div class="step-content flex-start">
        <div>
          <div class="step-title text-accent">In Transit</div>
          <small class="text-muted">NCR Sorting Facility</small>
        </div>
        <small>Oct 24, 2:30 PM</small>
      </div>
    </div>

    <div class="step inactive">
      <div class="step-icon"><i class="fas fa-map-marker-alt"></i></div>
      <div class="step-content flex-start">
        <div>
          <div class="step-title">Out for Delivery</div>
          <small class="text-muted">Pending dispatch</small>
        </div>
        <small>Pending</small>
      </div>
    </div>

    <div class="step inactive">
      <div class="step-icon"><i class="fas fa-check"></i></div>
      <div class="step-content flex-start">
        <div>
          <div class="step-title">Delivered</div>
          <small class="text-muted">Awaiting completion</small>
        </div>
        <small>Pending</small>
      </div>
    </div>

  </div>

  <div class="text-end" style="margin-top:25px;">
    <button class="btn-outline-dark" onclick="advanceStep()">Advance Status (Demo)</button>
  </div>

</div>

</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

let currentStep = 2;

function advanceStep() {
  const steps = document.querySelectorAll('.step');

  currentStep++;
  if (currentStep > 4) currentStep = 1;

  steps.forEach((step, index) => {
    step.classList.remove('active', 'inactive');

    if (index < currentStep) {
      step.classList.add('active');
    } else {
      step.classList.add('inactive');
    }
  });
}
</script>

</body>
</html>