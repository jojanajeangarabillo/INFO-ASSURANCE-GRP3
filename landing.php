<?php
session_start();
require_once 'admin/db.connect.php';

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? 'All');
$size = trim($_GET['size'] ?? 'All');
$color = trim($_GET['color'] ?? 'All');
$minPrice = isset($_GET['min_price']) ? (float) $_GET['min_price'] : '';
$maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : '';

$sql = "SELECT
          p.product_id,
          p.name,
          p.category_gender,
          MIN(pv.price) AS min_price
        FROM product p
        INNER JOIN product_variant pv ON pv.product_id = p.product_id
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

if ($size !== '' && $size !== 'All') {
  $sql .= " AND pv.size = ?";
  $types .= "s";
  $params[] = $size;
}

if ($color !== '' && $color !== 'All') {
  $sql .= " AND pv.color = ?";
  $types .= "s";
  $params[] = $color;
}

if ($minPrice !== '' && $minPrice >= 0) {
  $sql .= " AND pv.price >= ?";
  $types .= "d";
  $params[] = $minPrice;
}

if ($maxPrice !== '' && $maxPrice >= 0) {
  $sql .= " AND pv.price <= ?";
  $types .= "d";
  $params[] = $maxPrice;
}

$sql .= " GROUP BY p.product_id, p.name, p.category_gender ORDER BY p.created_at DESC LIMIT 24";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$sizes = [];
$colors = [];
$optionStmt = $conn->prepare("
  SELECT DISTINCT size, color
  FROM product_variant
  ORDER BY size ASC, color ASC
");
$optionStmt->execute();
$optionRows = $optionStmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($optionRows as $row) {
  if (!in_array($row['size'], $sizes, true)) {
    $sizes[] = $row['size'];
  }
  if (!in_array($row['color'], $colors, true)) {
    $colors[] = $row['color'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>J3RS Landing Page</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background: #fdf2f6;
}

/* Header */
header {
  position: sticky;
  top: 0;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid #eee;
  padding: 15px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo-img {
  width: 45px;   /* 👈 adjust size here */
  height: auto;
}

.logo-box {
  width: 40px;
  height: 40px;
  background: #610C27;
  border-radius: 10px;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

header strong {
  color: #000; /* black */
}

nav {
  display: flex;
  gap: 25px;
}

nav a {
  text-decoration: none;
  color: #000; /* black */
  font-size: 14px;
  font-weight: 500;
}

nav a:hover {
  color: #a61b4a;
}

.buttons button {
  margin-left: 10px;
  padding: 8px 15px;
  border: none;
  cursor: pointer;
  border-radius: 6px;
  font-weight: 500;
}

/* Buttons */
.login {
  background: transparent;
  color: #000;
}

.signup {
  background: #a61b4a;
  color: white;
}

.signup:hover {
  background: #610C27;
}

/* Hero */
.hero {
  position: relative;
  height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 20px;
}

.hero img {
  position: absolute;
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.4;
}

.hero-content {
  position: relative;
  max-width: 700px;
}

.hero h1 {
  font-size: 50px;
  color: #000; /* black */
  margin-bottom: 20px;
}

.hero span {
  color: #a61b4a;
}

.hero p {
  color: #000; /* black */
  margin-bottom: 30px;
}

/* CTA Button */
.hero button {
  padding: 15px 30px;
  background: #a61b4a;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 6px;
  font-size: 16px;
}

.hero button:hover {
  background: #610C27;
}

/* Features */
.features {
  padding: 80px 40px;
  background: white;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
  gap: 30px;
  text-align: center;
}

.feature-box {
  padding: 20px;
}

.icon {
  width: 60px;
  height: 60px;
  background: #fdf2f6;
  border-radius: 15px;
  margin: auto;
  margin-bottom: 20px;
}

h3 {
  color: #000; /* black */
}

p {
  color: #000; /* black */
}

/* Product listing */
.products-section {
  padding: 48px 0 64px;
  background: #fff;
}
.product-card {
  border: 1px solid #eee;
  border-radius: 14px;
  background: #fff;
  overflow: hidden;
}
.product-image {
  width: 100%;
  height: 340px;
  object-fit: cover;
}
.product-category {
  font-size: 12px;
  color: #6f6f6f;
  margin-bottom: 8px;
}
.product-title {
  font-size: 17px;
  margin: 0 0 8px;
}
.product-price {
  font-size: 22px;
  font-weight: 700;
  color: #610C27;
  margin-bottom: 12px;
}
.product-link {
  display: inline-block;
  text-decoration: none;
  background: #a61b4a;
  color: #fff;
  padding: 9px 12px;
  border-radius: 8px;
  font-size: 13px;
}
.product-link:hover { background: #610C27; }

.search-input {
  border: 1px solid #e8d7df;
  border-radius: 8px;
  padding: 10px 12px;
}

.signup-modal-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 1100;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.signup-modal-backdrop.active { display: flex; }
.signup-modal-box {
  width: 100%;
  max-width: 460px;
  background: #fff;
  border-radius: 14px;
  padding: 24px;
}
.signup-option-btn {
  width: 100%;
  border: none;
  border-radius: 8px;
  padding: 12px 14px;
  font-weight: 600;
}
.signup-option-btn.customer {
  background: #fdf2f6;
  color: #610C27;
}
.signup-option-btn.seller {
  background: #a61b4a;
  color: #fff;
}
</style>

</head>
<body>

<!-- Header -->
<header>
  <div class="logo">
    <img src="JERS-LOGO.png" class="logo-img">
  </div>

  <nav>
    <a href="#">Products</a>
    <a href="#">Categories</a>
    <a href="#">About Us</a>
    <a href="#">Contact</a>
  </nav>

  <div class="buttons">
    <button class="login" onclick="window.location.href='login.php'">Log in</button>
    <button class="signup" onclick="openSignupModal()">Sign up</button>
  </div>
</header>

<!-- Hero Section -->
<section class="hero">
  <img src="labg.jpg">
  <div class="hero-content">
    <h1>
      Curated essentials for <br>
      <span>modern living.</span>
    </h1>
    <p>
      Discover our handpicked selection of premium products designed to
      elevate your everyday experience. Quality meets aesthetics.
    </p>
    <button onclick="window.location.href='#products'">Shop Collection →</button>
  </div>
</section>

<div class="signup-modal-backdrop" id="signupModal">
  <div class="signup-modal-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Sign up as</h5>
      <button type="button" class="btn-close" aria-label="Close" onclick="closeSignupModal()"></button>
    </div>
    <p class="text-muted mb-3">Choose how you want to create your account.</p>
    <div class="d-grid gap-2">
      <button class="signup-option-btn customer" onclick="window.location.href='register.php'">Customer</button>
      <button class="signup-option-btn seller" onclick="window.location.href='seller_signup.php'">Seller</button>
    </div>
  </div>
</div>

<section class="products-section" id="products">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h2 class="mb-0" style="color:#000;">Product Collection</h2>
      <form method="GET" class="d-flex">
        <input class="search-input" type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products">
      </form>
    </div>

    <div class="row g-4">
      <?php if (empty($products)): ?>
        <div class="col-12">
          <p class="mb-0">No active products found.</p>
        </div>
      <?php endif; ?>
      <?php foreach ($products as $product): ?>
        <div class="col-12 col-sm-6 col-md-3">
          <div class="product-card h-100 d-flex flex-column">
            <img class="product-image" src="https://via.placeholder.com/300x400?text=Fashion+Item" alt="Product image">
            <div class="p-3 d-flex flex-column flex-grow-1">
              <div class="product-category"><?php echo htmlspecialchars($product['category_gender']); ?></div>
              <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
              <div class="product-price">P<?php echo number_format((float) $product['min_price'], 2); ?></div>
              <a class="product-link mt-auto" href="product_details.php?product_id=<?php echo (int) $product['product_id']; ?>">View Details</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Features -->
<section class="features">
  <div class="feature-grid">

    <div class="feature-box">
      <div class="icon"></div>
      <h3>Free Shipping</h3>
      <p>On all orders over ₱2,000. Fast and reliable delivery.</p>
    </div>

    <div class="feature-box">
      <div class="icon"></div>
      <h3>Secure Payments</h3>
      <p>Your transactions are protected with strong encryption.</p>
    </div>

    <div class="feature-box">
      <div class="icon"></div>
      <h3>Easy Returns</h3>
      <p>Return items within 30 days for a full refund.</p>
    </div>

  </div>
</section>

<script>
function openSignupModal() {
  document.getElementById('signupModal').classList.add('active');
}
function closeSignupModal() {
  document.getElementById('signupModal').classList.remove('active');
}
document.addEventListener('click', function (event) {
  const modal = document.getElementById('signupModal');
  if (event.target === modal) {
    closeSignupModal();
  }
});
</script>

</body>
</html>