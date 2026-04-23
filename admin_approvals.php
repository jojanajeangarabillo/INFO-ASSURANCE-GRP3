<?php
require_once 'admin/db.connect.php';
require_once 'admin/email.helper.php';

/* =========================
   HANDLE APPROVE / REJECT
========================= */
if (isset($_GET['action'], $_GET['id'])) {
    $sellerId = (int) $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        // Get seller details before approving
        $sellerStmt = $conn->prepare("
            SELECT s.*, u.user_id, u.username, u.email 
            FROM seller s 
            JOIN user u ON s.user_id = u.user_id 
            WHERE s.seller_id = ?
        ");
        $sellerStmt->bind_param("i", $sellerId);
        $sellerStmt->execute();
        $seller = $sellerStmt->get_result()->fetch_assoc();
        
        if ($seller) {
            // Update approval status
            $stmt = $conn->prepare("UPDATE seller SET is_approved = 1 WHERE seller_id = ?");
            $stmt->bind_param("i", $sellerId);
            $stmt->execute();
            
            // Generate temporary password
            $tempPassword = bin2hex(random_bytes(6));
            $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Update user password and ensure account is activated
            $updatePassStmt = $conn->prepare("UPDATE user SET password = ?, is_activated = 1, is_locked = 0, attempts = 0 WHERE user_id = ?");
            $updatePassStmt->bind_param("si", $passwordHash, $seller['user_id']);
            $updatePassStmt->execute();
            
            // Send approval email to seller
            $subject = "Your Seller Account Has Been Approved!";
            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { padding: 20px; background-color: #fdf2f6; }
                        .content { background-color: white; padding: 20px; border-radius: 10px; }
                        .header { color: #610C27; font-size: 24px; margin-bottom: 20px; }
                        .credentials { background-color: #f9dbe5; padding: 15px; border-radius: 8px; margin: 20px 0; }
                        .button { background-color: #610C27; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
                        .warning { color: #dc2626; font-size: 14px; margin-top: 10px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='content'>
                            <div class='header'>Welcome to J3RS Marketplace!</div>
                            <p>Dear " . htmlspecialchars($seller['shop_name']) . ",</p>
                            <p>Congratulations! Your seller account has been approved by our admin team.</p>
                            <div class='credentials'>
                                <p><strong>Your Account Credentials:</strong></p>
                                <p><strong>Username:</strong> " . htmlspecialchars($seller['username']) . "<br>
                                <strong>Temporary Password:</strong> " . htmlspecialchars($tempPassword) . "</p>
                                <p class='warning'><strong>Important:</strong> Please login and change your password immediately for security purposes.</p>
                            </div>
                            <p>Click the button below to login to your seller account:</p>
                            <p><a href='http://localhost/INFO-ASSURANCE-GRP3/login.php' class='button'>Login to Your Account</a></p>
                            <p>Once logged in, you can:</p>
                            <ul>
                                <li>Change your password in your profile settings</li>
                                <li>Start adding products to your store</li>
                                <li>Manage your orders and inventory</li>
                                <li>Update your shop information</li>
                            </ul>
                            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                            <p>Best regards,<br>J3RS Marketplace Team</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            send_email($seller['email'], $subject, $body);
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => 'Seller approved successfully']);
            exit;
        }
    }

    if ($action === 'reject') {
        // Get seller details before rejecting to send email
        $sellerStmt = $conn->prepare("
            SELECT s.*, u.user_id, u.username, u.email 
            FROM seller s 
            JOIN user u ON s.user_id = u.user_id 
            WHERE s.seller_id = ?
        ");
        $sellerStmt->bind_param("i", $sellerId);
        $sellerStmt->execute();
        $seller = $sellerStmt->get_result()->fetch_assoc();
        
        if ($seller) {
            // Send rejection email
            $subject = "Seller Application Update - J3RS Marketplace";
            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { padding: 20px; background-color: #fdf2f6; }
                        .content { background-color: white; padding: 20px; border-radius: 10px; }
                        .header { color: #dc2626; font-size: 24px; margin-bottom: 20px; }
                        .message-box { background-color: #fee2e2; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc2626; }
                        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='content'>
                            <div class='header'>Application Status Update</div>
                            <p>Dear " . htmlspecialchars($seller['shop_name']) . ",</p>
                            <div class='message-box'>
                                <p>Thank you for your interest in becoming a seller at J3RS Marketplace.</p>
                                <p>After careful review of your application, we regret to inform you that your seller application has not been approved at this time.</p>
                                <p><strong>Reason:</strong> Your application does not meet the minimum requirements for seller registration.</p>
                                <p>Common reasons for rejection include:</p>
                                <ul>
                                    <li>Incomplete or invalid documentation</li>
                                    <li>Business permit or valid ID not clearly visible</li>
                                    <li>Information provided does not match submitted documents</li>
                                    <li>Age requirement not met</li>
                                </ul>
                                <p>You may reapply after ensuring all requirements are properly met.</p>
                            </div>
                            <p>If you have any questions, please contact our support team for assistance.</p>
                            <p>Best regards,<br>J3RS Marketplace Team</p>
                            <div class='footer'>
                                <p>This is an automated message. Please do not reply to this email.</p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            send_email($seller['email'], $subject, $body);
            
            // Delete seller record
            $stmt = $conn->prepare("DELETE FROM seller WHERE seller_id = ?");
            $stmt->bind_param("i", $sellerId);
            $stmt->execute();
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => 'Seller rejected successfully']);
            exit;
        }
    }

    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header("Location: admin_approvals.php");
        exit;
    }
}

// Handle AJAX request for getting seller details - FIXED QUERY
if (isset($_GET['get_details']) && isset($_GET['id'])) {
    $sellerId = (int) $_GET['id'];
    
    $query = "
        SELECT s.*, u.username, u.email 
        FROM seller s 
        JOIN user u ON s.user_id = u.user_id 
        WHERE s.seller_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $sellerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $seller = $result->fetch_assoc();
    
    if ($seller) {
        $metadata = json_decode($seller['shop_description'], true);
        
        $response = [
            'success' => true,
            'seller_id' => $seller['seller_id'],
            'user_id' => $seller['user_id'],
            'username' => $seller['username'],
            'email' => $seller['email'],
            'full_name' => $seller['full_name'] ?? ($metadata['full_name'] ?? $seller['username']),
            'shop_name' => $seller['shop_name'],
            'shop_address' => $seller['shop_address'],
            'contact_number' => $seller['contact_number'],
            'is_approved' => $seller['is_approved'],
            'created_at' => $seller['created_at'],
            'metadata' => $metadata,
            'images' => [
                'business_permit' => $metadata['business_permit_picture'] ?? null,
                'valid_id' => $metadata['valid_id_picture'] ?? null,
                'shop_image' => $metadata['shop_image'] ?? null
            ]
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Seller not found']);
    }
    exit;
}

/* =========================
   FETCH SELLERS
========================= */
$query = "
SELECT s.*, u.username, u.email
FROM seller s
JOIN user u ON s.user_id = u.user_id
WHERE s.is_approved = 0
ORDER BY s.created_at DESC
";

$result = $conn->query($query);
?>

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

/* ===== MAIN CONTAINER ===== */
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

/* ===== CARD ===== */
.card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

/* ===== TABLE ===== */
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
}

/* ===== BUTTONS ===== */
.btn {
  padding: 6px 12px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-size: 12px;
  font-weight: bold;
  text-decoration: none;
  margin-right: 5px;
  display: inline-block;
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

/* ===== MODAL STYLES ===== */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: white;
  margin: 5% auto;
  padding: 0;
  width: 700px;
  border-radius: 16px;
  position: relative;
  max-height: 85%;
  overflow-y: auto;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    transform: translateY(-50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  padding: 20px 25px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  color: #610C27;
  font-size: 20px;
}

.close-btn {
  font-size: 28px;
  cursor: pointer;
  color: #999;
  transition: color 0.2s;
}

.close-btn:hover {
  color: #610C27;
}

.modal-body {
  padding: 25px;
}

.modal-footer {
  padding: 15px 25px;
  border-top: 1px solid #eee;
  text-align: right;
}

/* Confirmation Modal */
.confirm-modal .modal-content {
  width: 450px;
}

.confirm-icon {
  width: 60px;
  height: 60px;
  margin: 0 auto 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 30px;
}

.confirm-icon.approve {
  background: #dcfce7;
  color: #16a34a;
}

.confirm-icon.reject {
  background: #fee2e2;
  color: #dc2626;
}

.confirm-message {
  text-align: center;
  margin-bottom: 25px;
}

.confirm-message h3 {
  font-size: 20px;
  margin-bottom: 10px;
  color: #333;
}

.confirm-message p {
  color: #666;
  font-size: 14px;
}

.confirm-buttons {
  display: flex;
  gap: 10px;
  justify-content: center;
}

.confirm-buttons button {
  padding: 10px 20px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.2s;
}

.btn-confirm-approve {
  background: #610C27;
  color: white;
}

.btn-confirm-approve:hover {
  background: #a61b4a;
}

.btn-confirm-reject {
  background: #dc2626;
  color: white;
}

.btn-confirm-reject:hover {
  background: #b91c1c;
}

.btn-cancel {
  background: #e5e7eb;
  color: #374151;
}

.btn-cancel:hover {
  background: #d1d5db;
}

/* Other styles */
.image-preview {
  max-width: 200px;
  max-height: 200px;
  margin: 10px 0;
  border-radius: 8px;
  border: 1px solid #ddd;
}

.section-title {
  font-size: 18px;
  font-weight: bold;
  color: #610C27;
  margin-top: 15px;
  margin-bottom: 10px;
  border-bottom: 2px solid #f9dbe5;
  padding-bottom: 5px;
}

.success-popup {
  position: fixed;
  top: 20px;
  right: 20px;
  background: #10b981;
  color: white;
  padding: 15px 20px;
  border-radius: 8px;
  z-index: 1100;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.loading {
  text-align: center;
  padding: 20px;
  color: #610C27;
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #999;
  font-size: 16px;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Admin</h2>
  </div>

  <a href="admin_dashboard.php"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="admin_analytics.php"><i class="fas fa-chart-line"></i><span class="text">Analytics</span></a>
  <a href="admin_users.php"><i class="fas fa-users"></i><span class="text">Users</span></a>
  <a href="admin_product.php"><i class="fas fa-box"></i><span class="text">Products</span></a>
  <a href="admin_orders.php"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="admin_reports.php"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="admin_approvals.php" class="active"><i class="fas fa-user-check"></i><span class="text">Approvals</span></a>
  <a href="admin_settings.php"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
</div>

<!-- MAIN CONTENT -->
<div class="container" id="main">

<h1>Seller Approvals</h1>
<p>Review and manage new seller applications.</p>

<div class="card">
<?php if ($result && $result->num_rows > 0): ?>
<table>
<thead>
<tr>
  <th>Seller Name</th>
  <th>Shop Name</th>
  <th>Shop Address</th>
  <th>Contact Number</th>
  <th>Actions</th>
</tr>
</thead>

<tbody>
<?php while ($row = $result->fetch_assoc()): 
    $meta = json_decode($row['shop_description'], true);
?>
<tr>
  <td>
    <?php echo htmlspecialchars($row['full_name'] ?? ($meta['full_name'] ?? $row['username'])); ?>
  </td>
  <td>
    <?php echo htmlspecialchars($row['shop_name']); ?>
  </td>
  <td>
    <?php echo htmlspecialchars(substr($row['shop_address'], 0, 50)) . (strlen($row['shop_address']) > 50 ? '...' : ''); ?>
  </td>
  <td>
    <?php echo htmlspecialchars($row['contact_number']); ?>
  </td>
  <td>
    <button class="btn btn-view" onclick="openView(<?php echo $row['seller_id']; ?>)">
      <i class="fas fa-eye"></i> View
    </button>
    <button class="btn btn-approve" onclick="showApproveConfirm(<?php echo $row['seller_id']; ?>)">
      <i class="fas fa-check"></i> Approve
    </button>
    <button class="btn btn-reject" onclick="showRejectConfirm(<?php echo $row['seller_id']; ?>)">
      <i class="fas fa-times"></i> Reject
    </button>
  </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php else: ?>
<div class="no-data">
  <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
  No pending seller approvals at this time.
</div>
<?php endif; ?>
</div>
</div>

<!-- VIEW MODAL -->
<div id="viewModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2><i class="fas fa-store"></i> Seller Application Details</h2>
      <span class="close-btn" onclick="closeModal('viewModal')">&times;</span>
    </div>
    <div class="modal-body" id="modalBody">
      <div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading seller details...</div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-view" onclick="closeModal('viewModal')">Close</button>
    </div>
  </div>
</div>

<!-- APPROVE CONFIRMATION MODAL -->
<div id="approveModal" class="modal confirm-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Confirm Approval</h2>
      <span class="close-btn" onclick="closeModal('approveModal')">&times;</span>
    </div>
    <div class="modal-body">
      <div class="confirm-icon approve">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="confirm-message">
        <h3>Approve Seller Application?</h3>
        <p>Are you sure you want to approve this seller? An email will be sent to the seller with their login credentials.</p>
      </div>
      <div class="confirm-buttons">
        <button class="btn-cancel" onclick="closeModal('approveModal')">Cancel</button>
        <button class="btn-confirm-approve" id="confirmApproveBtn">Yes, Approve</button>
      </div>
    </div>
  </div>
</div>

<!-- REJECT CONFIRMATION MODAL -->
<div id="rejectModal" class="modal confirm-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Confirm Rejection</h2>
      <span class="close-btn" onclick="closeModal('rejectModal')">&times;</span>
    </div>
    <div class="modal-body">
      <div class="confirm-icon reject">
        <i class="fas fa-times-circle"></i>
      </div>
      <div class="confirm-message">
        <h3>Reject Seller Application?</h3>
        <p>Are you sure you want to reject this seller? An email will be sent to inform them about the rejection.</p>
      </div>
      <div class="confirm-buttons">
        <button class="btn-cancel" onclick="closeModal('rejectModal')">Cancel</button>
        <button class="btn-confirm-reject" id="confirmRejectBtn">Yes, Reject</button>
      </div>
    </div>
  </div>
</div>

<script>
let currentSellerId = null;

function openView(sellerId) {
    document.getElementById('modalBody').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading seller details...</div>';
    document.getElementById('viewModal').style.display = 'block';
    
    fetch(`admin_approvals.php?get_details=1&id=${sellerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const meta = data.metadata;
                
                let html = `
                    <div class="section-title">
                        <i class="fas fa-user"></i> Personal Information
                    </div>
                    <p><strong>Full Name:</strong> ${escapeHtml(data.full_name)}</p>
                    <p><strong>Email:</strong> ${escapeHtml(data.email)}</p>
                    <p><strong>Contact Number:</strong> ${escapeHtml(data.contact_number)}</p>
                    <p><strong>Age:</strong> ${escapeHtml(meta.age || 'N/A')}</p>
                    <p><strong>TIN ID:</strong> ${escapeHtml(meta.tin_id || 'N/A')}</p>
                    
                    <div class="section-title">
                        <i class="fas fa-building"></i> Business Information
                    </div>
                    <p><strong>Shop Name:</strong> ${escapeHtml(data.shop_name)}</p>
                    <p><strong>Shop Address:</strong> ${escapeHtml(data.shop_address)}</p>
                    <p><strong>Business Category:</strong> ${escapeHtml(meta.business_category || 'N/A')}</p>
                    
                    <div class="section-title">
                        <i class="fas fa-file-alt"></i> Submitted Documents
                    </div>
                `;
                
                if (meta.business_permit_picture) {
                    html += `<p><strong>Business Permit:</strong><br><img src="${escapeHtml(meta.business_permit_picture)}" class="image-preview" alt="Business Permit" onerror="this.style.display='none'"></p>`;
                } else {
                    html += `<p><strong>Business Permit:</strong> No image uploaded</p>`;
                }
                
                if (meta.valid_id_picture) {
                    html += `<p><strong>Valid ID:</strong><br><img src="${escapeHtml(meta.valid_id_picture)}" class="image-preview" alt="Valid ID" onerror="this.style.display='none'"></p>`;
                } else {
                    html += `<p><strong>Valid ID:</strong> No image uploaded</p>`;
                }
                
                if (meta.shop_image) {
                    html += `<p><strong>Shop Image:</strong><br><img src="${escapeHtml(meta.shop_image)}" class="image-preview" alt="Shop Image" onerror="this.style.display='none'"></p>`;
                } else {
                    html += `<p><strong>Shop Image:</strong> No image uploaded</p>`;
                }
                
                document.getElementById('modalBody').innerHTML = html;
            } else {
                document.getElementById('modalBody').innerHTML = `<div class="no-data">${escapeHtml(data.message)}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalBody').innerHTML = '<div class="no-data">Error loading seller details. Please try again.</div>';
        });
}

function showApproveConfirm(sellerId) {
    currentSellerId = sellerId;
    document.getElementById('approveModal').style.display = 'block';
}

function showRejectConfirm(sellerId) {
    currentSellerId = sellerId;
    document.getElementById('rejectModal').style.display = 'block';
}

document.getElementById('confirmApproveBtn')?.addEventListener('click', function() {
    if (currentSellerId) {
        closeModal('approveModal');
        processApprove(currentSellerId);
    }
});

document.getElementById('confirmRejectBtn')?.addEventListener('click', function() {
    if (currentSellerId) {
        closeModal('rejectModal');
        processReject(currentSellerId);
    }
});

function processApprove(sellerId) {
    showSuccessPopup('Processing approval...');
    
    fetch(`admin_approvals.php?action=approve&id=${sellerId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessPopup('✅ Seller approved successfully! An email has been sent.');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showSuccessPopup('❌ Error approving seller', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showSuccessPopup('❌ Error approving seller', 'error');
    });
}

function processReject(sellerId) {
    showSuccessPopup('Processing rejection...');
    
    fetch(`admin_approvals.php?action=reject&id=${sellerId}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessPopup('✅ Seller rejected successfully. Email notification sent.');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showSuccessPopup('❌ Error rejecting seller', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showSuccessPopup('❌ Error rejecting seller', 'error');
    });
}

function showSuccessPopup(message, type = 'success') {
    const popup = document.createElement('div');
    popup.className = 'success-popup';
    popup.style.background = type === 'error' ? '#dc2626' : '#10b981';
    popup.innerHTML = `<i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}" style="margin-right: 10px;"></i>${message}`;
    document.body.appendChild(popup);
    
    setTimeout(() => {
        popup.remove();
    }, 3000);
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
    document.getElementById("main").classList.toggle("full");
}

window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

</body>
</html>