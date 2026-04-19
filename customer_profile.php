<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
  body { background: #f5f1ee; font-family: 'Inter', sans-serif; }

  .main-content {
    margin-left: 240px;
    transition: 0.3s;
    padding: 30px;
  }

  .sidebar.collapsed ~ .main-content {
    margin-left: 70px;
  }

  .top-card{
    background: linear-gradient(90deg,#ffffff,#efe7e7);
    border-radius: 16px;
    padding: 24px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:20px;
  }

  .profile{display:flex;align-items:center;gap:15px;}
  .profile img{width:60px;height:60px;border-radius:50%;cursor:pointer;}

  .avatar-wrapper{position:relative;}
  .camera-btn{
    position:absolute;
    bottom:0;
    right:0;
    background:#6e0f25;
    color:#fff;
    border-radius:50%;
    width:22px;
    height:22px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    cursor:pointer;
  }
  .profile span{color:#b3a6a0;}

  .btn-maroon{background:#6e0f25;color:#fff;border:none;padding:12px 20px;border-radius:10px;}

  .layout{display:flex; gap:25px; margin-top:25px; align-items:flex-start;}

  .side-card{
    width:240px;
    background:#fff;
    border-radius:16px;
    padding:18px;
  }

  .menu-item{
    padding:10px 14px;
    border-radius:10px;
    cursor:pointer;
    margin-bottom:6px;
  }

  .menu-item.active{
    background:#e9e2dc;
    color:#6e0f25;
    font-weight:600;
  }

  .menu-item:hover{background:#f1ece8;}

  .content-card{
    flex:1;
    background:#fff;
    border-radius:16px;
    padding:30px;
  }

  .form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
  }

  .form-group label{font-size:14px;margin-bottom:6px;}

  .form-group input{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:1px solid #e5ded9;
    background:#faf7f5;
  }

  .section{display:none;}
  .section.active{display:block;}

  .save-btn{margin-top:20px;display:flex;justify-content:flex-end;}

  #notifications .form-check {
    margin-bottom: 4rem;
  }

  /* FIX POSITION TOP RIGHT */
  .notification-wrapper {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
  }

  /* Dropdown width like your UI */
  .notification-panel {
    width: 300px;
    border-radius: 12px;
  }

  /* Each notification spacing */
  .notif-item {
    padding: 8px 0;
    cursor: pointer;
    transition: background 0.2s;
  }
  .notif-item:hover {
    background: #f8f9fa;
  }
  .notif-item.unread {
    background: #fef2e8;
    border-left: 3px solid #6e0f25;
    padding-left: 12px;
  }
  .notif-item .fw-bold {
    font-weight: 600;
  }
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="logo-text">Customer</div>
  </div>

  <a href="customer_profile.php"><i class="bi bi-person-circle"></i><span class="text">Profile</span></a>
  <a href="customer_home.php"><i class="bi bi-house"></i><span class="text">Home</span></a>
  <a href="customer_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
  <a href="customer_cart.php"><i class="bi bi-cart-check"></i><span class="text">Cart</span></a>
  <a href="customer_wishlist.php"><i class="bi bi-bookmark-heart"></i><span class="text">Wishlist</span></a>
  <a href="customer_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat & Support</span></a>

  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>
</div>

<!-- TOP RIGHT NOTIFICATION -->
<div class="notification-wrapper">
  <div class="dropdown">
    <button class="btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-bell fs-4"></i>
      <!-- Badge: dynamic unread count -->
      <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
    </button>

    <!-- Dropdown Panel -->
    <div class="dropdown-menu dropdown-menu-end p-3 shadow notification-panel" style="min-width: 300px;">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Notifications</strong>
        <a href="#" id="markAllReadBtn" class="text-danger small text-decoration-none">Mark all read</a>
      </div>
      <hr class="mt-1 mb-2">

      <!-- Notification Items (each with unread class) -->
      <div id="notificationsList">
        <div class="notif-item unread" data-notif-id="1">
          <div class="fw-bold">Order Shipped</div>
          <small class="text-muted">Your order #ORD-9021 has been shipped.</small>
          <div class="text-end text-muted small">10 mins ago</div>
        </div>
        <hr>
        <div class="notif-item unread" data-notif-id="2">
          <div class="fw-bold">Flash Sale!</div>
          <small class="text-muted">Get 20% off on electronics today.</small>
          <div class="text-end text-muted small">1 hour ago</div>
        </div>
        <hr>
        <div class="notif-item unread" data-notif-id="3">
          <div class="fw-bold">Security Alert</div>
          <small class="text-muted">New login from Chrome on Windows.</small>
          <div class="text-end text-muted small">1 day ago</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content" id="main">
<div class="container-fluid">

<h2 class="fw-bold">Account Settings</h2>
<p class="text-muted">Manage your profile, preferences, and security.</p>

<div class="top-card">
  <div class="profile">
    <div class="avatar-wrapper">
      <img id="profileImage">
      <div class="camera-btn" onclick="openPhotoModal()"><i class="bi bi-camera"></i></div>
    </div>
    <span>jane.doe@example.com • Customer</span>
  </div>
  <button class="btn-maroon" onclick="switchRole()"><i class="bi bi-arrow-repeat me-2"></i>Switch Role</button>
</div>

<div class="layout">

  <!-- LEFT MENU -->
  <div class="side-card">
    <div class="menu-item active" data-section="personal"><i class="bi bi-person me-2"></i>Personal Information</div>
    <div class="menu-item" data-section="addresses"><i class="bi bi-geo-alt me-2"></i>Addresses</div>
    <div class="menu-item" data-section="payments"><i class="bi bi-credit-card me-2"></i>Payment Methods</div>
    <div class="menu-item" data-section="notifications"><i class="bi bi-bell me-2"></i>Notifications</div>
    <div class="menu-item" data-section="password"><i class="bi bi-key me-2"></i>Change Password</div>
  </div>

  <!-- RIGHT CONTENT -->
  <div class="content-card">
    <!-- PERSONAL -->
    <div class="section active" id="personal">
      <h5 class="fw-bold mb-4">Personal Information</h5>
      <div class="form-grid">
        <div class="form-group"><label>First Name</label><input value="Jane"></div>
        <div class="form-group"><label>Last Name</label><input value="Doe"></div>
        <div class="form-group"><label>Email</label><input value="jane.doe@example.com"></div>
        <div class="form-group"><label>Phone</label><input value="+63 912 345 6789"></div>
      </div>
      <div class="save-btn"><button class="btn-maroon">Save Changes</button></div>
    </div>

    <!-- ADDRESSES -->
    <div class="section" id="addresses">
      <h5 class="fw-bold mb-4">Addresses</h5>
      <p class="text-muted">No addresses saved yet.</p>
      <button class="btn-maroon"><i class="bi bi-geo-alt"></i> Add Address</button>
    </div>

    <!-- PAYMENTS -->
    <div class="section" id="payments">
      <h5 class="fw-bold mb-4">Payment Methods</h5>
      <p class="text-muted">No saved payment methods.</p>
      <button class="btn-maroon"><i class="bi bi-wallet2"></i> Add New</button>
    </div>

    <!-- NOTIFICATIONS -->
    <div class="section" id="notifications">
      <h5 class="fw-bold mb-4">Notification Preferences</h5>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" checked>
        <label><b>Order Updates</b></label>
        <h5 style="font-weight:normal; font-size:14px; margin-top:5px;">Get notified about your order status and delivery</h5>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" checked>
        <label><b>Seller Promotions</b></label>
        <h5 style="font-weight:normal; font-size:14px; margin-top:5px;">Receive updates about special offers and promotions</h5>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" checked>
        <label><b>System Alerts</b></label>
        <h5 style="font-weight:normal; font-size:14px; margin-top:5px;">Receive important system notifications</h5>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" checked>
        <label><b>Email Notifications</b></label>
        <h5 style="font-weight:normal; font-size:14px; margin-top:5px;">Receive a daily summary of your activity</h5>
      </div>
    </div>

    <!-- PASSWORD -->
    <div class="section" id="password">
      <h5 class="fw-bold mb-4">Change Password</h5>
      <div class="form-group mb-3"><label>Current Password</label><input type="password"></div>
      <div class="form-group mb-3"><label>New Password</label><input type="password"></div>
      <div class="form-group mb-3"><label>Confirm Password</label><input type="password"></div>
      <button class="btn-maroon">Update Password</button>
    </div>
  </div>
</div>

<!-- PHOTO MODAL -->
<div id="photoModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1100;">
  <div style="background:#fff; padding:25px; border-radius:12px; width:300px; text-align:center;">
    <h6 class="fw-bold mb-3">Change Profile Photo</h6>
    <input type="file" id="fileInput" accept="image/*" class="form-control mb-3">
    <div style="display:flex; gap:10px; justify-content:end;">
      <button class="btn btn-light" onclick="closePhotoModal()">Cancel</button>
      <button class="btn-maroon" onclick="savePhoto()">Save</button>
    </div>
  </div>
</div>

</div>
</div>

<script>
// Sidebar toggle
function toggleSidebar(){
  document.getElementById("sidebar").classList.toggle("collapsed");
}

// Left menu switching
const menuItems = document.querySelectorAll('.menu-item');
menuItems.forEach(item=>{
  item.addEventListener('click',()=>{
    document.querySelectorAll('.menu-item').forEach(i=>i.classList.remove('active'));
    item.classList.add('active');
    document.querySelectorAll('.section').forEach(sec=>sec.classList.remove('active'));
    document.getElementById(item.dataset.section).classList.add('active');
  });
});

// Photo modal
function openPhotoModal(){
  document.getElementById('photoModal').style.display='flex';
}
function closePhotoModal(){
  document.getElementById('photoModal').style.display='none';
}
function savePhoto(){
  const file = document.getElementById('fileInput').files[0];
  if(file){
    const reader = new FileReader();
    reader.onload = function(e){
      document.getElementById('profileImage').src = e.target.result;
    }
    reader.readAsDataURL(file);
  }
  closePhotoModal();
}

// ---------- NOTIFICATION MARK ALL READ LOGIC ----------
function updateUnreadBadge() {
  const unreadCount = document.querySelectorAll('.notif-item.unread').length;
  const badge = document.getElementById('notifBadge');
  if (unreadCount === 0) {
    badge.style.display = 'none';
  } else {
    badge.style.display = 'inline-block';
    badge.innerText = unreadCount;
  }
}

// Mark a single notification as read (optional – you can call this when clicking an item)
function markAsRead(element) {
  if (element.classList.contains('unread')) {
    element.classList.remove('unread');
    updateUnreadBadge();
  }
}

// Mark all as read
function markAllAsRead() {
  const unreadItems = document.querySelectorAll('.notif-item.unread');
  unreadItems.forEach(item => {
    item.classList.remove('unread');
  });
  updateUnreadBadge();
}

// Optional: click on a notification marks it as read and could navigate or show details
document.querySelectorAll('.notif-item').forEach(item => {
  item.addEventListener('click', function(e) {
    // Don't trigger if the click was on "Mark all read" link (prevent bubbling)
    if (e.target.closest('#markAllReadBtn')) return;
    markAsRead(this);
    // You can add additional logic here (e.g., open order details)
  });
});

// Attach event to "Mark all read" button
document.getElementById('markAllReadBtn').addEventListener('click', function(e) {
  e.preventDefault();
  markAllAsRead();
});

// Initial badge update
updateUnreadBadge();

// Dummy function for switch role (you can implement later)
function switchRole() {
  alert("Role switching would redirect to seller panel.");
}
</script>

<!-- BOOTSTRAP JS (required for dropdown) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>