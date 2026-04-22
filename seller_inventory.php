<?php
require_once 'auth.php';
require_roles([3, 4]);
require_once 'admin/db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;

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
    $variant_id = (int) ($_POST['variant_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($variant_id <= 0 || $quantity <= 0) {
        $error = "Invalid variant or quantity.";
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
            $message = "Restock successful!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to restock: " . $e->getMessage();
        }
    }
}

// Fetch all variants for the restock modal
$variants_stmt = $conn->prepare("
    SELECT pv.variant_id, p.name, pv.size, pv.color, pv.sku
    FROM product_variant pv
    JOIN product p ON pv.product_id = p.product_id
    WHERE p.seller_id = ?
");
$variants_stmt->bind_param("i", $seller_id);
$variants_stmt->execute();
$all_variants = $variants_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$variants_stmt->close();

// Fetch all variants and their restock batches
// We use a LEFT JOIN to ensure variants with no restock history still show up if they have stock
$inventory_sql = "
    SELECT 
        pv.variant_id,
        p.name as product_name,
        pv.sku,
        pv.stock_qty as total_variant_stock,
        ir.restock_id,
        ir.quantity as batch_quantity,
        ir.restock_date,
        ir.notes
    FROM product_variant pv
    JOIN product p ON pv.product_id = p.product_id
    LEFT JOIN inventory_restock ir ON pv.variant_id = ir.variant_id
    WHERE p.seller_id = ?
    ORDER BY p.name ASC, ir.restock_date DESC
";
$inventory_stmt = $conn->prepare($inventory_sql);
$inventory_stmt->bind_param("i", $seller_id);
$inventory_stmt->execute();
$inventory_entries = $inventory_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$inventory_stmt->close();

// Filter for history (only actual restock entries)
$history_entries = array_filter($inventory_entries, function($e) {
    return !is_null($e['restock_id']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Clothing Inventory Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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
}

.btn-brand:hover {
  background: #500b14;
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
}

.page-link {
  color: #6d0f1b;
}

.page-item.active .page-link {
  background-color: #6d0f1b;
  border-color: #6d0f1b;
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
    <a href="seller_reviews.php"><i class="bi bi-star"></i><span class="text">Reviews</span></a>
    <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
    <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

    <a href="logout.php" class="logout">
      <i class="bi bi-box-arrow-right"></i>
      <span class="text">Logout</span>
  </a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

<div class="container-fluid">

<h2 class="fw-bold">Inventory Management</h2>
<p class="text-muted">Manage stock levels, restock products, and track inventory history.</p>

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

<!-- HEADER ACTIONS -->
<div class="d-flex justify-content-between mb-4">
  <input class="form-control w-50" placeholder="Search inventory..." id="inventorySearch">
  <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#restockModal">
    <i class="bi bi-plus-lg"></i> Quick Restock
  </button>
</div>

<!-- TABS -->
<ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
  <li class="nav-item">
    <button class="nav-link active" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock-content" type="button">Current Stock</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-content" type="button">Restock History</button>
  </li>
</ul>

<div class="tab-content" id="inventoryTabsContent">

  <!-- CURRENT STOCK -->
  <div class="tab-pane fade show active" id="stock-content" role="tabpanel">
    <div class="card p-3">
      <div class="table-responsive">
        <table class="table" id="stockTable">
          <thead>
            <tr>
              <th>Product</th>
              <th>SKU</th>
              <th>Stock</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="stockTableBody">
            <?php if (empty($inventory_entries)): ?>
              <tr>
                <td colspan="5" class="text-center py-4">No inventory records found.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($inventory_entries as $entry): ?>
                <?php 
                  $display_qty = $entry['batch_quantity'] ?? $entry['total_variant_stock'];
                  $is_initial = is_null($entry['restock_id']);
                ?>
                <tr>
                  <td>
                    <strong><?php echo htmlspecialchars($entry['product_name']); ?></strong>
                    <div class="small text-muted">
                      <?php echo $is_initial ? 'Starting Batch' : 'Batch: RST-' . str_pad($entry['restock_id'], 4, '0', STR_PAD_LEFT); ?>
                    </div>
                  </td>
                  <td><?php echo htmlspecialchars($entry['sku']); ?></td>
                  <td>
                    <span class="fw-bold"><?php echo $display_qty; ?></span>
                    <div class="small text-muted">Total variant stock: <?php echo $entry['total_variant_stock']; ?></div>
                  </td>
                  <td>
                    <?php if ($entry['total_variant_stock'] > 20): ?>
                      <span class="badge-soft">In Stock</span>
                    <?php elseif ($entry['total_variant_stock'] > 0): ?>
                      <span class="badge-red">Low Stock</span>
                    <?php else: ?>
                      <span class="badge-red">Out of Stock</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary restock-btn" 
                            data-product="<?php echo htmlspecialchars($entry['product_name']); ?>" 
                            data-variant-id="<?php echo $entry['variant_id']; ?>">
                      Restock
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div id="stockPagination" class="pagination-container"></div>
    </div>
  </div>

  <!-- RESTOCK HISTORY -->
  <div class="tab-pane fade" id="history-content" role="tabpanel">
    <div class="card p-3">
      <div class="table-responsive">
        <table class="table" id="historyTable">
          <thead>
            <tr>
              <th>Restock ID</th>
              <th>Product</th>
              <th>Quantity Added</th>
              <th>Date</th>
              <th>Staff</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody id="historyTableBody">
            <?php if (empty($history_entries)): ?>
              <tr>
                <td colspan="6" class="text-center py-4">No restock history found.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($history_entries as $entry): ?>
                <tr>
                  <td>RST-<?php echo str_pad($entry['restock_id'], 4, '0', STR_PAD_LEFT); ?></td>
                  <td><?php echo htmlspecialchars($entry['product_name']); ?></td>
                  <td><?php echo $entry['batch_quantity']; ?></td>
                  <td><?php echo date('M d, Y', strtotime($entry['restock_date'])); ?></td>
                  <td>Seller</td>
                  <td><?php echo htmlspecialchars($entry['notes'] ?: 'N/A'); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div id="historyPagination" class="pagination-container"></div>
    </div>
  </div>

</div>
</div>
</div>

<!-- RESTOCK MODAL -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Restock Product: <span id="restockProductName">Select Product</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="restockForm" method="POST">
        <div class="modal-body">
          <input type="hidden" name="variant_id" id="variant_id_input">
          
          <div class="mb-3" id="variantSelectContainer">
            <label class="form-label">Select Product Variant</label>
            <select class="form-select" id="variant_select" onchange="document.getElementById('variant_id_input').value = this.value">
              <option value="">-- Choose a product --</option>
              <?php foreach ($all_variants as $v): ?>
                <option value="<?php echo $v['variant_id']; ?>">
                  <?php echo htmlspecialchars($v['name'] . " - " . $v['sku'] . " (" . $v['size'] . " / " . $v['color'] . ")"); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Restock Quantity</label>
            <input type="number" name="quantity" class="form-control" required min="1">
          </div>
          <div class="mb-3">
            <label class="form-label">Notes (Optional)</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="restock_submit" class="btn btn-brand">Confirm Restock</button>
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

// RESTOCK MODAL HANDLER
document.addEventListener('DOMContentLoaded', function() {
  const restockModalElement = document.getElementById('restockModal');
  const restockModal = new bootstrap.Modal(restockModalElement);
  const restockButtons = document.querySelectorAll('.restock-btn');
  const restockProductName = document.getElementById('restockProductName');
  const variantIdInput = document.getElementById('variant_id_input');
  const variantSelectContainer = document.getElementById('variantSelectContainer');
  const variantSelect = document.getElementById('variant_select');

  restockButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      const productName = this.getAttribute('data-product');
      const variantId = this.getAttribute('data-variant-id');
      
      restockProductName.textContent = productName;
      variantIdInput.value = variantId;
      variantSelectContainer.style.display = 'none'; // Hide selector when specific variant is chosen
      variantSelect.required = false;
      
      restockModal.show();
    });
  });

  // Handle "Quick Restock" button (which triggers modal without data-attributes)
  const quickRestockBtn = document.querySelector('.btn-brand[data-bs-target="#restockModal"]');
  if (quickRestockBtn) {
    quickRestockBtn.addEventListener('click', function() {
      restockProductName.textContent = 'Select Product';
      variantIdInput.value = '';
      variantSelectContainer.style.display = 'block'; // Show selector
      variantSelect.required = true;
      variantSelect.value = '';
    });
  }

  const itemsPerPage = 10;

  function paginateTable(tableBodyId, paginationId) {
    const tableBody = document.getElementById(tableBodyId);
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    const pageCount = Math.ceil(rows.length / itemsPerPage);
    const paginationContainer = document.getElementById(paginationId);
    let currentPage = 1;

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
      paginationContainer.innerHTML = `
        <div class="text-muted small">Showing ${Math.min(rows.length, itemsPerPage)} of ${rows.length} entries</div>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
              <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>
            ${Array.from({ length: pageCount }, (_, i) => i + 1).map(i => `
              <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
              </li>
            `).join('')}
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

    if (rows.length > 0) showPage(1);
    else paginationContainer.innerHTML = '<div class="text-muted small">No entries found</div>';
  }

  paginateTable('stockTableBody', 'stockPagination');

  document.getElementById('history-tab').addEventListener('shown.bs.tab', function () {
    paginateTable('historyTableBody', 'historyPagination');
  });

  document.getElementById('inventorySearch').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const activeTab = document.querySelector('.tab-pane.active');
    const tableBody = activeTab.querySelector('tbody');
    const rows = tableBody.querySelectorAll('tr');

    rows.forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(searchTerm) ? '' : 'none';
    });
  });
});
</script>

</body>
</html>