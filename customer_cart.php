<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Cart</title>

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

  @media (max-width: 768px) {
    .main-content {
      margin-left: 0;
      padding: 80px 20px 20px 20px;
    }
    .sidebar.collapsed ~ .main-content {
      margin-left: 0;
    }
  }

  /* ===== ADDITIONAL CART UI STYLES ===== */
  .cart-container {
    max-width: 1400px;
    margin: 0 auto;
  }

  .cart-header h2 {
    font-weight: 800;
    color: #2c3e2f;
    letter-spacing: -0.3px;
  }

  .cart-item-card {
    background: white;
    border-radius: 28px;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.02), 0 2px 4px rgba(0, 0, 0, 0.02);
    border: 1px solid #efe3d8;
    padding: 1.2rem;
    margin-bottom: 1rem;
    transition: all 0.2s;
  }

  .product-icon {
    width: 64px;
    height: 64px;
    background: #f8f1ea;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #5e3a2c;
  }

  .product-title {
    font-weight: 700;
    font-size: 1.05rem;
    margin-bottom: 4px;
  }

  .product-color {
    font-size: 0.75rem;
    color: #7a6856;
  }

  .price-main {
    font-weight: 700;
    color: #1f2e1d;
  }

  .quantity-selector {
    background: #f3ede7;
    border-radius: 60px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 4px 12px;
  }

  .qty-btn {
    background: transparent;
    border: none;
    font-size: 1.2rem;
    font-weight: 600;
    width: 28px;
    border-radius: 30px;
    color: #5c432e;
    transition: 0.1s;
  }

  .qty-btn:hover {
    background: #e1d5ca;
  }

  .remove-btn {
    background: none;
    border: none;
    color: #b35f3a;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 40px;
    transition: 0.2s;
  }

  .remove-btn:hover {
    background: #fae8df;
    color: #8e3a1a;
  }

  .order-summary-card {
    background: #ffffffec;
    backdrop-filter: blur(2px);
    border-radius: 32px;
    padding: 1.6rem;
    border: 1px solid #e8dfd6;
    box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 30px;
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    font-size: 1rem;
  }

  .total-row {
    font-weight: 800;
    font-size: 1.3rem;
    border-top: 2px dashed #ded0c4;
    padding-top: 1rem;
    margin-top: 0.5rem;
  }

  .btn-checkout-custom {
    background: #2b5e2f;
    border: none;
    padding: 14px 0;
    font-weight: 600;
    border-radius: 60px;
    width: 100%;
    transition: 0.2s;
    color: white;
  }

  .btn-checkout-custom:hover {
    background: #6e0f25;
    transform: scale(0.98);
  }

  .continue-shopping-link {
    font-weight: 500;
    color: #6e4f32;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: 0.2s;
  }

  .continue-shopping-link:hover {
    color: #6e0f25;
    border-bottom-color: #6e0f25;
  }

  .empty-cart-placeholder {
    background: #fffaf5;
    border-radius: 2rem;
    padding: 3rem;
    text-align: center;
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

<!-- ========= MAIN CONTENT========= -->
<div class="main-content">
  <div id="cartRoot" class="cart-container">
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

let cartItems = [
  {
    id: 1,
    name: "Sony WH-1000XM4 Wireless Headphones",
    price: 16999,
    color: "Silver",
    quantity: 1,
    icon: "bi-headphones"
  },
  {
    id: 2,
    name: "Minimalist Desk Lamp",
    price: 4900,
    color: "Matte Black",
    quantity: 1,
    icon: "bi-lamp"
  }
];

function formatPHP(amount) {
  return '₱' + amount.toLocaleString('en-PH');
}

function renderCart() {
  const root = document.getElementById('cartRoot');
  if (!root) return;

  const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  const total = subtotal; 
  const itemCount = cartItems.length;
  const totalQty = cartItems.reduce((sum, i) => sum + i.quantity, 0);

  if (cartItems.length === 0) {
    root.innerHTML = `
      <div class="empty-cart-placeholder">
        <i class="bi bi-bag-x fs-1" style="color:#b37b5a;"></i>
        <h4 class="mt-3 fw-semibold">Your cart is empty</h4>
        <p class="text-muted">Looks like you haven't added anything yet.</p>
      </div>
    `;
    const emptyBtn = document.getElementById('emptyContinueBtn');
    if (emptyBtn) emptyBtn.addEventListener('click', (e) => {
      e.preventDefault();
      alert("✨ Explore our collections and find your next favorite item!");
    });
    return;
  }

  let itemsHtml = '';
  cartItems.forEach(item => {
    const itemTotal = item.price * item.quantity;
    itemsHtml += `
      <div class="cart-item-card" data-id="${item.id}">
        <div class="row align-items-center g-3">
          <div class="col-md-2 col-3">
            <div class="product-icon">
              <i class="bi ${item.icon}"></i>
            </div>
          </div>
          <div class="col-md-4 col-9">
            <div class="product-title">${escapeHtml(item.name)}</div>
            <div class="product-color"><i class="bi bi-palette-fill me-1" style="font-size:0.7rem;"></i> Color: ${escapeHtml(item.color)}</div>
            <div class="price-main mt-1">${formatPHP(item.price)}</div>
          </div>
          <div class="col-md-3 col-6">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="quantity-selector">
                <button class="qty-btn dec-qty" data-id="${item.id}">−</button>
                <span class="fw-semibold mx-1" style="min-width: 28px; text-align:center;">${item.quantity}</span>
                <button class="qty-btn inc-qty" data-id="${item.id}">+</button>
              </div>
              <button class="remove-btn" data-id="${item.id}"><i class="bi bi-trash3 me-1"></i> Remove</button>
            </div>
          </div>
          <div class="col-md-3 col-6 text-md-end">
            <div class="fw-bold fs-6">${formatPHP(itemTotal)}</div>
          </div>
        </div>
      </div>
    `;
  });

  const fullHtml = `
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div class="cart-header">
        <h2 class="mb-0"><i class="bi bi-bag-heart me-2" style="color:#6e0f25;"></i> Shopping Cart</h2>
        <p class="text-muted mt-1">${itemCount} item${itemCount !== 1 ? 's' : ''} in your cart ${totalQty > 0 ? `• ${totalQty} total piece${totalQty !== 1 ? 's' : ''}` : ''}</p>
      </div>
      <div>
        <a href="customer_home.php" id="topContinueLink" class="continue-shopping-link"><i class="bi bi-arrow-left-short"></i> Continue Shopping</a>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        ${itemsHtml}
      </div>
      <div class="col-lg-4">
        <div class="order-summary-card">
          <h5 class="fw-bold mb-3">Order Summary</h5>
          <div class="summary-row">
            <span>Subtotal</span>
            <span id="summarySubtotal">${formatPHP(subtotal)}</span>
          </div>
          <div class="summary-row">
            <span>Shipping</span>
            <span class="text-success">Free</span>
          </div>
          <div class="total-row d-flex justify-content-between">
            <span>Total</span>
            <span id="summaryTotal" class="fw-bold">${formatPHP(total)}</span>
          </div>
          <button class="btn btn-checkout-custom mt-4" id="proceedCheckoutBtn">Proceed to Checkout →</button>
        </div>
      </div>
    </div>
  `;

  root.innerHTML = fullHtml;

  // attach event listeners for cart actions
  document.querySelectorAll('.inc-qty').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = parseInt(btn.getAttribute('data-id'));
      updateQuantity(id, +1);
    });
  });
  document.querySelectorAll('.dec-qty').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = parseInt(btn.getAttribute('data-id'));
      updateQuantity(id, -1);
    });
  });
  document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const id = parseInt(btn.getAttribute('data-id'));
      removeItem(id);
    });
  });

  // continue shopping links
  const topLink = document.getElementById('topContinueLink');
  const bottomLink = document.getElementById('bottomContinueLink');
  const proceedBtn = document.getElementById('proceedCheckoutBtn');
  if (bottomLink) bottomLink.addEventListener('click', (e) => { e.preventDefault(); alert("🛍️ Continue browsing — discover new arrivals!"); });
  if (proceedBtn) proceedBtn.addEventListener('click', () => {
    if (cartItems.length === 0) {
      alert("Your cart is empty. Add items before checkout 🛒");
      return;
    }
    const totalAmount = cartItems.reduce((s, i) => s + (i.price * i.quantity), 0);
    alert(`✅ Proceeding to secure checkout!\nTotal amount: ${formatPHP(totalAmount)}\nThank you for shopping with us.`);
  });
}

function updateQuantity(id, delta) {
  const idx = cartItems.findIndex(i => i.id === id);
  if (idx === -1) return;
  const newQty = cartItems[idx].quantity + delta;
  if (newQty < 1) {
    removeItem(id);
  } else {
    cartItems[idx].quantity = newQty;
    renderCart();
  }
}

function removeItem(id) {
  cartItems = cartItems.filter(i => i.id !== id);
  renderCart();
}

function escapeHtml(str) {
  return str.replace(/[&<>]/g, function(m) {
    if (m === '&') return '&amp;';
    if (m === '<') return '&lt;';
    if (m === '>') return '&gt;';
    return m;
  });
}

// initial render
renderCart();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>