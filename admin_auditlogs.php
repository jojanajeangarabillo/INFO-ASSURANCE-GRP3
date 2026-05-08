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

// Number of items per page
$items_per_page = 10;

// Function to build pagination URL
function buildPaginationUrl($tab, $page, $date_param = '') {
    $params = ['tab' => $tab, 'page' => $page];
    if (!empty($date_param)) {
        $params[$date_param] = $_GET[$date_param] ?? '';
    }
    return '?' . http_build_query($params);
}

// Get date filters from GET parameters (single date)
$login_date = isset($_GET['login_date']) ? $_GET['login_date'] : '';
$audit_date = isset($_GET['audit_date']) ? $_GET['audit_date'] : '';
$locked_date = isset($_GET['locked_date']) ? $_GET['locked_date'] : '';

// Get current page numbers
$login_page = isset($_GET['page']) && ($_GET['tab'] ?? '') === 'loginHistory' ? max(1, (int)$_GET['page']) : 1;
$audit_page = isset($_GET['page']) && ($_GET['tab'] ?? '') === 'auditLog' ? max(1, (int)$_GET['page']) : 1;
$locked_page = isset($_GET['page']) && ($_GET['tab'] ?? '') === 'lockedAccounts' ? max(1, (int)$_GET['page']) : 1;

// Function to fetch paginated data
function fetchPaginatedData($conn, $table, $date_column, $date_filter, $current_page, $items_per_page, $extra_join = '') {
    $offset = ($current_page - 1) * $items_per_page;
    
    // Count total items
    $count_query = "SELECT COUNT(*) as total FROM {$table} t";
    if ($extra_join) {
        $count_query .= " " . $extra_join;
    }
    if (!empty($date_filter)) {
        $count_query .= " WHERE DATE(t.{$date_column}) = ?";
    }
    
    if (!empty($date_filter)) {
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->bind_param("s", $date_filter);
        $count_stmt->execute();
        $total_items = $count_stmt->get_result()->fetch_assoc()['total'];
    } else {
        $count_result = $conn->query($count_query);
        $total_items = $count_result->fetch_assoc()['total'];
    }
    
    $total_pages = ceil($total_items / $items_per_page);
    
    // Fetch data for current page
    $data_query = "SELECT t.*, u.username FROM {$table} t LEFT JOIN user u ON t.user_id = u.user_id";
    if (!empty($date_filter)) {
        $data_query .= " WHERE DATE(t.{$date_column}) = ?";
    }
    $data_query .= " ORDER BY t.{$date_column} DESC LIMIT ? OFFSET ?";
    
    if (!empty($date_filter)) {
        $data_stmt = $conn->prepare($data_query);
        $data_stmt->bind_param("sii", $date_filter, $items_per_page, $offset);
    } else {
        $data_stmt = $conn->prepare($data_query);
        $data_stmt->bind_param("ii", $items_per_page, $offset);
    }
    $data_stmt->execute();
    $data = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return [
        'data' => $data,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'total_items' => $total_items
    ];
}

// Fetch login history
$loginData = fetchPaginatedData($conn, 'login_history', 'login_time', $login_date, $login_page, $items_per_page);
$loginHistory = $loginData['data'];
$login_total_pages = $loginData['total_pages'];
$login_current_page = $loginData['current_page'];
$login_total_items = $loginData['total_items'];

// Fetch audit log
$auditData = fetchPaginatedData($conn, 'audit_log', 'created_at', $audit_date, $audit_page, $items_per_page);
$auditLog = $auditData['data'];
$audit_total_pages = $auditData['total_pages'];
$audit_current_page = $auditData['current_page'];
$audit_total_items = $auditData['total_items'];

// Fetch locked accounts
$lockedData = fetchPaginatedData($conn, 'locked_accs', 'date_time', $locked_date, $locked_page, $items_per_page);
$lockedAccs = $lockedData['data'];
$locked_total_pages = $lockedData['total_pages'];
$locked_current_page = $lockedData['current_page'];
$locked_total_items = $lockedData['total_items'];

// Get current active tab from URL parameter
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'loginHistory';

