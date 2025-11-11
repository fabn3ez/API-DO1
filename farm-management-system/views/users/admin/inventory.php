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

// Get inventory stats
$total_items = $conn->query("SELECT COUNT(*) as count FROM inventory")->fetch_assoc()['count'];
$low_stock = $conn->query("SELECT COUNT(*) as count FROM inventory WHERE quantity < 10")->fetch_assoc()['count'];
$out_of_stock = $conn->query("SELECT COUNT(*) as count FROM inventory WHERE quantity = 0")->fetch_assoc()['count'];
$total_value = $conn->query("SELECT SUM(quantity * unit_price) as total FROM inventory")->fetch_assoc()['total'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Farm Management System</title>
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
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--forest-green);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--earth-brown);
            transform: translateY(-2px);
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
        
        .inventory-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .inventory-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .inventory-table th {
            background: var(--forest-green);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .inventory-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .inventory-table tr:hover {
            background: #f9f9f9;
        }

        .stock-low { 
            background: #fff3cd; 
        }
        
        .stock-out { 
            background: #f8d7da; 
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-success { 
            background: #d4edda; 
            color: #155724; 
        }
        
        .badge-warning { 
            background: #fff3cd; 
            color: #856404; 
        }
        
        .badge-danger { 
            background: #f8d7da; 
            color: #721c24; 
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.3s ease;
        }

        .edit-btn { 
            background: var(--sky-blue); 
            color: var(--dark-brown);
        }
        
        .edit-btn:hover { 
            background: #6ec5e0; 
            transform: translateY(-1px);
        }
        
        .delete-btn { 
            background: #ff4444; 
            color: white; 
        }
        
        .delete-btn:hover { 
            background: #cc0000; 
            transform: translateY(-1px);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .action-btn-large {
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
        
        .action-btn-large:hover {
            border-color: var(--forest-green);
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .action-icon {
            font-size: 2.5rem;
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
            <a href="inventory.php" class="nav-item active">
                <span>📦</span>
                <span>Inventory</span>
            </a>
            <a href="financial.php" class="nav-item">
                <span>💰</span>
                <span>Financial</span>
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
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>📦</span>
                    <span>Inventory Management</span>
                </div>
                <a href="add_inventory.php" class="btn btn-primary">
                    <span>➕</span>
                    <span>Add New Item</span>
                </a>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="add_inventory.php" class="action-btn-large">
                    <span class="action-icon">📥</span>
                    <span>Add Item</span>
                </a>
                <a href="inventory_report.php" class="action-btn-large">
                    <span class="action-icon">📊</span>
                    <span>Stock Report</span>
                </a>
                <a href="low_stock.php" class="action-btn-large">
                    <span class="action-icon">⚠️</span>
                    <span>Low Stock</span>
                </a>
                <a href="suppliers.php" class="action-btn-large">
                    <span class="action-icon">🏢</span>
                    <span>Suppliers</span>
                </a>
            </div>

            <!-- Inventory Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-number"><?php echo $total_items; ?></div>
                    <div class="stat-label">Total Items</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-number">$<?php echo number_format((float)($total_value ?? 0), 2); ?></div>
                    <div class="stat-label">Total Value</div>
                </div>
                <div class="stat-card" style="border-left-color: #ffc107;">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-number"><?php echo $low_stock; ?></div>
                    <div class="stat-label">Low Stock</div>
                </div>
                <div class="stat-card" style="border-left-color: #dc3545;">
                    <div class="stat-icon">🚨</div>
                    <div class="stat-number"><?php echo $out_of_stock; ?></div>
                    <div class="stat-label">Out of Stock</div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="inventory-table">
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                            <th>Supplier</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Animal Feed Premium</strong></td>
                            <td>Feed</td>
                            <td>50 kg</td>
                            <td>$25.00</td>
                            <td>$1,250.00</td>
                            <td>FeedCo Ltd</td>
                            <td>2024-12-31</td>
                            <td><span class="badge badge-warning">⚠️ Low Stock</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn delete-btn">🗑️</button>
                            </td>
                        </tr>
                        <tr class="stock-low">
                            <td><strong>Vaccine A - Livestock</strong></td>
                            <td>Medical</td>
                            <td>5 units</td>
                            <td>$45.00</td>
                            <td>$225.00</td>
                            <td>VetCare Solutions</td>
                            <td>2024-08-15</td>
                            <td><span class="badge badge-danger">🚨 Very Low</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn delete-btn">🗑️</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Water Trough Large</strong></td>
                            <td>Equipment</td>
                            <td>12 units</td>
                            <td>$120.00</td>
                            <td>$1,440.00</td>
                            <td>FarmEquip Inc</td>
                            <td>-</td>
                            <td><span class="badge badge-success">✅ In Stock</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn delete-btn">🗑️</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Milking Machine</strong></td>
                            <td>Equipment</td>
                            <td>3 units</td>
                            <td>$850.00</td>
                            <td>$2,550.00</td>
                            <td>DairyTech</td>
                            <td>-</td>
                            <td><span class="badge badge-success">✅ In Stock</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn delete-btn">🗑️</button>
                            </td>
                        </tr>
                        <tr class="stock-out">
                            <td><strong>Antibiotics Broad</strong></td>
                            <td>Medical</td>
                            <td>0 units</td>
                            <td>$35.00</td>
                            <td>$0.00</td>
                            <td>VetCare Solutions</td>
                            <td>2025-03-20</td>
                            <td><span class="badge badge-danger">❌ Out of Stock</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn delete-btn">🗑️</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Fencing Wire</strong></td>
                            <td>Infrastructure</td>
                            <td>25 rolls</td>
                            <td>$45.00</td>
                            <td>$1,125.00</td>
                            <td>FarmBuild Ltd</td>
                            <td>-</td>
                            <td><span class="badge badge-success">✅ In Stock</span></td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn delete-btn">🗑️</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Inventory Summary -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
                <div class="chart-container" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div class="chart-title" style="margin-bottom: 1rem; color: var(--forest-green); display: flex; align-items: center; gap: 10px; font-size: 1.1rem; font-weight: 600;">
                        <span>📊</span>
                        <span>INVENTORY BY CATEGORY</span>
                    </div>
                    <div style="height: 200px; background: #f9f9f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666; border: 2px dashed #ddd;">
                        [Category Distribution Chart]<br>
                        Feed 🍃 | Medical 💊 | Equipment ⚙️
                    </div>
                </div>
                <div class="chart-container" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div class="chart-title" style="margin-bottom: 1rem; color: var(--forest-green); display: flex; align-items: center; gap: 10px; font-size: 1.1rem; font-weight: 600;">
                        <span>📈</span>
                        <span>STOCK ALERTS</span>
                    </div>
                    <div style="height: 200px; background: #f9f9f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666; border: 2px dashed #ddd;">
                        [Stock Alert Chart]<br>
                        Items needing attention
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation active state
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Add confirmation for delete actions
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this inventory item?')) {
                        // Add delete logic here
                        const row = this.closest('tr');
                        row.style.opacity = '0.5';
                        setTimeout(() => {
                            row.remove();
                        }, 500);
                    }
                });
            });

            // Simulate real-time stock updates
            setInterval(() => {
                const stockNumbers = document.querySelectorAll('.inventory-table td:nth-child(3)');
                stockNumbers.forEach(cell => {
                    if (Math.random() > 0.8) {
                        const currentQty = parseInt(cell.textContent);
                        if (currentQty > 0) {
                            cell.textContent = (currentQty - 1) + ' ' + cell.textContent.split(' ')[1];
                        }
                    }
                });
            }, 10000);
        });
    </script>
</body>
</html>