<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logistics Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="sidebar.css">

<style>
/* ===== MAIN CONTENT STYLES ONLY – NO SIDEBAR MODIFICATIONS ===== */
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #fdf2f6;
}

.main-content {
  margin-left: 240px;
  padding: 40px 60px;
  transition: margin-left 0.3s ease;
}

.main-content.full {
  margin-left: 70px;
}

/* metrics & recent deliveries styling */
.card-box {
  background: white;
  border-radius: 18px;
  padding: 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  margin-bottom: 25px;
  transition: transform 0.25s ease, box-shadow 0.3s ease;
}

.card-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 18px 30px -8px rgba(97, 12, 39, 0.15);
}

.center-box {
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
}

.flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

h1 {
  color: #610C27;
  margin-bottom: 5px;
  font-size: 32px;
}

p {
  font-size: 16px;
}

/* metrics grid */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-bottom: 40px;
}

.metric-card {
  background: white;
  border-radius: 18px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: transform 0.25s ease, box-shadow 0.3s ease;
  cursor: default;
}

.metric-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 28px -12px rgba(97, 12, 39, 0.2);
}

.metric-icon {
  width: 50px;
  height: 50px;
  background: #fdf2f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #610C27;
}

.metric-label {
  font-size: 14px;
  color: #6b4a5c;
  margin-bottom: 4px;
}

.metric-value {
  font-size: 28px;
  font-weight: bold;
  color: #1e0a12;
}

/* recent deliveries table - 4 columns */
.delivery-header {
  display: grid;
  grid-template-columns: 1fr 1.4fr 1fr 1.2fr;
  gap: 16px;
  padding: 12px 0;
  border-bottom: 2px solid #f0e0e7;
  margin-top: 20px;
  font-weight: bold;
  font-size: 14px;
  color: #8e6b7c;
  text-transform: uppercase;
}

.delivery-row {
  display: grid;
  grid-template-columns: 1fr 1.4fr 1fr 1.2fr;
  gap: 16px;
  align-items: center;
  padding: 14px 0;
  border-bottom: 1px solid #f3e6ec;
  transition: background 0.15s;
}

.delivery-row:hover {
  background: #fefafc;
  border-radius: 12px;
  margin: 0 -4px;
  padding: 14px 4px;
}

.order-id {
  font-weight: 700;
  color: #2c0f1b;
}

.customer-name {
  font-weight: 500;
  color: #2c3a4b;
}

.delivery-date {
  font-size: 13px;
  color: #6b4a5c;
  background: #faf0f4;
  padding: 4px 12px;
  border-radius: 40px;
  display: inline-block;
  width: fit-content;
}

.status {
  font-size: 13px;
  font-weight: 600;
  padding: 5px 14px;
  border-radius: 100px;
  text-align: center;
  width: fit-content;
}

.status.delivered {
  background: #e6f4ea;
  color: #1e6f3f;
}

.status.out-for-delivery {
  background: #e3f2fd;
  color: #0b5e9e;
}

.status.in-transit {
  background: #fff1e0;
  color: #c45100;
}

.status.pending {
  background: #ffe8ed;
  color: #bc2c4e;
}

/* pagination */
.pagination-wrapper {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
  gap: 12px;
  align-items: center;
}

