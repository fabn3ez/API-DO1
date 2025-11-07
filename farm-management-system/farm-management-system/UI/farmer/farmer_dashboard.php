<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Farmer Dashboard | Farm Management System</title>
    <link rel="stylesheet" href="C:\Apache24\htdocs\API-DO1\farm-management-system\UI\farmer\style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="farmer-body">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2 class="logo">🌿 Farmer</h2>
            <nav>
                <a href="#" class="active">Dashboard</a>
                <a href="#">My Products</a>
                <a href="#">Add Product</a>
                <a href="#">Notifications</a>
                <a href="../index.php">Logout</a>
            </nav>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <header>
                <h1>Welcome, Samuel 👨‍🌾</h1>
                <p>Here’s an overview of your farm today.</p>
            </header>

            <section class="cards">
                <div class="card">
                    <h3>🌾 Total Products</h3>
                    <p>12</p>
                </div>
                <div class="card">
                    <h3>💰 Total Sales</h3>
                    <p>Ksh 25,400</p>
                </div>
                <div class="card">
                    <h3>📈 Product Growth</h3>
                    <p>+15% this week</p>
                </div>
            </section>

            <section class="notifications">
                <h2>Notifications</h2>
                <ul>
                    <li>New order for 50kg of maize 🌽</li>
                    <li>Soil moisture level is optimal ✅</li>
                    <li>Upcoming farm inspection next week 🧾</li>
                </ul>
            </section>

            <section class="add-product">
                <h2>Add New Product</h2>
                <form id="productForm">
                    <input type="text" id="productName" placeholder="Product Name" required>
                    <input type="number" id="productQty" placeholder="Quantity (kg)" required>
                    <button type="submit">Add Product</button>
                </form>
                <p class="note">⚠️ You can only add limited products.</p>
            </section>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>

</html>