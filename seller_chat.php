<?php
require_once 'auth.php';
require_roles([3, 4]);
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

  <a href="#" class="logout">
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

<div class="flex-grow-1 overflow-auto" id="conversationList"></div>

</div>
</div>

<!-- CHAT AREA -->
<div class="col-md-8 col-lg-9">
<div class="card-custom h-100 d-flex flex-column">

<!-- HEADER -->
<div class="p-3 border-bottom bg-light fw-bold" id="chatTitle">
  Select a conversation
</div>

<!-- MESSAGES -->
<div class="flex-grow-1 overflow-auto p-3" id="chatMessages" style="background:#fafafa;"></div>

<!-- INPUT -->
<div class="p-3 border-top">
<form onsubmit="sendMessage(event)" class="d-flex gap-2">
  <input id="messageInput" class="form-control" placeholder="Type a message...">
  <button class="btn text-white" style="background:#6d0f1b;">➤</button>
</form>
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

/* DATA */
const conversations = [
  { id: 'Customer1', name: 'Alice Smith', avatar: 'A' },
  { id: 'Customer2', name: 'Bob Johnson', avatar: 'B' },
  { id: 'Customer3', name: 'Charlie Brown', avatar: 'C' },
];

let activeChat = null;
let messages = [];

/* LOAD CONVERSATIONS */
const list = document.getElementById("conversationList");

conversations.forEach(c => {
  const div = document.createElement("div");
  div.className = "p-3 border-bottom d-flex align-items-center gap-3 cursor-pointer";
  div.style.cursor = "pointer";

  div.innerHTML = `
    <div class="avatar">${c.avatar}</div>
    <div>
      <div class="fw-bold">${c.name}</div>
    </div>
  `;

  div.onclick = () => openChat(c.id);
  list.appendChild(div);
});

/* OPEN CHAT */
function openChat(id) {
  activeChat = id;
  document.getElementById("chatTitle").innerText =
    conversations.find(c => c.id === id).name;

  renderMessages();
}

/* RENDER */
function renderMessages() {
  const container = document.getElementById("chatMessages");
  container.innerHTML = "";

  const filtered = messages.filter(m =>
    (m.from === "Seller" && m.to === activeChat) ||
    (m.from === activeChat && m.to === "Seller")
  );

  if (filtered.length === 0) {
    container.innerHTML = "<div class='text-center text-muted mt-4'>No messages yet.</div>";
    return;
  }

  filtered.forEach(m => {
    const isMe = m.from === "Seller";

    const div = document.createElement("div");
    div.className = "mb-2 d-flex " + (isMe ? "justify-content-end" : "justify-content-start");

    div.innerHTML = `
      <div>
        <div class="chat-bubble ${isMe ? 'chat-me' : 'chat-other'}">
          ${m.message}
        </div>
        <small class="text-muted">${m.time}</small>
      </div>
    `;

    container.appendChild(div);
  });

  container.scrollTop = container.scrollHeight;
}

/* SEND */
function sendMessage(e) {
  e.preventDefault();

  const input = document.getElementById("messageInput");
  const text = input.value.trim();

  if (!text || !activeChat) return;

  messages.push({
    from: "Seller",
    to: activeChat,
    message: text,
    time: new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})
  });

  input.value = "";
  renderMessages();
}
</script>

</body>
</html>