.page-btn {
  background: #fdf2f6;
  border: none;
  padding: 6px 16px;
  border-radius: 30px;
  font-weight: 600;
  color: #610C27;
  cursor: pointer;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-indicator {
  font-size: 14px;
  background: #fff4f8;
  padding: 4px 12px;
  border-radius: 30px;
}

.view-all {
  font-size: 14px;
  font-weight: 600;
  color: #610C27;
  text-decoration: none;
  background: #fdf2f6;
  padding: 6px 16px;
  border-radius: 40px;
}
</style>
</head>
<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<!-- SIDEBAR – UNCHANGED, EXACTLY AS ORIGINAL -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Logistics</h2>
  </div>

  <a href="logi_dashboard.php" class="<?= $current_page == 'logi_dashboard.php' ? 'active' : '' ?>">
    <i class="fas fa-table-columns"></i><span class="text">Dashboard</span>
  </a>

  <a href="logi_orders.php" class="<?= $current_page == 'logi_orders.php' ? 'active' : '' ?>">
    <i class="fas fa-cart-shopping"></i><span class="text">Orders</span>
  </a>

  <a href="logi_tracking.php" class="<?= $current_page == 'logi_tracking.php' ? 'active' : '' ?>">
    <i class="fas fa-truck-fast"></i><span class="text">Tracking</span>
  </a>

  <a href="logi_reports.php" class="<?= $current_page == 'logi_reports.php' ? 'active' : '' ?>">
    <i class="fas fa-file-lines"></i><span class="text">Reports</span>
  </a>

  <a href="logi_settings.php" class="<?= $current_page == 'logi_settings.php' ? 'active' : '' ?>">
    <i class="fas fa-gear"></i><span class="text">Settings</span>
  </a>

  <a href="#" class="logout">
    <i class="fas fa-right-from-bracket"></i><span class="text">Logout</span>
  </a>
</div>

<!-- MAIN CONTENT (ONLY IMPLEMENTATION, NO DELIVERY VOLUME CARD) -->
<div class="main-content" id="main">
  <div class="center-box">
    <h1>Logistics Overview</h1>
    <p>Delivery Status Monitoring</p>

    <!-- 4 metric cards -->
    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-icon"><i class="fas fa-box-open"></i></div>
        <div class="metric-info">
          <div class="metric-label">Pending Pickup</div>
          <div class="metric-value">45</div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon"><i class="fas fa-truck-ramp-box"></i></div>
        <div class="metric-info">
          <div class="metric-label">In Transit</div>
          <div class="metric-value">128</div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon"><i class="fas fa-person-walking-arrow-right"></i></div>
        <div class="metric-info">
          <div class="metric-label">Out for Delivery</div>
          <div class="metric-value">82</div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon"><i class="fas fa-circle-check"></i></div>
        <div class="metric-info">
          <div class="metric-label">Delivered Today</div>
          <div class="metric-value">215</div>
        </div>
      </div>
    </div>

    <!-- Recent Deliveries (with Customer, Date columns + card hover effect) -->
    <div class="card-box">
      <div class="flex">
        <h3>Recent Deliveries</h3>
      </div>

      <!-- Updated column titles: Order ID, Customer, Date, Status -->
      <div class="delivery-header">
        <span>Order ID</span>
        <span>Customer</span>
        <span>Order Date</span>
        <span>Status</span>
      </div>

      <div id="deliveryRowsContainer"></div>

      <div class="pagination-wrapper">
        <button class="page-btn" id="prevPageBtn" disabled><i class="fas fa-chevron-left"></i> Previous</button>
        <span id="pageIndicator" class="page-indicator">Page 1 of 2</span>
        <button class="page-btn" id="nextPageBtn">Next <i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
</div>

<script>
// ========== ORIGINAL SIDEBAR TOGGLE (UNCHANGED) ==========
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

// ========== UPDATED DELIVERIES DATA: INCLUDES CUSTOMER & DATE ==========
const deliveries = [
  { id: "ORD-9021", customer: "Emma Johnson", date: "Apr 15, 2025", status: "Delivered", statusClass: "delivered" },
  { id: "ORD-8943", customer: "Liam Smith", date: "Apr 15, 2025", status: "Out for Delivery", statusClass: "out-for-delivery" },
  { id: "ORD-8820", customer: "Olivia Brown", date: "Apr 14, 2025", status: "In Transit", statusClass: "in-transit" },
  { id: "ORD-8711", customer: "Noah Jones", date: "Apr 14, 2025", status: "Delivered", statusClass: "delivered" },
  { id: "ORD-8650", customer: "Ava Garcia", date: "Apr 13, 2025", status: "Pending Pickup", statusClass: "pending" },
  { id: "ORD-8592", customer: "Mason Lee", date: "Apr 13, 2025", status: "In Transit", statusClass: "in-transit" },
  { id: "ORD-8437", customer: "Isabella Martinez", date: "Apr 12, 2025", status: "Delivered", statusClass: "delivered" }
];

let currentPage = 1;
const itemsPerPage = 3;
const totalPages = Math.ceil(deliveries.length / itemsPerPage);

const container = document.getElementById("deliveryRowsContainer");
const prevBtn = document.getElementById("prevPageBtn");
const nextBtn = document.getElementById("nextPageBtn");
const pageIndicator = document.getElementById("pageIndicator");

function renderPage() {
  const start = (currentPage - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  const pageItems = deliveries.slice(start, end);
  
  let html = "";
  pageItems.forEach(item => {
    html += `
      <div class="delivery-row">
        <div class="order-id">${item.id}</div>
        <div class="customer-name">${escapeHtml(item.customer)}</div>
        <div><span class="delivery-date">${escapeHtml(item.date)}</span></div>
        <div class="status ${item.statusClass}">${item.status}</div>
      </div>
    `;
  });
  container.innerHTML = html;
  
  prevBtn.disabled = currentPage === 1;
  nextBtn.disabled = currentPage === totalPages;
  pageIndicator.innerText = `Page ${currentPage} of ${totalPages}`;
}

function escapeHtml(str) {
  return str.replace(/[&<>]/g, function(m) {
    if (m === '&') return '&amp;';
    if (m === '<') return '&lt;';
    if (m === '>') return '&gt;';
    return m;
  });
}

prevBtn.addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    renderPage();
  }
});

nextBtn.addEventListener("click", () => {
  if (currentPage < totalPages) {
    currentPage++;
    renderPage();
  }
});

document.getElementById("viewAllBtn")?.addEventListener("click", (e) => {
  e.preventDefault();
  currentPage = 1;
  renderPage();
});

renderPage();
</script>

</body>
</html>