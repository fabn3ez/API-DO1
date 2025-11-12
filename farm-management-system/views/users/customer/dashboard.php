<?php
session_start();
require_once '../../auth/check_role.php';
check_role('customer');

// Database connection
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Get customer-specific data
$user_id = $_SESSION['user_id'];

// Get customer profile
$customer_query = $conn->prepare("
    SELECT c.*, u.username, u.email 
    FROM customers c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.user_id = ?
");
$customer_query->bind_param("i", $user_id);
$customer_query->execute();
$customer = $customer_query->get_result()->fetch_assoc();
$customer_query->close();

// Get recent orders
$orders_query = $conn->prepare("
    SELECT id, order_type, customer_supplier, total, order_date, status 
    FROM sales_orders 
    WHERE user_id = ? 
    ORDER BY order_date DESC 
    LIMIT 5
");
$orders_query->bind_param("i", $user_id);
$orders_query->execute();
$recent_orders = $orders_query->get_result()->fetch_all(MYSQLI_ASSOC);
$orders_query->close();

// Get order stats
$order_stats_query = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(total) as total_spent
    FROM sales_orders 
    WHERE user_id = ?
");
$order_stats_query->bind_param("i", $user_id);
$order_stats_query->execute();
$order_stats = $order_stats_query->get_result()->fetch_assoc();
$order_stats_query->close();

// Get available animals count
$animals_count = $conn->query("SELECT COUNT(*) as count FROM animals")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Farm Management System</title>
    <style>
        /* Farm Theme Styles */
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
        
        /* Header Styles */
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
        
        /* Main Layout */
        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        
        /* Sidebar Styles */
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
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.03"><text x="50" y="50" font-size="80" text-anchor="middle" dominant-baseline="middle">🛒</text></svg>');
        }
        
        .welcome-banner {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--sky-blue), var(--forest-green));
            border-radius: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 5px solid var(--forest-green);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--forest-green);
        }
        
        .stat-label {
            color: var(--dark-brown);
            font-size: 0.9rem;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .action-btn {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: var(--dark-brown);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .action-btn:hover {
            border-color: var(--forest-green);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        /* Recent Orders */
        .recent-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section-title {
            margin-bottom: 1rem;
            color: var(--forest-green);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th {
            background: var(--forest-green);
            color: white;
            padding: 12px 15px;
            text-align: left;
        }
        
        .orders-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table tr:hover {
            background: #f9f9f9;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        /* Customer Profile */
        .profile-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .profile-item {
            padding: 1rem;
            background: var(--wheat);
            border-radius: 8px;
            border-left: 4px solid var(--forest-green);
        }
        
        .profile-label {
            font-weight: 600;
            color: var(--dark-brown);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .profile-value {
            font-size: 1.1rem;
            color: var(--forest-green);
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
            <span>👋 Welcome, <?php echo $_SESSION['username']; ?> </span>
            <span>🔔</span>
            <a href="../../auth/logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="dashboard.php" class="nav-item active">
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
            <a href="wishlist.php" class="nav-item">
                <span>❤️</span>
                <span>Wishlist</span>
            </a>
            <!-- <a href="profile.php" class="nav-item">
                <span>👤</span>
                <span>My Profile</span>
            </a> -->
            <a href="support.php" class="nav-item">
                <span>📞</span>
                <span>Support</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h1>🛒 WELCOME BACK, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                <p>Manage your purchases and farm animal orders</p>
                <?php if ($customer): ?>
                <p><strong>Customer Since:</strong> <?php echo date('F Y', strtotime($customer['customer_since'])); ?></p>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="products.php" class="action-btn">
                    <span class="action-icon">🐄</span>
                    <span>Browse Animals</span>
                </a>
                <a href="orders.php" class="action-btn">
                    <span class="action-icon">📋</span>
                    <span>View Orders</span>
                </a>
                <a href="cart.php" class="action-btn">
                    <span class="action-icon">🛒</span>
                    <span>Shopping Cart</span>
                </a>
                <!-- <a href="profile.php" class="action-btn">
                    <span class="action-icon">👤</span>
                    <span>My Profile</span>
                </a> -->
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-number"><?php echo $order_stats['total_orders'] ?? 0; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?php echo $order_stats['completed_orders'] ?? 0; ?></div>
                    <div class="stat-label">Completed Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-number"><?php echo $order_stats['pending_orders'] ?? 0; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-number">KSh <?php echo number_format($order_stats['total_spent'] ?? 0, 2); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="recent-section">
                <div class="section-title">
                    <span>📋</span>
                    <span>RECENT ORDERS</span>
                    <a href="orders.php" style="margin-left: auto; font-size: 0.9rem; color: var(--forest-green); text-decoration: none;">
                        View All →
                    </a>
                </div>
                <?php if (!empty($recent_orders)): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Type</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo ucfirst($order['order_type']); ?></td>
                            <td><?php echo $order['customer_supplier']; ?></td>
                            <td>KSh <?php echo number_format($order['total'], 2); ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align: center; color: #666; padding: 2rem;">No orders yet. <a href="products.php">Start shopping!</a></p>
                <?php endif; ?>
            </div>

            <!-- Customer Profile -->
            <?php if ($customer): ?>
            <div class="profile-section">
                <div class="section-title">
                    <span>👤</span>
                    <span>MY PROFILE</span>
                </div>
                <div class="profile-grid">
                    <div class="profile-item">
                        <div class="profile-label">📧 Email</div>
                        <div class="profile-value"><?php echo $customer['email']; ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">📞 Phone</div>
                        <div class="profile-value"><?php echo $customer['phone_number'] ?? 'Not set'; ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">🏠 Address</div>
                        <div class="profile-value"><?php echo $customer['city'] ?? 'Not set'; ?>, <?php echo $customer['state'] ?? ''; ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">💳 Payment Method</div>
                        <div class="profile-value"><?php echo ucfirst($customer['preferred_payment_method'] ?? 'Not set'); ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">🏢 Customer Type</div>
                        <div class="profile-value"><?php echo ucfirst($customer['customer_type'] ?? 'individual'); ?></div>
                    </div>
                    <div class="profile-item">
                        <div class="profile-label">⭐ Loyalty Points</div>
                        <div class="profile-value"><?php echo $customer['loyalty_points'] ?? 0; ?> points</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>