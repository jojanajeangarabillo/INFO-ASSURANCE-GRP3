<?php
require_once 'auth.php';
require_roles([1]);
require_once 'admin/db.connect.php';
require_once 'admin/email.helper.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Fetch session timeout from database
$query = "SELECT session_timeout_minutes FROM system_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$timeout_minutes = $row ? $row['session_timeout_minutes'] : 30; 

// Check session timeout
if (!isset($_SESSION['last_activity'])) {
  $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout_minutes * 60) {
  // Session expired, logout
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
} else {
  // Update last activity
  $_SESSION['last_activity'] = time();
}

// Calculate timeout in milliseconds for client-side auto-logout
$timeout_ms = $timeout_minutes * 60 * 1000;

/* =========================
   HANDLE LOCK/UNLOCK ACTIONS
========================= */
if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        header("Location: admin_users.php");
        exit;
    }
    
    $target_user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];

    // Get max attempts from system settings
    $settingsStmt = $conn->query("SELECT max_login_attempts FROM system_settings LIMIT 1");
    $settings = $settingsStmt->fetch_assoc();
    $max_attempts = $settings['max_login_attempts'] ?? 3;

    if ($action === 'lock') {
        $stmt = $conn->prepare("UPDATE user SET is_locked = 1, attempts = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $max_attempts, $target_user_id);
    } else if ($action === 'unlock') {
        $stmt = $conn->prepare("UPDATE user SET is_locked = 0, attempts = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $target_user_id);
    }

    if (isset($stmt)) {
        $stmt->execute();
        
        // Fetch username for audit log
        $userStmt = $conn->prepare("SELECT username FROM user WHERE user_id = ?");
        $userStmt->bind_param("i", $target_user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result()->fetch_assoc();
        $targetUsername = $userResult['username'] ?? 'Unknown User';
        
        log_audit_action($action, 'User Management', "Admin " . ($action === 'lock' ? 'locked' : 'unlocked') . " user: $targetUsername");
        
        header("Location: admin_users.php?msg=success");
        exit;
    }
}

/* =========================
   HANDLE ADD LOGISTIC PARTNER
========================= */
if (isset($_POST['add_logistic'])) {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        header("Location: admin_users.php");
        exit;
    }
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = 5; // Logistic Role

    // Validate inputs
    if (empty($username) || empty($email)) {
        header("Location: admin_users.php?error=empty_fields");
        exit;
    }

    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT user_id FROM user WHERE username = ? OR email = ?");
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        header("Location: admin_users.php?error=exists");
        exit;
    }

    // Generate temporary password
    $tempPassword = bin2hex(random_bytes(6));
    $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

    // Provide values for NOT NULL columns that don't have defaults
    $empty = '';
    $empty_expiry = '0000-00-00 00:00:00';
    $zero = 0;

    // Insert new user
    $insertStmt = $conn->prepare("
        INSERT INTO user (
            username, email, password, role_id, is_activated, is_active,
            otp_code, otp_expiry, verification_token, token_expiry, mfa_secret
        ) VALUES (?, ?, ?, 5, 1, 1, ?, ?, ?, ?, ?)
    ");
    $insertStmt->bind_param("ssssssss", 
        $username, $email, $passwordHash, 
        $empty, $empty_expiry, $empty, $empty_expiry, $empty
    );
    
    if ($insertStmt->execute()) {
        // Send email
        $subject = "Welcome to J3RS Logistics Team!";
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
                        <div class='header'>Logistic Partner Account Created</div>
                        <p>Dear " . htmlspecialchars($username) . ",</p>
                        <p>Your account as a Logistic Partner has been created by the administrator.</p>
                        
                        <div class='credentials'>
                            <p><strong>Login Credentials:</strong></p>
                            <p><strong>Username:</strong> " . htmlspecialchars($username) . "<br>
                            <strong>Temporary Password:</strong> " . htmlspecialchars($tempPassword) . "</p>
                            <p class='warning'><strong>Important:</strong> Please login and change your password immediately.</p>
                        </div>
                        
                        <p><a href='http://localhost/INFO-ASSURANCE-GRP3/login.php' class='button'>Login to Your Account</a></p>
                        
                        <p>Best regards,<br><strong>J3RS Marketplace Team</strong></p>
                    </div>
                </div>
            </body>
            </html>
        ";
        $emailSent = send_email($email, $subject, $body);

        // Log audit action
        log_audit_action('create', 'Logistic Partners', "Admin created logistic partner account for user: $username");
        //        
        if ($emailSent) {
            header("Location: admin_users.php?msg=logistic_success");
        } else {
            // User was created but email failed
            header("Location: admin_users.php?msg=logistic_created_email_failed");
        }
    } else {
        // Log error for debugging
        error_log("Database error: " . $insertStmt->error);
        header("Location: admin_users.php?error=failed");
    }
    exit;
}

/* =========================
   HANDLE APPROVE / REJECT (from admin_approvals.php)
========================= */
if (isset($_GET['seller_action'], $_GET['id'])) {
    $sellerId = (int) $_GET['id'];
    $action = $_GET['seller_action'];

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
            } else {
                // Pure seller registration - Keep as Seller Only (3)
                $newRole = 3;
                $roleType = 'Seller Only';
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
                $contactNumber = encrypt_data($seller['contact_number'] ?? '');
                $insertCustomerStmt->bind_param("iss", $seller['user_id'], $fullName, $contactNumber);
                $insertCustomerStmt->execute();
            }
            
            // Send Email
            if ($roleType == 'Dual (Customer & Seller)') {
                $emailBody = getDualRoleEmail($seller, $tempPassword, $roleType);
            } else {
                $emailBody = getSellerOnlyEmail($seller, $tempPassword, $roleType);
            }
            send_email($seller['email'], "Your Seller Account Has Been Approved!", $emailBody);
            
            // Log audit action
            log_audit_action('approve', 'Seller Applications', "Admin approved seller application for shop: " . $seller['shop_name']);
            
            // Create notification
            $notifStmt = $conn->prepare("INSERT INTO notification (user_id, title, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
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
            
            // Log audit action
            log_audit_action('reject', 'Seller Applications', "Admin rejected seller application for shop: " . $seller['shop_name']);
            
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
}

// Handle AJAX request for getting seller details
if (isset($_GET['get_seller_details']) && isset($_GET['id'])) {
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
    $seller = $stmt->get_result()->fetch_assoc();
    
    if ($seller) {
        $metadata = json_decode($seller['shop_description'], true);
        $hasCustomer = (int) $seller['has_customer'] > 0;
        
        // Determine application type
        if ($hasCustomer || $seller['role_id'] == 2) {
            $applicationType = "Customer Upgrading to Dual Role";
            $willBecome = "Dual Role (Customer + Seller) - Role ID: 4";
        } else {
            $applicationType = "Pure Seller Registration";
            $willBecome = "Seller Only - Role ID: 3";
        }
        
        echo json_encode([
            'success' => true,
            'full_name' => $seller['full_name'] ?? ($metadata['full_name'] ?? $seller['username']),
            'email' => $seller['email'],
            'contact_number' => decrypt_data($seller['contact_number']),
            'shop_name' => $seller['shop_name'],
            'shop_address' => $seller['shop_address'],
            'application_type' => $applicationType,
            'will_become' => $willBecome,
            'metadata' => $metadata
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Seller not found']);
    }
    exit;
}

// Helper functions for emails (copied from admin_approvals.php)
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

/* =========================
   FETCH DATA
========================= */
// Fetch All Users
$usersStmt = $conn->query("
    SELECT u.user_id, u.username, u.email, u.is_locked, u.attempts, r.role_name
    FROM user u 
    JOIN role r ON u.role_id = r.role_id
    ORDER BY u.user_id DESC
");
$users = $usersStmt->fetch_all(MYSQLI_ASSOC);

// Fetch Pending Sellers (with has_customer check)
$pendingStmt = $conn->query("
    SELECT s.*, u.username, u.email, u.role_id,
           (SELECT COUNT(*) FROM customer WHERE user_id = u.user_id) as has_customer
    FROM seller s
    JOIN user u ON s.user_id = u.user_id
    WHERE s.is_approved = 0
    ORDER BY s.created_at DESC
");
$pendingSellers = $pendingStmt->fetch_all(MYSQLI_ASSOC);

// Fetch Roles for filter
$rolesStmt = $conn->query("SELECT role_name FROM role");
$roles = $rolesStmt->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="sidebar.css">

<script>
    const timeoutMs = <?php echo $timeout_ms; ?>;
    let logoutTimer;

    function resetTimer() {
      clearTimeout(logoutTimer);
      logoutTimer = setTimeout(function() {
        alert("Session expired due to inactivity. You will be logged out.");
        window.location.href = "logout.php";
      }, timeoutMs);
    }

    document.addEventListener("mousemove", resetTimer);
    document.addEventListener("keypress", resetTimer);
    document.addEventListener("click", resetTimer);
    document.addEventListener("scroll", resetTimer);

    resetTimer();
  </script>

<style>
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #fdf2f6;
}

.container {
  margin-left: 240px;
  padding: 20px;
  transition: 0.3s;
}
.container.full {
  margin-left: 70px;
}

/* title */
.page-title {
  font-size: 32px;
  font-weight: bold;
  color: #610C27;
  margin-bottom: 5px;
}

/* tabs */
.tabs button {
  padding: 10px;
  border: none;
  background: none;
  cursor: pointer;
  border-bottom: 2px solid transparent;
}
.tabs button.active {
  border-bottom: 2px solid #a61b4a;
  color: #a61b4a;
  font-weight: bold;
}

/* card */
.card {
  background: white;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

/* table */
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 12px;
  border-bottom: 1px solid #ddd;
  text-align: left;
}
th {
  background: #f9dbe5;
  font-size: 13px;
  color: #610C27;
}

/* badges */
.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
}
.success { background: #d1fae5; color: #065f46; }
.warning { background: #fef3c7; color: #92400e; }
.danger { background: #fee2e2; color: #991b1b; }

.application-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: bold;
}
.badge-upgrade { background: #dcfce7; color: #166534; }
.badge-pure { background: #fef3c7; color: #92400e; }

/* buttons */
.btn {
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  border: 1px solid #ccc;
  font-size: 14px;
}
.btn-primary { background: #a61b4a; color: white; border: none; }
.btn-danger { background: #dc3545; color: white; border: none; }
.btn-view { background: #EFECE9; color: #333; }
.btn-approve { background: #610C27; color: white; border: none; }
.btn-reject { background: #fee2e2; color: #991b1b; border: none; }

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 16px;
    width: 600px;
    position: relative;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: translateY(0);
    transition: transform 0.3s ease;
}

.confirm-modal .modal-content {
    width: 450px;
    margin: 10% auto;
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    position: sticky;
    top: 0;
    z-index: 10;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

.modal-body {
    padding: 24px;
    color: #4b5563;
    line-height: 1.6;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #f3f4f6;
    text-align: right;
    background: #f9fafb;
    border-bottom-left-radius: 16px;
    border-bottom-right-radius: 16px;
}

.close-btn {
    font-size: 1.5rem;
    cursor: pointer;
    color: #9ca3af;
    transition: color 0.2s;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.close-btn:hover {
    color: #4b5563;
    background: #f3f4f6;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #610C27;
    margin-top: 20px;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid #f9dbe5;
}

.image-preview {
    max-width: 100%;
    height: auto;
    margin: 12px 0;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: transform 0.2s;
}

.image-preview:hover {
    transform: scale(1.02);
}

.info-box {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 16px;
    margin: 16px 0;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #1e40af;
}

.confirm-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 24px;
    padding-bottom: 8px;
}

.confirm-buttons .btn {
    padding: 10px 24px;
    font-weight: 600;
    font-size: 0.875rem;
    border-radius: 8px;
    transition: all 0.2s;
}

.confirm-buttons .btn-primary:hover { background: #8e153f; transform: translateY(-1px); }
.confirm-buttons .btn-danger:hover { background: #c82333; transform: translateY(-1px); }
.confirm-buttons .btn-view:hover { background: #e5e7eb; transform: translateY(-1px); }

.success-popup {
    position: fixed;
    top: 24px;
    right: 24px;
    background: #10b981;
    color: white;
    padding: 12px 24px;
    border-radius: 12px;
    z-index: 2000;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    font-weight: 600;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
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
  <a href="admin_auditlogs.php" class="<?php echo $current_page == 'admin_auditlogs.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i><span class="text">Audit Logs</span></a>
  <a href="admin_orders.php" class="<?php echo $current_page == 'admin_orders.php' ? 'active' : ''; ?>"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="admin_reports.php" class="<?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  
  <a href="admin_settings.php" class="<?php echo $current_page == 'admin_settings.php' ? 'active' : ''; ?>"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  
  <a href="logout.php" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
</div>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const main = document.getElementById("main");
  if (sidebar) sidebar.classList.toggle("collapsed");
  if (main) main.classList.toggle("full");
}
</script>

<!-- MAIN -->
<div class="container" id="main">

<h1 class="page-title">User Management</h1>
<p>Manage all platform users, roles, and permissions.</p>

<!-- TABS -->
<div class="tabs mb-4">
  <button class="active" onclick="showTab('users')">All Users</button>
  <button onclick="showTab('pending')">Pending Sellers</button>
</div>

<!-- USERS TAB -->
<div id="users" class="tab-content">

<div class="card" style="display: flex; justify-content: space-between; align-items: center;">
  <div style="flex-grow: 1; margin-right: 15px;">
    <input type="text" id="userSearch" placeholder="Search users by name or email..." style="width:60%; padding:8px;" onkeyup="filterUsers()">
    <select id="roleFilter" style="padding:8px;" onchange="filterUsers()">
      <option value="All">All Roles</option>
      <?php foreach ($roles as $r): ?>
          <option value="<?php echo htmlspecialchars($r['role_name']); ?>"><?php echo htmlspecialchars($r['role_name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-approve" onclick="openModal('addLogisticModal')" style="background: #2b5e2f; color: white; padding: 10px 20px;">
    <i class="fas fa-plus"></i> Add Logistic Partner
  </button>
</div>

<div class="card">
<table id="usersTable">
<thead>
<tr>
  <th>User Details</th>
  <th>Role</th>
  <th>Status</th>
  <th>Login Attempts</th>
  <th>Actions</th>
</tr>
</thead>

<tbody>
<?php foreach ($users as $u): ?>
<tr class="user-row" data-role="<?php echo htmlspecialchars($u['role_name']); ?>">
  <td class="search-data">
    <strong><?php echo htmlspecialchars($u['username']); ?></strong><br>
    <small><?php echo htmlspecialchars($u['email']); ?></small>
  </td>
  <td><?php echo htmlspecialchars($u['role_name']); ?></td>
  <td>
    <?php if ($u['is_locked']): ?>
        <span class="badge danger">Locked</span>
    <?php else: ?>
        <span class="badge success">Active</span>
    <?php endif; ?>
  </td>
  <td><?php echo $u['attempts']; ?></td>
  <td>
    <?php if ($u['is_locked']): ?>
        <button class="btn btn-primary" onclick="confirmAction('unlock', <?php echo $u['user_id']; ?>, '<?php echo $u['username']; ?>')">Unlock</button>
    <?php else: ?>
        <button class="btn btn-danger" onclick="confirmAction('lock', <?php echo $u['user_id']; ?>, '<?php echo $u['username']; ?>')">Lock</button>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</div>

<!-- PENDING TAB (logic from admin_approvals.php) -->
<div id="pending" class="tab-content" style="display:none;">

<div class="card">
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
<?php if (empty($pendingSellers)): ?>
    <tr><td colspan="6" style="text-align:center;">No pending seller applications.</td></tr>
<?php else: ?>
    <?php foreach ($pendingSellers as $s): 
        $meta = json_decode($s['shop_description'], true);
        $hasCustomer = (int) $s['has_customer'] > 0;
        $isUpgrade = ($hasCustomer || $s['role_id'] == 2);
        
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
      <td><?php echo htmlspecialchars($s['full_name'] ?? ($meta['full_name'] ?? $s['username'])); ?></td>
      <td><?php echo htmlspecialchars($s['shop_name']); ?></td>
      <td><span class="application-badge <?php echo $appBadge; ?>"><?php echo $appType; ?></span></td>
      <td><?php echo $s['role_id'] == 2 ? 'Customer' : 'New'; ?></td>
      <td><strong><?php echo $willBecome; ?></strong></td>
      <td>
        <button class="btn btn-view" onclick="openSellerView(<?php echo $s['seller_id']; ?>)">
          <i class="fas fa-eye"></i> View
        </button>
        <button class="btn btn-approve" onclick="showSellerApproveConfirm(<?php echo $s['seller_id']; ?>)">
          <i class="fas fa-check"></i> Approve
        </button>
        <button class="btn btn-reject" onclick="showSellerRejectConfirm(<?php echo $s['seller_id']; ?>)">
          <i class="fas fa-times"></i> Reject
        </button>
      </td>
    </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>

</div>

</div>

<!-- Confirmation Modal (Lock/Unlock) -->
<div id="confirmModal" class="modal confirm-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Confirm Action</h2>
            <span class="close-btn" onclick="closeModal('confirmModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalIcon" style="width: 60px; height: 60px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div style="text-align: center;">
                <p id="modalText" style="font-size: 1.1rem; color: #374151; margin-bottom: 10px;"></p>
                <p style="color: #6b7280; font-size: 0.875rem;">This action will take effect immediately.</p>
            </div>
            <form id="confirmForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="user_id" id="modalUserId">
                <input type="hidden" name="action" id="modalAction">
                <div class="confirm-buttons">
                    <button type="button" class="btn btn-view" onclick="closeModal('confirmModal')">Cancel</button>
                    <button type="submit" class="btn" id="modalSubmitBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Seller View Modal -->
<div id="sellerViewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-store"></i> Seller Application Details</h2>
            <span class="close-btn" onclick="closeModal('sellerViewModal')">&times;</span>
        </div>
        <div class="modal-body" id="sellerModalBody">Loading...</div>
        <div class="modal-footer">
            <button class="btn btn-view" onclick="closeModal('sellerViewModal')">Close</button>
        </div>
    </div>
</div>

<!-- Seller Approve Modal -->
<div id="sellerApproveModal" class="modal confirm-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Approval</h2>
            <span class="close-btn" onclick="closeModal('sellerApproveModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div id="sellerApproveModalBody"></div>
            <div class="confirm-buttons">
                <button class="btn btn-view" onclick="closeModal('sellerApproveModal')">Cancel</button>
                <button class="btn btn-approve" id="confirmSellerApproveBtn" style="background:#610C27; color:white;">Yes, Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- Seller Reject Modal -->
<div id="sellerRejectModal" class="modal confirm-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Rejection</h2>
            <span class="close-btn" onclick="closeModal('sellerRejectModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div style="width: 60px; height: 60px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; background: #fee2e2; color: #dc2626;">
                <i class="fas fa-times-circle"></i>
            </div>
            <div style="text-align: center; margin-bottom: 25px;">
                <h3 style="font-size: 20px; margin-bottom: 10px; color: #333;">Reject Seller Application?</h3>
                <p style="color: #666; font-size: 14px;">Are you sure you want to reject this seller? An email will be sent to inform them.</p>
            </div>
            <div class="confirm-buttons">
                <button class="btn btn-view" onclick="closeModal('sellerRejectModal')">Cancel</button>
                <button class="btn btn-danger" id="confirmSellerRejectBtn">Yes, Reject</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Logistic Partner Modal -->
<div id="addLogisticModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-truck"></i> Add Logistic Partner</h2>
            <span class="close-btn" onclick="closeModal('addLogisticModal')">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div class="modal-body">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Username</label>
                    <input type="text" name="username" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Email Address</label>
                    <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <p style="color: #666; font-size: 0.85rem; background: #f9f9f9; padding: 10px; border-radius: 5px;">
                    <i class="fas fa-info-circle"></i> A temporary password will be generated and sent to the email provided. The account will be pre-activated.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-view" onclick="closeModal('addLogisticModal')">Cancel</button>
                <button type="submit" name="add_logistic" class="btn btn-approve" style="background: #2b5e2f; color: white;">Add Partner</button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPTS -->
<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

let currentSellerId = null;

function showTab(tab) {
    document.getElementById('users').style.display = 'none';
    document.getElementById('pending').style.display = 'none';
    document.getElementById(tab).style.display = 'block';
    
    document.querySelectorAll('.tabs button').forEach(btn => btn.classList.remove('active'));
    if (tab === 'users') {
        document.querySelector('.tabs button:nth-child(1)').classList.add('active');
    } else {
        document.querySelector('.tabs button:nth-child(2)').classList.add('active');
    }
}

function filterUsers() {
    const searchValue = document.getElementById('userSearch').value.toLowerCase();
    const roleValue = document.getElementById('roleFilter').value;
    const rows = document.querySelectorAll('.user-row');

    rows.forEach(row => {
        const text = row.querySelector('.search-data').innerText.toLowerCase();
        const role = row.getAttribute('data-role');
        const matchesSearch = text.includes(searchValue);
        const matchesRole = roleValue === 'All' || role === roleValue;
        row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
    });
}

// User Lock/Unlock Modal
function confirmAction(action, userId, username) {
    const modalTitle = document.getElementById('modalTitle');
    const modalText = document.getElementById('modalText');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const modalIcon = document.getElementById('modalIcon');
    
    document.getElementById('modalUserId').value = userId;
    document.getElementById('modalAction').value = action;
    
    if (action === 'lock') {
        modalTitle.innerText = 'Lock User Account';
        modalText.innerText = `Are you sure you want to lock ${username}'s account?`;
        modalSubmitBtn.innerText = 'Lock Account';
        modalSubmitBtn.className = 'btn btn-danger';
        modalIcon.style.background = '#fee2e2';
        modalIcon.style.color = '#dc2626';
        modalIcon.innerHTML = '<i class="fas fa-user-lock"></i>';
    } else {
        modalTitle.innerText = 'Unlock User Account';
        modalText.innerText = `Are you sure you want to unlock ${username}'s account?`;
        modalSubmitBtn.innerText = 'Unlock Account';
        modalSubmitBtn.className = 'btn btn-primary';
        modalIcon.style.background = '#dcfce7';
        modalIcon.style.color = '#16a34a';
        modalIcon.innerHTML = '<i class="fas fa-user-check"></i>';
    }
    
    document.getElementById('confirmModal').style.display = 'block';
}

// Seller Management (logic from admin_approvals.php)
function openSellerView(sellerId) {
    document.getElementById('sellerModalBody').innerHTML = '<div style="text-align:center; padding:20px; color:#610C27;">Loading...</div>';
    document.getElementById('sellerViewModal').style.display = 'block';
    
    fetch(`admin_users.php?get_seller_details=1&id=${sellerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const meta = data.metadata;
                let html = `
                    <div class="info-box" style="margin-top: 0;">
                        <strong>Application Type:</strong> ${data.application_type}<br>
                        <strong>Will become:</strong> ${data.will_become}
                    </div>
                    
                    <div class="section-title">Personal Information</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <p><strong>Name:</strong><br>${escapeHtml(data.full_name)}</p>
                        <p><strong>Email:</strong><br>${escapeHtml(data.email)}</p>
                        <p><strong>Contact:</strong><br>${escapeHtml(data.contact_number)}</p>
                        <p><strong>Age:</strong><br>${escapeHtml(meta.age || 'N/A')}</p>
                    </div>
                    <p><strong>TIN ID:</strong> ${escapeHtml(meta.tin_id || 'N/A')}</p>
                    
                    <div class="section-title">Business Information</div>
                    <p><strong>Shop Name:</strong> ${escapeHtml(data.shop_name)}</p>
                    <p><strong>Shop Address:</strong> ${escapeHtml(data.shop_address)}</p>
                    <p><strong>Category:</strong> ${escapeHtml(meta.business_category || 'N/A')}</p>
                    
                    <div class="section-title">Documents</div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px;">
                `;
                
                if (meta.business_permit_picture) {
                    html += `<div><strong>Business Permit:</strong><br><img src="${escapeHtml(meta.business_permit_picture)}" class="image-preview" onclick="window.open(this.src)"></div>`;
                }
                if (meta.valid_id_picture) {
                    html += `<div><strong>Valid ID:</strong><br><img src="${escapeHtml(meta.valid_id_picture)}" class="image-preview" onclick="window.open(this.src)"></div>`;
                }
                if (meta.shop_image) {
                    html += `<div><strong>Shop Image:</strong><br><img src="${escapeHtml(meta.shop_image)}" class="image-preview" onclick="window.open(this.src)"></div>`;
                }
                
                html += `</div>`;
                
                document.getElementById('sellerModalBody').innerHTML = html;
            } else {
                document.getElementById('sellerModalBody').innerHTML = `<div style="text-align:center; padding:40px; color:#991b1b;">Error: ${escapeHtml(data.message)}</div>`;
            }
        });
}

function showSellerApproveConfirm(sellerId) {
    currentSellerId = sellerId;
    
    fetch(`admin_users.php?get_seller_details=1&id=${sellerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalBody = document.getElementById('sellerApproveModalBody');
                modalBody.innerHTML = `
                    <div style="width: 60px; height: 60px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; background: #dcfce7; color: #16a34a;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div style="text-align: center; margin-bottom: 25px;">
                        <h3 style="font-size: 20px; margin-bottom: 10px; color: #333;">Approve Seller Application?</h3>
                        <p><strong>${data.application_type}</strong></p>
                        <p>This seller will become: <strong>${data.will_become}</strong></p>
                        <div style="background:#dbeafe; border-left:4px solid #3b82f6; padding:12px; margin-top:15px; border-radius:8px; text-align:left;">
                            <i class="fas fa-info-circle"></i> An email will be sent to the seller with their credentials.
                        </div>
                    </div>
                `;
                document.getElementById('sellerApproveModal').style.display = 'block';
            }
        });
}

function showSellerRejectConfirm(sellerId) {
    currentSellerId = sellerId;
    document.getElementById('sellerRejectModal').style.display = 'block';
}

document.getElementById('confirmSellerApproveBtn')?.addEventListener('click', function() {
    if (currentSellerId) {
        closeModal('sellerApproveModal');
        processSellerAction(currentSellerId, 'approve');
    }
});

document.getElementById('confirmSellerRejectBtn')?.addEventListener('click', function() {
    if (currentSellerId) {
        closeModal('sellerRejectModal');
        processSellerAction(currentSellerId, 'reject');
    }
});

function processSellerAction(sellerId, action) {
    showSuccessPopup(`Processing ${action}...`);
    
    fetch(`admin_users.php?seller_action=${action}&id=${sellerId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        showSuccessPopup(data.message);
        setTimeout(() => location.reload(), 2000);
    })
    .catch(() => showSuccessPopup(`Error processing ${action}`, 'error'));
}

function showSuccessPopup(message, type = 'success') {
    const popup = document.createElement('div');
    popup.className = 'success-popup';
    popup.style.background = type === 'error' ? '#dc2626' : '#10b981';
    popup.innerHTML = message;
    document.body.appendChild(popup);
    setTimeout(() => popup.remove(), 3000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

window.onclick = function(e) {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
}

// Handle messages from URL
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    const error = urlParams.get('error');

    if (msg === 'logistic_success') {
        showSuccessPopup('Logistic Partner added successfully!');
    } else if (msg === 'logistic_created_email_failed') {
        showSuccessPopup('Partner created, but failed to send email.', 'error');
    } else if (error === 'exists') {
        showSuccessPopup('Username or Email already exists!', 'error');
    } else if (error === 'failed') {
        showSuccessPopup('Database error. Please try again.', 'error');
    } else if (error === 'empty_fields') {
        showSuccessPopup('Please fill all fields.', 'error');
    } else if (msg === 'success') {
        showSuccessPopup('Action completed successfully!');
    }
}
</script>

</body>
</html>