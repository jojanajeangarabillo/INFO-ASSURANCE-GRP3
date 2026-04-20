<?php
require_once 'auth.php';
require_roles([2, 4]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Orders</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

  /* notification bell fixed position */
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

  /* ========= ORDER HISTORY CUSTOM STYLES ========= */
  .order-card {
    border: none;
    border-radius: 24px;
    background: #ffffff;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.02), 0 2px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
  }

  .order-card .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding: 1.25rem 1.5rem;
    font-weight: 600;
  }

  .search-wrapper {
    max-width: 320px;
  }

  .search-wrapper .input-group {
    border-radius: 60px;
    background: #f8f9fc;
    border: 1px solid #e9ecef;
    transition: all 0.2s;
  }
  .search-wrapper .input-group:focus-within {
    border-color: #c7a17a;
    box-shadow: 0 0 0 2px rgba(199, 161, 122, 0.2);
  }
  .search-wrapper input {
    background: transparent;
    border: none;
    font-size: 0.9rem;
    padding: 0.6rem 1rem;
  }
  .search-wrapper .input-group-text {
    background: transparent;
    border: none;
    color: #9aa6b5;
  }

  .status-filter-group {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
  }
  .filter-chip {
    background: #f1f3f5;
    padding: 6px 18px;
    border-radius: 40px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    color: #2c3e4e;
    border: 1px solid transparent;
  }
  .filter-chip i {
    margin-right: 6px;
    font-size: 0.85rem;
  }
  .filter-chip.active {
    background: #6e0f25;
    color: white;
    box-shadow: 0 4px 10px rgba(110, 15, 37, 0.2);
    border-color: #6e0f25;
  }
  .filter-chip.active i {
    color: white;
  }
  .filter-chip:not(.active):hover {
    background: #e9ecef;
    transform: translateY(-1px);
  }
  .btn-clear-search {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 40px;
    padding: 6px 16px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #6c757d;
    transition: all 0.2s;
  }
  .btn-clear-search:hover {
    background: #f8f9fa;
    color: #6e0f25;
    border-color: #c7a17a;
  }

  .order-table {
    margin-bottom: 0;
  }
  .order-table thead th {
    background: #fafbfe;
    border-bottom: 1px solid #edf2f7;
    color: #1f2a3e;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem 1rem;
  }
  .order-table td {
    padding: 1rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f0f2f5;
    font-weight: 500;
    color: #2c3e4e;
  }
  .order-table tr:last-child td {
    border-bottom: none;
  }
  .order-table tr:hover td {
    background-color: #fefaf7;
  }

  .badge-status {
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 60px;
    font-size: 0.75rem;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    width: fit-content;
  }
  .badge-delivered {
    background: #e6f4ea;
    color: #2e7d32;
  }
  .badge-processing {
    background: #fff4e5;
    color: #b85c00;
  }
  .order-id {
    font-family: 'Inter', monospace;
    font-weight: 600;
    letter-spacing: -0.2px;
    color: #1e293b;
  }

  .empty-row-message td {
    text-align: center;
    padding: 3rem !important;
    color: #8a9cb0;
    font-weight: 500;
  }

  .total-count-badge {
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 40px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #334155;
  }

  @media (max-width: 768px) {
    .main-content {
      margin-left: 0;
      padding: 80px 20px 20px 20px;
    }
    .sidebar.collapsed ~ .main-content {
      margin-left: 0;
    }
    .status-filter-group {
      justify-content: flex-start;
      margin-top: 12px;
    }
    .order-card .card-header {
      flex-direction: column;
      align-items: stretch !important;
      gap: 16px;
    }
    .search-wrapper {
      max-width: 100%;
    }
  }
</style>
</head>
<body>

<!-- SIDEBAR-->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="logo-text">Customer</div>
  </div>

  <a href="customer_profile.php"><i class="bi bi-person-circle"></i><span class="text">Profile</span></a>
  <a href="customer_home.php"><i class="bi bi-house"></i><span class="text">Home</span></a>
  <a href="customer_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
  <a href="customer_cart.php"><i class="bi bi-cart-check"></i><span class="text">Cart</span></a>
  <a href="customer_wishlist.php"><i class="bi bi-bookmark-heart"></i><span class="text">Wishlist</span></a>
  <a href="customer_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat & Support</span></a>

  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>
</div>

