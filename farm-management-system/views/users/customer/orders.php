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

$user_id = $_SESSION['user_id'];

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Build query for orders
$query = "
    SELECT 
        so.*,
        COUNT(soi.id) as item_count,
        SUM(soi.quantity * soi.unit_price) as order_total
    FROM sales_orders so
    LEFT JOIN sales_order_items soi ON so.id = soi.order_id
    WHERE so.user_id = ?
";

$params = [$user_id];
$types = "i";

if (!empty($status_filter)) {
    $query .= " AND so.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($date_from)) {
    $query .= " AND DATE(so.order_date) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $query .= " AND DATE(so.order_date) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND (so.customer_supplier LIKE ? OR so.order_type LIKE ? OR so.id LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$query .= " GROUP BY so.id ORDER BY so.order_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get order statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(total) as total_spent
    FROM sales_orders 
    WHERE user_id = ?
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$order_stats = $stats_stmt->get_result()->fetch_assoc();
$stats_stmt->close();

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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.03"><text x="50" y="50" font-size="80" text-anchor="middle" dominant-baseline="middle">📋</text></svg>');
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
        
        /* Stats Overview */
        .stats-overview {
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
        
        /* Search and Filters */
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .filters-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            margin-bottom: 0.5rem;
            color: var(--dark-brown);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control {
            padding: 10px 15px;
            border: 2px solid var(--forest-green);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--earth-brown);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: var(--forest-green);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--earth-brown);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: var(--sky-blue);
            color: var(--dark-brown);
        }
        
        /* Orders Table */
        .orders-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            color: var(--forest-green);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .results-count {
            color: var(--dark-brown);
            font-weight: 500;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th {
            background: var(--forest-green);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
        }
        
        .orders-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table tr:hover {
            background: #f9f9f9;
        }
        
        .order-id {
            font-weight: bold;
            color: var(--forest-green);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce7ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .view-btn { 
            background: var(--forest-green); 
            color: white; 
        }
        
        .track-btn { 
            background: var(--sky-blue); 
            color: var(--dark-brown); 
        }
        
        .cancel-btn { 
            background: #ff6b6b; 
            color: white; 
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .action-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        /* No Orders */
        .no-orders {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .no-orders-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        /* Order Type Badge */
        .order-type {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            background: var(--wheat);
            color: var(--dark-brown);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .filters-form {
                grid-template-columns: 1fr;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .orders-table {
                display: block;
                overflow-x: auto;
            }
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
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>📋</span>
                    <span>My Orders</span>
                </div>
                <a href="products.php" class="btn btn-primary">
                    <span>🐄</span>
                    <span>Continue Shopping</span>
                </a>
            </div>

            <!-- Order Statistics -->
            <div class="stats-overview">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-number"><?php echo $order_stats['total_orders'] ?? 0; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-number"><?php echo $order_stats['pending_orders'] ?? 0; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔄</div>
                    <div class="stat-number"><?php echo $order_stats['processing_orders'] ?? 0; ?></div>
                    <div class="stat-label">Processing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?php echo $order_stats['completed_orders'] ?? 0; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-number">KSh <?php echo number_format($order_stats['total_spent'] ?? 0, 2); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="filters-section">
                <form method="GET" action="" class="filters-form">
                    <div class="form-group">
                        <label class="form-label">🔍 Search Orders</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by ID, supplier, or type..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📊 Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📅 From Date</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📅 To Date</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary">
                            <span>🔍</span>
                            <span>Apply Filters</span>
                        </button>
                    </div>
                </form>
                
                <?php if (!empty($status_filter) || !empty($date_from) || !empty($date_to) || !empty($search)): ?>
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="orders.php" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.9rem;">
                        <span>🗑️</span>
                        <span>Clear All Filters</span>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Orders List -->
            <div class="orders-section">
                <div class="section-header">
                    <div class="section-title">
                        <span>📦</span>
                        <span>Order History</span>
                    </div>
                    <div class="results-count">
                        <?php echo count($orders); ?> orders found
                    </div>
                </div>

                <?php if (!empty($orders)): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Type</th>
                            <th>Supplier</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td class="order-id">#<?php echo $order['id']; ?></td>
                            <td>
                                <span class="order-type">
                                    <?php echo ucfirst($order['order_type']); ?>
                                </span>
                            </td>
                            <td><?php echo $order['customer_supplier']; ?></td>
                            <td><?php echo $order['item_count']; ?> items</td>
                            <td><strong>KSh <?php echo number_format($order['order_total'] ?? $order['total'], 2); ?></strong></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="action-btn view-btn">
                                        👁️ View
                                    </a>
                                    
                                    <?php if ($order['status'] === 'processing' || $order['status'] === 'shipped'): ?>
                                    <a href="track_order.php?id=<?php echo $order['id']; ?>" class="action-btn track-btn">
                                        🚚 Track
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($order['status'] === 'pending'): ?>
                                    <a href="cancel_order.php?id=<?php echo $order['id']; ?>" 
                                       class="action-btn cancel-btn"
                                       onclick="return confirm('Are you sure you want to cancel order #<?php echo $order['id']; ?>?')">
                                        ❌ Cancel
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-orders">
                    <div class="no-orders-icon">📦</div>
                    <h3>No Orders Found</h3>
                    <p>You haven't placed any orders yet, or no orders match your current filters.</p>
                    <p>Start shopping for farm animals in our <a href="products.php" style="color: var(--forest-green);">products catalog</a>.</p>
                </div>
                <?php endif; ?>
            </div>
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

            // Date validation
            const dateFrom = document.querySelector('input[name="date_from"]');
            const dateTo = document.querySelector('input[name="date_to"]');
            
            if (dateFrom && dateTo) {
                dateFrom.addEventListener('change', function() {
                    dateTo.min = this.value;
                });
                
                dateTo.addEventListener('change', function() {
                    dateFrom.max = this.value;
                });
            }

            // Auto-submit form when filters change (optional)
            const filters = document.querySelectorAll('select[name="status"]');
            filters.forEach(filter => {
                filter.addEventListener('change', function() {
                    this.form.submit();
                });
            });
        });

        // Print order functionality
        function printOrder(orderId) {
            window.open(`print_order.php?id=${orderId}`, '_blank');
        }
    </script>
</body>
</html>