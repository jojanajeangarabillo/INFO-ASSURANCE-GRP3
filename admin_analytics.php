<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Analytics & Reports</title>

  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- YOUR EXTERNAL SIDEBAR CSS -->
  <link rel="stylesheet" href="sidebar.css">
</head>

<body class="bg-gray-100">

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <h2 class="logo-text">Admin</h2>
  </div>

  <a href="#"><i class="fas fa-table-columns"></i><span class="text">Dashboard</span></a>
  <a href="#"><i class="fas fa-chart-line"></i><span class="text">Analytics</span></a>
  <a href="#"><i class="fas fa-users"></i><span class="text">Users</span></a>
  <a href="#"><i class="fas fa-box"></i><span class="text">Products</span></a>
  <a href="#"><i class="fas fa-cart-shopping"></i><span class="text">Orders</span></a>
  <a href="#"><i class="fas fa-file-lines"></i><span class="text">Reports</span></a>
  <a href="#"><i class="fas fa-shield-halved"></i><span class="text">Security</span></a>
  <a href="#"><i class="fas fa-gear"></i><span class="text">Settings</span></a>
  <a href="#" class="logout"><i class="fas fa-right-from-bracket"></i><span class="text">Logout</span></a>
</div>

<!-- MAIN CONTENT -->
<div id="mainContent" class="ml-[240px] p-6 transition-all duration-300">

  <!-- Header -->
  <div class="mb-6 flex justify-between items-center">
    <div>
      <h1 style="font-size: 32px; font-weight: bold; color: #610C27; margin-bottom: 5px;">
      Analytic & Reports</h1>
      <p class="text-gray-500">Platform-wide performance and customer behavior insights.</p>
    </div>
    <div class="flex gap-2">
      <button class="bg-white border px-4 py-2 rounded">Export CSV</button>
      <button class="bg-blue-600 text-white px-4 py-2 rounded">Export PDF</button>
    </div>
  </div>

  <!-- Customer Behavior -->
  <h2 class="text-xl font-bold text-gray-900 mb-2">Customer Behavior</h2>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-white p-6 border-l-4 border-blue-500 rounded shadow">
      <div class="mb-4">📈</div>
      <div class="text-sm text-gray-500">Top Category</div>
      <div class="text-2xl font-bold">Electronics</div>
      <div class="text-xs text-green-600 mt-2">+12% from last month</div>
    </div>

    <div class="bg-white p-6 border-l-4 border-blue-500 rounded shadow">
      <div class="mb-4">👥</div>
      <div class="text-sm text-gray-500">Avg Order Value</div>
      <div class="text-2xl font-bold">₱3,450</div>
      <div class="text-xs text-green-600 mt-2">+5% from last month</div>
    </div>

    <div class="bg-white p-6 border-l-4 border-blue-500 rounded shadow">
      <div class="mb-4">🔁</div>
      <div class="text-sm text-gray-500">Repeat Customer Rate</div>
      <div class="text-2xl font-bold">42%</div>
      <div class="text-xs text-green-600 mt-2">+2% from last month</div>
    </div>

  </div>

  <!-- Seller Performance -->
  <h2 class="text-xl font-bold text-gray-900 mb-4">Seller Performance</h2>

  <div class="bg-white rounded shadow overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-100 border-b">
            <th class="p-4 text-xs font-bold text-gray-500 uppercase">Seller Name</th>
            <th class="p-4 text-xs font-bold text-gray-500 uppercase">Revenue</th>
            <th class="p-4 text-xs font-bold text-gray-500 uppercase">Orders</th>
            <th class="p-4 text-xs font-bold text-gray-500 uppercase">Rating</th>
            <th class="p-4 text-xs font-bold text-gray-500 uppercase">Fulfillment Rate</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <!-- your rows -->
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- JS -->
<script>
function applyLayout() {
  const sidebar = document.getElementById("sidebar");
  const main = document.getElementById("mainContent");

  if (sidebar.classList.contains("collapsed")) {
    main.style.marginLeft = "70px";
  } else {
    main.style.marginLeft = "240px";
  }
}

function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  sidebar.classList.toggle("collapsed");
  applyLayout();
}

// ✅ RUN ON PAGE LOAD
window.onload = applyLayout;
</script>
</body>
</html>