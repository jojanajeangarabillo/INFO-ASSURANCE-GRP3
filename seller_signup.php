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
    'shop_address' => '',
];

if ($isLoggedIn) {
    $userPrefillStmt = $conn->prepare("SELECT username, email FROM user WHERE user_id = ? LIMIT 1");
    $userPrefillStmt->bind_param("i", $currentUserId);
    $userPrefillStmt->execute();
    $prefill = $userPrefillStmt->get_result()->fetch_assoc();
    if ($prefill) {
        $form['full_name'] = (string) ($prefill['username'] ?? '');
        $form['email'] = (string) ($prefill['email'] ?? '');
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
        return ['ok' => false, 'error' => 'Invalid image type for ' . $fieldName];
    }

    if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'File too large for ' . $fieldName];
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
        $form['shop_address'] = trim($_POST['shop_address'] ?? '');

        $required = [
            $form['full_name'], $form['email'], $form['contact_number'], $form['age'],
            $form['tin_id'], $form['business_category'], $form['shop_name'], $form['shop_address']
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
            $message = 'Please provide a valid email.';
            $messageType = 'error';
        } elseif (!ctype_digit($form['age']) || (int) $form['age'] < 18) {
            $message = 'Seller age must be 18 or above.';
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
                    $targetUserId = 0;

                    if ($isLoggedIn) {
                        $targetUserId = $currentUserId;
                    } else {
                        $existingUserStmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? LIMIT 1");
                        $existingUserStmt->bind_param("s", $form['email']);
                        $existingUserStmt->execute();
                        $existingUser = $existingUserStmt->get_result()->fetch_assoc();
                        if ($existingUser) {
                            throw new RuntimeException('An account with this email already exists. Please login first.');
                        }

                        $baseUsername = preg_replace('/[^a-z0-9]+/i', '', strtolower($form['full_name']));
                        if ($baseUsername === '') {
                            $baseUsername = 'seller';
                        }
                        $username = $baseUsername;
                        $counter = 1;
                        while (true) {
                            $checkUserStmt = $conn->prepare("SELECT user_id FROM user WHERE username = ? LIMIT 1");
                            $checkUserStmt->bind_param("s", $username);
                            $checkUserStmt->execute();
                            if ($checkUserStmt->get_result()->num_rows === 0) {
                                break;
                            }
                            $counter++;
                            $username = $baseUsername . $counter;
                        }

                        $temporaryPassword = bin2hex(random_bytes(8)) . 'A1!';
                        $passwordHash = password_hash($temporaryPassword, PASSWORD_DEFAULT);
                        // Set role_id to 3 (Seller)
                        $roleId = 3;
                        $createUserStmt = $conn->prepare("INSERT INTO user (username, email, password, role_id, is_activated) VALUES (?, ?, ?, ?, 1)");
                        $createUserStmt->bind_param("sssi", $username, $form['email'], $passwordHash, $roleId);
                        $createUserStmt->execute();
                        $targetUserId = (int) $conn->insert_id;
                    }

                    $sellerExistsStmt = $conn->prepare("SELECT seller_id FROM seller WHERE user_id = ? LIMIT 1");
                    $sellerExistsStmt->bind_param("i", $targetUserId);
                    $sellerExistsStmt->execute();
                    if ($sellerExistsStmt->get_result()->num_rows > 0) {
                        throw new RuntimeException('Seller registration already exists for this account.');
                    }

                    // Store full name and other metadata in shop_description JSON field
                    $metadata = [
                        'full_name' => $form['full_name'],
                        'email' => $form['email'],
                        'age' => (int) $form['age'],
                        'tin_id' => $form['tin_id'],
                        'business_category' => $form['business_category'],
                        'business_permit_picture' => $permitUpload['path'],
                        'valid_id_picture' => $validIdUpload['path'],
                        'shop_image' => $shopUpload['path'],
                        'registration_date' => date('Y-m-d H:i:s')
                    ];
                    $shopDescription = json_encode($metadata);

                    // Insert into seller table with full_name column
                    $insertSellerStmt = $conn->prepare("
                        INSERT INTO seller (user_id, full_name, shop_name, shop_description, shop_address, contact_number, is_approved)
                        VALUES (?, ?, ?, ?, ?, ?, 0)
                    ");
                    $insertSellerStmt->bind_param("isssss", $targetUserId, $form['full_name'], $form['shop_name'], $shopDescription, $form['shop_address'], $form['contact_number']);
                    $insertSellerStmt->execute();

                    // Ensure role is set to Seller (3)
                    $updateRoleStmt = $conn->prepare("UPDATE user SET role_id = 3 WHERE user_id = ?");
                    $updateRoleStmt->bind_param("i", $targetUserId);
                    $updateRoleStmt->execute();

                    if ($isLoggedIn) {
                        $_SESSION['role_id'] = 3;
                    }

                    $conn->commit();
                    
                    // Clear form fields after successful registration
                    $form = [
                        'full_name' => '',
                        'email' => '',
                        'contact_number' => '',
                        'age' => '',
                        'tin_id' => '',
                        'business_category' => '',
                        'shop_name' => '',
                        'shop_address' => '',
                    ];
                    
                    $message = 'Seller registration submitted successfully. Please wait for admin approval.';
                    $messageType = 'success';
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
  <title>Seller Registration</title>
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
  <div class="min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-3xl">
      <div class="text-center mb-8">
        <div class="w-24 h-24 rounded-full overflow-hidden mx-auto mb-6 shadow-lg border-4 border-white">
          <img src="JERS-LOGO.png" alt="Logo" class="w-full h-full object-cover">
        </div>
        <h1 class="text-3xl font-bold text-brand-900 mb-2">Seller Registration</h1>
        <p class="text-brand-500">Complete your store details to register as a seller.</p>
      </div>

      <?php if ($message !== ''): ?>
        <div class="mb-4 p-3 rounded-lg text-sm font-medium <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="p-8 bg-white rounded-2xl shadow-xl">
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block mb-1 font-medium">Full Name</label>
              <input type="text" name="full_name" class="w-full p-3 border rounded-lg" value="<?php echo htmlspecialchars($form['full_name']); ?>" required>
            </div>
            <div>
              <label class="block mb-1 font-medium">Email</label>
              <input type="email" name="email" class="w-full p-3 border rounded-lg" value="<?php echo htmlspecialchars($form['email']); ?>" required>
            </div>
            <div>
              <label class="block mb-1 font-medium">Contact Number</label>
              <input type="text" name="contact_number" class="w-full p-3 border rounded-lg" value="<?php echo htmlspecialchars($form['contact_number']); ?>" required>
            </div>
            <div>
              <label class="block mb-1 font-medium">Age</label>
              <input type="number" min="18" name="age" class="w-full p-3 border rounded-lg" value="<?php echo htmlspecialchars($form['age']); ?>" required>
            </div>
            <div>
              <label class="block mb-1 font-medium">TIN ID</label>
              <input type="text" name="tin_id" class="w-full p-3 border rounded-lg" value="<?php echo htmlspecialchars($form['tin_id']); ?>" required>
            </div>
            <div>
              <label class="block mb-1 font-medium">Business Category</label>
              <select name="business_category" class="w-full p-3 border rounded-lg" required>
                <option value="">Select category</option>
                <option value="Men" <?php echo $form['business_category'] === 'Men' ? 'selected' : ''; ?>>Men Clothes</option>
                <option value="Women" <?php echo $form['business_category'] === 'Women' ? 'selected' : ''; ?>>Women Clothes</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block mb-1 font-medium">Shop Name</label>
            <input type="text" name="shop_name" class="w-full p-3 border rounded-lg" value="<?php echo htmlspecialchars($form['shop_name']); ?>" required>
          </div>

          <div>
            <label class="block mb-1 font-medium">Shop Address</label>
            <textarea name="shop_address" class="w-full p-3 border rounded-lg" rows="3" required><?php echo htmlspecialchars($form['shop_address']); ?></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block mb-1 font-medium">Business Permit Picture</label>
              <input type="file" name="business_permit_picture" class="w-full p-3 border rounded-lg" accept=".jpg,.jpeg,.png,.webp" required>
            </div>
            <div>
              <label class="block mb-1 font-medium">Valid ID Picture</label>
              <input type="file" name="valid_id_picture" class="w-full p-3 border rounded-lg" accept=".jpg,.jpeg,.png,.webp" required>
            </div>
            <div>
              <label class="block mb-1 font-medium">Shop Image</label>
              <input type="file" name="shop_image" class="w-full p-3 border rounded-lg" accept=".jpg,.jpeg,.png,.webp" required>
            </div>
          </div>

          <div class="flex gap-3 pt-2">
            <?php if ($fromProfile || $isLoggedIn): ?>
              <a href="profile.php" class="px-4 py-3 border rounded-xl text-brand-900">Back to Profile</a>
            <?php else: ?>
              <a href="landing.php" class="px-4 py-3 border rounded-xl text-brand-900">Back to Landing</a>
            <?php endif; ?>
            <button type="submit" class="px-6 py-3 bg-brand-900 text-white rounded-xl hover:bg-brand-700 transition">Submit Registration</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>