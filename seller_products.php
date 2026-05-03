<?php
require_once 'auth.php';
require_roles([3, 4]);
require_once 'admin/db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;

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


// Fetch seller information
$seller_stmt = $conn->prepare("SELECT seller_id FROM seller WHERE user_id = ?");
$seller_stmt->bind_param("i", $user_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_id = $seller['seller_id'] ?? 0;
$seller_stmt->close();

if (!$seller_id) {
    die("Seller profile not found. Please register as a seller.");
}

$message = '';
$error = '';

// Handle Product Addition with Variants
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? 'Men';
    
    // Variant data - each variant should be a separate combination
    $variants_data = $_POST['variants'] ?? [];
    
    if (empty($name) || empty($description) || empty($variants_data)) {
        $error = "Please fill in all fields and add at least one variant.";
    } else {
        $conn->begin_transaction();
        try {
            // Insert into product table ONLY - NOT variant
            $stmt = $conn->prepare("INSERT INTO product (seller_id, name, description, category_gender, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->bind_param("isss", $seller_id, $name, $description, $category);  
            $stmt->execute();
            $product_id = $conn->insert_id;
            $stmt->close();
            
            // Process each variant combination - these go to product_variant table, NOT product table
            $upload_dir = 'uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $variant_stmt = $conn->prepare("INSERT INTO product_variant (product_id, sku, size, color, price, stock_qty, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($variants_data as $index => $variant) {
                $size = trim($variant['size'] ?? '');
                $color = trim($variant['color'] ?? '');
                $price = (float)($variant['price'] ?? 0);
                $stock = (int)($variant['stock'] ?? 0);
                
                if (empty($size) || empty($color) || $price <= 0) {
                    continue;
                }
                
                // Generate SKU
                $sku = 'SKU-' . $product_id . '-' . strtoupper(substr(uniqid(), -6)) . '-' . $index;
                
                // Handle image upload
                $image_path = null;
                if (isset($_FILES['variant_image']['tmp_name'][$index]) && $_FILES['variant_image']['tmp_name'][$index] != '') {
                    $file_extension = strtolower(pathinfo($_FILES['variant_image']['name'][$index], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $new_filename = 'product_' . $product_id . '_variant_' . $index . '_' . time() . '.' . $file_extension;
                        $destination = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['variant_image']['tmp_name'][$index], $destination)) {
                            $image_path = $destination;
                        }
                    }
                }
                
                $variant_stmt->bind_param("isssdis", $product_id, $sku, $size, $color, $price, $stock, $image_path);
                $variant_stmt->execute();
            }
            $variant_stmt->close();
            
            $conn->commit();
            $message = "Product added successfully with " . count($variants_data) . " variant(s)!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to add product: " . $e->getMessage();
        }
    }
}

// Handle Single Variant Addition - Adds ONLY to product_variant table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_variant'])) {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    
    // Verify the product exists before adding variant
    $check_product = $conn->prepare("SELECT product_id FROM product WHERE product_id = ? AND seller_id = ?");
    $check_product->bind_param("ii", $product_id, $seller_id);
    $check_product->execute();
    $product_exists = $check_product->get_result()->num_rows > 0;
    $check_product->close();
    
    if (!$product_exists) {
        $error = "Invalid product selected.";
    } else {
        // Handle image upload
        $image_path = null;
        if (isset($_FILES['variant_image']) && $_FILES['variant_image']['error'] == 0) {
            $upload_dir = 'uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['variant_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = 'product_' . $product_id . '_variant_' . time() . '.' . $file_extension;
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['variant_image']['tmp_name'], $destination)) {
                    $image_path = $destination;
                }
            }
        }
        
        if ($product_id > 0 && !empty($size) && !empty($color) && $price > 0) {
            $sku = 'SKU-' . $product_id . '-' . strtoupper(substr(uniqid(), -8));
            
            $stmt = $conn->prepare("INSERT INTO product_variant (product_id, sku, size, color, price, stock_qty, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssdis", $product_id, $sku, $size, $color, $price, $stock, $image_path);
            
            if ($stmt->execute()) {
                $message = "Variant added successfully to the product!";
            } else {
                $error = "Failed to add variant: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Please fill in all variant fields correctly.";
        }
    }
}

