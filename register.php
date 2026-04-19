<?php
session_start();
require 'Captcha/src/Gregwar/Captcha/CaptchaBuilderInterface.php';
require 'Captcha/src/Gregwar/Captcha/PhraseBuilderInterface.php';
require 'Captcha/src/Gregwar/Captcha/ImageFileHandler.php';
require 'Captcha/src/Gregwar/Captcha/PhraseBuilder.php';
require 'Captcha/src/Gregwar/Captcha/CaptchaBuilder.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  require 'admin/db.connect.php';

  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $captcha = trim($_POST['captcha'] ?? '');

  // Validate captcha
  if (!isset($_SESSION['captcha']) || $captcha !== $_SESSION['captcha']) {
    $error = 'Invalid captcha';
  } elseif (empty($username) || empty($email) || empty($password)) {
    $error = 'All fields are required';
  } elseif ($password !== $confirm_password) {
    $error = 'Passwords do not match';
  } elseif (strlen($password) < 12 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
    $error = 'Password does not meet requirements';
  } else {
    // Check if username or email exists
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
      $error = 'Username or email already exists';
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $role_id = 2; // Customer

      // Insert user
      $stmt = $conn->prepare("INSERT INTO user (username, email, password, role_id) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("sssi", $username, $email, $hashed_password, $role_id);
      if ($stmt->execute()) {
        // Generate OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Update user with OTP using MySQL's NOW() to avoid timezone mismatch
        $update_stmt = $conn->prepare("UPDATE user SET otp_code = ?, otp_expiry = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = ?");
        $update_stmt->bind_param("ss", $otp, $email);
        $update_stmt->execute();

        // Send OTP Email
        require 'admin/email.helper.php';
        $subject = "Your Registration OTP";
        $body = "<h1>Welcome to J3RS!</h1><p>Your OTP for registration is: <strong>$otp</strong></p><p>It will expire in 10 minutes.</p>";
        
        if (send_email($email, $subject, $body)) {
          // Clear captcha on success
          unset($_SESSION['captcha']);
          // Redirect to OTP verification page
          header("Location: verify_otp.php?email=" . urlencode($email));
          exit;
        } else {
          $error = 'User registered but failed to send OTP email. Please contact support.';
        }
      } else {
        $error = 'Registration failed, please try again';
      }
    }
    $stmt->close();
  }
  $conn->close();
}

// Generate captcha for the form
$builder = new Gregwar\Captcha\CaptchaBuilder();
$builder->build();
$_SESSION['captcha'] = $builder->getPhrase();
$captchaImage = $builder->inline();
?>
<!DOCTYPE html>
<html lang="en">
<style>
  .circle-logo {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: white;
    /* 👈 THIS is the white inside */
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border: 4px solid white;
  }

  .circle-logo img {
    width: 70%;
    /* 👈 makes logo smaller inside circle */
    height: auto;
    object-fit: contain;
  }
</style>

