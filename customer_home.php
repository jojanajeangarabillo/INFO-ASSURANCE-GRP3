<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Home</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
  body { 
    background: #f5f1ee; 
    font-family: 'Inter', sans-serif; 
  }

  .main-content {
    margin-left: 240px;
    transition: 0.3s;
    /* Increased top padding to avoid overlapping with fixed notification bell */
    padding: 70px 30px 30px 30px;
  }

  .sidebar.collapsed ~ .main-content {
    margin-left: 70px;
  }

  /* FIX POSITION TOP RIGHT - notification bell stays fixed */
  .notification-wrapper {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
  }

  /* Dropdown panel styling */
  .notification-panel {
    width: 340px;
    border-radius: 16px;
    border: none;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.02);
  }

  .notif-item {
    padding: 12px 0;
    cursor: pointer;
    transition: background 0.2s;
    border-radius: 12px;
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

  /* Product card custom styling */
  .product-card {
    border: none;
    border-radius: 20px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background: white;
    overflow: hidden;
    height: 100%;
  }
  .product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -12px rgba(0,0,0,0.15);
  }
  .product-icon {
    background: #f9f5f2;
    padding: 24px 0;
    text-align: center;
    border-bottom: 1px solid #f0eae5;
  }
  .product-icon i {
    font-size: 3.2rem;
    color: #6e0f25;
  }
  .product-title {
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: #1f1a17;
  }
  .rating-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
  }
  .rating-value {
    font-weight: 700;
    color: #f4a261;
    background: #fff1e6;
    padding: 4px 8px;
    border-radius: 40px;
    font-size: 0.85rem;
  }
  .review-count {
    font-size: 0.8rem;
    color: #7c6e65;
  }
  .product-price {
    font-size: 1.5rem;
    font-weight: 800;
    color: #2c2c2c;
    margin: 12px 0 16px 0;
  }
  .btn-add-cart {
    background: #6e0f25;
    color: white;
    border: none;
    border-radius: 40px;
    padding: 10px 0;
    font-weight: 600;
    width: 100%;
    transition: all 0.2s;
  }
  .btn-add-cart:hover {
    background: #8c1c36;
    transform: scale(0.98);
    color: white;
  }
  .section-title {
    font-weight: 800;
    font-size: 1.9rem;
    margin-bottom: 1.8rem;
    position: relative;
    display: inline-block;
    color: #2d2a27;
  }
  .section-title:after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 60px;
    height: 3px;
    background: #6e0f25;
    border-radius: 4px;
  }
  
  /* HEADER + FILTERS STYLES */
  .brand-nav {
    background: white;
    border-radius: 24px;
    padding: 12px 24px;
    margin-bottom: 28px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02), 0 1px 2px rgba(0,0,0,0.03);
  }
  .logo-text-duo {
    font-weight: 800;
    font-size: 1.6rem;
    line-height: 1.2;
    letter-spacing: -0.02em;
  }
  .logo-text-duo .j3rs {
    color: #6e0f25;
    margin-left: 0px;
  }
  .nav-links {
    display: flex;
    gap: 2rem;
    font-weight: 500;
    color: #3c2f2a;
  }
  .nav-links a {
    text-decoration: none;
    color: #3c2f2a;
    transition: color 0.2s;
    font-size: 0.95rem;
  }
  .nav-links a:hover {
    color: #6e0f25;
  }
  .search-wrapper {
    background: #f5f1ee;
    border-radius: 60px;
    padding: 6px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    width: 260px;
  }
  .search-wrapper i {
    color: #9e8b7e;
    font-size: 1.1rem;
  }
  .search-wrapper input {
    background: transparent;
    border: none;
    outline: none;
    font-size: 0.9rem;
    width: 100%;
    padding: 6px 0;
    font-family: 'Inter', sans-serif;
  }
  .user-area {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fef7f2;
    padding: 5px 16px 5px 12px;
    border-radius: 50px;
  }
  .user-avatar {
    background: #6e0f25;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
  }
  .user-info {
    line-height: 1.2;
  }
  .user-name {
    font-weight: 700;
    font-size: 0.85rem;
    color: #2d2a27;
  }
  .user-role {
    font-size: 0.7rem;
    color: #8b6f5e;
  }
  .filter-section {
    background: white;
    border-radius: 20px;
    padding: 12px 20px;
    margin-bottom: 32px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
  }
  .filter-label {
    font-weight: 700;
    color: #2d2a27;
    font-size: 0.85rem;
    letter-spacing: 0.3px;
  }
  .filter-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .filter-chip {
    background: #f5f1ee;
    padding: 6px 18px;
    border-radius: 40px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #3c2f2a;
    cursor: pointer;
    transition: all 0.2s;
  }
  .filter-chip.active {
    background: #6e0f25;
    color: white;
  }
  .filter-chip:hover {
    background: #e3dbd4;
  }
  @media (max-width: 992px) {
    .brand-nav {
      flex-wrap: wrap;
      gap: 16px;
    }
    .search-wrapper {
      width: 100%;
      max-width: 300px;
    }
  }
  @media (max-width: 768px) {
    .main-content {
      margin-left: 0;
      padding: 80px 20px 20px 20px;
    }
    .sidebar.collapsed ~ .main-content {
      margin-left: 0;
    }
    .section-title {
      font-size: 1.6rem;
    }
    .nav-links {
      gap: 1.2rem;
    }
  }
  hr {
    opacity: 0.3;
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

<!-- TOP RIGHT NOTIFICATION (fixed) -->
<div class="notification-wrapper">
  <div class="dropdown">
    <button class="btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-bell fs-4"></i>
      <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
    </button>

    <div class="dropdown-menu dropdown-menu-end p-3 shadow notification-panel">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="fs-6">Notifications</strong>
        <a href="#" id="markAllReadBtn" class="text-danger small text-decoration-none fw-semibold">Mark all as Read</a>
      </div>
      <hr class="mt-1 mb-2">
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-truck text-danger mt-1"></i>
          <div>
            <div class="fw-bold">Order #SP-2345 shipped</div>
            <small class="text-muted">Your Sony WH-1000XM4 is on the way</small>
            <div class="text-muted small">2 hours ago</div>
          </div>
        </div>
      </div>
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-tag-fill text-success mt-1"></i>
          <div>
            <div class="fw-bold">Flash Sale: Mechanical Keyboard</div>
            <small class="text-muted">Up to 20% off for limited time</small>
            <div class="text-muted small">Yesterday</div>
          </div>
        </div>
      </div>
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-cup-hot text-warning mt-1"></i>
          <div>
            <div class="fw-bold">New ceramic collection</div>
            <small class="text-muted">Handcrafted mugs just dropped</small>
            <div class="text-muted small">2 days ago</div>
          </div>
        </div>
      </div>
      <div class="notif-item">
        <div class="d-flex gap-2">
          <i class="bi bi-check-circle text-secondary mt-1"></i>
          <div>
            <div class="fw-bold">Welcome to ShopHub!</div>
            <small class="text-muted">Complete your profile for perks</small>
            <div class="text-muted small">5 days ago</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MAIN CONTENT-->
<div class="main-content">
  <div class="container-fluid px-0">
    
    <div class="brand-nav d-flex flex-wrap align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-4 flex-wrap">
        <div class="logo-text-duo">
          <img src="JERS-LOGO.PNG" alt="JERS Logo" class="img-fluid" style="width: 100px; height: auto;">
        </div>
        <!-- Nav links -->
        <div class="nav-links">
          <a href="#">Home</a>
          <a href="#">Shop</a>
          <a href="#">Categories</a>
        </div>
      </div>
      
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <!-- Search bar -->
        <div class="search-wrapper">
          <i class="bi bi-search"></i>
          <input type="text" placeholder="Search products...">
        </div>
        <!-- User area: Jane Doe / Customer -->
        <div class="user-area">
          <div class="user-avatar">JD</div>
          <div class="user-info">
            <div class="user-name">Jane Doe</div>
            <div class="user-role">Customer</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters row -->
    <div class="filter-section">
      <span class="filter-label">Filters</span>
      <div class="filter-chips">
        <span class="filter-chip active">All</span>
        <span class="filter-chip">Electronics</span>
        <span class="filter-chip">Clothing</span>
        <span class="filter-chip">Home & Garden</span>
        <span class="filter-chip">Sports</span>
        <span class="filter-chip">Beauty</span>
      </div>
    </div>

    <!-- Recommended for You section -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
      <h2 class="section-title">Recommended for You</h2>
    </div>

    <!-- Product Grid: 4 cards -->
    <div class="row g-4">
      <!-- Sony WH-1000XM4 -->
      <div class="col-sm-6 col-lg-3">
        <div class="product-card card h-100">
          <div class="product-icon">
            <i class="bi bi-headphones"></i>
          </div>
          <div class="card-body">
            <h5 class="product-title">Sony WH-1000XM4</h5>
            <div class="rating-wrap">
              <span class="rating-value"><i class="bi bi-star-fill me-1"></i>4.8</span>
              <span class="review-count">(1,240 reviews)</span>
            </div>
            <div class="product-price">P16,999</div>
            <button class="btn-add-cart" onclick="alert('Added Sony WH-1000XM4 to cart')">Add to Cart</button>
          </div>
        </div>
      </div>

      <!-- Minimalist Desk Lamp -->
      <div class="col-sm-6 col-lg-3">
        <div class="product-card card h-100">
          <div class="product-icon">
            <i class="bi bi-lightbulb"></i>
          </div>
          <div class="card-body">
            <h5 class="product-title">Minimalist Desk Lamp</h5>
            <div class="rating-wrap">
              <span class="rating-value"><i class="bi bi-star-fill me-1"></i>4.5</span>
              <span class="review-count">(320 reviews)</span>
            </div>
            <div class="product-price">P2,450</div>
            <button class="btn-add-cart" onclick="alert('Added Minimalist Desk Lamp to cart')">Add to Cart</button>
          </div>
        </div>
      </div>

      <!-- Mechanical Keyboard -->
      <div class="col-sm-6 col-lg-3">
        <div class="product-card card h-100">
          <div class="product-icon">
            <i class="bi bi-keyboard"></i>
          </div>
          <div class="card-body">
            <h5 class="product-title">Mechanical Keyboard</h5>
            <div class="rating-wrap">
              <span class="rating-value"><i class="bi bi-star-fill me-1"></i>4.9</span>
              <span class="review-count">(850 reviews)</span>
            </div>
            <div class="product-price">P5,200</div>
            <button class="btn-add-cart" onclick="alert('Added Mechanical Keyboard to cart')">Add to Cart</button>
          </div>
        </div>
      </div>

      <!-- Ceramic Coffee Mug -->
      <div class="col-sm-6 col-lg-3">
        <div class="product-card card h-100">
          <div class="product-icon">
            <i class="bi bi-cup-straw"></i>
          </div>
          <div class="card-body">
            <h5 class="product-title">Ceramic Coffee Mug</h5>
            <div class="rating-wrap">
              <span class="rating-value"><i class="bi bi-star-fill me-1"></i>4.7</span>
              <span class="review-count">(145 reviews)</span>
            </div>
            <div class="product-price">P850</div>
            <button class="btn-add-cart" onclick="alert('Added Ceramic Coffee Mug to cart')">Add to Cart</button>
          </div>
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

// Notification mark all read logic
function updateUnreadBadge() {
  const unreadCount = document.querySelectorAll('.notif-item.unread').length;
  const badge = document.getElementById('notifBadge');
  if (unreadCount === 0) {
    if(badge) badge.style.display = 'none';
  } else {
    if(badge) {
      badge.style.display = 'inline-block';
      badge.innerText = unreadCount;
    }
  }
}

function markAsRead(element) {
  if (element.classList.contains('unread')) {
    element.classList.remove('unread');
    updateUnreadBadge();
  }
}

function markAllAsRead() {
  const unreadItems = document.querySelectorAll('.notif-item.unread');
  unreadItems.forEach(item => {
    item.classList.remove('unread');
  });
  updateUnreadBadge();
}

document.addEventListener('click', function(e) {
  const notifItem = e.target.closest('.notif-item');
  if (notifItem && !e.target.closest('#markAllReadBtn')) {
    markAsRead(notifItem);
  }
});

const markAllBtn = document.getElementById('markAllReadBtn');
if(markAllBtn) {
  markAllBtn.addEventListener('click', function(e) {
    e.preventDefault();
    markAllAsRead();
  });
}

updateUnreadBadge();

// Filter chip active toggle (visual only)
document.querySelectorAll('.filter-chip').forEach(chip => {
  chip.addEventListener('click', function() {
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
    this.classList.add('active');
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>