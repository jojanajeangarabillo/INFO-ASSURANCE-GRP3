<?php
require_once 'auth.php';
require_roles([2, 4]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Wishlist</title>

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
    padding: 70px 30px 30px 30px;
  }

  .sidebar.collapsed ~ .main-content {
    margin-left: 70px;
  }
  .notification-wrapper {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
  }

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
  .wishlist-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.75rem;
    margin-bottom: 1.75rem;
  }
  
  .wishlist-item-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
    border: 1px solid #f0eae4;
  }
  
  .wishlist-item-card:hover {
    box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.12);
    border-color: #e2d9d2;
    transform: translateY(-2px);
  }
  
  .product-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 8px;
  }
  
  .stars-group {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    color: #f5b342;
    font-size: 0.9rem;
  }
  
  .rating-value {
    font-weight: 700;
    color: #2c3e2f;
    background: #f8f3ef;
    padding: 2px 8px;
    border-radius: 40px;
    font-size: 0.8rem;
  }
  
  .product-title {
    font-size: 1.35rem;
    font-weight: 700;
    letter-spacing: -0.2px;
    color: #1e2a23;
    margin: 6px 0 4px 0;
  }
  
  .product-price {
    font-size: 1.7rem;
    font-weight: 800;
    color: #b23c1c;
    letter-spacing: -0.3px;
    line-height: 1.2;
  }
  
  .product-price small {
    font-size: 0.9rem;
    font-weight: 500;
    color: #7f6e62;
  }
  
  .btn-add-wishlist {
    background: #6e0f25;;    
    border: none;
    padding: 0.7rem 1.8rem;
    font-weight: 600;
    border-radius: 60px;
    transition: 0.2s;
    font-size: 0.9rem;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    color: white;            
  }
  
  .btn-add-wishlist i {
    color: white;            
  }
  
  .btn-add-wishlist:hover {
    background: #6e0f25;;    
    transform: scale(1.02);
    box-shadow: 0 8px 18px rgba(44, 122, 77, 0.3);
    color: white;
  }
  
  .btn-add-wishlist:hover i {
    color: white;
  }
  
  .product-icon {
    width: 70px;
    height: 70px;
    background: #f9f5f0;
    border-radius: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #b23c1c;
    margin-right: 1rem;
    transition: 0.2s;
  }
  
  .item-count-badge {
    background: #e9dfd7;
    color: #4a3b2f;
    font-weight: 600;
    padding: 0.4rem 1rem;
    border-radius: 60px;
    font-size: 0.85rem;
  }
  
  @media (max-width: 768px) {
    .main-content {
      margin-left: 0;
      padding: 80px 20px 20px 20px;
    }
    .sidebar.collapsed ~ .main-content {
      margin-left: 0;
    }
    .wishlist-item-card {
      padding: 1rem;
    }
    .product-title {
      font-size: 1.2rem;
    }
    .product-price {
      font-size: 1.4rem;
    }
    .product-icon {
      width: 55px;
      height: 55px;
      font-size: 1.6rem;
    }
    .btn-add-wishlist {
      padding: 0.5rem 1.2rem;
      font-size: 0.8rem;
    }
  }
</style>
</head>
<body>

<!-- ========= SIDEBAR ========= -->
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

<!-- ========= ORIGINAL NOTIFICATION BELL ========= -->
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

