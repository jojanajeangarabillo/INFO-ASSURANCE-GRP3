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
        
        // Cleanup temp session
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_username']);
        unset($_SESSION['temp_role_id']);

        // Redirect based on role
        $role_id = $_SESSION['role_id'];
        if ($role_id == 1) header("Location: admin_dashboard.php");
        elseif ($role_id == 2) header("Location: customer_home.php");
        elseif ($role_id == 3) header("Location: seller_dashboard.php");
        elseif ($role_id == 4) header("Location: customer_home.php");
        elseif ($role_id == 5) header("Location: logi_settings.php");
        else header("Location: landing.php");
        exit;
    } else {
        $error = "Invalid MFA code. Please try again.";
    }
}
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
                <h1 class="text-2xl font-bold text-brand-900">Two-Factor Authentication</h1>
                <p class="text-gray-500 mt-2 text-sm">Enter the 6-digit code from your authenticator app</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 p-3 bg-red-100 text-red-700 rounded-lg text-sm text-center font-medium">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Authenticator Code</label>
                    <input type="text" name="mfa_code" maxlength="6" autofocus
                        class="w-full p-4 border border-gray-300 rounded-xl text-center text-2xl tracking-[0.5em] font-bold focus:ring-2 focus:ring-brand-500 outline-none transition" 
                        placeholder="000000" required>
                </div>

                <button type="submit" 
                    class="w-full py-4 bg-brand-900 text-white rounded-xl font-bold hover:bg-brand-500 shadow-lg transition-all transform hover:-translate-y-0.5">
                    Verify & Sign In
                </button>
            </form>

            <p class="text-center mt-6">
                <a href="login.php" class="text-sm text-gray-500 hover:text-brand-900 transition underline">Back to login</a>
            </p>
        </div>
    </div>
</body>
</html>