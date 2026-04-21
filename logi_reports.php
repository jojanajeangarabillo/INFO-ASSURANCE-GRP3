<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logistics Reports</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="sidebar.css">

<style>
body {
  background: #fdf2f6;
  font-family: Arial, sans-serif;
}

.main-content {
  margin-left: 240px;
  padding: 40px 60px;
}

.text-brand { color: #610C27; }
.bg-brand { background: #610C27; color: #fff; }

.card {
  border-radius: 16px;
}

.progress {
  height: 8px;
}
</style>
</head>

<body>

<div class="sidebar">
  <div class="sidebar-header">
    <h2 class="logo-text">Logistics</h2>
  </div>

  <a href="logi_dashboard.php" class="<?= $current_page == 'logi_dashboard.php' ? 'active' : '' ?>">
    <i class="fas fa-table-columns"></i><span class="text">Dashboard</span>
  </a>

  <a href="logi_orders.php" class="<?= $current_page == 'logi_orders.php' ? 'active' : '' ?>">
    <i class="fas fa-cart-shopping"></i><span class="text">Orders</span>
  </a>

  <a href="logi_tracking.php" class="<?= $current_page == 'logi_tracking.php' ? 'active' : '' ?>">
    <i class="fas fa-truck-fast"></i><span class="text">Tracking</span>
  </a>

  <a href="logi_reports.php" class="<?= $current_page == 'logi_reports.php' ? 'active' : '' ?>">
    <i class="fas fa-file-lines"></i><span class="text">Reports</span>
  </a>

  <a href="logi_settings.php">
    <i class="fas fa-gear"></i><span class="text">Settings</span>
  </a>
</div>

<div class="main-content">

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold text-brand">Logistics Reports</h2>
    <p class="text-muted mb-0">Analyze courier performance and delivery metrics.</p>
  </div>

  <div>
    <button class="btn btn-outline-dark me-2">
      <i class="fa fa-download"></i> Export CSV
    </button>
    <button class="btn bg-brand">
      <i class="fa fa-download"></i> Export PDF
    </button>
  </div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h5 class="fw-bold text-brand mb-4">Delivery Performance by Courier (%)</h5>
    <canvas id="chart"></canvas>
  </div>
</div>

<div class="row g-4">

  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header bg-light fw-bold text-brand">
        Courier Efficiency
      </div>

      <div class="table-responsive">
        <table class="table mb-0">
          <thead class="table-light">
            <tr>
              <th>Courier</th>
              <th>Total Deliveries</th>
              <th>On-Time Rate</th>
              <th>Avg Time</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="fw-bold">J&T Express</td>
              <td>12,450</td>
              <td class="text-success">95%</td>
              <td>2.4 days</td>
            </tr>
            <tr>
              <td class="fw-bold">LBC</td>
              <td>8,230</td>
              <td class="text-success">92%</td>
              <td>2.8 days</td>
            </tr>
            <tr>
              <td class="fw-bold">Ninja Van</td>
              <td>5,120</td>
              <td class="text-warning">88%</td>
              <td>3.1 days</td>
            </tr>
            <tr>
              <td class="fw-bold">Grab Express</td>
              <td>3,400</td>
              <td class="text-success">98%</td>
              <td>4.5 hours</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm p-4">
      <h5 class="fw-bold text-brand mb-4">Cost Analysis (Monthly)</h5>

      <div class="mb-4">
        <div class="d-flex justify-content-between mb-2">
          <span>Total Shipping Costs</span>
          <strong>₱452,000</strong>
        </div>
        <div class="progress">
          <div class="progress-bar bg-brand w-100"></div>
        </div>
      </div>

      <div class="mb-4">
        <div class="d-flex justify-content-between mb-2">
          <span>J&T Express (45%)</span>
          <span>₱203,400</span>
        </div>
        <div class="progress">
          <div class="progress-bar bg-brand" style="width:45%"></div>
        </div>
      </div>

      <div class="mb-4">
        <div class="d-flex justify-content-between mb-2">
          <span>LBC (30%)</span>
          <span>₱135,600</span>
        </div>
        <div class="progress">
          <div class="progress-bar bg-brand opacity-75" style="width:30%"></div>
        </div>
      </div>

      <div>
        <div class="d-flex justify-content-between mb-2">
          <span>Others (25%)</span>
          <span>₱113,000</span>
        </div>
        <div class="progress">
          <div class="progress-bar bg-brand opacity-50" style="width:25%"></div>
        </div>
      </div>

    </div>
  </div>

</div>

</div>

<script>
new Chart(document.getElementById('chart'), {
  type: 'bar',
  data: {
    labels: ['J&T Express', 'LBC', 'Ninja Van', 'Grab Express'],
    datasets: [
      {
        label: 'On Time',
        data: [95, 92, 88, 98],
        backgroundColor: '#610C27'
      },
      {
        label: 'Delayed',
        data: [5, 8, 12, 2],
        backgroundColor: '#E3C1B4'
      }
    ]
  },
  options: {
    responsive: true,
    scales: {
      x: { stacked: true },
      y: { stacked: true, beginAtZero: true }
    }
  }
});
</script>

</body>
</html>