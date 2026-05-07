<?php
session_start();
require 'admin/db.connect.php';
require 'admin/email.helper.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$message = '';
$messageType = '';
$showSuccess = false;
$submittedEmail = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($csrfToken, $token)) {
        $message = 'Invalid form submission. Please try again.';
        $messageType = 'error';
    } else {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $message = 'Please enter your email address.';
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            // Check if email exists in database (only activated accounts)
            $stmt = $conn->prepare("SELECT user_id, username, email FROM user WHERE email = ? AND is_activated = 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                // For security, don't reveal that email doesn't exist - show same success message
                $showSuccess = true;
                $submittedEmail = $email;
            } else {
                $user = $result->fetch_assoc();
                $userId = $user['user_id'];
                $username = $user['username'];
                
                // Generate a strong temporary password
                function generateTemporaryPassword($length = 12) {
                    $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
                    $lowercase = 'abcdefghijkmnopqrstuvwxyz';
                    $numbers = '23456789';
                    $special = '!@#$%&*?';
                    
                    $password = '';
                    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
                    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
                    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
                    $password .= $special[random_int(0, strlen($special) - 1)];
                    
                    $all = $uppercase . $lowercase . $numbers . $special;
                    for ($i = strlen($password); $i < $length; $i++) {
                        $password .= $all[random_int(0, strlen($all) - 1)];
                    }
                    
                    return str_shuffle($password);
                }
                
                $tempPassword = generateTemporaryPassword(12);
                $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
                
                // Update user's password in database
                $updateStmt = $conn->prepare("UPDATE user SET password = ?, attempts = 0, is_locked = 0 WHERE user_id = ?");
                $updateStmt->bind_param("si", $hashedPassword, $userId);
                
                if ($updateStmt->execute()) {
                    // Delete any existing password reset tokens for this user (if table exists)
                    $tableCheck = $conn->query("SHOW TABLES LIKE 'password_resets'");
                    if ($tableCheck->num_rows > 0) {
                        $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
                        $deleteStmt->bind_param("i", $userId);
                        $deleteStmt->execute();
                        $deleteStmt->close();
                    }
                    
                    // Prepare email content with temporary password
                    $subject = "Your Temporary Password - J3RS";
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $loginLink = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/login.php";
                    
                    $body = '
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>Temporary Password</title>
                        </head>
                        <body style="font-family: Arial, sans-serif; background-color: #EFECE9; margin: 0; padding: 20px;">
                            <div style="max-width: 500px; margin: 0 auto; background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                <div style="text-align: center; margin-bottom: 25px;">
                                    <img src="' . $protocol . '://' . $_SERVER['HTTP_HOST'] . '/JERS-LOGO.png" alt="J3RS Logo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #610C27;">
                                </div>
                                <h2 style="color: #610C27; margin-bottom: 20px; text-align: center;">Password Reset</h2>
                                <p style="color: #333; line-height: 1.6;">Hello <strong>' . htmlspecialchars($username) . '</strong>,</p>
                                <p style="color: #333; line-height: 1.6;">You recently requested to reset your password. Here is your temporary password:</p>
                                <div style="text-align: center; margin: 25px 0; padding: 15px; background: #fdf2f6; border-radius: 10px; border: 1px solid #f9dbe5;">
                                    <p style="font-size: 24px; font-weight: bold; color: #610C27; letter-spacing: 2px; margin: 0;">' . htmlspecialchars($tempPassword) . '</p>
                                </div>
                                <p style="color: #333; line-height: 1.6;">Please use this temporary password to log in to your account. For security reasons, you will be required to change your password after logging in.</p>
                                <div style="text-align: center; margin: 30px 0;">
                                    <a href="' . $loginLink . '" style="background-color: #610C27; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">Log In Now</a>
                                </div>
                                <div style="background: #fff3cd; padding: 12px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #ffc107;">
                                    <p style="color: #856404; margin: 0; font-size: 13px;"><strong>⚠️ Important Security Tips:</strong></p>
                                    <p style="color: #856404; margin: 5px 0 0 0; font-size: 12px;">• Change this temporary password immediately after login<br>• Do not share this password with anyone<br>• For security, this password is valid until you change it</p>
                                </div>
                                <hr style="margin: 25px 0; border-color: #eee;">
                                <p style="color: #999; font-size: 12px; text-align: center;">If you didn\'t request this password reset, please contact our support team immediately.</p>
                                <p style="color: #999; font-size: 12px; text-align: center;">J3RS - Your trusted shopping partner</p>
                            </div>
                        </body>
                        </html>
                    ';
                    
                    // Send email with temporary password
                    if (send_email($email, $subject, $body)) {
                        $_SESSION['success_message'] = 'A temporary password has been sent to your email. Please check your inbox and change your password after logging in.';
                        header("Location: login.php");
                        exit;
                    } else {
                        $message = 'Failed to send temporary password email. Please try again later or contact support.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'An error occurred. Please try again.';
                    $messageType = 'error';
                }
                $updateStmt->close();
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | J3RS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#fdf2f6',
              100: '#f9dbe5',
              500: '#a61b4a',
              900: '#610C27',
            },
            custombg: '#EFECE9'
          }
        }
      }
    }
  </script>
