<?php
session_start();
require 'admin/db.connect.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Load system settings
$stmt = $conn->prepare("SELECT * FROM system_settings LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $settings = $result->fetch_assoc();
    
    // Ensure all required keys exist with defaults
    $settings = array_merge([
        'max_login_attempts' => 3,
        'password_min_length' => 12,
        'require_uppercase' => 1,
        'require_lowercase' => 1,
        'require_number' => 1,
        'require_special_char' => 1
    ], $settings);
} else {
    $settings = [
        'max_login_attempts' => 3,
        'password_min_length' => 12,
        'require_uppercase' => 1,
        'require_lowercase' => 1,
        'require_number' => 1,
        'require_special_char' => 1
    ];
}

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

  $token = $_POST['csrf_token'] ?? '';
  if (!is_string($token) || !hash_equals($csrfToken, $token)) {
    $error = 'Invalid form submission';
  } else {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $captcha = trim($_POST['captcha'] ?? '');

  // Validate captcha
  if (!isset($_SESSION['captcha_text']) || $captcha !== $_SESSION['captcha_text']) {
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
          unset($_SESSION['captcha_text']);
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
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup | J3RS</title>
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
    /* Terms modal scroll styling */
    .terms-scroll {
      max-height: 55vh;
      overflow-y: auto;
      scrollbar-width: thin;
    }
    .terms-scroll::-webkit-scrollbar {
      width: 5px;
    }
    .terms-scroll::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }
    .terms-scroll::-webkit-scrollbar-thumb {
      background: #a61b4a;
      border-radius: 10px;
    }
    .circle-logo {
      width: 96px;
      height: 96px;
      border-radius: 50%;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      border: 4px solid white;
    }
    .circle-logo img {
      width: 70%;
      height: auto;
      object-fit: contain;
    }
  </style>
</head>
<body class="bg-custombg">

<div class="min-h-[80vh] flex items-center justify-center py-12">
  <div class="w-full max-w-lg">
    <!-- HEADER -->
    <div class="text-center mb-8">
      <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
        <img src="JERS-LOGO.png" alt="J3RS Logo" class="w-full h-full object-cover">
      </div>
      <h1 class="text-3xl font-bold text-brand-900 mb-2">Create an account</h1>
      <p class="text-brand-500">Join J3RS to start shopping today.</p>
      <?php if (isset($error)) echo "<p class='text-red-500 text-center mt-4'>" . htmlspecialchars($error) . "</p>"; ?>
    </div>

    <!-- CARD -->
    <div class="p-8 bg-white rounded-2xl shadow-xl">
      <form method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

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
          <input type="password" id="password" name="password" class="w-full p-3 border rounded-lg" oninput="checkPassword()" required>
          <div class="bg-brand-50 p-4 rounded-xl mt-3 border">
            <p class="text-xs font-semibold text-brand-900 mb-2">Password requirements:</p>
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
          <label><input type="checkbox" id="captchaCheckbox" onchange="toggleCaptcha()"> I am not a robot</label>
          <span class="text-xs text-brand-500">CAPTCHA</span>
        </div>
        <div id="captchaDiv" style="display:none;" class="bg-brand-50 border rounded-xl p-4">
          <label class="block mb-1 font-medium">Enter the Captcha</label>
          <input type="text" name="captcha" class="w-full p-3 border rounded-lg">
          <img src="new_captcha/captcha.php" alt="Captcha" class="mt-2" id="captchaImg" onclick="refreshCaptcha()">
          <button type="button" onclick="refreshCaptcha()" class="text-xs text-brand-900 underline mt-1">Refresh</button>
        </div>

        <!-- TERMS CHECKBOX -->
        <div class="flex items-start gap-2">
          <input type="checkbox" id="terms" onchange="toggleButton()">
          <span class="text-sm">
            I agree to the
            <button type="button" onclick="openTerms()" class="text-brand-500 font-medium">
              Terms and Agreements
            </button>
          </span>
        </div>

        <!-- SUBMIT BUTTON -->
        <button id="submitBtn" type="submit" class="w-full py-3 bg-brand-900 text-white rounded-xl mt-4 opacity-50 cursor-not-allowed" disabled>
          Create Account
        </button>
      </form>
    </div>

    <!-- LOGIN LINK -->
    <p class="text-center mt-8 text-brand-500">
      Already have an account?
      <a href="login.php" class="text-brand-900 font-medium">Log in</a>
    </p>
  </div>
</div>

<!-- TERMS & CONDITIONS MODAL (ENHANCED CONTENT) -->
<div id="termsModal" class="hidden fixed inset-0 flex items-center justify-center bg-black/50 z-50 backdrop-blur-sm transition-all duration-200">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 transform transition-all">
    <div class="border-b border-brand-100 px-6 py-4 flex justify-between items-center">
      <h2 class="text-2xl font-bold text-brand-900 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Terms and Agreements
      </h2>
      <button onclick="closeTerms()" class="text-gray-400 hover:text-brand-900 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>

    <div class="terms-scroll px-6 py-4 text-gray-700 space-y-4 text-sm leading-relaxed">
      <!-- ====== FULL TERMS & CONDITIONS CONTENT ====== -->
      
      <p>Welcome to J3RS Company. These Terms and Conditions govern your use of our website, services. By registering an account, accessing, or using our Platform, you agree to be bound by these Terms. If you do not agree, please do not use our services.</p>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">1. Account Registration & Eligibility</h3>
        <p>To create an account, you must be at least 18 years old or have legal parental/guardian consent. You agree to provide accurate, current, and complete information during registration. You are solely responsible for maintaining the confidentiality of your login credentials and for any activity under your account. Notify us immediately of any unauthorized use. J3RS reserves the right to suspend or terminate accounts that violate these Terms.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">2. Privacy & Data Protection</h3>
        <p>Your privacy is important. Our Privacy Policy explains how we collect, use, and protect your personal data. By using our Platform, you consent to such processing and warrant that all data provided is accurate. We implement security measures including OTP verification, encrypted passwords, and secure sessions, but no system is 100% secure. You agree that J3RS is not liable for unauthorized access beyond our reasonable control.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">3. User Conduct & Prohibited Activities</h3>
        <p>You agree not to: (a) use the Platform for any illegal purpose; (b) attempt to gain unauthorized access to any systems; (c) upload malicious code or interfere with the Platform’s functionality; (d) engage in fraudulent activities, including fake registrations or identity theft; (e) violate any applicable laws or third-party rights. Any violation may lead to immediate account termination and legal action.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">4. Purchases, Orders & Digital Goods</h3>
        <p>All product listings, prices, and promotions are subject to change without notice. We reserve the right to refuse or cancel any order due to pricing errors, availability, or suspected fraud. Payment information must be valid and accurate. Digital goods (if any) are non-returnable unless required by law. Delivery estimates are not guaranteed unless otherwise stated.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">5. Intellectual Property</h3>
        <p>All content, logos, graphics, designs, and trademarks on the Platform are owned by J3RS or its licensors. You may not copy, modify, distribute, or reverse-engineer any portion without explicit written consent. You retain ownership of user-generated content but grant J3RS a worldwide, royalty-free license to display and promote such content in connection with our services.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">6. Limitation of Liability & Disclaimers</h3>
        <p>THE PLATFORM IS PROVIDED “AS IS” AND “AS AVAILABLE”. J3RS DOES NOT WARRANT THAT THE SERVICE WILL BE UNINTERRUPTED, ERROR-FREE, OR SECURE. TO THE MAXIMUM EXTENT PERMITTED BY LAW, J3RS SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, OR CONSEQUENTIAL DAMAGES ARISING FROM YOUR USE OR INABILITY TO USE THE PLATFORM, EVEN IF ADVISED OF POSSIBILITY. OUR TOTAL LIABILITY SHALL NOT EXCEED THE AMOUNT PAID BY YOU (IF ANY) DURING THE PRIOR 6 MONTHS.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">7. Termination & Suspension</h3>
        <p>We may suspend or terminate your account at our sole discretion, without notice, for conduct that violates these Terms or applicable laws, or for any fraudulent or harmful activity. Upon termination, you must cease using the Platform. Sections regarding intellectual property, liability, and dispute resolution survive termination.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">8. Third-Party Links & Integrations</h3>
        <p>Our Platform may contain links to third-party websites. J3RS is not responsible for the content, privacy practices, or terms of those websites. Access them at your own risk.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">9. Modifications to Terms</h3>
        <p>We reserve the right to update or modify these Terms at any time. Changes will be effective upon posting on the Platform. Your continued use after changes constitutes acceptance. Material changes may be notified via email or prominent notice.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">10. Governing Law & Dispute Resolution</h3>
        <p>These Terms shall be governed by the laws of [Your State/Country], without regard to conflict of law principles. Any dispute arising out of these Terms shall be resolved exclusively through binding arbitration or small claims court in the jurisdiction of J3RS’s principal place of business. You waive any right to participate in class actions.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">11. OTP & Security Verification</h3>
        <p>As part of registration, you are required to verify your email via One-Time Password (OTP). This OTP is confidential; sharing it may lead to unauthorized access. J3RS will never ask for your OTP outside of the verification page. You agree to receive transactional emails and notifications for security purposes.</p>
      </div>

      <div>
        <h3 class="font-bold text-brand-800 text-md mt-3 mb-1">12. Contact Information</h3>
        <p>For questions about these Terms, please contact our support team at <span class="text-brand-600">support@j3rs.com</span> or via the contact form on our website.</p>
      </div>
      <p class="text-xs text-gray-400 pt-2 border-t mt-2">By clicking “Accept” below, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions, including any future modifications.</p>
    </div>

    <div class="border-t border-brand-100 px-6 py-4 flex justify-end gap-3 bg-brand-50 rounded-b-2xl">
      <button onclick="closeTerms()" class="px-5 py-2 text-brand-700 border border-brand-300 rounded-lg hover:bg-white transition">Decline</button>
      <button onclick="acceptTerms()" class="px-6 py-2 bg-brand-900 text-white rounded-lg shadow-md hover:bg-brand-800 transition">Accept & Close</button>
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
    if(checkbox.checked) {
      btn.classList.remove("opacity-50", "cursor-not-allowed");
    } else {
      btn.classList.add("opacity-50", "cursor-not-allowed");
    }
  }

  function toggleCaptcha() {
    let checkbox = document.getElementById('captchaCheckbox');
    let div = document.getElementById('captchaDiv');
    let input = div.querySelector('input');
    div.style.display = checkbox.checked ? 'block' : 'none';
    if(input) input.required = checkbox.checked;
  }

  function openTerms() {
    document.getElementById("termsModal").classList.remove("hidden");
    document.body.style.overflow = 'hidden';
  }

  function closeTerms() {
    document.getElementById("termsModal").classList.add("hidden");
    document.body.style.overflow = '';
  }

  function refreshCaptcha() {
    const img = document.getElementById('captchaImg');
    if(img) img.src = 'new_captcha/captcha.php?' + new Date().getTime();
  }

  function acceptTerms() {
    document.getElementById("terms").checked = true;
    toggleButton();
    closeTerms();
  }

  // Ensure captcha required state is toggled if user re-activates
  document.addEventListener("DOMContentLoaded", function() {
    let pwd = document.getElementById("password");
    if(pwd) pwd.addEventListener("input", checkPassword);
    // Initially check password if any prefilled (unlikely)
    checkPassword();

    // extra security: if terms modal opened by mistake, prevent accidental close from outside?
    const modal = document.getElementById("termsModal");
    modal.addEventListener('click', function(e) {
      if(e.target === modal) closeTerms();
    });
  });
</script>
</body>
</html>