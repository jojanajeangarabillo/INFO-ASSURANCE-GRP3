<?php
require_once 'auth.php';
require_roles([2, 3, 4]); // Allow Customer, Seller, and Dual roles
require_once 'admin/db.connect.php';
require_once 'admin/email.helper.php';

$userId = (int) $_SESSION['user_id'];
$roleId = (int) ($_SESSION['role_id'] ?? 2);

// Check seller application status
$sellerStatus = null;
$sellerData = null;
$sellerCheckStmt = $conn->prepare("SELECT seller_id, is_approved, created_at FROM seller WHERE user_id = ? LIMIT 1");
$sellerCheckStmt->bind_param("i", $userId);
$sellerCheckStmt->execute();
$sellerData = $sellerCheckStmt->get_result()->fetch_assoc();

if ($sellerData) {
    if ($sellerData['is_approved'] == 1) {
        $sellerStatus = 'approved';
        // Ensure role is Dual if approved
        if ($roleId != 4) {
            $updateRoleStmt = $conn->prepare("UPDATE user SET role_id = 4 WHERE user_id = ?");
            $updateRoleStmt->bind_param("i", $userId);
            $updateRoleStmt->execute();
            $_SESSION['role_id'] = 4;
            $roleId = 4;
        }
    } elseif ($sellerData['is_approved'] == 0) {
        $sellerStatus = 'pending';
    }
} else {
    $sellerStatus = 'none';
}

// Determine if user can switch roles (only if approved seller with dual role)
$hasApprovedSeller = ($sellerStatus === 'approved');
$hasPendingSeller = ($sellerStatus === 'pending');
$canSwitchRole = $hasApprovedSeller && $roleId == 4;

// Set active role
if (!isset($_SESSION['active_role'])) {
    $_SESSION['active_role'] = 'customer';
}

