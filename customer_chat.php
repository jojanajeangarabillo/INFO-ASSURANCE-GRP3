<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Chat & Support</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
  body { 
    background: #f5f1ee; 
    font-family: 'Inter', sans-serif; 
  }

  .main-content {
    margin-left: 240px;
    transition: 0.3s;
    padding: 70px 30px 30px 30px;
  }

  .sidebar.collapsed ~ .main-content {
    margin-left: 70px;
  }
  .notification-wrapper {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
  }

  .notification-panel {
    width: 340px;
    border-radius: 16px;
    border: none;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.02);
  }

  .notif-item {
    padding: 12px 0;
    cursor: pointer;
    transition: background 0.2s;
    border-radius: 12px;
  }
  .notif-item:hover {
    background: #f8f9fa;
  }
  .notif-item.unread {
    background: #fef2e8;
    border-left: 3px solid #6e0f25;
    padding-left: 12px;
  }
  .chat-main-card {
    background: #ffffff;
    border-radius: 28px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: all 0.2s ease;
  }

  .chat-header-title h3 {
    font-weight: 700;
    letter-spacing: -0.3px;
    color: #2c2c2c;
  }

  .chat-sub {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 4px;
  }

  /* left panel */
  .conversations-panel {
    background: #fefcf9;
    border-right: 1px solid #f0eae4;
    height: 100%;
    min-height: 540px;
  }

  .search-chat-wrapper {
    padding: 1.25rem 1rem 0.75rem 1rem;
    border-bottom: 1px solid #f0eae4;
  }

  .search-chat-input {
    background-color: #f8f5f2;
    border: none;
    border-radius: 40px;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    font-size: 0.85rem;
    width: 100%;
    transition: 0.2s;
  }

  .search-chat-input:focus {
    background-color: white;
    box-shadow: 0 0 0 2px rgba(110, 15, 37, 0.2);
    outline: none;
    border: none;
  }

  .search-icon-overlay {
    position: absolute;
    left: 25px;
    top: 50%;
    transform: translateY(-50%);
    color: #a99f94;
    font-size: 1rem;
    pointer-events: none;
  }

  .conversation-list {
    max-height: 480px;
    overflow-y: auto;
    padding: 0.5rem 0;
  }

  .conv-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    cursor: pointer;
    transition: all 0.2s;
    border-left: 3px solid transparent;
  }

  .conv-item:hover {
    background: #faf6f2;
  }

  .conv-item.active {
    background: #fef5ef;
    border-left-color: #6e0f25;
  }

  .conv-avatar {
    width: 48px;
    height: 48px;
    background: #f0e7df;
    border-radius: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #5e3c2c;
    flex-shrink: 0;
  }

  .conv-info {
    flex: 1;
    min-width: 0;
  }

  .conv-name {
    font-weight: 600;
    font-size: 0.95rem;
    color: #1f1e1d;
    margin-bottom: 4px;
  }

  .conv-preview {
    font-size: 0.8rem;
    color: #7b6e62;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .conv-time {
    font-size: 0.7rem;
    color: #b1a394;
    margin-left: 8px;
    white-space: nowrap;
  }

  /* right panel - chat window */
  .chat-window {
    background: #ffffff;
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 540px;
  }

  .chat-window-header {
    padding: 1.2rem 1.8rem;
    border-bottom: 1px solid #f0eae4;
    background: #ffffff;
  }

  .chat-contact-name {
    font-weight: 700;
    font-size: 1.1rem;
    color: #2c2c2c;
  }

  .chat-contact-status {
    font-size: 0.7rem;
    color: #2e7d32;
    background: #e8f5e9;
    display: inline-block;
    padding: 2px 8px;
    border-radius: 50px;
    margin-left: 8px;
  }

  .messages-area {
    flex: 1;
    padding: 1.5rem 1.8rem;
    overflow-y: auto;
    background: #fefcf9;
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
  }

  /* message input area (new) */
  .chat-input-area {
    padding: 1rem 1.5rem 1.5rem 1.5rem;
    background: #ffffff;
    border-top: 1px solid #f0eae4;
  }

  .message-input-group {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f8f5f2;
    border-radius: 60px;
    padding: 0.4rem 0.4rem 0.4rem 1.2rem;
    transition: all 0.2s;
  }

  .message-input-group:focus-within {
    background: #ffffff;
    box-shadow: 0 0 0 2px rgba(110, 15, 37, 0.15);
  }

  .message-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 0.65rem 0;
    font-size: 0.9rem;
    outline: none;
    font-family: 'Inter', sans-serif;
  }

  .message-input::placeholder {
    color: #b9aea2;
    font-weight: 400;
  }

  .send-btn {
    background: #6e0f25;
    border: none;
    border-radius: 50px;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: 0.2s;
  }

  .send-btn:hover {
    background: #8a1a34;
    transform: scale(0.96);
  }

  .message-row {
    display: flex;
    width: 100%;
  }

  .message-row.received {
    justify-content: flex-start;
  }

  .message-row.sent {
    justify-content: flex-end;
  }

  .message-bubble {
    max-width: 75%;
    padding: 10px 16px;
    border-radius: 22px;
    font-size: 0.9rem;
    line-height: 1.4;
    position: relative;
    word-wrap: break-word;
    box-shadow: 0 1px 1px rgba(0,0,0,0.02);
  }

  .message-bubble.received {
    background: #ffffff;
    border: 1px solid #ede5dc;
    border-top-left-radius: 6px;
    color: #2d2a27;
  }

  .message-bubble.sent {
    background: #6e0f25;
    color: white;
    border-top-right-radius: 6px;
  }

  .message-time {
    font-size: 0.65rem;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 4px;
    color: #8f8a85;
  }

  .sent .message-time {
    justify-content: flex-end;
    color: #d9c2bb;
  }

  .received .message-time {
    justify-content: flex-start;
  }

  @media (max-width: 768px) {
    .main-content {
      margin-left: 0;
      padding: 80px 20px 20px 20px;
    }
    .sidebar.collapsed ~ .main-content {
      margin-left: 0;
    }
    .conv-avatar {
      width: 42px;
      height: 42px;
      font-size: 1.2rem;
    }
    .message-bubble {
      max-width: 85%;
    }
    .chat-window-header, .messages-area, .chat-input-area {
      padding: 1rem;
    }
    .chat-input-area {
      padding-bottom: 1rem;
    }
  }

  .conversation-list::-webkit-scrollbar {
    width: 4px;
  }
  .conversation-list::-webkit-scrollbar-track {
    background: #f0eae4;
    border-radius: 4px;
  }
  .conversation-list::-webkit-scrollbar-thumb {
    background: #cdbcae;
    border-radius: 4px;
  }
