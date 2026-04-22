<?php
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

    <a href="#" class="logout">
      <i class="bi bi-box-arrow-right"></i>
      <span class="text">Logout</span>
  </a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

<div class="container-fluid">

<h2 class="fw-bold">Inventory Management</h2>
<p class="text-muted">Manage stock levels, restock products, and track inventory history.</p>

<!-- HEADER ACTIONS -->
<div class="d-flex justify-content-between mb-4">
  <input class="form-control w-50" placeholder="Search inventory..." id="inventorySearch">
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
            <tr>
              <td>Oversized Hoodie</td>
              <td>CLOTH-001</td>
              <td>40</td>
              <td><span class="badge-soft">In Stock</span></td>
              <td><button class="btn btn-sm btn-outline-primary restock-btn" data-product="Oversized Hoodie">Restock</button></td>
            </tr>
            <tr>
              <td>Streetwear Shirt</td>
              <td>CLOTH-002</td>
              <td>12</td>
              <td><span class="badge-red">Low Stock</span></td>
              <td><button class="btn btn-sm btn-outline-primary restock-btn" data-product="Streetwear Shirt">Restock</button></td>
            </tr>
            <tr>
              <td>Baggy Jeans</td>
              <td>CLOTH-003</td>
              <td>0</td>
              <td><span class="badge-red">Out of Stock</span></td>
              <td><button class="btn btn-sm btn-outline-primary restock-btn" data-product="Baggy Jeans">Restock</button></td>
            </tr>
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
            <tr>
              <td>RST-001</td>
              <td>Oversized Hoodie</td>
              <td>25</td>
              <td>Oct 20, 2023</td>
              <td>Admin</td>
              <td>Supplier delivery</td>
            </tr>
            <tr>
              <td>RST-002</td>
              <td>Streetwear Shirt</td>
              <td>30</td>
              <td>Oct 18, 2023</td>
              <td>Manager</td>
              <td>Batch refill</td>
            </tr>
            <tr>
              <td>RST-003</td>
              <td>Baggy Jeans</td>
              <td>50</td>
              <td>Oct 15, 2023</td>
              <td>Admin</td>
              <td>Bulk purchase</td>
            </tr>
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
        <h5 class="modal-title">Restock Product: <span id="restockProductName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="restockForm">
          <div class="mb-3">
            <label class="form-label">Restock Quantity</label>
            <input type="number" class="form-control" required min="1">
          </div>
          <div class="mb-3">
            <label class="form-label">Notes (Optional)</label>
            <textarea class="form-control" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-brand">Confirm Restock</button>
      </div>
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
  const restockModal = new bootstrap.Modal(document.getElementById('restockModal'));
  const restockButtons = document.querySelectorAll('.restock-btn');
  const restockProductName = document.getElementById('restockProductName');

  restockButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      restockProductName.textContent = this.getAttribute('data-product');
      restockModal.show();
    });
  });

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