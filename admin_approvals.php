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
            SELECT s.*, u.user_id, u.username, u.email, u.role_id, 
                   (SELECT COUNT(*) FROM customer WHERE user_id = u.user_id) as has_customer
            FROM seller s 
            JOIN user u ON s.user_id = u.user_id 
            WHERE s.seller_id = ?
        ");
        $sellerStmt->bind_param("i", $sellerId);
        $sellerStmt->execute();
        $seller = $sellerStmt->get_result()->fetch_assoc();
        
        if ($seller) {
            $currentRoleId = (int) $seller['role_id'];
            $hasCustomerRecord = (int) $seller['has_customer'] > 0;
            
            // Determine the new role based on user type
            $newRole = null;
            $roleType = '';
            
            if ($hasCustomerRecord || $currentRoleId == 2) {
                // User has customer record OR is currently a customer - Upgrade to Dual Role (4)
                $newRole = 4;
                $roleType = 'Dual (Customer & Seller)';
                $upgradeMessage = "This user had a customer account and is being upgraded to Dual Role.";
            } else {
                // Pure seller registration - Keep as Seller Only (3)
                $newRole = 3;
                $roleType = 'Seller Only';
                $upgradeMessage = "This is a pure seller registration (no existing customer account).";
            }
            
            // Update approval status
            $stmt = $conn->prepare("UPDATE seller SET is_approved = 1 WHERE seller_id = ?");
            $stmt->bind_param("i", $sellerId);
            $stmt->execute();
            
            // Update role if needed
            if ($newRole && $currentRoleId != $newRole) {
                $updateRoleStmt = $conn->prepare("UPDATE user SET role_id = ? WHERE user_id = ?");
                $updateRoleStmt->bind_param("ii", $newRole, $seller['user_id']);
                $updateRoleStmt->execute();
            }
            
            // Generate temporary password
            $tempPassword = bin2hex(random_bytes(6));
            $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Update user password and ensure account is activated
            $updatePassStmt = $conn->prepare("UPDATE user SET password = ?, is_activated = 1, is_locked = 0, attempts = 0 WHERE user_id = ?");
            $updatePassStmt->bind_param("si", $passwordHash, $seller['user_id']);
            $updatePassStmt->execute();
            
            // Update username if needed (for pure seller registration)
            if (!$hasCustomerRecord && $currentRoleId == 0) {
                $newUsername = preg_replace('/[^a-z0-9]/i', '_', strtolower($seller['shop_name']));
                $checkUserStmt = $conn->prepare("SELECT user_id FROM user WHERE username = ? AND user_id != ? LIMIT 1");
                $checkUserStmt->bind_param("si", $newUsername, $seller['user_id']);
                $checkUserStmt->execute();
                if ($checkUserStmt->get_result()->num_rows === 0) {
                    $updateUsernameStmt = $conn->prepare("UPDATE user SET username = ? WHERE user_id = ?");
                    $updateUsernameStmt->bind_param("si", $newUsername, $seller['user_id']);
                    $updateUsernameStmt->execute();
                }
            }
            
            // Create customer record if needed (for pure seller registration)
            if (!$hasCustomerRecord) {
                $insertCustomerStmt = $conn->prepare("
                    INSERT INTO customer (user_id, full_name, contact_number, address_line, city, region, postal_code)
                    VALUES (?, ?, ?, '', '', '', '')
                ");
                $fullName = $seller['full_name'] ?? $seller['shop_name'];
                $contactNumber = $seller['contact_number'] ?? '';
                $insertCustomerStmt->bind_param("iss", $seller['user_id'], $fullName, $contactNumber);
                $insertCustomerStmt->execute();
            }
            
            // Send appropriate approval email based on role type
            if ($roleType == 'Dual (Customer & Seller)') {
                $emailBody = getDualRoleEmail($seller, $tempPassword, $roleType);
            } else {
                $emailBody = getSellerOnlyEmail($seller, $tempPassword, $roleType);
            }
            
            send_email($seller['email'], "Your Seller Account Has Been Approved!", $emailBody);
            
            // Create notification for the user
            $notifStmt = $conn->prepare("
                INSERT INTO notification (user_id, title, message, is_read, created_at) 
                VALUES (?, ?, ?, 0, NOW())
            ");
            
            if ($roleType == 'Dual (Customer & Seller)') {
                $notifTitle = "Seller Application Approved - Account Upgraded to Dual Role";
                $notifMessage = "Congratulations! Your account has been upgraded to Dual Role. You can now switch between Customer and Seller modes using the 'Switch Role' button.";
            } else {
                $notifTitle = "Seller Application Approved";
                $notifMessage = "Congratulations! Your seller account has been approved. You can now start selling on J3RS Marketplace.";
            }
            
            $notifStmt->bind_param("iss", $seller['user_id'], $notifTitle, $notifMessage);
            $notifStmt->execute();
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => "Seller approved successfully as $roleType"]);
            exit;
        }
    }

    if ($action === 'reject') {
        // Get seller details before rejecting
        $sellerStmt = $conn->prepare("
            SELECT s.*, u.user_id, u.username, u.email, u.role_id 
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
            $body = getRejectionEmail($seller);
            send_email($seller['email'], $subject, $body);
            
            // Create notification for rejection
            $notifStmt = $conn->prepare("
                INSERT INTO notification (user_id, title, message, is_read, created_at) 
                VALUES (?, ?, ?, 0, NOW())
            ");
            $notifTitle = "Seller Application Not Approved";
            $notifMessage = "We regret to inform you that your seller application was not approved. Please check your email for more details.";
            $notifStmt->bind_param("iss", $seller['user_id'], $notifTitle, $notifMessage);
            $notifStmt->execute();
            
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

// Helper function for Dual Role email
function getDualRoleEmail($seller, $tempPassword, $roleType) {
    return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { padding: 20px; background-color: #fdf2f6; }
                .content { background-color: white; padding: 20px; border-radius: 10px; }
                .header { color: #610C27; font-size: 24px; margin-bottom: 20px; }
                .credentials { background-color: #f9dbe5; padding: 15px; border-radius: 8px; margin: 20px 0; }
                .upgrade-badge { background-color: #10b981; color: white; padding: 5px 10px; border-radius: 5px; display: inline-block; font-size: 12px; margin-left: 10px; }
                .button { background-color: #610C27; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .warning { color: #dc2626; font-size: 14px; margin-top: 10px; }
                .feature-list { margin: 15px 0; padding-left: 20px; }
                .feature-list li { margin: 8px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='content'>
                    <div class='header'>
                        Welcome to J3RS Marketplace! 
                        <span class='upgrade-badge'>Role Upgraded to Dual</span>
                    </div>
                    <p>Dear " . htmlspecialchars($seller['shop_name']) . ",</p>
                    <p>Congratulations! Your seller application has been approved by our admin team.</p>
                    
                    <div class='credentials'>
                        <p><strong>Your Account Has Been Upgraded!</strong></p>
                        <p>✅ Your account has been upgraded from <strong>Customer</strong> to <strong>Dual Role</strong> (Customer + Seller)</p>
                        <p><strong>Login Credentials:</strong></p>
                        <p><strong>Username:</strong> " . htmlspecialchars($seller['username']) . "<br>
                        <strong>Temporary Password:</strong> " . htmlspecialchars($tempPassword) . "</p>
                        <p class='warning'><strong>Important:</strong> Please login and change your password immediately.</p>
                    </div>
                    
                    <p><strong>What's New with Your Dual Role Account:</strong></p>
                    <ul class='feature-list'>
                        <li>🔄 <strong>Role Switching:</strong> Switch between Customer and Seller views</li>
                        <li>🏪 <strong>Sell Products:</strong> Start listing your products</li>
                        <li>📦 <strong>Manage Orders:</strong> Process customer orders</li>
                        <li>🛍️ <strong>Continue Shopping:</strong> Shop as a customer too</li>
                    </ul>
                    
                    <p><a href='http://localhost/INFO-ASSURANCE-GRP3/login.php' class='button'>Login to Your Account</a></p>
                    
                    <p>Best regards,<br><strong>J3RS Marketplace Team</strong></p>
                </div>
            </div>
        </body>
        </html>
    ";
}

// Helper function for Seller Only email
function getSellerOnlyEmail($seller, $tempPassword, $roleType) {
    return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { padding: 20px; background-color: #fdf2f6; }
                .content { background-color: white; padding: 20px; border-radius: 10px; }
                .header { color: #610C27; font-size: 24px; margin-bottom: 20px; }
                .credentials { background-color: #f9dbe5; padding: 15px; border-radius: 8px; margin: 20px 0; }
                .seller-badge { background-color: #f59e0b; color: white; padding: 5px 10px; border-radius: 5px; display: inline-block; font-size: 12px; margin-left: 10px; }
                .button { background-color: #610C27; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .warning { color: #dc2626; font-size: 14px; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='content'>
                    <div class='header'>
                        Welcome to J3RS Marketplace!
                        <span class='seller-badge'>Seller Account</span>
                    </div>
                    <p>Dear " . htmlspecialchars($seller['shop_name']) . ",</p>
                    <p>Congratulations! Your seller application has been approved by our admin team.</p>
                    
                    <div class='credentials'>
                        <p><strong>Your Seller Account Credentials:</strong></p>
                        <p><strong>Username:</strong> " . htmlspecialchars($seller['username']) . "<br>
                        <strong>Temporary Password:</strong> " . htmlspecialchars($tempPassword) . "</p>
                        <p class='warning'><strong>Important:</strong> Please login and change your password immediately.</p>
                    </div>
                    
                    <p><strong>What You Can Do Now:</strong></p>
                    <ul>
                        <li>🏪 <strong>Manage Your Shop:</strong> Add and manage products</li>
                        <li>📦 <strong>Process Orders:</strong> View and fulfill customer orders</li>
                        <li>📊 <strong>Track Analytics:</strong> Monitor your sales performance</li>
                    </ul>
                    
                    <p><a href='http://localhost/INFO-ASSURANCE-GRP3/login.php' class='button'>Login to Your Seller Account</a></p>
                    
                    <p>Best regards,<br><strong>J3RS Marketplace Team</strong></p>
                </div>
            </div>
        </body>
        </html>
    ";
}

// Helper function for rejection email
function getRejectionEmail($seller) {
    return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { padding: 20px; background-color: #fdf2f6; }
                .content { background-color: white; padding: 20px; border-radius: 10px; }
                .header { color: #dc2626; font-size: 24px; margin-bottom: 20px; }
                .message-box { background-color: #fee2e2; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc2626; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='content'>
                    <div class='header'>Application Status Update</div>
                    <p>Dear " . htmlspecialchars($seller['shop_name']) . ",</p>
                    <div class='message-box'>
                        <p>Thank you for your interest in becoming a seller at J3RS Marketplace.</p>
                        <p>After careful review, we regret to inform you that your seller application has not been approved at this time.</p>
                        <p>You may reapply after ensuring all requirements are properly met.</p>
                    </div>
                    <p>Best regards,<br>J3RS Marketplace Team</p>
                </div>
            </div>
        </body>
        </html>
    ";
}

// Handle AJAX request for getting seller details
if (isset($_GET['get_details']) && isset($_GET['id'])) {
    $sellerId = (int) $_GET['id'];
    
    $query = "
        SELECT s.*, u.username, u.email, u.role_id,
               (SELECT COUNT(*) FROM customer WHERE user_id = u.user_id) as has_customer
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
        $hasCustomer = (int) $seller['has_customer'] > 0;
        
        // Determine application type
        if ($hasCustomer || $seller['role_id'] == 2) {
            $applicationType = "Customer Upgrading to Dual Role";
            $typeColor = "#10b981";
            $willBecome = "Dual Role (Customer + Seller) - Role ID: 4";
        } else {
            $applicationType = "Pure Seller Registration";
            $typeColor = "#f59e0b";
            $willBecome = "Seller Only - Role ID: 3";
        }
        
        $response = [
            'success' => true,
            'seller_id' => $seller['seller_id'],
            'user_id' => $seller['user_id'],
            'username' => $seller['username'],
            'email' => $seller['email'],
            'role_id' => $seller['role_id'],
            'has_customer' => $seller['has_customer'],
            'application_type' => $applicationType,
            'will_become' => $willBecome,
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
SELECT s.*, u.username, u.email, u.role_id,
       (SELECT COUNT(*) FROM customer WHERE user_id = u.user_id) as has_customer
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
}

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

.application-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: bold;
}

.badge-upgrade {
  background: #dcfce7;
  color: #166534;
}

.badge-pure {
  background: #fef3c7;
  color: #92400e;
}

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

.btn-confirm-approve {
  background: #610C27;
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: bold;
}

.btn-confirm-reject {
  background: #dc2626;
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: bold;
}

.btn-cancel {
  background: #e5e7eb;
  color: #374151;
  padding: 10px 20px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: bold;
}

.image-preview {
  max-width: 200px;
  max-height: 200px;
  margin: 10px 0;
  border-radius: 8px;
  border: 1px solid #ddd;
  cursor: pointer;
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

.info-box {
  background: #dbeafe;
  border-left: 4px solid #3b82f6;
  padding: 12px;
  margin: 15px 0;
  border-radius: 8px;
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

.loading {
  text-align: center;
  padding: 20px;
  color: #610C27;
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #999;
}
</style>
</head>

<body>

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

<div class="container" id="main">
<h1>Seller Approvals</h1>
<p>Review and manage seller applications. <strong>Customer upgrades</strong> become Dual Role (4). <strong>Pure sellers</strong> remain Seller Only (3).</p>

<div class="card">
<?php if ($result && $result->num_rows > 0): ?>
<table>
<thead>
<tr>
  <th>Applicant</th>
  <th>Shop Name</th>
  <th>Application Type</th>
  <th>Current Role</th>
  <th>Will Become</th>
  <th>Actions</th>
</tr>
</thead>
<tbody>
<?php while ($row = $result->fetch_assoc()): 
    $meta = json_decode($row['shop_description'], true);
    $hasCustomer = (int) $row['has_customer'] > 0;
    $isUpgrade = ($hasCustomer || $row['role_id'] == 2);
    
    if ($isUpgrade) {
        $appType = "Customer Upgrade";
        $appBadge = "badge-upgrade";
        $willBecome = "Dual Role (4)";
    } else {
        $appType = "Pure Seller";
        $appBadge = "badge-pure";
        $willBecome = "Seller Only (3)";
    }
?>
<tr>
  <td><?php echo htmlspecialchars($row['full_name'] ?? ($meta['full_name'] ?? $row['username'])); ?></td>
  <td><?php echo htmlspecialchars($row['shop_name']); ?></td>
  <td><span class="application-badge <?php echo $appBadge; ?>"><?php echo $appType; ?></span></td>
  <td><?php echo $row['role_id'] == 2 ? 'Customer' : ($row['role_id'] == 3 ? 'Seller' : 'New'); ?></td>
  <td><strong><?php echo $willBecome; ?></strong></td>
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
<div class="no-data">No pending seller applications.</div>
<?php endif; ?>
</div>
</div>

<!-- MODALS (same as before but with updated text) -->
<div id="viewModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2><i class="fas fa-store"></i> Seller Application Details</h2>
      <span class="close-btn" onclick="closeModal('viewModal')">&times;</span>
    </div>
    <div class="modal-body" id="modalBody">Loading...</div>
    <div class="modal-footer">
      <button class="btn btn-view" onclick="closeModal('viewModal')">Close</button>
    </div>
  </div>
</div>

<div id="approveModal" class="modal confirm-modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Confirm Approval</h2>
      <span class="close-btn" onclick="closeModal('approveModal')">&times;</span>
    </div>
    <div class="modal-body" id="approveModalBody"></div>
    <div class="confirm-buttons" style="padding: 0 25px 25px 25px;">
      <button class="btn-cancel" onclick="closeModal('approveModal')">Cancel</button>
      <button class="btn-confirm-approve" id="confirmApproveBtn">Yes, Approve</button>
    </div>
  </div>
</div>

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
        <p>Are you sure you want to reject this seller? An email will be sent to inform them.</p>
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
let currentApplicationData = null;

function openView(sellerId) {
    document.getElementById('modalBody').innerHTML = '<div class="loading">Loading...</div>';
    document.getElementById('viewModal').style.display = 'block';
    
    fetch(`admin_approvals.php?get_details=1&id=${sellerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentApplicationData = data;
                const meta = data.metadata;
                
                let html = `
                    <div class="info-box">
                        <strong>Application Type:</strong> ${data.application_type}<br>
                        <strong>Will become:</strong> ${data.will_become}
                    </div>
                    
                    <div class="section-title">Personal Information</div>
                    <p><strong>Name:</strong> ${escapeHtml(data.full_name)}</p>
                    <p><strong>Email:</strong> ${escapeHtml(data.email)}</p>
                    <p><strong>Contact:</strong> ${escapeHtml(data.contact_number)}</p>
                    <p><strong>Age:</strong> ${escapeHtml(meta.age || 'N/A')}</p>
                    <p><strong>TIN ID:</strong> ${escapeHtml(meta.tin_id || 'N/A')}</p>
                    
                    <div class="section-title">Business Information</div>
                    <p><strong>Shop Name:</strong> ${escapeHtml(data.shop_name)}</p>
                    <p><strong>Shop Address:</strong> ${escapeHtml(data.shop_address)}</p>
                    <p><strong>Category:</strong> ${escapeHtml(meta.business_category || 'N/A')}</p>
                    
                    <div class="section-title">Documents</div>
                `;
                
                if (meta.business_permit_picture) {
                    html += `<p><strong>Business Permit:</strong><br><img src="${escapeHtml(meta.business_permit_picture)}" class="image-preview" onclick="window.open(this.src)"></p>`;
                }
                if (meta.valid_id_picture) {
                    html += `<p><strong>Valid ID:</strong><br><img src="${escapeHtml(meta.valid_id_picture)}" class="image-preview" onclick="window.open(this.src)"></p>`;
                }
                if (meta.shop_image) {
                    html += `<p><strong>Shop Image:</strong><br><img src="${escapeHtml(meta.shop_image)}" class="image-preview" onclick="window.open(this.src)"></p>`;
                }
                
                document.getElementById('modalBody').innerHTML = html;
            } else {
                document.getElementById('modalBody').innerHTML = `<div class="no-data">Error: ${escapeHtml(data.message)}</div>`;
            }
        });
}

function showApproveConfirm(sellerId) {
    currentSellerId = sellerId;
    
    fetch(`admin_approvals.php?get_details=1&id=${sellerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalBody = document.getElementById('approveModalBody');
                modalBody.innerHTML = `
                    <div class="confirm-icon approve">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="confirm-message">
                        <h3>Approve Seller Application?</h3>
                        <p><strong>${data.application_type}</strong></p>
                        <p>This seller will become: <strong>${data.will_become}</strong></p>
                        <div class="info-box" style="margin-top: 15px;">
                            <i class="fas fa-info-circle"></i> An email will be sent to the seller with their credentials.
                        </div>
                    </div>
                `;
                document.getElementById('approveModal').style.display = 'block';
            }
        });
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
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        showSuccessPopup(data.message);
        setTimeout(() => location.reload(), 2000);
    })
    .catch(() => showSuccessPopup('Error approving seller', 'error'));
}

function processReject(sellerId) {
    showSuccessPopup('Processing rejection...');
    
    fetch(`admin_approvals.php?action=reject&id=${sellerId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        showSuccessPopup(data.message);
        setTimeout(() => location.reload(), 2000);
    })
    .catch(() => showSuccessPopup('Error rejecting seller', 'error'));
}

function showSuccessPopup(message, type = 'success') {
    const popup = document.createElement('div');
    popup.className = 'success-popup';
    popup.style.background = type === 'error' ? '#dc2626' : '#10b981';
    popup.innerHTML = message;
    document.body.appendChild(popup);
    setTimeout(() => popup.remove(), 3000);
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
    document.getElementById("main").classList.toggle("full");
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.onclick = function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
}
</script>

</body>
</html>