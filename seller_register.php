<?php
session_start();
require_once 'admin/db.connect.php';
require_once 'admin/email.helper.php';

$isLoggedIn = isset($_SESSION['user_id']);
$currentUserId = $isLoggedIn ? (int) $_SESSION['user_id'] : 0;
$fromProfile = ($_GET['source'] ?? '') === 'profile';
$message = '';
$messageType = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$form = [
    'full_name' => '',
    'email' => '',
    'contact_number' => '',
    'age' => '',
    'tin_id' => '',
    'business_category' => '',
    'shop_name' => '',
];

if ($isLoggedIn) {
    $userPrefillStmt = $conn->prepare("
        SELECT u.username, u.email, u.role_id, 
               c.full_name as customer_name, c.contact_number as customer_contact
        FROM user u 
        LEFT JOIN customer c ON u.user_id = c.user_id
        WHERE u.user_id = ? LIMIT 1
    ");
    $userPrefillStmt->bind_param("i", $currentUserId);
    $userPrefillStmt->execute();
    $prefill = $userPrefillStmt->get_result()->fetch_assoc();
    
    if ($prefill) {
        $form['full_name'] = (string) ($prefill['customer_name'] ?? $prefill['username'] ?? '');
        $form['email'] = (string) ($prefill['email'] ?? '');
        $form['contact_number'] = (string) ($prefill['customer_contact'] ?? '');
        $currentRoleId = (int) ($prefill['role_id'] ?? 2);
        
        // Check if user already has seller application
        $existingSellerStmt = $conn->prepare("SELECT seller_id, is_approved FROM seller WHERE user_id = ? LIMIT 1");
        $existingSellerStmt->bind_param("i", $currentUserId);
        $existingSellerStmt->execute();
        $existingSeller = $existingSellerStmt->get_result()->fetch_assoc();
        
        if ($existingSeller) {
            if ($existingSeller['is_approved'] == 1) {
                $message = 'You are already an approved seller! You can switch between roles.';
                $messageType = 'warning';
                echo "<script>setTimeout(function(){ window.location.href = 'customer_profile.php'; }, 3000);</script>";
            } elseif ($existingSeller['is_approved'] == 0) {
                $message = 'You already have a pending seller application. Please wait for admin approval.';
                $messageType = 'info';
                echo "<script>setTimeout(function(){ window.location.href = 'customer_profile.php'; }, 3000);</script>";
            }
        }
        
        // If already dual role, redirect
        if ($currentRoleId == 4) {
            header("Location: customer_profile.php?msg=already_dual");
            exit;
        }
    }
}

function save_upload(string $fieldName): array
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return ['ok' => false, 'error' => 'Missing upload: ' . $fieldName];
    }
    $file = $_FILES[$fieldName];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed for ' . $fieldName];
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $original = (string) ($file['name'] ?? '');
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        return ['ok' => false, 'error' => 'Invalid image type for ' . $fieldName . '. Allowed: JPG, PNG, WEBP'];
    }

    if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'File too large for ' . $fieldName . '. Max 5MB'];
    }

    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'seller_docs';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $fileName = $fieldName . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $dir . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
        return ['ok' => false, 'error' => 'Failed to save ' . $fieldName];
    }

    return ['ok' => true, 'path' => 'uploads/seller_docs/' . $fileName];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $message = 'Invalid form submission. Please try again.';
        $messageType = 'error';
    } else {
        $form['full_name'] = trim($_POST['full_name'] ?? '');
        $form['email'] = trim($_POST['email'] ?? '');
        $form['contact_number'] = trim($_POST['contact_number'] ?? '');
        $form['age'] = trim($_POST['age'] ?? '');
        $form['tin_id'] = trim($_POST['tin_id'] ?? '');
        $form['business_category'] = trim($_POST['business_category'] ?? '');
        $form['shop_name'] = trim($_POST['shop_name'] ?? '');

        $required = [
            $form['full_name'], $form['email'], $form['contact_number'], $form['age'],
            $form['tin_id'], $form['business_category'], $form['shop_name']
        ];
        $hasEmpty = false;
        foreach ($required as $value) {
            if ($value === '') {
                $hasEmpty = true;
                break;
            }
        }

        if ($hasEmpty) {
            $message = 'All fields are required.';
            $messageType = 'error';
        } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $message = 'Please provide a valid email address.';
            $messageType = 'error';
        } elseif (!ctype_digit($form['age']) || (int) $form['age'] < 18) {
            $message = 'Seller age must be 18 years or above.';
            $messageType = 'error';
        } elseif (!in_array($form['business_category'], ['Men', 'Women'], true)) {
            $message = 'Invalid business category selected.';
            $messageType = 'error';
        } else {
            $permitUpload = save_upload('business_permit_picture');
            $validIdUpload = save_upload('valid_id_picture');
            $shopUpload = save_upload('shop_image');

            if (!$permitUpload['ok']) {
                $message = $permitUpload['error'];
                $messageType = 'error';
            } elseif (!$validIdUpload['ok']) {
                $message = $validIdUpload['error'];
                $messageType = 'error';
            } elseif (!$shopUpload['ok']) {
                $message = $shopUpload['error'];
                $messageType = 'error';
            } else {
                try {
                    $conn->begin_transaction();
                    $targetUserId = $currentUserId;

                    // Check if user already has seller application
                    $sellerExistsStmt = $conn->prepare("SELECT seller_id, is_approved FROM seller WHERE user_id = ? LIMIT 1");
                    $sellerExistsStmt->bind_param("i", $targetUserId);
                    $sellerExistsStmt->execute();
                    $existingSeller = $sellerExistsStmt->get_result()->fetch_assoc();
                    
                    if ($existingSeller) {
                        throw new RuntimeException('You already have a seller application. Please wait for admin approval.');
                    }

                    // Update or insert customer data
                    $customerExistsStmt = $conn->prepare("SELECT customer_id FROM customer WHERE user_id = ? LIMIT 1");
                    $customerExistsStmt->bind_param("i", $targetUserId);
                    $customerExistsStmt->execute();
                    $customerExists = $customerExistsStmt->get_result()->num_rows > 0;

                    if ($customerExists) {
                        $updateCustomerStmt = $conn->prepare("
                            UPDATE customer
                            SET full_name = ?, contact_number = ?
                            WHERE user_id = ?
                        ");
                        $updateCustomerStmt->bind_param("ssi", $form['full_name'], $form['contact_number'], $targetUserId);
                        $updateCustomerStmt->execute();
                    } else {
                        $insertCustomerStmt = $conn->prepare("
                            INSERT INTO customer (user_id, full_name, contact_number, address_line, city, region, postal_code)
                            VALUES (?, ?, ?, '', '', '', '')
                        ");
                        $insertCustomerStmt->bind_param("iss", $targetUserId, $form['full_name'], $form['contact_number']);
                        $insertCustomerStmt->execute();
                    }

                    // Store seller application data (role remains 2/Customer until approved)
                    $metadata = [
                        'full_name' => $form['full_name'],
                        'email' => $form['email'],
                        'age' => (int) $form['age'],
                        'tin_id' => $form['tin_id'],
                        'business_category' => $form['business_category'],
                        'business_permit_picture' => $permitUpload['path'],
                        'valid_id_picture' => $validIdUpload['path'],
                        'shop_image' => $shopUpload['path'],
                        'registration_date' => date('Y-m-d H:i:s'),
                        'application_type' => 'customer_upgrade'
                    ];
                    $shopDescription = json_encode($metadata);

                    // FIXED: Added full_name to the INSERT statement
                    $insertSellerStmt = $conn->prepare("
                        INSERT INTO seller (user_id, full_name, shop_name, shop_description, contact_number, is_approved)
                        VALUES (?, ?, ?, ?, ?, 0)
                    ");
                    $insertSellerStmt->bind_param("issss", $targetUserId, $form['full_name'], $form['shop_name'], $shopDescription, $form['contact_number']);
                    $insertSellerStmt->execute();

                    // Send notification email to admin
                    $adminEmails = ['admin@j3rs.com'];
                    $subject = "New Seller Application - Customer Upgrade Request";
                    $body = "
                        <html>
                        <head><style>body{font-family:Arial,sans-serif}</style></head>
                        <body>
                            <h2>New Customer to Seller Upgrade Request</h2>
                            <p>A customer has applied to become a seller and is waiting for approval:</p>
                            <p><strong>Full Name:</strong> " . htmlspecialchars($form['full_name']) . "<br>
                            <strong>Shop Name:</strong> " . htmlspecialchars($form['shop_name']) . "<br>
                            <strong>Email:</strong> " . htmlspecialchars($form['email']) . "<br>
                            <strong>Contact:</strong> " . htmlspecialchars($form['contact_number']) . "</p>
                            <p><strong>Application Type:</strong> Customer Upgrading to Dual Role</p>
                            <p><strong>Will Become:</strong> Dual Role (Customer + Seller) (Role ID: 4)</p>
                            <p>Please review the application in the admin panel.</p>
                            <hr>
                            <p><a href='http://localhost/INFO-ASSURANCE-GRP3/admin/admin_approvals.php'>View Pending Approvals</a></p>
                        </body>
                        </html>
                    ";
                    
                    foreach ($adminEmails as $adminEmail) {
                        send_email($adminEmail, $subject, $body);
                    }

                    $conn->commit();
                    $message = 'Seller registration submitted successfully! Please wait for admin approval. You will be notified via email once approved.';
                    $messageType = 'success';
                    
                    if ($messageType === 'success') {
                        $_SESSION['seller_application_pending'] = true;
                        echo "<script>setTimeout(function(){ window.location.href = 'customer_profile.php'; }, 3000);</script>";
                    }
                } catch (Throwable $e) {
                    $conn->rollback();
                    $message = $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Become a Seller - J3RS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { 50: '#fdf2f6', 100: '#f9dbe5', 500: '#a61b4a', 900: '#610C27' },
            custombg: '#EFECE9'
          }
        }
      }
    }
  </script>
</head>
<body class="bg-custombg">
  <div class="min-h-[80vh] flex items-center justify-center py-12">
    <div class="w-full max-w-3xl">
      <div class="text-center mb-8">
        <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
          <img src="JERS-LOGO.png" alt="J3RS Logo" class="w-full h-full object-cover">
        </div>
        <h1 class="text-3xl font-bold text-brand-900 mb-2">Become a Seller</h1>
        <p class="text-brand-500">Upgrade your customer account to a seller account.</p>
        <p class="text-sm text-gray-600 mt-2">Upon approval, your account will be upgraded to <strong>Dual Role</strong> (Customer + Seller).</p>
      </div>

      <?php if ($message !== ''): ?>
        <div class="mb-4 p-4 rounded-lg text-sm font-medium <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700 border-l-4 border-green-500' : 'bg-red-100 text-red-700 border-l-4 border-red-500'; ?>">
          <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-brand-900 text-white px-6 py-3">
          <h2 class="font-semibold"><i class="fas fa-store mr-2"></i> Seller Application Form</h2>
        </div>
        
        <div class="p-8">
          <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-1 font-medium text-gray-700">Full Name *</label>
                <input type="text" name="full_name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" value="<?php echo htmlspecialchars($form['full_name']); ?>" required>
              </div>
              <div>
                <label class="block mb-1 font-medium text-gray-700">Email Address *</label>
                <input type="email" name="email" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" value="<?php echo htmlspecialchars($form['email']); ?>" required>
              </div>
              <div>
                <label class="block mb-1 font-medium text-gray-700">Contact Number *</label>
                <input type="text" name="contact_number" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" value="<?php echo htmlspecialchars($form['contact_number']); ?>" required>
              </div>
              <div>
                <label class="block mb-1 font-medium text-gray-700">Age *</label>
                <input type="number" min="18" name="age" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" value="<?php echo htmlspecialchars($form['age']); ?>" required>
                <p class="text-xs text-gray-500 mt-1">Must be 18 years or older</p>
              </div>
              <div>
                <label class="block mb-1 font-medium text-gray-700">TIN ID *</label>
                <input type="text" name="tin_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" value="<?php echo htmlspecialchars($form['tin_id']); ?>" required>
              </div>
              <div>
                <label class="block mb-1 font-medium text-gray-700">Business Category *</label>
                <select name="business_category" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" required>
                  <option value="">Select category</option>
                  <option value="Men" <?php echo $form['business_category'] === 'Men' ? 'selected' : ''; ?>>Men's Clothing</option>
                  <option value="Women" <?php echo $form['business_category'] === 'Women' ? 'selected' : ''; ?>>Women's Clothing</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block mb-1 font-medium text-gray-700">Shop Name *</label>
              <input type="text" name="shop_name" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent" value="<?php echo htmlspecialchars($form['shop_name']); ?>" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block mb-1 font-medium text-gray-700">Business Permit Picture *</label>
                <input type="file" name="business_permit_picture" class="w-full p-2 border border-gray-300 rounded-lg" accept=".jpg,.jpeg,.png,.webp" required>
                <p class="text-xs text-gray-500 mt-1">JPG, PNG, or WEBP (Max 5MB)</p>
              </div>
              <div>
                <label class="block mb-1 font-medium text-gray-700">Valid ID Picture *</label>
                <input type="file" name="valid_id_picture" class="w-full p-2 border border-gray-300 rounded-lg" accept=".jpg,.jpeg,.png,.webp" required>
                <p class="text-xs text-gray-500 mt-1">JPG, PNG, or WEBP (Max 5MB)</p>
              </div>
              <div>
                <label class="block mb-1 font-medium text-gray-700">Shop Image/Logo *</label>
                <input type="file" name="shop_image" class="w-full p-2 border border-gray-300 rounded-lg" accept=".jpg,.jpeg,.png,.webp" required>
                <p class="text-xs text-gray-500 mt-1">JPG, PNG, or WEBP (Max 5MB)</p>
              </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mt-4">
              <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Note:</strong> Your account will remain as a <strong>Customer</strong> until your seller application is approved. 
                Once approved, your account will be upgraded to <strong>Dual Role</strong>, allowing you to switch between customer and seller modes.
              </p>
            </div>

            <div class="flex gap-3 pt-4">
              <a href="<?php echo $fromProfile || $isLoggedIn ? 'customer_profile.php' : 'landing.php'; ?>" class="px-6 py-3 border-2 border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back
              </a>
              <button type="submit" class="flex-1 px-6 py-3 bg-brand-900 text-white rounded-xl hover:bg-brand-500 transition-colors font-semibold">
                <i class="fas fa-paper-plane mr-2"></i> Submit Application
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>