// Function to render pagination
function renderPagination($current_page, $total_pages, $tab, $date_param = '') {
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<a href="' . buildPaginationUrl($tab, $current_page - 1, $date_param) . '">&laquo; Previous</a>';
    } else {
        $html .= '<span class="disabled">&laquo; Previous</span>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= '<span class="active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . buildPaginationUrl($tab, $i, $date_param) . '">' . $i . '</a>';
        }
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $html .= '<a href="' . buildPaginationUrl($tab, $current_page + 1, $date_param) . '">Next &raquo;</a>';
    } else {
        $html .= '<span class="disabled">Next &raquo;</span>';
    }
    
    $html .= '</div>';
    return $html;
}
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

.date-filter {
  background: #f9f9f9;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
  align-items: flex-end;
}

.date-filter-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.date-filter-group label {
  font-size: 12px;
  font-weight: bold;
  color: #610C27;
}

.date-filter-group input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.date-filter button {
  background: #a61b4a;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

.date-filter button:hover {
  background: #7a1438;
}

.date-filter .reset-btn {
  background: #666;
}

.date-filter .reset-btn:hover {
  background: #444;
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

.filter-summary {
  background: #e9ecef;
  padding: 8px 15px;
  border-radius: 6px;
  margin-bottom: 15px;
  font-size: 13px;
  color: #495057;
}

.filter-summary strong {
  color: #610C27;
}

/* Pagination styles */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.pagination a, .pagination span {
  padding: 8px 14px;
  border: 1px solid #ddd;
  border-radius: 6px;
  text-decoration: none;
  color: #610C27;
  font-size: 14px;
  transition: all 0.2s;
}

.pagination a:hover {
  background: #f9dbe5;
  border-color: #a61b4a;
}

.pagination .active {
  background: #a61b4a;
  color: white;
  border-color: #a61b4a;
}

.pagination .disabled {
  color: #999;
  cursor: not-allowed;
  background: #f5f5f5;
}

.pagination-info {
  text-align: center;
  color: #666;
  font-size: 13px;
  margin-top: 10px;
}

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
  // Update URL parameter
  const url = new URL(window.location.href);
  url.searchParams.set('tab', tabId);
  window.history.pushState({}, '', url);
  
  // Show/hide tabs
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.tabs button').forEach(btn => btn.classList.remove('active'));
  
  document.getElementById(tabId).style.display = 'block';
  event.target.classList.add('active');
}

// Apply filter for a specific tab
function applyFilter(tabName) {
  const date = document.getElementById(`${tabName}_date`).value;
  
  const url = new URL(window.location.href);
  if (date) {
    url.searchParams.set(`${tabName}_date`, date);
  } else {
    url.searchParams.delete(`${tabName}_date`);
  }
  url.searchParams.set('tab', tabName);
  window.location.href = url.toString();
}

// Reset filter for a specific tab
function resetFilter(tabName) {
  const url = new URL(window.location.href);
  url.searchParams.delete(`${tabName}_date`);
  url.searchParams.set('tab', tabName);
  window.location.href = url.toString();
}

// Set active tab on page load
document.addEventListener('DOMContentLoaded', function() {
  const activeTab = '<?php echo $active_tab; ?>';
  const tabButtons = document.querySelectorAll('.tabs button');
  const tabContents = document.querySelectorAll('.tab-content');
  
  tabContents.forEach(content => content.style.display = 'none');
  tabButtons.forEach(btn => btn.classList.remove('active'));
  
  document.getElementById(activeTab).style.display = 'block';
  // Find and activate the corresponding button
  tabButtons.forEach(btn => {
    if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(activeTab)) {
      btn.classList.add('active');
    }
  });
});
</script>

<div class="container" id="main">

<h1 class="page-title">Audit Logs</h1>
<p>Monitor all user activities and login events across the platform.</p>

<div class="tabs mb-4">
  <button class="<?php echo $active_tab === 'loginHistory' ? 'active' : ''; ?>" onclick="showTab('loginHistory')">Login History</button>
  <button class="<?php echo $active_tab === 'auditLog' ? 'active' : ''; ?>" onclick="showTab('auditLog')">Action History</button>
  <button class="<?php echo $active_tab === 'lockedAccounts' ? 'active' : ''; ?>" onclick="showTab('lockedAccounts')">Locked Accounts</button>
</div>