// Only allow seller active role if user has approved seller account
if ($_SESSION['active_role'] === 'seller' && !$hasApprovedSeller) {
    $_SESSION['active_role'] = 'customer';
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$alert = '';
$alertType = 'info';
$otpSent = false;
$otpVerified = false;

function format_datetime(?string $dt): string
{
    if (!$dt) {
        return '';
    }
    $timestamp = strtotime($dt);
    if ($timestamp === false) {
        return $dt;
    }
    return date('M d, Y h:i A', $timestamp);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        $alert = 'Invalid request token.';
        $alertType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $contact = trim($_POST['contact_number'] ?? '');
            $addressLine = trim($_POST['address_line'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $region = trim($_POST['region'] ?? '');
            $postalCode = trim($_POST['postal_code'] ?? '');

            if ($username === '' || $email === '' || $fullName === '') {
                $alert = 'Username, email, and full name are required.';
                $alertType = 'danger';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $alert = 'Invalid email format.';
                $alertType = 'danger';
            } else {
                $checkStmt = $conn->prepare("SELECT user_id FROM user WHERE (email = ? OR username = ?) AND user_id <> ? LIMIT 1");
                $checkStmt->bind_param("ssi", $email, $username, $userId);
                $checkStmt->execute();
                if ($checkStmt->get_result()->num_rows > 0) {
                    $alert = 'Username or email is already used by another account.';
                    $alertType = 'danger';
                } else {
                    $conn->begin_transaction();
                    // Update user table (username and email)
                    $updateUserStmt = $conn->prepare("UPDATE user SET username = ?, email = ? WHERE user_id = ?");
                    $updateUserStmt->bind_param("ssi", $username, $email, $userId);
                    $updateUserStmt->execute();

                    // Update or insert customer table (full_name, contact, address)
                    $customerExistsStmt = $conn->prepare("SELECT customer_id FROM customer WHERE user_id = ? LIMIT 1");
                    $customerExistsStmt->bind_param("i", $userId);
                    $customerExistsStmt->execute();
                    $hasCustomer = $customerExistsStmt->get_result()->num_rows > 0;

                    if ($hasCustomer) {
                        $updateCustomerStmt = $conn->prepare("
                            UPDATE customer
                            SET full_name = ?, contact_number = ?, address_line = ?, city = ?, region = ?, postal_code = ?
                            WHERE user_id = ?
                        ");
                        $updateCustomerStmt->bind_param("ssssssi", $fullName, $contact, $addressLine, $city, $region, $postalCode, $userId);
                        $updateCustomerStmt->execute();
                    } else {
                        $insertCustomerStmt = $conn->prepare("
                            INSERT INTO customer (user_id, full_name, contact_number, address_line, city, region, postal_code)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $insertCustomerStmt->bind_param("issssss", $userId, $fullName, $contact, $addressLine, $city, $region, $postalCode);
                        $insertCustomerStmt->execute();
                    }
                    $conn->commit();
                    $_SESSION['username'] = $username;
                    $alert = 'Profile updated successfully.';
                    $alertType = 'success';
                }
            }
        } elseif ($action === 'send_password_otp') {
            $currentPassword = $_POST['current_password'] ?? '';
            $stmt = $conn->prepare("SELECT email, password FROM user WHERE user_id = ? LIMIT 1");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if (!$row || !password_verify($currentPassword, (string) $row['password'])) {
                $alert = 'Current password is incorrect.';
                $alertType = 'danger';
            } else {
                $otp = sprintf("%06d", random_int(1, 999999));
                $otpStmt = $conn->prepare("UPDATE user SET otp_code = ?, otp_expiry = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE user_id = ?");
                $otpStmt->bind_param("si", $otp, $userId);
                $otpStmt->execute();

                $subject = "Password Change OTP - J3RS";
                $body = "<p>Your OTP for password change is: <strong>{$otp}</strong></p><p>It expires in 10 minutes.</p>";
                if (send_email((string) $row['email'], $subject, $body)) {
                    $alert = 'OTP sent to your email. Please verify OTP first, then set your new password.';
                    $alertType = 'success';
                    $otpSent = true;
                    $_SESSION['otp_sent'] = true;
                } else {
                    $alert = 'Unable to send OTP email. Please try again.';
                    $alertType = 'danger';
                }
            }
        } elseif ($action === 'verify_otp') {
            $otpInput = trim($_POST['otp_code'] ?? '');
            if ($otpInput === '') {
                $alert = 'Please enter the OTP code.';
                $alertType = 'danger';
            } else {
                $otpCheckStmt = $conn->prepare("SELECT otp_code FROM user WHERE user_id = ? AND otp_code = ? AND otp_expiry > NOW() LIMIT 1");
                $otpCheckStmt->bind_param("is", $userId, $otpInput);
                $otpCheckStmt->execute();
                if ($otpCheckStmt->get_result()->num_rows === 0) {
                    $alert = 'Invalid or expired OTP. Please request a new OTP.';
                    $alertType = 'danger';
                } else {
                    $alert = 'OTP verified successfully! You can now set your new password.';
                    $alertType = 'success';
                    $otpVerified = true;
                    $_SESSION['otp_verified'] = true;
                    $_SESSION['verified_otp'] = $otpInput;
                }
            }
        } elseif ($action === 'update_password') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Check if OTP was verified
            if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
                $alert = 'Please verify OTP first before changing password.';
                $alertType = 'danger';
            } elseif ($newPassword === '' || $confirmPassword === '') {
                $alert = 'New password fields are required.';
                $alertType = 'danger';
            } elseif ($newPassword !== $confirmPassword) {
                $alert = 'New password and confirmation do not match.';
                $alertType = 'danger';
            } elseif (strlen($newPassword) < 12 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
                $alert = 'New password does not meet security requirements.';
                $alertType = 'danger';
            } else {
                // Verify OTP one more time before updating
                $otpCheckStmt = $conn->prepare("SELECT otp_code FROM user WHERE user_id = ? AND otp_code = ? AND otp_expiry > NOW() LIMIT 1");
                $otpCheckStmt->bind_param("is", $userId, $_SESSION['verified_otp']);
                $otpCheckStmt->execute();
                if ($otpCheckStmt->get_result()->num_rows === 0) {
                    $alert = 'OTP has expired. Please request a new OTP.';
                    $alertType = 'danger';
                    unset($_SESSION['otp_verified']);
                    unset($_SESSION['verified_otp']);
                } else {
                    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updatePasswordStmt = $conn->prepare("UPDATE user SET password = ?, otp_code = '', otp_expiry = '0000-00-00 00:00:00', attempts = 0 WHERE user_id = ?");
                    $updatePasswordStmt->bind_param("si", $passwordHash, $userId);
                    $updatePasswordStmt->execute();

                    // Clear OTP session variables
                    unset($_SESSION['otp_sent']);
                    unset($_SESSION['otp_verified']);
                    unset($_SESSION['verified_otp']);
                    
                    $alert = 'Password changed successfully! Please login again with your new password.';
                    $alertType = 'success';
                    
                    // Logout user after password change
                    session_unset();
                    session_destroy();
                    session_start();
                    $_SESSION['success_message'] = 'Password updated successfully. Please sign in with your new password.';
                    header("Location: login.php");
                    exit;
                }
            }
        } elseif ($action === 'switch_role') {
            // Verify user has approved seller account with dual role
            $verifySellerStmt = $conn->prepare("SELECT seller_id, is_approved FROM seller WHERE user_id = ? AND is_approved = 1 LIMIT 1");
            $verifySellerStmt->bind_param("i", $userId);
            $verifySellerStmt->execute();
            $approvedSeller = $verifySellerStmt->get_result()->fetch_assoc();
            
            if ($approvedSeller && $roleId == 4) {
                if ($_SESSION['active_role'] === 'customer') {
                    $_SESSION['active_role'] = 'seller';
                    header("Location: seller_dashboard.php");
                } else {
                    $_SESSION['active_role'] = 'customer';
                    header("Location: customer_home.php");
                }
                exit;
            } else {
                $alert = 'You do not have an approved seller account. Please register and wait for approval.';
                $alertType = 'warning';
            }
        }
    }
}

// Check session for OTP status
if (isset($_SESSION['otp_sent']) && $_SESSION['otp_sent'] === true) {
    $otpSent = true;
}
if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
    $otpVerified = true;
}

