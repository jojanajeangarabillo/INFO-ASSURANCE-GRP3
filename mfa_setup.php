<?php
session_start();
require 'admin/db.connect.php';
require 'admin/GoogleAuthenticator.php';

if (!isset($_SESSION['temp_user_id'])) {
    header("Location: login.php");
    exit;
}

$ga = new GoogleAuthenticator();
$user_id = $_SESSION['temp_user_id'];
$username = $_SESSION['temp_username'];
$role_id = $_SESSION['temp_role_id'];

// Generate secret if not already in session
if (!isset($_SESSION['mfa_setup_secret'])) {
    $_SESSION['mfa_setup_secret'] = $ga->createSecret();
}

$secret = $_SESSION['mfa_setup_secret'];
$qrCodeUrl = $ga->getQRCodeGoogleUrl($username . "@J3RS", $secret, "J3RS-System");

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['mfa_code'] ?? '';
    
    if ($ga->verifyCode($secret, $code)) {
        // Success: Save secret to DB
        $stmt = $conn->prepare("UPDATE user SET mfa_secret = ? WHERE user_id = ?");
        $stmt->bind_param("si", $secret, $user_id);
        
        if ($stmt->execute()) {
            // Log user in fully
            $_SESSION['user_id'] = $_SESSION['temp_user_id'];
            $_SESSION['username'] = $_SESSION['temp_username'];
            $_SESSION['role_id'] = $_SESSION['temp_role_id'];
            
            // Track successful login
            $login_stmt = $conn->prepare("INSERT INTO login_history (user_id, login_time, status) VALUES (?, NOW(), 'success')");
            $login_stmt->bind_param("i", $user_id);
            $login_stmt->execute();
            
            // Store login_id in session for logout tracking
            $_SESSION['current_login_id'] = $conn->insert_id;
            
            // Log audit action for login
            if (function_exists('log_audit_action')) {
                log_audit_action('login', 'Authentication', 'User logged in successfully');
            }
            
            // Cleanup temp session
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_username']);
            unset($_SESSION['temp_role_id']);
            unset($_SESSION['mfa_setup_secret']);

            // Redirect based on role
            $role_id = $_SESSION['role_id'];
            switch ($role_id) {
                case 1: // Admin
                    header("Location: admin_dashboard.php");
                    break;
                case 2: // Customer
                    header("Location: customer_home.php");
                    break;
                case 3: // Seller
                case 4: // Dual
                    header("Location: seller_dashboard.php");
                    break;
                case 5: // Logistics
                    header("Location: logi_dashboard.php");
                    break;
                case 6: // Driver
                    header("Location: driver_dashboard.php");
                    break;
                default:
                    header("Location: landing.php");
                    break;
            }
            exit;
        } else {
            $error = "Failed to save MFA settings. Please try again.";
        }
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MFA Setup - J3RS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fdf2f6',
                            100: '#f9dbe5',
                            500: '#a61b4a',
                            900: '#610C27'
                        },
                        custombg: '#EFECE9'
                    }
                }
            }
        }
    </script>
    <style>
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .role-driver { background: #e0e7ff; color: #4338ca; }
        .role-logistics { background: #fef3c7; color: #d97706; }
        .role-seller { background: #d1fae5; color: #059669; }
        .role-customer { background: #dbeafe; color: #2563eb; }
        .role-admin { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body class="bg-custombg">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-brand-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-brand-900">
                    Setup MFA
                </h1>
                <p class="text-gray-500 mt-2 text-sm">Scan the QR code with your Google Authenticator app</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 p-3 bg-red-100 text-red-700 rounded-lg text-sm text-center font-medium">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="flex justify-center mb-8 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" alt="QR Code" class="rounded-lg shadow-sm">
            </div>

            <div class="bg-brand-50 p-4 rounded-xl mb-8">
                <p class="text-xs text-brand-900 font-semibold mb-1 uppercase tracking-wider">Manual Entry Key:</p>
                <code class="text-sm font-mono text-brand-500 break-all select-all"><?php echo htmlspecialchars($secret); ?></code>
            </div>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Verification Code</label>
                    <input type="text" name="mfa_code" maxlength="6" 
                        class="w-full p-4 border border-gray-300 rounded-xl text-center text-2xl tracking-[0.5em] font-bold focus:ring-2 focus:ring-brand-500 outline-none transition" 
                        placeholder="000000" 
                        pattern="[0-9]{6}"
                        title="Please enter 6-digit code"
                        autocomplete="off"
                        required>
                    <p class="text-xs text-gray-500 mt-2">Enter the 6-digit code from your authenticator app</p>
                </div>

                <button type="submit" 
                    class="w-full py-4 bg-brand-900 text-white rounded-xl font-bold hover:bg-brand-500 shadow-lg transition-all transform hover:-translate-y-0.5">
                    Verify & Complete Setup
                </button>
            </form>

            <div class="mt-6 text-center">
                <hr class="my-4">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                </div>
                <a href="login.php" class="text-sm text-gray-500 hover:text-brand-900 transition underline">Cancel and go back</a>
            </div>
        </div>
    </div>
</body>
</html>