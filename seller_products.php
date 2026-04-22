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
    die("Seller profile not found. Please register as a seller.");
}

$message = '';
$error = '';

// Handle Product Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $category = $_POST['category'] ?? 'Men';

    if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
        $error = "Please fill in all fields correctly.";
    } else {
        $conn->begin_transaction();
        try {
            // Insert into product table
            $stmt = $conn->prepare("INSERT INTO product (seller_id, name, description, price, qty, category_gender, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
            $stmt->bind_param("issdis", $seller_id, $name, $description, $price, $stock, $category);  
            $stmt->execute();
            $product_id = $conn->insert_id;
            $stmt->close();

            // Insert into product_variant table (default variant)
            $sku = 'PROD-' . $product_id . '-' . strtoupper(substr(uniqid(), -4));
            $variant_stmt = $conn->prepare("INSERT INTO product_variant (product_id, sku, size, color, price, stock_qty) VALUES (?, ?, 'Standard', 'Default', ?, ?)");
            $variant_stmt->bind_param("isdi", $product_id, $sku, $price, $stock);
            $variant_stmt->execute();
            $variant_stmt->close();

            $conn->commit();
            $message = "Product added successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to add product: " . $e->getMessage();
        }
    }
}

// Pagination and Filtering
$search = trim($_GET['search'] ?? '');
$filter_category = $_GET['category'] ?? 'All Categories';
$page = (int) ($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Base query for counting
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

// Query for products
$products_sql = "
    SELECT p.product_id, p.name, p.category_gender, p.status, 
           COALESCE(MIN(pv.price), 0) as price, 
           COALESCE(SUM(pv.stock_qty), 0) as total_stock
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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

  .btn-brand {
    background: #6d0f1b;
    color: white;
    border-radius: 10px;
  }

  .btn-brand:hover {
    background: #500b14;
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
    <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<!-- MAIN -->
<div class="main-content" id="main">

<div class="container-fluid">

<h2 class="fw-bold">Products</h2>
<p class="text-muted">Manage your store inventory and listings.</p>

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
  <button class="btn btn-outline-secondary">
    <i class="bi bi-upload"></i> Bulk Upload
  </button>
  <button class="btn btn-brand" data-bs-toggle="modal" data-bs-target="#addProductModal">
    <i class="bi bi-plus-lg"></i> Add Product
  </button>
</div>

<!-- ADD PRODUCT MODAL -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background: #6d0f1b; color: white;">
        <h5 class="modal-title">Add New Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
      </div>
      <div class="modal-body">
        <form id="addProductForm" method="POST">
          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Product Description</label>
            <textarea name="description" class="form-control" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" name="price" class="form-control" step="0.01" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select">
              <option value="Men">Men</option>
              <option value="Women">Women</option>
            </select>
          </div>
          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="add_product" class="btn btn-brand">Add Product</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- FILTER -->
<form method="GET" class="card p-3 mb-4 d-flex flex-row flex-wrap gap-3">
  <input name="search" class="form-control w-50" placeholder="Search products by name..." value="<?php echo htmlspecialchars($search); ?>">
  
  <select name="category" class="form-select w-auto" onchange="this.form.submit()">
    <option value="All Categories" <?php echo $filter_category === 'All Categories' ? 'selected' : ''; ?>>All Categories</option>
    <option value="Men" <?php echo $filter_category === 'Men' ? 'selected' : ''; ?>>Men</option>
    <option value="Women" <?php echo $filter_category === 'Women' ? 'selected' : ''; ?>>Women</option>
  </select>
  
  <button type="submit" class="btn btn-outline-secondary">Filter</button>
  <a href="seller_products.php" class="btn btn-link text-decoration-none">Clear</a>
</form>

<!-- PRODUCT TABLE -->
<div class="card p-3 mb-4">

<h5 class="fw-bold mb-3">Product List</h5>

<table class="table">
<thead>
<tr>
<th>Product</th>
<th>Price</th>
<th>Stock</th>
<th>Category</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php if (empty($products)): ?>
<tr>
  <td colspan="5" class="text-center py-4">No products found.</td>
</tr>
<?php else: ?>
  <?php foreach ($products as $p): ?>
  <tr>
  <td><?php echo htmlspecialchars($p['name']); ?></td>
  <td>₱<?php echo number_format($p['price'], 2); ?></td>
  <td><?php echo $p['total_stock']; ?></td>
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
  </tr>
  <?php endforeach; ?>
<?php endif; ?>

</tbody>
</table>

<!-- PAGINATION -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Product pagination">
  <ul class="pagination justify-content-center mt-3">
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

<!-- JS -->
<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}
</script>

</body>
</html> 