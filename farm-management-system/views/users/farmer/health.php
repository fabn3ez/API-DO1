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

// Get health statistics
$total_animals = $conn->query("SELECT SUM(number) as total FROM animals")->fetch_assoc()['total'];
$animals_due_vaccination = $conn->query("
    SELECT COUNT(*) as count FROM animals 
    WHERE id IN (SELECT animal_id FROM health_records WHERE next_vaccination <= CURDATE() OR next_vaccination IS NULL)
")->fetch_assoc()['count'];
$recent_health_checks = $conn->query("
    SELECT COUNT(*) as count FROM health_records 
    WHERE DATE(check_date) = CURDATE()
")->fetch_assoc()['count'];
$animals_under_treatment = $conn->query("
    SELECT COUNT(*) as count FROM health_records 
    WHERE treatment_status = 'Under Treatment'
")->fetch_assoc()['count'];

// Get recent health records
$recent_health_records = $conn->query("
    SELECT hr.*, a.type, a.breed, a.shed_no 
    FROM health_records hr 
    LEFT JOIN animals a ON hr.animal_id = a.id 
    ORDER BY hr.check_date DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get vaccination schedule
$upcoming_vaccinations = $conn->query("
    SELECT hr.*, a.type, a.breed, a.shed_no 
    FROM health_records hr 
    LEFT JOIN animals a ON hr.animal_id = a.id 
    WHERE hr.next_vaccination BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY hr.next_vaccination ASC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get health issues by type
$health_issues = $conn->query("
    SELECT health_issue, COUNT(*) as count 
    FROM health_records 
    WHERE health_issue IS NOT NULL 
    GROUP BY health_issue 
    ORDER BY count DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Records - Farm Management System</title>
    <style>
        /* Farm Theme Styles */
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
            --health-red: #dc3545;
            --health-orange: #fd7e14;
            --health-green: #28a745;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.03"><text x="50" y="50" font-size="80" text-anchor="middle" dominant-baseline="middle">❤️</text></svg>');
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
        
        /* Health Actions */
        .health-actions {
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
        
        .btn-danger {
            background: var(--health-red);
            color: white;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Health Stats */
        .health-stats {
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
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.critical {
            border-left: 5px solid var(--health-red);
        }
        
        .stat-card.warning {
            border-left: 5px solid var(--health-orange);
        }
        
        .stat-card.good {
            border-left: 5px solid var(--health-green);
        }
        
        .stat-card.info {
            border-left: 5px solid var(--sky-blue);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-card.critical .stat-number {
            color: var(--health-red);
        }
        
        .stat-card.warning .stat-number {
            color: var(--health-orange);
        }
        
        .stat-card.good .stat-number {
            color: var(--health-green);
        }
        
        .stat-card.info .stat-number {
            color: var(--sky-blue);
        }
        
        .stat-label {
            color: var(--dark-brown);
            font-size: 0.9rem;
        }
        
        /* Health Tables */
        .health-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        
        .data-table {
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
        
        /* Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-healthy {
            background: #d4edda;
            color: #155724;
        }
        
        .status-treatment {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-critical {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-vaccinated {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        /* Alert Styles */
        .alert-item {
            padding: 12px;
            margin: 8px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-critical {
            background: #f8d7da;
            border-left: 4px solid var(--health-red);
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid var(--health-orange);
        }
        
        .alert-info {
            background: #d1ecf1;
            border-left: 4px solid var(--sky-blue);
        }
        
        /* Health Grid */
        .health-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .health-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .health-card .section-title {
            margin-bottom: 1rem;
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
            <a href="health.php" class="nav-item active">
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
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>❤️</span>
                    <span>Animal Health Records</span>
                </div>
                <div class="health-actions">
                    <a href="add_health_record.php" class="action-btn btn-primary">
                        <span>➕</span>
                        <span>Add Health Record</span>
                    </a>
                    <button class="action-btn btn-secondary" onclick="scheduleVaccination()">
                        <span>💉</span>
                        <span>Schedule Vaccination</span>
                    </button>
                    <button class="action-btn btn-danger" onclick="alertEmergency()">
                        <span>🚨</span>
                        <span>Emergency Alert</span>
                    </button>
                </div>
            </div>

            <!-- Health Statistics -->
            <div class="health-stats">
                <div class="stat-card critical">
                    <div class="stat-icon">🚨</div>
                    <div class="stat-number"><?php echo $animals_under_treatment; ?></div>
                    <div class="stat-label">Under Treatment</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">💉</div>
                    <div class="stat-number"><?php echo $animals_due_vaccination; ?></div>
                    <div class="stat-label">Due for Vaccination</div>
                </div>
                <div class="stat-card good">
                    <div class="stat-icon">✅</div>
                    <div class="stat-number"><?php echo $recent_health_checks; ?></div>
                    <div class="stat-label">Today's Checkups</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">🐄</div>
                    <div class="stat-number"><?php echo number_format($total_animals); ?></div>
                    <div class="stat-label">Total Animals</div>
                </div>
            </div>

            <!-- Health Grid -->
            <div class="health-grid">
                <!-- Recent Health Records -->
                <div class="health-card">
                    <div class="section-title">
                        <span>📋</span>
                        <span>RECENT HEALTH RECORDS</span>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Animal</th>
                                <th>Check Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_health_records as $record): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $record['type']; ?></strong><br>
                                    <small><?php echo $record['breed']; ?> - Shed <?php echo $record['shed_no']; ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($record['check_date'])); ?></td>
                                <td>
                                    <?php if($record['treatment_status'] === 'Under Treatment'): ?>
                                        <span class="status-badge status-critical">Under Treatment</span>
                                    <?php elseif($record['health_status'] === 'Healthy'): ?>
                                        <span class="status-badge status-healthy">Healthy</span>
                                    <?php else: ?>
                                        <span class="status-badge status-treatment">Needs Care</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="action-btn" style="padding: 4px 8px; font-size: 0.8rem;" onclick="viewHealthRecord(<?php echo $record['id']; ?>)">
                                        👁️ View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Upcoming Vaccinations -->
                <div class="health-card">
                    <div class="section-title">
                        <span>💉</span>
                        <span>UPCOMING VACCINATIONS</span>
                    </div>
                    <?php if(!empty($upcoming_vaccinations)): ?>
                        <?php foreach($upcoming_vaccinations as $vaccination): ?>
                        <div class="alert-item alert-warning">
                            <span>💉</span>
                            <div>
                                <strong><?php echo $vaccination['type']; ?> (<?php echo $vaccination['breed']; ?>)</strong><br>
                                Shed <?php echo $vaccination['shed_no']; ?> - 
                                <?php echo date('M d, Y', strtotime($vaccination['next_vaccination'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert-item alert-info">
                            <span>✅</span>
                            <div>No upcoming vaccinations in the next 7 days</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Health Alerts Section -->
            <div class="health-section">
                <div class="section-title">
                    <span>🚨</span>
                    <span>HEALTH ALERTS & NOTIFICATIONS</span>
                </div>
                <div class="alert-item alert-critical">
                    <span>🚨</span>
                    <div>
                        <strong>URGENT: 5 cattle in Shed 3 showing fever symptoms</strong><br>
                        <small>Immediate veterinary attention required</small>
                    </div>
                </div>
                <div class="alert-item alert-warning">
                    <span>💉</span>
                    <div>
                        <strong>Vaccination due for poultry in Shed 6</strong><br>
                        <small>Due date: <?php echo date('M d, Y', strtotime('+3 days')); ?></small>
                    </div>
                </div>
                <div class="alert-item alert-info">
                    <span>🌡️</span>
                    <div>
                        <strong>Routine temperature check completed</strong><br>
                        <small>All animals in Shed 2 are within normal range</small>
                    </div>
                </div>
            </div>

            <!-- Common Health Issues -->
            <div class="health-section">
                <div class="section-title">
                    <span>📊</span>
                    <span>COMMON HEALTH ISSUES</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Health Issue</th>
                            <th>Cases</th>
                            <th>Severity</th>
                            <th>Recommended Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Respiratory Infection</td>
                            <td>12</td>
                            <td><span class="status-badge status-critical">High</span></td>
                            <td>Isolate affected animals, consult vet</td>
                        </tr>
                        <tr>
                            <td>Foot Rot</td>
                            <td>8</td>
                            <td><span class="status-badge status-treatment">Medium</span></td>
                            <td>Improve drainage, apply treatment</td>
                        </tr>
                        <tr>
                            <td>Mastitis</td>
                            <td>5</td>
                            <td><span class="status-badge status-treatment">Medium</span></td>
                            <td>Antibiotic treatment, milking hygiene</td>
                        </tr>
                        <tr>
                            <td>Parasites</td>
                            <td>15</td>
                            <td><span class="status-badge status-treatment">Medium</span></td>
                            <td>Deworming, pasture management</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Quick Health Tips -->
            <div class="health-section">
                <div class="section-title">
                    <span>💡</span>
                    <span>QUICK HEALTH TIPS</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div style="background: #e8f5e8; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--health-green);">
                        <strong>🩺 Regular Checkups</strong><br>
                        Conduct daily health checks and maintain proper records
                    </div>
                    <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--health-orange);">
                        <strong>💉 Vaccination Schedule</strong><br>
                        Follow the vaccination calendar strictly for disease prevention
                    </div>
                    <div style="background: #e3f2fd; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--sky-blue);">
                        <strong>🍽️ Nutrition</strong><br>
                        Ensure balanced diet and clean drinking water availability
                    </div>
                    <div style="background: #fce4ec; padding: 1rem; border-radius: 8px; border-left: 4px solid #ec407a;">
                        <strong>🏠 Hygiene</strong><br>
                        Maintain clean shelters and proper waste management
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

        function scheduleVaccination() {
            alert('Vaccination scheduling form would open here!');
            // In a real application, this would open a vaccination scheduling form
        }

        function alertEmergency() {
            const confirmed = confirm('This will send emergency alerts to veterinarians. Continue?');
            if (confirmed) {
                alert('Emergency alert sent to veterinary services!');
                // In a real application, this would trigger emergency protocols
            }
        }

        function viewHealthRecord(recordId) {
            alert(`Viewing health record #${recordId}`);
            // In a real application, this would navigate to the health record details page
        }

        // Auto-refresh health alerts every 2 minutes
        setInterval(() => {
            console.log('Refreshing health data...');
            // In a real application, this would refresh health data and alerts
        }, 120000);
    </script>
</body>
</html>