</style>
</head>
<body>

<!-- ========= SIDEBAR ========= -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <div class="logo-text">Customer</div>
  </div>

  <a href="customer_profile.php"><i class="bi bi-person-circle"></i><span class="text">Profile</span></a>
  <a href="customer_home.php"><i class="bi bi-house"></i><span class="text">Home</span></a>
  <a href="customer_orders.php"><i class="bi bi-bag"></i><span class="text">Orders</span></a>
  <a href="customer_cart.php"><i class="bi bi-cart-check"></i><span class="text">Cart</span></a>
  <a href="customer_wishlist.php"><i class="bi bi-bookmark-heart"></i><span class="text">Wishlist</span></a>
  <a href="customer_chat.php"><i class="bi bi-chat-dots"></i><span class="text">Chat & Support</span></a>

  <a href="logout.php" class="logout">
    <i class="bi bi-box-arrow-right"></i>
    <span class="text">Logout</span>
  </a>
</div>

<!-- ========= NOTIFICATION BELL ========= -->
<div class="notification-wrapper">
  <div class="dropdown">
    <button class="btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-bell fs-4"></i>
      <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
    </button>

    <div class="dropdown-menu dropdown-menu-end p-3 shadow notification-panel">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="fs-6">Notifications</strong>
        <a href="#" id="markAllReadBtn" class="text-danger small text-decoration-none fw-semibold">Mark all as Read</a>
      </div>
      <hr class="mt-1 mb-2">
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-truck text-danger mt-1"></i>
          <div>
            <div class="fw-bold">Order #SP-2345 shipped</div>
            <small class="text-muted">Your Sony WH-1000XM4 is on the way</small>
            <div class="text-muted small">2 hours ago</div>
          </div>
        </div>
      </div>
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-tag-fill text-success mt-1"></i>
          <div>
            <div class="fw-bold">Flash Sale: Mechanical Keyboard</div>
            <small class="text-muted">Up to 20% off for limited time</small>
            <div class="text-muted small">Yesterday</div>
          </div>
        </div>
      </div>
      <div class="notif-item unread">
        <div class="d-flex gap-2">
          <i class="bi bi-cup-hot text-warning mt-1"></i>
          <div>
            <div class="fw-bold">New ceramic collection</div>
            <small class="text-muted">Handcrafted mugs just dropped</small>
            <div class="text-muted small">2 days ago</div>
          </div>
        </div>
      </div>
      <div class="notif-item">
        <div class="d-flex gap-2">
          <i class="bi bi-check-circle text-secondary mt-1"></i>
          <div>
            <div class="fw-bold">Welcome to ShopHub!</div>
            <small class="text-muted">Complete your profile for perks</small>
            <div class="text-muted small">5 days ago</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ========= MAIN CONTENT ========= -->
