<?php
// Show messages for add/delete actions
$message = '';
if (isset($_GET['user_added'])) {
    $message = 'User added successfully!';
}
if (isset($_GET['deleted'])) {
    $message = 'User deleted successfully!';
}
if (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
}
?>

<?php if ($message): ?>
    <div class="message" style="margin:1rem auto;max-width:600px;text-align:center;color:#28a745;background:#eafbe7;padding:0.7rem 1rem;border-radius:5px;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>
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

// Get all users
$users = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$total_users = count($users);
$admin_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='admin'")->fetch_assoc()['count'];
$farmer_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='farmer'")->fetch_assoc()['count'];
$customer_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='customer'")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Farm Management System</title>
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
        
        .users-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            background: var(--forest-green);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .users-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .users-table tr:hover {
            background: #f9f9f9;
        }

        .role-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .role-admin { 
            background: linear-gradient(135deg, #ff6b6b, #ee5a52); 
            color: white; 
        }
        .role-farmer { 
            background: linear-gradient(135deg, #4ecdc4, #44a08d); 
            color: white; 
        }
        .role-customer { 
            background: linear-gradient(135deg, #45b7d1, #3a92ab); 
            color: white; 
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
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

        /* Search and Filter */
        .search-filter {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--forest-green);
        }

        .filter-select {
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            background: white;
            min-width: 150px;
        }

        .user-status {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }

        .status-online { color: #28a745; }
        .status-offline { color: #6c757d; }
        .status-busy { color: #ffc107; }

        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
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
            <a href="users.php" class="nav-item active">
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

        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>👥</span>
                    <span>User Management</span>
                </div>
                <a href="add_user.php" class="btn btn-primary">
                    <span>➕</span>
                    <span>Add New User</span>
                </a>
            </div>

            <!-- Quick Actions -->
            <!-- <div class="quick-actions">
                <a href="add_user.php" class="action-btn-large">
                    <span class="action-icon">👤</span>
                    <span>Add User</span>
                </a>
                <a href="user_roles.php" class="action-btn-large">
                    <span class="action-icon">🎭</span>
                    <span>Manage Roles</span>
                </a>
                <a href="user_permissions.php" class="action-btn-large">
                    <span class="action-icon">🔐</span>
                    <span>Permissions</span>
                </a>
                <a href="user_activity.php" class="action-btn-large">
                    <span class="action-icon">📈</span>
                    <span>User Activity</span>
                </a>
            </div> -->

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👑</div>
                    <div class="stat-number"><?php echo $admin_count; ?></div>
                    <div class="stat-label">Administrators</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🌾</div>
                    <div class="stat-number"><?php echo $farmer_count; ?></div>
                    <div class="stat-label">Farmers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-number"><?php echo $customer_count; ?></div>
                    <div class="stat-label">Customers</div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="🔍 Search users by name, email, or role...">
                </div>
                <select class="filter-select">
                    <option>All Roles</option>
                    <option>Admin</option>
                    <option>Farmer</option>
                    <option>Customer</option>
                </select>
                <select class="filter-select">
                    <option>Sort by: Newest First</option>
                    <option>Sort by: Oldest First</option>
                    <option>Sort by: Name A-Z</option>
                    <option>Sort by: Name Z-A</option>
                </select>
                <button class="btn btn-primary">
                    <span>🔄</span>
                    <span>Apply Filters</span>
                </button>
            </div>

            <!-- Users Table -->
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--sky-blue), var(--forest-green)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo $user['username']; ?></strong>
                                        <div class="user-status">
                                            <span class="status-online">●</span>
                                            <span>Active</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div><?php echo $user['email']; ?></div>
                                    <div style="font-size: 0.8rem; color: #666;">Last login: 2 hours ago</div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php 
                                    $roleIcons = [
                                        'admin' => '👑',
                                        'farmer' => '👨‍🌾', 
                                        'customer' => '🛒'
                                    ];
                                    echo $roleIcons[$user['role']] . ' ' . ucfirst($user['role']);
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-success" style="background: #d4edda; color: #155724; padding: 6px 12px; border-radius: 12px; font-size: 0.8rem;">
                                    ✅ Active
                                </span>
                            </td>
                            <td>
                                <div>
                                    <div><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                                    <div style="font-size: 0.8rem; color: #666;"><?php echo date('g:i A', strtotime($user['created_at'])); ?></div>
                                </div>
                            </td>
                            <td class="action-buttons">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn">✏️ Edit</a>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <!-- Sample additional users for demonstration -->
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b6b, #ee5a52); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                        M
                                    </div>
                                    <div>
                                        <strong>Maria Garcia</strong>
                                        <div class="user-status">
                                            <span class="status-offline">●</span>
                                            <span>Offline</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div>maria@greenvalleyfarm.com</div>
                                    <div style="font-size: 0.8rem; color: #666;">Last login: 2 days ago</div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-farmer">
                                    👨‍🌾 Farmer
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-warning" style="background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 12px; font-size: 0.8rem;">
                                    ⚠️ Inactive
                                </span>
                            </td>
                            <td>
                                <div>
                                    <div>Jan 10, 2024</div>
                                    <div style="font-size: 0.8rem; color: #666;">10:30 AM</div>
                                </div>
                            </td>
                            <td class="action-buttons">
                                <a href="edit_user.php?id=6" class="action-btn edit-btn">✏️ Edit</a>
                                <a href="delete_user.php?id=6" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">🗑️</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
<!-- 
            User Analytics
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="chart-title">
                        <span>📊</span>
                        <span>USER ROLE DISTRIBUTION</span>
                    </div>
                    <div style="height: 250px; background: #f9f9f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666; border: 2px dashed #ddd;">
                        [Role Distribution Chart]<br>
                        Admins 👑 | Farmers 👨‍🌾 | Customers 🛒
                    </div>
                </div>
                <div class="chart-container">
                    <div class="chart-title">
                        <span>📈</span>
                        <span>USER ACTIVITY TREND</span>
                    </div>
                    <div style="height: 250px; background: #f9f9f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #666; border: 2px dashed #ddd;">
                        [Activity Trend Chart]<br>
                        User logins and activity over time
                    </div>
                </div>
            </div>

            <!-- User Management Tips -->
            <div class="chart-container" style="margin-top: 1.5rem;">
                <div class="chart-title">
                    <span>💡</span>
                    <span>USER MANAGEMENT TIPS</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div style="padding: 1rem; background: #f0f8f0; border-radius: 8px; border-left: 4px solid var(--forest-green);">
                        <strong>🔐 Role Management</strong>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">Assign appropriate roles to maintain security and functionality.</p>
                    </div>
                    <div style="padding: 1rem; background: #f0f8f0; border-radius: 8px; border-left: 4px solid var(--forest-green);">
                        <strong>📧 Communication</strong>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">Keep users informed about system updates and changes.</p>
                    </div>
                    <div style="padding: 1rem; background: #f0f8f0; border-radius: 8px; border-left: 4px solid var(--forest-green);">
                        <strong>🛡️ Security</strong>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">Regularly review user access and permissions.</p>
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

            // Search functionality
            const searchInput = document.querySelector('.search-input');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.users-table tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Role filter functionality
            const roleFilter = document.querySelector('.filter-select');
            roleFilter.addEventListener('change', function() {
                const selectedRole = this.value.toLowerCase();
                const rows = document.querySelectorAll('.users-table tbody tr');
                
                rows.forEach(row => {
                    if (selectedRole === 'all roles' || selectedRole === '') {
                        row.style.display = '';
                    } else {
                        const roleCell = row.querySelector('.role-badge');
                        if (roleCell && roleCell.textContent.toLowerCase().includes(selectedRole)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });

            // Add confirmation for delete actions
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });

            // Simulate user status updates
            setInterval(() => {
                const statusIndicators = document.querySelectorAll('.user-status span:first-child');
                statusIndicators.forEach(indicator => {
                    if (Math.random() > 0.7) {
                        indicator.className = Math.random() > 0.5 ? 'status-online' : 'status-offline';
                    }
                });
            }, 10000);
        });
    </script>
</body>
</html>