<!-- NOTIFICATION BELL (fixed) -->
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
            <small class="text-muted">Your Sony WH-1000XM4 is on the way</small>
            <div class="text-muted small">2 hours ago</div>
          </div>
        </div>
      </div>
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-tag-fill text-success mt-1"></i>
          <div>
            <div class="fw-bold">Flash Sale: Mechanical Keyboard</div>
            <small class="text-muted">Up to 20% off for limited time</small>
            <div class="text-muted small">Yesterday</div>
          </div>
        </div>
      </div>
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-cup-hot text-warning mt-1"></i>
          <div>
            <div class="fw-bold">New ceramic collection</div>
            <small class="text-muted">Handcrafted mugs just dropped</small>
            <div class="text-muted small">2 days ago</div>
          </div>
        </div>
      </div>
      <div class="notif-item">
        <div class="d-flex gap-2">
          <i class="bi bi-check-circle text-secondary mt-1"></i>
          <div>
            <div class="fw-bold">Welcome to ShopHub!</div>
            <small class="text-muted">Complete your profile for perks</small>
            <div class="text-muted small">5 days ago</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ========= MAIN CONTENT -->
<div class="main-content">
  <div class="container-fluid px-0">
    <!-- Order History Card -->
    <div class="card order-card">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
          <h4 class="mb-1 fw-bold" style="color: #1e2a3a;">Order History</h4>
          <p class="text-muted mb-0 small">View and track your recent orders.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <div class="total-count-badge" id="orderCountBadge">
            <i class="bi bi-receipt"></i> <span id="visibleOrderCount">2</span> orders
          </div>
        </div>
      </div>
      
      <div class="card-body p-4">
        <!-- Search & Filters row -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
          <div class="search-wrapper flex-grow-1" style="max-width: 320px;">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" class="form-control" id="searchOrderInput" placeholder="Search orders by ID...">
              <button class="btn btn-outline-secondary border-0" type="button" id="clearSearchBtn" style="background: transparent;">
                <i class="bi bi-x-circle-fill" style="color: #adb5bd;"></i>
              </button>
            </div>
          </div>
          
          <div class="status-filter-group">
            <div class="filter-chip active" data-filter="all">
              <i class="bi bi-grid-3x3-gap-fill"></i> All
            </div>
            <div class="filter-chip" data-filter="Delivered">
              <i class="bi bi-check2-circle"></i> Delivered
            </div>
            <div class="filter-chip" data-filter="Processing">
              <i class="bi bi-arrow-repeat"></i> Processing
            </div>
            <button id="resetFiltersBtn" class="btn-clear-search ms-1">
              <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
          </div>
        </div>

        <!-- Orders Table -->
        <div class="table-responsive">
          <table class="table order-table align-middle">
            <thead>
              <tr>
                <th>ORDER ID</th>
                <th>DATE</th>
                <th>TOTAL</th>
                <th>STATUS</th>
              </tr>
            </thead>
            <tbody id="ordersTableBody">
              <tr class="order-row" data-order-id="ORD-9021" data-status="Delivered">
                <td class="order-id fw-semibold">ORD-9021</td>
                <td>Oct 24, 2023</td>
                <td class="fw-semibold">₱21,899</td>
                <td><span class="badge-status badge-delivered"><i class="bi bi-check-circle-fill me-1"></i> Delivered</span></td>
              </tr>
              <tr class="order-row" data-order-id="ORD-8943" data-status="Processing">
                <td class="order-id fw-semibold">ORD-8943</td>
                <td>Oct 12, 2023</td>
                <td class="fw-semibold">₱4,500</td>
                <td><span class="badge-status badge-processing"><i class="bi bi-clock-history me-1"></i> Processing</span></td>
              </tr>
    
              <tr class="order-row" data-order-id="ORD-9105" data-status="Delivered">
                <td class="order-id fw-semibold">ORD-9105</td>
                <td>Nov 02, 2023</td>
                <td class="fw-semibold">₱8,750</td>
                <td><span class="badge-status badge-delivered"><i class="bi bi-check-circle-fill me-1"></i> Delivered</span></td>
              </tr>
              <tr class="order-row" data-order-id="ORD-9287" data-status="Processing">
                <td class="order-id fw-semibold">ORD-9287</td>
                <td>Nov 05, 2023</td>
                <td class="fw-semibold">₱12,300</td>
                <td><span class="badge-status badge-processing"><i class="bi bi-clock-history me-1"></i> Processing</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ========== SIDEBAR TOGGLE ==========
function toggleSidebar(){
  document.getElementById("sidebar").classList.toggle("collapsed");
}

