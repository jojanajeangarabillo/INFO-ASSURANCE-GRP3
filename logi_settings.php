<?php
require_once 'auth.php';
require_roles([5]); // Only logistics role can access

require_once __DIR__ . '/admin/db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;
$message = '';
$error = '';

// Fetch session timeout from database
$query = "SELECT session_timeout_minutes FROM system_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$timeout_minutes = $row ? $row['session_timeout_minutes'] : 30;

if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout_minutes * 60) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$_SESSION['last_activity'] = time();

$current_page = basename($_SERVER['PHP_SELF']);

// Check if logistics profile exists, if not create one
$check_logistics = $conn->prepare("SELECT * FROM logistics WHERE user_id = ?");
$check_logistics->bind_param("i", $user_id);
$check_logistics->execute();
$logistics = $check_logistics->get_result()->fetch_assoc();
$check_logistics->close();

if (!$logistics) {
    // Get user info
    $user_stmt = $conn->prepare("SELECT first_name, last_name, username, email FROM user WHERE user_id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_info = $user_stmt->get_result()->fetch_assoc();
    $user_stmt->close();
    
    // Create default logistics profile
    $insert_logistics = $conn->prepare("
        INSERT INTO logistics (user_id, company_name, contact_email, status) 
        VALUES (?, ?, ?, 'active')
    ");
    $company_name = $user_info['first_name'] . ' ' . $user_info['last_name'] . ' Logistics';
    $insert_logistics->bind_param("iss", $user_id, $company_name, $user_info['email']);
    $insert_logistics->execute();
    $insert_logistics->close();
    
    // Create default notification settings
    $insert_notif = $conn->prepare("
        INSERT INTO notification_settings (logistics_id, customer_sms, customer_email, seller_alerts, driver_notifications) 
        VALUES ((SELECT logistics_id FROM logistics WHERE user_id = ?), 1, 1, 1, 1)
    ");
    $insert_notif->bind_param("i", $user_id);
    $insert_notif->execute();
    $insert_notif->close();
    
    // Refetch logistics profile
    $check_logistics = $conn->prepare("SELECT * FROM logistics WHERE user_id = ?");
    $check_logistics->bind_param("i", $user_id);
    $check_logistics->execute();
    $logistics = $check_logistics->get_result()->fetch_assoc();
    $check_logistics->close();
}

$logistics_id = $logistics['logistics_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $company_name = trim($_POST['company_name']);
        $business_address = trim($_POST['business_address']);
        $contact_number = trim($_POST['contact_number']);
        $contact_email = trim($_POST['contact_email']);
        $license_number = trim($_POST['license_number']);
        $authorized_person = trim($_POST['authorized_person']);
        $website = trim($_POST['website']);
        $operating_hours = trim($_POST['operating_hours']);
        $service_type = $_POST['service_type'];
        $coverage_areas = trim($_POST['coverage_areas']);
        
        $update_stmt = $conn->prepare("
            UPDATE logistics 
            SET company_name = ?, business_address = ?, contact_number = ?, 
                contact_email = ?, license_number = ?, authorized_person = ?, 
                website = ?, operating_hours = ?, service_type = ?, coverage_areas = ?
            WHERE logistics_id = ?
        ");
        $update_stmt->bind_param("ssssssssssi", 
            $company_name, $business_address, $contact_number, 
            $contact_email, $license_number, $authorized_person, 
            $website, $operating_hours, $service_type, $coverage_areas, $logistics_id
        );
        
        if ($update_stmt->execute()) {
            $message = "Profile updated successfully!";
            // Refresh logistics data
            $refresh = $conn->prepare("SELECT * FROM logistics WHERE logistics_id = ?");
            $refresh->bind_param("i", $logistics_id);
            $refresh->execute();
            $logistics = $refresh->get_result()->fetch_assoc();
            $refresh->close();
        } else {
            $error = "Failed to update profile.";
        }
        $update_stmt->close();
    }
    
    // Add shipping zone
    if (isset($_POST['add_zone'])) {
        $zone_name = trim($_POST['zone_name']);
        $areas_covered = trim($_POST['areas_covered']);
        $base_rate = floatval($_POST['base_rate']);
        $additional_per_kg = floatval($_POST['additional_per_kg']);
        $estimated_days = trim($_POST['estimated_days']);
        
        $insert_stmt = $conn->prepare("
            INSERT INTO shipping_zones (logistics_id, zone_name, areas_covered, base_rate, additional_per_kg, estimated_days) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert_stmt->bind_param("issdds", $logistics_id, $zone_name, $areas_covered, $base_rate, $additional_per_kg, $estimated_days);
        
        if ($insert_stmt->execute()) {
            $message = "Shipping zone added successfully!";
        } else {
            $error = "Failed to add shipping zone.";
        }
        $insert_stmt->close();
    }
    
    // Add courier
    if (isset($_POST['add_courier'])) {
        $name = trim($_POST['courier_name']);
        $description = trim($_POST['courier_description']);
        $delivery_fee = floatval($_POST['delivery_fee']);
        
        $insert_stmt = $conn->prepare("
            INSERT INTO couriers (logistics_id, name, description, delivery_fee, is_enabled) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $insert_stmt->bind_param("issd", $logistics_id, $name, $description, $delivery_fee);
        
        if ($insert_stmt->execute()) {
            $message = "Courier added successfully!";
        } else {
            $error = "Failed to add courier.";
        }
        $insert_stmt->close();
    }
    
    // Update notification settings
    if (isset($_POST['update_notifications'])) {
        $customer_sms = isset($_POST['customer_sms']) ? 1 : 0;
        $customer_email = isset($_POST['customer_email']) ? 1 : 0;
        $seller_alerts = isset($_POST['seller_alerts']) ? 1 : 0;
        $driver_notifications = isset($_POST['driver_notifications']) ? 1 : 0;
        
        $update_stmt = $conn->prepare("
            UPDATE notification_settings 
            SET customer_sms = ?, customer_email = ?, seller_alerts = ?, driver_notifications = ?, updated_at = NOW() 
            WHERE logistics_id = ?
        ");
        $update_stmt->bind_param("iiiii", $customer_sms, $customer_email, $seller_alerts, $driver_notifications, $logistics_id);
        
        if ($update_stmt->execute()) {
            $message = "Notification settings updated successfully!";
        } else {
            $error = "Failed to update notification settings.";
        }
        $update_stmt->close();
    }
    
    // Update courier status via AJAX
    if (isset($_POST['toggle_courier'])) {
        $courier_id = intval($_POST['courier_id']);
        $is_enabled = intval($_POST['is_enabled']);
        
        $toggle_stmt = $conn->prepare("UPDATE couriers SET is_enabled = ? WHERE courier_id = ? AND logistics_id = ?");
        $toggle_stmt->bind_param("iii", $is_enabled, $courier_id, $logistics_id);
        $toggle_stmt->execute();
        $toggle_stmt->close();
        exit;
    }
}

// Fetch shipping zones
$zones_stmt = $conn->prepare("SELECT * FROM shipping_zones WHERE logistics_id = ? ORDER BY base_rate ASC");
$zones_stmt->bind_param("i", $logistics_id);
$zones_stmt->execute();
$shipping_zones = $zones_stmt->get_result();
$zones_stmt->close();

// Fetch couriers
$couriers_stmt = $conn->prepare("SELECT * FROM couriers WHERE logistics_id = ? ORDER BY name ASC");
$couriers_stmt->bind_param("i", $logistics_id);
$couriers_stmt->execute();
$couriers = $couriers_stmt->get_result();
$couriers_stmt->close();

// Fetch notification settings
$notif_stmt = $conn->prepare("SELECT * FROM notification_settings WHERE logistics_id = ?");
$notif_stmt->bind_param("i", $logistics_id);
$notif_stmt->execute();
$notif_settings = $notif_stmt->get_result()->fetch_assoc();
$notif_stmt->close();

// Get user info for display
$user_stmt = $conn->prepare("SELECT first_name, last_name, username, email FROM user WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_info = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logistics Settings</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="sidebar.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: #fdf2f6;
}

.main-content {
  margin-left: 240px;
  padding: 40px 60px;
  transition: margin-left 0.3s ease;
}

.main-content.full {
  margin-left: 70px;
}

h1 {
  color: #610C27;
  font-size: 32px;
  margin-bottom: 5px;
}

.grid {
  display: grid;
  gap: 20px;
}

.grid-2-1 {
  grid-template-columns: 2fr 1fr;
}

@media (max-width: 1024px) {
  .grid-2-1 {
    grid-template-columns: 1fr;
  }
}

.card {
  background: white;
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  margin-bottom: 20px;
}

.section-title {
  font-size: 18px;
  font-weight: bold;
  color: #610C27;
  margin-bottom: 15px;
  border-left: 3px solid #610C27;
  padding-left: 12px;
}

.form-label {
  font-weight: 600;
  font-size: 13px;
  color: #555;
  margin-bottom: 5px;
}

.form-control, .form-select {
  border-radius: 10px;
  border: 1px solid #ddd;
  padding: 10px 15px;
}

.form-control:focus, .form-select:focus {
  border-color: #610C27;
  box-shadow: 0 0 0 0.2rem rgba(97, 12, 39, 0.25);
}

.btn-primary {
  background: #610C27;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: #8a1423;
}

.btn-outline {
  padding: 8px 16px;
  border: 1.5px solid #610C27;
  border-radius: 8px;
  background: white;
  color: #610C27;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: #610C27;
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
}

.courier-item, .zone-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #eee;
  border-radius: 10px;
  margin-bottom: 10px;
  background: #fcfcfc;
  transition: all 0.2s;
}

.courier-item:hover, .zone-item:hover {
  border-color: #610C27;
  box-shadow: 0 2px 5px rgba(97, 12, 39, 0.1);
}

.courier-info h4, .zone-info h4 {
  margin: 0;
  color: #333;
}

.courier-info p, .zone-info p {
  margin: 5px 0 0 0;
  font-size: 12px;
  color: #666;
}

.checkbox-custom {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #610C27;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  font-size: 12px;
  text-transform: uppercase;
  color: #666;
  background: #f9dbe5;
}

.notification-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-info h4 {
  margin: 0 0 5px 0;
  color: #610C27;
}

.notification-info p {
  margin: 0;
  font-size: 12px;
  color: #666;
}

.alert-custom {
  border-radius: 12px;
  margin-bottom: 20px;
  padding: 12px 20px;
}

.modal-content {
  border-radius: 16px;
}

.modal-header {
  background: #610C27;
  color: white;
  border-radius: 16px 16px 0 0;
}

.modal-header .btn-close {
  filter: invert(1);
}

.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
}

.status-active {
  background: #d1fae5;
  color: #059669;
}

.status-inactive {
  background: #fee2e2;
  color: #dc2626;
}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Logistics</h2>
  </div>

  <a href="logi_dashboard.php"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="logi_orders.php"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="logi_drivers.php"><i class="fas fa-truck"></i><span class="text">Drivers</span></a>
  <a href="logi_reports.php"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="logi_settings.php" class="active"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  <a href="logout.php" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
</div>

<div class="main-content" id="main">

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1><i class="fas fa-building"></i> Logistics Settings</h1>
    <p class="text-muted mb-0">Manage your logistics company profile and preferences</p>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i> <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="grid grid-2-1">
  
  <!-- LEFT COLUMN -->
  <div class="left-col">
    
    <!-- Company Profile Card -->
    <div class="card">
      <h2 class="section-title"><i class="fas fa-building"></i> Company Profile</h2>
      <form method="POST">
        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control" 
                   value="<?php echo htmlspecialchars($logistics['company_name']); ?>" required>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-control" 
                   value="<?php echo htmlspecialchars($logistics['contact_number']); ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Contact Email</label>
            <input type="email" name="contact_email" class="form-control" 
                   value="<?php echo htmlspecialchars($logistics['contact_email']); ?>">
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">License Number</label>
            <input type="text" name="license_number" class="form-control" 
                   value="<?php echo htmlspecialchars($logistics['license_number']); ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Authorized Person</label>
            <input type="text" name="authorized_person" class="form-control" 
                   value="<?php echo htmlspecialchars($logistics['authorized_person']); ?>">
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Website</label>
            <input type="url" name="website" class="form-control" 
                   value="<?php echo htmlspecialchars($logistics['website']); ?>">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Service Type</label>
            <select name="service_type" class="form-select">
              <option value="standard" <?php echo $logistics['service_type'] == 'standard' ? 'selected' : ''; ?>>Standard Delivery</option>
              <option value="express" <?php echo $logistics['service_type'] == 'express' ? 'selected' : ''; ?>>Express Delivery</option>
              <option value="both" <?php echo $logistics['service_type'] == 'both' ? 'selected' : ''; ?>>Both</option>
            </select>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Business Address</label>
          <textarea name="business_address" class="form-control" rows="2"><?php echo htmlspecialchars($logistics['business_address']); ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Coverage Areas</label>
          <textarea name="coverage_areas" class="form-control" rows="2" placeholder="e.g., Metro Manila, Luzon, Visayas, Mindanao"><?php echo htmlspecialchars($logistics['coverage_areas']); ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Operating Hours</label>
          <input type="text" name="operating_hours" class="form-control" 
                 value="<?php echo htmlspecialchars($logistics['operating_hours']); ?>" 
                 placeholder="e.g., Mon-Fri: 8AM-6PM, Sat: 9AM-3PM">
        </div>
        <div class="text-end">
          <button type="submit" name="update_profile" class="btn-primary">
            <i class="fas fa-save"></i> Update Profile
          </button>
        </div>
      </form>
    </div>

    <!-- Shipping Zones -->
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 class="section-title" style="margin: 0;"><i class="fas fa-map-marker-alt"></i> Shipping Zones</h2>
        <button class="btn-outline" data-bs-toggle="modal" data-bs-target="#addZoneModal">
          <i class="fas fa-plus"></i> Add Zone
        </button>
      </div>
      
      <?php if ($shipping_zones->num_rows == 0): ?>
        <div class="text-center text-muted py-4">No shipping zones configured. Click "Add Zone" to create one.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr><th>Zone Name</th><th>Areas Covered</th><th>Base Rate</th><th>Addt'l per kg</th><th>Est. Days</th></tr>
            </thead>
            <tbody>
              <?php while($zone = $shipping_zones->fetch_assoc()): ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($zone['zone_name']); ?></strong></td>
                <td><?php echo htmlspecialchars($zone['areas_covered']); ?></td>
                <td>₱<?php echo number_format($zone['base_rate'], 2); ?></td>
                <td>₱<?php echo number_format($zone['additional_per_kg'], 2); ?></td>
                <td><?php echo htmlspecialchars($zone['estimated_days'] ?: '-'); ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Couriers -->
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 class="section-title" style="margin: 0;"><i class="fas fa-truck"></i> Courier Partners</h2>
        <button class="btn-outline" data-bs-toggle="modal" data-bs-target="#addCourierModal">
          <i class="fas fa-plus"></i> Add Courier
        </button>
      </div>
      
      <?php if ($couriers->num_rows == 0): ?>
        <div class="text-center text-muted py-4">No couriers added. Click "Add Courier" to add one.</div>
      <?php else: 
        while($courier = $couriers->fetch_assoc()): ?>
        <div class="courier-item">
          <div class="courier-info">
            <h4><?php echo htmlspecialchars($courier['name']); ?></h4>
            <p><?php echo htmlspecialchars($courier['description']); ?></p>
            <small>Delivery Fee: ₱<?php echo number_format($courier['delivery_fee'], 2); ?></small>
          </div>
          <input type="checkbox" class="checkbox-custom courier-toggle" 
                 data-id="<?php echo $courier['courier_id']; ?>"
                 <?php echo $courier['is_enabled'] ? 'checked' : ''; ?>>
        </div>
      <?php endwhile; endif; ?>
    </div>

  </div>

  <!-- RIGHT COLUMN -->
  <div class="right-col">
    
    <!-- Account Information -->
    <div class="card">
      <h2 class="section-title"><i class="fas fa-user-circle"></i> Account Information</h2>
      <div class="mb-2">
        <strong>Username:</strong> <?php echo htmlspecialchars($user_info['username']); ?>
      </div>
      <div class="mb-2">
        <strong>Name:</strong> <?php echo htmlspecialchars($user_info['first_name'] . ' ' . $user_info['last_name']); ?>
      </div>
      <div class="mb-2">
        <strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?>
      </div>
      <div class="mb-2">
        <strong>Role:</strong> <span class="badge bg-secondary">Logistics</span>
      </div>
      <div class="mb-2">
        <strong>Status:</strong> 
        <span class="status-badge status-<?php echo $logistics['status']; ?>">
          <?php echo ucfirst($logistics['status']); ?>
        </span>
      </div>
      <div class="mb-2">
        <strong>Rating:</strong> 
        <?php 
        $rating = $logistics['rating'];
        for($i = 1; $i <= 5; $i++) {
            if($i <= $rating) {
                echo '<i class="fas fa-star text-warning"></i>';
            } elseif($i - 0.5 <= $rating) {
                echo '<i class="fas fa-star-half-alt text-warning"></i>';
            } else {
                echo '<i class="far fa-star text-warning"></i>';
            }
        }
        ?>
      </div>
    </div>

    <!-- Notification Settings -->
    <div class="card">
      <h2 class="section-title"><i class="fas fa-bell"></i> Notifications</h2>
      <form method="POST">
        <div class="notification-item">
          <div class="notification-info">
            <h4>Customer SMS</h4>
            <p>Send SMS updates for 'Out for Delivery' and 'Delivered' statuses.</p>
          </div>
          <input type="checkbox" name="customer_sms" class="checkbox-custom" 
                 <?php echo $notif_settings['customer_sms'] ? 'checked' : ''; ?>>
        </div>

        <div class="notification-item">
          <div class="notification-info">
            <h4>Customer Email</h4>
            <p>Send email updates for all status changes.</p>
          </div>
          <input type="checkbox" name="customer_email" class="checkbox-custom" 
                 <?php echo $notif_settings['customer_email'] ? 'checked' : ''; ?>>
        </div>

        <div class="notification-item">
          <div class="notification-info">
            <h4>Seller Alerts</h4>
            <p>Notify sellers of failed delivery attempts or returns.</p>
          </div>
          <input type="checkbox" name="seller_alerts" class="checkbox-custom" 
                 <?php echo $notif_settings['seller_alerts'] ? 'checked' : ''; ?>>
        </div>

        <div class="notification-item">
          <div class="notification-info">
            <h4>Driver Notifications</h4>
            <p>Send notifications to drivers for new assignments.</p>
          </div>
          <input type="checkbox" name="driver_notifications" class="checkbox-custom" 
                 <?php echo $notif_settings['driver_notifications'] ? 'checked' : ''; ?>>
        </div>

        <div class="mt-3">
          <button type="submit" name="update_notifications" class="btn-primary">
            <i class="fas fa-save"></i> Save Notification Settings
          </button>
        </div>
      </form>
    </div>

  </div>

</div>

</div>

<!-- Add Zone Modal -->
<div class="modal fade" id="addZoneModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-map-marker-alt"></i> Add Shipping Zone</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Zone Name *</label>
            <input type="text" name="zone_name" class="form-control" required placeholder="e.g., Metro Manila">
          </div>
          <div class="mb-3">
            <label class="form-label">Areas Covered *</label>
            <textarea name="areas_covered" class="form-control" rows="2" required placeholder="List of provinces/cities covered"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Base Rate (₱) *</label>
              <input type="number" name="base_rate" class="form-control" step="0.01" required placeholder="0.00">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Additional per kg (₱) *</label>
              <input type="number" name="additional_per_kg" class="form-control" step="0.01" required placeholder="0.00">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Estimated Delivery Days</label>
            <input type="text" name="estimated_days" class="form-control" placeholder="e.g., 2-3 days">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_zone" class="btn-primary">Add Zone</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Courier Modal -->
<div class="modal fade" id="addCourierModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-truck"></i> Add Courier Partner</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Courier Name *</label>
            <input type="text" name="courier_name" class="form-control" required placeholder="e.g., J&T Express">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="courier_description" class="form-control" rows="2" placeholder="Description of services..."></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Delivery Fee (₱)</label>
            <input type="number" name="delivery_fee" class="form-control" step="0.01" value="0.00">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_courier" class="btn-primary">Add Courier</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.getElementById("main").classList.toggle("full");
}

// Handle courier toggle via AJAX
document.querySelectorAll('.courier-toggle').forEach(toggle => {
  toggle.addEventListener('change', function() {
    const courierId = this.dataset.id;
    const isEnabled = this.checked ? 1 : 0;
    
    fetch('logi_settings.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `toggle_courier=1&courier_id=${courierId}&is_enabled=${isEnabled}`
    });
  });
});
</script>

</body>
</html>