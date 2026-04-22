<?php
require_once 'auth.php';
require_roles([3, 4]);

require_once __DIR__ . '/admin/db.connect.php';

$user_id = $_SESSION['user_id'] ?? 0;

$seller_stmt = $conn->prepare("SELECT seller_id FROM seller WHERE user_id = ?");
$seller_stmt->bind_param("i", $user_id);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_id = $seller['seller_id'] ?? 0;
$seller_stmt->close();

$conversations = [];
$conv_stmt = $conn->prepare("
    SELECT c.conversation_id, c.customer_id as user_id, u.username, u.email,
           (SELECT COUNT(*) FROM message m WHERE m.conversation_id = c.conversation_id AND m.is_read = 0 AND m.sender_user_id != ?) as unread_count,
           (SELECT m.message_body FROM message m WHERE m.conversation_id = c.conversation_id ORDER BY m.created_at DESC LIMIT 1) as last_message,
           (SELECT m.created_at FROM message m WHERE m.conversation_id = c.conversation_id ORDER BY m.created_at DESC LIMIT 1) as last_time
    FROM conversation c
    JOIN user u ON c.customer_id = u.user_id
    WHERE c.seller_id = ?
    ORDER BY last_time DESC
");
$conv_stmt->bind_param("ii", $user_id, $seller_id);
$conv_stmt->execute();
$conv_result = $conv_stmt->get_result();
while ($row = $conv_result->fetch_assoc()) {
    $conversations[] = $row;
}
$conv_stmt->close();

$active_conv_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
$messages = [];

if ($active_conv_id > 0) {
    $msg_stmt = $conn->prepare("
        SELECT m.*, u.username as sender_name
        FROM message m
        JOIN conversation c ON m.conversation_id = c.conversation_id
        JOIN user u ON m.sender_user_id = u.user_id
        WHERE m.conversation_id = ? AND c.seller_id = ?
        ORDER BY m.created_at ASC
    ");
    $msg_stmt->bind_param("ii", $active_conv_id, $seller_id);
    $msg_stmt->execute();
    $msg_result = $msg_stmt->get_result();
    while ($row = $msg_result->fetch_assoc()) {
        $messages[] = $row;
    }
    $msg_stmt->close();

    $unread_stmt = $conn->prepare("
        UPDATE message SET is_read = 1
        WHERE conversation_id = ? AND sender_user_id != ?
    ");
    $unread_stmt->bind_param("ii", $active_conv_id, $user_id);
    $unread_stmt->execute();
    $unread_stmt->close();
}

function formatMessageTime($datetime) {
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    $now = time();
    $diff = $now - $ts;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return date('M j', $ts);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Seller Chat</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body {
  background: #f5f5f5;
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
}

/* SIDEBAR */
.sidebar {
  width: 240px;
  position: fixed;
  height: 100%;
}

.sidebar.collapsed {
  width: 70px;
}

/* MAIN */
.main-content {
  margin-left: 240px;
  transition: 0.3s;
  padding: 20px;
}

.sidebar.collapsed ~ .main-content {
  margin-left: 70px;
}

/* CARD */
.card-custom {
  background: #fff;
  border-radius: 16px;
  border: none;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

/* BRAND */
:root {
  --brand: #6d0f1b;
}

/* CHAT */
.chat-container {
  height: 600px;
}

/* CHAT BUBBLES */
.chat-bubble {
  padding: 10px 15px;
  border-radius: 15px;
  max-width: 75%;
  font-size: 14px;
}

.chat-me {
  background: var(--brand);
  color: white;
  border-bottom-right-radius: 0;
}

.chat-other {
  background: white;
  border: 1px solid #ddd;
  border-bottom-left-radius: 0;
}

/* AVATAR */
.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--brand);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="logo-text">Seller</div>
  </div>

    <a href="seller_dashboard.php"><i class="bi bi-speedometer2"></i><span class="text">Dashboard</span></a>
    <a href="seller_products.php"><i class="bi bi-box-seam"></i><span class="text">Products</span></a>
    <a href="seller_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
    <a href="seller_inventory.php"><i class="bi bi-box"></i><span class="text">Inventory</span></a>
    <a href="seller_reviews.php"><i class="bi bi-star"></i><span class="text">Reviews</span></a>
    <a href="seller_profile.php"><i class="bi bi-shop"></i><span class="text">Profile</span></a>
    <a href="seller_chat.php" class="active"><i class="bi bi-chat-dots"></i><span class="text">Chat</span></a>

  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>

</div>

<!-- MAIN -->
<div class="main-content">
<div class="container-fluid">

<h3 class="fw-bold">Customer Chat</h3>
<p class="text-muted">Communicate with your buyers.</p>

<div class="row g-3 chat-container">

<!-- CONVERSATIONS -->
<div class="col-md-4 col-lg-3">
<div class="card-custom h-100 d-flex flex-column">

<div class="p-3 border-bottom">
  <input class="form-control" placeholder="Search messages...">
</div>

<div class="flex-grow-1 overflow-auto" id="conversationList">
  <?php if (empty($conversations)): ?>
    <div class="p-3 text-center text-muted">No conversations yet.</div>
  <?php else: ?>
    <?php foreach ($conversations as $conv): ?>
      <a href="?conversation_id=<?php echo $conv['conversation_id']; ?>" 
         class="p-3 border-bottom d-flex align-items-center gap-3 text-decoration-none text-dark conversation-item <?php echo $active_conv_id == $conv['conversation_id'] ? 'bg-light' : ''; ?>">
        <div class="avatar"><?php echo strtoupper(substr($conv['username'], 0, 1)); ?></div>
        <div class="flex-grow-1 overflow-hidden">
          <div class="d-flex justify-content-between">
            <span class="fw-bold"><?php echo htmlspecialchars($conv['username']); ?></span>
            <small class="text-muted"><?php echo formatMessageTime($conv['last_time']); ?></small>
          </div>
          <div class="text-truncate small text-muted">
            <?php echo htmlspecialchars($conv['last_message'] ?? 'No messages yet'); ?>
          </div>
        </div>
        <?php if ($conv['unread_count'] > 0): ?>
          <span class="badge rounded-pill bg-danger"><?php echo $conv['unread_count']; ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

</div>
</div>

<!-- CHAT AREA -->
<div class="col-md-8 col-lg-9">
<div class="card-custom h-100 d-flex flex-column">

<!-- HEADER -->
<div class="p-3 border-bottom bg-light fw-bold" id="chatTitle">
  <?php 
  if ($active_conv_id > 0) {
      $current_user = '';
      foreach ($conversations as $c) {
          if ($c['conversation_id'] == $active_conv_id) {
              $current_user = $c['username'];
              break;
          }
      }
      echo "Chat with " . htmlspecialchars($current_user);
  } else {
      echo "Select a conversation";
  }
  ?>
</div>

<!-- MESSAGES -->
<div class="flex-grow-1 overflow-auto p-3" id="chatMessages" style="background:#fafafa;">
  <?php if ($active_conv_id == 0): ?>
    <div class="h-100 d-flex align-items-center justify-content-center text-muted">
      Select a customer to start chatting
    </div>
  <?php elseif (empty($messages)): ?>
    <div class="text-center text-muted mt-4">No messages yet.</div>
  <?php else: ?>
    <?php foreach ($messages as $msg): ?>
      <?php $isMe = ($msg['sender_user_id'] == $user_id); ?>
      <div class="mb-3 d-flex <?php echo $isMe ? 'justify-content-end' : 'justify-content-start'; ?>">
        <div style="max-width: 75%;">
          <div class="chat-bubble <?php echo $isMe ? 'chat-me' : 'chat-other'; ?>">
            <?php echo nl2br(htmlspecialchars($msg['message_body'])); ?>
          </div>
          <small class="text-muted d-block mt-1 <?php echo $isMe ? 'text-end' : 'text-start'; ?>">
            <?php echo date('h:i A', strtotime($msg['created_at'])); ?>
          </small>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- INPUT -->
<div class="p-3 border-top">
<?php if ($active_conv_id > 0): ?>
<form action="seller_chat_send.php" method="POST" class="d-flex gap-2">
  <input type="hidden" name="conversation_id" value="<?php echo $active_conv_id; ?>">
  <input name="message_text" id="messageInput" class="form-control" placeholder="Type a message..." required autocomplete="off">
  <button type="submit" class="btn text-white" style="background:#6d0f1b;">➤</button>
</form>
<?php else: ?>
  <input class="form-control" placeholder="Select a conversation to type..." disabled>
<?php endif; ?>
</div>

</div>
</div>

</div>

</div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
}

// Auto-scroll to bottom of chat
const chatMessages = document.getElementById('chatMessages');
if (chatMessages) {
  chatMessages.scrollTop = chatMessages.scrollHeight;
}
</script>

</body>
</html>