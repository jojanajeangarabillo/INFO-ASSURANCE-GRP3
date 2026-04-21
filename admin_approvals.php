
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Seller Approvals</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="sidebar.css">

<style>
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #fdf2f6;
}

.container {
  margin-left: 240px;
  padding: 20px;
  transition: margin-left 0.3s ease;
}

.container.full {
  margin-left: 70px;
}

h1 {
  color: #610C27;
  font-size: 32px;
  margin-bottom: 5px;
}

.card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  background: #f9dbe5;
  color: #610C27;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn {
  padding: 6px 12px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-size: 12px;
  font-weight: bold;
  transition: 0.2s;
  margin-right: 5px;
}

.btn-view {
  background: #EFECE9;
  color: #333;
}

.btn-approve {
  background: #610C27;
  color: white;
}

.btn-reject {
  background: #fee2e2;
  color: #991b1b;
}

.btn:hover {
  opacity: 0.8;
}

.pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
  gap: 5px;
}

.page-link {
  padding: 5px 10px;
  border: 1px solid #ddd;
  background: white;
  color: #610C27;
  text-decoration: none;
  border-radius: 4px;
  font-size: 13px;
}

.page-link.active {
  background: #610C27;
  color: white;
  border-color: #610C27;
}

/* MODAL STYLES */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
}

.modal-content {
  background-color: white;
  margin: 10% auto;
  padding: 30px;
  border-radius: 12px;
  width: 500px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
  position: relative;
}

.modal-header {
  border-bottom: 1px solid #eee;
  padding-bottom: 15px;
  margin-bottom: 20px;
}

.modal-header h2 {
  margin: 0;
  color: #610C27;
}

.close-btn {
  position: absolute;
  right: 20px;
  top: 20px;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.modal-footer {
  margin-top: 25px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.modal-body p {
  margin: 10px 0;
  font-size: 14px;
}

.modal-body strong {
  color: #610C27;
  width: 120px;
  display: inline-block;
}

.reason-input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-top: 10px;
  resize: vertical;
  min-height: 80px;
}
</style>
</head>

<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Admin</h2>
  </div>

  <a href="admin_dashboard.php" class="<?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="admin_analytics.php" class="<?php echo $current_page == 'admin_analytics.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i><span class="text">Analytics</span></a>
  <a href="admin_users.php" class="<?php echo $current_page == 'admin_users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i><span class="text">Users</span></a>
  <a href="admin_product.php" class="<?php echo $current_page == 'admin_product.php' ? 'active' : ''; ?>"><i class="fas fa-box"></i><span class="text">Products</span></a>
  <a href="admin_orders.php" class="<?php echo $current_page == 'admin_orders.php' ? 'active' : ''; ?>"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="admin_reports.php" class="<?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="admin_approvals.php" class="<?php echo $current_page == 'admin_approvals.php' ? 'active' : ''; ?>"><i class="fas fa-user-check"></i><span class="text">Approvals</span></a>
  <a href="admin_settings.php" class="<?php echo $current_page == 'admin_settings.php' ? 'active' : ''; ?>"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  
  <a href="login.php" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
</div>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const main = document.getElementById("main");
  if (sidebar) sidebar.classList.toggle("collapsed");
  if (main) main.classList.toggle("full");
}
</script>

<!-- CONTENT -->
<div class="container" id="main">

<h1>Seller Approvals</h1>
<p>Review and manage new seller applications.</p>

