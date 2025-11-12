
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$wishlist_query = $conn->prepare('SELECT w.id as wishlist_id, a.* FROM wishlist w JOIN animals a ON w.animal_id = a.id WHERE w.user_id = ? ORDER BY w.added_at DESC');
$wishlist_query->bind_param('i', $user_id);
$wishlist_query->execute();
$wishlist_result = $wishlist_query->get_result();
$wishlist = $wishlist_result->fetch_all(MYSQLI_ASSOC);
$wishlist_query->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - Farm Management System</title>
    <style>
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: var(--cream-white);
            color: var(--dark-brown);
        }
        .header {
            background: linear-gradient(to right, var(--forest-green), var(--earth-brown));
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        .sidebar {
            width: 250px;
            background: var(--wheat);
            padding: 2rem 1rem;
            border-right: 3px solid var(--earth-brown);
        }
        .nav-item {
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            text-decoration: none;
            color: var(--dark-brown);
        }
        .nav-item:hover, .nav-item.active {
            background-color: var(--forest-green);
            color: white;
            transform: translateX(5px);
        }
        .main-content {
            flex: 1;
            padding: 2rem;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.03"><text x="50" y="50" font-size="80" text-anchor="middle" dominant-baseline="middle">🛒</text></svg>');
        }
        .wishlist-section {
            background: white;
            padding: 2rem 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, 0.10);
            max-width: 700px;
            margin: 2rem auto;
        }
        .wishlist-title {
            font-size: 2rem;
            color: var(--forest-green);
            margin-bottom: 1rem;
            font-weight: 700;
            text-align: center;
        }
        .wishlist-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        .wishlist-table th {
            background: var(--forest-green);
            color: white;
            padding: 12px 15px;
            text-align: left;
        }
        .wishlist-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        .wishlist-table tr:hover {
            background: #f9f9f9;
        }
        .remove-btn {
            background: var(--earth-brown);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 6px 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .remove-btn:hover {
            background: #5d2e0f;
        }
        .empty-message {
            text-align: center;
            color: #666;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <span>🚜</span>
            <span>FARM MANAGEMENT SYSTEM</span>
        </div>
        <div class="user-menu">
            <span>👋 Welcome, Customer</span>
            <span>🔔</span>
            <a href="../../auth/logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </div>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="dashboard.php" class="nav-item">
                <span>📊</span>
                <span>Dashboard</span>
            </a>
            <a href="products.php" class="nav-item">
                <span>🐄</span>
                <span>Browse Animals</span>
            </a>
            <a href="orders.php" class="nav-item">
                <span>📋</span>
                <span>My Orders</span>
            </a>
            <a href="cart.php" class="nav-item">
                <span>🛒</span>
                <span>Shopping Cart</span>
            </a>
            <a href="wishlist.php" class="nav-item active">
                <span>❤️</span>
                <span>Wishlist</span>
            </a>
            <a href="profile.php" class="nav-item">
                <span>👤</span>
                <span>My Profile</span>
            </a>
            <a href="support.php" class="nav-item">
                <span>📞</span>
                <span>Support</span>
            </a>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="wishlist-section">
                <div class="wishlist-title">❤️ My Wishlist</div>
                <?php if (!empty($wishlist)): ?>
                <table class="wishlist-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Breed</th>
                            <th>Gender</th>
                            <th>Available</th>
                            <th>Avg Weight</th>
                            <th>Shed</th>
                            
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wishlist as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['type']); ?></td>
                            <td><?php echo htmlspecialchars($item['breed']); ?></td>
                            <td><?php echo htmlspecialchars($item['gender']); ?></td>
                            <td><?php echo htmlspecialchars($item['number']); ?></td>
                            <td><?php echo htmlspecialchars($item['avg_weight']); ?></td>
                            <td><?php echo htmlspecialchars($item['shed_no']); ?></td>

                            <td>
                                <form method="POST" action="remove_from_wishlist.php" style="margin:0;">
                                    <input type="hidden" name="wishlist_id" value="<?php echo $item['wishlist_id']; ?>">
                                    <button type="submit" class="remove-btn" onclick="return confirm('Remove this animal from your wishlist?');">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-message">Your wishlist is empty. <a href="products.php" style="color: var(--forest-green); text-decoration: underline;">Browse animals</a> to add items!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
