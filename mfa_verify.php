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
$role_id = $_SESSION['temp_role_id'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['mfa_code'] ?? '';
    
    // Fetch secret from DB
    $stmt = $conn->prepare("SELECT mfa_secret FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $secret = $row['mfa_secret'];
    
    if ($ga->verifyCode($secret, $code)) {
        // Success: Log user in fully
        $_SESSION['user_id'] = $_SESSION['temp_user_id'];
        $_SESSION['username'] = $_SESSION['temp_username'];
        $_SESSION['role_id'] = $_SESSION['temp_role_id'];
        
        // Track successful login
        $login_stmt = $conn->prepare("INSERT INTO login_history (user_id, login_time, status) VALUES (?, NOW(), 'success')");
        $login_stmt->bind_param("i", $user_id);
        $login_stmt->execute();
        
        // Store login_id in session for logout tracking
        $_SESSION['current_login_id'] = $conn->insert_id;
        
        // Log audit action for login if function exists
        if (function_exists('log_audit_action')) {
            log_audit_action('login', 'Authentication', 'User logged in successfully');
        }
        
        // Cleanup temp session
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_username']);
        unset($_SESSION['temp_role_id']);

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
        $error = "Invalid MFA code. Please try again.";
        
        // Track failed MFA attempt
        $failed_stmt = $conn->prepare("INSERT INTO login_history (user_id, login_time, status) VALUES (?, NOW(), 'mfa_failed')");
        $failed_stmt->bind_param("i", $user_id);
        $failed_stmt->execute();
        $failed_stmt->close();
    }
}

// Get user info for display
$userStmt = $conn->prepare("SELECT first_name, last_name, role_id FROM user WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userInfo = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

$fullName = ($userInfo['first_name'] ?? '') . ' ' . ($userInfo['last_name'] ?? '');
$roleNames = [1 => 'Admin', 2 => 'Customer', 3 => 'Seller', 4 => 'Dual', 5 => 'Logistics', 6 => 'Driver'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MFA Verification - J3RS</title>
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
        .role-dual { background: #f3e8ff; color: #9333ea; }
        
        .countdown {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        
        .resend-link {
            display: none;
            margin-top: 15px;
        }
        
        .resend-link a {
            color: #610C27;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-custombg">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-brand-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040L3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622l-.382-3.016z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-brand-900">
                    Two-Factor Authentication
                </h1>
                <p class="text-gray-500 text-sm mt-2">Enter the 6-digit code from your authenticator app</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 p-3 bg-red-100 text-red-700 rounded-lg text-sm text-center font-medium">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo htmlspecialchars($error); ?>
                    <div class="text-xs mt-1 text-red-600">Please check your authenticator app and try again.</div>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6" id="mfaForm">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Authenticator Code</label>
                    <input type="text" name="mfa_code" maxlength="6" autofocus
                        class="w-full p-4 border border-gray-300 rounded-xl text-center text-2xl tracking-[0.5em] font-bold focus:ring-2 focus:ring-brand-500 outline-none transition" 
                        placeholder="000000" 
                        pattern="[0-9]{6}"
                        title="Please enter 6-digit code"
                        autocomplete="off"
                        required>

                </div>

                <button type="submit" 
                    class="w-full py-4 bg-brand-900 text-white rounded-xl font-bold hover:bg-brand-500 shadow-lg transition-all transform hover:-translate-y-0.5">
                    Verify & Sign In
                </button>
            </form>

            <div class="mt-6 text-center space-y-3">
                <a href="login.php" class="text-sm text-gray-500 hover:text-brand-900 transition underline block mt-4">
                    ← Back to login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-submit when 6 digits are entered
        document.querySelector('input[name="mfa_code"]').addEventListener('input', function(e) {
            if (this.value.length === 6 && /^\d+$/.test(this.value)) {
                document.getElementById('mfaForm').submit();
            }
        });
        
        // Countdown timer for code expiration (30 seconds)
        let timeLeft = 30;
        const countdownEl = document.getElementById('countdown');
        const resendLink = document.getElementById('resendLink');
        
        function updateCountdown() {
            if (timeLeft > 0) {
                countdownEl.innerHTML = `Code expires in ${timeLeft} seconds`;
                timeLeft--;
                setTimeout(updateCountdown, 1000);
            } else {
                countdownEl.innerHTML = 'Code expired. Please request a new one.';
                resendLink.style.display = 'block';
            }
        }
        
        updateCountdown();
        
        // Show instructions on page load
        window.addEventListener('load', function() {
            const codeInput = document.querySelector('input[name="mfa_code"]');
            codeInput.focus();
            
            // Show tooltip for new users
            if (!localStorage.getItem('mfa_instruction_shown')) {
                setTimeout(() => {
                    alert('📱 Open Google Authenticator app and enter the 6-digit code shown for J3RS-System');
                    localStorage.setItem('mfa_instruction_shown', 'true');
                }, 1000);
            }
        });
    </script>
</body>
</html>