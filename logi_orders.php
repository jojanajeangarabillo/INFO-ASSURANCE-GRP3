<?php

// Mock Order Data
$deliveries = [
    ['id' => 'ORD-9021', 'customer' => 'Jane Doe', 'rider_id' => 1, 'rider_name' => 'Juan Dela Cruz', 'status' => 'Pending Pickup', 'pickup' => '-', 'delivery' => '-'],
    ['id' => 'ORD-8943', 'customer' => 'John Smith', 'rider_id' => 2, 'rider_name' => 'Maria Santos', 'status' => 'In Transit', 'pickup' => 'Oct 24', 'delivery' => 'Est. Oct 26'],
    ['id' => 'ORD-8820', 'customer' => 'Alice Brown', 'rider_id' => 0, 'rider_name' => 'Unassigned', 'status' => 'Pending Pickup', 'pickup' => '-', 'delivery' => '-'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logistics Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="sidebar.css">

<style>
/* Existing Layout Styles */
body { margin: 0; font-family: 'Inter', Arial, sans-serif; background: #fdf2f6; }
.main-content { margin-left: 240px; padding: 40px 60px; transition: margin-left 0.3s ease; }
.main-content.full { margin-left: 70px; }
h1 { color: #610C27; margin-bottom: 5px; font-size: 32px; }
.sub-header { color: #777; margin-bottom: 30px; }

/* Table Component Styles */
.tab-nav { display: flex; gap: 25px; border-bottom: 1px solid #ddd; margin-bottom: 25px; }
.tab-nav a { text-decoration: none; color: #888; padding-bottom: 10px; font-size: 14px; font-weight: 600; position: relative; cursor: pointer; }
.tab-nav a.active { color: #610C27; }
.tab-nav a.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; background: #610C27; }

.search-container { background: white; padding: 15px 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px; border: 1px solid #eee; display: flex; align-items: center; }
.search-container input { width: 100%; border: none; outline: none; font-size: 14px; margin-left: 10px; }

.table-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; padding: 15px 20px; font-size: 12px; text-transform: uppercase; color: #aaa; background: #fcfcfc; }
td { padding: 18px 20px; border-top: 1px solid #f5f5f5; font-size: 14px; color: #444; }

/* Badges and Buttons */
.badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.pending { background: #fff8e6; color: #ffa000; border: 1px solid #ffecc2; }
.transit { background: #eef4ff; color: #3b82f6; border: 1px solid #dbeafe; }
.outfordelivery { background: #fff0f6; color: #ec4899; border: 1px solid #ffdeeb; }
.delivered { background: #ecfdf5; color: #10b981; border: 1px solid #d1fae5; }

.btn-edit { background: white; color: #610C27; border: 1.5px solid #610C27; padding: 6px 14px; border-radius: 6px; font-size: 13px; cursor: pointer; transition: 0.2s; font-weight: bold; }
.btn-edit:hover { background: #610C27; color: white; }

/* Modal Styles */
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
.modal-content { background-color: white; margin: 10% auto; padding: 30px; border-radius: 15px; width: 400px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.modal-body label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; font-size: 14px; }
.modal-body select, .modal-body input { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; }
.btn-save { background: #610C27; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; }
.btn-cancel { background: #eee; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
</style>
</head>

<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Logistics</h2>
  </div>
  <a href="logi_dashboard.php" class="<?= $current_page == 'logi_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="logi_orders.php" class="<?= $current_page == 'logi_orders.php' ? 'active' : '' ?>"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="logi_tracking.php" class="<?= $current_page == 'logi_tracking.php' ? 'active' : '' ?>"><i class="fas fa-truck-fast"></i><span class="text">Tracking</span></a>
  <a href="logi_reports.php" class="<?= $current_page == 'logi_reports.php' ? 'active' : '' ?>"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="logi_settings.php" class="<?= $current_page == 'logi_settings.php' ? 'active' : '' ?>"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  <a href="#" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
</div>

<div class="main-content" id="main">
    <header>
        <h1>Delivery Management</h1>
        <p class="sub-header">Manage order status.</p>
    </header>

    <div class="tab-nav">
        <a class="active" onclick="filterTable('all', this)">All</a>
        <a onclick="filterTable('Pending Pickup', this)">Pending Pickup</a>
        <a onclick="filterTable('In Transit', this)">In Transit</a>
        <a onclick="filterTable('Out for Delivery', this)">Out for Delivery</a>
        <a onclick="filterTable('Delivered', this)">Delivered</a>
    </div>

    <div class="search-container">
        <i class="fas fa-search" style="color: #ccc;"></i>
        <input type="text" id="searchInput" onkeyup="searchOrders()" placeholder="Search by Order ID or Customer...">
    </div>

    <div class="table-card">
        <table id="deliveryTable">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Assigned Rider</th>
                    <th>Status</th>
                    <th>Dates</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveries as $order): ?>
                <tr data-status="<?php echo $order['status']; ?>">
                    <td style="font-weight: bold;"><?php echo $order['id']; ?></td>
                    <td><?php echo $order['customer']; ?></td>
                    <td>
                        <span class="rider-name"><?php echo $order['rider_name']; ?></span>
                    </td>
                    <td>
                        <span class="badge <?php echo strtolower(str_replace(' ', '', $order['status'])); ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </td>
                    <td style="font-size: 12px; color: #888;">
                        <div>Pickup: <?php echo $order['pickup']; ?></div>
                        <div>Delivery: <?php echo $order['delivery']; ?></div>
                    </td>
                    <td>
                        <button class="btn-edit" onclick="openEditModal('<?php echo $order['id']; ?>', '<?php echo $order['rider_id']; ?>', '<?php echo $order['status']; ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalOrderId" style="color: #610C27; margin: 0;">Edit Order</h2>
            <span onclick="closeModal()" style="cursor: pointer; font-size: 24px;">&times;</span>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editOrderId">
            
            <label>Assign Rider</label>
            <input type="text" id="editRiderName" list="riderSuggestions" placeholder="Type rider name...">
            <datalist id="riderSuggestions">
                <?php foreach ($riders as $rider): ?>
                    <option value="<?php echo $rider['name']; ?>">
                <?php endforeach; ?>
            </datalist>

            <label>Update Status</label>
            <select id="editStatus">
                <option value="Pending Pickup">Pending Pickup</option>
                <option value="In Transit">In Transit</option>
                <option value="Out for Delivery">Out for Delivery</option>
                <option value="Delivered">Delivered</option>
            </select>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn-save" onclick="saveChanges()">Save Changes</button>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

function searchOrders() {
    let input = document.getElementById("searchInput").value.toUpperCase();
    let rows = document.getElementById("deliveryTable").getElementsByTagName("tr");
    for (let i = 1; i < rows.length; i++) {
        let text = rows[i].textContent || rows[i].innerText;
        rows[i].style.display = text.toUpperCase().indexOf(input) > -1 ? "" : "none";
    }
}

function filterTable(status, element) {
    let rows = document.querySelectorAll("#deliveryTable tbody tr");
    document.querySelectorAll(".tab-nav a").forEach(a => a.classList.remove("active"));
    element.classList.add("active");

    rows.forEach(row => {
        if (status === 'all' || row.getAttribute("data-status") === status) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

/* Updated Modal Functions */

function openEditModal(orderId, riderId, status) {
    // We find the current rider name from the table row to pre-fill the input
    const rows = document.querySelectorAll("#deliveryTable tbody tr");
    let currentRiderName = "";
    
    rows.forEach(row => {
        if(row.cells[0].innerText === orderId) {
            currentRiderName = row.querySelector('.rider-name').innerText;
        }
    });

    document.getElementById("editModal").style.display = "block";
    document.getElementById("modalOrderId").innerText = "Edit " + orderId;
    document.getElementById("editOrderId").value = orderId;
    
    // Set the text input value
    document.getElementById("editRiderName").value = (currentRiderName === "Unassigned") ? "" : currentRiderName;
    document.getElementById("editStatus").value = status;
}

function saveChanges() {
    const id = document.getElementById("editOrderId").value;
    const riderName = document.getElementById("editRiderName").value; // Gets typed text
    const status = document.getElementById("editStatus").value;

    if (riderName.trim() === "") {
        alert("Please enter a rider name.");
        return;
    }

    // Logic for updating the UI immediately (Visual feedback)
    const rows = document.querySelectorAll("#deliveryTable tbody tr");
    rows.forEach(row => {
        if(row.cells[0].innerText === id) {
            // Update Rider Name Text
            row.querySelector('.rider-name').innerText = riderName;
            
            // Update Status Badge
            const badge = row.querySelector('.badge');
            badge.innerText = status;
            badge.className = "badge " + status.toLowerCase().replace(/\s+/g, '');
            
            // Update Data Attribute for filtering
            row.setAttribute("data-status", status);
        }
    });

    console.log(`Payload: Order ${id} assigned to ${riderName} with status ${status}`);
    
    // Toast notification
    showToast(`Order ${id} Updated`);
    closeModal();
}

function showToast(msg) {
    let toast = document.getElementById("toast");
    if(!toast) {
        // Create toast if it doesn't exist in the HTML
        toast = document.createElement("div");
        toast.id = "toast";
        document.body.appendChild(toast);
    }
    toast.innerHTML = msg;
    toast.className = "show";
    setTimeout(() => { toast.className = ""; }, 3000);
}

function closeModal() {
    document.getElementById("editModal").style.display = "none";
}
</script>

</body>
</html>