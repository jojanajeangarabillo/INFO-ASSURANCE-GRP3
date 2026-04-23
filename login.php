<?php
session_start();
require 'admin/db.connect.php';

// Load system settings
$stmt = $conn->prepare("SELECT * FROM system_settings LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->num_rows > 0 ? $result->fetch_assoc() : [
  'max_login_attempts' => 3,
  'password_min_length' => 12,
  'require_uppercase' => 1,
  'require_lowercase' => 1,
  'require_number' => 1,
  'require_special_char' => 1
];

if (isset($_POST['login'])) {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if (empty($username) || empty($password)) {
    $_SESSION['error_message'] = "Username and password are required.";
    header("Location: login.php");
    exit;
  }

  // Get user details including role and activation status
  $stmt = $conn->prepare("SELECT user_id, username, email, password, role_id, is_activated, is_locked, attempts FROM user WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Check if account is locked
    if ($row['is_locked'] == 1) {
      $_SESSION['error_message'] = "Your account is locked due to multiple failed attempts. Please contact support.";
      header("Location: login.php");
      exit;
    }

    // Check if account is activated
    if ($row['is_activated'] == 0) {
      $_SESSION['error_message'] = "Please verify your account before logging in. Check your email for the verification link.";
      header("Location: login.php");
      exit;
    }

    if (password_verify($password, $row['password'])) {
      // Success: Reset failed attempts
      $reset_stmt = $conn->prepare("UPDATE user SET attempts = 0 WHERE user_id = ?");
      $reset_stmt->bind_param("i", $row['user_id']);
      $reset_stmt->execute();

      // Store temporary session data before MFA
      $_SESSION['temp_user_id'] = $row['user_id'];
      $_SESSION['temp_username'] = $row['username'];
      $_SESSION['temp_role_id'] = $row['role_id'];

      // Check if MFA is set up
      $mfa_stmt = $conn->prepare("SELECT mfa_secret FROM user WHERE user_id = ?");
      $mfa_stmt->bind_param("i", $row['user_id']);
      $mfa_stmt->execute();
      $mfa_result = $mfa_stmt->get_result();
      $mfa_row = $mfa_result->fetch_assoc();

      if (empty($mfa_row['mfa_secret'])) {
        // First time login: Setup MFA
        header("Location: mfa_setup.php");
        exit;
      } else {
        // Subsequent login: Verify MFA
        header("Location: mfa_verify.php");
        exit;
      }
    } else {
      // Failure: Increment attempts
      $new_attempts = $row['attempts'] + 1;
      if ($new_attempts >= $settings['max_login_attempts']) {
        $lock_stmt = $conn->prepare("UPDATE user SET attempts = ?, is_locked = 1 WHERE user_id = ?");
        $lock_stmt->bind_param("ii", $new_attempts, $row['user_id']);
        $lock_stmt->execute();
        $_SESSION['error_message'] = "Too many failed attempts. Your account has been locked.";
      } else {
        $update_stmt = $conn->prepare("UPDATE user SET attempts = ? WHERE user_id = ?");
        $update_stmt->bind_param("ii", $new_attempts, $row['user_id']);
        $update_stmt->execute();
        $_SESSION['error_message'] = "Invalid username or password. Attempts remaining: " . ($settings['max_login_attempts'] - $new_attempts);
      }
      header("Location: login.php");
      exit;
    }
  } else {
    $_SESSION['error_message'] = "Invalid username or password.";
    header("Location: login.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Login</title>

  <script src="https://cdn.tailwindcss.com"></script>

  <!-- CUSTOM COLORS -->
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
            custombg: '#EFECE9' // YOUR BACKGROUND
          }
        }
      }
    }
  </script>

</head>

<body class="bg-custombg">

  <div class="min-h-[80vh] flex items-center justify-center py-12">
    <div class="w-full max-w-md">

      <!-- Header -->
      <div class="text-center mb-8">
        <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
          <img src="JERS-LOGO.png" alt="Logo" class="w-full h-full object-cover">
        </div>

        <h1 class="text-3xl font-bold text-brand-900 mb-2">
          Welcome back
        </h1>

        <p class="text-brand-500">
          Please enter your details to sign in.
        </p>

        <?php
        if (isset($_SESSION['success_message'])) {
          echo "<div class='mt-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm text-center font-medium'>" . $_SESSION['success_message'] . "</div>";
          unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
          echo "<div class='mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm text-center font-medium'>" . $_SESSION['error_message'] . "</div>";
          unset($_SESSION['error_message']);
        }
        ?>
      </div>

      <!-- Card -->
      <div class="p-8 shadow-xl rounded-2xl bg-white border border-gray-100">

        <form action="login.php" method="POST" class="space-y-5">

          <div>
            <label class="block text-sm font-semibold mb-1 text-gray-700">Username</label>
            <input type="text" name="username"
              class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
              placeholder="Enter your username" required>
          </div>

          <div>
            <label class="block text-sm font-semibold mb-1 text-gray-700">Password</label>
            <input type="password" name="password"
              class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
              placeholder="••••••••" required>

            <div class="flex justify-between items-center pt-3 text-sm">
              <label class="flex items-center text-gray-600">
                <input type="checkbox" class="mr-2 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                Remember me
              </label>

              <a href="forgotpass.php" class="font-semibold text-brand-900 hover:text-brand-500 transition">
                Forgot password?
              </a>
            </div>
          </div>

          <button type="submit" name="login"
            class="w-full py-3 mt-4 text-base font-bold bg-brand-900 text-white rounded-xl hover:bg-brand-500 shadow-lg transition-all transform hover:-translate-y-0.5">
            Sign in
          </button>

        </form>
      </div>

      <p class="text-center mt-8 text-brand-500">
        Don't have an account?
        <a href="register.php" class="font-bold text-brand-900 hover:text-brand-500 transition">
          Sign up
        </a>
      </p>

    </div>
  </div>

</body>

</html>