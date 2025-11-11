 <?php
session_start();
require_once '../../auth/check_role.php';
check_role('farmer');

// Database connection
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Get report data
$total_animals = $conn->query("SELECT SUM(number) as total FROM animals")->fetch_assoc()['total'];
$animal_types = $conn->query("SELECT COUNT(DISTINCT type) as types FROM animals")->fetch_assoc()['types'];
$total_sheds = $conn->query("SELECT COUNT(DISTINCT shed_no) as sheds FROM animals")->fetch_assoc()['sheds'];

// Get animal distribution by type
$animal_distribution = $conn->query("
    SELECT type, SUM(number) as total 
    FROM animals 
    GROUP BY type 
    ORDER BY total DESC
")->fetch_all(MYSQLI_ASSOC);

// Get shed capacity
$shed_capacity = $conn->query("
    SELECT shed_no, SUM(number) as total_animals 
    FROM animals 
    GROUP BY shed_no 
    ORDER BY shed_no
")->fetch_all(MYSQLI_ASSOC);

// Get monthly animal additions (last 6 months)
$monthly_additions = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(number) as total_added
    FROM animals 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Reports - Farm Management System</title>
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
        
        /* Report Actions */
        .report-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .action-btn {
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
        }
        
        .btn-primary {
            background: var(--forest-green);
            color: white;
        }
        
        .btn-secondary {
            background: var(--sky-blue);
            color: var(--dark-brown);
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Report Cards */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .report-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 5px solid var(--forest-green);
            transition: transform 0.3s ease;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
        }
        
        .report-title {
            margin-bottom: 1rem;
            color: var(--forest-green);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .report-content {
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        /* Data Tables */
        .data-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: var(--forest-green);
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .data-table tr:hover {
            background: #f9f9f9;
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--forest-green);
            border-radius: 5px;
        }
        
        /* Chart Placeholders */
        .chart-placeholder {
            height: 200px;
            background: #f9f9f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            margin: 1rem 0;
        }
        
        /* Summary Stats */
        .summary-stats {
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
            <span>👋 Welcome, <?php echo $_SESSION['username']; ?> (Farmer)</span>
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
                <span>My Animals</span>
            </a>
            <a href="add_animal.php" class="nav-item">
                <span>➕</span>
                <span>Add Animal</span>
            </a>
            <a href="health.php" class="nav-item">
                <span>❤️</span>
                <span>Health Records</span>
            </a>
            <a href="sheds.php" class="nav-item">
                <span>🏠</span>
                <span>Shed Management</span>
            </a>
            <a href="reports.php" class="nav-item active">
                <span>📈</span>
                <span>Farm Reports</span>
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
                    <span>📈</span>
                    <span>Farm Reports & Analytics</span>
                </div>
                <div class="report-actions">
                    <button class="action-btn btn-primary" onclick="window.print()">
                        <span>🖨️</span>
                        <span>Print Report</span>
                    </button>
                    <button class="action-btn btn-secondary" onclick="exportToPDF()">
                        <span>📥</span>
                        <span>Export PDF</span>
                    </button>
                    <button class="action-btn btn-secondary" onclick="exportToExcel()">
                        <span>📊</span>
                        <span>Export Excel</span>
                    </button>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="summary-stats">
                <div class="stat-card">
                    <div class="stat-icon">🐄</div>
                    <div class="stat-number"><?php echo number_format($total_animals); ?></div>
                    <div class="stat-label">Total Animals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🐓</div>
                    <div class="stat-number"><?php echo $animal_types; ?></div>
                    <div class="stat-label">Animal Types</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-number"><?php echo $total_sheds; ?></div>
                    <div class="stat-label">Active Sheds</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-number"><?php echo date('M Y'); ?></div>
                    <div class="stat-label">Current Period</div>
                </div>
            </div>

            <!-- Reports Grid -->
            <div class="reports-grid">
                <!-- Animal Distribution Report -->
                <div class="report-card">
                    <div class="report-title">
                        <span>🐄</span>
                        <span>ANIMAL DISTRIBUTION</span>
                    </div>
                    <div class="report-content">
                        <div class="chart-placeholder">
                            [Animal Distribution Chart]<br>
                            Cattle 🐄 | Poultry 🐔 | Goats 🐐 | Sheep 🐑
                        </div>
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Animal Type</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($animal_distribution as $animal): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $icons = [
                                                'Cow' => '🐄', 'Cattle' => '🐂', 'Hen' => '🐔', 'Cock' => '🐓',
                                                'Goat' => '🐐', 'Sheep' => '🐑', 'Rabbit' => '🐇', 'Horse' => '🐎'
                                            ];
                                            echo ($icons[$animal['type']] ?? '🐾') . ' ' . $animal['type'];
                                            ?>
                                        </td>
                                        <td><?php echo number_format($animal['total']); ?></td>
                                        <td>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo ($animal['total'] / $total_animals) * 100; ?>%"></div>
                                            </div>
                                            <small><?php echo round(($animal['total'] / $total_animals) * 100, 1); ?>%</small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Shed Capacity Report -->
                <div class="report-card">
                    <div class="report-title">
                        <span>🏠</span>
                        <span>SHED CAPACITY</span>
                    </div>
                    <div class="report-content">
                        <div class="chart-placeholder">
                            [Shed Capacity Chart]<br>
                            Shed-wise animal distribution
                        </div>
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Shed Number</th>
                                        <th>Animal Count</th>
                                        <th>Capacity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($shed_capacity as $shed): ?>
                                    <tr>
                                        <td><?php echo $shed['shed_no']; ?></td>
                                        <td><?php echo number_format($shed['total_animals']); ?></td>
                                        <td>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo min(($shed['total_animals'] / 1000) * 100, 100); ?>%"></div>
                                            </div>
                                            <small><?php echo $shed['total_animals']; ?> animals</small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Monthly Growth Report -->
                <div class="report-card">
                    <div class="report-title">
                        <span>📈</span>
                        <span>MONTHLY GROWTH</span>
                    </div>
                    <div class="report-content">
                        <div class="chart-placeholder">
                            [Monthly Growth Chart]<br>
                            Animal additions over last 6 months
                        </div>
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Animals Added</th>
                                        <th>Growth</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach(array_reverse($monthly_additions) as $month): ?>
                                    <tr>
                                        <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                        <td><?php echo number_format($month['total_added']); ?></td>
                                        <td>
                                            <span style="color: var(--forest-green);">↑</span>
                                            <?php echo $month['total_added']; ?> new
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Health Summary Report -->
                <div class="report-card">
                    <div class="report-title">
                        <span>❤️</span>
                        <span>HEALTH SUMMARY</span>
                    </div>
                    <div class="report-content">
                        <div class="chart-placeholder">
                            [Health Status Chart]<br>
                            Vaccination & Health Overview
                        </div>
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Health Status</th>
                                        <th>Count</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>✅ Vaccinated</td>
                                        <td>1,250</td>
                                        <td><span style="color: var(--forest-green);">Good</span></td>
                                    </tr>
                                    <tr>
                                        <td>⚠️ Due for Vaccination</td>
                                        <td>350</td>
                                        <td><span style="color: #ffaa00;">Pending</span></td>
                                    </tr>
                                    <tr>
                                        <td>❌ Not Vaccinated</td>
                                        <td>120</td>
                                        <td><span style="color: #ff4444;">Critical</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Insights -->
            <div class="report-card">
                <div class="report-title">
                    <span>💡</span>
                    <span>QUICK INSIGHTS</span>
                </div>
                <div class="report-content">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div style="background: #e8f5e8; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--forest-green);">
                            <strong>📊 Peak Population</strong><br>
                            Your farm has the highest number of animals in <strong>Shed 5</strong>
                        </div>
                        <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; border-left: 4px solid #ffc107;">
                            <strong>💉 Vaccination Alert</strong><br>
                            <strong>350</strong> animals need vaccination this month
                        </div>
                        <div style="background: #e3f2fd; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--sky-blue);">
                            <strong>📈 Growth Trend</strong><br>
                            Animal population increased by <strong>15%</strong> this quarter
                        </div>
                    </div>
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
        });

        function exportToPDF() {
            alert('PDF export functionality would be implemented here!');
            // In a real application, this would generate a PDF report
        }

        function exportToExcel() {
            alert('Excel export functionality would be implemented here!');
            // In a real application, this would generate an Excel report
        }

        // Auto-refresh reports every 5 minutes
        setInterval(() => {
            console.log('Refreshing report data...');
            // In a real application, this would refresh the report data
        }, 300000);
    </script>
</body>
</html>