<div class="main-content">
  <div class="chat-main-card">
    <!-- Header section: Chat & Support + tagline -->
    <div class="px-4 pt-4 pb-2 border-0">
      <div class="chat-header-title">
        <h3 class="mb-0">Chat & Support</h3>
        <p class="chat-sub mt-1">Communicate with sellers and our support team.</p>
      </div>
    </div>

    <!-- Two column layout: conversations list + active chat window -->
    <div class="row g-0">
      <!-- LEFT COLUMN: conversation list -->
      <div class="col-md-5 col-lg-4 conversations-panel">
        <div class="search-chat-wrapper position-relative">
          <i class="bi bi-search search-icon-overlay"></i>
          <input type="text" class="search-chat-input" placeholder="Search messages..." aria-label="Search messages">
        </div>
        <div class="conversation-list">
          <!-- Customer Support-->
          <div class="conv-item active" data-chat="support">
            <div class="conv-avatar">
              <i class="bi bi-headset"></i>
            </div>
            <div class="conv-info">
              <div class="d-flex justify-content-between align-items-center">
                <span class="conv-name">Customer Support</span>
                <span class="conv-time">10:00 AM</span>
              </div>
              <div class="conv-preview">How can we help you today?</div>
            </div>
          </div>
          <!-- TechGadgets Store -->
          <div class="conv-item" data-chat="tech">
            <div class="conv-avatar">
              <i class="bi bi-laptop"></i>
            </div>
            <div class="conv-info">
              <div class="d-flex justify-content-between align-items-center">
                <span class="conv-name">TechGadgets Store</span>
                <span class="conv-time">10:05 AM</span>
              </div>
              <div class="conv-preview">Your item has been shipped.</div>
            </div>
          </div>
          <!-- Fashion Hub -->
          <div class="conv-item" data-chat="fashion">
            <div class="conv-avatar">
              <i class="bi bi-bag-heart"></i>
            </div>
            <div class="conv-info">
              <div class="d-flex justify-content-between align-items-center">
                <span class="conv-name">Fashion Hub</span>
                <span class="conv-time">10:15 AM</span>
              </div>
              <div class="conv-preview">Yes, we have size M in stock.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN: chat window with message input area -->
      <div class="col-md-7 col-lg-8 chat-window">
        <div class="chat-window-header d-flex align-items-center">
          <i class="bi bi-chat-dots-fill me-2 fs-5" style="color: #6e0f25;"></i>
          <span class="chat-contact-name" id="chatContactName">Customer Support</span>
          <span class="chat-contact-status" id="chatContactStatus">Online</span>
        </div>
        
        <!-- messages container -->
        <div class="messages-area" id="messagesArea">
          <!-- initial messages for support (default active) -->
          <div class="message-row received">
            <div class="message-bubble received">
              Hello! How can we help you today?
              <div class="message-time">
                <i class="bi bi-clock"></i> 10:00 AM
              </div>
            </div>
          </div>
          <div class="message-row sent">
            <div class="message-bubble sent">
              I have a question about my order.
              <div class="message-time">
                <i class="bi bi-check"></i> 10:25 AM
              </div>
            </div>
          </div>
        </div>

        <!-- NEW: Message input field -->
        <div class="chat-input-area">
          <div class="message-input-group">
            <input type="text" class="message-input" id="messageInput" placeholder="Type a message..." autocomplete="off">
            <button class="send-btn" id="sendMessageBtn">
              <i class="bi bi-send-fill"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ========== SIDEBAR TOGGLE ==========
function toggleSidebar(){
  document.getElementById("sidebar").classList.toggle("collapsed");
}

// ========== NOTIFICATION LOGIC ==========
function updateUnreadBadge() {
  const unreadCount = document.querySelectorAll('.notif-item.unread').length;
  const badge = document.getElementById('notifBadge');
  if (unreadCount === 0) {
    if(badge) badge.style.display = 'none';
  } else {
    if(badge) {
      badge.style.display = 'inline-block';
      badge.innerText = unreadCount;
    }
  }
}
function markAsRead(element) {
  if (element.classList.contains('unread')) {
    element.classList.remove('unread');
    updateUnreadBadge();
  }
}
function markAllAsRead() {
  const unreadItems = document.querySelectorAll('.notif-item.unread');
  unreadItems.forEach(item => {
    item.classList.remove('unread');
  });
  updateUnreadBadge();
}
document.addEventListener('click', function(e) {
  const notifItem = e.target.closest('.notif-item');
  if (notifItem && !e.target.closest('#markAllReadBtn')) {
    markAsRead(notifItem);
  }
});
const markAllBtn = document.getElementById('markAllReadBtn');
if(markAllBtn) {
  markAllBtn.addEventListener('click', function(e) {
    e.preventDefault();
    markAllAsRead();
  });
}
updateUnreadBadge();

