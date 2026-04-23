<?php
session_start();
require 'Captcha/src/Gregwar/Captcha/CaptchaBuilderInterface.php';
require 'Captcha/src/Gregwar/Captcha/PhraseBuilderInterface.php';
require 'Captcha/src/Gregwar/Captcha/ImageFileHandler.php';
require 'Captcha/src/Gregwar/Captcha/PhraseBuilder.php';
require 'Captcha/src/Gregwar/Captcha/CaptchaBuilder.php';

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

$password_min_length = $settings['password_min_length'];
$require_uppercase = $settings['require_uppercase'];
$require_lowercase = $settings['require_lowercase'];
$require_number = $settings['require_number'];
$require_special_char = $settings['require_special_char'];



// Build password pattern
$password_pattern = '/^';
if ($require_lowercase > 0)
  $password_pattern .= '(?=(?:.*[a-z]){' . $require_lowercase . ',})';
if ($require_uppercase > 0)
  $password_pattern .= '(?=(?:.*[A-Z]){' . $require_uppercase . ',})';
if ($require_number > 0)
  $password_pattern .= '(?=(?:.*\d){' . $require_number . ',})';
if ($require_special_char > 0)
  $password_pattern .= '(?=(?:.*[^A-Za-z0-9]){' . $require_special_char . ',})';
$password_pattern .= '.{' . $password_min_length . ',}$/';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

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
  } elseif (!preg_match($password_pattern, $password)) {
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

      $conn->begin_transaction();
      try {
        // Insert user
        $stmt = $conn->prepare("INSERT INTO user (username, email, password, role_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $username, $email, $hashed_password, $role_id);
        $stmt->execute();
        $newUserId = (int) $conn->insert_id;

        // Insert initial customer profile row
        $customerStmt = $conn->prepare("
            INSERT INTO customer (user_id)
            VALUES (?)
          ");
        $customerStmt->bind_param("i", $newUserId);
        $customerStmt->execute();

        // Generate OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));

        // Update user with OTP using MySQL's NOW() to avoid timezone mismatch
        $update_stmt = $conn->prepare("UPDATE user SET otp_code = ?, otp_expiry = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = ?");
        $update_stmt->bind_param("ss", $otp, $email);
        $update_stmt->execute();
        $conn->commit();

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
      } catch (Throwable $t) {
        $conn->rollback();
        die("ERROR: " . $t->getMessage());
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

                <div id="rule-length" data-base="At least <?php echo $password_min_length; ?> characters">❌ At least <?php echo $password_min_length; ?> characters</div>
                <div id="rule-upper" data-base="<?php echo $require_uppercase; ?> Uppercase letter<?php echo $require_uppercase > 1 ? 's' : ''; ?>">❌ <?php echo $require_uppercase; ?> Uppercase letter<?php echo $require_uppercase > 1 ? 's' : ''; ?></div>
                <div id="rule-lower" data-base="<?php echo $require_lowercase; ?> Lowercase letter<?php echo $require_lowercase > 1 ? 's' : ''; ?>">❌ <?php echo $require_lowercase; ?> Lowercase letter<?php echo $require_lowercase > 1 ? 's' : ''; ?></div>
                <div id="rule-number" data-base="<?php echo $require_number; ?> Number<?php echo $require_number > 1 ? 's' : ''; ?>">❌ <?php echo $require_number; ?> Number<?php echo $require_number > 1 ? 's' : ''; ?></div>
                <div id="rule-special" data-base="<?php echo $require_special_char; ?> Special character<?php echo $require_special_char > 1 ? 's' : ''; ?>">❌ <?php echo $require_special_char; ?> Special character<?php echo $require_special_char > 1 ? 's' : ''; ?></div>

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
    const passwordRequirements = {
      minLength: <?php echo $password_min_length; ?>,
      upper: <?php echo $require_uppercase; ?>,
      lower: <?php echo $require_lowercase; ?>,
      number: <?php echo $require_number; ?>,
      special: <?php echo $require_special_char; ?>
    };

    function checkPassword() {
      let p = document.getElementById("password").value;

      // Count occurrences
      const upperCount = (p.match(/[A-Z]/g) || []).length;
      const lowerCount = (p.match(/[a-z]/g) || []).length;
      const numberCount = (p.match(/[0-9]/g) || []).length;
      const specialCount = (p.match(/[^A-Za-z0-9]/g) || []).length;

      updateRule("rule-length", p.length >= passwordRequirements.minLength);
      updateRule("rule-upper", upperCount >= passwordRequirements.upper);
      updateRule("rule-lower", lowerCount >= passwordRequirements.lower);
      updateRule("rule-number", numberCount >= passwordRequirements.number);
      updateRule("rule-special", specialCount >= passwordRequirements.special);
    }

    function updateRule(id, valid) {
      let el = document.getElementById(id);
      let baseText = el.getAttribute('data-base');
      el.innerHTML = (valid ? "✅ " : "❌ ") + baseText;
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