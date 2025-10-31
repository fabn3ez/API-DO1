<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

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
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #0078d7;
      color: white;
      padding: 15px 30px;
    }
    .logout-btn {
      background: crimson;
      color: white;
      padding: 8px 15px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }
    .logout-btn:hover {
      background: darkred;
    }
    .dashboard-container {
      max-width: 900px;
      margin: 40px auto;
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
  <header class="topbar">
    <div>🌿 <strong>FarmManager Admin</strong></div>
    <a href="../auth/logout.php" class="logout-btn">Logout</a>
  </header>

  <div class="dashboard-container">
    <h1>Admin Dashboard</h1>

    <?php if (isset($stats['error'])): ?>
      <p style="color:red; text-align:center;">Error fetching stats: <?= $stats['error'] ?></p>
    <?php else: ?>
      <div class="stats">
        <div class="card"><h3>Total Farmers</h3><p><?= $stats['total_farmers'] ?></p></div>
        <div class="card"><h3>Total Crops</h3><p><?= $stats['total_crops'] ?></p></div>
        <div class="card"><h3>Total Livestock</h3><p><?= $stats['total_livestock'] ?></p></div>
        <div class="card"><h3>Total Sales</h3><p><?= $stats['total_sales'] ?></p></div>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
