<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farmer Dashboard | Farm Management System</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="sidebar">
    <h2>🌿 Farmer Panel</h2>
    <ul>
      <li class="active">Dashboard</li>
      <li>Add Products</li>
      <li>View Sales</li>
      <li>Notifications</li>
      <li>Logout</li>
    </ul>
  </div>

  <div class="main-content">
    <header>
      <h1>Welcome, Farmer 👩‍🌾</h1>
      <p>Manage your farm operations efficiently and sustainably.</p>
    </header>

    <section class="cards">
      <div class="card">
        <h3>Farm Overview</h3>
        <p>View and update farm details. You can only update once.</p>
        <button id="updateFarmBtn">Update Farm</button>
      </div>

      <div class="card">
        <h3>Product Management</h3>
        <p>Add a limited number of products to your farm inventory.</p>
        <button id="addProductBtn">Add Product</button>
      </div>

      <div class="card">
        <h3>Sales Overview</h3>
        <p>Check your farm’s sales and performance trends.</p>
        <button id="viewSalesBtn">View Sales</button>
      </div>

      <div class="card">
        <h3>Notifications</h3>
        <p>See important farm alerts and updates.</p>
        <button id="viewNotificationsBtn">View Notifications</button>
      </div>
    </section>

    <footer>
      <p>&copy; 2025 Farm Management System | Built for Modern Farmers 🌱</p>
    </footer>
  </div>

  <script src="assets/js/script.js"></script>
</body>
</html>