</head>

<body class="bg-custombg flex items-center justify-center min-h-screen">

  <div class="w-full max-w-md px-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl">

      <!-- ERROR/INFO MESSAGE -->
      <?php if (!empty($message)): ?>
        <div class="mb-6 p-3 rounded-lg text-sm text-center <?php echo $messageType === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
          <i class="fas <?php echo $messageType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> mr-2"></i>
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <!-- FORM STATE (visible when success not shown) -->
      <div id="formState" <?php echo $showSuccess ? 'style="display: none;"' : ''; ?>>
        <div class="text-center mb-8">
          <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
            <img src="JERS-LOGO.png" alt="Logo" class="w-full h-full object-cover">
          </div>
          <h1 class="text-2xl font-bold text-brand-900 mb-2">
            Forgot password?
          </h1>
          <p class="text-brand-500">
            We'll send a temporary password to your email.
          </p>
        </div>

        <form method="POST" action="" class="space-y-6">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
          
          <div>
            <label class="block text-sm font-medium text-brand-900 mb-1">
              Email Address
            </label>
            <div class="relative">
              <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
              <input
                type="email"
                name="email"
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                placeholder="Enter your registered email"
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                required
              />
            </div>
            <p class="text-xs text-gray-500 mt-1">We'll send a temporary password to this email address.</p>
          </div>

          <button
            type="submit"
            name="reset_password"
            class="w-full py-3 bg-brand-900 text-white rounded-lg hover:bg-brand-500 transition transform hover:-translate-y-0.5 shadow-md font-semibold"
          >
            <i class="fas fa-paper-plane mr-2"></i> Send Temporary Password
          </button>
        </form>

        <div class="mt-8 text-center">
          <a href="login.php" class="text-sm font-medium text-brand-900 hover:text-brand-500 transition inline-flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Back to log in
          </a>
        </div>
      </div>

      <!-- SUCCESS STATE (visible after successful email send) -->
      <div id="successState" <?php echo $showSuccess ? '' : 'style="display: none;"'; ?> class="text-center py-6">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <i class="fas fa-check-circle text-green-600 text-3xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-brand-900 mb-3">
          Request Received
        </h2>
        <p class="text-brand-500 mb-4">
          If an account exists with <?php echo htmlspecialchars($submittedEmail); ?>, we'll send a temporary password.
        </p>
        <div class="bg-brand-50 p-4 rounded-lg mb-6 text-sm text-brand-700">
          <i class="fas fa-shield-alt mr-2"></i> For security, the temporary password will be sent to your registered email.
        </div>
        <div class="space-y-3">
          <a href="login.php" class="block w-full py-3 bg-brand-900 text-white rounded-lg hover:bg-brand-500 transition text-center">
            <i class="fas fa-sign-in-alt mr-2"></i> Go to Login
          </a>
          <button onclick="location.reload()" class="w-full py-3 border border-brand-300 text-brand-700 rounded-lg hover:bg-brand-50 transition">
            <i class="fas fa-redo-alt mr-2"></i> Try Again
          </button>
        </div>
      </div>

    </div>
  </div>

  <script>
    // Client-side validation feedback
    const form = document.querySelector('form');
    if (form) {
      form.addEventListener('submit', function(e) {
        const emailInput = form.querySelector('input[name="email"]');
        if (emailInput && !emailInput.value.trim()) {
          e.preventDefault();
          alert('Please enter your email address.');
        } else if (emailInput && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
          e.preventDefault();
          alert('Please enter a valid email address.');
        }
      });
    }
  </script>

</body>
</html>