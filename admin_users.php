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
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
} else {
  $_SESSION['last_activity'] = time();
}

$timeout_ms = $timeout_minutes * 60 * 1000;

// Number of items per page
$items_per_page = 10;

// Get current page
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// Function to build pagination URL
function buildUsersPaginationUrl($page) {
    return '?page=' . $page;
}

// Function to render pagination
function renderUsersPagination($current_page, $total_pages) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<a href="' . buildUsersPaginationUrl($current_page - 1) . '">&laquo; Previous</a>';
    } else {
        $html .= '<span class="disabled">&laquo; Previous</span>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . buildUsersPaginationUrl($i) . '">' . $i . '</a>';
        }
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $html .= '<a href="' . buildUsersPaginationUrl($current_page + 1) . '">Next &raquo;</a>';
    } else {
        $html .= '<span class="disabled">Next &raquo;</span>';
    }
    
    $html .= '</div>';
    return $html;
}

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

    if (empty($username) || empty($email)) {
        header("Location: admin_users.php?error=empty_fields");
        exit;
    }

    $checkStmt = $conn->prepare("SELECT user_id FROM user WHERE username = ? OR email = ?");
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        header("Location: admin_users.php?error=exists");
        exit;
    }

    $tempPassword = bin2hex(random_bytes(6));
    $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

    $empty = '';
    $empty_expiry = '0000-00-00 00:00:00';

    $insertStmt = $conn->prepare("
        INSERT INTO user (
            username, email, password, role_id, is_activated, is_active,
            otp_code, otp_expiry, verification_token, token_expiry, mfa_secret
        ) VALUES (?, ?, ?, 5, 1, 1, ?, ?, ?, ?, ?)
    ");
    $insertStmt->bind_param("sssssssss", 
        $username, $email, $passwordHash, 
        $empty, $empty_expiry, $empty, $empty_expiry, $empty
    );
    
    if ($insertStmt->execute()) {
        $subject = "Welcome to J3RS Logistics Team!";
        $body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { padding: 20px; background-color: #fdf2f6; }
                    .content { background-color: white; padding: 20px; border-radius: 10px; }
                    .header { color: #610C27; font-size: 24px; margin-bottom: 20px; }
                    .button { background-color: #610C27; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='content'>
                        <div class='header'>Welcome to J3RS Logistics Team!</div>
                        <p>Dear " . htmlspecialchars($username) . ",</p>
                        <p>Your account as a Logistic Partner has been created by the administrator.</p>
                        <p>You can now log in to your account using your registered credentials.</p>
                        <p><a href='http://localhost/INFO-ASSURANCE-GRP3/login.php' class='button'>Login to Your Account</a></p>
                        <p>Best regards,<br><strong>J3RS Marketplace Team</strong></p>
                    </div>
                </div>
            </body>
            </html>
        ";
        $emailSent = send_email($email, $subject, $body);

        log_audit_action('create', 'Logistic Partners', "Admin created logistic partner account for user: $username");
        
        if ($emailSent) {
            header("Location: admin_users.php?msg=logistic_success");
        } else {
            header("Location: admin_users.php?msg=logistic_created_email_failed");
        }
    } else {
        error_log("Database error: " . $insertStmt->error);
        header("Location: admin_users.php?error=failed");
    }
    exit;
}

/* =========================
   HANDLE APPROVE / REJECT - FIXED VERSION
========================= */
if (isset($_GET['seller_action'], $_GET['id'])) {
    $sellerId = (int) $_GET['id'];
    $action = $_GET['seller_action'];

    if ($action === 'approve') {
        $sellerStmt = $conn->prepare("
            SELECT s.*, u.user_id, u.username, u.email, u.role_id, u.password, u.first_name, u.last_name,
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
            
            $newRole = null;
            $roleType = '';
            $isPureSeller = false;
            
            // Determine if this is a pure seller or dual role
            if ($hasCustomerRecord || $currentRoleId == 2) {
                $newRole = 4;
                $roleType = 'Dual (Customer & Seller)';
                $isPureSeller = false;
            } else {
                $newRole = 3;
                $roleType = 'Seller Only';
                $isPureSeller = true;
            }
            
            // Generate temporary password for pure sellers
            $tempPassword = null;
            $passwordHash = null;
            $newUsername = null;
            
            if ($isPureSeller) {
                // Generate a secure random password (12 characters for better security)
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
                $tempPassword = '';
                for ($i = 0; $i < 12; $i++) {
                    $tempPassword .= $chars[random_int(0, strlen($chars) - 1)];
                }
                $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
                
                // Generate username from email or full name
                if (!empty($seller['email'])) {
                    $baseUsername = preg_replace('/@.*$/', '', $seller['email']);
                    $baseUsername = preg_replace('/[^a-z0-9]/i', '_', $baseUsername);
                } else {
                    $baseUsername = preg_replace('/[^a-z0-9]/i', '_', strtolower($seller['shop_name']));
                }
                
                // Ensure username is unique
                $newUsername = $baseUsername;
                $counter = 1;
                $checkStmt = $conn->prepare("SELECT user_id FROM user WHERE username = ?");
                $checkStmt->bind_param("s", $newUsername);
                $checkStmt->execute();
                while ($checkStmt->get_result()->num_rows > 0) {
                    $newUsername = $baseUsername . '_' . $counter;
                    $checkStmt->bind_param("s", $newUsername);
                    $checkStmt->execute();
                    $counter++;
                }
                
                // Debug: Log the generated password
                error_log("Generated temp password for seller ID {$sellerId}: {$tempPassword}");
                error_log("Generated username: {$newUsername}");
                error_log("Password hash: " . substr($passwordHash, 0, 20) . "...");
            }
            
            // Update seller approval status
            $stmt = $conn->prepare("UPDATE seller SET is_approved = 1 WHERE seller_id = ?");
            $stmt->bind_param("i", $sellerId);
            $stmt->execute();
            
            // Split full name into first and last name if available
            $firstName = null;
            $lastName = null;
            if (!empty($seller['full_name'])) {
                $nameParts = explode(' ', $seller['full_name'], 2);
                $firstName = $nameParts[0];
                $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
            } else if (!empty($seller['first_name'])) {
                $firstName = $seller['first_name'];
                $lastName = $seller['last_name'] ?? '';
            }
            
            // Update user record - FIXED: Ensure proper field updates
            if ($isPureSeller && $passwordHash && $newUsername) {
                // For pure sellers: Update all necessary fields
                if ($firstName && $lastName) {
                    $updateUserStmt = $conn->prepare("
                        UPDATE user 
                        SET role_id = ?, 
                            username = ?, 
                            password = ?, 
                            first_name = ?, 
                            last_name = ?,
                            is_activated = 1,
                            attempts = 0,
                            is_locked = 0
                        WHERE user_id = ?
                    ");
                    $updateUserStmt->bind_param("issssi", $newRole, $newUsername, $passwordHash, $firstName, $lastName, $seller['user_id']);
                } else {
                    $updateUserStmt = $conn->prepare("
                        UPDATE user 
                        SET role_id = ?, 
                            username = ?, 
                            password = ?,
                            is_activated = 1,
                            attempts = 0,
                            is_locked = 0
                        WHERE user_id = ?
                    ");
                    $updateUserStmt->bind_param("issi", $newRole, $newUsername, $passwordHash, $seller['user_id']);
                }
                
                if ($updateUserStmt->execute()) {
                    error_log("Successfully updated user ID {$seller['user_id']} with new role {$newRole}, username {$newUsername}");
                    
                    // Verify the password was saved correctly
                    $verifyStmt = $conn->prepare("SELECT password FROM user WHERE user_id = ?");
                    $verifyStmt->bind_param("i", $seller['user_id']);
                    $verifyStmt->execute();
                    $verifyResult = $verifyStmt->get_result()->fetch_assoc();
                    
                    if ($verifyResult && password_verify($tempPassword, $verifyResult['password'])) {
                        error_log("Password verification SUCCESS for user ID {$seller['user_id']}");
                    } else {
                        error_log("Password verification FAILED for user ID {$seller['user_id']}");
                    }
                } else {
                    error_log("Failed to update user: " . $updateUserStmt->error);
                }
            } elseif ($newRole && $currentRoleId != $newRole) {
                // For dual role: Only update role
                $updateUserStmt = $conn->prepare("UPDATE user SET role_id = ?, is_activated = 1 WHERE user_id = ?");
                $updateUserStmt->bind_param("ii", $newRole, $seller['user_id']);
                $updateUserStmt->execute();
            }
            
            // Create customer record for pure sellers (if needed)
            if ($isPureSeller && !$hasCustomerRecord) {
                $fullName = $seller['full_name'] ?? ($firstName && $lastName ? $firstName . ' ' . $lastName : $seller['shop_name']);
                $contactNumber = !empty($seller['contact_number']) ? $seller['contact_number'] : '';
                
                $insertCustomerStmt = $conn->prepare("
                    INSERT INTO customer (user_id, full_name, contact_number, address_line, city, region, postal_code)
                    VALUES (?, ?, ?, '', '', '', '')
                ");
                $insertCustomerStmt->bind_param("iss", $seller['user_id'], $fullName, $contactNumber);
                $insertCustomerStmt->execute();
            }
            
            // Send appropriate email with login credentials
            if ($isPureSeller) {
                // For pure sellers: include username, temporary password, and login link
                $emailBody = getSellerOnlyEmail($seller, $newUsername, $tempPassword);
                $subject = "Welcome to J3RS Marketplace - Your Seller Account Has Been Approved!";
            } else {
                // For dual role: just confirmation email
                $emailBody = getDualRoleEmail($seller);
                $subject = "Your Seller Application Has Been Approved - Account Upgraded!";
            }
            
            $emailSent = send_email($seller['email'], $subject, $emailBody);
            
            if (!$emailSent) {
                error_log("Failed to send email to {$seller['email']}");
            }
            
            log_audit_action('approve', 'Seller Applications', "Admin approved seller application for shop: " . $seller['shop_name']);
            
            // Create notification
            $notifStmt = $conn->prepare("INSERT INTO notification (user_id, title, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
            if ($roleType == 'Dual (Customer & Seller)') {
                $notifTitle = "Seller Application Approved - Account Upgraded to Dual Role";
                $notifMessage = "Congratulations! Your account has been upgraded to Dual Role. You can now switch between Customer and Seller modes using the 'Switch Role' button.";
            } else {
                $notifTitle = "Seller Application Approved - Account Created";
                $notifMessage = "Congratulations! Your seller account has been approved. Your login credentials have been sent to your email.\n\nUsername: {$newUsername}\nTemporary Password: {$tempPassword}\n\nPlease login and change your password immediately.";
            }
            $notifStmt->bind_param("iss", $seller['user_id'], $notifTitle, $notifMessage);
            $notifStmt->execute();
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => "Seller approved successfully as $roleType"]);
            exit;
        } else {
            header("Location: admin_users.php?msg=approve_success");
            exit;
        }
    }

    if ($action === 'reject') {
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
            $subject = "Seller Application Update - J3RS Marketplace";
            $body = getRejectionEmail($seller);
            send_email($seller['email'], $subject, $body);
            
            $notifStmt = $conn->prepare("
                INSERT INTO notification (user_id, title, message, is_read, created_at) 
                VALUES (?, ?, ?, 0, NOW())
            ");
            $notifTitle = "Seller Application Not Approved";
            $notifMessage = "We regret to inform you that your seller application was not approved. Please check your email for more details.";
            $notifStmt->bind_param("iss", $seller['user_id'], $notifTitle, $notifMessage);
            $notifStmt->execute();
            
            log_audit_action('reject', 'Seller Applications', "Admin rejected seller application for shop: " . $seller['shop_name']);
            
            $stmt = $conn->prepare("DELETE FROM seller WHERE seller_id = ?");
            $stmt->bind_param("i", $sellerId);
            $stmt->execute();
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => 'Seller rejected successfully']);
            exit;
        } else {
            header("Location: admin_users.php?msg=reject_success");
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
        $decryptedContact = !empty($seller['contact_number']) ? decrypt_data($seller['contact_number']) : '';
        $decryptedTinId = !empty($seller['tin_id']) ? decrypt_data($seller['tin_id']) : '';
        
        $hasCustomer = (int) $seller['has_customer'] > 0;
        
        if ($hasCustomer || $seller['role_id'] == 2) {
            $applicationType = "Customer Upgrading to Dual Role";
            $willBecome = "Dual Role (Customer + Seller) - Role ID: 4";
        } else {
            $applicationType = "Pure Seller Registration";
            $willBecome = "Seller Only - Role ID: 3";
        }
        
        echo json_encode([
            'success' => true,
            'full_name' => $seller['full_name'] ?? $seller['username'] ?? '',
            'email' => $seller['email'],
            'contact_number' => $decryptedContact,
            'age' => $seller['age'],
            'tin_id' => $decryptedTinId,
            'shop_name' => $seller['shop_name'],
            'shop_address' => $seller['shop_address'],
            'shop_description' => $seller['shop_description'],
            'business_category' => $seller['business_category'],
            'additional_info' => $seller['additional_info'],
            'business_permit' => $seller['business_permit'],
            'valid_id' => $seller['valid_id'],
            'shop_image' => $seller['shop_image'],
            'application_type' => $applicationType,
            'will_become' => $willBecome,
            'created_at' => $seller['created_at']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Seller not found']);
    }
    exit;
}

// Email helper functions - FIXED VERSION
function getDualRoleEmail($seller) {
    return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { padding: 20px; background-color: #fdf2f6; }
                .content { background-color: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto; }
                .header { color: #610C27; font-size: 24px; margin-bottom: 20px; }
                .upgrade-badge { background-color: #10b981; color: white; padding: 5px 10px; border-radius: 5px; display: inline-block; font-size: 12px; margin-left: 10px; }
                .button { background-color: #610C27; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .feature-list { margin: 15px 0; padding-left: 20px; }
                .feature-list li { margin: 8px 0; }
                .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='content'>
                    <div class='header'>
                        Seller Application Approved! 
                        <span class='upgrade-badge'>Role Upgraded to Dual</span>
                    </div>
                    <p>Dear " . htmlspecialchars($seller['shop_name']) . ",</p>
                    <p>Congratulations! Your seller application has been approved by our admin team.</p>
                    
                    <div style='background:#dcfce7; padding:15px; border-radius:8px; margin:15px 0;'>
                        <p style='margin:0;'><strong>✅ Your account has been upgraded from Customer to Dual Role (Customer + Seller)</strong></p>
                    </div>
                    
                    <p><strong>What's New with Your Dual Role Account:</strong></p>
                    <ul class='feature-list'>
                        <li>🔄 <strong>Role Switching:</strong> Switch between Customer and Seller views</li>
                        <li>🏪 <strong>Sell Products:</strong> Start listing your products</li>
                        <li>📦 <strong>Manage Orders:</strong> Process customer orders</li>
                        <li>🛍️ <strong>Continue Shopping:</strong> Shop as a customer too</li>
                    </ul>
                    
                    <p><strong>How to Access Your Seller Features:</strong></p>
                    <ol>
                        <li>Log in to your account as usual using your existing credentials</li>
                        <li>Look for the 'Switch Role' button in your dashboard</li>
                        <li>Toggle to 'Seller Mode' to access your seller dashboard</li>
                    </ol>
                    
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='http://localhost/INFO-ASSURANCE-GRP3/login.php' class='button'>
                            Login to Your Account
                        </a>
                    </div>
                    
                    <div class='footer'>
                        <p>Best regards,<br><strong>J3RS Marketplace Team</strong></p>
                    </div>
                </div>
            </div>
        </body>
        </html>
    ";
}

function getSellerOnlyEmail($seller, $username, $tempPassword) {
    return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { padding: 20px; background-color: #fdf2f6; }
                .content { background-color: white; padding: 25px; border-radius: 10px; max-width: 600px; margin: 0 auto; }
                .header { color: #610C27; font-size: 24px; margin-bottom: 20px; border-bottom: 2px solid #f9dbe5; padding-bottom: 10px; }
                .seller-badge { background-color: #f59e0b; color: white; padding: 5px 10px; border-radius: 5px; display: inline-block; font-size: 12px; margin-left: 10px; }
                .button { background-color: #610C27; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 15px 0; font-weight: bold; }
                .credentials-box { background: #fef3c7; padding: 20px; border-radius: 12px; margin: 20px 0; border-left: 4px solid #f59e0b; }
                .credential-item { margin: 12px 0; padding: 8px; background: white; border-radius: 8px; }
                .credential-label { font-weight: bold; color: #610C27; display: inline-block; width: 120px; }
                .credential-value { font-family: 'Courier New', monospace; font-size: 16px; color: #333; font-weight: bold; }
                .warning-box { background-color: #fee2e2; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc2626; }
                .feature-list { margin: 15px 0; padding-left: 20px; }
                .feature-list li { margin: 8px 0; }
                code { background: #f5f5f5; padding: 2px 6px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 14px; }
                .login-link { background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; }
                .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='content'>
                    <div class='header'>
                        🎉 Welcome to J3RS Marketplace!
                        <span class='seller-badge'>Seller Account</span>
                    </div>
                    
                    <p>Dear <strong>" . htmlspecialchars($seller['full_name'] ?? $seller['shop_name']) . "</strong>,</p>
                    
                    <p>Congratulations! Your seller application has been <strong>approved</strong> by our admin team. Your seller account has been successfully created.</p>
                    
                    <div class='credentials-box'>
                        <h3 style='margin: 0 0 15px 0; color: #92400e;'>📋 Your Account Credentials</h3>
                        
                        <div class='credential-item'>
                            <span class='credential-label'>👤 Username:</span>
                            <span class='credential-value'><code>" . htmlspecialchars($username) . "</code></span>
                        </div>
                        
                        <div class='credential-item'>
                            <span class='credential-label'>📧 Email:</span>
                            <span class='credential-value'>" . htmlspecialchars($seller['email']) . "</span>
                        </div>
                        
                        <div class='credential-item'>
                            <span class='credential-label'>🔐 Temporary Password:</span>
                            <span class='credential-value'><code style='font-size: 18px; padding: 8px 12px; background: #fff; border-radius: 6px; display: inline-block;'>" . htmlspecialchars($tempPassword) . "</code></span>
                        </div>
                    </div>
                    
                    <div class='warning-box'>
                        <strong>⚠️ Important Security Notice:</strong>
                        <ul style='margin: 10px 0 0 20px;'>
                            <li>This is a temporary password for first-time login only</li>
                            <li>You will be required to <strong>change your password immediately</strong> after logging in</li>
                            <li>Never share your password with anyone</li>
                            <li>For security, please use a strong, unique password</li>
                        </ul>
                    </div>
                    
                    <div class='login-link'>
                        <h3 style='margin: 0 0 10px 0;'>🔑 Click the button below to login:</h3>
                        <a href='http://localhost/INFO-ASSURANCE-GRP3/login.php' class='button'>
                            🚀 Login to Your Seller Account
                        </a>
                    </div>
                    
                    <h3>📝 How to Login:</h3>
                    <ol>
                        <li>Click the <strong>\"Login to Your Seller Account\"</strong> button above</li>
                        <li>Enter your <strong>Username</strong>: <code>" . htmlspecialchars($username) . "</code></li>
                        <li>Enter your <strong>Temporary Password</strong>: <code>" . htmlspecialchars($tempPassword) . "</code></li>
                        <li>Click <strong>Sign in</strong></li>
                        <li>You will be prompted to change your password on first login</li>
                        <li>After changing your password, you can start selling!</li>
                    </ol>
                    
                    <h3>✨ What You Can Do Now:</h3>
                    <ul class='feature-list'>
                        <li>🏪 <strong>Set Up Your Shop:</strong> Complete your shop profile and settings</li>
                        <li>📦 <strong>Add Products:</strong> Start listing your products for sale</li>
                        <li>📊 <strong>Dashboard Access:</strong> View your sales analytics and performance</li>
                        <li>💬 <strong>Customer Management:</strong> Communicate with your customers</li>
                        <li>🔐 <strong>Security:</strong> Change your password immediately after login</li>
                    </ul>
                    
                    <div class='footer'>
                        <p><strong>Need Help?</strong> If you have any issues logging in, please contact our support team at support@j3rs.com</p>
                        <p>Best regards,<br>
                        <strong>J3RS Marketplace Team</strong><br>
                        <small>Your Trusted Online Marketplace</small></p>
                    </div>
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
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { padding: 20px; background-color: #fdf2f6; }
                .content { background-color: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto; }
                .header { color: #dc2626; font-size: 24px; margin-bottom: 20px; }
                .message-box { background-color: #fee2e2; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc2626; }
                .button { background-color: #610C27; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
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
                        <p>After careful review, we regret to inform you that your seller application has not been approved at this time.</p>
                        <p>You may reapply after ensuring all requirements are properly met.</p>
                    </div>
                    <p>If you have any questions, please contact our support team.</p>
                    <p><a href='http://localhost/INFO-ASSURANCE-GRP3/contact.php' class='button'>Contact Support</a></p>
                    <div class='footer'>
                        <p>Best regards,<br><strong>J3RS Marketplace Team</strong></p>
                    </div>
                </div>
            </div>
        </body>
        </html>
    ";
}

/* =========================
   FETCH DATA
========================= */
// Count total users
$countUsersStmt = $conn->query("SELECT COUNT(*) as total FROM user");
$total_users = $countUsersStmt->fetch_assoc()['total'];
$total_pages = ceil($total_users / $items_per_page);

// Fetch All Users with pagination
$usersStmt = $conn->prepare("
    SELECT u.user_id, u.username, u.email, u.is_locked, u.attempts, r.role_name
    FROM user u 
    JOIN role r ON u.role_id = r.role_id
    ORDER BY u.user_id DESC
    LIMIT ? OFFSET ?
");
$usersStmt->bind_param("ii", $items_per_page, $offset);
$usersStmt->execute();

if (!$usersStmt) {
    die("Error fetching users: " . $conn->error);
}
$users = $usersStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Pending Sellers
$pendingQuery = "
    SELECT s.*, u.username, u.email, u.role_id, u.first_name, u.last_name,
           (SELECT COUNT(*) FROM customer WHERE user_id = u.user_id) as has_customer
    FROM seller s
    JOIN user u ON s.user_id = u.user_id
    WHERE s.is_approved = 0 OR s.is_approved IS NULL
    ORDER BY s.created_at DESC
";

$pendingStmt = $conn->query($pendingQuery);

if (!$pendingStmt) {
    die("Error fetching pending sellers: " . $conn->error);
}

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

.page-title {
  font-size: 32px;
  font-weight: bold;
  color: #610C27;
  margin-bottom: 5px;
}

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

.card {
  background: white;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

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

.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
}
.success { background: #d1fae5; color: #065f46; }
.warning { background: #fef3c7; color: #92400e; }
.danger { background: #fee2e2; color: #991b1b; }

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.pagination a, .pagination span {
  padding: 8px 14px;
  border: 1px solid #ddd;
  border-radius: 6px;
  text-decoration: none;
  color: #610C27;
  font-size: 14px;
  transition: all 0.2s;
}

.pagination a:hover {
  background: #f9dbe5;
  border-color: #a61b4a;
}

.pagination .active {
  background: #a61b4a;
  color: white;
  border-color: #a61b4a;
}

.pagination .disabled {
  color: #999;
  cursor: not-allowed;
  background: #f5f5f5;
}

.pagination-info {
  text-align: center;
  color: #666;
  font-size: 13px;
  margin-top: 10px;
}

.application-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: bold;
}
.badge-upgrade { background: #dcfce7; color: #166534; }
.badge-pure { background: #fef3c7; color: #92400e; }

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
    width: 650px;
    position: relative;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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

.document-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.document-card {
    background: #f9fafb;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    border: 1px solid #e5e7eb;
}

.document-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
}

.document-card p {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}
</style>
</head>

<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
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

<div class="container" id="main">

<h1 class="page-title">User Management</h1>
<p>Manage all platform users, roles, and permissions.</p>

<div class="tabs mb-4">
  <button class="active" onclick="showTab('users')">All Users</button>
  <button onclick="showTab('pending')">Pending Sellers <?php echo count($pendingSellers) > 0 ? '<span style="background:#dc2626; color:white; padding:2px 8px; border-radius:12px; margin-left:5px; font-size:12px;">'.count($pendingSellers).'</span>' : ''; ?></button>
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
    <?php echo renderUsersPagination($page, $total_pages); ?>
    <div class="pagination-info">
        Showing page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_users; ?> total users)
    </div>
</div>

</div>

<!-- PENDING TAB -->
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
    <tr><td colspan="6" style="text-align:center; padding:40px;">No pending seller applications.</td></tr>
<?php else: ?>
    <?php foreach ($pendingSellers as $s): 
        $hasCustomer = (int) ($s['has_customer'] ?? 0);
        $isUpgrade = ($hasCustomer || ($s['role_id'] ?? 0) == 2);
        
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
      <td><?php echo htmlspecialchars($s['full_name'] ?? $s['username'] ?? 'N/A'); ?></td>
      <td><?php echo htmlspecialchars($s['shop_name'] ?? 'N/A'); ?></td>
      <td><span class="application-badge <?php echo $appBadge; ?>"><?php echo $appType; ?></span></td>
      <td><?php echo ($s['role_id'] ?? 0) == 2 ? 'Customer' : 'New'; ?></td>
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

<!-- Modals (same as before - keeping them for functionality) -->
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

<div id="sellerViewModal" class="modal">
    <div class="modal-content" style="width: 750px;">
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
                    <i class="fas fa-info-circle"></i> A confirmation email will be sent to the provided email address.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-view" onclick="closeModal('addLogisticModal')">Cancel</button>
                <button type="submit" name="add_logistic" class="btn btn-approve" style="background: #2b5e2f; color: white;">Add Partner</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
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

function openSellerView(sellerId) {
    document.getElementById('sellerModalBody').innerHTML = '<div style="text-align:center; padding:20px; color:#610C27;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    document.getElementById('sellerViewModal').style.display = 'block';
    
    fetch(`admin_users.php?get_seller_details=1&id=${sellerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div class="info-box" style="margin-top: 0;">
                        <strong><i class="fas fa-info-circle"></i> Application Type:</strong> ${escapeHtml(data.application_type)}<br>
                        <strong><i class="fas fa-exchange-alt"></i> Will become:</strong> ${escapeHtml(data.will_become)}<br>
                        <strong><i class="far fa-calendar-alt"></i> Submitted:</strong> ${escapeHtml(data.created_at)}
                    </div>
                    
                    <div class="section-title">
                        <i class="fas fa-user-circle"></i> Personal Information
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; background: #f9fafb; padding: 15px; border-radius: 12px;">
                        <div><strong>Full Name:</strong><br>${escapeHtml(data.full_name)}</div>
                        <div><strong>Email Address:</strong><br>${escapeHtml(data.email)}</div>
                        <div><strong>Contact Number:</strong><br>${escapeHtml(data.contact_number)}</div>
                        <div><strong>Age:</strong><br>${escapeHtml(data.age)} years old</div>
                        <div><strong>TIN ID:</strong><br>${escapeHtml(data.tin_id)}</div>
                    </div>
                    
                    <div class="section-title">
                        <i class="fas fa-store"></i> Business Information
                    </div>
                    <div style="background: #f9fafb; padding: 15px; border-radius: 12px;">
                        <div style="margin-bottom: 12px;"><strong>Shop Name:</strong><br>${escapeHtml(data.shop_name)}</div>
                        <div style="margin-bottom: 12px;"><strong>Business Category:</strong><br>${escapeHtml(data.business_category)}</div>
                        <div style="margin-bottom: 12px;"><strong>Shop Address:</strong><br>${escapeHtml(data.shop_address)}</div>
                `;
                
                if (data.shop_description) {
                    html += `<div style="margin-bottom: 12px;"><strong>Shop Description:</strong><br>${escapeHtml(data.shop_description)}</div>`;
                }
                
                if (data.additional_info) {
                    html += `<div style="margin-bottom: 12px;"><strong>Additional Information:</strong><br>${escapeHtml(data.additional_info)}</div>`;
                }
                
                html += `</div>`;
                
                html += `<div class="section-title"><i class="fas fa-file-alt"></i> Supporting Documents</div>`;
                html += `<div class="document-grid">`;
                
                if (data.business_permit) {
                    html += `
                        <div class="document-card">
                            <img src="${escapeHtml(data.business_permit)}" onclick="window.open(this.src)" alt="Business Permit">
                            <p><i class="fas fa-certificate"></i> Business Permit</p>
                        </div>
                    `;
                }
                if (data.valid_id) {
                    html += `
                        <div class="document-card">
                            <img src="${escapeHtml(data.valid_id)}" onclick="window.open(this.src)" alt="Valid ID">
                            <p><i class="fas fa-id-card"></i> Valid ID</p>
                        </div>
                    `;
                }
                if (data.shop_image) {
                    html += `
                        <div class="document-card">
                            <img src="${escapeHtml(data.shop_image)}" onclick="window.open(this.src)" alt="Shop Image">
                            <p><i class="fas fa-image"></i> Shop Image/Logo</p>
                        </div>
                    `;
                }
                
                html += `</div>`;
                
                document.getElementById('sellerModalBody').innerHTML = html;
            } else {
                document.getElementById('sellerModalBody').innerHTML = `<div style="text-align:center; padding:40px; color:#991b1b;"><i class="fas fa-exclamation-triangle"></i> Error: ${escapeHtml(data.message)}</div>`;
            }
        })
        .catch(error => {
            document.getElementById('sellerModalBody').innerHTML = `<div style="text-align:center; padding:40px; color:#991b1b;"><i class="fas fa-exclamation-triangle"></i> Failed to load seller details.</div>`;
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
                        <p><strong>Shop:</strong> ${escapeHtml(data.shop_name)}</p>
                        <p><strong>${data.application_type}</strong></p>
                        <p>This seller will become: <strong>${data.will_become}</strong></p>
                        <div style="background:#dbeafe; border-left:4px solid #3b82f6; padding:12px; margin-top:15px; border-radius:8px; text-align:left;">
                            <i class="fas fa-info-circle"></i> A confirmation email will be sent to the seller.
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

window.onclick = function(e) {
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
}

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
    } else if (msg === 'approve_success') {
        showSuccessPopup('Seller approved successfully! Email has been sent.');
    } else if (msg === 'reject_success') {
        showSuccessPopup('Seller rejected successfully!');
    }
}
</script>

</body>
</html>