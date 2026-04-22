<?php
require_once 'auth.php';
require_roles([3, 4]);

require_once __DIR__ . '/admin/db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$message = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $shop_name = trim($_POST['shop_name'] ?? '');
    $shop_description = trim($_POST['shop_description'] ?? '');
    $shop_address = trim($_POST['shop_address'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    if (empty($shop_name)) {
        $error = "Shop name is required.";
    } else {
        $stmt = $conn->prepare("UPDATE seller SET shop_name = ?, shop_description = ?, shop_address = ?, contact_number = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $shop_name, $shop_description, $shop_address, $contact_number, $user_id);
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $error = "Failed to update profile.";
        }
        $stmt->close();
    }
}

$seller_stmt = $conn->prepare("
    SELECT s.*, u.username, u.email
    FROM seller s
    JOIN user u ON s.user_id = u.user_id
    WHERE s.user_id = ?
");
$seller_stmt->bind_param("i", $user_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_stmt->close();

$shop_name = $seller['shop_name'] ?? '';
$shop_description = $seller['shop_description'] ?? '';
$shop_address = $seller['shop_address'] ?? '';
$contact_number = $seller['contact_number'] ?? '';
$email = $seller['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Store Settings</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body {
  background: #f5f5f5;
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
}

.sidebar {
  width: 240px;
  position: fixed;
  height: 100%;
}

.sidebar.collapsed {
  width: 70px;
}

.main-content {
  margin-left: 240px;
  transition: 0.3s;
  padding: 20px;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 70px;
}

.card-custom {
  background: #fff;
  border-radius: 16px;
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  padding: 25px;
}

:root {
  --brand: #6d0f1b;
}

.btn-brand {
  background: var(--brand);
  color: white;
  border-radius: 10px;
}

.btn-brand:hover {
  background: #500b14;
}

.tab-btn {
  cursor: pointer;
  padding: 10px 15px;
  border-radius: 10px;
}

.tab-active {
  background: var(--brand);
  color: white;
}

.form-control, .form-select {
  border-radius: 10px;
}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="logo-text">Seller</div>
  </div>

    <a href="seller_dashboard.php"><i class="bi bi-speedometer2"></i><span class="text">Dashboard</span></a>
    <a href="seller_products.php"><i class="bi bi-box-seam"></i><span class="text">Products</span></a>
    <a href="seller_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
    <a href="seller_inventory.php"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
    <a href="seller_reviews.php"><i class="bi bi-star"></i><span class="text">Reviews</span></a>
    <a href="seller_profile.php" class="active"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
    <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>
  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<div class="main-content">

<div class="container-fluid">

<h3 class="fw-bold">Store Settings</h3>
<p class="text-muted">Manage your store profile and preferences.</p>

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

<div class="d-flex gap-2 mb-4">
  <div class="tab-btn tab-active" onclick="switchTab('profile')">Store Profile</div>
</div>

<div id="profile" class="tab-content">

<form action="" method="POST">
<div class="card-custom mb-4">
<h5 class="fw-bold mb-3">Store Information</h5>

<div class="mb-3">
    <label class="form-label text-muted small fw-bold">SHOP NAME</label>
    <input name="shop_name" class="form-control" value="<?php echo htmlspecialchars($shop_name); ?>" required>
</div>

<div class="mb-3">
    <label class="form-label text-muted small fw-bold">SHOP DESCRIPTION</label>
    <textarea name="shop_description" class="form-control" rows="3"><?php echo htmlspecialchars($shop_description); ?></textarea>
</div>

<div class="mb-3">
    <label class="form-label text-muted small fw-bold">SHOP ADDRESS</label>
    <input name="shop_address" class="form-control" value="<?php echo htmlspecialchars($shop_address); ?>">
</div>

<div class="mb-3">
    <label class="form-label text-muted small fw-bold">CONTACT NUMBER</label>
    <input name="contact_number" class="form-control" value="<?php echo htmlspecialchars($contact_number); ?>">
</div>

<div class="mb-3">
    <label class="form-label text-muted small fw-bold">EMAIL ADDRESS</label>
    <input class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
    <small class="text-muted">Email cannot be changed here.</small>
</div>

</div>

<div class="text-end">
<button type="submit" name="update_profile" class="btn btn-brand">Save Settings</button>
</div>
</form>

</div>

</div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
}

function switchTab(tab) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.add('d-none'));
  document.getElementById(tab).classList.remove('d-none');

  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('tab-active'));
  event.target.classList.add('tab-active');
}
</script>

</body>
</html>