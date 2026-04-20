<?php
require_once 'auth.php';
require_roles([3, 4]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Store Settings</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body {
  background: #f5f5f5;
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
}

/* SIDEBAR */
.sidebar {
  width: 240px;
  position: fixed;
  height: 100%;
}

.sidebar.collapsed {
  width: 70px;
}

/* MAIN CONTENT */
.main-content {
  margin-left: 240px;
  transition: 0.3s;
  padding: 20px;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 70px;
}

/* CARD STYLE */
.card-custom {
  background: #fff;
  border-radius: 16px;
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  padding: 25px;
}

/* BRAND */
:root {
  --brand: #6d0f1b;
}

/* BUTTON */
.btn-brand {
  background: var(--brand);
  color: white;
  border-radius: 10px;
}

.btn-brand:hover {
  background: #500b14;
}

/* TABS */
.tab-btn {
  cursor: pointer;
  padding: 10px 15px;
  border-radius: 10px;
}

.tab-active {
  background: var(--brand);
  color: white;
}

/* INPUT */
.form-control, .form-select {
  border-radius: 10px;
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
  <a href="seller_profile.php" class="active"><i class="bi bi-shop"></i><span class="text">Store Profile</span></a>
  <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

  <a href="#" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<!-- MAIN -->
<div class="main-content">

<div class="container-fluid">

<h3 class="fw-bold">Store Settings</h3>
<p class="text-muted">Manage your store profile and preferences.</p>

<!-- TABS -->
<div class="d-flex gap-2 mb-4">
  <div class="tab-btn tab-active" onclick="switchTab('profile')">Store Profile</div>
  <div class="tab-btn" onclick="switchTab('business')">Business Details</div>
  <div class="tab-btn" onclick="switchTab('shipping')">Shipping & Returns</div>
  <div class="tab-btn" onclick="switchTab('channels')">Multi-Channel</div>
</div>

<!-- STORE PROFILE -->
<div id="profile" class="tab-content">

<div class="card-custom mb-4">
<h5 class="fw-bold mb-3">Store Information</h5>

<input class="form-control mb-3" value="TechGadgets Official">
<input class="form-control mb-3" value="j3rs.com/store/techgadgets">
<textarea class="form-control mb-3">Premium electronics.</textarea>

</div>

<div class="text-end">
<button class="btn btn-brand">Save Settings</button>
</div>

</div>

<!-- BUSINESS DETAILS -->
<div id="business" class="tab-content d-none">

<div class="card-custom mb-4">
<h5 class="fw-bold mb-3">Business Information</h5>

<input class="form-control mb-3" value="TechGadgets Inc.">

<select class="form-select mb-3">
<option>Corporation</option>
<option>Individual</option>
<option>Partnership</option>
</select>

<input class="form-control mb-3" value="123-456-789-000">
<input class="form-control mb-3" value="CS202312345">

</div>

<div class="text-end">
<button class="btn btn-brand">Save Settings</button>
</div>

</div>

<!-- SHIPPING -->
<div id="shipping" class="tab-content d-none">

<div class="card-custom mb-4">
<h5 class="fw-bold mb-3">Store Policies</h5>

<textarea class="form-control mb-3">Orders are processed within 1-2 business days. Standard shipping takes 3-5 days.</textarea>

<textarea class="form-control mb-3">We accept returns within 30 days of delivery.</textarea>

<textarea class="form-control mb-3">1-year manufacturer warranty.</textarea>

</div>

<div class="text-end">
<button class="btn btn-brand">Save Policies</button>
</div>

</div>

<!-- MULTI CHANNEL -->
<div id="channels" class="tab-content d-none">

<div class="card-custom">

<h5 class="fw-bold mb-2">Sales Channels</h5>
<p class="text-muted mb-4">Connect your store to other platforms.</p>

<div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
  <div class="d-flex gap-3 align-items-center">
    <div class="bg-warning text-white p-3 rounded">S</div>
    <div>
      <strong>Shopee</strong><br>
      <small class="text-muted">Sync products and orders</small>
    </div>
  </div>
  <input type="checkbox" checked>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
  <div class="d-flex gap-3 align-items-center">
    <div class="bg-primary text-white p-3 rounded">L</div>
    <div>
      <strong>Lazada</strong><br>
      <small class="text-muted">Sync products and orders</small>
    </div>
  </div>
  <input type="checkbox">
</div>

<div class="d-flex justify-content-between align-items-center p-3 border rounded">
  <div class="d-flex gap-3 align-items-center">
    <div class="bg-dark text-white p-3 rounded">T</div>
    <div>
      <strong>TikTok Shop</strong><br>
      <small class="text-muted">Sync products and orders</small>
    </div>
  </div>
  <input type="checkbox">
</div>

</div>

</div>

</div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
}

function switchTab(tab) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.add('d-none'));
  document.getElementById(tab).classList.remove('d-none');

  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('tab-active'));
  event.target.classList.add('tab-active');
}
</script>

</body>
</html>