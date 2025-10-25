<?php
require_once __DIR__ . '/../../controllers/FarmerController.php';

$controller = new FarmerController();
$stats = $controller->getStats();
$trend = $controller->getProductionTrend();
$livestock = $controller->getLivestockDistribution();
$orders = $controller->getRecentOrders();
$activity = $controller->getRecentActivity();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Farmer Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../UI/assets/css/farmer.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <aside class="sidebar">
    <div class="brand">🌿 FarmManager</div>
    <nav>
      <a class="active" href="#">Dashboard</a>
      <a id="navProducts" href="#">Products</a>
      <a id="navSales" href="#">Sales</a>
      <a id="navCrops" href="#">Crops</a>
      <a id="navLivestock" href="#">Livestock</a>
      <a id="navNotifications" href="#">Notifications</a>
    </nav>
    <div class="user">Signed in: <strong id="userName">Farmer</strong></div>
  </aside>

  <main class="main">
    <header class="topbar">
      <h1 id="greeting">Welcome, Farmer</h1>
      <div>
        <button id="refreshBtn">Refresh</button>
      </div>
    </header>

    <!-- 📊 Dashboard Stats -->
    <section class="cards">
      <div class="card">
        <h4>Total Products</h4>
        <p><?php echo $stats['total_products'] ?? 0; ?></p>
      </div>
      <div class="card">
        <h4>Inventory Value</h4>
        <p>Ksh <?php echo number_format($stats['inventory_value'] ?? 0, 2); ?></p>
      </div>
      <div class="card">
        <h4>Total Sales</h4>
        <p>Ksh <?php echo number_format($stats['total_sales'] ?? 0, 2); ?></p>
      </div>
    </section>

    <!-- 📈 Charts -->
    <section class="content-grid">
      <div class="panel">
        <h3>Production Trend</h3>
        <canvas id="productionTrendChart" height="200"></canvas>
      </div>
      <div class="panel">
        <h3>Livestock Distribution</h3>
        <canvas id="livestockDistributionChart" height="200"></canvas>
      </div>
    </section>

    <!-- 🧾 Latest Orders -->
    <section class="table-panel">
      <h3>Latest Orders</h3>
      <?php if (!empty($orders)): ?>
        <table class="data-table">
          <thead>
            <tr><th>#</th><th>Product</th><th>Qty</th><th>Amount</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo $order['product_name']; ?></td>
                <td><?php echo $order['quantity']; ?></td>
                <td>Ksh <?php echo number_format($order['amount'], 2); ?></td>
                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No recent orders.</p>
      <?php endif; ?>
    </section>

    <!-- 🕓 Recent Activity -->
    <section class="table-panel">
      <h3>Recent Activity</h3>
      <?php if (!empty($activity)): ?>
        <ul>
          <?php foreach ($activity as $act): ?>
            <li>🔔 <?php echo htmlspecialchars($act['action'] . ' - ' . $act['details']); ?> 
                (<?php echo date('d M Y H:i', strtotime($act['created_at'])); ?>)</li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>No recent activity.</p>
      <?php endif; ?>
    </section>
  </main>

  <!-- Pass PHP data to JS -->
  <script>
    const trendData = <?php echo json_encode($trend); ?>;
    const livestockData = <?php echo json_encode($livestock); ?>;
  </script>

  <script src="../../UI/assets/js/farmer_dashboard.js"></script>
</body>
</html>
