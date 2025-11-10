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

// Get admin stats
$total_animals = $conn->query("SELECT SUM(number) as total FROM animals")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM sales_orders WHERE status='pending'")->fetch_assoc()['count'];
$recent_transactions = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Farm Management System</title>
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
                <a href="inventory.php" class="action-btn">
                    <span class="action-icon">📦</span>
                    <span>View Inventory</span>
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
                    <div style="height: 200px; background: #f9f9f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666;">
                        [Animal Distribution Chart]<br>
                        Cattle 🐄 | Poultry 🐔 | Goats 🐐
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-title">
                        <span>📈</span>
                        <span>FARM ACTIVITY</span>
                    </div>
                    <div style="height: 200px; background: #f9f9f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666;">
                        [Activity Chart Placeholder]
                    </div>
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="activity-feed">
                <div class="chart-title">
                    <span>🌾</span>
                    <span>RECENT FARM ACTIVITIES</span>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">🐄</div>
                    <div class="activity-content">650 Jersey cattle added to Shed 5</div>
                    <div class="activity-time">2 hours ago</div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">🐔</div>
                    <div class="activity-content">900 Rhode Island Red chickens recorded</div>
                    <div class="activity-time">4 hours ago</div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">👥</div>
                    <div class="activity-content">New farmer registration: John Doe</div>
                    <div class="activity-time">6 hours ago</div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">💰</div>
                    <div class="activity-content">Monthly financial report generated</div>
                    <div class="activity-time">1 day ago</div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon">📦</div>
                    <div class="activity-content">Inventory restocked: Animal Feed</div>
                    <div class="activity-time">2 days ago</div>
                </div>
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
            
            setInterval(() => {
                const stats = document.querySelectorAll('.stat-number');
                if (stats[0]) {
                    const current = parseInt(stats[0].textContent.replace(',', ''));
                    stats[0].textContent = (current + Math.floor(Math.random() * 3)).toLocaleString();
                }
            }, 10000);
        });
    </script>
</body>
</html>