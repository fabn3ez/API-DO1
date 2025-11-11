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

// Get customer's orders
$user_id = $_SESSION['user_id'];
$orders_query = "
    SELECT so.*, 
           COUNT(soi.id) as item_count,
           SUM(soi.quantity * soi.unit_price) as total_amount
    FROM sales_orders so
    LEFT JOIN sales_order_items soi ON so.id = soi.order_id
    WHERE so.user_id = ?
    GROUP BY so.id
    ORDER BY so.created_at DESC
";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Farm Management System</title>
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.03"><text x="50" y="50" font-size="80" text-anchor="middle" dominant-baseline="middle">🌾</text></svg>');
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--forest-green);
        }

        .page-title {
            color: var(--forest-green);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
        }

        /* Orders Grid */
        .orders-grid {
            display: grid;
            gap: 1.5rem;
        }

        .order-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 5px solid var(--forest-green);
            transition: transform 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .order-info h3 {
            color: var(--forest-green);
            margin-bottom: 0.5rem;
        }

        .order-meta {
            color: #666;
            font-size: 0.9rem;
        }

        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            text-align: center;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: bold;
            color: var(--dark-brown);
        }

        .order-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .view-btn { background: var(--forest-green); color: white; }
        .track-btn { background: var(--sky-blue); color: var(--dark-brown); }
        .cancel-btn { background: #ff4444; color: white; }
        .review-btn { background: #ffc107; color: var(--dark-brown); }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            color: var(--forest-green);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .btn-primary {
            background: var(--forest-green);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--earth-brown);
            transform: translateY(-2px);
        }

        /* Filter Section */
        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 12px;
            border: 2px solid var(--forest-green);
            border-radius: 8px;
            background: white;
            color: var(--dark-brown);
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
            <span>👋 Welcome, <?php echo $_SESSION['username']; ?> (Customer)</span>
            <span>🔔</span>
            <a href="../../auth/logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </div>

    <!-- Main Container -->
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
            <a href="orders.php" class="nav-item active">
                <span>📦</span>
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
            <a href="profile.php" class="nav-item">
                <span>👤</span>
                <span>My Profile</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>📦</span>
                    <span>My Orders</span>
                </div>
                <a href="products.php" class="btn-primary">
                    <span>🐄</span>
                    <span>Continue Shopping</span>
                </a>
            </div>

            <!-- Filters -->
            <div class="filters">
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                
                <select class="filter-select" id="timeFilter">
                    <option value="all">All Time</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="quarter">Last 3 Months</option>
                    <option value="year">This Year</option>
                </select>
            </div>

            <!-- Orders List -->
            <div class="orders-grid">
                <?php if (empty($orders)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <h3>No Orders Yet</h3>
                        <p>You haven't placed any orders. Start shopping to see your orders here!</p>
                        <a href="products.php" class="btn-primary">
                            <span>🐄</span>
                            <span>Browse Animals</span>
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card" data-status="<?php echo strtolower($order['status']); ?>">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Order #<?php echo $order['id']; ?></h3>
                                    <div class="order-meta">
                                        Placed on <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                        • <?php echo $order['item_count']; ?> items
                                    </div>
                                </div>
                                <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </div>
                            </div>

                            <div class="order-details">
                                <div class="detail-item">
                                    <div class="detail-label">Total Amount</div>
                                    <div class="detail-value">$<?php echo number_format($order['total_amount'], 2); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Items</div>
                                    <div class="detail-value"><?php echo $order['item_count']; ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Order Date</div>
                                    <div class="detail-value"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Last Updated</div>
                                    <div class="detail-value"><?php echo date('M j, Y', strtotime($order['updated_at'] ?? $order['created_at'])); ?></div>
                                </div>
                            </div>

                            <div class="order-actions">
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="action-btn view-btn">
                                    <span>👁️</span>
                                    <span>View Details</span>
                                </a>
                                <a href="track_order.php?id=<?php echo $order['id']; ?>" class="action-btn track-btn">
                                    <span>🚚</span>
                                    <span>Track Order</span>
                                </a>
                                
                                <?php if ($order['status'] === 'pending' || $order['status'] === 'confirmed'): ?>
                                    <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="action-btn cancel-btn" 
                                       onclick="return confirm('Are you sure you want to cancel this order?')">
                                        <span>❌</span>
                                        <span>Cancel Order</span>
                                    </a>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'delivered'): ?>
                                    <a href="write_review.php?order_id=<?php echo $order['id']; ?>" class="action-btn review-btn">
                                        <span>⭐</span>
                                        <span>Write Review</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const timeFilter = document.getElementById('timeFilter');
            const orderCards = document.querySelectorAll('.order-card');

            function filterOrders() {
                const statusValue = statusFilter.value;
                const timeValue = timeFilter.value;
                
                orderCards.forEach(card => {
                    let showCard = true;
                    
                    // Status filter
                    if (statusValue !== 'all') {
                        const cardStatus = card.getAttribute('data-status');
                        if (cardStatus !== statusValue) {
                            showCard = false;
                        }
                    }
                    
                    // Time filter (this would need more complex date logic in a real app)
                    if (timeValue !== 'all' && showCard) {
                        // For demo purposes, we'll just show all
                        // In real implementation, you'd filter by date ranges
                    }
                    
                    card.style.display = showCard ? 'block' : 'none';
                });
            }

            statusFilter.addEventListener('change', filterOrders);
            timeFilter.addEventListener('change', filterOrders);

            // Navigation active state
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