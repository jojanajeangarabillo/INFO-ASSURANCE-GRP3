<?php
session_start();
require_once 'admin/db.connect.php';
require_once 'config_settings.php';

// Initialize settings
$settings = SiteSettings::getInstance($conn);

// Get filters (only search and category remain)
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? 'All');

// Get dynamic categories
$categories = $settings->getCategories();

// Build products query (simplified - removed size, color, price filters)
$sql = "SELECT
          p.product_id,
          p.name,
          p.description,
          p.category_gender,
          p.price,
          (SELECT pv2.image_path 
           FROM product_variant pv2 
           WHERE pv2.product_id = p.product_id 
           AND pv2.image_path IS NOT NULL 
           LIMIT 1) AS image_path
        FROM product p
        WHERE p.status = 'active'";

$types = "";
$params = [];

// Category filter (only if not 'All' and category exists in list)
if ($category !== 'All' && in_array($category, $categories)) {
  $sql .= " AND p.category_gender = ?";
  $types .= "s";
  $params[] = $category;
}

// Search filter
if ($search !== '') {
  $sql .= " AND p.name LIKE ?";
  $types .= "s";
  $params[] = "%" . $search . "%";
}

$sql .= " ORDER BY p.created_at DESC LIMIT 24";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get dynamic content
$siteName = $settings->get('site_name', 'J3RS Shop Co.');
$heroTitle = $settings->get('hero_title', 'Curated essentials for <br> <span>modern living.</span>');
$heroSubtitle = $settings->get('hero_subtitle', 'Discover our handpicked selection of premium products designed to elevate your everyday experience. Quality meets aesthetics.');
$heroButtonText = $settings->get('hero_button_text', 'Shop Collection →');
$heroBgImage = $settings->get('hero_background_image', 'labg.jpg');
$siteLogo = $settings->get('site_logo', 'JERS-LOGO.png');
$aboutTitle = $settings->get('about_title', 'About J3RS Shop Co.');
$aboutContent = $settings->get('about_content', '');
$contactEmail = $settings->get('contact_email', 'support@j3rsshopco.com');
$contactPhone = $settings->get('contact_phone', '+63 912 345 6789');
$contactLocation = $settings->get('contact_location', 'Philippines, Pasig City');
$contactPhoneHours = $settings->get('contact_phone_hours', 'Mon-Sat, 9AM - 6PM');
$contactEmailResponse = $settings->get('contact_email_response', 'We\'ll respond within 24 hours');
$facebookUrl = $settings->get('facebook_url', '#');
$instagramUrl = $settings->get('instagram_url', '#');
$twitterUrl = $settings->get('twitter_url', '#');
$tiktokUrl = $settings->get('tiktok_url', '#');
$features = $settings->getFeatures();
$whyChooseUs = $settings->getWhyChooseUs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($siteName); ?> - Stylish & Affordable Fashion</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  background: #fdf2f6;
  scroll-behavior: smooth;
}