<!-- ========= MAIN CONTENT: WISHLIST UI (EXACT REFERENCE) ========= -->
<div class="main-content">
  <div class="container-fluid px-0">
    <!-- Wishlist header: matches "My Wishlist" + "2 items saved" exactly -->
    <div class="d-flex flex-wrap justify-content-between align-items-center wishlist-header">
      <div>
        <h1 class="display-6 fw-bold" style="color: #2c3e2f; letter-spacing: -0.5px;">My Wishlist</h1>
        <p class="text-muted mt-1 mb-0" style="font-size: 0.9rem;">Save your favorite items & shop later</p>
      </div>
      <div class="item-count-badge mt-2 mt-sm-0">
        <i class="bi bi-bookmark-heart-fill me-1" style="color:#b23c1c;"></i> 2 items saved
      </div>
    </div>

    <div class="wishlist-item-card">
      <div class="row align-items-center gy-4">
        <div class="col-md-1 col-2 text-center text-md-start">
          <div class="product-icon">
            <i class="bi bi-headphones"></i>
          </div>
        </div>
        <div class="col-md-7 col-10">
          <div class="product-rating">
            <div class="stars-group">

              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-half"></i>
            </div>
            <span class="rating-value">4.8 (1,240 reviews)</span>
          </div>
          <h3 class="product-title">Sony WH-1000XM4</h3>
          <div class="product-price mt-2">
            ₱16,999 <small>PHP</small>
          </div>
        </div>
        <div class="col-md-4 text-md-end text-start">
          <button class="btn btn-add-wishlist add-to-cart-btn" data-product="Sony WH-1000XM4" data-price="16999">
            <i class="bi bi-cart-plus me-2"></i>Add to Cart
          </button>
        </div>
      </div>
    </div>

    <div class="wishlist-item-card">
      <div class="row align-items-center gy-4">
        <div class="col-md-1 col-2 text-center text-md-start">
          <div class="product-icon">
            <i class="bi bi-keyboard"></i>
          </div>
        </div>
        <div class="col-md-7 col-10">
          <div class="product-rating">
            <div class="stars-group">
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-half"></i>
            </div>
            <span class="rating-value">4.9 (850 reviews)</span>
          </div>
          <h3 class="product-title">Mechanical Keyboard</h3>
          <div class="product-price mt-2">
            ₱5,200 <small>PHP</small>
          </div>
        </div>
        <div class="col-md-4 text-md-end text-start">
          <button class="btn btn-add-wishlist add-to-cart-btn" data-product="Mechanical Keyboard" data-price="5200">
            <i class="bi bi-cart-plus me-2"></i>Add to Cart
          </button>
        </div>
      </div>
    </div>

    <div class="mt-4 text-muted small text-center opacity-50">
      <i class="bi bi-heart-fill me-1" style="color:#e0b39e;"></i> Keep your favorites — ready when you are
    </div>
  </div>
</div>

<script>
// ========== SIDEBAR TOGGLE ==========
function toggleSidebar(){
  document.getElementById("sidebar").classList.toggle("collapsed");
}

// ==========  NOTIFICATION LOGIC ==========
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

// ========== ADD TO CART INTERACTION ==========
document.querySelectorAll('.add-to-cart-btn').forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault();
    const productName = this.getAttribute('data-product') || 'item';
    const priceSpan = this.closest('.wishlist-item-card')?.querySelector('.product-price')?.innerText || '';
    const toastMsg = document.createElement('div');
    toastMsg.innerText = `✨ ${productName} added to cart!`;
    toastMsg.style.position = 'fixed';
    toastMsg.style.bottom = '30px';
    toastMsg.style.left = '50%';
    toastMsg.style.transform = 'translateX(-50%)';
    toastMsg.style.backgroundColor = '#6e0f25;';  
    toastMsg.style.color = '#030202';
    toastMsg.style.padding = '12px 24px';
    toastMsg.style.borderRadius = '60px';
    toastMsg.style.fontWeight = '600';
    toastMsg.style.fontSize = '0.9rem';
    toastMsg.style.zIndex = '9999';
    toastMsg.style.boxShadow = '0 12px 22px rgba(0,0,0,0.2)';
    toastMsg.style.backdropFilter = 'blur(4px)';
    toastMsg.style.fontFamily = "'Inter', sans-serif";
    toastMsg.style.letterSpacing = '0.2px';
    document.body.appendChild(toastMsg);
    setTimeout(() => {
      toastMsg.style.opacity = '0';
      toastMsg.style.transition = 'opacity 0.2s ease';
      setTimeout(() => toastMsg.remove(), 300);
    }, 1800);
    
    console.log(`🛒 Added to cart: ${productName} — ${priceSpan}`);
  });
});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>