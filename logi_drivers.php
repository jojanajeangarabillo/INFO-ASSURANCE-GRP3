<?php
// logi_drivers.php
require_once 'auth.php';
require_roles([5]); // Only logistics role can access

require_once __DIR__ . '/admin/db.connect.php';
require_once __DIR__ . '/admin/email.helper.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$user_id = $_SESSION['user_id'] ?? 0;
$message = '';
$error = '';

// Fetch session timeout from database
$query = "SELECT session_timeout_minutes FROM system_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$timeout_minutes = $row ? $row['session_timeout_minutes'] : 30;

if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout_minutes * 60) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['last_activity'] = time();
$timeout_ms = $timeout_minutes * 60 * 1000;

// Get logistics user info
$logistics_user_stmt = $conn->prepare("
    SELECT username, first_name, last_name, email 
    FROM user WHERE user_id = ?
");
$logistics_user_stmt->bind_param("i", $user_id);
$logistics_user_stmt->execute();
$logistics_user = $logistics_user_stmt->get_result()->fetch_assoc();
$logistics_user_stmt->close();

// Function to send notification
function sendNotification($conn, $user_id, $title, $message, $notification_type, $reference_id = null) {
    $insert_notif = $conn->prepare("
        INSERT INTO notification (user_id, title, message, notification_type, reference_id, is_read, created_at) 
        VALUES (?, ?, ?, ?, ?, 0, NOW())
    ");
    $insert_notif->bind_param("issii", $user_id, $title, $message, $notification_type, $reference_id);
    return $insert_notif->execute();
}

// Helper function to send driver welcome email
function sendDriverWelcomeEmail($email, $username, $name, $temp_password, $login_url) {
    $subject = "Welcome as a Driver - J3RS Logistics";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { padding: 20px; background-color: #f9f9f9; }
            .header { background-color: #6d0f1b; color: white; padding: 10px; text-align: center; border-radius: 5px; }
            .credentials { background-color: #fff; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #ddd; }
            .warning { color: #ff0000; font-size: 12px; }
            .btn-login { 
                display: inline-block; 
                padding: 10px 20px; 
                background-color: #6d0f1b; 
                color: white; 
                text-decoration: none; 
                border-radius: 5px; 
                margin-top: 15px;
            }
            .btn-login:hover { background-color: #8a1423; }
            .info-box { background-color: #f0f8ff; padding: 15px; border-radius: 8px; margin: 15px 0; }
            .credentials p { margin: 8px 0; }
            code { background: #f0f0f0; padding: 3px 6px; border-radius: 4px; font-family: monospace; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Welcome to J3RS Logistics, $name!</h2>
            </div>
            <p>You have been added as a driver to our logistics team. Here are your login credentials:</p>
            <div class='credentials'>
                <p><strong>📧 Email:</strong> $email</p>
                <p><strong>👤 Username:</strong> <code>$username</code></p>
                <p><strong>🔑 Temporary Password:</strong> <code>$temp_password</code></p>
                <p><strong>🔗 Login URL:</strong> <a href='$login_url'>$login_url</a></p>
            </div>
            <div style='text-align: center;'>
                <a href='$login_url' class='btn-login'>🚚 Click Here to Login</a>
            </div>
            <div class='info-box'>
                <p><strong>📋 Important Instructions:</strong></p>
                <ul>
                    <li>🔐 Use your <strong>Username</strong> (not email) to login</li>
                    <li>🔄 Please change your password after first login</li>
                    <li>📱 You will be required to set up MFA (Multi-Factor Authentication) using Google Authenticator</li>
                    <li>🛡️ Keep your login credentials secure</li>
                    <li>📧 Save this email for future reference</li>
                </ul>
            </div>
            <p class='warning'>⚠️ This is an automated message. Please do not reply to this email.</p>
            <hr>
            <p style='font-size: 10px; color: #999;'>© 2024 J3RS Logistics. All rights reserved.</p>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "Welcome to J3RS Logistics, $name!\n\n";
    $altBody .= "You have been added as a driver. Here are your login credentials:\n";
    $altBody .= "Email: $email\n";
    $altBody .= "Username: $username\n";
    $altBody .= "Temporary Password: $temp_password\n";
    $altBody .= "Login URL: $login_url\n\n";
    $altBody .= "IMPORTANT: Use your Username (not email) to login.\n";
    $altBody .= "Please change your password after first login.\n\n";
    $altBody .= "Important: You will be required to set up MFA (Multi-Factor Authentication) for security.\n\n";
    $altBody .= "Keep this email for future reference.\n";
    
    return send_email($email, $subject, $message, $altBody);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        $error = "Invalid form submission. Please refresh the page.";
    } else {
        $action = $_POST['action'] ?? '';
        
        // Add new driver
        if ($action === 'add_driver') {
            $email = trim($_POST['email'] ?? '');
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $driver_license = trim($_POST['driver_license'] ?? '');
            $license_expiry = $_POST['license_expiry'] ?? '';
            $emergency_contact = trim($_POST['emergency_contact'] ?? '');
            $emergency_phone = trim($_POST['emergency_phone'] ?? '');
            $vehicle_assigned = trim($_POST['vehicle_assigned'] ?? '');
            $license_plate = trim($_POST['license_plate'] ?? '');
            $vehicle_type = trim($_POST['vehicle_type'] ?? '');
            $hire_date = $_POST['hire_date'] ?? date('Y-m-d');
            
            $validation_errors = [];
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validation_errors[] = "Valid email address is required.";
            }
            if (empty($first_name)) $validation_errors[] = "First name is required.";
            if (empty($last_name)) $validation_errors[] = "Last name is required.";
            if (empty($driver_license)) $validation_errors[] = "Driver's license number is required.";
            if (empty($license_expiry)) $validation_errors[] = "License expiry date is required.";
            
            if (empty($validation_errors)) {
                $conn->begin_transaction();
                
                try {
                    // Check if user already exists
                    $checkStmt = $conn->prepare("SELECT user_id, role_id, username FROM user WHERE email = ?");
                    $checkStmt->bind_param("s", $email);
                    $checkStmt->execute();
                    $existing = $checkStmt->get_result()->fetch_assoc();
                    
                    if ($existing) {
                        // User exists - check if already a driver
                        $driverCheck = $conn->prepare("SELECT driver_id FROM driver WHERE user_id = ?");
                        $driverCheck->bind_param("i", $existing['user_id']);
                        $driverCheck->execute();
                        if ($driverCheck->get_result()->num_rows > 0) {
                            throw new Exception("This email is already registered as a driver.");
                        }
                        $user_id_existing = $existing['user_id'];
                        $username = $existing['username'];
                        $temp_password = null; // No need to generate for existing user
                    } else {
                        // Create new user account for driver
                        $temp_password = bin2hex(random_bytes(6)); // 12 char password
                        $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);
                        $username = strtolower($first_name . '.' . $last_name . rand(100, 999));
                        
                        $insertUser = $conn->prepare("
                            INSERT INTO user (username, first_name, last_name, email, password, otp_code, otp_expiry, verification_token, token_expiry, role_id, is_activated) 
                            VALUES (?, ?, ?, ?, ?, '', '', '', '', 6, 1)
                        ");
                        $insertUser->bind_param("sssss", $username, $first_name, $last_name, $email, $password_hash);
                        $insertUser->execute();
                        $user_id_existing = $conn->insert_id;
                        $insertUser->close();
                    }
                    
                    // Insert driver record
                    $insertDriver = $conn->prepare("
                        INSERT INTO driver (user_id, logistics_id, driver_license, license_expiry, emergency_contact, 
                                           emergency_phone, vehicle_assigned, license_plate, vehicle_type, hire_date, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
                    ");
                    $insertDriver->bind_param("iissssssss", 
                        $user_id_existing, $user_id, $driver_license, $license_expiry, $emergency_contact,
                        $emergency_phone, $vehicle_assigned, $license_plate, $vehicle_type, $hire_date
                    );
                    $insertDriver->execute();
                    $driver_id = $conn->insert_id;
                    $insertDriver->close();
                    
                    // Send welcome email ONLY for new users (with temp password)
                    if (isset($temp_password) && $temp_password) {
                        // Build login URL
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                        $base_path = "/INFO-ASSURANCE-GRP3";
                        $login_url = $protocol . $_SERVER['HTTP_HOST'] . $base_path . "/login.php";
                        
                        sendDriverWelcomeEmail(
                            $email,
                            $username,
                            $first_name . ' ' . $last_name,
                            $temp_password,
                            $login_url
                        );
                        $message = "Driver added successfully! An email with login credentials (Username & Password) has been sent to " . htmlspecialchars($email);
                    } else {
                        $message = "Existing user added as driver successfully! No email sent as user already has an account.";
                    }
                    
                    $conn->commit();
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Failed to add driver: " . $e->getMessage();
                }
            } else {
                $error = implode("<br>", $validation_errors);
            }
        }
        
        // Assign driver to order
        elseif ($action === 'assign_driver') {
            $driver_id = $_POST['driver_id'] ?? 0;
            $order_id = $_POST['order_id'] ?? 0;
            
            if ($driver_id && $order_id) {
                // Verify this order belongs to this logistics user
                $verify_stmt = $conn->prepare("
                    SELECT dt.logistic_user_id 
                    FROM delivery_tracking dt 
                    WHERE dt.order_id = ?
                ");
                $verify_stmt->bind_param("i", $order_id);
                $verify_stmt->execute();
                $tracking = $verify_stmt->get_result()->fetch_assoc();
                $verify_stmt->close();
                
                if (!$tracking || $tracking['logistic_user_id'] != $user_id) {
                    $error = "You don't have permission to assign drivers to this order.";
                } else {
                    $checkStmt = $conn->prepare("
                        SELECT assignment_id FROM driver_assignment 
                        WHERE order_id = ? AND status NOT IN ('delivered', 'cancelled')
                    ");
                    $checkStmt->bind_param("i", $order_id);
                    $checkStmt->execute();
                    if ($checkStmt->get_result()->num_rows > 0) {
                        $error = "This order is already assigned to a driver.";
                    } else {
                        $assignStmt = $conn->prepare("
                            INSERT INTO driver_assignment (driver_id, order_id, assigned_by, status) 
                            VALUES (?, ?, ?, 'pending')
                        ");
                        $assignStmt->bind_param("iii", $driver_id, $order_id, $user_id);
                        
                        if ($assignStmt->execute()) {
                            $message = "Order assigned to driver successfully!";
                            
                            // Get driver email and username for notification
                            $driverInfoStmt = $conn->prepare("
                                SELECT u.email, u.first_name, u.last_name, u.username, u.user_id as driver_user_id
                                FROM driver d 
                                JOIN user u ON d.user_id = u.user_id 
                                WHERE d.driver_id = ?
                            ");
                            $driverInfoStmt->bind_param("i", $driver_id);
                            $driverInfoStmt->execute();
                            $driverInfo = $driverInfoStmt->get_result()->fetch_assoc();
                            $driverInfoStmt->close();
                            
                            // Get order and customer details
                            $orderDetailsStmt = $conn->prepare("
                                SELECT o.order_number, o.customer_id, u.username as customer_name, u.role_id, u.email as customer_email
                                FROM orders o
                                JOIN user u ON o.customer_id = u.user_id
                                WHERE o.order_id = ?
                            ");
                            $orderDetailsStmt->bind_param("i", $order_id);
                            $orderDetailsStmt->execute();
                            $orderInfo = $orderDetailsStmt->get_result()->fetch_assoc();
                            $orderDetailsStmt->close();
                            
                            $driverName = $driverInfo['first_name'] . ' ' . $driverInfo['last_name'];
                            
                            // Send email notification to driver
                            $subject = "New Delivery Assignment - J3RS Logistics";
                            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                            $base_path = "/INFO-ASSURANCE-GRP3";
                            $dashboard_url = $protocol . $_SERVER['HTTP_HOST'] . $base_path . "/driver_dashboard.php";
                            
                            $msg = "<html><body>
                                <h2>New Delivery Assignment</h2>
                                <p>Hello " . htmlspecialchars($driverInfo['first_name']) . ",</p>
                                <p>You have been assigned a new delivery for order #{$orderInfo['order_number']}.</p>
                                <p><strong>Customer:</strong> " . htmlspecialchars($orderInfo['customer_name']) . "</p>
                                <p>Please log in to your driver dashboard to accept and view the delivery details.</p>
                                <p><strong>Login Info:</strong></p>
                                <ul>
                                    <li>Username: <code>" . htmlspecialchars($driverInfo['username']) . "</code></li>
                                    <li>Dashboard: <a href='$dashboard_url'>$dashboard_url</a></li>
                                </ul>
                                <p><a href='$dashboard_url' style='background:#6d0f1b; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Dashboard</a></p>
                            </body></html>";
                            
                            send_email($driverInfo['email'], $subject, $msg);
                            
                            // Send IN-APP notification to driver
                            $driver_title = "New Delivery Assignment - Order #{$orderInfo['order_number']}";
                            $driver_message = "You have been assigned to deliver order #{$orderInfo['order_number']} to {$orderInfo['customer_name']}. Please log in to your driver dashboard to accept this delivery.";
                            sendNotification($conn, $driverInfo['driver_user_id'], $driver_title, $driver_message, 'driver_assignment', $order_id);
                            
                            // Send IN-APP notification to customer
                            $customer_title = "Driver Assigned - Order #{$orderInfo['order_number']}";
                            $customer_message = "Good news! A driver ({$driverName}) has been assigned to deliver your order #{$orderInfo['order_number']}. You can track your delivery status in real-time.";
                            sendNotification($conn, $orderInfo['customer_id'], $customer_title, $customer_message, 'order_update', $order_id);
                            
                            // Send EMAIL notification to customer
                            $customer_email_subject = "Driver Assigned for Your Order #{$orderInfo['order_number']} - J3RS Logistics";
                            $customer_email_msg = "<html><body>
                                <h2>Driver Assigned for Your Order</h2>
                                <p>Hello " . htmlspecialchars($orderInfo['customer_name']) . ",</p>
                                <p>Good news! A driver has been assigned to deliver your order <strong>#{$orderInfo['order_number']}</strong>.</p>
                                <p><strong>Driver Details:</strong></p>
                                <ul>
                                    <li>Name: {$driverName}</li>
                                </ul>
                                <p>You can track your delivery status in your account.</p>
                                <p>Thank you for shopping with J3RS!</p>
                            </body></html>";
                            send_email($orderInfo['customer_email'], $customer_email_subject, $customer_email_msg);
                            
                            // If customer has Dual role (role_id = 4), send notification to their seller dashboard too
                            if ($orderInfo['role_id'] == 4) {
                                $dual_title = "Driver Assignment - Order #{$orderInfo['order_number']}";
                                $dual_message = "A driver ({$driverName}) has been assigned to deliver your order #{$orderInfo['order_number']}.";
                                sendNotification($conn, $orderInfo['customer_id'], $dual_title, $dual_message, 'order_update', $order_id);
                            }
                            
                        } else {
                            $error = "Failed to assign order.";
                        }
                        $assignStmt->close();
                    }
                    $checkStmt->close();
                }
            }
        }
        
        // Update driver status
        elseif ($action === 'update_status') {
            $driver_id = $_POST['driver_id'];
            $status = $_POST['status'];
            
            $stmt = $conn->prepare("UPDATE driver SET status = ? WHERE driver_id = ? AND logistics_id = ?");
            $stmt->bind_param("sii", $status, $driver_id, $user_id);
            if ($stmt->execute()) {
                $message = "Driver status updated successfully!";
            } else {
                $error = "Failed to update driver status.";
            }
            $stmt->close();
        }
        
        // Delete driver
        elseif ($action === 'delete_driver') {
            $driver_id = $_POST['driver_id'];
            
            // Check if driver has pending assignments
            $checkStmt = $conn->prepare("
                SELECT COUNT(*) as count FROM driver_assignment 
                WHERE driver_id = ? AND status NOT IN ('delivered', 'cancelled')
            ");
            $checkStmt->bind_param("i", $driver_id);
            $checkStmt->execute();
            $pendingCount = $checkStmt->get_result()->fetch_assoc()['count'];
            
            if ($pendingCount > 0) {
                $error = "Cannot delete driver with pending deliveries. Please reassign or complete deliveries first.";
            } else {
                $stmt = $conn->prepare("DELETE FROM driver WHERE driver_id = ? AND logistics_id = ?");
                $stmt->bind_param("ii", $driver_id, $user_id);
                if ($stmt->execute()) {
                    $message = "Driver removed successfully.";
                } else {
                    $error = "Failed to remove driver.";
                }
                $stmt->close();
            }
        }
    }
}

// Fetch all drivers - FILTERED BY LOGGED-IN LOGISTICS USER
$driversQuery = $conn->prepare("
    SELECT d.*, u.email, u.first_name, u.last_name, u.username, u.is_active,
           COUNT(da.assignment_id) as total_assignments,
           SUM(CASE WHEN da.status = 'delivered' THEN 1 ELSE 0 END) as completed_deliveries,
           SUM(CASE WHEN da.status = 'pending' THEN 1 ELSE 0 END) as pending_deliveries
    FROM driver d 
    JOIN user u ON d.user_id = u.user_id 
    LEFT JOIN driver_assignment da ON d.driver_id = da.driver_id
    WHERE d.logistics_id = ?
    GROUP BY d.driver_id
    ORDER BY d.created_at DESC
");
$driversQuery->bind_param("i", $user_id);
$driversQuery->execute();
$drivers = $driversQuery->get_result();
$driversQuery->close();

// Fetch orders pending assignment - ONLY FOR ORDERS ASSIGNED TO THIS LOGISTICS USER
$pendingOrdersQuery = $conn->prepare("
    SELECT o.order_id, o.order_number, o.total_amount, o.created_at,
           o.shipping_full_name as customer_name,
           o.shipping_address_line, o.shipping_city
    FROM orders o
    JOIN delivery_tracking dt ON o.order_id = dt.order_id
    WHERE o.order_status = 'processing' 
    AND dt.logistic_user_id = ?
    AND o.order_id NOT IN (
        SELECT DISTINCT order_id FROM driver_assignment WHERE status NOT IN ('delivered', 'cancelled')
    )
    ORDER BY o.created_at ASC
");
$pendingOrdersQuery->bind_param("i", $user_id);
$pendingOrdersQuery->execute();
$pendingOrders = $pendingOrdersQuery->get_result();
$pendingOrdersQuery->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Driver Management - Logistics</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="sidebar.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
body { margin: 0; font-family: 'Inter', Arial, sans-serif; background: #fdf2f6; }
.main-content { margin-left: 240px; padding: 40px 60px; transition: margin-left 0.3s ease; }
.main-content.full { margin-left: 70px; }
h1 { color: #610C27; margin-bottom: 5px; font-size: 32px; }
.sub-header { color: #777; margin-bottom: 30px; }

.tab-nav { display: flex; gap: 25px; border-bottom: 1px solid #ddd; margin-bottom: 25px; flex-wrap: wrap; }
.tab-nav a { text-decoration: none; color: #888; padding-bottom: 10px; font-size: 14px; font-weight: 600; position: relative; cursor: pointer; }
.tab-nav a.active { color: #610C27; }
.tab-nav a.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; background: #610C27; }

.search-container { background: white; padding: 15px 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 20px; border: 1px solid #eee; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
.search-container input { flex: 1; border: none; outline: none; font-size: 14px; }
.search-container input:focus { outline: none; }

.table-card { background: white; border-radius: 15px; overflow-x: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
table { width: 100%; border-collapse: collapse; min-width: 800px; }
th { text-align: left; padding: 15px 20px; font-size: 12px; text-transform: uppercase; color: #aaa; background: #fcfcfc; }
td { padding: 18px 20px; border-top: 1px solid #f5f5f5; font-size: 14px; color: #444; }

.badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
.status-active { background: #ecfdf5; color: #10b981; border: 1px solid #d1fae5; }
.status-inactive { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.status-suspended { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

.btn-primary { background: #610C27; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold; margin-right: 10px; }
.btn-primary:hover { background: #8a1423; }
.btn-secondary { background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; }
.btn-secondary:hover { background: #5a6268; }
.btn-outline { background: white; color: #610C27; border: 1.5px solid #610C27; padding: 8px 16px; border-radius: 8px; cursor: pointer; transition: 0.2s; }
.btn-outline:hover { background: #610C27; color: white; }

.modal-content { border-radius: 16px; }
.modal-header { background: #610C27; color: white; border-radius: 16px 16px 0 0; }
.modal-header .btn-close { filter: invert(1); }

.form-control, .form-select { border-radius: 10px; border: 1px solid #ddd; padding: 10px 15px; }
.form-control:focus, .form-select:focus { border-color: #610C27; box-shadow: 0 0 0 0.2rem rgba(97, 12, 39, 0.25); }

.driver-stats { display: inline-flex; gap: 10px; font-size: 12px; color: #666; }
.driver-stats span { margin-right: 10px; }

.alert-custom { border-radius: 12px; margin-bottom: 20px; padding: 12px 20px; }

.card-custom { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }

.info-banner {
    background-color: #e7f3ff;
    border-left: 4px solid #0d6efd;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Logistics</h2>
  </div>
  <a href="logi_dashboard.php"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="logi_orders.php"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="logi_drivers.php" class="active"><i class="fas fa-truck"></i><span class="text">Drivers</span></a>
  <a href="logi_reports.php"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="logi_settings.php"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  <a href="logout.php" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
</div>

<div class="main-content" id="main">
    <header>
        <h1><i class="fas fa-truck"></i> Driver Management</h1>
        <p class="sub-header">Manage delivery drivers, assign orders, and track performance.</p>
    </header>

    <!-- Info Banner showing logged-in logistics user -->
    <div class="info-banner">
        <i class="fas fa-user-circle"></i> 
        <strong>Logged in as:</strong> <?php echo htmlspecialchars($logistics_user['first_name'] ?: $logistics_user['username']); ?> 
        (Logistics ID: <?php echo $user_id; ?>)
        <br>
        <small><i class="fas fa-info-circle"></i> You can only manage drivers and orders assigned to you.</small>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="info-banner" style="background-color: #f0f8ff;">
        <i class="fas fa-bell"></i> <strong>Notification System:</strong> 
        When you assign a driver to an order, the customer will receive:
        <ul class="mt-2 mb-0">
            <li>📱 In-app notification</li>
            <li>📧 Email notification with driver details</li>
        </ul>
        Drivers also receive email and in-app notifications for new assignments.
    </div>

    <div class="tab-nav">
        <a class="active" onclick="showTab('drivers', this)"><i class="fas fa-users"></i> Drivers List</a>
        <a onclick="showTab('add', this)"><i class="fas fa-user-plus"></i> Add New Driver</a>
        <a onclick="showTab('assign', this)"><i class="fas fa-tasks"></i> Assign Orders</a>
    </div>

    <!-- Drivers List Tab -->
    <div id="driversTab" class="tab-content">
        <div class="search-container">
            <i class="fas fa-search" style="color: #ccc;"></i>
            <input type="text" id="driverSearch" onkeyup="searchDrivers()" placeholder="Search by name, email, username, or license plate...">
            <select id="statusFilter" onchange="filterByStatus()" class="form-select w-auto">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>

        <div class="table-card">
            <table id="driversTable">
                <thead>
                    <tr>
                        <th>Driver</th>
                        <th>Username</th>
                        <th>Contact</th>
                        <th>License Info</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Performance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($drivers->num_rows == 0): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted p-4">
                                <i class="fas fa-info-circle"></i> No drivers found. Click "Add New Driver" to get started.
                            </td>
                        </tr>
                    <?php else: 
                        while($driver = $drivers->fetch_assoc()): ?>
                        <tr data-status="<?php echo $driver['status']; ?>">
                            <td>
                                <strong><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></strong><br>
                                <small class="text-muted"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($driver['email']); ?></small>
                            </td>
                            <td>
                                <code><i class="fas fa-user"></i> <?php echo htmlspecialchars($driver['username']); ?></code>
                            </tr>
                            <td>
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($driver['emergency_phone'] ?: 'N/A'); ?><br>
                                <small><i class="fas fa-user-friends"></i> Emergency: <?php echo htmlspecialchars($driver['emergency_contact'] ?: 'N/A'); ?></small>
                            </td>
                            <td>
                                <i class="fas fa-id-card"></i> <?php echo htmlspecialchars($driver['driver_license']); ?><br>
                                <small><i class="fas fa-calendar-alt"></i> Expires: <?php echo date('M d, Y', strtotime($driver['license_expiry'])); ?></small>
                            </td>
                            <td>
                                <?php if ($driver['vehicle_assigned']): ?>
                                    <i class="fas fa-truck"></i> <?php echo htmlspecialchars($driver['vehicle_assigned']); ?><br>
                                    <small><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($driver['license_plate']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-car"></i> Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge status-<?php echo $driver['status']; ?>">
                                    <?php echo ucfirst($driver['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="driver-stats">
                                    <span><i class="fas fa-star text-warning"></i> <?php echo $driver['rating']; ?></span>
                                    <span><i class="fas fa-check-circle text-success"></i> <?php echo $driver['completed_deliveries']; ?></span>
                                    <span><i class="fas fa-clock text-warning"></i> <?php echo $driver['pending_deliveries']; ?></span>
                                </div>
                            </td>
                            <td>
                                <button class="btn-outline btn-sm" onclick="toggleDriverStatus(<?php echo $driver['driver_id']; ?>, '<?php echo $driver['status']; ?>')">
                                    <i class="fas <?php echo $driver['status'] == 'active' ? 'fa-pause' : 'fa-play'; ?>"></i>
                                </button>
                                <button class="btn-outline btn-sm text-danger" onclick="deleteDriver(<?php echo $driver['driver_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; 
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Driver Tab -->
    <div id="addTab" class="tab-content" style="display: none;">
        <div class="card-custom">
            <h5 class="fw-bold mb-4"><i class="fas fa-user-plus"></i> Add New Driver</h5>
            
            <form method="POST" onsubmit="return validateDriverForm()">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="add_driver">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required>
                        <small class="text-muted">Login credentials (Username & Password) will be sent to this email.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+63XXXXXXXXXX">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Driver's License Number *</label>
                        <input type="text" name="driver_license" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">License Expiry Date *</label>
                        <input type="date" name="license_expiry" class="form-control" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact" class="form-control" placeholder="Full name of emergency contact">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Emergency Contact Number</label>
                        <input type="text" name="emergency_phone" class="form-control" placeholder="+63XXXXXXXXXX">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vehicle Assigned</label>
                        <input type="text" name="vehicle_assigned" class="form-control" placeholder="e.g., Toyota Hilux">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">License Plate</label>
                        <input type="text" name="license_plate" class="form-control" placeholder="ABC-1234">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" class="form-select">
                            <option value="">Select type</option>
                            <option value="motorcycle">🏍️ Motorcycle</option>
                            <option value="van">🚐 Van</option>
                            <option value="truck">🚚 Truck</option>
                            <option value="suv">🚙 SUV</option>
                            <option value="bicycle">🚲 Bicycle</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Hire Date</label>
                    <input type="date" name="hire_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> The system will automatically generate:
                    <ul class="mb-0 mt-2">
                        <li>A unique username for the driver</li>
                        <li>A secure temporary password</li>
                        <li>Login instructions will be sent via email</li>
                        <li>The driver will be prompted to change password and setup MFA on first login</li>
                    </ul>
                </div>
                
                <div class="text-end mt-3">
                    <button type="submit" class="btn-primary px-4 py-2">
                        <i class="fas fa-plus"></i> Add Driver
                    </button>
                    <button type="button" class="btn-secondary px-4 py-2" onclick="this.form.reset()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Orders Tab -->
    <div id="assignTab" class="tab-content" style="display: none;">
        <div class="card-custom">
            <h5 class="fw-bold mb-4"><i class="fas fa-tasks"></i> Assign Orders to Drivers</h5>
            
            <?php if ($pendingOrders->num_rows == 0): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No pending orders available for assignment. All orders assigned to you are already assigned to drivers or completed.
                </div>
            <?php else: ?>
                <form method="POST" id="assignForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="assign_driver">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Order *</label>
                            <select name="order_id" class="form-select" required onchange="updateOrderDetails(this)">
                                <option value="">-- Select Order (Assigned to You) --</option>
                                <?php 
                                $pendingOrders->data_seek(0);
                                while($order = $pendingOrders->fetch_assoc()): ?>
                                <option value="<?php echo $order['order_id']; ?>" 
                                        data-customer="<?php echo htmlspecialchars($order['customer_name']); ?>"
                                        data-address="<?php echo htmlspecialchars($order['shipping_address_line'] . ', ' . $order['shipping_city']); ?>"
                                        data-amount="<?php echo number_format($order['total_amount'], 2); ?>">
                                    #<?php echo $order['order_number']; ?> - <?php echo htmlspecialchars($order['customer_name']); ?> (₱<?php echo number_format($order['total_amount'], 2); ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Driver *</label>
                            <select name="driver_id" class="form-select" required onchange="updateDriverInfo(this)">
                                <option value="">-- Select Driver --</option>
                                <?php 
                                $drivers->data_seek(0);
                                while($driver = $drivers->fetch_assoc()): 
                                    if($driver['status'] == 'active'): ?>
                                <option value="<?php echo $driver['driver_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>"
                                        data-username="<?php echo htmlspecialchars($driver['username']); ?>"
                                        data-vehicle="<?php echo htmlspecialchars($driver['vehicle_assigned']); ?>"
                                        data-plate="<?php echo htmlspecialchars($driver['license_plate']); ?>"
                                        data-rating="<?php echo $driver['rating']; ?>"
                                        data-deliveries="<?php echo $driver['completed_deliveries']; ?>">
                                    <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?> 
                                    (@<?php echo htmlspecialchars($driver['username']); ?>)
                                </option>
                                <?php endif; endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Order Details Preview -->
                    <div id="orderPreview" class="mt-3" style="display: none;">
                        <div class="alert alert-light border">
                            <h6><i class="fas fa-shopping-cart"></i> Order Details</h6>
                            <div id="orderInfo"></div>
                        </div>
                    </div>
                    
                    <!-- Driver Details Preview -->
                    <div id="driverPreview" class="mt-3" style="display: none;">
                        <div class="alert alert-light border">
                            <h6><i class="fas fa-truck"></i> Driver Details</h6>
                            <div id="driverInfo"></div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-bell"></i> <strong>Notification:</strong> 
                        The customer will receive an email and in-app notification when you assign this driver.
                    </div>
                    
                    <div class="text-end mt-3">
                        <button type="submit" class="btn-primary px-4 py-2">
                            <i class="fas fa-check-circle"></i> Assign Delivery & Notify Customer
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
    document.getElementById("main").classList.toggle("full");
}

function showTab(tab, element) {
    document.getElementById('driversTab').style.display = 'none';
    document.getElementById('addTab').style.display = 'none';
    document.getElementById('assignTab').style.display = 'none';
    
    if (tab === 'drivers') document.getElementById('driversTab').style.display = 'block';
    else if (tab === 'add') document.getElementById('addTab').style.display = 'block';
    else if (tab === 'assign') document.getElementById('assignTab').style.display = 'block';
    
    document.querySelectorAll('.tab-nav a').forEach(a => a.classList.remove('active'));
    element.classList.add('active');
}

function searchDrivers() {
    let input = document.getElementById("driverSearch").value.toUpperCase();
    let rows = document.getElementById("driversTable").getElementsByTagName("tr");
    for (let i = 1; i < rows.length; i++) {
        let text = rows[i].textContent || rows[i].innerText;
        rows[i].style.display = text.toUpperCase().indexOf(input) > -1 ? "" : "none";
    }
}

function filterByStatus() {
    let status = document.getElementById("statusFilter").value;
    let rows = document.getElementById("driversTable").getElementsByTagName("tr");
    for (let i = 1; i < rows.length; i++) {
        let rowStatus = rows[i].getAttribute("data-status");
        if (status === 'all' || rowStatus === status) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
}

function validateDriverForm() {
    let licenseExpiry = document.querySelector('input[name="license_expiry"]').value;
    let today = new Date().toISOString().split('T')[0];
    
    if (licenseExpiry < today) {
        alert("License expiry date cannot be in the past.");
        return false;
    }
    
    let email = document.querySelector('input[name="email"]').value;
    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        alert("Please enter a valid email address.");
        return false;
    }
    
    return true;
}

function updateOrderDetails(select) {
    let option = select.options[select.selectedIndex];
    let preview = document.getElementById('orderPreview');
    let info = document.getElementById('orderInfo');
    
    if (select.value) {
        let customer = option.getAttribute('data-customer');
        let address = option.getAttribute('data-address');
        let amount = option.getAttribute('data-amount');
        
        info.innerHTML = `
            <p><strong>👤 Customer:</strong> ${customer}</p>
            <p><strong>📍 Delivery Address:</strong> ${address}</p>
            <p><strong>💰 Order Amount:</strong> ₱${amount}</p>
        `;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

function updateDriverInfo(select) {
    let option = select.options[select.selectedIndex];
    let preview = document.getElementById('driverPreview');
    let info = document.getElementById('driverInfo');
    
    if (select.value) {
        let name = option.getAttribute('data-name');
        let username = option.getAttribute('data-username');
        let vehicle = option.getAttribute('data-vehicle') || 'Not assigned';
        let plate = option.getAttribute('data-plate') || 'N/A';
        let rating = parseFloat(option.getAttribute('data-rating'));
        let deliveries = option.getAttribute('data-deliveries');
        
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += i <= rating ? '⭐' : '☆';
        }
        
        info.innerHTML = `
            <p><strong>👨‍✈️ Name:</strong> ${name}</p>
            <p><strong>👤 Username:</strong> <code>${username}</code></p>
            <p><strong>🚗 Vehicle:</strong> ${vehicle} (${plate})</p>
            <p><strong>⭐ Rating:</strong> ${rating} ${stars}</p>
            <p><strong>📦 Completed Deliveries:</strong> ${deliveries}</p>
        `;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

function toggleDriverStatus(driverId, currentStatus) {
    let newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    let statusText = newStatus === 'active' ? 'activate' : 'deactivate';
    let actionText = newStatus === 'active' ? 'activated' : 'deactivated';
    
    if (confirm(`Are you sure you want to ${statusText} this driver?\n\nDriver will be ${actionText}.`)) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="driver_id" value="${driverId}">
            <input type="hidden" name="status" value="${newStatus}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteDriver(driverId) {
    if (confirm("⚠️ WARNING: This will permanently delete the driver account and all associated data!\n\nAre you absolutely sure?")) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="delete_driver">
            <input type="hidden" name="driver_id" value="${driverId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</body>
</html>