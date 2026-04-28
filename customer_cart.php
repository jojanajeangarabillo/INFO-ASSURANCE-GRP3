<?php
require_once 'auth.php';
require_roles([2, 4]);
require_once 'admin/db.connect.php';

$customerId = (int) $_SESSION['user_id'];
$customerName = current_user_name();

// Handle AJAX requests for cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];
    
    if ($action === 'get_cart') {
        // Get cart items with product and variant details including images
        $cartStmt = $conn->prepare("
            SELECT 
                ci.cart_item_id,
                ci.variant_id,
                ci.quantity,
                pv.price,
                pv.size,
                pv.color,
                pv.stock_qty,
                pv.image_path,
                p.product_id,
                p.name as product_name,
                p.category_gender
            FROM cart_item ci
            INNER JOIN cart c ON ci.cart_id = c.cart_id
            INNER JOIN product_variant pv ON ci.variant_id = pv.variant_id
            INNER JOIN product p ON pv.product_id = p.product_id
            WHERE c.user_id = ? AND p.status = 'active'
            ORDER BY ci.created_at DESC
        ");
        $cartStmt->bind_param("i", $customerId);
        $cartStmt->execute();
        $cartItems = $cartStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $items = [];
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $subtotal += $itemTotal;
            
            // Use image from variant table only
            $imagePath = !empty($item['image_path']) ? $item['image_path'] : '';
            
            $items[] = [
                'cart_item_id' => $item['cart_item_id'],
                'variant_id' => $item['variant_id'],
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'size' => $item['size'],
                'color' => $item['color'],
                'price' => (float) $item['price'],
                'quantity' => (int) $item['quantity'],
                'stock_qty' => (int) $item['stock_qty'],
                'item_total' => $itemTotal,
                'category' => $item['category_gender'],
                'image_path' => $imagePath
            ];
        }
        
        $response = [
            'success' => true,
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'item_count' => count($items),
            'total_quantity' => array_sum(array_column($items, 'quantity'))
        ];
        
    } elseif ($action === 'update_quantity') {
        $cartItemId = (int) ($_POST['cart_item_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        
        if ($cartItemId <= 0) {
            $response = ['success' => false, 'message' => 'Invalid cart item'];
            echo json_encode($response);
            exit;
        }
        
        // Check variant stock
        $stockStmt = $conn->prepare("
            SELECT pv.stock_qty, pv.variant_id 
            FROM cart_item ci
            INNER JOIN product_variant pv ON ci.variant_id = pv.variant_id
            WHERE ci.cart_item_id = ?
        ");
        $stockStmt->bind_param("i", $cartItemId);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();
        $stockData = $stockResult->fetch_assoc();
        
        if (!$stockData) {
            $response = ['success' => false, 'message' => 'Item not found'];
            echo json_encode($response);
            exit;
        }
        
        if ($quantity > $stockData['stock_qty']) {
            $response = ['success' => false, 'message' => 'Not enough stock available', 'max_stock' => $stockData['stock_qty']];
            echo json_encode($response);
            exit;
        }
        
        if ($quantity <= 0) {
            // Remove item if quantity is 0
            $deleteStmt = $conn->prepare("DELETE FROM cart_item WHERE cart_item_id = ?");
            $deleteStmt->bind_param("i", $cartItemId);
            $deleteStmt->execute();
            $response = ['success' => true, 'message' => 'Item removed', 'action' => 'removed'];
        } else {
            $updateStmt = $conn->prepare("UPDATE cart_item SET quantity = ?, updated_at = NOW() WHERE cart_item_id = ?");
            $updateStmt->bind_param("ii", $quantity, $cartItemId);
            $updateStmt->execute();
            $response = ['success' => true, 'message' => 'Quantity updated', 'action' => 'updated'];
        }
        
    } elseif ($action === 'remove_item') {
        $cartItemId = (int) ($_POST['cart_item_id'] ?? 0);
        if ($cartItemId > 0) {
            $deleteStmt = $conn->prepare("DELETE FROM cart_item WHERE cart_item_id = ?");
            $deleteStmt->bind_param("i", $cartItemId);
            $deleteStmt->execute();
            $response = ['success' => true, 'message' => 'Item removed from cart'];
        } else {
            $response = ['success' => false, 'message' => 'Invalid item'];
        }
    } elseif ($action === 'checkout_selected') {
        $selectedItems = json_decode($_POST['selected_items'] ?? '[]', true);
        if (empty($selectedItems)) {
            $response = ['success' => false, 'message' => 'No items selected'];
            echo json_encode($response);
            exit;
        }
        
        // Store selected items in session for checkout
        $_SESSION['checkout_items'] = $selectedItems;
        $response = ['success' => true, 'message' => 'Proceeding to checkout'];
    }
    
    echo json_encode($response);
    exit;
}

// Get initial cart data for page load
$cartStmt = $conn->prepare("
    SELECT 
        ci.cart_item_id,
        ci.variant_id,
        ci.quantity,
        pv.price,
        pv.size,
        pv.color,
        pv.stock_qty,
        pv.image_path,
        p.product_id,
        p.name as product_name,
        p.category_gender
    FROM cart_item ci
    INNER JOIN cart c ON ci.cart_id = c.cart_id
    INNER JOIN product_variant pv ON ci.variant_id = pv.variant_id
    INNER JOIN product p ON pv.product_id = p.product_id
    WHERE c.user_id = ? AND p.status = 'active'
    ORDER BY ci.created_at DESC
");
$cartStmt->bind_param("i", $customerId);
$cartStmt->execute();
$cartItems = $cartStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$initialSubtotal = 0;
foreach ($cartItems as $item) {
    $initialSubtotal += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Cart - J3RS</title>

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
  
  .cart-item-card.selected {
    background: #fef8f0;
    border: 2px solid #6e0f25;
    box-shadow: 0 6px 14px rgba(110, 15, 37, 0.1);
  }

  .product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 16px;
    background: #f8f1ea;
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
    color: #6e0f25;
  }

  .product-title {
    font-weight: 700;
    font-size: 1.05rem;
    margin-bottom: 4px;
    color: #1f1a17;
  }

  .product-variant {
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

  .qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
  
  .btn-checkout-custom:disabled {
    background: #b8b8b8;
    cursor: not-allowed;
    transform: none;
  }
  
  .select-all-checkbox {
    cursor: pointer;
    width: 20px;
    height: 20px;
    margin-right: 10px;
  }
  
  .item-checkbox {
    cursor: pointer;
    width: 20px;
    height: 20px;
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
  
  .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .toast-message {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
    background: white;
    border-radius: 50px;
    padding: 12px 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-left: 4px solid #6e0f25;
  }
  
  .selection-header {
    background: white;
    border-radius: 16px;
    padding: 15px 20px;
    margin-bottom: 20px;
    border: 1px solid #efe3d8;
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
  <a href="customer_cart.php" class="active"><i class="bi bi-cart-check"></i><span class="text">Cart</span></a>
  
  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>
</div>

<!-- NOTIFICATION BELL -->
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
            <small class="text-muted">Your order is on the way</small>
            <div class="text-muted small">2 hours ago</div>
          </div>
        </div>
      </div>
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-tag-fill text-success mt-1"></i>
          <div>
            <div class="fw-bold">Flash Sale!</div>
            <small class="text-muted">Limited time discounts</small>
            <div class="text-muted small">Yesterday</div>
          </div>
        </div>
      </div>
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-cup-hot text-warning mt-1"></i>
          <div>
            <div class="fw-bold">New collection</div>
            <small class="text-muted">Fresh styles just dropped</small>
            <div class="text-muted small">2 days ago</div>
          </div>
        </div>
      </div>
      <div class="notif-item">
        <div class="d-flex gap-2">
          <i class="bi bi-check-circle text-secondary mt-1"></i>
          <div>
            <div class="fw-bold">Welcome to J3RS!</div>
            <small class="text-muted">Explore our products</small>
            <div class="text-muted small">5 days ago</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div id="cartRoot" class="cart-container">
    <div class="text-center py-5">
      <div class="spinner-border text-danger" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3">Loading your cart...</p>
    </div>
  </div>
</div>

<script>
// Pass PHP cart data to JavaScript
const initialCartData = {
    items: <?php echo json_encode($cartItems); ?>,
    subtotal: <?php echo $initialSubtotal; ?>
};

function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

// Notification logic
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

function formatPHP(amount) {
    return '₱' + amount.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function showToast(message, isError = false) {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.style.backgroundColor = isError ? '#fee2e2' : '#e8f5e9';
    toast.style.borderLeftColor = isError ? '#dc3545' : '#2b5e2f';
    toast.innerHTML = `
        <i class="bi ${isError ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'} me-2" style="color: ${isError ? '#dc3545' : '#2b5e2f'}"></i>
        ${message}
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

async function fetchCartData() {
    try {
        const formData = new URLSearchParams();
        formData.append('action', 'get_cart');
        
        const response = await fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        });
        
        const data = await response.json();
        if (data.success) {
            renderCart(data);
        } else {
            showToast('Failed to load cart', true);
        }
    } catch (error) {
        console.error('Error fetching cart:', error);
        showToast('Error loading cart', true);
    }
}

async function updateQuantity(cartItemId, newQuantity) {
    try {
        const formData = new URLSearchParams();
        formData.append('action', 'update_quantity');
        formData.append('cart_item_id', cartItemId);
        formData.append('quantity', newQuantity);
        
        const response = await fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        });
        
        const data = await response.json();
        if (data.success) {
            await fetchCartData();
            showToast(data.message);
        } else {
            if (data.max_stock) {
                showToast(`Only ${data.max_stock} items available in stock`, true);
            } else {
                showToast(data.message || 'Failed to update', true);
            }
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
        showToast('Error updating cart', true);
    }
}

async function removeItem(cartItemId) {
    try {
        const formData = new URLSearchParams();
        formData.append('action', 'remove_item');
        formData.append('cart_item_id', cartItemId);
        
        const response = await fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        });
        
        const data = await response.json();
        if (data.success) {
            await fetchCartData();
            showToast(data.message);
        } else {
            showToast(data.message || 'Failed to remove item', true);
        }
    } catch (error) {
        console.error('Error removing item:', error);
        showToast('Error removing item', true);
    }
}

async function proceedToCheckout(selectedItems) {
    if (selectedItems.length === 0) {
        showToast('Please select at least one item to checkout', true);
        return false;
    }
    
    try {
        const formData = new URLSearchParams();
        formData.append('action', 'checkout_selected');
        formData.append('selected_items', JSON.stringify(selectedItems));
        
        const response = await fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        });
        
        const data = await response.json();
        if (data.success) {
            showToast('Proceeding to checkout...');
            setTimeout(() => {
                window.location.href = 'customer_checkout.php';
            }, 1000);
            return true;
        } else {
            showToast(data.message || 'Failed to proceed', true);
            return false;
        }
    } catch (error) {
        console.error('Error during checkout:', error);
        showToast('Error proceeding to checkout', true);
        return false;
    }
}

function renderCart(data) {
    const root = document.getElementById('cartRoot');
    if (!root) return;
    
    const items = data.items || [];
    const subtotal = data.subtotal || 0;
    const total = data.total || 0;
    const itemCount = data.item_count || 0;
    const totalQty = data.total_quantity || 0;
    
    if (items.length === 0) {
        root.innerHTML = `
            <div class="empty-cart-placeholder">
                <i class="bi bi-bag-x fs-1" style="color:#b37b5a;"></i>
                <h4 class="mt-3 fw-semibold">Your cart is empty</h4>
                <p class="text-muted">Looks like you haven't added anything yet.</p>
                <a href="customer_home.php" class="btn btn-outline-danger rounded-pill px-4 mt-2">Continue Shopping</a>
            </div>
        `;
        return;
    }
    
    let itemsHtml = '';
    items.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        const isLowStock = item.stock_qty <= 5 && item.stock_qty > 0;
        const outOfStock = item.stock_qty <= 0;
        const imagePath = item.image_path && item.image_path !== '' ? item.image_path : null;
        
        itemsHtml += `
            <div class="cart-item-card" data-cart-item-id="${item.cart_item_id}" data-item-index="${index}">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        <input type="checkbox" class="item-checkbox" data-id="${item.cart_item_id}" data-price="${item.price}" data-quantity="${item.quantity}" ${outOfStock ? 'disabled' : 'checked'}>
                    </div>
                    <div class="col-md-2 col-4">
                        ${imagePath ? 
                            `<img src="${escapeHtml(imagePath)}" class="product-image" alt="${escapeHtml(item.product_name)}" onerror="this.src='https://placehold.co/80x80?text=No+Image'">` : 
                            `<div class="product-icon">
                                <i class="bi bi-bag"></i>
                            </div>`
                        }
                    </div>
                    <div class="col-md-4 col-8">
                        <div class="product-title">${escapeHtml(item.product_name)}</div>
                        <div class="product-variant">
                            <i class="bi bi-palette-fill me-1" style="font-size:0.7rem;"></i> ${escapeHtml(item.size)} / ${escapeHtml(item.color)}
                        </div>
                        <div class="price-main mt-1">${formatPHP(item.price)}</div>
                        ${isLowStock ? `<small class="text-warning"><i class="bi bi-exclamation-triangle"></i> Only ${item.stock_qty} left!</small>` : ''}
                        ${outOfStock ? `<small class="text-danger"><i class="bi bi-x-circle"></i> Out of stock</small>` : ''}
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="quantity-selector">
                                <button class="qty-btn dec-qty" data-id="${item.cart_item_id}" data-current="${item.quantity}" ${outOfStock ? 'disabled' : ''}>−</button>
                                <span class="fw-semibold mx-1" style="min-width: 28px; text-align:center;">${item.quantity}</span>
                                <button class="qty-btn inc-qty" data-id="${item.cart_item_id}" data-current="${item.quantity}" data-max="${item.stock_qty}" ${outOfStock ? 'disabled' : ''}>+</button>
                            </div>
                            <button class="remove-btn" data-id="${item.cart_item_id}"><i class="bi bi-trash3 me-1"></i> Remove</button>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 text-end">
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
                <p class="text-muted mt-1">${itemCount} item${itemCount !== 1 ? 's' : ''} in your cart • ${totalQty} total piece${totalQty !== 1 ? 's' : ''}</p>
            </div>
            <div>
                <a href="customer_home.php" class="continue-shopping-link"><i class="bi bi-arrow-left-short"></i> Continue Shopping</a>
            </div>
        </div>
        
        <div class="selection-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <input type="checkbox" id="selectAllCheckbox" class="select-all-checkbox">
                    <label for="selectAllCheckbox" class="mb-0 fw-semibold">Select All Items</label>
                </div>
                <div>
                    <button id="deleteSelectedBtn" class="btn btn-link text-danger text-decoration-none">
                        <i class="bi bi-trash3"></i> Delete Selected
                    </button>
                </div>
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
                        <span>Selected Items</span>
                        <span id="selectedCount">0</span>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="selectedSubtotal">₱0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span class="text-success">Free</span>
                    </div>
                    <div class="total-row d-flex justify-content-between">
                        <span>Total</span>
                        <span class="fw-bold" id="selectedTotal">₱0.00</span>
                    </div>
                    <button class="btn btn-checkout-custom mt-4" id="proceedCheckoutBtn">Checkout Selected Items →</button>
                </div>
            </div>
        </div>
    `;
    
    root.innerHTML = fullHtml;
    
    // Store items data globally for selection calculations
    window.cartItems = items;
    
    // Initialize checkboxes
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    function updateSelectedSummary() {
        let selectedCount = 0;
        let selectedSubtotal = 0;
        const selectedItems = [];
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked && !checkbox.disabled) {
                const cartItemId = parseInt(checkbox.getAttribute('data-id'));
                const item = window.cartItems.find(i => i.cart_item_id === cartItemId);
                if (item) {
                    selectedCount++;
                    selectedSubtotal += item.price * item.quantity;
                    selectedItems.push({
                        cart_item_id: item.cart_item_id,
                        variant_id: item.variant_id,
                        quantity: item.quantity,
                        price: item.price
                    });
                }
            }
        });
        
        document.getElementById('selectedCount').innerText = selectedCount;
        document.getElementById('selectedSubtotal').innerHTML = formatPHP(selectedSubtotal);
        document.getElementById('selectedTotal').innerHTML = formatPHP(selectedSubtotal);
        
        // Store selected items for checkout
        window.selectedCheckoutItems = selectedItems;
        
        // Enable/disable checkout button
        const checkoutBtn = document.getElementById('proceedCheckoutBtn');
        if (checkoutBtn) {
            checkoutBtn.disabled = selectedCount === 0;
        }
    }
    
    // Add event listeners to checkboxes
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedSummary();
            
            // Update select all checkbox state
            if (selectAllCheckbox) {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked || cb.disabled);
                const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked && !cb.disabled);
                selectAllCheckbox.checked = allChecked && !anyUnchecked;
                selectAllCheckbox.indeterminate = !allChecked && anyUnchecked;
            }
            
            // Highlight selected items
            const cartItemCard = this.closest('.cart-item-card');
            if (cartItemCard) {
                if (this.checked) {
                    cartItemCard.classList.add('selected');
                } else {
                    cartItemCard.classList.remove('selected');
                }
            }
        });
        
        // Trigger initial highlight
        if (checkbox.checked) {
            const cartItemCard = checkbox.closest('.cart-item-card');
            if (cartItemCard) cartItemCard.classList.add('selected');
        }
    });
    
    // Select All functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            checkboxes.forEach(checkbox => {
                if (!checkbox.disabled) {
                    checkbox.checked = isChecked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });
    }
    
    // Delete Selected button
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', async function() {
            const selectedCheckboxes = Array.from(checkboxes).filter(cb => cb.checked && !cb.disabled);
            if (selectedCheckboxes.length === 0) {
                showToast('No items selected', true);
                return;
            }
            
            if (confirm(`Remove ${selectedCheckboxes.length} selected item(s) from cart?`)) {
                for (const checkbox of selectedCheckboxes) {
                    const cartItemId = parseInt(checkbox.getAttribute('data-id'));
                    await removeItem(cartItemId);
                }
            }
        });
    }
    
    // Quantity buttons
    document.querySelectorAll('.inc-qty').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const cartItemId = parseInt(btn.getAttribute('data-id'));
            const currentQty = parseInt(btn.getAttribute('data-current'));
            const maxStock = parseInt(btn.getAttribute('data-max'));
            if (currentQty < maxStock) {
                await updateQuantity(cartItemId, currentQty + 1);
            } else {
                showToast(`Only ${maxStock} items available in stock`, true);
            }
        });
    });
    
    document.querySelectorAll('.dec-qty').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const cartItemId = parseInt(btn.getAttribute('data-id'));
            const currentQty = parseInt(btn.getAttribute('data-current'));
            if (currentQty > 1) {
                await updateQuantity(cartItemId, currentQty - 1);
            } else if (currentQty === 1) {
                if (confirm('Remove this item from cart?')) {
                    await removeItem(cartItemId);
                }
            }
        });
    });
    
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const cartItemId = parseInt(btn.getAttribute('data-id'));
            if (confirm('Remove this item from your cart?')) {
                await removeItem(cartItemId);
            }
        });
    });
    
    // Checkout button
    const checkoutBtn = document.getElementById('proceedCheckoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', async () => {
            if (window.selectedCheckoutItems && window.selectedCheckoutItems.length > 0) {
                await proceedToCheckout(window.selectedCheckoutItems);
            } else {
                showToast('Please select items to checkout', true);
            }
        });
    }
    
    // Initial summary update
    updateSelectedSummary();
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Load cart on page load
fetchCartData();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>