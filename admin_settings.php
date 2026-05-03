<?php
require_once 'auth.php';
require_roles([1]);
require_once 'admin/db.connect.php';
require_once 'config_settings.php';

// Initialize site settings
$siteSettings = SiteSettings::getInstance($conn);

// Fetch session timeout from database
$query = "SELECT session_timeout_minutes FROM system_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$timeout_minutes = $row ? $row['session_timeout_minutes'] : 30; 

// Check session timeout
if (!isset($_SESSION['last_activity'])) {
  $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout_minutes * 60) {
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit;
} else {
  $_SESSION['last_activity'] = time();
}

$timeout_ms = $timeout_minutes * 60 * 1000;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_general_settings'])) {
        // Update general settings in site_settings table
        $generalSettings = [
            'site_name' => $_POST['site_name'] ?? 'J3RS Shop Co.',
            'default_language' => $_POST['default_language'] ?? 'English (US)',
            'default_currency' => $_POST['default_currency'] ?? 'PHP (₱)',
            'timezone' => $_POST['timezone'] ?? 'Asia/Manila'
        ];
        
        foreach ($generalSettings as $key => $value) {
            $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
        
        // Handle logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['site_logo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $newFilename = 'logo_' . time() . '.' . $ext;
                $uploadPath = 'uploads/' . $newFilename;
                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $uploadPath)) {
                    $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'site_logo'");
                    $stmt->bind_param("s", $uploadPath);
                    $stmt->execute();
                }
            }
        }
        
        // Handle hero background upload
        if (isset($_FILES['hero_background']) && $_FILES['hero_background']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['hero_background']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $newFilename = 'hero_' . time() . '.' . $ext;
                $uploadPath = 'uploads/' . $newFilename;
                if (move_uploaded_file($_FILES['hero_background']['tmp_name'], $uploadPath)) {
                    $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'hero_background_image'");
                    $stmt->bind_param("s", $uploadPath);
                    $stmt->execute();
                }
            }
        }
        
        echo "<script>alert('General settings saved successfully!'); window.location.href='admin_settings.php';</script>";
    }
    
    elseif (isset($_POST['save_hero_settings'])) {
        $heroSettings = [
            'hero_title' => $_POST['hero_title'] ?? 'Curated essentials for <br> <span>modern living.</span>',
            'hero_subtitle' => $_POST['hero_subtitle'] ?? '',
            'hero_button_text' => $_POST['hero_button_text'] ?? 'Shop Collection →'
        ];
        
        foreach ($heroSettings as $key => $value) {
            $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
        
        echo "<script>alert('Hero settings saved successfully!'); window.location.href='admin_settings.php';</script>";
    }
    
    elseif (isset($_POST['save_categories_settings'])) {
        $categories = [];
        if (isset($_POST['categories']) && is_array($_POST['categories'])) {
            foreach ($_POST['categories'] as $cat) {
                if (!empty($cat)) {
                    $categories[] = $cat;
                }
            }
        }
        $categoriesJson = json_encode($categories);
        $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'categories'");
        $stmt->bind_param("s", $categoriesJson);
        $stmt->execute();
        
        // Handle new category addition
        if (!empty($_POST['new_category'])) {
            $categories[] = $_POST['new_category'];
            $updatedJson = json_encode($categories);
            $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'categories'");
            $stmt->bind_param("s", $updatedJson);
            $stmt->execute();
        }
        
        echo "<script>alert('Categories saved successfully!'); window.location.href='admin_settings.php';</script>";
    }
    
    elseif (isset($_POST['save_about_settings'])) {
        $aboutSettings = [
            'about_title' => $_POST['about_title'] ?? 'About J3RS Shop Co.',
            'about_content' => $_POST['about_content'] ?? ''
        ];
        
        foreach ($aboutSettings as $key => $value) {
            $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
        
        // Update why choose us (JSON)
        if (isset($_POST['why_choose_us']) && is_array($_POST['why_choose_us'])) {
            $whyChooseUs = [];
            foreach ($_POST['why_choose_us']['icon'] as $index => $icon) {
                $whyChooseUs[] = [
                    'icon' => $icon,
                    'title' => $_POST['why_choose_us']['title'][$index],
                    'description' => $_POST['why_choose_us']['description'][$index]
                ];
            }
            $whyJson = json_encode($whyChooseUs);
            $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'why_choose_us'");
            $stmt->bind_param("s", $whyJson);
            $stmt->execute();
        }
        
        echo "<script>alert('About settings saved successfully!'); window.location.href='admin_settings.php';</script>";
    }
    
    elseif (isset($_POST['save_contact_settings'])) {
        $contactSettings = [
            'contact_email' => $_POST['contact_email'] ?? 'support@j3rsshopco.com',
            'contact_phone' => $_POST['contact_phone'] ?? '+63 912 345 6789',
            'contact_location' => $_POST['contact_location'] ?? 'Philippines, Pasig City',
            'contact_phone_hours' => $_POST['contact_phone_hours'] ?? 'Mon-Sat, 9AM - 6PM',
            'contact_email_response' => $_POST['contact_email_response'] ?? 'We\'ll respond within 24 hours'
        ];
        
        foreach ($contactSettings as $key => $value) {
            $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
        
        // Social media settings
        $socialSettings = [
            'facebook_url' => $_POST['facebook_url'] ?? '#',
            'instagram_url' => $_POST['instagram_url'] ?? '#',
            'twitter_url' => $_POST['twitter_url'] ?? '#',
            'tiktok_url' => $_POST['tiktok_url'] ?? '#'
        ];
        
        foreach ($socialSettings as $key => $value) {
            $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
        
        echo "<script>alert('Contact settings saved successfully!'); window.location.href='admin_settings.php';</script>";
    }
    
    elseif (isset($_POST['save_features_settings'])) {
        $features = [];
        if (isset($_POST['features']) && is_array($_POST['features'])) {
            foreach ($_POST['features']['icon'] as $index => $icon) {
                $features[] = [
                    'icon' => $icon,
                    'title' => $_POST['features']['title'][$index],
                    'description' => $_POST['features']['description'][$index]
                ];
            }
        }
        $featuresJson = json_encode($features);
        $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'features'");
        $stmt->bind_param("s", $featuresJson);
        $stmt->execute();
        
        echo "<script>alert('Features settings saved successfully!'); window.location.href='admin_settings.php';</script>";
    }
    
    elseif (isset($_POST['save_security_settings'])) {
        $max_login_attempts = max(0, (int) ($_POST['max_login_attempts'] ?? 3));
        $password_min_length = (int) ($_POST['password_min_length'] ?? 12);
        $require_uppercase = (int) ($_POST['require_uppercase'] ?? 0);
        $require_lowercase = (int) ($_POST['require_lowercase'] ?? 0);
        $require_number = (int) ($_POST['require_number'] ?? 0);
        $require_special_char = (int) ($_POST['require_special_char'] ?? 0);
        
        $stmt = $conn->prepare("SELECT setting_id FROM system_settings LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->fetch_assoc();
        $stmt->close();
        
        if ($exists) {
            $stmt = $conn->prepare("UPDATE system_settings SET max_login_attempts=?, password_min_length=?, require_uppercase=?, require_lowercase=?, require_number=?, require_special_char=? WHERE setting_id=?");
            $stmt->bind_param("iiiiiii", $max_login_attempts, $password_min_length, $require_uppercase, $require_lowercase, $require_number, $require_special_char, $exists['setting_id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO system_settings (max_login_attempts, password_min_length, require_uppercase, require_lowercase, require_number, require_special_char) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiiii", $max_login_attempts, $password_min_length, $require_uppercase, $require_lowercase, $require_number, $require_special_char);
        }
        
        if ($stmt->execute()) {
            echo "<script>alert('Security settings saved successfully!'); window.location.href='admin_settings.php';</script>";
        } else {
            echo "<script>alert('Error saving security settings: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    }
}

// Load current settings
$settings = [];
$stmt = $conn->prepare("SELECT * FROM system_settings LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $settings = $result->fetch_assoc();
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
$stmt->close();

// Load site settings
$currentCategories = $siteSettings->getCategories();
$currentFeatures = $siteSettings->getFeatures();
$currentWhyChooseUs = $siteSettings->getWhyChooseUs();
$currentHeroTitle = $siteSettings->get('hero_title', 'Curated essentials for <br> <span>modern living.</span>');
$currentHeroSubtitle = $siteSettings->get('hero_subtitle', '');
$currentHeroButtonText = $siteSettings->get('hero_button_text', 'Shop Collection →');
$currentHeroBgImage = $siteSettings->get('hero_background_image', 'labg.jpg');
$currentSiteLogo = $siteSettings->get('site_logo', 'JERS-LOGO.png');
$currentSiteName = $siteSettings->get('site_name', 'J3RS Shop Co.');
$currentDefaultLanguage = $siteSettings->get('default_language', 'English (US)');
$currentDefaultCurrency = $siteSettings->get('default_currency', 'PHP (₱)');
$currentTimezone = $siteSettings->get('timezone', 'Asia/Manila');

$currentAboutTitle = $siteSettings->get('about_title', 'About J3RS Shop Co.');
$currentAboutContent = $siteSettings->get('about_content', '');

$currentContactEmail = $siteSettings->get('contact_email', 'support@j3rsshopco.com');
$currentContactPhone = $siteSettings->get('contact_phone', '+63 912 345 6789');
$currentContactLocation = $siteSettings->get('contact_location', 'Philippines, Pasig City');
$currentContactPhoneHours = $siteSettings->get('contact_phone_hours', 'Mon-Sat, 9AM - 6PM');
$currentContactEmailResponse = $siteSettings->get('contact_email_response', 'We\'ll respond within 24 hours');

$currentFacebookUrl = $siteSettings->get('facebook_url', '#');
$currentInstagramUrl = $siteSettings->get('instagram_url', '#');
$currentTwitterUrl = $siteSettings->get('twitter_url', '#');
$currentTiktokUrl = $siteSettings->get('tiktok_url', '#');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>System Settings</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="sidebar.css">

  <script>
    const timeoutMs = <?php echo $timeout_ms; ?>;
    let logoutTimer;

    function resetTimer() {
      clearTimeout(logoutTimer);
      logoutTimer = setTimeout(function() {
        alert("Session expired due to inactivity. You will be logged out.");
        window.location.href = "logout.php";
      }, timeoutMs);
    }

    document.addEventListener("mousemove", resetTimer);
    document.addEventListener("keypress", resetTimer);
    document.addEventListener("click", resetTimer);
    document.addEventListener("scroll", resetTimer);

    resetTimer();
  </script>

  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #fdf2f6;
    }

    .container {
      margin-left: 240px;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }

    .container.full {
      margin-left: 70px;
    }

    h1 {
      color: #610C27;
      font-size: 32px;
      margin-bottom: 5px;
    }

    .settings-layout {
      display: flex;
      gap: 30px;
      margin-top: 20px;
    }

    .settings-sidebar {
      width: 260px;
      background: white;
      padding: 10px;
      border-radius: 12px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      height: fit-content;
    }

    .tab-btn {
      width: 100%;
      text-align: left;
      padding: 12px 15px;
      border: none;
      background: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 14px;
      color: #333;
      transition: 0.3s;
      margin-bottom: 5px;
    }

    .tab-btn:hover {
      background: #fdf2f6;
      color: #610C27;
    }

    .tab-btn.active {
      background: #f9dbe5;
      color: #610C27;
      font-weight: bold;
    }

    .settings-content {
      flex: 1;
    }

    .card {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
    }

    .card h3 {
      margin-top: 0;
      color: #610C27;
      border-bottom: 1px solid #eee;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 20px;
      max-width: 500px;
    }

    .form-group label {
      display: block;
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 8px;
      color: #333;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
    }

    .form-group textarea {
      min-height: 150px;
      resize: vertical;
    }

    .form-group p {
      font-size: 12px;
      color: #666;
      margin-top: 5px;
    }

    .btn-save {
      background: #610C27;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    .category-item, .feature-item, .why-item-editor {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
      align-items: center;
    }
    
    .category-item input, .feature-item input, .why-item-editor input {
      flex: 1;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 6px;
    }
    
    .remove-btn {
      background: #dc3545;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer;
    }
    
    .add-btn {
      background: #28a745;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 6px;
      cursor: pointer;
      margin-top: 10px;
    }
    
    .image-preview {
      max-width: 100px;
      max-height: 100px;
      margin-top: 10px;
      border-radius: 8px;
    }
    
    .current-image {
      margin-bottom: 10px;
    }
    
    .current-image img {
      max-width: 100px;
      max-height: 100px;
      border-radius: 8px;
    }

    @media (max-width: 768px) {
      .settings-layout {
        flex-direction: column;
      }
      .settings-sidebar {
        width: 100%;
      }
    }
  </style>
</head>

<body>

  <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
      <h2 class="logo-text">Admin</h2>
    </div>
    <a href="admin_dashboard.php" class="<?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
    <a href="admin_analytics.php" class="<?php echo $current_page == 'admin_analytics.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i><span class="text">Analytics</span></a>
    <a href="admin_users.php" class="<?php echo $current_page == 'admin_users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i><span class="text">Users</span></a>
    <a href="admin_auditlogs.php" class="<?php echo $current_page == 'admin_auditlogs.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i><span class="text">Audit Logs</span></a>
    <a href="admin_orders.php" class="<?php echo $current_page == 'admin_orders.php' ? 'active' : ''; ?>"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
    <a href="admin_reports.php" class="<?php echo $current_page == 'admin_reports.php' ? 'active' : ''; ?>"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
    <a href="admin_settings.php" class="<?php echo $current_page == 'admin_settings.php' ? 'active' : ''; ?>"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
    <a href="logout.php" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
  </div>

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById("sidebar");
      const main = document.getElementById("main");
      if (sidebar) sidebar.classList.toggle("collapsed");
      if (main) main.classList.toggle("full");
    }
  </script>

  <div class="container" id="main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <div>
        <h1>System Settings</h1>
        <p>Global configuration affecting the entire website.</p>
      </div>
    </div>

    <div class="settings-layout">
      <div class="settings-sidebar">
        <button type="button" class="tab-btn active" onclick="openTab(event, 'General')">General</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'Hero')">Hero Section</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'Categories')">Categories</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'About')">About Us</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'Contact')">Contact & Social</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'Features')">Features</button>
        <button type="button" class="tab-btn" onclick="openTab(event, 'Security')">Security</button>
      </div>

      <div class="settings-content">
        <!-- General Settings -->
        <div id="General" class="tab-content">
          <form method="POST" action="" enctype="multipart/form-data">
            <div class="card">
              <h3>General Settings</h3>
              <div class="form-group">
                <label>Site Name</label>
                <input type="text" name="site_name" value="<?php echo htmlspecialchars($currentSiteName); ?>">
              </div>
              <div class="form-group">
                <label>Site Logo</label>
                <div class="current-image">
                  <img src="<?php echo htmlspecialchars($currentSiteLogo); ?>" class="image-preview" alt="Current Logo">
                </div>
                <input type="file" name="site_logo" accept="image/*">
                <p>Recommended size: 50x50px. Leave empty to keep current logo.</p>
              </div>
              <div class="form-group">
                <label>Default Language</label>
                <select name="default_language">
                  <option value="English (US)" <?php echo $currentDefaultLanguage == 'English (US)' ? 'selected' : ''; ?>>English (US)</option>
                  <option value="Tagalog" <?php echo $currentDefaultLanguage == 'Tagalog' ? 'selected' : ''; ?>>Tagalog</option>
                </select>
              </div>
              <div class="form-group">
                <label>Default Currency</label>
                <select name="default_currency">
                  <option value="PHP (₱)" <?php echo $currentDefaultCurrency == 'PHP (₱)' ? 'selected' : ''; ?>>PHP (₱)</option>
                  <option value="USD ($)" <?php echo $currentDefaultCurrency == 'USD ($)' ? 'selected' : ''; ?>>USD ($)</option>
                </select>
              </div>
              <div class="form-group">
                <label>Timezone</label>
                <select name="timezone">
                  <option value="Asia/Manila" <?php echo $currentTimezone == 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila (GMT+8)</option>
                </select>
              </div>
              <button type="submit" name="save_general_settings" class="btn-save">Save General Settings</button>
            </div>
          </form>
        </div>

        <!-- Hero Settings -->
        <div id="Hero" class="tab-content" style="display:none;">
          <form method="POST" action="" enctype="multipart/form-data">
            <div class="card">
              <h3>Hero Section Settings</h3>
              <div class="form-group">
                <label>Hero Title (HTML allowed)</label>
                <textarea name="hero_title" rows="3"><?php echo htmlspecialchars($currentHeroTitle); ?></textarea>
                <p>You can use HTML tags like &lt;br&gt; and &lt;span&gt; for styling.</p>
              </div>
              <div class="form-group">
                <label>Hero Subtitle</label>
                <textarea name="hero_subtitle" rows="2"><?php echo htmlspecialchars($currentHeroSubtitle); ?></textarea>
              </div>
              <div class="form-group">
                <label>Hero Button Text</label>
                <input type="text" name="hero_button_text" value="<?php echo htmlspecialchars($currentHeroButtonText); ?>">
              </div>
              <div class="form-group">
                <label>Hero Background Image</label>
                <div class="current-image">
                  <img src="<?php echo htmlspecialchars($currentHeroBgImage); ?>" class="image-preview" alt="Current Hero BG">
                </div>
                <input type="file" name="hero_background" accept="image/*">
                <p>Recommended size: 1920x1080px.</p>
              </div>
              <button type="submit" name="save_hero_settings" class="btn-save">Save Hero Settings</button>
            </div>
          </form>
        </div>

        <!-- Categories Settings -->
        <div id="Categories" class="tab-content" style="display:none;">
          <form method="POST" action="">
            <div class="card">
              <h3>Category Management</h3>
              <div id="categories-list">
                <?php foreach ($currentCategories as $index => $cat): ?>
                  <div class="category-item">
                    <input type="text" name="categories[]" value="<?php echo htmlspecialchars($cat); ?>">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
                  </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="add-btn" onclick="addCategoryField()">+ Add Category</button>
              <div class="form-group" style="margin-top: 20px;">
                <label>Or Add New Category</label>
                <input type="text" name="new_category" placeholder="Enter new category name">
              </div>
              <button type="submit" name="save_categories_settings" class="btn-save" style="margin-top: 20px;">Save Categories</button>
            </div>
          </form>
        </div>

        <!-- About Us Settings -->
        <div id="About" class="tab-content" style="display:none;">
          <form method="POST" action="">
            <div class="card">
              <h3>About Us Content</h3>
              <div class="form-group">
                <label>About Section Title</label>
                <input type="text" name="about_title" value="<?php echo htmlspecialchars($currentAboutTitle); ?>">
              </div>
              <div class="form-group">
                <label>About Content (HTML allowed)</label>
                <textarea name="about_content" rows="10"><?php echo htmlspecialchars($currentAboutContent); ?></textarea>
                <p>You can use HTML tags for formatting.</p>
              </div>
              
              <h4 style="margin-top: 30px;">Why Choose Us Section</h4>
              <div id="why-choose-list">
                <?php foreach ($currentWhyChooseUs as $index => $item): ?>
                  <div class="why-item-editor">
                    <input type="text" name="why_choose_us[icon][]" placeholder="Icon (emoji)" value="<?php echo htmlspecialchars($item['icon']); ?>" style="width: 80px;">
                    <input type="text" name="why_choose_us[title][]" placeholder="Title" value="<?php echo htmlspecialchars($item['title']); ?>">
                    <input type="text" name="why_choose_us[description][]" placeholder="Description" value="<?php echo htmlspecialchars($item['description']); ?>">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
                  </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="add-btn" onclick="addWhyChooseField()">+ Add Reason</button>
              
              <button type="submit" name="save_about_settings" class="btn-save" style="margin-top: 20px;">Save About Settings</button>
            </div>
          </form>
        </div>

        <!-- Contact & Social Settings -->
        <div id="Contact" class="tab-content" style="display:none;">
          <form method="POST" action="">
            <div class="card">
              <h3>Contact Information</h3>
              <div class="form-group">
                <label>Contact Email</label>
                <input type="email" name="contact_email" value="<?php echo htmlspecialchars($currentContactEmail); ?>">
              </div>
              <div class="form-group">
                <label>Contact Phone</label>
                <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($currentContactPhone); ?>">
              </div>
              <div class="form-group">
                <label>Location</label>
                <input type="text" name="contact_location" value="<?php echo htmlspecialchars($currentContactLocation); ?>">
              </div>
              <div class="form-group">
                <label>Phone Hours</label>
                <input type="text" name="contact_phone_hours" value="<?php echo htmlspecialchars($currentContactPhoneHours); ?>">
              </div>
              <div class="form-group">
                <label>Email Response Message</label>
                <input type="text" name="contact_email_response" value="<?php echo htmlspecialchars($currentContactEmailResponse); ?>">
              </div>
              
              <h3 style="margin-top: 30px;">Social Media Links</h3>
              <div class="form-group">
                <label>Facebook URL</label>
                <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($currentFacebookUrl); ?>">
              </div>
              <div class="form-group">
                <label>Instagram URL</label>
                <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($currentInstagramUrl); ?>">
              </div>
              <div class="form-group">
                <label>Twitter URL</label>
                <input type="url" name="twitter_url" value="<?php echo htmlspecialchars($currentTwitterUrl); ?>">
              </div>
              <div class="form-group">
                <label>TikTok URL</label>
                <input type="url" name="tiktok_url" value="<?php echo htmlspecialchars($currentTiktokUrl); ?>">
              </div>
              
              <button type="submit" name="save_contact_settings" class="btn-save">Save Contact Settings</button>
            </div>
          </form>
        </div>

        <!-- Features Settings -->
        <div id="Features" class="tab-content" style="display:none;">
          <form method="POST" action="">
            <div class="card">
              <h3>Features Section (Homepage)</h3>
              <div id="features-list">
                <?php foreach ($currentFeatures as $index => $feature): ?>
                  <div class="feature-item">
                    <input type="text" name="features[icon][]" placeholder="Icon (emoji)" value="<?php echo htmlspecialchars($feature['icon']); ?>" style="width: 80px;">
                    <input type="text" name="features[title][]" placeholder="Title" value="<?php echo htmlspecialchars($feature['title']); ?>">
                    <input type="text" name="features[description][]" placeholder="Description" value="<?php echo htmlspecialchars($feature['description']); ?>">
                    <button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>
                  </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="add-btn" onclick="addFeatureField()">+ Add Feature</button>
              <button type="submit" name="save_features_settings" class="btn-save" style="margin-top: 20px;">Save Features</button>
            </div>
          </form>
        </div>

        <!-- Security Settings -->
        <div id="Security" class="tab-content" style="display:none;">
          <form method="POST" action="">
            <div class="card">
              <h3>Security Settings</h3>
              <div class="form-group">
                <label>Maximum Login Attempts</label>
                <input type="number" name="max_login_attempts" value="<?php echo $settings['max_login_attempts']; ?>" min="0">
                <p>Number of failed login attempts before an account is temporarily locked.</p>
              </div>
              <h4>Password Requirements</h4>
              <div class="form-group">
                <label>Minimum Password Length</label>
                <input type="number" name="password_min_length" value="<?php echo $settings['password_min_length']; ?>" min="8">
              </div>
              <div class="form-group">
                <label>Required Uppercase Letters</label>
                <input type="number" name="require_uppercase" value="<?php echo $settings['require_uppercase']; ?>" min="0">
              </div>
              <div class="form-group">
                <label>Required Lowercase Letters</label>
                <input type="number" name="require_lowercase" value="<?php echo $settings['require_lowercase']; ?>" min="0">
              </div>
              <div class="form-group">
                <label>Required Numbers</label>
                <input type="number" name="require_number" value="<?php echo $settings['require_number']; ?>" min="0">
              </div>
              <div class="form-group">
                <label>Required Special Characters</label>
                <input type="number" name="require_special_char" value="<?php echo $settings['require_special_char']; ?>" min="0">
              </div>
              <button type="submit" name="save_security_settings" class="btn-save">Save Security Settings</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    function openTab(evt, tabName) {
      var i, tabcontent, tablinks;
      tabcontent = document.getElementsByClassName("tab-content");
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }
      tablinks = document.getElementsByClassName("tab-btn");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
      }
      document.getElementById(tabName).style.display = "block";
      evt.currentTarget.className += " active";
    }
    
    function addCategoryField() {
      const container = document.getElementById('categories-list');
      const div = document.createElement('div');
      div.className = 'category-item';
      div.innerHTML = '<input type="text" name="categories[]" placeholder="Category name"><button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>';
      container.appendChild(div);
    }
    
    function addFeatureField() {
      const container = document.getElementById('features-list');
      const div = document.createElement('div');
      div.className = 'feature-item';
      div.innerHTML = '<input type="text" name="features[icon][]" placeholder="Icon (emoji)" style="width: 80px;"><input type="text" name="features[title][]" placeholder="Title"><input type="text" name="features[description][]" placeholder="Description"><button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>';
      container.appendChild(div);
    }
    
    function addWhyChooseField() {
      const container = document.getElementById('why-choose-list');
      const div = document.createElement('div');
      div.className = 'why-item-editor';
      div.innerHTML = '<input type="text" name="why_choose_us[icon][]" placeholder="Icon (emoji)" style="width: 80px;"><input type="text" name="why_choose_us[title][]" placeholder="Title"><input type="text" name="why_choose_us[description][]" placeholder="Description"><button type="button" class="remove-btn" onclick="this.parentElement.remove()">Remove</button>';
      container.appendChild(div);
    }
  </script>

</body>

</html>