<?php
require_once 'auth.php';
require_roles([2, 4]);
require_once 'admin/db.connect.php';

$customerId = (int) $_SESSION['user_id'];
$customerName = current_user_name();
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? 'All');
$flashMessage = '';

// Fetch session timeout from database
$query = "SELECT session_timeout_minutes FROM system_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$timeout_minutes = $row ? $row['session_timeout_minutes'] : 30;

// Check session timeout
if (!isset($_SESSION['last_activity'])) {
  $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout_minutes * 60) {
  // Session expired, logout
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
} else {
  // Update last activity
  $_SESSION['last_activity'] = time();
}

$timeout_ms = $timeout_minutes * 60 * 1000;

// Handle AJAX request for product details
if (isset($_GET['ajax_product_id'])) {
    $ajaxProductId = (int) $_GET['ajax_product_id'];
    header('Content-Type: application/json');
    
    // Fetch product details
    $productStmt = $conn->prepare("
        SELECT 
            p.product_id,
            p.name,
            p.description,
            p.category_gender,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(r.review_id) AS review_count
        FROM product p
        LEFT JOIN review r ON r.product_id = p.product_id
        WHERE p.product_id = ? AND p.status = 'active'
        GROUP BY p.product_id, p.name, p.description, p.category_gender
    ");
    $productStmt->bind_param("i", $ajaxProductId);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
    $product = $productResult->fetch_assoc();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Fetch all variants for this product with image
    $variantStmt = $conn->prepare("
        SELECT 
            variant_id,
            product_id,
            size,
            color,
            price,
            stock_qty,
            image_path
        FROM product_variant
        WHERE product_id = ?
        ORDER BY price ASC
    ");
    $variantStmt->bind_param("i", $ajaxProductId);
    $variantStmt->execute();
    $variants = $variantStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'product' => [
            'name' => $product['name'],
            'description' => $product['description'] ?? 'No description available.',
            'category_gender' => $product['category_gender'],
            'avg_rating' => (float) $product['avg_rating'],
            'review_count' => (int) $product['review_count']
        ],
        'variants' => $variants
    ]);
    exit;
}

// Handle Add to Cart from modal or card
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart_variant_id'])) {
    $variantId = (int) $_POST['add_to_cart_variant_id'];
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
    
    if ($variantId > 0 && $quantity > 0) {
        $conn->begin_transaction();
        try {
            // Get or create cart
            $cartStmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? LIMIT 1");
            $cartStmt->bind_param("i", $customerId);
            $cartStmt->execute();
            $cartRes = $cartStmt->get_result();
            if ($cartRes->num_rows > 0) {
                $cartRow = $cartRes->fetch_assoc();
                $cartId = (int) $cartRow['cart_id'];
            } else {
                $createCartStmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
                $createCartStmt->bind_param("i", $customerId);
                $createCartStmt->execute();
                $cartId = (int) $conn->insert_id;
            }
            
            // Check if item already in cart
            $itemStmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_item WHERE cart_id = ? AND variant_id = ? LIMIT 1");
            $itemStmt->bind_param("ii", $cartId, $variantId);
            $itemStmt->execute();
            $itemRes = $itemStmt->get_result();
            
            if ($itemRes->num_rows > 0) {
                $itemRow = $itemRes->fetch_assoc();
                $newQty = (int) $itemRow['quantity'] + $quantity;
                $updateStmt = $conn->prepare("UPDATE cart_item SET quantity = ?, updated_at = NOW() WHERE cart_item_id = ?");
                $updateStmt->bind_param("ii", $newQty, $itemRow['cart_item_id']);
                $updateStmt->execute();
            } else {
                $insertStmt = $conn->prepare("INSERT INTO cart_item (cart_id, variant_id, quantity) VALUES (?, ?, ?)");
                $insertStmt->bind_param("iii", $cartId, $variantId, $quantity);
                $insertStmt->execute();
            }
            
            $conn->commit();
            
            // If this is an AJAX request, return JSON response
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => true, 'message' => 'Item added to cart']);
                exit;
            }
            
            $flashMessage = "Item added to cart.";
        } catch (Throwable $t) {
            $conn->rollback();
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'message' => 'Unable to add item to cart']);
                exit;
            }
            $flashMessage = "Unable to add item to cart.";
        }
    }
}