<head>
  <meta charset="UTF-8">
  <title>Signup</title>

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

  <div class="min-h-[80vh] flex items-center justify-center py-12">
    <div class="w-full max-w-lg">

      <!-- HEADER -->
      <div class="text-center mb-8">
        <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
          <img src="JERS-LOGO.png" alt="Logo" class="w-full h-full object-cover">
        </div>

        <h1 class="text-3xl font-bold text-brand-900 mb-2">
          Create an account
        </h1>

        <p class="text-brand-500">
          Join J3RS to start shopping today.
        </p>

        <?php if (isset($error))
          echo "<p class='text-red-500 text-center mt-4'>$error</p>"; ?>
      </div>

      <!-- CARD -->
      <div class="p-8 bg-white rounded-2xl shadow-xl">

        <form method="POST" class="space-y-5">

          <!-- USERNAME -->
          <div>
            <label class="block mb-1 font-medium">Username</label>
            <input type="text" name="username" class="w-full p-3 border rounded-lg" required>
          </div>

          <!-- EMAIL -->
          <div>
            <label class="block mb-1 font-medium">Email</label>
            <input type="email" name="email" class="w-full p-3 border rounded-lg" required>
          </div>

          <!-- PASSWORD -->
          <div>
            <label class="block mb-1 font-medium">Password</label>
            <input type="password" id="password" name="password" class="w-full p-3 border rounded-lg"
              oninput="checkPassword()" required>

            <!-- RULES -->
            <div class="bg-brand-50 p-4 rounded-xl mt-3 border">
              <p class="text-xs font-semibold text-brand-900 mb-2">
                Password requirements:
              </p>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">

                <div id="rule-length">❌ At least 12 characters</div>
                <div id="rule-upper">❌ Uppercase letter</div>
                <div id="rule-lower">❌ Lowercase letter</div>
                <div id="rule-number">❌ Number</div>
                <div id="rule-special">❌ Special character</div>

              </div>
            </div>
          </div>

          <!-- CONFIRM PASSWORD -->
          <div>
            <label class="block mb-1 font-medium">Confirm Password</label>
            <input type="password" name="confirm_password" class="w-full p-3 border rounded-lg" required>
          </div>

          <!-- CAPTCHA -->
          <div class="bg-brand-50 border rounded-xl p-4 flex justify-between items-center">
            <label>
              <input type="checkbox" id="captchaCheckbox" onchange="toggleCaptcha()"> I am not a robot
            </label>
            <span class="text-xs text-brand-500">CAPTCHA</span>
          </div>

          <!-- CAPTCHA INPUT -->
          <div id="captchaDiv" style="display:none;" class="bg-brand-50 border rounded-xl p-4">
            <label class="block mb-1 font-medium">Enter the Captcha</label>
            <input type="text" name="captcha" class="w-full p-3 border rounded-lg">
            <img src="<?php echo $captchaImage; ?>" alt="Captcha" class="mt-2">
          </div>

          <!-- TERMS -->
          <div class="flex items-start gap-2">
            <input type="checkbox" id="terms" onchange="toggleButton()">
            <span class="text-sm">
              I agree to the
              <button type="button" onclick="openTerms()" class="text-brand-500 font-medium">
                Terms and Agreements
              </button>
            </span>
          </div>

          <!-- BUTTON -->
          <button id="submitBtn" type="submit"
            class="w-full py-3 bg-brand-900 text-white rounded-xl mt-4 opacity-50 cursor-not-allowed" disabled>
            Create Account
          </button>

        </form>
      </div>

      <!-- LOGIN -->
      <p class="text-center mt-8 text-brand-500">
        Already have an account?
        <a href="login.php" class="text-brand-900 font-medium">
          Log in
        </a>
      </p>

    </div>
  </div>

  <!-- TERMS MODAL -->
  <div id="termsModal" class="hidden fixed inset-0 flex items-center justify-center bg-black/40">

    <div class="bg-white p-6 rounded-xl max-w-lg w-full">
      <h2 class="text-xl font-bold text-brand-900 mb-3">Terms and Agreements</h2>

      <p class="text-sm text-brand-500 mb-4">
        Sample terms... You agree to follow the rules.
      </p>

      <div class="flex justify-end gap-3">
        <button onclick="closeTerms()">Decline</button>
        <button onclick="acceptTerms()" class="bg-brand-900 text-white px-4 py-2 rounded">
          Accept
        </button>
      </div>
    </div>

  </div>

  <script>
    function checkPassword() {
      let p = document.getElementById("password").value;

      updateRule("rule-length", p.length >= 12);
      updateRule("rule-upper", /[A-Z]/.test(p));
      updateRule("rule-lower", /[a-z]/.test(p));
      updateRule("rule-number", /[0-9]/.test(p));
      updateRule("rule-special", /[^A-Za-z0-9]/.test(p));
    }

    function updateRule(id, valid) {
      let el = document.getElementById(id);
      el.innerHTML = (valid ? "✅ " : "❌ ") + el.innerText.slice(2);
      el.className = valid ? "text-green-600" : "text-gray-500";
    }

    function toggleButton() {
      let checkbox = document.getElementById("terms");
      let btn = document.getElementById("submitBtn");

      btn.disabled = !checkbox.checked;
      btn.classList.toggle("opacity-50");
      btn.classList.toggle("cursor-not-allowed");
    }

    function toggleCaptcha() {
      let checkbox = document.getElementById('captchaCheckbox');
      let div = document.getElementById('captchaDiv');
      let input = div.querySelector('input');
      div.style.display = checkbox.checked ? 'block' : 'none';
      input.required = checkbox.checked;
    }

    function openTerms() {
      document.getElementById("termsModal").classList.remove("hidden");
    }

    function closeTerms() {
      document.getElementById("termsModal").classList.add("hidden");
    }

    function acceptTerms() {
      document.getElementById("terms").checked = true;
      toggleButton();
      closeTerms();
    }
  </script>

</body>

</html>