$profileStmt = $conn->prepare("
    SELECT
        u.username,
        u.email,
        c.full_name,
        c.contact_number,
        c.address_line,
        c.city,
        c.region,
        c.postal_code
    FROM user u
    LEFT JOIN customer c ON c.user_id = u.user_id
    WHERE u.user_id = ?
    LIMIT 1
");
$profileStmt->bind_param("i", $userId);
$profileStmt->execute();
$profile = $profileStmt->get_result()->fetch_assoc() ?: [];

$username = (string) ($profile['username'] ?? ($_SESSION['username'] ?? ''));
$email = (string) ($profile['email'] ?? '');
$fullName = (string) ($profile['full_name'] ?? '');
$contactNumber = (string) ($profile['contact_number'] ?? '');
$addressLine = (string) ($profile['address_line'] ?? '');
$city = (string) ($profile['city'] ?? '');
$region = (string) ($profile['region'] ?? '');
$postalCode = (string) ($profile['postal_code'] ?? '');

$notifStmt = $conn->prepare("
    SELECT notification_id, title, message, is_read, created_at
    FROM notification
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$notifStmt->bind_param("i", $userId);
$notifStmt->execute();
$notifications = $notifStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$unreadCount = 0;
foreach ($notifications as $notif) {
    if ((int) $notif['is_read'] === 0) {
        $unreadCount++;
    }
}

// Seller status message
$sellerStatusMessage = '';
if ($hasPendingSeller) {
    $sellerStatusMessage = 'Your seller application is pending approval. You will be able to switch roles once approved.';
} elseif ($hasApprovedSeller) {
    $sellerStatusMessage = 'Your seller account is approved! You can now switch between Customer and Seller roles.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Profile - J3RS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { background: #f5f1ee; font-family: 'Inter', sans-serif; }
  .main-content { margin-left: 240px; transition: 0.3s; padding: 30px; }
  .sidebar.collapsed ~ .main-content { margin-left: 70px; }
  .top-card { background: linear-gradient(90deg, #ffffff, #efe7e7); border-radius: 16px; padding: 24px; display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
  .profile { display: flex; align-items: center; gap: 15px; }
  .profile-avatar { width: 60px; height: 60px; border-radius: 50%; background: #6e0f25; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 22px; }
  .btn-maroon { background: #6e0f25; color: #fff; border: none; padding: 12px 20px; border-radius: 10px; transition: all 0.3s; }
  .btn-maroon:hover { background: #8b1a35; transform: translateY(-2px); }
  .btn-maroon-outline { background: transparent; color: #6e0f25; border: 2px solid #6e0f25; padding: 12px 20px; border-radius: 10px; transition: all 0.3s; }
  .btn-maroon-outline:hover { background: #6e0f25; color: #fff; transform: translateY(-2px); }
  .btn-success-custom { background: #28a745; color: #fff; border: none; padding: 12px 20px; border-radius: 10px; transition: all 0.3s; }
  .btn-success-custom:hover { background: #218838; transform: translateY(-2px); }
  .layout { display: flex; gap: 25px; margin-top: 25px; align-items: flex-start; }
  .side-card { width: 240px; background: #fff; border-radius: 16px; padding: 18px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
  .menu-item { padding: 10px 14px; border-radius: 10px; cursor: pointer; margin-bottom: 6px; transition: all 0.3s; }
  .menu-item.active { background: #e9e2dc; color: #6e0f25; font-weight: 600; }
  .menu-item:hover { background: #f1ece8; transform: translateX(5px); }
  .content-card { flex: 1; background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  .section { display: none; }
  .section.active { display: block; }
  .notification-wrapper { position: fixed; top: 20px; right: 20px; z-index: 1050; }
  .notification-panel { width: 320px; border-radius: 12px; }
  .notif-item { padding: 8px 0; border-radius: 8px; }
  .notif-item.unread { background: #fef2e8; border-left: 3px solid #6e0f25; padding-left: 10px; }
  .seller-badge { display: inline-block; background: #6e0f25; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; margin-left: 10px; }
  .pending-badge { background: #f0ad4e; color: #333; }
  .approved-badge { background: #28a745; }
  .password-input-wrapper { position: relative; }
  .password-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; }
  .otp-step { border-left: 3px solid #6e0f25; padding-left: 15px; margin-bottom: 20px; }
  .step-badge { display: inline-block; width: 30px; height: 30px; background: #6e0f25; color: white; border-radius: 50%; text-align: center; line-height: 30px; margin-right: 10px; }
  @media (max-width: 992px) {
    .layout { flex-direction: column; }
    .side-card { width: 100%; }
  }
  @media (max-width: 768px) {
    .main-content { margin-left: 0; padding: 80px 20px 20px 20px; }
    .sidebar.collapsed ~ .main-content { margin-left: 0; }
    .form-grid { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="logo-text"><?php echo ucfirst($_SESSION['active_role']); ?></div>
  </div>
  <a href="customer_profile.php"><i class="bi bi-person-circle"></i><span class="text">Profile</span></a>
  <a href="customer_home.php"><i class="bi bi-house"></i><span class="text">Home</span></a>
  <a href="customer_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
  <a href="customer_cart.php"><i class="bi bi-cart-check"></i><span class="text">Cart</span></a>
  <a href="customer_wishlist.php"><i class="bi bi-bookmark-heart"></i><span class="text">Wishlist</span></a>
  <a href="customer_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat & Support</span></a>
  <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i><span class="text">Logout</span></a>
</div>

<div class="notification-wrapper">
  <div class="dropdown">
    <button class="btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-bell fs-4"></i>
      <?php if ($unreadCount > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo $unreadCount; ?></span>
      <?php endif; ?>
    </button>
    <div class="dropdown-menu dropdown-menu-end p-3 shadow notification-panel">
      <strong>Notifications</strong>
      <hr class="mt-2 mb-2">
      <?php if (empty($notifications)): ?>
        <small class="text-muted">No notifications yet.</small>
      <?php else: ?>
        <?php foreach ($notifications as $notif): ?>
          <div class="notif-item <?php echo ((int) $notif['is_read'] === 0) ? 'unread' : ''; ?>">
            <div class="fw-bold"><?php echo htmlspecialchars($notif['title']); ?></div>
            <small class="text-muted"><?php echo htmlspecialchars($notif['message']); ?></small>
            <div class="text-end text-muted small"><?php echo htmlspecialchars(format_datetime((string) $notif['created_at'])); ?></div>
          </div>
          <hr class="my-2">
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="main-content">
  <div class="container-fluid">
    <h2 class="fw-bold">Account Settings</h2>
    <p class="text-muted">Manage your profile, preferences, and security.</p>

    <?php if ($alert !== ''): ?>
      <div class="alert alert-<?php echo htmlspecialchars($alertType); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($alert); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if ($sellerStatusMessage): ?>
      <div class="alert alert-info">
        <i class="bi bi-info-circle-fill me-2"></i>
        <?php echo htmlspecialchars($sellerStatusMessage); ?>
      </div>
    <?php endif; ?>

    <div class="top-card">
      <div class="profile">
        <div class="profile-avatar"><?php echo htmlspecialchars(strtoupper(substr($fullName ?: $username, 0, 1))); ?></div>
        <div>
          <span><?php echo htmlspecialchars($email); ?></span>
          <span class="badge bg-secondary ms-2"><?php echo ucfirst($_SESSION['active_role']); ?></span>
          <?php if ($hasApprovedSeller): ?>
            <span class="seller-badge approved-badge"><i class="bi bi-shop"></i> Dual Role</span>
          <?php elseif ($hasPendingSeller): ?>
            <span class="seller-badge pending-badge"><i class="bi bi-hourglass-split"></i> Pending Seller Approval</span>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($canSwitchRole): ?>
        <form method="POST" class="mb-0">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
          <input type="hidden" name="action" value="switch_role">
          <button type="submit" class="btn-maroon">
            <i class="bi bi-arrow-repeat me-2"></i>
            Switch to <?php echo $_SESSION['active_role'] === 'customer' ? 'Seller Dashboard' : 'Customer Home'; ?>
          </button>
        </form>
      <?php elseif ($sellerStatus === 'none'): ?>
        <a href="seller_register.php?source=profile" class="btn-maroon-outline text-decoration-none">
          <i class="bi bi-shop me-2"></i>Become a Seller
        </a>
      <?php endif; ?>
    </div>

    <div class="layout">
      <div class="side-card">
        <div class="menu-item active" data-section="personal"><i class="bi bi-person me-2"></i>Personal Information</div>
        <div class="menu-item" data-section="addresses"><i class="bi bi-geo-alt me-2"></i>Address</div>
        <div class="menu-item" data-section="notifications"><i class="bi bi-bell me-2"></i>Notifications</div>
        <div class="menu-item" data-section="password"><i class="bi bi-key me-2"></i>Change Password</div>
      </div>

      <div class="content-card">
        <div class="section active" id="personal">
          <h5 class="fw-bold mb-4">Personal Information</h5>
          <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-grid">
              <div>
                <label class="form-label">Username (Login ID)</label>
                <input class="form-control" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <small class="text-muted">This is your login username</small>
              </div>
              <div>
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
              </div>
              <div>
                <label class="form-label">Full Name</label>
                <input class="form-control" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>" required>
                <small class="text-muted">This will appear on your profile</small>
              </div>
              <div>
                <label class="form-label">Contact Number</label>
                <input class="form-control" name="contact_number" value="<?php echo htmlspecialchars($contactNumber); ?>">
              </div>
            </div>
            <div class="mt-3 text-end">
              <button class="btn-maroon" type="submit">Save Changes</button>
            </div>
          </form>
        </div>

        <div class="section" id="addresses">
          <h5 class="fw-bold mb-4">Address Information</h5>
          <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>">
            <input type="hidden" name="contact_number" value="<?php echo htmlspecialchars($contactNumber); ?>">
            <div class="mb-3">
              <label class="form-label">Address Line</label>
              <textarea class="form-control" name="address_line" rows="2"><?php echo htmlspecialchars($addressLine); ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">City</label>
              <input class="form-control" name="city" value="<?php echo htmlspecialchars($city); ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Region</label>
              <input class="form-control" name="region" value="<?php echo htmlspecialchars($region); ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Postal Code</label>
              <input class="form-control" name="postal_code" value="<?php echo htmlspecialchars($postalCode); ?>">
            </div>
            <div class="mt-3 text-end">
              <button class="btn-maroon" type="submit">Save Address</button>
            </div>
          </form>
        </div>

        <div class="section" id="notifications">
          <h5 class="fw-bold mb-4">Order Notifications</h5>
          <?php if (empty($notifications)): ?>
            <p class="text-muted mb-0">No order notifications available.</p>
          <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
              <div class="border rounded p-3 mb-3">
                <div class="fw-bold"><?php echo htmlspecialchars($notif['title']); ?></div>
                <div class="text-muted"><?php echo htmlspecialchars($notif['message']); ?></div>
                <small class="text-muted"><?php echo htmlspecialchars(format_datetime((string) $notif['created_at'])); ?></small>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="section" id="password">
          <h5 class="fw-bold mb-4">Change Password</h5>
          
          <!-- Step 1: Request OTP -->
          <div class="otp-step">
            <div class="d-flex align-items-center mb-3">
              <span class="step-badge">1</span>
              <h6 class="mb-0">Request OTP Verification</h6>
            </div>
            <form method="POST" class="mb-3">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
              <input type="hidden" name="action" value="send_password_otp">
              <div class="password-input-wrapper">
                <label class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
                <span class="password-toggle" onclick="togglePassword('current_password')">
                  <i class="bi bi-eye-slash"></i>
                </span>
              </div>
              <button class="btn-maroon mt-2" type="submit" <?php echo ($otpVerified) ? 'disabled' : ''; ?>>
                <i class="bi bi-envelope-paper me-2"></i>Send OTP
              </button>
            </form>
          </div>

          <!-- Step 2: Verify OTP -->
          <div class="otp-step">
            <div class="d-flex align-items-center mb-3">
              <span class="step-badge">2</span>
              <h6 class="mb-0">Verify OTP Code</h6>
            </div>
            <form method="POST" class="mb-3">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
              <input type="hidden" name="action" value="verify_otp">
              <label class="form-label">Enter OTP Code</label>
              <input class="form-control mb-2" name="otp_code" maxlength="6" placeholder="6-digit code" required <?php echo ($otpVerified) ? 'disabled' : ''; ?>>
              <button class="btn btn-outline-secondary" type="submit" <?php echo ($otpVerified) ? 'disabled' : ''; ?>>
                <i class="bi bi-check-circle me-2"></i>Verify OTP
              </button>
              <?php if ($otpVerified): ?>
                <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i> Verified!</span>
              <?php endif; ?>
            </form>
          </div>

          <!-- Step 3: Set New Password (only shown after OTP verification) -->
          <?php if ($otpVerified): ?>
          <div class="otp-step">
            <div class="d-flex align-items-center mb-3">
              <span class="step-badge">3</span>
              <h6 class="mb-0">Set New Password</h6>
            </div>
            <form method="POST">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
              <input type="hidden" name="action" value="update_password">
              
              <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="password-input-wrapper">
                  <input type="password" class="form-control" id="new_password" name="new_password" required>
                  <span class="password-toggle" onclick="togglePassword('new_password')">
                    <i class="bi bi-eye-slash"></i>
                  </span>
                </div>
                <div class="text-muted small mt-1">
                  <i class="bi bi-info-circle"></i> Password must be at least 12 characters and include:
                  <ul class="mt-1 mb-0">
                    <li>At least one uppercase letter (A-Z)</li>
                    <li>At least one lowercase letter (a-z)</li>
                    <li>At least one number (0-9)</li>
                    <li>At least one special character (!@#$%^&*)</li>
                  </ul>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <div class="password-input-wrapper">
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                  <span class="password-toggle" onclick="togglePassword('confirm_password')">
                    <i class="bi bi-eye-slash"></i>
                  </span>
                </div>
              </div>
              
              <button class="btn-success-custom" type="submit">
                <i class="bi bi-key me-2"></i>Update Password
              </button>
            </form>
          </div>
          <?php endif; ?>
          
          <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Security Note:</strong> After changing your password, you will be logged out and need to login again with your new password.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
}

document.querySelectorAll('.menu-item').forEach(function(item) {
  item.addEventListener('click', function() {
    document.querySelectorAll('.menu-item').forEach(function(node) { node.classList.remove('active'); });
    item.classList.add('active');
    document.querySelectorAll('.section').forEach(function(section) { section.classList.remove('active'); });
    document.getElementById(item.dataset.section).classList.add('active');
  });
});

// Password show/hide functionality
function togglePassword(fieldId) {
  const field = document.getElementById(fieldId);
  const toggleIcon = field.nextElementSibling.querySelector('i');
  
  if (field.type === 'password') {
    field.type = 'text';
    toggleIcon.classList.remove('bi-eye-slash');
    toggleIcon.classList.add('bi-eye');
  } else {
    field.type = 'password';
    toggleIcon.classList.remove('bi-eye');
    toggleIcon.classList.add('bi-eye-slash');
  }
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
  let alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(alert) {
    let bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);

// Optional: Add password strength indicator
document.getElementById('new_password')?.addEventListener('input', function() {
  const password = this.value;
  const strengthIndicator = document.getElementById('password-strength');
  
  if (!strengthIndicator) {
    const div = document.createElement('div');
    div.id = 'password-strength';
    div.className = 'mt-1';
    this.parentNode.parentNode.appendChild(div);
  }
  
  const indicator = document.getElementById('password-strength');
  if (indicator) {
    let strength = 0;
    if (password.length >= 12) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    if (password.length === 0) {
      indicator.innerHTML = '';
    } else if (strength <= 2) {
      indicator.innerHTML = '<span class="text-danger">Weak password</span>';
    } else if (strength <= 4) {
      indicator.innerHTML = '<span class="text-warning">Medium password</span>';
    } else {
      indicator.innerHTML = '<span class="text-success">Strong password!</span>';
    }
  }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>