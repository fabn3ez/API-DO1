<?php
require_once __DIR__ . '/../../controllers/AdminController.php';

$controller = new AdminController();
$stats = $controller->getDashboardStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f3f6fa;
      margin: 0;
      padding: 0;
    }
    .dashboard-container {
      max-width: 900px;
      margin: 50px auto;
      background: white;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      color: #333;
    }
    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    .card {
      background: #0078d7;
      color: white;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
    }
    .card h3 {
      margin: 0;
      font-size: 20px;
    }
    .card p {
      font-size: 28px;
      font-weight: bold;
      margin: 10px 0 0;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <h1>Admin Dashboard</h1>

    <?php if (isset($stats['error'])): ?>
      <p style="color:red; text-align:center;">Error fetching stats: <?= $stats['error'] ?></p>
    <?php else: ?>
      <div class="stats">
        <div class="card">
          <h3>Total Farmers</h3>
          <p><?= $stats['total_farmers'] ?></p>
        </div>
        <div class="card">
          <h3>Total Crops</h3>
          <p><?= $stats['total_crops'] ?></p>
        </div>
        <div class="card">
          <h3>Total Livestock</h3>
          <p><?= $stats['total_livestock'] ?></p>
        </div>
        <div class="card">
          <h3>Total Sales</h3>
          <p><?= $stats['total_sales'] ?></p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
