<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('farmer');

// Set default date range (last 30 days)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'overview';

// Validate dates
if ($start_date > $end_date) {
    $temp = $start_date;
    $start_date = $end_date;
    $end_date = $temp;
}

// Fetch report data based on type
$report_data = [];
$chart_data = [];

switch ($report_type) {
    case 'animals':
        // Animal statistics
        $animal_stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_animals,
                COUNT(CASE WHEN gender = 'Male' THEN 1 END) as males,
                COUNT(CASE WHEN gender = 'Female' THEN 1 END) as females,
                COUNT(CASE WHEN health_status = 'Excellent' THEN 1 END) as excellent_health,
                COUNT(CASE WHEN health_status = 'Good' THEN 1 END) as good_health,
                COUNT(CASE WHEN health_status = 'Fair' THEN 1 END) as fair_health,
                COUNT(CASE WHEN health_status = 'Poor' THEN 1 END) as poor_health,
                COUNT(CASE WHEN health_status = 'Critical' THEN 1 END) as critical_health
            FROM animals 
            WHERE user_id = ?
        ");
        $animal_stmt->execute([$_SESSION['user_id']]);
        $report_data = $animal_stmt->fetch(PDO::FETCH_ASSOC);

        // Species distribution
        $species_stmt = $pdo->prepare("
            SELECT species, COUNT(*) as count 
            FROM animals 
            WHERE user_id = ? 
            GROUP BY species 
            ORDER BY count DESC
        ");
        $species_stmt->execute([$_SESSION['user_id']]);
        $chart_data = $species_stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'health':
        // Health records statistics
        $health_stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_records,
                COUNT(DISTINCT animal_id) as animals_with_records,
                AVG(CASE WHEN health_condition LIKE '%healthy%' OR health_condition LIKE '%good%' THEN 1 ELSE 0 END) as healthy_percentage
            FROM health_records hr
            JOIN animals a ON hr.animal_id = a.id
            WHERE a.user_id = ? AND hr.checkup_date BETWEEN ? AND ?
        ");
        $health_stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
        $report_data = $health_stmt->fetch(PDO::FETCH_ASSOC);

        // Common health conditions
        $conditions_stmt = $pdo->prepare("
            SELECT health_condition, COUNT(*) as count 
            FROM health_records hr
            JOIN animals a ON hr.animal_id = a.id
            WHERE a.user_id = ? AND hr.checkup_date BETWEEN ? AND ?
            GROUP BY health_condition 
            ORDER BY count DESC 
            LIMIT 10
        ");
        $conditions_stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
        $chart_data = $conditions_stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'sales':
        // Sales statistics
        $sales_stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_order_value,
                MIN(total_amount) as min_order_value,
                MAX(total_amount) as max_order_value
            FROM sales_orders 
            WHERE user_id = ? AND order_date BETWEEN ? AND ? AND status = 'completed'
        ");
        $sales_stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
        $report_data = $sales_stmt->fetch(PDO::FETCH_ASSOC);

        // Monthly sales trend
        $trend_stmt = $pdo->prepare("
            SELECT 
                DATE_FORMAT(order_date, '%Y-%m') as month,
                SUM(total_amount) as revenue,
                COUNT(*) as orders
            FROM sales_orders 
            WHERE user_id = ? AND order_date BETWEEN ? AND ? AND status = 'completed'
            GROUP BY DATE_FORMAT(order_date, '%Y-%m')
            ORDER BY month
        ");
        $trend_stmt->execute([$_SESSION['user_id'], $start_date, $end_date]);
        $chart_data = $trend_stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'inventory':
        // Inventory statistics
        $inv_stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_items,
                SUM(quantity) as total_quantity,
                SUM(quantity * price) as total_value,
                COUNT(CASE WHEN quantity < 10 THEN 1 END) as low_stock_items,
                COUNT(CASE WHEN quantity = 0 THEN 1 END) as out_of_stock_items
            FROM inventory 
            WHERE user_id = ?
        ");
        $inv_stmt->execute([$_SESSION['user_id']]);
        $report_data = $inv_stmt->fetch(PDO::FETCH_ASSOC);

        // Category distribution
        $category_stmt = $pdo->prepare("
            SELECT category, COUNT(*) as count, SUM(quantity * price) as value
            FROM inventory 
            WHERE user_id = ?
            GROUP BY category 
            ORDER BY value DESC
        ");
        $category_stmt->execute([$_SESSION['user_id']]);
        $chart_data = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    default: // overview
        // Overview statistics
        $overview_stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM animals WHERE user_id = ?) as total_animals,
                (SELECT COUNT(*) FROM sheds WHERE user_id = ?) as total_sheds,
                (SELECT COUNT(*) FROM inventory WHERE user_id = ?) as total_inventory_items,
                (SELECT COUNT(*) FROM sales_orders WHERE user_id = ? AND status = 'completed') as total_orders,
                (SELECT SUM(total_amount) FROM sales_orders WHERE user_id = ? AND status = 'completed') as total_revenue,
                (SELECT COUNT(*) FROM health_records hr JOIN animals a ON hr.animal_id = a.id WHERE a.user_id = ? AND hr.checkup_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_health_records
        ");
        $overview_stmt->execute([
            $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'],
            $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']
        ]);
        $report_data = $overview_stmt->fetch(PDO::FETCH_ASSOC);
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-chart-bar"></i> Farm Reports & Analytics</h1>
            </div>

            <!-- Report Filters -->
            <div class="report-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" onchange="this.form.submit()">
                                <option value="overview" <?php echo $report_type == 'overview' ? 'selected' : ''; ?>>Overview</option>
                                <option value="animals" <?php echo $report_type == 'animals' ? 'selected' : ''; ?>>Animals</option>
                                <option value="health" <?php echo $report_type == 'health' ? 'selected' : ''; ?>>Health</option>
                                <option value="sales" <?php echo $report_type == 'sales' ? 'selected' : ''; ?>>Sales</option>
                                <option value="inventory" <?php echo $report_type == 'inventory' ? 'selected' : ''; ?>>Inventory</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>

                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="printReport()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Report Content -->
            <div class="report-content" id="reportContent">
                <?php if ($report_type == 'overview'): ?>
                    <!-- Overview Report -->
                    <div class="report-section">
                        <h2><i class="fas fa-tachometer-alt"></i> Farm Overview</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon animals">
                                    <i class="fas fa-cow"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $report_data['total_animals']; ?></h3>
                                    <p>Total Animals</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon sheds">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $report_data['total_sheds']; ?></h3>
                                    <p>Sheds</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon inventory">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $report_data['total_inventory_items']; ?></h3>
                                    <p>Inventory Items</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon sales">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $report_data['total_orders']; ?></h3>
                                    <p>Completed Orders</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon revenue">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <div class="stat-info">
                                    <h3>$<?php echo number_format($report_data['total_revenue'], 2); ?></h3>
                                    <p>Total Revenue</p>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon health">
                                    <i class="fas fa-heartbeat"></i>
                                </div>
                                <div class="stat-info">
                                    <h3><?php echo $report_data['recent_health_records']; ?></h3>
                                    <p>Health Records (30 days)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($report_type == 'animals'): ?>
                    <!-- Animals Report -->
                    <div class="report-section">
                        <h2><i class="fas fa-cow"></i> Animal Statistics</h2>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3><?php echo $report_data['total_animals']; ?></h3>
                                <p>Total Animals</p>
                            </div>
                            <div class="stat-card">
                                <h3><?php echo $report_data['males']; ?></h3>
                                <p>Male Animals</p>
                            </div>
                            <div class="stat-card">
                                <h3><?php echo $report_data['females']; ?></h3>
                                <p>Female Animals</p>
                            </div>
                            <div class="stat-card critical">
                                <h3><?php echo $report_data['critical_health']; ?></h3>
                                <p>Critical Health</p>
                            </div>
                        </div>

                        <?php if (!empty($chart_data)): ?>
                        <div class="chart-container">
                            <canvas id="speciesChart"></canvas>
                        </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($report_type == 'health'): ?>
                    <!-- Health Report -->
                    <div class="report-section">
                        <h2><i class="fas fa-heartbeat"></i> Health Records Report</h2>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3><?php echo $report_data['total_records']; ?></h3>
                                <p>Total Records</p>
                            </div>
                            <div class="stat-card">
                                <h3><?php echo $report_data['animals_with_records']; ?></h3>
                                <p>Animals with Records</p>
                            </div>
                            <div class="stat-card">
                                <h3><?php echo round($report_data['healthy_percentage'] * 100, 1); ?>%</h3>
                                <p>Healthy Animals</p>
                            </div>
                        </div>

                        <?php if (!empty($chart_data)): ?>
                        <div class="chart-container">
                            <canvas id="healthChart"></canvas>
                        </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($report_type == 'sales'): ?>
                    <!-- Sales Report -->
                    <div class="report-section">
                        <h2><i class="fas fa-chart-line"></i> Sales Report</h2>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3><?php echo $report_data['total_orders']; ?></h3>
                                <p>Total Orders</p>
                            </div>
                            <div class="stat-card revenue">
                                <h3>$<?php echo number_format($report_data['total_revenue'], 2); ?></h3>
                                <p>Total Revenue</p>
                            </div>
                            <div class="stat-card">
                                <h3>$<?php echo number_format($report_data['average_order_value'], 2); ?></h3>
                                <p>Average Order</p>
                            </div>
                        </div>

                        <?php if (!empty($chart_data)): ?>
                        <div class="chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($report_type == 'inventory'): ?>
                    <!-- Inventory Report -->
                    <div class="report-section">
                        <h2><i class="fas fa-boxes"></i> Inventory Report</h2>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3><?php echo $report_data['total_items']; ?></h3>
                                <p>Total Items</p>
                            </div>
                            <div class="stat-card">
                                <h3><?php echo $report_data['total_quantity']; ?></h3>
                                <p>Total Quantity</p>
                            </div>
                            <div class="stat-card revenue">
                                <h3>$<?php echo number_format($report_data['total_value'], 2); ?></h3>
                                <p>Total Value</p>
                            </div>
                            <div class="stat-card warning">
                                <h3><?php echo $report_data['low_stock_items']; ?></h3>
                                <p>Low Stock Items</p>
                            </div>
                            <div class="stat-card critical">
                                <h3><?php echo $report_data['out_of_stock_items']; ?></h3>
                                <p>Out of Stock</p>
                            </div>
                        </div>

                        <?php if (!empty($chart_data)): ?>
                        <div class="chart-container">
                            <canvas id="inventoryChart"></canvas>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        function printReport() {
            window.print();
        }

        // Initialize charts based on report type
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($report_type == 'animals' && !empty($chart_data)): ?>
                new Chart(document.getElementById('speciesChart'), {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode(array_column($chart_data, 'species')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($chart_data, 'count')); ?>,
                            backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#607D8B']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Animal Species Distribution'
                            }
                        }
                    }
                });
            <?php elseif ($report_type == 'health' && !empty($chart_data)): ?>
                new Chart(document.getElementById('healthChart'), {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($chart_data, 'health_condition')); ?>,
                        datasets: [{
                            label: 'Number of Cases',
                            data: <?php echo json_encode(array_column($chart_data, 'count')); ?>,
                            backgroundColor: '#FF6B6B'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Common Health Conditions'
                            }
                        }
                    }
                });
            <?php elseif ($report_type == 'sales' && !empty($chart_data)): ?>
                new Chart(document.getElementById('salesChart'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_column($chart_data, 'month')); ?>,
                        datasets: [{
                            label: 'Revenue ($)',
                            data: <?php echo json_encode(array_column($chart_data, 'revenue')); ?>,
                            borderColor: '#4CAF50',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Monthly Sales Trend'
                            }
                        }
                    }
                });
            <?php elseif ($report_type == 'inventory' && !empty($chart_data)): ?>
                new Chart(document.getElementById('inventoryChart'), {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($chart_data, 'category')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($chart_data, 'value')); ?>,
                            backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Inventory Value by Category'
                            }
                        }
                    }
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>