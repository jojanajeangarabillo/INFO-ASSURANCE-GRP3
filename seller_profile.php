<?php
require_once 'auth.php';
require_roles([3, 4]);

require_once __DIR__ . '/admin/db.connect.php';
require_once __DIR__ . '/admin/email.helper.php';

$user_id = $_SESSION['user_id'] ?? 0;
$message = '';
$error = '';
$otpSent = false;
$otpVerified = false;

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Fetch session timeout from database
$query = "SELECT session_timeout_minutes FROM system_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$timeout_minutes = $row ? (int)$row['session_timeout_minutes'] : 30;

// Check session timeout
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout_minutes * 60) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['last_activity'] = time();

// Helper: Decrypt contact number
function decryptContactNumber($encrypted, $fallback = '') {
    if (empty($encrypted)) {
        return $fallback;
    }
    return decrypt_data($encrypted);
}

// Helper: Encrypt contact number
function encryptContactNumber($plaintext) {
    return encrypt_data($plaintext);
}

// Generate OTP
function generateOTP() {
    return sprintf("%06d", random_int(0, 999999));
}

// Send OTP email
function sendOTPEmail($email, $otp, $name = '') {
    $subject = "Password Change Verification - J3RS";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { padding: 20px; background-color: #f9f9f9; }
            .otp-code { font-size: 32px; font-weight: bold; color: #6d0f1b; padding: 15px 25px; background-color: #fff; display: inline-block; border-radius: 8px; letter-spacing: 5px; font-family: monospace; }
            .footer { margin-top: 20px; font-size: 12px; color: #666; }
            .warning { color: #ff0000; font-size: 12px; }
            .header { background-color: #6d0f1b; color: white; padding: 10px; text-align: center; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>J3RS Password Change Request</h2>
            </div>
            <p>Hello " . htmlspecialchars($name) . ",</p>
            <p>You have requested to change your password. Please use the following OTP to verify your identity:</p>
            <div style='text-align: center; margin: 30px 0;'>
                <div class='otp-code'>{$otp}</div>
            </div>
            <p>This OTP is valid for <strong>10 minutes</strong> and can only be used once.</p>
            <p class='warning'>⚠️ If you did not request this, please ignore this email and ensure your account is secure.</p>
            <div class='footer'>
                <p>Thank you,<br>J3RS Team</p>
                <p><small>This is an automated message, please do not reply to this email.</small></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "Hello " . $name . ",\n\n";
    $altBody .= "You have requested to change your password. Please use the following OTP to verify your identity:\n\n";
    $altBody .= "OTP: " . $otp . "\n\n";
    $altBody .= "This OTP is valid for 10 minutes and can only be used once.\n\n";
    $altBody .= "If you did not request this, please ignore this email and ensure your account is secure.\n\n";
    $altBody .= "Thank you,\nJ3RS Team";
    
    return send_email($email, $subject, $message, $altBody);
}

// Validate password against requirements
function validatePassword($password, &$errors = []) {
    $errors = [];
    if (strlen($password) < 12) $errors[] = "Password must be at least 12 characters long.";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password must contain at least one uppercase letter.";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "Password must contain at least one lowercase letter.";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain at least one number.";
    if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = "Password must contain at least one special character.";
    return empty($errors);
}

// Get document data for modal viewing
function getDocumentData($conn, $user_id, $type) {
    $column = ($type === 'permit') ? 'business_permit' : 'valid_id';
    $stmt = $conn->prepare("SELECT $column FROM seller WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $seller = $result->fetch_assoc();
    $stmt->close();
    
    if (!$seller || empty($seller[$column])) {
        return null;
    }
    
    $document_data = $seller[$column];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_buffer($finfo, $document_data);
    finfo_close($finfo);
    
    if (!$mime_type || $mime_type === 'application/octet-stream') {
        $magic_bytes = bin2hex(substr($document_data, 0, 8));
        if (strpos($magic_bytes, '89504e47') === 0) {
            $mime_type = 'image/png';
        } elseif (strpos($magic_bytes, 'ffd8') === 0) {
            $mime_type = 'image/jpeg';
        } elseif (strpos($magic_bytes, '47494638') === 0) {
            $mime_type = 'image/gif';
        } elseif (strpos($magic_bytes, '25504446') === 0) {
            $mime_type = 'application/pdf';
        } elseif (strpos($magic_bytes, '52494646') === 0 && strpos(bin2hex(substr($document_data, 8, 4)), '57454250') === 0) {
            $mime_type = 'image/webp';
        } else {
            $mime_type = 'application/octet-stream';
        }
    }
    
    return [
        'data' => base64_encode($document_data),
        'mime' => $mime_type,
        'is_pdf' => $mime_type === 'application/pdf',
        'is_image' => strpos($mime_type, 'image/') === 0
    ];
}

// Fetch current user data
$userStmt = $conn->prepare("SELECT user_id, username, email, first_name, last_name, is_active FROM user WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();
$userStmt->close();

if (!$userData || $userData['is_active'] != 1) {
    $error = "User account not found or inactive.";
}

// Fetch seller data
$sellerStmt = $conn->prepare("SELECT * FROM seller WHERE user_id = ?");
$sellerStmt->bind_param("i", $user_id);
$sellerStmt->execute();
$sellerResult = $sellerStmt->get_result();
$seller = $sellerResult->fetch_assoc();
$sellerStmt->close();

if (!$seller) {
    $error = "Seller profile not found.";
}

// Build full name
$fullName = '';
if (!empty($seller['full_name'])) {
    $fullName = $seller['full_name'];
} else {
    $firstName = $userData['first_name'] ?? '';
    $lastName = $userData['last_name'] ?? '';
    $fullName = trim($firstName . ' ' . $lastName);
}

// Decrypt contact number
$contactNumberDecrypted = decryptContactNumber($seller['contact_number'] ?? '', '');

// Check session for OTP status
if (isset($_SESSION['otp_sent']) && $_SESSION['otp_sent'] === true) {
    $otpSent = true;
}
if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
    $otpVerified = true;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        $error = 'Invalid request token. Please refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        // STEP 1: Send OTP with current password validation
        if ($action === 'send_otp') {
            $current_password = $_POST['current_password'] ?? '';
            $user_email = $userData['email'];
            
            if (empty($current_password)) {
                $error = "Current password is required to request OTP.";
            } else {
                // Validate current password
                $passStmt = $conn->prepare("SELECT password FROM user WHERE user_id = ?");
                $passStmt->bind_param("i", $user_id);
                $passStmt->execute();
                $currentUser = $passStmt->get_result()->fetch_assoc();
                $passStmt->close();
                
                if (password_verify($current_password, $currentUser['password'])) {
                    // Generate and store new OTP (don't clear previous OTP, just update)
                    $otp = generateOTP();
                    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                    $storeStmt = $conn->prepare("UPDATE user SET otp_code = ?, otp_expiry = ? WHERE user_id = ?");
                    $storeStmt->bind_param("ssi", $otp, $otp_expiry, $user_id);
                    
                    if ($storeStmt->execute()) {
                        $emailSent = sendOTPEmail($user_email, $otp, $fullName);
                        
                        if ($emailSent) {
                            log_audit_action('otp_request', 'Seller Security', 'User requested password change OTP');
                            $_SESSION['otp_sent'] = true;
                            $_SESSION['otp_email'] = $user_email;
                            $_SESSION['otp_code'] = $otp; // Store OTP in session for debugging
                            $otpSent = true;
                            $message = "✓ OTP has been sent to your email address: " . htmlspecialchars($user_email) . ". Please check your inbox or spam folder.";
                            error_log("OTP sent to user $user_id: $otp");
                        } else {
                            $error = "Failed to send OTP email. Please check your email settings.";
                            error_log("Failed to send OTP email to $user_email");
                        }
                    } else {
                        $error = "Failed to store OTP. Please try again.";
                        error_log("Failed to store OTP for user $user_id");
                    }
                    $storeStmt->close();
                } else {
                    $error = "Current password is incorrect.";
                }
            }
        }
        
        // STEP 2: Verify OTP
        elseif ($action === 'verify_otp') {
            $entered_otp = trim($_POST['otp_code'] ?? '');
            
            if (empty($entered_otp) || !preg_match('/^\d{6}$/', $entered_otp)) {
                $error = "Please enter a valid 6-digit OTP code.";
            } else {
                // Check OTP in database - make sure it exists, matches, and hasn't expired
                $checkStmt = $conn->prepare("SELECT otp_code, otp_expiry FROM user WHERE user_id = ? AND otp_code IS NOT NULL");
                $checkStmt->bind_param("i", $user_id);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                $userOtpData = $result->fetch_assoc();
                $checkStmt->close();
                
                if ($userOtpData && !empty($userOtpData['otp_code'])) {
                    $storedOtp = $userOtpData['otp_code'];
                    $expiryTime = strtotime($userOtpData['otp_expiry']);
                    $currentTime = time();
                    
                    error_log("Verifying OTP - Entered: $entered_otp, Stored: $storedOtp, Expires: " . date('Y-m-d H:i:s', $expiryTime) . ", Now: " . date('Y-m-d H:i:s', $currentTime));
                    
                    if ($storedOtp === $entered_otp) {
                        if ($currentTime <= $expiryTime) {
                            log_audit_action('otp_verify', 'Seller Security', 'User verified password change OTP');
                            $_SESSION['otp_verified'] = true;
                            $_SESSION['verified_otp'] = $entered_otp;
                            $otpVerified = true;
                            $message = "✓ OTP verified successfully! You can now change your password.";
                            error_log("OTP verified for user $user_id");
                        } else {
                            $error = "OTP has expired. Please request a new OTP.";
                            error_log("OTP expired for user $user_id");
                        }
                    } else {
                        // Track failed attempts
                        $_SESSION['otp_attempts'] = ($_SESSION['otp_attempts'] ?? 0) + 1;
                        $remaining = 3 - $_SESSION['otp_attempts'];
                        
                        if ($_SESSION['otp_attempts'] >= 3) {
                            $error = "Too many failed attempts. Please request a new OTP.";
                            // Clear OTP after too many attempts
                            $clearStmt = $conn->prepare("UPDATE user SET otp_code = NULL, otp_expiry = NULL WHERE user_id = ?");
                            $clearStmt->bind_param("i", $user_id);
                            $clearStmt->execute();
                            $clearStmt->close();
                            unset($_SESSION['otp_sent'], $_SESSION['otp_attempts']);
                            $otpSent = false;
                        } else {
                            $error = "Invalid OTP code. $remaining attempt(s) remaining.";
                        }
                        error_log("Failed OTP attempt for user $user_id. Entered: $entered_otp, Expected: $storedOtp");
                    }
                } else {
                    $error = "No OTP found. Please request a new OTP.";
                    error_log("No OTP found for user $user_id");
                }
            }
        }
        
        // Resend OTP
        elseif ($action === 'resend_otp') {
            $user_email = $userData['email'];
            
            $otp = generateOTP();
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $storeStmt = $conn->prepare("UPDATE user SET otp_code = ?, otp_expiry = ? WHERE user_id = ?");
            $storeStmt->bind_param("ssi", $otp, $otp_expiry, $user_id);
            
            if ($storeStmt->execute() && sendOTPEmail($user_email, $otp, $fullName)) {
                $_SESSION['otp_sent'] = true;
                $_SESSION['otp_attempts'] = 0;
                $message = "✓ New OTP has been sent to your email address: " . htmlspecialchars($user_email);
                error_log("OTP resent to user $user_id: $otp");
            } else {
                $error = "Failed to resend OTP. Please try again.";
            }
            $storeStmt->close();
        }
        
        // STEP 3: Change Password
        elseif ($action === 'update_password') {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Check if OTP was verified
            if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
                $error = "Please verify OTP first before changing password.";
            } elseif (empty($new_password) || empty($confirm_password)) {
                $error = "New password fields are required.";
            } elseif ($new_password !== $confirm_password) {
                $error = "New password and confirmation do not match.";
            } else {
                // Validate password strength
                $password_errors = [];
                if (!validatePassword($new_password, $password_errors)) {
                    $error = implode("<br>", $password_errors);
                } else {
                    // Verify OTP still valid in database before updating
                    $otpCheckStmt = $conn->prepare("SELECT otp_code FROM user WHERE user_id = ? AND otp_code IS NOT NULL AND otp_expiry > NOW()");
                    $otpCheckStmt->bind_param("i", $user_id);
                    $otpCheckStmt->execute();
                    $hasValidOtp = $otpCheckStmt->get_result()->num_rows > 0;
                    $otpCheckStmt->close();
                    
                    if (!$hasValidOtp) {
                        $error = "OTP has expired or is invalid. Please request a new OTP.";
                        unset($_SESSION['otp_verified'], $_SESSION['otp_sent'], $_SESSION['verified_otp']);
                        $otpVerified = false;
                        $otpSent = false;
                    } else {
                        // Check if new password is same as old password
                        $passStmt = $conn->prepare("SELECT password FROM user WHERE user_id = ?");
                        $passStmt->bind_param("i", $user_id);
                        $passStmt->execute();
                        $currentUser = $passStmt->get_result()->fetch_assoc();
                        $passStmt->close();
                        
                        if (password_verify($new_password, $currentUser['password'])) {
                            $error = "New password cannot be the same as your current password.";
                        } else {
                            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                            $updateStmt = $conn->prepare("UPDATE user SET password = ?, otp_code = NULL, otp_expiry = NULL WHERE user_id = ?");
                            $updateStmt->bind_param("si", $new_hash, $user_id);
                            
                            if ($updateStmt->execute()) {
                                log_audit_action('update', 'Seller Security', 'User changed their password');
                                $message = "✓ Password changed successfully! You will be redirected to login.";
                                
                                // Send confirmation email
                                $subject = "Password Changed Successfully - J3RS";
                                $body = "<html><body><h2>Password Changed</h2><p>Your password has been changed successfully at " . date('Y-m-d H:i:s') . ".</p><p>If you did not do this, please contact support immediately.</p></body></html>";
                                send_email($userData['email'], $subject, $body);
                                
                                // Clear OTP session data
                                unset($_SESSION['otp_verified'], $_SESSION['otp_sent'], $_SESSION['otp_attempts'], $_SESSION['verified_otp']);
                                
                                // Redirect to login after 2 seconds
                                echo '<script>setTimeout(function() { window.location.href = "logout.php"; }, 2000);</script>';
                            } else {
                                $error = "Failed to change password. Please try again.";
                            }
                            $updateStmt->close();
                        }
                    }
                }
            }
        }
        
        // Handle Profile Update
        elseif ($action === 'update_profile') {
            $shop_name = trim($_POST['shop_name'] ?? '');
            $shop_address = trim($_POST['shop_address'] ?? '');
            $contact_number_raw = trim($_POST['contact_number'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $age = trim($_POST['age'] ?? '');
            $tin_id = trim($_POST['tin_id'] ?? '');
            $business_category = trim($_POST['business_category'] ?? '');
            $additional_info = trim($_POST['additional_info'] ?? '');
            $full_name_profile = trim($_POST['full_name'] ?? '');
            
            $nameParts = explode(' ', $full_name_profile, 2);
            $first_name = $nameParts[0] ?? '';
            $last_name = $nameParts[1] ?? '';
            
            $validation_errors = [];
            if (empty($shop_name)) $validation_errors[] = "Shop name is required.";
            if (empty($full_name_profile)) $validation_errors[] = "Full name is required.";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $validation_errors[] = "Valid email address is required.";
            if (!empty($age) && (!is_numeric($age) || $age < 18 || $age > 120)) $validation_errors[] = "Age must be between 18 and 120.";
            if (empty($business_category)) $validation_errors[] = "Business category is required.";
            
            if (empty($validation_errors)) {
                try {
                    $conn->begin_transaction();
                    
                    $emailCheckStmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? AND user_id != ?");
                    $emailCheckStmt->bind_param("si", $email, $user_id);
                    $emailCheckStmt->execute();
                    if ($emailCheckStmt->get_result()->num_rows > 0) {
                        throw new Exception("Email address is already in use by another account.");
                    }
                    $emailCheckStmt->close();
                    
                    $encrypted_contact = encryptContactNumber($contact_number_raw);
                    
                    $updateUserStmt = $conn->prepare("UPDATE user SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
                    $updateUserStmt->bind_param("sssi", $first_name, $last_name, $email, $user_id);
                    $updateUserStmt->execute();
                    $updateUserStmt->close();
                    
                    $updateSellerStmt = $conn->prepare("
                        UPDATE seller 
                        SET full_name = ?, 
                            shop_name = ?, 
                            shop_address = ?, 
                            contact_number = ?,
                            business_category = ?,
                            tin_id = ?,
                            age = ?,
                            additional_info = ?
                        WHERE user_id = ?
                    ");
                    $updateSellerStmt->bind_param("ssssssssi", 
                        $full_name_profile, 
                        $shop_name, 
                        $shop_address, 
                        $encrypted_contact,
                        $business_category,
                        $tin_id,
                        $age,
                        $additional_info,
                        $user_id
                    );
                    
                    if ($updateSellerStmt->execute()) {
                        $conn->commit();
                        log_audit_action('update', 'Seller Profile', 'User updated their seller profile information');
                        $message = "Profile updated successfully!";
                        $_SESSION['first_name'] = $first_name;
                        $_SESSION['last_name'] = $last_name;
                        $userData['email'] = $email;
                        $seller['shop_name'] = $shop_name;
                        $seller['shop_address'] = $shop_address;
                        $seller['business_category'] = $business_category;
                        $seller['tin_id'] = $tin_id;
                        $seller['age'] = $age;
                        $seller['additional_info'] = $additional_info;
                        $fullName = $full_name_profile;
                        $contactNumberDecrypted = $contact_number_raw;
                    } else {
                        throw new Exception("Failed to update seller information.");
                    }
                    $updateSellerStmt->close();
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = $e->getMessage();
                    error_log("Seller profile update error: " . $e->getMessage());
                }
            } else {
                $error = implode("<br>", $validation_errors);
            }
        }
        
        // Handle Shop Image Upload
        elseif ($action === 'upload_image' && isset($_FILES['shop_image'])) {
            if ($_FILES['shop_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo($_FILES['shop_image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $error = "Invalid image type. Allowed: JPG, JPEG, PNG, WEBP";
                } elseif ($_FILES['shop_image']['size'] > 5 * 1024 * 1024) {
                    $error = "File too large. Max 5MB.";
                } else {
                    $imgData = file_get_contents($_FILES['shop_image']['tmp_name']);
                    $stmt = $conn->prepare("UPDATE seller SET shop_image = ? WHERE user_id = ?");
                    $stmt->bind_param("bi", $imgData, $user_id);
                    $stmt->send_long_data(0, $imgData);
                    if ($stmt->execute()) {
                        $message = "Shop image updated successfully!";
                        $seller['shop_image'] = $imgData;
                    } else {
                        $error = "Failed to update shop image.";
                    }
                    $stmt->close();
                }
            } else {
                $error = "Please select an image file to upload.";
            }
        }
        
        // Handle Document Upload
        elseif ($action === 'upload_document' && isset($_FILES['document_file'])) {
            $doc_type = $_POST['document_type'] ?? '';
            if ($_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];
                $ext = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $error = "Invalid file type. Allowed: JPG, JPEG, PNG, PDF, WEBP";
                } elseif ($_FILES['document_file']['size'] > 10 * 1024 * 1024) {
                    $error = "File too large. Max 10MB.";
                } else {
                    $docData = file_get_contents($_FILES['document_file']['tmp_name']);
                    $column = ($doc_type === 'permit') ? 'business_permit' : 'valid_id';
                    $stmt = $conn->prepare("UPDATE seller SET $column = ? WHERE user_id = ?");
                    $stmt->bind_param("bi", $docData, $user_id);
                    $stmt->send_long_data(0, $docData);
                    if ($stmt->execute()) {
                        $message = ucfirst($doc_type) . " document updated successfully!";
                        if ($doc_type === 'permit') $seller['business_permit'] = $docData;
                        else $seller['valid_id'] = $docData;
                    } else {
                        $error = "Failed to update document.";
                    }
                    $stmt->close();
                }
            } else {
                $error = "Please select a file to upload.";
            }
        }
    }
}

// Handle AJAX request for document viewing
if (isset($_GET['ajax_get_document'])) {
    header('Content-Type: application/json');
    $doc_type = $_GET['type'] ?? '';
    
    if (!in_array($doc_type, ['permit', 'valid_id'])) {
        echo json_encode(['error' => 'Invalid document type']);
        exit;
    }
    
    $docData = getDocumentData($conn, $user_id, $doc_type);
    if ($docData === null) {
        echo json_encode(['error' => 'Document not found']);
        exit;
    }
    
    echo json_encode($docData);
    exit;
}

// Helper: Display document button
function displayDocumentButton($blob, $type, $docName) {
    if (empty($blob)) {
        return '<p class="text-muted">No document uploaded yet.</p>';
    }
    return '<button type="button" class="btn btn-outline-primary btn-sm" onclick="viewDocument(\'' . $type . '\', \'' . htmlspecialchars($docName) . '\')">
                <i class="bi bi-eye"></i> View ' . htmlspecialchars($docName) . '
            </button>';
}

// Helper: Display image from BLOB
function displayShopImage($blob) {
    if (empty($blob)) {
        return 'default-shop.png';
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_buffer($finfo, $blob);
    finfo_close($finfo);
    return 'data:' . $mime . ';base64,' . base64_encode($blob);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Store Settings - Seller Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
body { background: #f5f5f5; font-family: 'Segoe UI', sans-serif; margin: 0; }
.sidebar { width: 240px; position: fixed; height: 100%; z-index: 1000; }
.sidebar.collapsed { width: 70px; }
.main-content { margin-left: 240px; transition: 0.3s; padding: 20px; }
.sidebar.collapsed ~ .main-content { margin-left: 70px; }
.card-custom { background: #fff; border-radius: 16px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
:root { --brand: #6d0f1b; }
.btn-brand { background: var(--brand); color: white; border-radius: 10px; transition: all 0.3s; }
.btn-brand:hover { background: #500b14; color: white; transform: translateY(-1px); }
.btn-brand:disabled { background: #aaa; cursor: not-allowed; transform: none; }
.tab-btn { cursor: pointer; padding: 10px 20px; border-radius: 10px; transition: all 0.3s; display: inline-block; }
.tab-btn:hover { background: #e9ecef; }
.tab-active { background: var(--brand); color: white; }
.document-preview { max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 10px; }
.shop-image-preview { max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 10px; border: 2px solid #dee2e6; margin-top: 10px; }
.password-strength { height: 5px; margin-top: 5px; border-radius: 3px; transition: all 0.3s; }
.strength-weak { width: 25%; background-color: #dc3545; }
.strength-fair { width: 50%; background-color: #ffc107; }
.strength-good { width: 75%; background-color: #17a2b8; }
.strength-strong { width: 100%; background-color: #28a745; }
.otp-input { font-size: 28px; letter-spacing: 8px; text-align: center; font-family: monospace; }
.pdf-viewer { width: 100%; height: 70vh; border: none; }
.modal-xl { max-width: 90%; }
.otp-step { border-left: 3px solid #6d0f1b; padding-left: 20px; margin-bottom: 25px; }
.step-badge { display: inline-block; width: 32px; height: 32px; background: #6d0f1b; color: white; border-radius: 50%; text-align: center; line-height: 32px; margin-right: 12px; font-weight: bold; }
.step-completed { background: #28a745; }
.step-active { background: #6d0f1b; }
.step-pending { background: #6c757d; }
</style>
<script>
const timeoutMs = <?php echo $timeout_minutes * 60 * 1000; ?>;
let logoutTimer;
function resetTimer() { clearTimeout(logoutTimer); logoutTimer = setTimeout(() => { alert("Session expired due to inactivity."); window.location.href = "logout.php"; }, timeoutMs); }
document.addEventListener("mousemove", resetTimer);
document.addEventListener("keypress", resetTimer);
document.addEventListener("click", resetTimer);
resetTimer();

function switchTab(tabId, el) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('tab-active'));
    el.classList.add('tab-active');
}

function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            let preview = document.getElementById(previewId);
            if (!preview) {
                const container = document.getElementById('shop_image_preview_container');
                const img = document.createElement('img');
                img.id = previewId;
                img.className = 'shop-image-preview';
                img.src = e.target.result;
                container.innerHTML = '';
                container.appendChild(img);
            } else preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewDocument(input, previewId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const preview = document.getElementById(previewId);
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => preview.innerHTML = '<img src="' + e.target.result + '" class="document-preview" style="max-width:300px; max-height:200px;">';
            reader.readAsDataURL(file);
        } else preview.innerHTML = '<div class="alert alert-info mt-2">Document selected: ' + file.name + '</div>';
    }
}

function validateOTP(input) { 
    input.value = input.value.replace(/[^0-9]/g, '').slice(0, 6); 
}

function validatePasswordForm() {
    const pwd = document.getElementById('new_password').value;
    const confirm = document.getElementById('confirm_password').value;
    if (pwd !== confirm) { alert('Passwords do not match!'); return false; }
    if (pwd.length < 12) { alert('Password must be at least 12 characters.'); return false; }
    if (!/[A-Z]/.test(pwd)) { alert('Password must contain an uppercase letter.'); return false; }
    if (!/[a-z]/.test(pwd)) { alert('Password must contain a lowercase letter.'); return false; }
    if (!/[0-9]/.test(pwd)) { alert('Password must contain a number.'); return false; }
    if (!/[^A-Za-z0-9]/.test(pwd)) { alert('Password must contain a special character.'); return false; }
    return true;
}

function viewDocument(type, docName) {
    const modal = new bootstrap.Modal(document.getElementById('documentViewerModal'));
    const modalTitle = document.getElementById('documentViewerModalLabel');
    const modalBody = document.getElementById('documentViewerModalBody');
    
    modalTitle.innerHTML = '<i class="bi bi-file-earmark-text"></i> ' + docName;
    modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><br>Loading document...</div>';
    modal.show();
    
    fetch('?ajax_get_document=1&type=' + encodeURIComponent(type))
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalBody.innerHTML = '<div class="alert alert-danger m-3">' + data.error + '</div>';
                return;
            }
            
            if (data.is_pdf) {
                modalBody.innerHTML = '<embed src="data:' + data.mime + ';base64,' + data.data + '" class="pdf-viewer" type="application/pdf">';
            } else if (data.is_image) {
                modalBody.innerHTML = '<div class="text-center"><img src="data:' + data.mime + ';base64,' + data.data + '" class="document-preview" alt="Document"></div>';
            } else {
                modalBody.innerHTML = `
                    <div class="alert alert-info m-3">
                        <i class="bi bi-info-circle"></i> This file type cannot be previewed directly.
                        <hr>
                        <a href="data:${data.mime};base64,${data.data}" download="document" class="btn btn-primary">
                            <i class="bi bi-download"></i> Download Document
                        </a>
                    </div>
                `;
            }
        })
        .catch(error => {
            modalBody.innerHTML = '<div class="alert alert-danger m-3">Error loading document: ' + error + '</div>';
        });
}

document.addEventListener('DOMContentLoaded', () => {
    const pwd = document.getElementById('new_password');
    const confirm = document.getElementById('confirm_password');
    const strengthDiv = document.getElementById('passwordStrength');
    if (pwd) {
        pwd.addEventListener('input', () => {
            let strength = 0;
            const val = pwd.value;
            if (val.length >= 12) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[a-z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;
            strengthDiv.className = 'password-strength';
            if (strength <= 2) strengthDiv.classList.add('strength-weak');
            else if (strength <= 3) strengthDiv.classList.add('strength-fair');
            else if (strength <= 4) strengthDiv.classList.add('strength-good');
            else strengthDiv.classList.add('strength-strong');
        });
    }
    if (confirm) {
        confirm.addEventListener('input', () => {
            const matchDiv = document.getElementById('passwordMatch');
            if (pwd && pwd.value === confirm.value && confirm.value !== '') {
                matchDiv.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Passwords match!</span>';
            } else if (confirm.value !== '') {
                matchDiv.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Passwords do not match!</span>';
            } else {
                matchDiv.innerHTML = '';
            }
        });
    }
    
    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
    });
});

function toggleSidebar() { document.getElementById("sidebar").classList.toggle("collapsed"); }
</script>
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header"><div class="toggle-btn" onclick="toggleSidebar()">☰</div><div class="logo-text">Seller</div></div>
    <a href="seller_dashboard.php"><i class="bi bi-speedometer2"></i><span class="text">Dashboard</span></a>
    <a href="seller_products.php"><i class="bi bi-box-seam"></i><span class="text">Products</span></a>
    <a href="seller_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
    <a href="seller_inventory.php"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
    <a href="seller_profile.php" class="active"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
    <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i><span class="text">Logout</span></a>
</div>
<div class="main-content">
    <div class="container-fluid">
        <h3 class="fw-bold">Store Settings</h3>
        <p class="text-muted">Manage your store profile, personal information, and business details.</p>
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <div class="tab-btn tab-active" onclick="switchTab('profile', this)">Store Profile</div>
            <div class="tab-btn" onclick="switchTab('personal', this)">Personal Information</div>
            <div class="tab-btn" onclick="switchTab('documents', this)">Documents & Images</div>
            <div class="tab-btn" onclick="switchTab('security', this)">Security</div>
        </div>

        <!-- Store Profile Tab -->
        <div id="profile" class="tab-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="update_profile">
                <div class="card-custom">
                    <h5 class="fw-bold mb-3">Store Information</h5>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">SHOP NAME *</label>
                        <input type="text" name="shop_name" class="form-control" value="<?php echo htmlspecialchars($seller['shop_name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">SHOP ADDRESS</label>
                        <textarea name="shop_address" class="form-control" rows="3"><?php echo htmlspecialchars($seller['shop_address'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">CONTACT NUMBER</label>
                        <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($contactNumberDecrypted); ?>">
                        <small class="text-muted">Format: +63XXXXXXXXXX or 09XXXXXXXXX</small>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-brand px-4 py-2">
                        <i class="bi bi-save"></i> Save Store Information
                    </button>
                </div>
            </form>
        </div>

        <!-- Personal Information Tab -->
        <div id="personal" class="tab-content" style="display: none;">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="update_profile">
                <div class="card-custom">
                    <h5 class="fw-bold mb-3">Personal Information</h5>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">FULL NAME *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($fullName); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">EMAIL ADDRESS *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                        <small class="text-muted">Changing email will update your login credentials.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">AGE *</label>
                        <input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($seller['age'] ?? ''); ?>" min="18" max="120" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">TIN ID</label>
                        <input type="text" name="tin_id" class="form-control" value="<?php echo htmlspecialchars($seller['tin_id'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">BUSINESS CATEGORY *</label>
                        <select name="business_category" class="form-select" required>
                            <option value="">Select category</option>
                            <?php $cats = ['Men', 'Women', 'Electronics', 'Furniture', 'Food']; foreach($cats as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo ($seller['business_category'] ?? '') === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">ADDITIONAL INFORMATION</label>
                        <textarea name="additional_info" class="form-control" rows="2"><?php echo htmlspecialchars($seller['additional_info'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-brand px-4 py-2">
                        <i class="bi bi-save"></i> Save Personal Information
                    </button>
                </div>
            </form>
        </div>

        <!-- Documents & Images Tab -->
        <div id="documents" class="tab-content" style="display: none;">
            <div class="card-custom">
                <h5 class="fw-bold mb-3">Shop Image</h5>
                <?php if (!empty($seller['shop_image'])): ?>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Current Shop Image</label>
                    <div><img src="<?php echo displayShopImage($seller['shop_image']); ?>" class="shop-image-preview"></div>
                </div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="upload_image">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Upload New Shop Image</label>
                        <input type="file" name="shop_image" class="form-control" accept=".jpg,.jpeg,.png,.webp" onchange="previewImage(this, 'shop_image_preview')">
                        <div id="shop_image_preview_container" class="mt-2"></div>
                        <small class="text-muted">Max 5MB. JPG, JPEG, PNG, WEBP</small>
                    </div>
                    <button type="submit" class="btn btn-brand"><i class="bi bi-upload"></i> Upload Shop Image</button>
                </form>
            </div>
            <div class="card-custom">
                <h5 class="fw-bold mb-3">Business Permit</h5>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Current Business Permit</label>
                    <div><?php echo displayDocumentButton($seller['business_permit'] ?? null, 'permit', 'Business Permit'); ?></div>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="upload_document">
                    <input type="hidden" name="document_type" value="permit">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Upload New Business Permit</label>
                        <input type="file" name="document_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp" onchange="previewDocument(this, 'permit_preview')">
                        <div id="permit_preview" class="mt-2"></div>
                        <small class="text-muted">Max 10MB. JPG, JPEG, PNG, PDF, WEBP</small>
                    </div>
                    <button type="submit" class="btn btn-brand"><i class="bi bi-upload"></i> Upload Business Permit</button>
                </form>
            </div>
            <div class="card-custom">
                <h5 class="fw-bold mb-3">Valid ID</h5>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Current Valid ID</label>
                    <div><?php echo displayDocumentButton($seller['valid_id'] ?? null, 'valid_id', 'Valid ID'); ?></div>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="upload_document">
                    <input type="hidden" name="document_type" value="valid_id">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Upload New Valid ID</label>
                        <input type="file" name="document_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp" onchange="previewDocument(this, 'valid_id_preview')">
                        <div id="valid_id_preview" class="mt-2"></div>
                        <small class="text-muted">Max 10MB. JPG, JPEG, PNG, PDF, WEBP</small>
                    </div>
                    <button type="submit" class="btn btn-brand"><i class="bi bi-upload"></i> Upload Valid ID</button>
                </form>
            </div>
        </div>

        <!-- Security Tab -->
        <div id="security" class="tab-content" style="display: none;">
            <div class="card-custom">
                <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock"></i> Change Password</h5>
                <p class="text-muted">Change your account password securely with OTP verification.</p>
                
                <!-- STEP 1: Request OTP -->
                <div class="otp-step">
                    <div class="d-flex align-items-center mb-3">
                        <span class="step-badge <?php echo $otpSent ? 'step-completed' : 'step-active'; ?>">1</span>
                        <h6 class="mb-0">Request OTP Verification</h6>
                    </div>
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="action" value="send_otp">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">CURRENT PASSWORD *</label>
                            <div class="password-input-wrapper" style="position: relative;">
                                <input type="password" name="current_password" class="form-control" required <?php echo ($otpSent) ? 'disabled' : ''; ?>>
                                <span class="password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                    <i class="bi bi-eye-slash"></i>
                                </span>
                            </div>
                            <small class="text-muted">Enter your current password to receive OTP.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">EMAIL ADDRESS</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" readonly disabled>
                            </div>
                            <small class="text-muted">OTP will be sent to this email address.</small>
                        </div>
                        <button type="submit" class="btn btn-brand" <?php echo ($otpSent) ? 'disabled' : ''; ?>>
                            <i class="bi bi-envelope-paper"></i> Send OTP
                        </button>
                        <?php if ($otpSent): ?>
                            <span class="text-success ms-3"><i class="bi bi-check-circle-fill"></i> OTP Sent!</span>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- STEP 2: Verify OTP -->
                <div class="otp-step">
                    <div class="d-flex align-items-center mb-3">
                        <span class="step-badge <?php echo $otpVerified ? 'step-completed' : ($otpSent ? 'step-active' : 'step-pending'); ?>">2</span>
                        <h6 class="mb-0">Verify OTP Code</h6>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="action" value="verify_otp">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">ENTER OTP CODE</label>
                            <input type="text" name="otp_code" class="form-control otp-input" placeholder="000000" maxlength="6" oninput="validateOTP(this)" <?php echo (!$otpSent || $otpVerified) ? 'disabled' : 'required autofocus'; ?>>
                            <small class="text-muted">Enter the 6-digit code sent to your email. Valid for 10 minutes.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-brand" <?php echo (!$otpSent || $otpVerified) ? 'disabled' : ''; ?>>
                                <i class="bi bi-check-circle"></i> Verify OTP
                            </button>
                            <?php if ($otpSent && !$otpVerified): ?>
                                <button type="submit" name="action" value="resend_otp" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-repeat"></i> Resend OTP
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if ($otpVerified): ?>
                            <div class="mt-2 text-success">
                                <i class="bi bi-check-circle-fill"></i> OTP Verified Successfully!
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- STEP 3: Set New Password -->
                <div class="otp-step">
                    <div class="d-flex align-items-center mb-3">
                        <span class="step-badge <?php echo $otpVerified ? 'step-active' : 'step-pending'; ?>">3</span>
                        <h6 class="mb-0">Set New Password</h6>
                    </div>
                    <?php if ($otpVerified): ?>
                        <form method="POST" onsubmit="return validatePasswordForm()">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            <input type="hidden" name="action" value="update_password">
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">NEW PASSWORD *</label>
                                <div class="password-input-wrapper" style="position: relative;">
                                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                                    <span class="password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                        <i class="bi bi-eye-slash"></i>
                                    </span>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                                <small class="text-muted">Password must be at least 12 characters and include uppercase, lowercase, number, and special character.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">CONFIRM NEW PASSWORD *</label>
                                <div class="password-input-wrapper" style="position: relative;">
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                    <span class="password-toggle" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                        <i class="bi bi-eye-slash"></i>
                                    </span>
                                </div>
                                <div id="passwordMatch" class="small mt-1"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-key"></i> Update Password
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='seller_profile.php'">
                                <i class="bi bi-x-circle"></i> Cancel
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-lock"></i> Please complete steps 1 and 2 first to enable password change.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-custom">
                <h6 class="fw-bold mb-2"><i class="bi bi-shield-check"></i> Password Security Tips</h6>
                <ul class="text-muted small mb-0">
                    <li>Use at least 12 characters</li>
                    <li>Include uppercase and lowercase letters</li>
                    <li>Include numbers and special characters</li>
                    <li>Avoid common words or personal information</li>
                    <li>Never share your password with anyone</li>
                    <li>Use a unique password for each account</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Document Viewer Modal -->
<div class="modal fade" id="documentViewerModal" tabindex="-1" aria-labelledby="documentViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="documentViewerModalLabel">
                    <i class="bi bi-file-earmark-text"></i> Document Viewer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="documentViewerModalBody">
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <br>Loading document...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>