<div class="card">
  <table>
    <thead>
      <tr>
        <th>Seller Name</th>
        <th>Shop Name</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Juan Dela Cruz</td>
        <td>Juan's Clothing</td>
        <td>
          <button class="btn btn-view" onclick="openViewModal('Juan Dela Cruz', '09123456789', 'juan@example.com', 'Juan\'s Clothing', 'Individual', '123-456-789')">View</button>
          <button class="btn btn-approve" onclick="openAcceptModal('Juan Dela Cruz')">Approve</button>
          <button class="btn btn-reject" onclick="openDeclineModal('Juan Dela Cruz')">Reject</button>
        </td>
      </tr>
      <tr>
        <td>Maria Clara</td>
        <td>Clara Fashion</td>
        <td>
          <button class="btn btn-view" onclick="openViewModal('Maria Clara', '09987654321', 'maria@example.com', 'Clara Fashion', 'Business', '987-654-321')">View</button>
          <button class="btn btn-approve" onclick="openAcceptModal('Maria Clara')">Approve</button>
          <button class="btn btn-reject" onclick="openDeclineModal('Maria Clara')">Reject</button>
        </td>
      </tr>
      <tr>
        <td>Jose Rizal</td>
        <td>Traditional Clothes</td>
        <td>
          <button class="btn btn-view" onclick="openViewModal('Jose Rizal', '09112223334', 'jose@example.com', 'Traditional Clothes', 'Individual', '111-222-333')">View</button>
          <button class="btn btn-approve" onclick="openAcceptModal('Jose Rizal')">Approve</button>
          <button class="btn btn-reject" onclick="openDeclineModal('Jose Rizal')">Reject</button>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- PAGINATION -->
  <div class="pagination">
    <a href="#" class="page-link">Previous</a>
    <a href="#" class="page-link active">1</a>
    <a href="#" class="page-link">2</a>
    <a href="#" class="page-link">Next</a>
  </div>
</div>

</div>

<!-- VIEW MODAL -->
<div id="viewModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('viewModal')">&times;</span>
    <div class="modal-header">
      <h2>Seller Details</h2>
    </div>
    <div class="modal-body">
      <p><strong>Name:</strong> <span id="viewName"></span></p>
      <p><strong>Contact:</strong> <span id="viewContact"></span></p>
      <p><strong>Email:</strong> <span id="viewEmail"></span></p>
      <p><strong>Shop Name:</strong> <span id="viewShop"></span></p>
      <p><strong>Seller Type:</strong> <span id="viewType"></span></p>
      <p><strong>TIN Number:</strong> <span id="viewTin"></span></p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-view" onclick="closeModal('viewModal')">Close</button>
    </div>
  </div>
</div>

<!-- ACCEPT MODAL -->
<div id="acceptModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('acceptModal')">&times;</span>
    <div class="modal-header">
      <h2>Accept Seller</h2>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to accept <strong><span id="acceptName"></span></strong> as a seller?</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-view" onclick="closeModal('acceptModal')">Cancel</button>
      <button class="btn btn-approve" onclick="confirmAccept()">Accept</button>
    </div>
  </div>
</div>

<!-- DECLINE MODAL -->
<div id="declineModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('declineModal')">&times;</span>
    <div class="modal-header">
      <h2>Decline Seller</h2>
    </div>
    <div class="modal-body">
      <p>Reason to decline <strong><span id="declineName"></span></strong>:</p>
      <textarea class="reason-input" id="declineReason" placeholder="Enter reason here..."></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn btn-view" onclick="closeModal('declineModal')">Cancel</button>
      <button class="btn btn-reject" onclick="confirmDecline()">Submit</button>
    </div>
  </div>
</div>

<script>
function openViewModal(name, contact, email, shop, type, tin) {
  document.getElementById('viewName').innerText = name;
  document.getElementById('viewContact').innerText = contact;
  document.getElementById('viewEmail').innerText = email;
  document.getElementById('viewShop').innerText = shop;
  document.getElementById('viewType').innerText = type;
  document.getElementById('viewTin').innerText = tin;
  document.getElementById('viewModal').style.display = 'block';
}

function openAcceptModal(name) {
  document.getElementById('acceptName').innerText = name;
  document.getElementById('acceptModal').style.display = 'block';
}

function openDeclineModal(name) {
  document.getElementById('declineName').innerText = name;
  document.getElementById('declineModal').style.display = 'block';
}

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

function confirmAccept() {
  alert('Seller accepted successfully!');
  closeModal('acceptModal');
}

function confirmDecline() {
  const reason = document.getElementById('declineReason').value;
  if(!reason) {
    alert('Please provide a reason.');
    return;
  }
  alert('Seller declined. Reason: ' + reason);
  closeModal('declineModal');
}

// Close modal when clicking outside
window.onclick = function(event) {
  if (event.target.className === 'modal') {
    event.target.style.display = 'none';
  }
}
</script>

</body>
</html>