header {
  position: sticky;
  top: 0;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid #eee;
  padding: 15px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 1000;
  flex-wrap: wrap;
  gap: 15px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.logo-img {
  width: 50px;
  height: auto;
}

.logo-text {
  font-size: 20px;
  font-weight: 700;
  color: #610C27;
  text-decoration: none;
}

nav {
  display: flex;
  gap: 25px;
  flex-wrap: wrap;
}

nav a {
  text-decoration: none;
  color: #333;
  font-size: 14px;
  font-weight: 500;
  transition: color 0.3s;
}

nav a:hover {
  color: #a61b4a;
}

.buttons {
  display: flex;
  gap: 10px;
}

.buttons button {
  padding: 8px 20px;
  border: none;
  cursor: pointer;
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.3s;
}

.login {
  background: transparent;
  color: #333;
  border: 1px solid #ddd;
}

.login:hover {
  background: #f0f0f0;
}

.signup {
  background: #a61b4a;
  color: white;
}

.signup:hover {
  background: #610C27;
  transform: translateY(-2px);
}

.hero {
  position: relative;
  height: 85vh;
  min-height: 500px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 20px;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.35;
  z-index: 0;
}

.hero-content {
  position: relative;
  max-width: 800px;
  z-index: 1;
  animation: fadeInUp 1s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.hero h1 {
  font-size: 56px;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 20px;
  line-height: 1.2;
}

.hero h1 span {
  color: #a61b4a;
}

.hero p {
  font-size: 18px;
  color: #444;
  margin-bottom: 30px;
  line-height: 1.6;
}

.hero button {
  padding: 15px 40px;
  background: #a61b4a;
  color: white;
  border: none;
  cursor: pointer;
  border-radius: 40px;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s;
}

.hero button:hover {
  background: #610C27;
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(97, 12, 39, 0.3);
}

.category-filters {
  background: white;
  padding: 20px 0;
  border-bottom: 1px solid #eee;
}

.category-btn {
  padding: 10px 25px;
  border-radius: 30px;
  font-weight: 500;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-block;
}

.category-btn.active {
  background: #a61b4a;
  color: white;
  border-color: #a61b4a;
}

.category-btn:not(.active) {
  background: transparent;
  color: #666;
  border: 1px solid #ddd;
}

.category-btn:not(.active):hover {
  background: #fdf2f6;
  color: #a61b4a;
  border-color: #a61b4a;
}

.products-section {
  padding: 60px 0;
  background: #f8f9fa;
}

.section-title {
  font-size: 32px;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 10px;
}

.section-subtitle {
  color: #666;
  margin-bottom: 40px;
}

.product-card {
  border: none;
  border-radius: 16px;
  background: white;
  overflow: hidden;
  transition: all 0.3s;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.product-image-wrapper {
  position: relative;
  overflow: hidden;
  height: 300px;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s;
}

.product-card:hover .product-image {
  transform: scale(1.05);
}

.product-category {
  position: absolute;
  top: 12px;
  left: 12px;
  background: rgba(97, 12, 39, 0.9);
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 500;
}

.product-info {
  padding: 20px;
}

.product-title {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #1a1a1a;
}

.product-description {
  font-size: 13px;
  color: #777;
  margin-bottom: 12px;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-price {
  font-size: 22px;
  font-weight: 700;
  color: #610C27;
  margin-bottom: 15px;
}

.product-link {
  display: inline-block;
  text-decoration: none;
  background: #a61b4a;
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s;
  text-align: center;
}

.product-link:hover {
  background: #610C27;
  color: white;
}

.features {
  padding: 80px 40px;
  background: white;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
}

.feature-box {
  text-align: center;
  padding: 30px;
  transition: all 0.3s;
}

.feature-box:hover {
  transform: translateY(-5px);
}

.feature-icon {
  width: 80px;
  height: 80px;
  background: #fdf2f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 36px;
}

.feature-box h3 {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 12px;
  color: #1a1a1a;
}

.feature-box p {
  color: #666;
  line-height: 1.5;
}

.about-section {
  padding: 80px 40px;
  background: #fdf2f6;
}

.about-content {
  max-width: 1200px;
  margin: 0 auto;
}

.about-title {
  text-align: center;
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 40px;
  color: #1a1a1a;
}

.about-text {
  text-align: center;
  max-width: 800px;
  margin: 0 auto 50px;
  color: #555;
  line-height: 1.8;
}

.about-text p {
  margin-bottom: 20px;
}

.why-choose-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 30px;
  margin-top: 40px;
}

.why-item {
  text-align: center;
  padding: 20px;
}

.why-icon {
  font-size: 48px;
  margin-bottom: 15px;
}

.why-item h4 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #1a1a1a;
}

.why-item p {
  color: #666;
  font-size: 14px;
}

.contact-section {
  padding: 80px 40px;
  background: white;
}

.contact-title {
  text-align: center;
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 40px;
  color: #1a1a1a;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 40px;
  max-width: 1000px;
  margin: 0 auto;
}

.contact-card {
  background: #fdf2f6;
  padding: 30px;
  border-radius: 16px;
  text-align: center;
  transition: all 0.3s;
}

.contact-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.contact-icon {
  font-size: 40px;
  margin-bottom: 20px;
}

.contact-card h3 {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 15px;
  color: #1a1a1a;
}

.contact-card p {
  color: #555;
  margin-bottom: 5px;
}

.social-links {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 40px;
}

.social-link {
  width: 50px;
  height: 50px;
  background: #fdf2f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #a61b4a;
  transition: all 0.3s;
  text-decoration: none;
}

.social-link:hover {
  background: #a61b4a;
  color: white;
  transform: translateY(-3px);
}

.footer {
  background: #1a1a1a;
  color: #999;
  padding: 40px 40px 20px;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 40px;
  margin-bottom: 40px;
}

.footer-about h3 {
  color: white;
  font-size: 20px;
  margin-bottom: 15px;
}

.footer-about p {
  line-height: 1.6;
}

.footer-links h4, .footer-contact h4 {
  color: white;
  font-size: 18px;
  margin-bottom: 15px;
}

.footer-links a {
  display: block;
  color: #999;
  text-decoration: none;
  margin-bottom: 10px;
  transition: color 0.3s;
}

.footer-links a:hover {
  color: #a61b4a;
}

.footer-bottom {
  text-align: center;
  padding-top: 20px;
  border-top: 1px solid #333;
  font-size: 14px;
}

.signup-modal-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 1100;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.signup-modal-backdrop.active { 
  display: flex; 
}

.signup-modal-box {
  width: 100%;
  max-width: 420px;
  background: #fff;
  border-radius: 20px;
  padding: 32px;
  animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.signup-option-btn {
  width: 100%;
  border: none;
  border-radius: 10px;
  padding: 14px;
  font-weight: 600;
  transition: all 0.3s;
}

.signup-option-btn.customer {
  background: #fdf2f6;
  color: #610C27;
}

.signup-option-btn.seller {
  background: #a61b4a;
  color: #fff;
}

.signup-option-btn:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}

.search-container {
  display: flex;
  justify-content: center;
  margin-bottom: 40px;
}

.search-input {
  border: 2px solid #e8d7df;
  border-radius: 40px;
  padding: 12px 25px;
  width: 400px;
  font-size: 16px;
}

.search-input:focus {
  outline: none;
  border-color: #a61b4a;
}

@media (max-width: 768px) {
  header {
    flex-direction: column;
    padding: 15px 20px;
  }
  
  .hero h1 {
    font-size: 32px;
  }
  
  .hero p {
    font-size: 14px;
  }
  
  .features, .about-section, .contact-section {
    padding: 60px 20px;
  }
  
  .search-input {
    width: 100%;
  }
}
</style>

</head>
<body>

<header>
  <a href="#home" class="logo">
    <img src="<?php echo htmlspecialchars($siteLogo); ?>" class="logo-img" alt="<?php echo htmlspecialchars($siteName); ?>" onerror="this.src='https://via.placeholder.com/50x50?text=Logo'">
    <span class="logo-text"><?php echo htmlspecialchars($siteName); ?></span>
  </a>

  <nav>
    <a href="#home">Home</a>
    <a href="#products">Products</a>
    <a href="#about">About Us</a>
    <a href="#contact">Contact</a>
  </nav>

  <div class="buttons">
    <button class="login" onclick="window.location.href='login.php'">Log in</button>
    <button class="signup" onclick="openSignupModal()">Sign up</button>
  </div>
</header>

<section class="hero" id="home">
  <img src="<?php echo htmlspecialchars($heroBgImage); ?>" class="hero-bg" alt="Hero background" onerror="this.src='https://via.placeholder.com/1920x1080?text=Hero+Banner'">
  <div class="hero-content">
    <h1><?php echo $heroTitle; ?></h1>
    <p><?php echo htmlspecialchars($heroSubtitle); ?></p>
    <button onclick="window.location.href='#products'"><?php echo htmlspecialchars($heroButtonText); ?></button>
  </div>
</section>

<div class="category-filters" id="categories">
  <div class="container">
    <div class="d-flex flex-wrap gap-2 justify-content-center">
      <a href="?category=All<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
         class="category-btn <?php echo $category == 'All' ? 'active' : ''; ?>">
        All
      </a>
      <?php foreach ($categories as $cat): ?>
        <?php if ($cat !== 'All'): ?>
          <a href="?category=<?php echo urlencode($cat); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
             class="category-btn <?php echo $category == $cat ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($cat); ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="signup-modal-backdrop" id="signupModal">
  <div class="signup-modal-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">Sign up as</h5>
      <button type="button" class="btn-close" aria-label="Close" onclick="closeSignupModal()"></button>
    </div>
    <p class="text-muted mb-3">Choose how you want to create your account.</p>
    <div class="d-grid gap-3">
      <button class="signup-option-btn customer" onclick="window.location.href='register.php'">Customer</button>
      <button class="signup-option-btn seller" onclick="window.location.href='seller_signup.php'">Seller</button>
    </div>
  </div>
</div>

<section class="products-section" id="products">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title">Product Collection</h2>
      <p class="section-subtitle"><?php echo count($products); ?> products found</p>
      
      <!-- Search Form -->
      <div class="search-container">
        <form method="GET" class="d-flex justify-content-center">
          <?php if ($category !== 'All'): ?>
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
          <?php endif; ?>
          <input class="search-input" type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products...">
          <button type="submit" style="display: none;">Search</button>
          <?php if ($search !== ''): ?>
            <a href="?<?php echo $category !== 'All' ? 'category=' . urlencode($category) : ''; ?>" class="btn btn-outline-secondary ms-2" style="border-radius: 40px;">Clear</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
    
    <div class="row g-4">
      <?php if (empty($products)): ?>
        <div class="col-12">
          <div class="text-center py-5">
            <i class="fas fa-search" style="font-size: 64px; color: #ccc;"></i>
            <h3 class="mt-3">No products found</h3>
            <p>Try adjusting your search or category filter.</p>
            <a href="?" class="btn btn-primary">View All Products</a>
          </div>
        </div>
      <?php endif; ?>
      
      <?php foreach ($products as $product): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <div class="product-card h-100 d-flex flex-column">
            <div class="product-image-wrapper">
              <?php 
              $imagePath = !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : 'https://via.placeholder.com/400x500?text=No+Image';
              ?>
              <img class="product-image" src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='https://via.placeholder.com/400x500?text=Product+Image'">
              <span class="product-category"><?php echo htmlspecialchars($product['category_gender']); ?></span>
            </div>
            <div class="product-info d-flex flex-column flex-grow-1">
              <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
              <p class="product-description"><?php echo htmlspecialchars(substr($product['description'] ?? 'No description available', 0, 80)); ?>...</p>
              <div class="product-price">₱<?php echo number_format((float) $product['price'], 2); ?></div>
              <a class="product-link mt-auto" href="product_details.php?product_id=<?php echo (int) $product['product_id']; ?>">
                View Details <i class="fas fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="features">
  <div class="feature-grid">
    <?php foreach ($features as $feature): ?>
      <div class="feature-box">
        <div class="feature-icon"><?php echo htmlspecialchars($feature['icon']); ?></div>
        <h3><?php echo htmlspecialchars($feature['title']); ?></h3>
        <p><?php echo htmlspecialchars($feature['description']); ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="about-section" id="about">
  <div class="about-content">
    <h2 class="about-title"><?php echo htmlspecialchars($aboutTitle); ?></h2>
    <div class="about-text">
      <?php echo $aboutContent; ?>
    </div>
    
    <div class="why-choose-grid">
      <?php foreach ($whyChooseUs as $reason): ?>
        <div class="why-item">
          <div class="why-icon"><?php echo htmlspecialchars($reason['icon']); ?></div>
          <h4><?php echo htmlspecialchars($reason['title']); ?></h4>
          <p><?php echo htmlspecialchars($reason['description']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="contact-section" id="contact">
  <div class="container">
    <h2 class="contact-title">Get In Touch</h2>
    <div class="contact-grid">
      <div class="contact-card">
        <div class="contact-icon">📧</div>
        <h3>Email Us</h3>
        <p><?php echo htmlspecialchars($contactEmail); ?></p>
        <p><?php echo htmlspecialchars($contactEmailResponse); ?></p>
      </div>
      <div class="contact-card">
        <div class="contact-icon">📞</div>
        <h3>Call Us</h3>
        <p><?php echo htmlspecialchars($contactPhone); ?></p>
        <p><?php echo htmlspecialchars($contactPhoneHours); ?></p>
      </div>
      <div class="contact-card">
        <div class="contact-icon">📍</div>
        <h3>Visit Us</h3>
        <p><?php echo htmlspecialchars($contactLocation); ?></p>
        <p>By appointment only</p>
      </div>
    </div>
    
    <div class="social-links">
      <a href="<?php echo htmlspecialchars($facebookUrl); ?>" class="social-link" target="_blank"><i class="fab fa-facebook-f"></i></a>
      <a href="<?php echo htmlspecialchars($instagramUrl); ?>" class="social-link" target="_blank"><i class="fab fa-instagram"></i></a>
      <a href="<?php echo htmlspecialchars($twitterUrl); ?>" class="social-link" target="_blank"><i class="fab fa-twitter"></i></a>
      <a href="<?php echo htmlspecialchars($tiktokUrl); ?>" class="social-link" target="_blank"><i class="fab fa-tiktok"></i></a>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="footer-content">
    <div class="footer-about">
      <h3><?php echo htmlspecialchars($siteName); ?></h3>
      <p>Your go-to destination for stylish, trendy, and affordable fashion. Express your style without breaking the bank.</p>
    </div>
    <div class="footer-links">
      <h4>Quick Links</h4>
      <a href="#home">Home</a>
      <a href="#products">Products</a>
      <a href="#about">About Us</a>
      <a href="#contact">Contact</a>
    </div>
    <div class="footer-contact">
      <h4>Contact Info</h4>
      <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contactEmail); ?></p>
      <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contactPhone); ?></p>
      <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($contactLocation); ?></p>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?>. All rights reserved. | Designed with <i class="fas fa-heart" style="color: #a61b4a;"></i> for fashion lovers</p>
  </div>
</footer>

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