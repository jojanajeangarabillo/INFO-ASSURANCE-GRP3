<?php
require_once 'auth.php';
require_roles([1]);

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

// Fetch login history
$loginHistoryStmt = $conn->query("
    SELECT lh.*, u.username 
    FROM login_history lh 
    LEFT JOIN user u ON lh.user_id = u.user_id 
    ORDER BY lh.login_time DESC 
    LIMIT 100
");
$loginHistory = $loginHistoryStmt->fetch_all(MYSQLI_ASSOC);

// Fetch audit log
$auditLogStmt = $conn->query("
    SELECT al.*, u.username 
    FROM audit_log al 
    LEFT JOIN user u ON al.user_id = u.user_id 
    ORDER BY al.created_at DESC 
    LIMIT 100
");
$auditLog = $auditLogStmt->fetch_all(MYSQLI_ASSOC);

// Fetch locked accounts
$lockedAccsStmt = $conn->query("
    SELECT la.*, u.username 
    FROM locked_accs la 
    LEFT JOIN user u ON la.user_id = u.user_id 
    ORDER BY la.date_time DESC 
    LIMIT 100
");
$lockedAccs = $lockedAccsStmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Audit Logs - J3RS</title>
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
  transition: 0.3s;
}
.container.full {
  margin-left: 70px;
}

.page-title {
  font-size: 32px;
  font-weight: bold;
  color: #610C27;
  margin-bottom: 5px;
}

.tabs button {
  padding: 10px 20px;
  border: none;
  background: none;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  font-size: 16px;
  color: #666;
}
.tabs button.active {
  border-bottom: 2px solid #a61b4a;
  color: #a61b4a;
  font-weight: bold;
}

.card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}
th, td {
  padding: 12px 15px;
  border-bottom: 1px solid #eee;
  text-align: left;
}
th {
  background: #f9dbe5;
  color: #610C27;
  font-weight: bold;
}

.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}
.success { background: #d1fae5; color: #065f46; }
.danger { background: #fee2e2; color: #991b1b; }
.warning { background: #fef3c7; color: #92400e; }
.info { background: #dbeafe; color: #1e40af; }

.mb-4 { margin-bottom: 1.5rem; }

@media (max-width: 768px) {
  .container { margin-left: 0; padding: 20px; }
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

  <a href="admin_dashboard.php"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="admin_analytics.php"><i class="fas fa-chart-line"></i><span class="text">Analytics</span></a>
  <a href="admin_users.php"><i class="fas fa-users"></i><span class="text">Users</span></a>
  <a href="admin_auditlogs.php" class="active"><i class="fas fa-history"></i><span class="text">Audit Logs</span></a>
  <a href="admin_orders.php"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="admin_reports.php"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  
  <a href="admin_settings.php"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  
  <a href="logout.php" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
</div>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const main = document.getElementById("main");
  if (sidebar) sidebar.classList.toggle("collapsed");
  if (main) main.classList.toggle("full");
}

function showTab(tabId) {
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.tabs button').forEach(btn => btn.classList.remove('active'));
  
  document.getElementById(tabId).style.display = 'block';
  event.target.classList.add('active');
}
</script>

<div class="container" id="main">

<h1 class="page-title">Audit Logs</h1>
<p>Monitor all user activities and login events across the platform.</p>

<div class="tabs mb-4">
  <button class="active" onclick="showTab('loginHistory')">Login History</button>
  <button onclick="showTab('auditLog')">Action History</button>
  <button onclick="showTab('lockedAccounts')">Locked Accounts</button>
</div>

<!-- Login History -->
<div id="loginHistory" class="tab-content">
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Login Time</th>
          <th>Logout Time</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($loginHistory)): ?>
          <tr><td colspan="4" style="text-align: center; color: #999;">No login history available.</td></tr>
        <?php else: ?>
          <?php foreach ($loginHistory as $log): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></strong></td>
              <td><?php echo htmlspecialchars($log['login_time']); ?></td>
              <td><?php echo htmlspecialchars($log['logout_time'] ?? 'N/A'); ?></td>
              <td>
                <span class="badge <?php echo $log['status'] === 'success' ? 'success' : 'danger'; ?>">
                  <?php echo ucfirst($log['status']); ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Audit Log -->
<div id="auditLog" class="tab-content" style="display: none;">
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Action</th>
          <th>Module</th>
          <th>Description</th>
          <th>IP Address</th>
          <th>Date & Time</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($auditLog)): ?>
          <tr><td colspan="6" style="text-align: center; color: #999;">No action history available.</td></tr>
        <?php else: ?>
          <?php foreach ($auditLog as $log): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></strong></td>
              <td>
                <span class="badge <?php
                  $action = $log['action'];
                  $badgeClass = 'warning';
                  if ($action === 'create') $badgeClass = 'success';
                  elseif ($action === 'update') $badgeClass = 'warning';
                  elseif ($action === 'delete') $badgeClass = 'danger';
                  elseif ($action === 'login' || $action === 'logout') $badgeClass = 'info';
                  echo $badgeClass;
                ?>">
                  <?php echo ucfirst($log['action']); ?>
                </span>
              </td>
              <td><?php echo htmlspecialchars($log['module']); ?></td>
              <td><?php echo htmlspecialchars($log['description'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($log['created_at']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Locked Accounts -->
<div id="lockedAccounts" class="tab-content" style="display: none;">
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Locked ID</th>
          <th>User</th>
          <th>Attempts</th>
          <th>Date & Time</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($lockedAccs)): ?>
          <tr><td colspan="4" style="text-align: center; color: #999;">No locked accounts available.</td></tr>
        <?php else: ?>
          <?php foreach ($lockedAccs as $acc): ?>
            <tr>
              <td><?php echo htmlspecialchars($acc['locked_id']); ?></td>
              <td><strong><?php echo htmlspecialchars($acc['username'] ?? 'Unknown'); ?></strong></td>
              <td><?php echo htmlspecialchars($acc['attempts']); ?></td>
              <td><?php echo htmlspecialchars($acc['date_time']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>
</body>
</html>