<!-- Login History -->
<div id="loginHistory" class="tab-content" style="<?php echo $active_tab === 'loginHistory' ? 'display: block;' : 'display: none;'; ?>">
  <div class="card">
    <div class="date-filter">
      <div class="date-filter-group">
        <label>Filter by Date</label>
        <input type="date" id="login_date" value="<?php echo htmlspecialchars($login_date); ?>">
      </div>
      <button onclick="applyFilter('login')">Apply Filter</button>
      <button class="reset-btn" onclick="resetFilter('login')">Reset</button>
    </div>
    
    <?php if (!empty($login_date)): ?>
    <div class="filter-summary">
      <strong>Showing records for: <?php echo htmlspecialchars($login_date); ?></strong>
    </div>
    <?php endif; ?>
    
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
          <tr><td colspan="4" style="text-align: center; color: #999;">
            <?php echo !empty($login_date) ? 'No login history available for ' . htmlspecialchars($login_date) : 'No login history available.'; ?>
          </td></tr>
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
    <?php echo renderPagination($login_current_page, $login_total_pages, 'loginHistory', 'login_date'); ?>
    <div class="pagination-info">
        Showing page <?php echo $login_current_page; ?> of <?php echo $login_total_pages; ?> (<?php echo $login_total_items; ?> total records)
    </div>
  </div>
</div>

<!-- Audit Log -->
<div id="auditLog" class="tab-content" style="<?php echo $active_tab === 'auditLog' ? 'display: block;' : 'display: none;'; ?>">
  <div class="card">
    <div class="date-filter">
      <div class="date-filter-group">
        <label>Filter by Date</label>
        <input type="date" id="audit_date" value="<?php echo htmlspecialchars($audit_date); ?>">
      </div>
      <button onclick="applyFilter('audit')">Apply Filter</button>
      <button class="reset-btn" onclick="resetFilter('audit')">Reset</button>
    </div>
    
    <?php if (!empty($audit_date)): ?>
    <div class="filter-summary">
      <strong>Showing records for: <?php echo htmlspecialchars($audit_date); ?></strong>
    </div>
    <?php endif; ?>
    
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Action</th>
          <th>Module</th>
          <th>Description</th>
          <th>Date & Time</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($auditLog)): ?>
          <tr><td colspan="5" style="text-align: center; color: #999;">
            <?php echo !empty($audit_date) ? 'No action history available for ' . htmlspecialchars($audit_date) : 'No action history available.'; ?>
          </td></tr>
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
    <?php echo renderPagination($audit_current_page, $audit_total_pages, 'auditLog', 'audit_date'); ?>
    <div class="pagination-info">
        Showing page <?php echo $audit_current_page; ?> of <?php echo $audit_total_pages; ?> (<?php echo $audit_total_items; ?> total records)
    </div>
  </div>
</div>

<!-- Locked Accounts -->
<div id="lockedAccounts" class="tab-content" style="<?php echo $active_tab === 'lockedAccounts' ? 'display: block;' : 'display: none;'; ?>">
  <div class="card">
    <div class="date-filter">
      <div class="date-filter-group">
        <label>Filter by Date</label>
        <input type="date" id="locked_date" value="<?php echo htmlspecialchars($locked_date); ?>">
      </div>
      <button onclick="applyFilter('locked')">Apply Filter</button>
      <button class="reset-btn" onclick="resetFilter('locked')">Reset</button>
    </div>
    
    <?php if (!empty($locked_date)): ?>
    <div class="filter-summary">
      <strong>Showing records for: <?php echo htmlspecialchars($locked_date); ?></strong>
    </div>
    <?php endif; ?>
    
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
          <tr><td colspan="4" style="text-align: center; color: #999;">
            <?php echo !empty($locked_date) ? 'No locked accounts available for ' . htmlspecialchars($locked_date) : 'No locked accounts available.'; ?>
           </td></tr>
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
    <?php echo renderPagination($locked_current_page, $locked_total_pages, 'lockedAccounts', 'locked_date'); ?>
    <div class="pagination-info">
        Showing page <?php echo $locked_current_page; ?> of <?php echo $locked_total_pages; ?> (<?php echo $locked_total_items; ?> total records)
    </div>
  </div>
</div>

</div>
</body>
</html>