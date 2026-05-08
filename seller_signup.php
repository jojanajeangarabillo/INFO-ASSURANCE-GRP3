<?php
session_start();
require_once 'admin/db.connect.php';

$isLoggedIn = isset($_SESSION['user_id']);
$currentUserId = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;

$fromProfile = isset($_GET['source']) && $_GET['source'] === 'profile';

$message = '';
$messageType = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

/* ---------------- FORM STATE ---------------- */
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

/* ---------------- UPLOAD ---------------- */
function save_upload(string $fieldName): array
{
    if (!isset($_FILES[$fieldName])) {
        return ['ok' => false, 'error' => 'Missing upload'];
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed'];
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        return ['ok' => false, 'error' => 'Invalid file type'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'File too large'];
    }

    $dir = __DIR__ . '/uploads/seller_docs/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $fileName = $fieldName . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $fullPath = $dir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
        return ['ok' => false, 'error' => 'Upload move failed'];
    }

    return ['ok' => true, 'path' => 'uploads/seller_docs/' . $fileName];
}

/* ---------------- POST ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $message = 'Invalid CSRF token';
        $messageType = 'error';
    } else {

        foreach ($form as $key => $_) {
            $form[$key] = trim($_POST[$key] ?? '');
        }

        if (in_array('', $form, true)) {
            $message = 'All fields are required';
            $messageType = 'error';

        } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email';
            $messageType = 'error';

        } elseif (!ctype_digit($form['age']) || (int)$form['age'] < 18) {
            $message = 'Age must be 18+';
            $messageType = 'error';

        } else {

            $permit = save_upload('business_permit_picture');
            $validId = save_upload('valid_id_picture');
            $shopImg = save_upload('shop_image');

            if (!$permit['ok']) {
                $message = $permit['error'];
                $messageType = 'error';

            } elseif (!$validId['ok']) {
                $message = $validId['error'];
                $messageType = 'error';

            } elseif (!$shopImg['ok']) {
                $message = $shopImg['error'];
                $messageType = 'error';

            } else {

                try {
                    $conn->begin_transaction();

                    /* CREATE USER IF NOT LOGGED IN */
                    if (!$isLoggedIn) {

                        $username = strtolower(preg_replace('/[^a-z0-9]/i', '', $form['full_name']));
                        if ($username === '') $username = 'seller';

                        $passwordHash = password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT);

                        $stmt = $conn->prepare("
                            INSERT INTO user (username, email, password, role_id, is_activated)
                            VALUES (?, ?, ?, 3, 1)
                        ");

                        $stmt->bind_param("sss", $username, $form['email'], $passwordHash);
                        $stmt->execute();

                        $currentUserId = $conn->insert_id;
                    }

                    /* CHECK DUPLICATE SELLER */
                    $check = $conn->prepare("SELECT seller_id FROM seller WHERE user_id=?");
                    $check->bind_param("i", $currentUserId);
                    $check->execute();

                    if ($check->get_result()->num_rows > 0) {
                        throw new Exception("Seller already exists");
                    }

                    /* SIMPLE INTRO ONLY */
                    $shopDescription = "Welcome to " . $form['shop_name'] . "!";

                    $encryptedContact = encrypt_data($form['contact_number']);
                    $encryptedTinId = encrypt_data($form['tin_id']);

                    /* INSERT SELLER */
                    $stmt = $conn->prepare("
                        INSERT INTO seller (
                            user_id,
                            full_name,
                            shop_name,
                            shop_description,
                            business_category,
                            tin_id,
                            age,
                            shop_address,
                            contact_number,
                            business_permit,
                            valid_id,
                            shop_image,
                            is_approved
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
                    ");

                    $stmt->bind_param(
                        "isssssisssss",
                        $currentUserId,
                        $form['full_name'],
                        $form['shop_name'],
                        $shopDescription,
                        $form['business_category'],
                        $encryptedTinId,
                        $form['age'],
                        $form['shop_address'],
                        $encryptedContact,
                        $permit['path'],
                        $validId['path'],
                        $shopImg['path']
                    );

                    $stmt->execute();

                    $conn->commit();

                    /* CLEAR FORM AFTER SUCCESS */
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

                    $message = "Registration successful. Wait for approval.";
                    $messageType = "success";

                } catch (Throwable $e) {

                    // SAFE rollback (prevents crash)
                    if ($conn && $conn->ping()) {
                        $conn->rollback();
                    }

                    $message = $e->getMessage();
                    $messageType = "error";
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