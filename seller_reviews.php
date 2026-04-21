<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Reviews</title>

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

.tab-btn {
  border: none;
  background: none;
  padding: 10px 15px;
  font-weight: 600;
  cursor: pointer;
}

.tab-active {
  border-bottom: 2px solid #6d0f1b;
  color: #6d0f1b;
}

.star {
  color: #f59e0b;
}

.reply-box {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 12px;
  border: 1px solid #eee;
}

.review-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 15px;
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
    <a href="seller_inventory.php"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
    <a href="seller_reviews.php" class="active"><i class="bi bi-star"></i><span class="text">Reviews</span></a>
    <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
    <a href="seller_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

    <a href="#" class="logout">
        <i class="bi bi-box-arrow-right"></i>
        <span class="text">Logout</span>
    </a>
</div>

<!-- MAIN -->
<div class="main-content">

<h2 class="fw-bold">Customer Reviews</h2>
<p class="text-muted">Monitor and respond to customer feedback.</p>

<!-- TOP STATS -->
<div class="row g-3 mb-4">

  <div class="col-md-4">
    <div class="card p-4 text-center">
      <h2 class="fw-bold">4.8</h2>
      <div class="star fs-4">★★★★★</div>
      <small class="text-muted">Average Rating</small>
    </div>
  </div>

  <!-- RATING BREAKDOWN (MANUAL STYLE BARS) -->
  <div class="col-md-8">
    <div class="card p-4">

      <h6 class="fw-bold mb-3">Rating Breakdown</h6>

      <div class="d-flex align-items-center mb-2">
        <div style="width:50px;">5★</div>
        <div class="progress flex-grow-1 me-2" style="height:8px;">
          <div class="progress-bar bg-success" style="width:75%"></div>
        </div>
        <small>930</small>
      </div>

      <div class="d-flex align-items-center mb-2">
        <div style="width:50px;">4★</div>
        <div class="progress flex-grow-1 me-2" style="height:8px;">
          <div class="progress-bar bg-success" style="width:15%"></div>
        </div>
        <small>186</small>
      </div>

      <div class="d-flex align-items-center mb-2">
        <div style="width:50px;">3★</div>
        <div class="progress flex-grow-1 me-2" style="height:8px;">
          <div class="progress-bar bg-warning" style="width:5%"></div>
        </div>
        <small>62</small>
      </div>

      <div class="d-flex align-items-center mb-2">
        <div style="width:50px;">2★</div>
        <div class="progress flex-grow-1 me-2" style="height:8px;">
          <div class="progress-bar bg-danger" style="width:3%"></div>
        </div>
        <small>37</small>
      </div>

      <div class="d-flex align-items-center">
        <div style="width:50px;">1★</div>
        <div class="progress flex-grow-1 me-2" style="height:8px;">
          <div class="progress-bar bg-danger" style="width:2%"></div>
        </div>
        <small>25</small>
      </div>

    </div>
  </div>

</div>

<!-- TABS -->
<div class="mb-3">
  <button class="tab-btn tab-active" onclick="showTab('all')">All Reviews</button>
  <button class="tab-btn" onclick="showTab('photos')">With Photos</button>
  <button class="tab-btn" onclick="showTab('reply')">Needs Reply</button>
</div>

<!-- SEARCH -->
<div class="card p-3 mb-3">
  <input class="form-control" placeholder="Search reviews...">
</div>

<!-- REVIEWS -->
<div id="all">

  <div class="review-card">
    <strong>Alice Smith</strong><br>
    <small class="text-muted">Oct 24, 2023</small>

    <div class="star">★★★★★</div>

    <p>Amazing product quality and very comfortable.</p>

    <button class="btn btn-outline-secondary btn-sm" onclick="toggleReply(this)">Reply</button>

    <div class="reply-area mt-3 d-none">
      <textarea class="form-control mb-2" placeholder="Write reply..."></textarea>
      <button class="btn btn-brand btn-sm">Post Reply</button>
    </div>
  </div>

  <div class="review-card">
    <strong>Bob Johnson</strong><br>
    <small class="text-muted">Oct 22, 2023</small>

    <div class="star">★★★★☆</div>

    <p>Good product but shipping was slow.</p>

    <div class="reply-box mt-3">
      <strong>Reply:</strong><br>
      Thanks for your feedback! We’re improving logistics.
    </div>
  </div>

  <div class="review-card">
    <strong>Charlie Brown</strong><br>
    <small class="text-muted">Oct 20, 2023</small>

    <div class="star">★★☆☆☆</div>

    <p>Item arrived damaged.</p>

    <button class="btn btn-outline-secondary btn-sm" onclick="toggleReply(this)">Reply</button>

    <div class="reply-area mt-3 d-none">
      <textarea class="form-control mb-2" placeholder="Write reply..."></textarea>
      <button class="btn btn-brand btn-sm">Post Reply</button>
    </div>
  </div>

</div>

<!-- JS -->
<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

function showTab(tab) {
  alert("Filtering: " + tab);
  document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("tab-active"));
  event.target.classList.add("tab-active");
}

function toggleReply(btn) {
  btn.nextElementSibling.classList.toggle("d-none");
}
</script>

</body>
</html>