$sql = "SELECT 
          p.product_id,
          p.name,
          p.category_gender,
          MIN(pv.price) AS min_price,
          MAX(pv.price) AS max_price,
          MIN(pv.variant_id) AS default_variant_id,
          MIN(pv.image_path) AS sample_image,
          COALESCE(AVG(r.rating), 0) AS avg_rating,
          COUNT(DISTINCT r.review_id) AS review_count
        FROM product p
        INNER JOIN product_variant pv ON pv.product_id = p.product_id
        LEFT JOIN review r ON r.product_id = p.product_id
        WHERE p.status = 'active'";

$types = "";
$params = [];

if ($category === 'Men' || $category === 'Women') {
    $sql .= " AND p.category_gender = ?";
    $types .= "s";
    $params[] = $category;
}

if ($search !== '') {
    $sql .= " AND p.name LIKE ?";
    $types .= "s";
    $params[] = "%" . $search . "%";
}

$sql .= " GROUP BY p.product_id, p.name, p.category_gender ORDER BY p.created_at DESC LIMIT 24";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Home - J3RS</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<script>
    const timeoutMs = <?php echo $timeout_ms; ?>;
    let logoutTimer;

    function resetTimer() {
      clearTimeout(logoutTimer);
      logoutTimer = setTimeout(function() {
        alert("Session expired due to inactivity. You will be logged out.");
        window.location.href = "logout.php";
      }, timeoutMs);
    }

    document.addEventListener("mousemove", resetTimer);
    document.addEventListener("keypress", resetTimer);
    document.addEventListener("click", resetTimer);
    document.addEventListener("scroll", resetTimer);

    resetTimer();
  </script>

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
    .product-image {
        background: #f9f5f2;
        padding: 24px 0;
        text-align: center;
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .product-image img {
        max-width: 100%;
        max-height: 140px;
        object-fit: contain;
    }
    .product-image i {
        font-size: 3.2rem;
        color: #6e0f25;
    }
    .product-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: #1f1a17;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
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
        font-size: 0.75rem;
    }
    .review-count {
        font-size: 0.7rem;
        color: #7c6e65;
    }
    .product-price {
        font-size: 1.3rem;
        font-weight: 800;
        color: #2c2c2c;
        margin: 12px 0 16px 0;
    }
    .original-price {
        font-size: 0.85rem;
        color: #999;
        text-decoration: line-through;
        margin-left: 8px;
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
    .btn-view-details {
        background: transparent;
        color: #6e0f25;
        border: 1px solid #6e0f25;
        border-radius: 40px;
        padding: 8px 0;
        font-weight: 600;
        width: 100%;
        margin-bottom: 8px;
        transition: all 0.2s;
    }
    .btn-view-details:hover {
        background: #6e0f25;
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
        text-decoration: none;
    }
    .filter-chip.active {
        background: #6e0f25;
        color: white;
    }
    .filter-chip:hover {
        background: #e3dbd4;
    }

    .product-modal-image {
        background: #f9f5f2;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-modal-image img {
        max-width: 100%;
        max-height: 260px;
        object-fit: contain;
    }
    .product-modal-image i {
        font-size: 5rem;
        color: #6e0f25;
    }
    .modal-product-title {
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    .modal-price {
        font-size: 1.8rem;
        font-weight: 800;
        color: #6e0f25;
    }
    .variant-selector {
        background: #f8f5f2;
        border-radius: 16px;
        padding: 16px;
    }
    .variant-group {
        margin-bottom: 20px;
    }
    .variant-group-title {
        font-weight: 600;
        margin-bottom: 12px;
        font-size: 0.9rem;
        color: #2d2a27;
        border-bottom: 1px solid #e0d6cf;
        padding-bottom: 6px;
    }
    .variant-radio-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 8px;
    }
    .variant-radio {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        padding: 8px 16px;
        border: 1px solid #e0d6cf;
        border-radius: 40px;
        transition: all 0.2s;
        background: white;
    }
    .variant-radio input {
        margin-right: 8px;
        cursor: pointer;
        accent-color: #6e0f25;
    }
    .variant-radio:hover {
        background: #f5f1ee;
        border-color: #6e0f25;
    }
    .variant-radio.selected {
        background: #6e0f25;
        border-color: #6e0f25;
        color: white;
    }
    .variant-radio.selected input {
        accent-color: white;
    }
    .variant-radio.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f0f0f0;
    }
    .variant-radio.disabled input {
        cursor: not-allowed;
    }
    .color-swatch {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
        vertical-align: middle;
        border: 1px solid #ddd;
    }
    .stock-badge-small {
        font-size: 0.7rem;
        margin-left: 8px;
        color: #666;
    }
    .quantity-input {
        width: 100px;
        text-align: center;
        border-radius: 40px;
        border: 1px solid #ddd;
        padding: 8px;
    }
    .btn-modal-add {
        background: #6e0f25;
        color: white;
        border-radius: 50px;
        padding: 12px 24px;
        font-weight: 700;
        width: 100%;
        border: none;
    }
    .btn-modal-add:hover:not(:disabled) {
        background: #8c1c36;
        color: white;
    }
    .btn-modal-add:disabled {
        opacity: 0.6;
        cursor: not-allowed;
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
    
    .alert-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
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
    <a href="customer_home.php" class="active"><i class="bi bi-house"></i><span class="text">Home</span></a>
    <a href="customer_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
    <a href="customer_cart.php"><i class="bi bi-cart-check"></i><span class="text">Cart</span></a>

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
                        <small class="text-muted">Your product is on the way</small>
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

<!-- MAIN CONTENT-->
<div class="main-content">
    <div class="container-fluid px-0">
        
        <div class="brand-nav d-flex flex-wrap align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div class="logo-text-duo">
                    <img src="JERS-LOGO.PNG" alt="JERS Logo" class="img-fluid" style="width: 100px; height: auto;">
                </div>
                <div class="nav-links">
                    <a href="#">Home</a>
                    <a href="#">Shop</a>
                    <a href="#">Categories</a>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <form method="GET" class="search-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products...">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                </form>
                <div class="user-area">
                    <div class="user-avatar"><?php echo htmlspecialchars(strtoupper(substr($customerName, 0, 1))); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($customerName); ?></div>
                        <div class="user-role">Customer</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="filter-section">
            <span class="filter-label">Filters</span>
            <div class="filter-chips">
                <a class="filter-chip <?php echo $category === 'All' ? 'active' : ''; ?>" href="?category=All&search=<?php echo urlencode($search); ?>">All</a>
                <a class="filter-chip <?php echo $category === 'Men' ? 'active' : ''; ?>" href="?category=Men&search=<?php echo urlencode($search); ?>">Men</a>
                <a class="filter-chip <?php echo $category === 'Women' ? 'active' : ''; ?>" href="?category=Women&search=<?php echo urlencode($search); ?>">Women</a>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
            <h2 class="section-title">Recommended for You</h2>
        </div>

        <?php if ($flashMessage !== ''): ?>
            <div class="alert alert-info mb-3 alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($flashMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Product Grid -->
        <div class="row g-4">
            <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="card p-4 text-center">No products available yet.</div>
                </div>
            <?php endif; ?>

            <?php foreach ($products as $product): ?>
            <div class="col-sm-6 col-lg-3">
                <div class="product-card card h-100">
                    <div class="product-image">
                        <?php if (!empty($product['sample_image']) && file_exists($product['sample_image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['sample_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <i class="bi bi-bag"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <div class="product-price">
                            ₱<?php echo number_format((float) $product['min_price'], 2); ?>
                            <?php if ($product['max_price'] > $product['min_price']): ?>
                                <span class="original-price">₱<?php echo number_format((float) $product['max_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn-view-details view-details-btn" 
                                data-product-id="<?php echo (int) $product['product_id']; ?>"
                                data-bs-toggle="modal" 
                                data-bs-target="#productDetailModal">
                            <i class="bi bi-eye"></i> View Details
                        </button>
                        <form method="POST" class="add-to-cart-simple">
                            <input type="hidden" name="add_to_cart_variant_id" value="<?php echo (int) $product['default_variant_id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn-add-cart">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 24px; overflow: hidden;">
            <div class="modal-header border-0" style="background: #fefaf5; padding: 1.5rem 1.5rem 0 1.5rem;">
                <h5 class="modal-title" id="productDetailModalLabel" style="font-weight: 700;">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="product-modal-image" id="modalImageContainer">
                            <i class="bi bi-bag-heart" id="modalProductIcon"></i>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h3 class="modal-product-title" id="modalProductName">Product Name</h3>
                        <div class="modal-price mb-3" id="modalPrice">₱0.00</div>
                        <div class="mb-3">
                            <p class="text-muted" id="modalDescription">Product description will appear here.</p>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-secondary" id="modalCategory">Category</span>
                        </div>
                        
                        <!-- Variant Selection Section with Radio Buttons -->
                        <div class="variant-selector mb-3">
                            <div id="sizeGroupContainer" class="variant-group">
                                <div class="variant-group-title">Select Size:</div>
                                <div id="sizeRadioGroup" class="variant-radio-group">
                                    <div class="text-muted">Loading sizes...</div>
                                </div>
                            </div>
                            
                            <div id="colorGroupContainer" class="variant-group">
                                <div class="variant-group-title">Select Color:</div>
                                <div id="colorRadioGroup" class="variant-radio-group">
                                    <div class="text-muted">Please select a size first</div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label class="fw-semibold mb-1">Quantity:</label>
                                <input type="number" id="modalQuantity" class="quantity-input" value="1" min="1" max="99">
                                <span id="modalStockDisplay" class="ms-2 text-muted small"></span>
                            </div>
                        </div>
                        
                        <form method="POST" id="modalAddToCartForm">
                            <input type="hidden" name="add_to_cart_variant_id" id="modalVariantId" value="">
                            <input type="hidden" name="quantity" id="modalQuantityHidden" value="1">
                            <button type="submit" class="btn-modal-add" id="modalAddToCartBtn" disabled>Select Size & Color</button>
                        </form>
                        <div class="mt-2 text-center">
                            <small class="text-muted" id="modalVariantHint">Select a size and color to add to cart</small>
                        </div>
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

// Show toast notification
function showToast(message, isSuccess = true) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${isSuccess ? 'success' : 'danger'} alert-dismissible fade show alert-toast`;
    toast.innerHTML = `
        ${isSuccess ? '✓' : '✗'} ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// PRODUCT DETAILS MODAL - Fetch from same file using AJAX
document.addEventListener('DOMContentLoaded', function() {
    const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
    const modal = document.getElementById('productDetailModal');
    let currentVariants = [];
    let selectedSize = null;
    let selectedColor = null;
    let selectedVariantId = null;
    
    // Helper function to get color display
    function getColorDisplay(color) {
        const colorLower = color.toLowerCase();
        const colorMap = {
            'red': '#ff0000',
            'blue': '#0000ff',
            'black': '#000000',
            'white': '#ffffff',
            'green': '#00ff00',
            'yellow': '#ffff00',
            'purple': '#800080',
            'orange': '#ffa500',
            'pink': '#ffc0cb',
            'brown': '#8b4513',
            'gray': '#808080',
            'grey': '#808080'
        };
        
        for (let [key, value] of Object.entries(colorMap)) {
            if (colorLower.includes(key)) {
                return `<span class="color-swatch" style="background-color: ${value}; border: 1px solid #ddd;"></span>`;
            }
        }
        return `<span class="color-swatch" style="background-color: ${colorLower}; border: 1px solid #ddd;"></span>`;
    }
    
    // Get unique sizes from variants
    function getUniqueSizes(variants) {
        const sizes = [...new Set(variants.map(v => v.size))];
        return sizes.sort();
    }
    
    // Get colors available for a specific size
    function getColorsForSize(variants, size) {
        return variants.filter(v => v.size === size);
    }
    
    // Find variant by size and color
    function findVariantBySizeColor(variants, size, color) {
        return variants.find(v => v.size === size && v.color === color);
    }
    
    // Update modal UI based on selected size and color
    function updateSelection() {
        if (selectedSize && selectedColor) {
            const variant = findVariantBySizeColor(currentVariants, selectedSize, selectedColor);
            if (variant) {
                selectedVariantId = variant.variant_id;
                document.getElementById('modalVariantId').value = variant.variant_id;
                document.getElementById('modalPrice').innerHTML = '₱' + parseFloat(variant.price).toFixed(2);
                
                const stockStatus = variant.stock_qty > 0 ? 
                    `<span class="badge bg-success">In Stock (${variant.stock_qty} available)</span>` : 
                    `<span class="badge bg-danger">Out of Stock</span>`;
                document.getElementById('modalStockDisplay').innerHTML = stockStatus;
                
                // Update quantity max based on stock
                const qtyInput = document.getElementById('modalQuantity');
                qtyInput.max = variant.stock_qty > 0 ? variant.stock_qty : 0;
                if (qtyInput.value > variant.stock_qty && variant.stock_qty > 0) qtyInput.value = variant.stock_qty;
                if (variant.stock_qty <= 0) {
                    qtyInput.disabled = true;
                    qtyInput.value = 0;
                } else {
                    qtyInput.disabled = false;
                }
                
                const addBtn = document.getElementById('modalAddToCartBtn');
                if (variant.stock_qty <= 0) {
                    addBtn.disabled = true;
                    addBtn.innerHTML = 'Out of Stock';
                    document.getElementById('modalVariantHint').innerHTML = '<span class="text-danger">This variant is out of stock</span>';
                } else {
                    addBtn.disabled = false;
                    addBtn.innerHTML = 'Add to Cart';
                    document.getElementById('modalVariantHint').innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Ready to add';
                }
                
                // Update image if variant has specific image
                const modalImageContainer = document.getElementById('modalImageContainer');
                if (variant.image_path && variant.image_path !== 'null' && variant.image_path !== '') {
                    modalImageContainer.innerHTML = `<img src="${variant.image_path}" alt="Product image">`;
                } else {
                    const firstVariantWithImage = currentVariants.find(v => v.image_path && v.image_path !== 'null' && v.image_path !== '');
                    if (firstVariantWithImage) {
                        modalImageContainer.innerHTML = `<img src="${firstVariantWithImage.image_path}" alt="Product image">`;
                    } else {
                        modalImageContainer.innerHTML = `<i class="bi bi-bag-heart" id="modalProductIcon"></i>`;
                    }
                }
            }
        } else {
            document.getElementById('modalAddToCartBtn').disabled = true;
            document.getElementById('modalAddToCartBtn').innerHTML = 'Select Size & Color';
            document.getElementById('modalVariantHint').innerHTML = 'Select a size and color to add to cart';
            document.getElementById('modalVariantId').value = '';
        }
    }
    
    // Render size radio buttons
    function renderSizes(sizes, variants) {
        const container = document.getElementById('sizeRadioGroup');
        if (!sizes || sizes.length === 0) {
            container.innerHTML = '<div class="text-muted">No sizes available</div>';
            return;
        }
        
        let html = '';
        sizes.forEach(size => {
            const hasStock = variants.some(v => v.size === size && v.stock_qty > 0);
            const disabledClass = !hasStock ? 'disabled' : '';
            const stockText = !hasStock ? ' (Out of Stock)' : '';
            
            html += `
                <label class="variant-radio ${disabledClass}" data-size="${escapeHtml(size)}">
                    <input type="radio" name="size" value="${escapeHtml(size)}" ${!hasStock ? 'disabled' : ''}>
                    <strong>${escapeHtml(size)}</strong>${stockText}
                </label>
            `;
        });
        container.innerHTML = html;
        
        // Add event listeners to size radio buttons
        document.querySelectorAll('input[name="size"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    selectedSize = this.value;
                    // Update color options based on selected size
                    updateColorsForSize(selectedSize);
                    
                    // Update styling
                    document.querySelectorAll('.variant-radio').forEach(label => {
                        if (label.querySelector('input[name="size"]') === this) {
                            label.classList.add('selected');
                        } else if (label.querySelector('input[name="size"]')) {
                            label.classList.remove('selected');
                        }
                    });
                }
            });
        });
    }
    
    // Update color options based on selected size
    function updateColorsForSize(size) {
        const colorsForSize = getColorsForSize(currentVariants, size);
        const colorContainer = document.getElementById('colorRadioGroup');
        
        if (!colorsForSize || colorsForSize.length === 0) {
            colorContainer.innerHTML = '<div class="text-muted">No colors available for this size</div>';
            selectedColor = null;
            selectedVariantId = null;
            updateSelection();
            return;
        }
        
        let html = '';
        colorsForSize.forEach(variant => {
            const hasStock = variant.stock_qty > 0;
            const disabledClass = !hasStock ? 'disabled' : '';
            const colorHtml = getColorDisplay(variant.color);
            const stockText = !hasStock ? ` (Out of Stock - ${variant.stock_qty})` : ` (${variant.stock_qty} in stock)`;
            
            html += `
                <label class="variant-radio ${disabledClass}" data-color="${escapeHtml(variant.color)}" data-variant-id="${variant.variant_id}">
                    <input type="radio" name="color" value="${escapeHtml(variant.color)}" data-variant-id="${variant.variant_id}" ${!hasStock ? 'disabled' : ''}>
                    ${colorHtml}
                    <span>${escapeHtml(variant.color)}</span>
                    <span class="stock-badge-small">${stockText}</span>
                </label>
            `;
        });
        colorContainer.innerHTML = html;
        
        // Reset color selection
        selectedColor = null;
        selectedVariantId = null;
        
        // Add event listeners to color radio buttons
        document.querySelectorAll('input[name="color"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    selectedColor = this.value;
                    const variantId = parseInt(this.dataset.variantId);
                    const variant = currentVariants.find(v => v.variant_id === variantId);
                    if (variant) {
                        selectedVariantId = variantId;
                    }
                    updateSelection();
                    
                    // Update styling
                    document.querySelectorAll('.variant-radio').forEach(label => {
                        if (label.querySelector('input[name="color"]') === this) {
                            label.classList.add('selected');
                        } else if (label.querySelector('input[name="color"]')) {
                            label.classList.remove('selected');
                        }
                    });
                }
            });
        });
        
        updateSelection();
    }
    
    // Escape HTML helper
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    
    // Fetch product details via AJAX
    async function loadProductDetails(productId) {
        try {
            const response = await fetch(`?ajax_product_id=${productId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            if (data.success) {
                // Update basic product info
                document.getElementById('modalProductName').innerText = data.product.name;
                document.getElementById('modalDescription').innerHTML = escapeHtml(data.product.description || 'No description available.');
                document.getElementById('modalCategory').innerHTML = data.product.category_gender;
                
                // Store variants
                currentVariants = data.variants;
                
                if (currentVariants.length > 0) {
                    // Reset selections
                    selectedSize = null;
                    selectedColor = null;
                    selectedVariantId = null;
                    
                    // Get unique sizes and render
                    const uniqueSizes = getUniqueSizes(currentVariants);
                    renderSizes(uniqueSizes, currentVariants);
                    
                    // Reset color group
                    document.getElementById('colorRadioGroup').innerHTML = '<div class="text-muted">Please select a size first</div>';
                    document.getElementById('modalPrice').innerHTML = '₱0.00';
                    document.getElementById('modalStockDisplay').innerHTML = '';
                    document.getElementById('modalAddToCartBtn').disabled = true;
                    document.getElementById('modalAddToCartBtn').innerHTML = 'Select Size & Color';
                    
                    // Set default price range display
                    const minPrice = Math.min(...currentVariants.map(v => v.price));
                    const maxPrice = Math.max(...currentVariants.map(v => v.price));
                    if (minPrice !== maxPrice) {
                        document.getElementById('modalPrice').innerHTML = `₱${minPrice.toFixed(2)} - ₱${maxPrice.toFixed(2)}`;
                    } else {
                        document.getElementById('modalPrice').innerHTML = `₱${minPrice.toFixed(2)}`;
                    }
                } else {
                    document.getElementById('sizeRadioGroup').innerHTML = '<div class="text-muted">No variants available</div>';
                    document.getElementById('colorRadioGroup').innerHTML = '';
                    document.getElementById('modalAddToCartBtn').disabled = true;
                    document.getElementById('modalAddToCartBtn').innerHTML = 'No Variants Available';
                }
            } else {
                console.error('Failed to load product:', data.message);
                document.getElementById('sizeRadioGroup').innerHTML = '<div class="text-danger">Error loading product details</div>';
            }
        } catch (error) {
            console.error('AJAX error:', error);
            document.getElementById('sizeRadioGroup').innerHTML = '<div class="text-danger">Failed to load product details</div>';
        }
    }
    
    // Attach click handlers to view details buttons
    viewDetailsBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const productId = this.dataset.productId;
            if (productId) {
                // Reset modal state
                document.getElementById('modalProductName').innerText = 'Loading...';
                document.getElementById('modalImageContainer').innerHTML = '<i class="bi bi-bag-heart" id="modalProductIcon"></i>';
                document.getElementById('sizeRadioGroup').innerHTML = '<div class="text-muted">Loading sizes...</div>';
                document.getElementById('colorRadioGroup').innerHTML = '<div class="text-muted">Loading colors...</div>';
                document.getElementById('modalVariantId').value = '';
                selectedVariantId = null;
                loadProductDetails(productId);
            }
        });
    });
    
    // Handle quantity validation
    const qtyInput = document.getElementById('modalQuantity');
    const qtyHidden = document.getElementById('modalQuantityHidden');
    
    qtyInput.addEventListener('change', function() {
        let val = parseInt(this.value);
        const currentVariant = currentVariants.find(v => v.variant_id == selectedVariantId);
        const maxStock = currentVariant?.stock_qty || 0;
        if (isNaN(val) || val < 1) val = 1;
        if (maxStock > 0 && val > maxStock) val = maxStock;
        this.value = val;
        qtyHidden.value = val;
    });
    
    // Handle modal form submission with AJAX
    const modalForm = document.getElementById('modalAddToCartForm');
    
    modalForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const variantId = document.getElementById('modalVariantId').value;
        const quantity = parseInt(document.getElementById('modalQuantity').value);
        
        if (!variantId || variantId == '') {
            showToast('Please select a size and color first!', false);
            return;
        }
        
        const variant = currentVariants.find(v => v.variant_id == variantId);
        if (variant && variant.stock_qty < quantity) {
            showToast(`Only ${variant.stock_qty} items available in stock.`, false);
            return;
        }
        
        // Submit via AJAX to add to cart
        const formData = new URLSearchParams();
        formData.append('add_to_cart_variant_id', variantId);
        formData.append('quantity', quantity);
        
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData.toString()
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message, true);
                
                // Close modal
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            } else {
                showToast(result.message || 'Failed to add to cart. Please try again.', false);
            }
        } catch (err) {
            console.error('Add to cart error:', err);
            showToast('An error occurred. Please try again.', false);
        }
    });
    
    // Handle simple add to cart forms
    document.querySelectorAll('.add-to-cart-simple').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const params = new URLSearchParams();
            params.append('add_to_cart_variant_id', formData.get('add_to_cart_variant_id'));
            params.append('quantity', formData.get('quantity') || '1');
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: params.toString()
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, true);
                } else {
                    showToast(result.message || 'Failed to add to cart', false);
                }
            } catch (err) {
                console.error('Add to cart error:', err);
                showToast('An error occurred', false);
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>