// ========== CHAT INTERACTION: conversation switching & sending messages ==========
// Data store for different chat histories (to preserve messages when switching)
const chatMessagesDB = {
  support: [
    { type: 'received', text: 'Hello! How can we help you today?', time: '10:00 AM' },
    { type: 'sent', text: 'I have a question about my order.', time: '10:25 AM' }
  ],
  tech: [
    { type: 'received', text: 'Your item has been shipped. Tracking ID: USPS 123456', time: '10:05 AM' },
    { type: 'received', text: 'Expected delivery by Friday.', time: '10:07 AM' }
  ],
  fashion: [
    { type: 'received', text: 'Yes, we have size M in stock. Would you like to place an order?', time: '10:15 AM' },
    { type: 'sent', text: 'How much is the shipping?', time: '10:18 AM' },
    { type: 'received', text: 'Free shipping over $50.', time: '10:20 AM' }
  ]
};

// Current active conversation
let currentChat = 'support'; // support, tech, fashion

// Function to render messages for a given chat
function renderMessages(chatId) {
  const messagesContainer = document.getElementById('messagesArea');
  if (!messagesContainer) return;
  const messages = chatMessagesDB[chatId] || [];
  messagesContainer.innerHTML = '';
  messages.forEach(msg => {
    const messageRow = document.createElement('div');
    messageRow.className = `message-row ${msg.type}`;
    const bubbleClass = msg.type === 'received' ? 'received' : 'sent';
    const timeIcon = msg.type === 'received' ? '<i class="bi bi-clock"></i>' : '<i class="bi bi-check"></i>';
    messageRow.innerHTML = `
      <div class="message-bubble ${bubbleClass}">
        ${msg.text}
        <div class="message-time">
          ${timeIcon} ${msg.time}
        </div>
      </div>
    `;
    messagesContainer.appendChild(messageRow);
  });
  // scroll to bottom
  messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Save current messages before switching, then render new
function switchConversation(chatId, contactName, statusText) {
  if (currentChat === chatId) return;
  currentChat = chatId;
  document.getElementById('chatContactName').innerText = contactName;
  document.getElementById('chatContactStatus').innerText = statusText;
  renderMessages(chatId);
}

// Add a new message to the current conversation and re-render
function addNewMessage(text, type) {
  if (!text.trim()) return false;
  const now = new Date();
  let timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  // fallback: if time string empty
  if (!timeStr) timeStr = `${now.getHours()}:${now.getMinutes().toString().padStart(2,'0')}`;
  const newMsg = {
    type: type,
    text: text.trim(),
    time: timeStr
  };
  if (chatMessagesDB[currentChat]) {
    chatMessagesDB[currentChat].push(newMsg);
  } else {
    chatMessagesDB[currentChat] = [newMsg];
  }
  renderMessages(currentChat);
  return true;
}

// Event: send message (user typed)
function sendCurrentMessage() {
  const inputField = document.getElementById('messageInput');
  const messageText = inputField.value;
  if (!messageText.trim()) return;
  addNewMessage(messageText, 'sent');
  inputField.value = '';
}

// Setup conversation switching
document.querySelectorAll('.conv-item').forEach(item => {
  item.addEventListener('click', function() {
    // remove active class from all
    document.querySelectorAll('.conv-item').forEach(ci => ci.classList.remove('active'));
    this.classList.add('active');
    const chatType = this.getAttribute('data-chat');
    let contactName = '';
    let status = 'Online';
    if (chatType === 'support') {
      contactName = 'Customer Support';
      status = 'Online';
      switchConversation('support', contactName, status);
    } else if (chatType === 'tech') {
      contactName = 'TechGadgets Store';
      status = 'Usually replies in 1h';
      switchConversation('tech', contactName, status);
    } else if (chatType === 'fashion') {
      contactName = 'Fashion Hub';
      status = 'Online';
      switchConversation('fashion', contactName, status);
    }
  });
});

// Send message on button click or Enter key
const sendBtn = document.getElementById('sendMessageBtn');
const msgInput = document.getElementById('messageInput');
if (sendBtn) {
  sendBtn.addEventListener('click', sendCurrentMessage);
}
if (msgInput) {
  msgInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      sendCurrentMessage();
    }
  });
}

renderMessages('support');
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>