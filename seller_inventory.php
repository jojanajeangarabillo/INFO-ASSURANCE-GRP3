<?php
require_once 'auth.php';
require_roles([3, 4]);
require_once 'admin/db.connect.php';


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

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
    die("Seller profile not found.");
}

$message = '';
$error = '';

// Handle Restock Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock_submit'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        $error = 'Invalid form submission';
    } else {
    $variant_id = (int) ($_POST['variant_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($variant_id <= 0 || $quantity <= 0) {
        $error = "Invalid variant or quantity.";
    } else {
        // Verify variant belongs to seller
        $verify_stmt = $conn->prepare("
            SELECT pv.variant_id, p.product_id 
            FROM product_variant pv 
            JOIN product p ON pv.product_id = p.product_id 
            WHERE pv.variant_id = ? AND p.seller_id = ?
        ");
        $verify_stmt->bind_param("ii", $variant_id, $seller_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            $error = "Variant not found or access denied.";
        } else {
            $conn->begin_transaction();
            try {
                // Insert into inventory_restock
                $stmt = $conn->prepare("INSERT INTO inventory_restock (variant_id, seller_id, quantity, notes) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiis", $variant_id, $seller_id, $quantity, $notes);
                $stmt->execute();
                $stmt->close();

                // Update total stock in product_variant
                $update_stmt = $conn->prepare("UPDATE product_variant SET stock_qty = stock_qty + ? WHERE variant_id = ?");
                $update_stmt->bind_param("ii", $quantity, $variant_id);
                $update_stmt->execute();
                $update_stmt->close();

                $conn->commit();
                $message = "Restock successful! Added " . $quantity . " units.";
                log_audit_action('update', 'Seller Inventory', 'User restocked inventory: added ' . $quantity . ' units');
                
                // Redirect to refresh the page
                header("Location: seller_inventory.php?success=1");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Failed to restock: " . $e->getMessage();
            }
        }
        $verify_stmt->close();
    }
}
}

// Handle Stock Adjustment (Increase/Decrease)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_stock'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        $error = 'Invalid form submission';
    } else {
    $variant_id = (int) ($_POST['variant_id'] ?? 0);
    $adjustment = (int) ($_POST['adjustment'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    
    if ($variant_id <= 0 || $adjustment == 0) {
        $error = "Invalid adjustment request.";
    } else {
        // Verify variant belongs to seller
        $verify_stmt = $conn->prepare("
            SELECT pv.variant_id, pv.stock_qty 
            FROM product_variant pv 
            JOIN product p ON pv.product_id = p.product_id 
            WHERE pv.variant_id = ? AND p.seller_id = ?
        ");
        $verify_stmt->bind_param("ii", $variant_id, $seller_id);
        $verify_stmt->execute();
        $variant_info = $verify_stmt->get_result()->fetch_assoc();
        
        if (!$variant_info) {
            $error = "Variant not found or access denied.";
        } else {
            $new_stock = $variant_info['stock_qty'] + $adjustment;
            
            if ($new_stock < 0) {
                $error = "Stock cannot be negative. Current stock: " . $variant_info['stock_qty'];
            } else {
                $conn->begin_transaction();
                try {
                    // Update stock
                    $update_stmt = $conn->prepare("UPDATE product_variant SET stock_qty = ? WHERE variant_id = ?");
                    $update_stmt->bind_param("ii", $new_stock, $variant_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Record adjustment in inventory_restock with negative quantity for decreases
                    $adjustment_type = $adjustment > 0 ? $adjustment : $adjustment;
                    $notes = "Manual adjustment: " . ($adjustment > 0 ? "+" : "") . $adjustment . " units. Reason: " . $reason;
                    
                    $stmt = $conn->prepare("INSERT INTO inventory_restock (variant_id, seller_id, quantity, notes) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiis", $variant_id, $seller_id, $adjustment, $notes);
                    $stmt->execute();
                    $stmt->close();
                    
                    $conn->commit();
                    $message = "Stock adjusted successfully! New stock: " . $new_stock;
                    log_audit_action('update', 'Seller Inventory', 'User adjusted inventory: ' . ($adjustment > 0 ? '+' : '') . $adjustment . ' units');
                    
                    header("Location: seller_inventory.php?success=1");
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Failed to adjust stock: " . $e->getMessage();
                }
            }
        }
        $verify_stmt->close();
    }
  }
}

// Fetch all variants with their current stock
$variants_stmt = $conn->prepare("
    SELECT 
        pv.variant_id,
        pv.sku,
        pv.size,
        pv.color,
        pv.price,
        pv.stock_qty,
        pv.image_path,
        p.product_id,
        p.name as product_name,
        p.category_gender,
        p.status as product_status
    FROM product_variant pv
    JOIN product p ON pv.product_id = p.product_id
    WHERE p.seller_id = ?
    ORDER BY p.name ASC, pv.size ASC, pv.color ASC
");
$variants_stmt->bind_param("i", $seller_id);
$variants_stmt->execute();
$variants = $variants_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$variants_stmt->close();

// Fetch restock history with batch information
$history_stmt = $conn->prepare("
    SELECT 
        ir.restock_id,
        ir.quantity,
        ir.restock_date,
        ir.notes,
        pv.variant_id,
        pv.sku,
        pv.size,
        pv.color,
        p.name as product_name
    FROM inventory_restock ir
    JOIN product_variant pv ON ir.variant_id = pv.variant_id
    JOIN product p ON pv.product_id = p.product_id
    WHERE ir.seller_id = ?
    ORDER BY ir.restock_date DESC
");
$history_stmt->bind_param("i", $seller_id);
$history_stmt->execute();
$restock_history = $history_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$history_stmt->close();

// Calculate summary statistics
$total_products = count($variants);
$total_stock = array_sum(array_column($variants, 'stock_qty'));
$low_stock_items = count(array_filter($variants, function($v) { return $v['stock_qty'] > 0 && $v['stock_qty'] <= 10; }));
$out_of_stock = count(array_filter($variants, function($v) { return $v['stock_qty'] == 0; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory Management - Seller</title>

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
body {
  background: #f5f5f5;
  font-family: 'Segoe UI', sans-serif;
}

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

.btn-brand {
  background: #6d0f1b;
  color: white;
  border-radius: 10px;
  padding: 8px 20px;
}

.btn-brand:hover {
  background: #500b14;
  color: white;
}

.btn-outline-brand {
  border: 1px solid #6d0f1b;
  color: #6d0f1b;
  background: transparent;
  border-radius: 10px;
}

.btn-outline-brand:hover {
  background: #6d0f1b;
  color: white;
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

.badge-warning {
  background: #fef3c7;
  color: #d97706;
  font-size: 12px;
  padding: 4px 10px;
  border-radius: 20px;
}

.stats-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  text-align: center;
  transition: transform 0.2s;
}

.stats-card:hover {
  transform: translateY(-5px);
}

.stats-number {
  font-size: 32px;
  font-weight: bold;
  color: #6d0f1b;
}

.stats-label {
  color: #666;
  font-size: 14px;
  margin-top: 5px;
}

.nav-tabs {
  border-bottom: 2px solid #eee;
  margin-bottom: 20px;
}

.nav-tabs .nav-link {
  border: none;
  color: #666;
  font-weight: 600;
  padding: 10px 20px;
}

.nav-tabs .nav-link.active {
  color: #6d0f1b;
  border-bottom: 2px solid #6d0f1b;
  background: transparent;
}

.pagination-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.page-link {
  color: #6d0f1b;
}

.page-item.active .page-link {
  background-color: #6d0f1b;
  border-color: #6d0f1b;
}

.variant-img-thumb {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
}

.table th {
  font-weight: 600;
  color: #4a5568;
  border-bottom-width: 1px;
}

.product-tag {
  background: #f3f4f6;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  color: #666;
}

.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.btn-icon-sm {
  padding: 5px 10px;
  font-size: 12px;
}

.filter-section {
  background: white;
  border-radius: 12px;
  padding: 15px;
  margin-bottom: 20px;
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
  <a href="seller_inventory.php" class="active"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
  <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content" id="main">
<div class="container-fluid">

<h2 class="fw-bold mb-2">Inventory Management</h2>
<p class="text-muted mb-4">Track and manage stock levels for all your product variants.</p>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Operation completed successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-md-3">
    <div class="stats-card">
      <div class="stats-number"><?php echo $total_products; ?></div>
      <div class="stats-label">Total Variants</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card">
      <div class="stats-number"><?php echo number_format($total_stock); ?></div>
      <div class="stats-label">Total Units in Stock</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card">
      <div class="stats-number" style="color: #d97706;"><?php echo $low_stock_items; ?></div>
      <div class="stats-label">Low Stock Items (≤10)</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card">
      <div class="stats-number" style="color: #dc2626;"><?php echo $out_of_stock; ?></div>
      <div class="stats-label">Out of Stock</div>
    </div>
  </div>
</div>

<!-- Header Actions -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div class="d-flex gap-2">
    <div class="input-group" style="width: 300px;">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" id="inventorySearch" placeholder="Search by product, SKU, size, color...">
    </div>
    <select class="form-select" id="stockFilter" style="width: 150px;">
      <option value="all">All Stock</option>
      <option value="instock">In Stock (>10)</option>
      <option value="lowstock">Low Stock (1-10)</option>
      <option value="outofstock">Out of Stock (0)</option>
    </select>
  </div>
  <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#restockModal" id="quickRestockBtn">
    <i class="bi bi-plus-lg"></i> Quick Restock
  </button>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
  <li class="nav-item">
    <button class="nav-link active" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock-content" type="button">
      <i class="bi bi-boxes"></i> Current Stock
    </button>
  </li>
  <li class="nav-item">
    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-content" type="button">
      <i class="bi bi-clock-history"></i> Restock History
    </button>
  </li>
</ul>

<div class="tab-content" id="inventoryTabsContent">

  <!-- CURRENT STOCK TAB -->
  <div class="tab-pane fade show active" id="stock-content" role="tabpanel">
    <div class="card p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Image</th>
              <th>Product / Variant</th>
              <th>SKU</th>
              <th>Size</th>
              <th>Color</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="stockTableBody">
           <?php if (empty($variants)): ?>
  <tr>
    <td colspan="9" class="text-center py-5">
      <i class="bi bi-inbox fs-1 text-muted"></i>
      <p class="mt-2">No inventory items found.</p>
      <a href="seller_products.php" class="btn btn-sm btn-brand">Add Products</a>
    </td>
  </tr>
<?php else: ?>
  <?php foreach ($variants as $variant): ?>
    <tr class="inventory-row"
        data-product="<?php echo strtolower(htmlspecialchars($variant['product_name'])); ?>"
        data-sku="<?php echo strtolower(htmlspecialchars($variant['sku'])); ?>"
        data-size="<?php echo strtolower(htmlspecialchars($variant['size'])); ?>"
        data-color="<?php echo strtolower(htmlspecialchars($variant['color'])); ?>"
        data-stock="<?php echo $variant['stock_qty']; ?>">

      <!-- IMAGE -->
      <td>
        <?php if (!empty($variant['image_path']) && file_exists($variant['image_path'])): ?>
          <img src="<?php echo htmlspecialchars($variant['image_path']); ?>" class="variant-img-thumb">
        <?php else: ?>
          <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width:50px;height:50px;">
            <i class="bi bi-image text-secondary"></i>
          </div>
        <?php endif; ?>
      </td>

      <!-- PRODUCT -->
      <td>
        <strong><?php echo htmlspecialchars($variant['product_name']); ?></strong>
        <div class="product-tag mt-1">Variant ID: #<?php echo $variant['variant_id']; ?></div>
      </td>

      <!-- SKU -->
      <td><code><?php echo htmlspecialchars($variant['sku']); ?></code></td>

      <!-- SIZE -->
      <td>
        <span class="badge bg-secondary bg-opacity-10 text-dark">
          <?php echo htmlspecialchars($variant['size']); ?>
        </span>
      </td>

      <!-- COLOR -->
      <td>
        <div class="d-flex align-items-center gap-2">
          <span style="width:20px;height:20px;background:<?php echo htmlspecialchars($variant['color']); ?>;border-radius:50%;border:1px solid #ddd;"></span>
          <?php echo htmlspecialchars($variant['color']); ?>
        </div>
       </td>

      <!-- PRICE -->
      <td>₱<?php echo number_format($variant['price'], 2); ?></td>

      <!-- STOCK -->
      <td>
        <span class="fw-bold <?php echo $variant['stock_qty'] > 10 ? 'text-success' : ($variant['stock_qty'] > 0 ? 'text-warning' : 'text-danger'); ?>">
          <?php echo $variant['stock_qty']; ?>
        </span>
       </td>

      <!-- STATUS -->
      <td>
        <?php if ($variant['stock_qty'] > 20): ?>
          <span class="badge-soft">In Stock</span>
        <?php elseif ($variant['stock_qty'] > 0): ?>
          <span class="badge-warning">Low Stock</span>
        <?php else: ?>
          <span class="badge-red">Out of Stock</span>
        <?php endif; ?>
       </td>

      <!-- ACTIONS -->
      <td>
        <div class="action-buttons">
          <button class="btn btn-sm btn-outline-brand restock-btn"
            data-variant-id="<?php echo $variant['variant_id']; ?>"
            data-product-name="<?php echo htmlspecialchars($variant['product_name']); ?>"
            data-variant-detail="<?php echo $variant['size'] . ' / ' . $variant['color']; ?>">
            <i class="bi bi-plus-circle"></i> Restock
          </button>

          <button class="btn btn-sm btn-outline-secondary adjust-stock-btn"
            data-variant-id="<?php echo $variant['variant_id']; ?>"
            data-product-name="<?php echo htmlspecialchars($variant['product_name']); ?>"
            data-current-stock="<?php echo $variant['stock_qty']; ?>">
            <i class="bi bi-sliders2"></i> Adjust
          </button>
        </div>
       </td>

     </tr>
  <?php endforeach; ?>
<?php endif; ?>
          </tbody>
        </table>
      </div>
      <div id="stockPagination" class="pagination-container px-3"></div>
    </div>
  </div>

  <!-- RESTOCK HISTORY TAB -->
  <div class="tab-pane fade" id="history-content" role="tabpanel">
    <div class="card p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Product</th>
              <th>Variant</th>
              <th>SKU</th>
              <th>Quantity</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody id="historyTableBody">
            <?php if (empty($restock_history)): ?>
              <tr>
                <td colspan="6" class="text-center py-5">
                  <i class="bi bi-clock-history fs-1 text-muted"></i>
                  <p class="mt-2">No restock history found.</p>
                  <p class="text-muted small">Restock products to see history here.</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($restock_history as $history): ?>
                <tr>
                  <td>
                    <span class="fw-bold"><?php echo date('M d, Y', strtotime($history['restock_date'])); ?></span>
                    <br>
                    <small class="text-muted"><?php echo date('h:i A', strtotime($history['restock_date'])); ?></small>
                  </td>
                  <td>
                    <strong><?php echo htmlspecialchars($history['product_name']); ?></strong>
                  </td>
                  <td>
                    <span class="badge bg-secondary bg-opacity-10 text-dark">
                      Size: <?php echo htmlspecialchars($history['size']); ?>
                    </span>
                    <br>
                    <small>Color: <?php echo htmlspecialchars($history['color']); ?></small>
                  </td>
                  <td>
                    <code><?php echo htmlspecialchars($history['sku']); ?></code>
                  </td>
                  <td>
                    <?php 
                    $quantity = (int)$history['quantity'];
                    $badgeClass = $quantity > 0 ? 'bg-success' : 'bg-danger';
                    $sign = $quantity > 0 ? '+' : '';
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>">
                      <?php echo $sign . $quantity; ?> units
                    </span>
                  </td>
                  <td>
                    <small class="text-muted">
                      <?php echo !empty($history['notes']) ? htmlspecialchars($history['notes']) : '—'; ?>
                    </small>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div id="historyPagination" class="pagination-container px-3"></div>
    </div>
  </div>

</div>
</div>
</div>

<!-- RESTOCK MODAL -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background: #6d0f1b; color: white;">
        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Restock Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
          <input type="hidden" name="variant_id" id="restock_variant_id">
          
          <div class="mb-3">
            <label class="form-label fw-bold">Product</label>
            <div class="form-control bg-light" id="restockProductDisplay">Select a variant above</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Restock Quantity *</label>
            <input type="number" name="quantity" class="form-control" required min="1" placeholder="Enter quantity to add">
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Notes (Optional)</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="e.g., Supplier delivery, Stock adjustment, etc."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="restock_submit" class="btn btn-brand">Confirm Restock</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ADJUST STOCK MODAL -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background: #6d0f1b; color: white;">
        <h5 class="modal-title"><i class="bi bi-sliders2"></i> Adjust Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="variant_id" id="adjust_variant_id">
          
          <div class="mb-3">
            <label class="form-label fw-bold">Product</label>
            <div class="form-control bg-light" id="adjustProductDisplay">Loading...</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Current Stock</label>
            <div class="form-control bg-light" id="currentStockDisplay">0</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Adjustment Amount *</label>
            <input type="number" name="adjustment" class="form-control" required placeholder="Positive to add, negative to remove">
            <small class="text-muted">Enter positive number to add stock, negative to remove stock</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Reason *</label>
            <textarea name="reason" class="form-control" rows="2" required placeholder="e.g., Damaged items, Inventory count correction, Return, etc."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="adjust_stock" class="btn btn-brand">Apply Adjustment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

// Global variables for pagination
let stockRows = [];
let historyRows = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  // Setup stock rows for pagination
  const stockTableBody = document.getElementById('stockTableBody');
  if (stockTableBody) {
    stockRows = Array.from(stockTableBody.querySelectorAll('.inventory-row'));
    if (stockRows.length > 0) {
      setupPagination('stockTableBody', 'stockPagination', stockRows);
    }
  }
  
  // Setup history rows for pagination
  const historyTableBody = document.getElementById('historyTableBody');
  if (historyTableBody) {
    historyRows = Array.from(historyTableBody.querySelectorAll('tr'));
    // Filter out the "no data" row if it exists
    historyRows = historyRows.filter(row => !row.querySelector('td[colspan]'));
    if (historyRows.length > 0) {
      setupPagination('historyTableBody', 'historyPagination', historyRows);
    }
  }
  
  // Search functionality
  const searchInput = document.getElementById('inventorySearch');
  if (searchInput) {
    searchInput.addEventListener('keyup', filterStockTable);
  }
  
  // Stock filter
  const stockFilter = document.getElementById('stockFilter');
  if (stockFilter) {
    stockFilter.addEventListener('change', filterStockTable);
  }
  
  // Restock buttons
  document.querySelectorAll('.restock-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const variantId = this.getAttribute('data-variant-id');
      const productName = this.getAttribute('data-product-name');
      const variantDetail = this.getAttribute('data-variant-detail') || '';
      
      document.getElementById('restock_variant_id').value = variantId;
      document.getElementById('restockProductDisplay').innerHTML = 
        `<strong>${productName}</strong><br><small class="text-muted">${variantDetail}</small>`;
      
      const modal = new bootstrap.Modal(document.getElementById('restockModal'));
      modal.show();
    });
  });
  
  // Quick restock button (opens modal with variant selection)
  const quickRestockBtn = document.getElementById('quickRestockBtn');
  if (quickRestockBtn) {
    quickRestockBtn.addEventListener('click', function() {
      document.getElementById('restock_variant_id').value = '';
      document.getElementById('restockProductDisplay').innerHTML = 
        `<span class="text-muted">Select a product variant from the list below first, or use the "Restock" button on any row</span>
         <div class="mt-2">
           <select class="form-select" id="variantQuickSelect">
             <option value="">-- Select a variant --</option>
             <?php foreach ($variants as $v): ?>
               <option value="<?php echo $v['variant_id']; ?>">
                 <?php echo htmlspecialchars($v['product_name'] . ' - ' . $v['size'] . ' / ' . $v['color'] . ' (SKU: ' . $v['sku'] . ')'); ?>
               </option>
             <?php endforeach; ?>
           </select>
         </div>`;
      
      setTimeout(() => {
        const quickSelect = document.getElementById('variantQuickSelect');
        if (quickSelect) {
          quickSelect.addEventListener('change', function() {
            const selectedId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            if (selectedId) {
              document.getElementById('restock_variant_id').value = selectedId;
              document.getElementById('restockProductDisplay').innerHTML = 
                `<strong>${selectedOption.text.split(' - ')[0]}</strong><br><small class="text-muted">${selectedOption.text.split(' - ').slice(1).join(' - ')}</small>`;
            }
          });
        }
      }, 100);
    });
  }
  
  // Adjust stock buttons
  document.querySelectorAll('.adjust-stock-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const variantId = this.getAttribute('data-variant-id');
      const productName = this.getAttribute('data-product-name');
      const currentStock = this.getAttribute('data-current-stock');
      
      document.getElementById('adjust_variant_id').value = variantId;
      document.getElementById('adjustProductDisplay').innerHTML = `<strong>${productName}</strong>`;
      document.getElementById('currentStockDisplay').innerHTML = currentStock;
      
      const modal = new bootstrap.Modal(document.getElementById('adjustStockModal'));
      modal.show();
    });
  });
});

function filterStockTable() {
  const searchTerm = document.getElementById('inventorySearch').value.toLowerCase();
  const stockFilter = document.getElementById('stockFilter').value;
  const rows = document.querySelectorAll('#stockTableBody .inventory-row');
  
  let visibleCount = 0;
  rows.forEach(row => {
    const product = row.getAttribute('data-product') || '';
    const sku = row.getAttribute('data-sku') || '';
    const size = row.getAttribute('data-size') || '';
    const color = row.getAttribute('data-color') || '';
    const stock = parseInt(row.getAttribute('data-stock')) || 0;
    
    const matchesSearch = product.includes(searchTerm) || 
                          sku.includes(searchTerm) || 
                          size.includes(searchTerm) || 
                          color.includes(searchTerm);
    
    let matchesFilter = true;
    if (stockFilter === 'instock') matchesFilter = stock > 10;
    else if (stockFilter === 'lowstock') matchesFilter = stock > 0 && stock <= 10;
    else if (stockFilter === 'outofstock') matchesFilter = stock === 0;
    
    if (matchesSearch && matchesFilter) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  
  // Update pagination for filtered rows
  const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
  if (visibleRows.length > 0) {
    setupPagination('stockTableBody', 'stockPagination', visibleRows);
  }
  
  // Show message if no results
  const tableBody = document.getElementById('stockTableBody');
  const existingMsg = tableBody.querySelector('.no-results-msg');
  if (visibleCount === 0 && !existingMsg) {
    const msgRow = document.createElement('tr');
    msgRow.className = 'no-results-msg';
    msgRow.innerHTML = `<td colspan="9" class="text-center py-4 text-muted">No items match your search criteria.</td>`;
    tableBody.appendChild(msgRow);
  } else if (visibleCount > 0 && existingMsg) {
    existingMsg.remove();
  }
}

function setupPagination(tableBodyId, paginationId, rows) {
  const itemsPerPage = 10;
  const pageCount = Math.ceil(rows.length / itemsPerPage);
  const paginationContainer = document.getElementById(paginationId);
  let currentPage = 1;
  
  if (!paginationContainer) return;
  
  function showPage(page) {
    currentPage = page;
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    
    rows.forEach((row, index) => {
      row.style.display = (index >= start && index < end) ? '' : 'none';
    });
    
    renderPagination();
  }
  
  function renderPagination() {
    if (pageCount <= 1) {
      paginationContainer.innerHTML = `<div class="text-muted small">Showing all ${rows.length} entries</div>`;
      return;
    }
    
    paginationContainer.innerHTML = `
      <div class="text-muted small">Showing ${Math.min(rows.length, itemsPerPage)} of ${rows.length} entries</div>
      <nav>
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
          </li>
          ${generatePageNumbers()}
          <li class="page-item ${currentPage === pageCount ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
          </li>
        </ul>
      </nav>
    `;
    
    paginationContainer.querySelectorAll('.page-link').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const page = parseInt(e.target.getAttribute('data-page'));
        if (page >= 1 && page <= pageCount) {
          showPage(page);
        }
      });
    });
  }
  
  function generatePageNumbers() {
    let pages = '';
    const maxVisible = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(pageCount, startPage + maxVisible - 1);
    
    if (endPage - startPage < maxVisible - 1) {
      startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    if (startPage > 1) {
      pages += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
      if (startPage > 2) pages += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    
    for (let i = startPage; i <= endPage; i++) {
      pages += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                  <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
    }
    
    if (endPage < pageCount) {
      if (endPage < pageCount - 1) pages += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      pages += `<li class="page-item"><a class="page-link" href="#" data-page="${pageCount}">${pageCount}</a></li>`;
    }
    
    return pages;
  }
  
  if (rows.length > 0) showPage(1);
  else paginationContainer.innerHTML = '<div class="text-muted small">No entries found</div>';
}

// Reinitialize pagination when switching tabs
document.getElementById('history-tab')?.addEventListener('shown.bs.tab', function() {
  const historyRows_ = Array.from(document.querySelectorAll('#historyTableBody tr'));
  const validHistoryRows = historyRows_.filter(row => !row.querySelector('td[colspan]'));
  if (validHistoryRows.length > 0) {
    setupPagination('historyTableBody', 'historyPagination', validHistoryRows);
  }
});

document.getElementById('stock-tab')?.addEventListener('shown.bs.tab', function() {
  const stockRows_ = Array.from(document.querySelectorAll('#stockTableBody .inventory-row'));
  const validStockRows = stockRows_.filter(row => row.style.display !== 'none');
  if (validStockRows.length > 0) {
    setupPagination('stockTableBody', 'stockPagination', validStockRows);
  } else if (stockRows_.length > 0) {
    setupPagination('stockTableBody', 'stockPagination', stockRows_);
  }
});
</script>

</body>
</html>