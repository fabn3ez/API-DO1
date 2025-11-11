<?php
session_start();
require_once '../../auth/check_role.php';
check_role('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Farm Management System</title>
    <style>
        /* Farm Theme Styles - Same as dashboard */
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
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--sky-blue), var(--forest-green));
            border-radius: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .page-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .action-btn {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            color: var(--dark-brown);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .action-btn:hover {
            border-color: var(--forest-green);
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
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
        
        .chart-placeholder {
            height: 300px;
            background: #f9f9f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            border: 2px dashed #ddd;
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
            <a href="dashboard.php" class="nav-item">
                <span>📊</span>
                <span>Dashboard</span>
            </a>
            <a href="animals.php" class="nav-item">
                <span>🐄</span>
                <span>Animals Management</span>
            </a>
            <a href="users.php" class="nav-item">
                <span>👥</span>
                <span>User Management</span>
            </a>
            <a href="inventory.php" class="nav-item">
                <span>📦</span>
                <span>Inventory</span>
            </a>
            <a href="financial.php" class="nav-item">
                <span>💰</span>
                <span>Financial</span>
            </a>
            <a href="reports.php" class="nav-item active">
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
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>📊</span>
                    <span>Reports & Analytics</span>
                </div>
            </div>

            <!-- Report Cards -->
            <div class="quick-actions">
                <a href="animal_report.php" class="action-btn">
                    <span class="action-icon">🐄</span>
                    <span>Animal Report</span>
                </a>
                <a href="financial_report.php" class="action-btn">
                    <span class="action-icon">💰</span>
                    <span>Financial Report</span>
                </a>
                <a href="inventory_report.php" class="action-btn">
                    <span class="action-icon">📦</span>
                    <span>Inventory Report</span>
                </a>
                <a href="user_report.php" class="action-btn">
                    <span class="action-icon">👥</span>
                    <span>User Report</span>
                </a>
                <a href="production_report.php" class="action-btn">
                    <span class="action-icon">🥛</span>
                    <span>Production Report</span>
                </a>
                <a href="sales_report.php" class="action-btn">
                    <span class="action-icon">🛒</span>
                    <span>Sales Report</span>
                </a>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="chart-title">
                        <span>📈</span>
                        <span>ANIMAL POPULATION TREND</span>
                    </div>
                    <div class="chart-placeholder">
                        [Animal Population Chart]<br>
                        Line chart showing population changes over time
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-title">
                        <span>💰</span>
                        <span>REVENUE TREND</span>
                    </div>
                    <div class="chart-placeholder">
                        [Revenue Trend Chart]<br>
                        Bar chart showing monthly revenue
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-title">
                        <span>📦</span>
                        <span>INVENTORY LEVELS</span>
                    </div>
                    <div class="chart-placeholder">
                        [Inventory Chart]<br>
                        Pie chart showing inventory distribution
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-title">
                        <span>👥</span>
                        <span>USER ACTIVITY</span>
                    </div>
                    <div class="chart-placeholder">
                        [User Activity Chart]<br>
                        Heatmap showing user activity patterns
                    </div>
                </div>
            </div>

            <!-- Report Generation Section -->
            <div class="chart-container">
                <div class="chart-title">
                    <span>🔄</span>
                    <span>GENERATE CUSTOM REPORT</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Report Type</label>
                        <select style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                            <option>Comprehensive Farm Report</option>
                            <option>Financial Summary</option>
                            <option>Animal Health Report</option>
                            <option>Inventory Analysis</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Date Range</label>
                        <select style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                            <option>Last 7 Days</option>
                            <option>Last 30 Days</option>
                            <option>Last 3 Months</option>
                            <option>Last Year</option>
                            <option>Custom Range</option>
                        </select>
                    </div>
                </div>
                <button style="margin-top: 1.5rem; padding: 12px 24px; background: var(--forest-green); color: white; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px; margin-left: auto;">
                    <span>📄</span>
                    <span>Generate Report</span>
                </button>
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
        });
    </script>
</body>
</html>