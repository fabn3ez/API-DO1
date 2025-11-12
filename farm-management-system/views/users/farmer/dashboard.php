<?php
session_start();
require_once '../../auth/check_role.php';
check_role('admin');

// Database connection
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get admin stats
$total_animals = $conn->query("SELECT SUM(number) as total FROM animals")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0;
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM sales_orders WHERE status='pending'")->fetch_assoc()['count'] ?? 0;
$recent_transactions = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'] ?? 0;

// Get data for charts
// Animal distribution by type
$animal_distribution = $conn->query("
    SELECT type, SUM(number) as total 
    FROM animals 
    GROUP BY type
");

// Revenue data (last 7 days)
$revenue_data = $conn->query("
    SELECT DATE(created_at) as date, 
           SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as income,
           SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as expense
    FROM transactions 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
");

// User roles distribution
$user_roles = $conn->query("
    SELECT role, COUNT(*) as count 
    FROM users 
    GROUP BY role
");

// Recent activities
$recent_activities = $conn->query("
    (SELECT 'animal' as type, CONCAT('Added ', number, ' ', breed, ' ', type, ' to Shed ', shed_no) as activity, created_at as timestamp
     FROM animals 
     ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'order' as type, CONCAT('New ', order_type, ' order from ', customer_supplier, ' - $', total) as activity, created_at as timestamp
     FROM sales_orders 
     ORDER BY created_at DESC LIMIT 3)
    UNION ALL
    (SELECT 'transaction' as type, CONCAT(transaction_type, ' of $', amount, ' - ', description) as activity, created_at as timestamp
     FROM transactions 
     ORDER BY created_at DESC LIMIT 3)
    ORDER BY timestamp DESC 
    LIMIT 5
");

// Monthly revenue trend
$monthly_revenue = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE 0 END) as income,
        SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as expense
    FROM transactions 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");

$conn->close();

// Prepare chart data
$animal_labels = [];
$animal_data = [];
while($row = $animal_distribution->fetch_assoc()) {
    $animal_labels[] = $row['type'];
    $animal_data[] = $row['total'];
}

$revenue_labels = [];
$income_data = [];
$expense_data = [];
while($row = $revenue_data->fetch_assoc()) {
    $revenue_labels[] = date('M j', strtotime($row['date']));
    $income_data[] = floatval($row['income']);
    $expense_data[] = floatval($row['expense']);
}

$user_labels = [];
$user_data = [];
while($row = $user_roles->fetch_assoc()) {
    $user_labels[] = ucfirst($row['role']);
    $user_data[] = $row['count'];
}

$monthly_labels = [];
$monthly_income = [];
$monthly_expense = [];
while($row = $monthly_revenue->fetch_assoc()) {
    $monthly_labels[] = date('M Y', strtotime($row['month']));
    $monthly_income[] = floatval($row['income']);
    $monthly_expense[] = floatval($row['expense']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Farm Management System</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Farm Theme Styles - Same as before */
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.03"><text x="50" y="50" font-size="80" text-anchor="middle" dominant-baseline="middle">🌾</text></svg>');
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
        
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .chart-container {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .chart-title {
            margin-bottom: 1rem;
            color: var(--forest-green);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .chart-canvas {
            width: 100% !important;
            height: 250px !important;
        }
        
        .activity-feed {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            font-size: 1.2rem;
            width: 30px;
            text-align: center;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-time {
            color: #666;
            font-size: 0.8rem;
            margin-left: auto;
        }
        
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
        
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
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
            <span>👋 Welcome, <?php echo $_SESSION['username']; ?> (Admin)</span>
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
            <a href="animals.php" class="nav-item">
                <span>🐄</span>
                <span>Animals Management</span>
            </a>
            <a href="inventory.php" class="nav-item">
                <span>📦</span>
                <span>Inventory</span>
            </a>
            <a href="financial.php" class="nav-item">
                <span>💰</span>
                <span>Financial</span>
            </a>
            <a href="users.php" class="nav-item">
                <span>👥</span>
                <span>User Management</span>
            </a>
            <a href="reports.php" class="nav-item">
                <span>📈</span>
                <span>Reports</span>
            </a>
            <a href="settings.php" class="nav-item">
                <span>⚙️</span>
                <span>Settings</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h1>🌾 WELCOME TO FARM ADMIN DASHBOARD 🌾</h1>
                <p>Manage your farm operations efficiently</p>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="animals.php" class="action-btn">
                    <span class="action-icon">🐄</span>
                    <span>Manage Animals</span>
                </a>
                <a href="users.php" class="action-btn">
                    <span class="action-icon">👥</span>
                    <span>Manage Users</span>
                </a>
                <a href="financial.php" class="action-btn">
                    <span class="action-icon">💰</span>
                    <span>View Financials</span>
                </a>
                <a href="reports.php" class="action-btn">
                    <span class="action-icon">📈</span>
                    <span>Generate Reports</span>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🐄</div>
                    <div class="stat-number"><?php echo number_format($total_animals); ?></div>
                    <div class="stat-label">Total Animals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-number"><?php echo $pending_orders; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-number"><?php echo $recent_transactions; ?></div>
                    <div class="stat-label">Today's Transactions</div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="chart-title">
                        <span>🗺️</span>
                        <span>ANIMAL DISTRIBUTION</span>
                    </div>
                    <canvas class="chart-canvas" id="animalChart"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-title">
                        <span>👥</span>
                        <span>USER ROLES DISTRIBUTION</span>
                    </div>
                    <canvas class="chart-canvas" id="userChart"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-title">
                        <span>💰</span>
                        <span>REVENUE TREND (LAST 7 DAYS)</span>
                    </div>
                    <canvas class="chart-canvas" id="revenueChart"></canvas>
                </div>
                <div class="chart-container">
                    <div class="chart-title">
                        <span>📈</span>
                        <span>MONTHLY REVENUE TREND</span>
                    </div>
                    <canvas class="chart-canvas" id="monthlyRevenueChart"></canvas>
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="activity-feed">
                <div class="chart-title">
                    <span>🌾</span>
                    <span>RECENT FARM ACTIVITIES</span>
                </div>
                <?php if ($recent_activities && $recent_activities->num_rows > 0): ?>
                    <?php while($activity = $recent_activities->fetch_assoc()): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?php 
                                switch($activity['type']) {
                                    case 'animal': echo '🐄'; break;
                                    case 'order': echo '📋'; break;
                                    case 'transaction': echo '💰'; break;
                                    default: echo '🌾';
                                }
                                ?>
                            </div>
                            <div class="activity-content"><?php echo htmlspecialchars($activity['activity']); ?></div>
                            <div class="activity-time"><?php echo date('M j, g:i A', strtotime($activity['timestamp'])); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="activity-item">
                        <div class="activity-content">No recent activities found</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animal Distribution Chart
            const animalCtx = document.getElementById('animalChart').getContext('2d');
            new Chart(animalCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($animal_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($animal_data); ?>,
                        backgroundColor: [
                            '#228B22', '#8B4513', '#87CEEB', '#FFD700', '#FF6347', '#9370DB'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // User Roles Chart
            const userCtx = document.getElementById('userChart').getContext('2d');
            new Chart(userCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($user_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($user_data); ?>,
                        backgroundColor: [
                            '#228B22', '#8B4513', '#87CEEB', '#FFD700'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Revenue Trend Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($revenue_labels); ?>,
                    datasets: [
                        {
                            label: 'Income',
                            data: <?php echo json_encode($income_data); ?>,
                            borderColor: '#228B22',
                            backgroundColor: 'rgba(34, 139, 34, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Expense',
                            data: <?php echo json_encode($expense_data); ?>,
                            borderColor: '#8B4513',
                            backgroundColor: 'rgba(139, 69, 19, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Monthly Revenue Chart
            const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($monthly_labels); ?>,
                    datasets: [
                        {
                            label: 'Income',
                            data: <?php echo json_encode($monthly_income); ?>,
                            backgroundColor: 'rgba(34, 139, 34, 0.8)',
                            borderColor: '#228B22',
                            borderWidth: 1
                        },
                        {
                            label: 'Expense',
                            data: <?php echo json_encode($monthly_expense); ?>,
                            backgroundColor: 'rgba(139, 69, 19, 0.8)',
                            borderColor: '#8B4513',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Navigation
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