// ========== NOTIFICATION MARK-ALL-READ ==========
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

// ========== ORDER HISTORY FILTER & SEARCH LOGIC ==========
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchOrderInput');
  const clearSearchBtn = document.getElementById('clearSearchBtn');
  const resetBtn = document.getElementById('resetFiltersBtn');
  const filterChips = document.querySelectorAll('.filter-chip');
  const ordersTableBody = document.getElementById('ordersTableBody');
  
  let currentStatusFilter = 'all';   
  let currentSearchTerm = '';
  
  function getAllOrderRows() {
    return Array.from(document.querySelectorAll('#ordersTableBody .order-row'));
  }
  
  function removeEmptyPlaceholder() {
    const existingEmpty = document.querySelector('#ordersTableBody .empty-row-message');
    if(existingEmpty) existingEmpty.remove();
  }
  
  function showEmptyState() {
    removeEmptyPlaceholder();
    const emptyRow = document.createElement('tr');
    emptyRow.className = 'empty-row-message';
    emptyRow.innerHTML = `<td colspan="4" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="mt-2 mb-0 fw-medium text-muted">No orders found</p>
                            <small class="text-secondary">Try adjusting your search or filters</small>
                          </td>`;
    ordersTableBody.appendChild(emptyRow);
  }
  
  // update visible order count badge
  function updateOrderCount(visibleCount) {
    const countSpan = document.getElementById('visibleOrderCount');
    if(countSpan) countSpan.innerText = visibleCount;
  }
  
  // core filter function: combines status filter + search by Order ID
  function applyFilters() {
    const rows = getAllOrderRows();
    removeEmptyPlaceholder();
    
    let visibleRowsCount = 0;
    const searchTermLower = currentSearchTerm.trim().toLowerCase();
    
    rows.forEach(row => {
      const orderId = row.getAttribute('data-order-id') || '';
      const orderStatus = row.getAttribute('data-status') || '';
      
      // status condition
      let statusMatch = false;
      if (currentStatusFilter === 'all') {
        statusMatch = true;
      } else {
        statusMatch = (orderStatus.toLowerCase() === currentStatusFilter.toLowerCase());
      }
      
      // search condition (match order ID)
      let searchMatch = false;
      if (searchTermLower === '') {
        searchMatch = true;
      } else {
        searchMatch = orderId.toLowerCase().includes(searchTermLower);
      }
      
      if (statusMatch && searchMatch) {
        row.style.display = '';
        visibleRowsCount++;
      } else {
        row.style.display = 'none';
      }
    });
    
    // if no rows visible, show empty state
    if (visibleRowsCount === 0) {
      showEmptyState();
    }
    
    updateOrderCount(visibleRowsCount);
  }
  
  // event: search input typing
  function onSearchInput() {
    currentSearchTerm = searchInput.value;
    applyFilters();
  }
  
  // clear search input
  function clearSearch() {
    searchInput.value = '';
    currentSearchTerm = '';
    applyFilters();
  }
  
  // set active filter chip UI
  function setActiveFilterChip(filterValue) {
    filterChips.forEach(chip => {
      const chipFilter = chip.getAttribute('data-filter');
      if (chipFilter === filterValue) {
        chip.classList.add('active');
      } else {
        chip.classList.remove('active');
      }
    });
  }
  
  // change status filter
  function setStatusFilter(filterValue) {
    currentStatusFilter = filterValue;
    setActiveFilterChip(filterValue);
    applyFilters();
  }
  
  // reset everything: status = all, search empty
  function resetAllFilters() {
    currentStatusFilter = 'all';
    currentSearchTerm = '';
    searchInput.value = '';
    setActiveFilterChip('all');
    applyFilters();
  }
  
  // Attach event listeners
  if (searchInput) {
    searchInput.addEventListener('input', onSearchInput);
  }
  if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', clearSearch);
  }
  if (resetBtn) {
    resetBtn.addEventListener('click', resetAllFilters);
  }
  
  // filter chip click handlers
  filterChips.forEach(chip => {
    chip.addEventListener('click', function(e) {
      const filterValue = this.getAttribute('data-filter');
      if (filterValue === 'all') {
        setStatusFilter('all');
      } else if (filterValue === 'Delivered') {
        setStatusFilter('Delivered');
      } else if (filterValue === 'Processing') {
        setStatusFilter('Processing');
      }
    });
  });
  
  applyFilters();
  
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>