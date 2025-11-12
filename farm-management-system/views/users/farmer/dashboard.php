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

// Get farmer-specific stats
$my_animals = $conn->query("SELECT SUM(number) as total FROM animals")->fetch_assoc()['total'];
$animal_types = $conn->query("SELECT COUNT(DISTINCT type) as types FROM animals")->fetch_assoc()['types'];
$total_sheds = $conn->query("SELECT COUNT(DISTINCT shed_no) as sheds FROM animals")->fetch_assoc()['sheds'];
$recent_activities = $conn->query("SELECT COUNT(*) as count FROM animals WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];

// Get farmer's recent animals
$recent_animals = $conn->query("SELECT type, breed, number, shed_no FROM animals ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - Farm Management System</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
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
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
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

        .nav-item:hover,
        .nav-item.active {
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

        .welcome-banner {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--sky-blue), var(--forest-green));
            border-radius: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .action-btn:hover {
            border-color: var(--forest-green);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Recent Animals */
        .recent-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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

        .animals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .animal-card {
            background: var(--wheat);
            padding: 1rem;
            border-radius: 10px;
            border-left: 4px solid var(--forest-green);
        }

        .animal-type {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .animal-info {
            font-size: 0.9rem;
            color: var(--dark-brown);
        }

        /* Health Alerts */
        .alert-section {
            background: #fff3cd;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #ffc107;
        }

        .alert-title {
            color: #856404;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1rem;
        }

        .alert-item {
            padding: 10px;
            background: white;
            margin: 5px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            <a href="dashboard.php" class="nav-item active">
                <span>📊</span>
                <span>Dashboard</span>
            </a>
            <!-- <a href="animals.php" class="nav-item">
                <span>🐄</span>
                <span>My Animals</span>
            </a> -->
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
            <a href="reports.php" class="nav-item">
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
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h1>🌾 WELCOME TO YOUR FARM DASHBOARD 🌾</h1>
                <p>Manage your animals and farm activities</p>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="add_animal.php" class="action-btn">
                    <span class="action-icon">🐄</span>
                    <span>Add New Animal</span>
                </a>
                <a href="animals.php" class="action-btn">
                    <span class="action-icon">📋</span>
                    <span>View All Animals</span>
                </a>
                <a href="health.php" class="action-btn">
                    <span class="action-icon">❤️</span>
                    <span>Health Records</span>
                </a>
                <a href="reports.php" class="action-btn">
                    <span class="action-icon">📊</span>
                    <span>Generate Report</span>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🐄</div>
                    <div class="stat-number"><?php echo number_format($my_animals); ?></div>
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
                    <div class="stat-number"><?php echo $recent_activities; ?></div>
                    <div class="stat-label">Today's Activities</div>
                </div>
            </div>

            <!-- Recent Animals -->
            <div class="recent-section">
                <div class="section-title">
                    <span>🐄</span>
                    <span>RECENT ANIMALS</span>
                </div>
                <div class="animals-grid">
                    <?php foreach ($recent_animals as $animal): ?>
                        <div class="animal-card">
                            <div class="animal-type">
                                <?php
                                $icons = [
                                    'Cow' => '🐄',
                                    'Cattle' => '🐂',
                                    'Hen' => '🐔',
                                    'Cock' => '🐓',
                                    'Goat' => '🐐',
                                    'Sheep' => '🐑',
                                    'Rabbit' => '🐇',
                                    'Horse' => '🐎',
                                    'Dog' => '🐕',
                                    'Cat' => '🐈',
                                    'Fish' => '🐟',
                                    'Turkey' => '🦃',
                                    'Goose' => '🦆'
                                ];
                                echo $icons[$animal['type']] ?? '🐾';
                                ?>
                            </div>
                            <div class="animal-info">
                                <strong><?php echo $animal['type']; ?></strong><br>
                                Breed: <?php echo $animal['breed']; ?><br>
                                Count: <?php echo $animal['number']; ?><br>
                                Shed: <?php echo $animal['shed_no']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Health Alerts -->
            <div class="alert-section">
                <div class="alert-title">
                    <span>⚠️</span>
                    <span>HEALTH ALERTS & REMINDERS</span>
                </div>
                <div class="alert-item">
                    <span>💉</span>
                    <span>Vaccination due for Jersey cattle in Shed 5</span>
                </div>
                <div class="alert-item">
                    <span>🌡️</span>
                    <span>Temperature check needed for poultry in Shed 6</span>
                </div>
                <div class="alert-item">
                    <span>🍽️</span>
                    <span>Feed stock running low - reorder soon</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function () {
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>

</html>