// Handle Variant Deletion
if (isset($_GET['delete_variant'])) {
    $variant_id = (int)$_GET['delete_variant'];
    
    // Verify variant belongs to seller's product
    $verify_stmt = $conn->prepare("
        SELECT pv.image_path FROM product_variant pv 
        INNER JOIN product p ON pv.product_id = p.product_id 
        WHERE pv.variant_id = ? AND p.seller_id = ?
    ");
    $verify_stmt->bind_param("ii", $variant_id, $seller_id);
    $verify_stmt->execute();
    $variant_data = $verify_stmt->get_result()->fetch_assoc();
    $verify_stmt->close();
    
    if ($variant_data) {
        if ($variant_data['image_path'] && file_exists($variant_data['image_path'])) {
            unlink($variant_data['image_path']);
        }
        
        $stmt = $conn->prepare("DELETE FROM product_variant WHERE variant_id = ?");
        $stmt->bind_param("i", $variant_id);
        if ($stmt->execute()) {
            $message = "Variant deleted successfully!";
        } else {
            $error = "Failed to delete variant.";
        }
        $stmt->close();
    } else {
        $error = "Variant not found or access denied.";
    }
}

// Handle Variant Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_variant'])) {
    $variant_id = (int)($_POST['variant_id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    
    // Verify variant belongs to seller's product
    $verify_stmt = $conn->prepare("
        SELECT pv.image_path FROM product_variant pv 
        INNER JOIN product p ON pv.product_id = p.product_id 
        WHERE pv.variant_id = ? AND p.seller_id = ?
    ");
    $verify_stmt->bind_param("ii", $variant_id, $seller_id);
    $verify_stmt->execute();
    $old_data = $verify_stmt->get_result()->fetch_assoc();
    $verify_stmt->close();
    
    if (!$old_data) {
        $error = "Variant not found or access denied.";
    } else {
        // Handle image upload for edit
        $image_path = null;
        if (isset($_FILES['variant_image_edit']) && $_FILES['variant_image_edit']['error'] == 0) {
            $upload_dir = 'uploads/products/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old image
            if ($old_data['image_path'] && file_exists($old_data['image_path'])) {
                unlink($old_data['image_path']);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['variant_image_edit']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = 'product_variant_' . $variant_id . '_' . time() . '.' . $file_extension;
                $destination = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['variant_image_edit']['tmp_name'], $destination)) {
                    $image_path = $destination;
                }
            }
        }
        
        if ($variant_id > 0 && !empty($size) && !empty($color) && $price > 0) {
            if ($image_path) {
                $stmt = $conn->prepare("UPDATE product_variant SET size = ?, color = ?, price = ?, stock_qty = ?, image_path = ? WHERE variant_id = ?");
                $stmt->bind_param("ssdisi", $size, $color, $price, $stock, $image_path, $variant_id);
            } else {
                $stmt = $conn->prepare("UPDATE product_variant SET size = ?, color = ?, price = ?, stock_qty = ? WHERE variant_id = ?");
                $stmt->bind_param("ssdii", $size, $color, $price, $stock, $variant_id);
            }
            
            if ($stmt->execute()) {
                $message = "Variant updated successfully!";
            } else {
                $error = "Failed to update variant.";
            }
            $stmt->close();
        } else {
            $error = "Please fill in all fields correctly.";
        }
    }
}

// Pagination and Filtering
$search = trim($_GET['search'] ?? '');
$filter_category = $_GET['category'] ?? 'All Categories';
$page = (int) ($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Base query for counting - ONLY from product table
$count_sql = "SELECT COUNT(*) as total FROM product WHERE seller_id = ?";
$params = [$seller_id];
$types = "i";

if ($search !== '') {
    $count_sql .= " AND name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if ($filter_category !== 'All Categories') {
    $count_sql .= " AND category_gender = ?";
    $params[] = $filter_category;
    $types .= "s";
}

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);
$count_stmt->close();

// Query for products with variant info - ONLY from product table, variants are aggregated
$products_sql = "
    SELECT p.product_id, p.name, p.category_gender, p.status, 
           COALESCE(MIN(pv.price), 0) as min_price,
           COALESCE(MAX(pv.price), 0) as max_price,
           COALESCE(SUM(pv.stock_qty), 0) as total_stock,
           COUNT(pv.variant_id) as variant_count
    FROM product p
    LEFT JOIN product_variant pv ON p.product_id = pv.product_id
    WHERE p.seller_id = ?
";

if ($search !== '') {
    $products_sql .= " AND p.name LIKE ?";
}

if ($filter_category !== 'All Categories') {
    $products_sql .= " AND p.category_gender = ?";
}

$products_sql .= " GROUP BY p.product_id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";

$products_stmt = $conn->prepare($products_sql);
$limit_offset_params = array_merge($params, [$limit, $offset]);
$products_stmt->bind_param($types . "ii", ...$limit_offset_params);
$products_stmt->execute();
$products_result = $products_stmt->get_result();
$products = $products_result->fetch_all(MYSQLI_ASSOC);
$products_stmt->close();

// Also verify that no variant records exist in product table (safety check)
// This ensures data integrity - variants should never be in product table
$safety_check = $conn->query("SELECT COUNT(*) as variant_in_product FROM product WHERE product_id LIKE '%SKU%' OR name LIKE '%SKU%'");
$safety_result = $safety_check->fetch_assoc();
if ($safety_result['variant_in_product'] > 0) {
    error_log("Warning: Potential data issue - variants found in product table");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products - Manage Variants</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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
  body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; }

  .main-content {
    margin-left: 240px;
    transition: 0.3s;
    padding: 20px;
  }

  .sidebar.collapsed ~ .main-content {
    margin-left: 70px;
  }

  .card {
    border-radius: 16px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  }

  .badge-soft {
    background: #e6f7ef;
    color: #16a34a;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 20px;
  }

  .badge-red {
    background: #fde8e8;
    color: #dc2626;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 20px;
  }
  
  .badge-info {
    background: #e3f2fd;
    color: #0d6efd;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 20px;
  }

  .btn-brand {
    background: #6d0f1b;
    color: white;
    border-radius: 10px;
  }

  .btn-brand:hover {
    background: #500b14;
  }
  
  .variant-row {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #e0e0e0;
  }
  
  .color-preview {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
    vertical-align: middle;
    border: 2px solid #ddd;
  }
  
  .variant-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
  }
  
  .price-range {
    font-size: 14px;
    color: #666;
  }
  
  .variant-header {
    font-weight: 600;
    margin-bottom: 10px;
    color: #6d0f1b;
  }
  
  .remove-variant {
    margin-top: 10px;
  }
  
  /* Product Table Styles */
  .product-table th {
    font-weight: 600;
    color: #4a5568;
    border-bottom-width: 1px;
    padding: 12px 8px;
  }
  
  .product-table td {
    padding: 12px 8px;
    vertical-align: middle;
  }
  
  /* Variant Modal Table Styles */
  .variant-modal-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #4a5568;
  }
  
  .variant-modal-table td {
    vertical-align: middle;
  }
  
  .variant-img-thumb {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
  }
  
  .variant-color-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  
  .action-buttons {
    display: flex;
    gap: 5px;
  }
  
  .btn-icon-sm {
    padding: 4px 8px;
    font-size: 12px;
  }

  /* Modal variant list styling */
  .modal-variant-card {
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
  }
  .modal-variant-card:hover {
    background: #fafafa;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }
  .variant-label {
    font-weight: 600;
    color: #6d0f1b;
    min-width: 60px;
  }
  .variant-detail-text {
    color: #2d3748;
  }
  .stock-badge {
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 20px;
  }
  .stock-instock {
    background: #e6f7ef;
    color: #16a34a;
  }
  .stock-out {
    background: #fde8e8;
    color: #dc2626;
  }
  hr {
    margin: 1rem 0;
    opacity: 0.6;
  }
  
  .info-note {
    background: #e3f2fd;
    border-left: 4px solid #0d6efd;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
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
    <a href="seller_products.php" class="active"><i class="bi bi-box-seam"></i><span class="text">Products</span></a>
    <a href="seller_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
    <a href="seller_inventory.php"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
    <a href="seller_reviews.php"><i class="bi bi-star"></i><span class="text">Reviews</span></a>
    <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
   

  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<!-- MAIN -->
<div class="main-content" id="main">

<div class="container-fluid">

<h2 class="fw-bold">Products</h2>
<p class="text-muted">Manage your store inventory with size, color, price, stock, and images.</p>

<div class="info-note">
  <i class="bi bi-info-circle-fill me-2"></i>
  <strong>Note:</strong> Products and variants are separate. Each product can have multiple variants (size, color combinations). The product list shows only products, not individual variants.
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- ACTION BUTTONS -->
<div class="d-flex justify-content-between mb-3">
  <div>
    <button class="btn btn-outline-secondary me-2">
      <i class="bi bi-upload"></i> Bulk Upload
    </button>
    <button class="btn btn-outline-secondary">
      <i class="bi bi-download"></i> Export
    </button>
  </div>
  <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#addProductModal">
    <i class="bi bi-plus-lg"></i> Add Product
  </button>
</div>

<!-- ADD PRODUCT MODAL (with multiple variants) -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header" style="background: #6d0f1b; color: white;">
        <h5 class="modal-title">Add New Product with Variants</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
      </div>
      <div class="modal-body">
        <form id="addProductForm" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Product Name *</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Product Description *</label>
            <textarea name="description" class="form-control" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Category *</label>
            <select name="category" class="form-select" required>
              <option value="Men">Men</option>
              <option value="Women">Women</option>
            </select>
          </div>
          
          <hr>
          <h6 class="fw-bold mb-3">Product Variants (Each size/color combination is a separate variant)</h6>
          <p class="text-muted small mb-3">For example: If you have S, M, L sizes and Red, Blue colors, you need to create 6 variants (S-Red, S-Blue, M-Red, M-Blue, L-Red, L-Blue)</p>
          
          <div id="variants-container">
            <div class="variant-row" data-index="0">
              <div class="variant-header">Variant #1</div>
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label small">Size *</label>
                  <input type="text" name="variants[0][size]" class="form-control form-control-sm" placeholder="e.g., S, M, L, XL, 28, 30" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label small">Color *</label>
                  <input type="text" name="variants[0][color]" class="form-control form-control-sm" placeholder="e.g., Red, Blue, Black" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Price *</label>
                  <input type="number" step="0.01" name="variants[0][price]" class="form-control form-control-sm" placeholder="₱" required>
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Stock</label>
                  <input type="number" name="variants[0][stock]" class="form-control form-control-sm" placeholder="Qty" value="0">
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Image</label>
                  <input type="file" name="variant_image[0]" class="form-control form-control-sm" accept="image/*">
                </div>
              </div>
            </div>
          </div>
          
          <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addVariantRow()">
            <i class="bi bi-plus-circle"></i> Add Another Variant Combination
          </button>
          
          <div class="modal-footer px-0 pb-0 mt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="add_product" class="btn btn-brand">Add Product</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ADD SINGLE VARIANT MODAL -->
<div class="modal fade" id="addVariantModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background: #6d0f1b; color: white;">
        <h5 class="modal-title">Add New Variant to Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="product_id" id="variant_product_id" value="">
          <div class="mb-3">
            <label class="form-label">Size *</label>
            <input type="text" name="size" class="form-control" required placeholder="e.g., S, M, L, XL">
          </div>
          <div class="mb-3">
            <label class="form-label">Color *</label>
            <input type="text" name="color" class="form-control" required placeholder="e.g., Red, Blue, Black">
          </div>
          <div class="mb-3">
            <label class="form-label">Price *</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" value="0">
          </div>
          <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="variant_image" class="form-control" accept="image/*">
          </div>
          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="add_variant" class="btn btn-brand">Add Variant to Product</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- EDIT VARIANT MODAL -->
<div class="modal fade" id="editVariantModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background: #6d0f1b; color: white;">
        <h5 class="modal-title">Edit Variant</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
      </div>
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="variant_id" id="edit_variant_id" value="">
          <div class="mb-3">
            <label class="form-label">Size *</label>
            <input type="text" name="size" id="edit_size" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Color *</label>
            <input type="text" name="color" id="edit_color" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Price *</label>
            <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" id="edit_stock" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Current Image</label>
            <div id="current_image_preview"></div>
            <label class="form-label mt-2">Change Image (optional)</label>
            <input type="file" name="variant_image_edit" class="form-control" accept="image/*">
          </div>
          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="edit_variant" class="btn btn-brand">Update Variant</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- FILTER -->
<form method="GET" class="card p-3 mb-4 d-flex flex-row flex-wrap gap-3 align-items-center">
  <div class="flex-grow-1">
    <input name="search" class="form-control" placeholder="Search products by name..." value="<?php echo htmlspecialchars($search); ?>">
  </div>
  
  <select name="category" class="form-select w-auto" onchange="this.form.submit()">
    <option value="All Categories" <?php echo $filter_category === 'All Categories' ? 'selected' : ''; ?>>All Categories</option>
    <option value="Men" <?php echo $filter_category === 'Men' ? 'selected' : ''; ?>>Men</option>
    <option value="Women" <?php echo $filter_category === 'Women' ? 'selected' : ''; ?>>Women</option>
  </select>
  
  <button type="submit" class="btn btn-outline-secondary">Filter</button>
  <a href="seller_products.php" class="btn btn-link text-decoration-none">Clear</a>
</form>

<!-- PRODUCT TABLE - Clean with only specified columns -->
<div class="card p-3 mb-4">
  <h5 class="fw-bold mb-3">Product List</h5>
  
  <div class="table-responsive">
    <table class="table product-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Price Range</th>
          <th>Total Stock</th>
          <th>Variants</th>
          <th>Category</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr>
            <td colspan="7" class="text-center py-4">No products found. Click "Add Product" to create one.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
              <td>
                ₱<?php echo number_format($p['min_price'], 2); ?>
                <?php if ($p['max_price'] > $p['min_price']): ?>
                  - ₱<?php echo number_format($p['max_price'], 2); ?>
                <?php endif; ?>
               </td>
              <td><?php echo number_format($p['total_stock']); ?></td>
              <td><span class="badge-info"><?php echo $p['variant_count']; ?> variant(s)</span></td>
              <td><?php echo htmlspecialchars($p['category_gender']); ?></td>
              <td>
                <?php if ($p['status'] === 'active'): ?>
                  <span class="badge-soft">Active</span>
                <?php elseif ($p['status'] === 'inactive'): ?>
                  <span class="badge-red">Inactive</span>
                <?php elseif ($p['status'] === 'draft'): ?>
                  <span class="badge-secondary bg-secondary text-white px-2 py-1 rounded-pill" style="font-size: 12px;">Draft</span>
                <?php elseif ($p['status'] === 'archived'): ?>
                  <span class="badge-dark bg-dark text-white px-2 py-1 rounded-pill" style="font-size: 12px;">Archived</span>
                <?php else: ?>
                  <span class="badge-red"><?php echo ucfirst($p['status']); ?></span>
                <?php endif; ?>
               </td>
              <td>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#variantsModal<?php echo $p['product_id']; ?>">
                  <i class="bi bi-eye"></i> View Variants
                </button>
               </td>
            </tr>
            
            <!-- Variants Modal for each product - Shows ONLY variants, not products -->
            <div class="modal fade" id="variantsModal<?php echo $p['product_id']; ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header" style="background: #6d0f1b; color: white;">
                    <h5 class="modal-title"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Variants for: <?php echo htmlspecialchars($p['name']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
                  </div>
                  <div class="modal-body">
                    <?php
                    // Fetch variants for this product from product_variant table ONLY
                    $modal_variant_stmt = $conn->prepare("SELECT * FROM product_variant WHERE product_id = ? ORDER BY size, color");
                    $modal_variant_stmt->bind_param("i", $p['product_id']);
                    $modal_variant_stmt->execute();
                    $modal_variants = $modal_variant_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $modal_variant_stmt->close();
                    ?>
                    
                    <?php if (empty($modal_variants)): ?>
                      <div class="alert alert-info text-center py-4 mb-0">
                        <i class="bi bi-info-circle fs-4"></i>
                        <p class="mb-0 mt-2">No variants found for this product.</p>
                        <p class="small text-muted mt-2">Click "Add Another Variant" below to create one.</p>
                      </div>
                    <?php else: ?>
                      <div class="variant-list">
                        <?php foreach ($modal_variants as $variant): ?>
                          <div class="modal-variant-card">
                            <div class="row align-items-center">
                              <!-- Image Column -->
                              <div class="col-md-2 text-center mb-3 mb-md-0">
                                <?php if (!empty($variant['image_path']) && file_exists($variant['image_path'])): ?>
                                  <img src="<?php echo htmlspecialchars($variant['image_path']); ?>" class="variant-img-thumb" alt="Variant image" style="width: 80px; height: 80px; border-radius: 12px;">
                                <?php else: ?>
                                  <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 80px; height: 80px; border-radius: 12px;">
                                    <i class="bi bi-image text-secondary fs-2"></i>
                                  </div>
                                <?php endif; ?>
                              </div>
                              
                              <!-- Variant Details -->
                              <div class="col-md-8">
                                <div class="row g-2">
                                  <div class="col-sm-4">
                                    <div class="d-flex align-items-center">
                                      <span class="variant-label me-2"><i class="bi bi-arrow-left-right"></i> Size:</span>
                                      <span class="variant-detail-text fw-semibold"><?php echo htmlspecialchars($variant['size']); ?></span>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="d-flex align-items-center">
                                      <span class="variant-label me-2"><i class="bi bi-palette"></i> Color:</span>
                                      <div class="variant-color-badge">
                                        <span class="color-preview" style="background-color: <?php echo htmlspecialchars($variant['color']); ?>; width: 18px; height: 18px;"></span>
                                        <span><?php echo htmlspecialchars($variant['color']); ?></span>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="d-flex align-items-center">
                                      <span class="variant-label me-2"><i class="bi bi-upc-scan"></i> SKU:</span>
                                      <span class="variant-detail-text small"><code><?php echo htmlspecialchars($variant['sku']); ?></code></span>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="d-flex align-items-center">
                                      <span class="variant-label me-2"><i class="bi bi-tag"></i> Price:</span>
                                      <span class="variant-detail-text fw-bold text-success">₱<?php echo number_format($variant['price'], 2); ?></span>
                                    </div>
                                  </div>
                                  <div class="col-sm-4">
                                    <div class="d-flex align-items-center">
                                      <span class="variant-label me-2"><i class="bi bi-box"></i> Stock:</span>
                                      <?php if ($variant['stock_qty'] > 0): ?>
                                        <span class="stock-badge stock-instock"><?php echo $variant['stock_qty']; ?> in stock</span>
                                      <?php else: ?>
                                        <span class="stock-badge stock-out">Out of stock</span>
                                      <?php endif; ?>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              
                              <!-- Actions -->
                              <div class="col-md-2 text-center text-md-end mt-3 mt-md-0">
                                <div class="action-buttons justify-content-center justify-content-md-end">
                                  <button class="btn btn-sm btn-outline-secondary" onclick="populateEditModal(<?php echo htmlspecialchars(json_encode($variant)); ?>)" data-bs-toggle="modal" data-bs-target="#editVariantModal" title="Edit Variant">
                                    <i class="bi bi-pencil-square"></i>
                                  </button>
                                  <a href="?delete_variant=<?php echo $variant['variant_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this variant?')" title="Delete Variant">
                                    <i class="bi bi-trash3"></i>
                                  </a>
                                </div>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                    
                    <hr class="my-3">
                    <div class="text-end">
                      <button class="btn btn-sm btn-brand" onclick="showAddVariantModal(<?php echo $p['product_id']; ?>)">
                        <i class="bi bi-plus-circle"></i> Add Another Variant
                      </button>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <!-- PAGINATION -->
  <?php if ($total_pages > 1): ?>
  <nav aria-label="Product pagination" class="mt-3">
    <ul class="pagination justify-content-center">
      <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($filter_category); ?>">Previous</a>
      </li>
      
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
          <a class="page-link <?php echo $page === $i ? 'bg-brand border-brand' : ''; ?>" 
             href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($filter_category); ?>"
             style="<?php echo $page === $i ? 'background-color: #6d0f1b; border-color: #6d0f1b; color: white;' : 'color: #6d0f1b;'; ?>">
            <?php echo $i; ?>
          </a>
        </li>
      <?php endfor; ?>
      
      <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($filter_category); ?>">Next</a>
      </li>
    </ul>
  </nav>
  <?php endif; ?>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
let variantCount = 1;

function addVariantRow() {
    const container = document.getElementById('variants-container');
    const newRow = document.createElement('div');
    newRow.className = 'variant-row';
    newRow.setAttribute('data-index', variantCount);
    newRow.innerHTML = `
        <div class="variant-header">Variant #${variantCount + 1}</div>
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small">Size *</label>
                <input type="text" name="variants[${variantCount}][size]" class="form-control form-control-sm" placeholder="e.g., S, M, L, XL" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Color *</label>
                <input type="text" name="variants[${variantCount}][color]" class="form-control form-control-sm" placeholder="e.g., Red, Blue, Black" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Price *</label>
                <input type="number" step="0.01" name="variants[${variantCount}][price]" class="form-control form-control-sm" placeholder="₱" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Stock</label>
                <input type="number" name="variants[${variantCount}][stock]" class="form-control form-control-sm" placeholder="Qty" value="0">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Image</label>
                <input type="file" name="variant_image[${variantCount}]" class="form-control form-control-sm" accept="image/*">
            </div>
        </div>
        <div class="remove-variant">
            <button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('.variant-row').remove()">
                <i class="bi bi-trash"></i> Remove Variant
            </button>
        </div>
    `;
    container.appendChild(newRow);
    variantCount++;
}

function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
    document.getElementById("main").classList.toggle("full");
}

function showAddVariantModal(productId) {
    document.getElementById('variant_product_id').value = productId;
    const modal = new bootstrap.Modal(document.getElementById('addVariantModal'));
    modal.show();
}

function populateEditModal(variant) {
    document.getElementById('edit_variant_id').value = variant.variant_id;
    document.getElementById('edit_size').value = variant.size;
    document.getElementById('edit_color').value = variant.color;
    document.getElementById('edit_price').value = variant.price;
    document.getElementById('edit_stock').value = variant.stock_qty;
    
    const previewDiv = document.getElementById('current_image_preview');
    if (variant.image_path && variant.image_path !== 'null' && variant.image_path !== '') {
        previewDiv.innerHTML = `<img src="${variant.image_path}" style="max-width: 100px; max-height: 100px; border-radius: 8px;">`;
    } else {
        previewDiv.innerHTML = '<p class="text-muted text-center py-2 mb-0">No image uploaded</p>';
    }
}
</script>

</body>
</html>