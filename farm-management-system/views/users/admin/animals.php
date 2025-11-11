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

// Get all animals
$animals = $conn->query("SELECT * FROM animals ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$total_animals = $conn->query("SELECT SUM(number) as total FROM animals")->fetch_assoc()['total'];
$animal_types = $conn->query("SELECT COUNT(DISTINCT type) as types FROM animals")->fetch_assoc()['types'];
$total_sheds = $conn->query("SELECT COUNT(DISTINCT shed_no) as sheds FROM animals")->fetch_assoc()['sheds'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animals Management - Farm Management System</title>
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

        /* Toolbar */
        .toolbar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-box {
            padding: 10px 15px;
            border: 2px solid var(--forest-green);
            border-radius: 25px;
            width: 300px;
            font-size: 1rem;
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
        }

        .btn-primary {
            background: var(--forest-green);
            color: white;
        }

        .btn-secondary {
            background: var(--sky-blue);
            color: var(--dark-brown);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* Animals Table */
        .animals-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .animals-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .animals-table th {
            background: var(--forest-green);
            color: white;
            padding: 15px;
            text-align: left;
        }

        .animals-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .animals-table tr:hover {
            background: #f9f9f9;
        }

        .animal-type {
            font-size: 1.5rem;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }

        .edit-btn { background: var(--sky-blue); color: white; }
        .view-btn { background: var(--forest-green); color: white; }
        .delete-btn { background: #ff4444; color: white; }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 12px;
            border: 2px solid var(--forest-green);
            border-radius: 8px;
            background: white;
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
            <a href="animals.php" class="nav-item active">
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
                    <span>🐄</span>
                    <span>Animals Management</span>
                </div>
                <a href="add_animal.php" class="btn btn-primary">
                    <span>➕</span>
                    <span>Add New Animal</span>
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
                    <div class="stat-icon">📝</div>
                    <div class="stat-number"><?php echo count($animals); ?></div>
                    <div class="stat-label">Total Records</div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="toolbar">
                <input type="text" class="search-box" placeholder="🔍 Search animals...">
                <div class="filters">
                    <select class="filter-select">
                        <option>All Types</option>
                        <option>Cattle</option>
                        <option>Poultry</option>
                        <option>Goats</option>
                        <option>Sheep</option>
                    </select>
                    <select class="filter-select">
                        <option>All Sheds</option>
                        <option>Shed 1</option>
                        <option>Shed 2</option>
                        <option>Shed 3</option>
                    </select>
                </div>
                <button class="btn btn-secondary">
                    <span>📤</span>
                    <span>Export</span>
                </button>
            </div>

            <!-- Animals Table -->
            <div class="animals-table">
                <table>
                    <thead>
                        <tr>
                            <th width="80">Type</th>
                            <th>Breed</th>
                            <th>Gender</th>
                            <th width="120">Count</th>
                            <th width="120">Avg Weight</th>
                            <th width="100">Shed</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($animals as $animal): ?>
                        <tr>
                            <td class="animal-type">
                                <?php
                                $icons = [
                                    'Cow' => '🐄', 'Cattle' => '🐂', 'Hen' => '🐔', 'Cock' => '🐓',
                                    'Goat' => '🐐', 'Sheep' => '🐑', 'Rabbit' => '🐇', 'Horse' => '🐎',
                                    'Dog' => '🐕', 'Cat' => '🐈', 'Fish' => '🐟', 'Turkey' => '🦃',
                                    'Goose' => '🦆'
                                ];
                                echo $icons[$animal['type']] ?? '🐾';
                                ?>
                            </td>
                            <td><strong><?php echo $animal['breed']; ?></strong></td>
                            <td><?php echo $animal['gender']; ?></td>
                            <td><?php echo $animal['number']; ?></td>
                            <td><?php echo $animal['avg_weight']; ?></td>
                            <td><?php echo $animal['shed_no']; ?></td>
                            <td class="action-buttons">
                                <a href="edit_animal.php?id=<?php echo $animal['id']; ?>" class="action-btn edit-btn">✏️ Edit</a>
                                <a href="view_animal.php?id=<?php echo $animal['id']; ?>" class="action-btn view-btn">👁️ View</a>
                                <a href="delete_animal.php?id=<?php echo $animal['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this animal?')">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

            // Search functionality
            document.querySelector('.search-box').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('.animals-table tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        });
    </script>
</body>
</html>