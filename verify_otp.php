<?php
session_start();
require 'admin/db.connect.php';
require 'admin/email.helper.php';

$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? AND otp_code = ? AND otp_expiry > NOW()");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // OTP is valid, generate verification token
        $token = bin2hex(random_bytes(32));

        // Update user with token using MySQL's NOW() for expiry
        $update_stmt = $conn->prepare("UPDATE user SET verification_token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL 24 HOUR), otp_code = NULL, otp_expiry = NULL WHERE email = ?");
        $update_stmt->bind_param("ss", $token, $email);
        $update_stmt->execute();

        // Send Verification Link Email
$current_dir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
$verify_link = "http://" . $_SERVER['HTTP_HOST'] . rtrim($current_dir, '/') . "/verify_account.php?token=$token&email=" . urlencode($email);
$subject = "Verify Your J3RS Account";
$body = "<h1>Almost there!</h1><p>Please click the link below to verify your account:</p><p><a href='$verify_link'>$verify_link</a></p><p>This link will expire in 24 hours.</p>";
        if (send_email($email, $subject, $body)) {
            $success = "OTP verified! A verification link has been sent to your email. Please click it to activate your account.";
        } else {
            $error = "OTP verified but failed to send verification link. Please contact support.";
        }
    } else {
        $error = "Invalid or expired OTP.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
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
    <div class="min-h-screen flex items-center justify-center py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
                    <img src="JERS-LOGO.png" alt="Logo" class="w-full h-full object-cover">
                </div>
                <h1 class="text-3xl font-bold text-brand-900 mb-2">Verify OTP</h1>
                <p class="text-brand-500">Enter the 6-digit code sent to <?php echo htmlspecialchars($email); ?></p>
                
                <?php if (isset($error)): ?>
                    <p class="text-red-500 mt-4"><?php echo $error; ?></p>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="mt-4 p-4 bg-green-100 text-green-700 rounded-lg">
                        <?php echo $success; ?>
                        <p class="mt-2"><a href="login.php" class="font-bold underline">Go to Login</a></p>
                    </div>
                <?php else: ?>
                    <div class="p-8 bg-white rounded-2xl shadow-xl mt-8">
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <div>
                                <label class="block mb-2 font-medium">OTP Code</label>
                                <input type="text" name="otp" maxlength="6" class="w-full p-4 border rounded-xl text-center text-2xl tracking-widest focus:ring-2 focus:ring-brand-500 outline-none" placeholder="000000" required>
                            </div>
                            <button type="submit" class="w-full py-3 bg-brand-900 text-white rounded-xl hover:bg-brand-500 transition font-bold">
                                Verify OTP
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>