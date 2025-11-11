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
    <title>System Settings - Farm Management System</title>
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
        
        .tabs-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .tabs {
            display: flex;
            background: var(--wheat);
            border-bottom: 2px solid var(--earth-brown);
        }
        
        .tab-btn {
            padding: 15px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            background: var(--forest-green);
            color: white;
        }
        
        .tab-btn:hover:not(.active) {
            background: rgba(34, 139, 34, 0.1);
        }
        
        .tab-content {
            padding: 2rem;
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .settings-form {
            max-width: 600px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-brown);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--forest-green);
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
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .setting-card {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid var(--forest-green);
        }
        
        .setting-card h4 {
            margin-bottom: 0.5rem;
            color: var(--forest-green);
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
            <a href="reports.php" class="nav-item">
                <span>📈</span>
                <span>Reports</span>
            </a>
            <a href="settings.php" class="nav-item active">
                <span>⚙️</span>
                <span>Settings</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>⚙️</span>
                    <span>System Settings</span>
                </div>
            </div>

            <!-- Settings Tabs -->
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab-btn active" data-tab="general">🌐 General</button>
                    <button class="tab-btn" data-tab="notifications">🔔 Notifications</button>
                    <button class="tab-btn" data-tab="security">🔒 Security</button>
                    <button class="tab-btn" data-tab="backup">💾 Backup</button>
                    <button class="tab-btn" data-tab="farm">🚜 Farm Settings</button>
                </div>

                <!-- General Settings -->
                <div class="tab-content active" id="general">
                    <div class="settings-form">
                        <div class="form-group">
                            <label>Farm Name</label>
                            <input type="text" value="Green Valley Farm" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Farm Address</label>
                            <textarea class="form-control" rows="3">123 Farm Road, Agricultural Zone</textarea>
                        </div>
                        <div class="form-group">
                            <label>Contact Email</label>
                            <input type="email" value="info@greenvalleyfarm.com" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" value="+1 (555) 123-4567" class="form-control">
                        </div>
                        <button class="btn btn-primary">💾 Save General Settings</button>
                    </div>
                </div>

                <!-- Notifications Settings -->
                <div class="tab-content" id="notifications">
                    <div class="settings-form">
                        <h3 style="margin-bottom: 1.5rem; color: var(--forest-green);">Notification Preferences</h3>
                        <div class="settings-grid">
                            <div class="setting-card">
                                <h4>🔔 Email Notifications</h4>
                                <p>Receive email alerts for important events</p>
                                <label style="display: flex; align-items: center; gap: 8px; margin-top: 1rem;">
                                    <input type="checkbox" checked>
                                    <span>Enabled</span>
                                </label>
                            </div>
                            <div class="setting-card">
                                <h4>📱 SMS Alerts</h4>
                                <p>Get SMS for critical farm alerts</p>
                                <label style="display: flex; align-items: center; gap: 8px; margin-top: 1rem;">
                                    <input type="checkbox">
                                    <span>Enabled</span>
                                </label>
                            </div>
                            <div class="setting-card">
                                <h4>⚠️ Low Stock Alerts</h4>
                                <p>Notify when inventory is low</p>
                                <label style="display: flex; align-items: center; gap: 8px; margin-top: 1rem;">
                                    <input type="checkbox" checked>
                                    <span>Enabled</span>
                                </label>
                            </div>
                            <div class="setting-card">
                                <h4>🐄 Animal Health</h4>
                                <p>Health monitoring alerts</p>
                                <label style="display: flex; align-items: center; gap: 8px; margin-top: 1rem;">
                                    <input type="checkbox" checked>
                                    <span>Enabled</span>
                                </label>
                            </div>
                        </div>
                        <button class="btn btn-primary" style="margin-top: 1.5rem;">💾 Save Notification Settings</button>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="tab-content" id="security">
                    <div class="settings-form">
                        <h3 style="margin-bottom: 1.5rem; color: var(--forest-green);">Security Settings</h3>
                        <div class="form-group">
                            <label>Session Timeout (minutes)</label>
                            <input type="number" value="30" class="form-control" min="5" max="120">
                        </div>
                        <div class="form-group">
                            <label>Password Policy</label>
                            <select class="form-control">
                                <option>Standard (8+ characters)</option>
                                <option>Strong (12+ characters with special chars)</option>
                                <option>Very Strong (16+ characters with complexity)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Two-Factor Authentication</label>
                            <select class="form-control">
                                <option>Disabled</option>
                                <option>Optional</option>
                                <option>Required for Admins</option>
                                <option>Required for All Users</option>
                            </select>
                        </div>
                        <button class="btn btn-primary">💾 Save Security Settings</button>
                    </div>
                </div>

                <!-- Backup Settings -->
                <div class="tab-content" id="backup">
                    <div class="settings-form">
                        <h3 style="margin-bottom: 1.5rem; color: var(--forest-green);">Backup & Recovery</h3>
                        <div class="settings-grid">
                            <div class="setting-card">
                                <h4>💾 Auto Backup</h4>
                                <p>Automatically backup farm data</p>
                                <label style="display: flex; align-items: center; gap: 8px; margin-top: 1rem;">
                                    <input type="checkbox" checked>
                                    <span>Enabled</span>
                                </label>
                            </div>
                            <div class="setting-card">
                                <h4>🕒 Backup Frequency</h4>
                                <p>How often to backup data</p>
                                <select style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 0.5rem;">
                                    <option>Daily</option>
                                    <option>Weekly</option>
                                    <option>Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top: 2rem; padding: 1.5rem; background: #f0f8f0; border-radius: 8px;">
                            <h4 style="color: var(--forest-green); margin-bottom: 1rem;">🔄 Manual Backup</h4>
                            <p style="margin-bottom: 1rem;">Create a manual backup of all farm data</p>
                            <button class="btn btn-primary">📥 Create Backup Now</button>
                        </div>
                    </div>
                </div>

                <!-- Farm Settings -->
                <div class="tab-content" id="farm">
                    <div class="settings-form">
                        <h3 style="margin-bottom: 1.5rem; color: var(--forest-green);">Farm Configuration</h3>
                        <div class="form-group">
                            <label>Farm Type</label>
                            <select class="form-control">
                                <option>Dairy Farm</option>
                                <option>Poultry Farm</option>
                                <option>Mixed Farming</option>
                                <option>Organic Farm</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Measurement Units</label>
                            <select class="form-control">
                                <option>Metric (kg, liters)</option>
                                <option>Imperial (lbs, gallons)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Currency</label>
                            <select class="form-control">
                                <option>USD ($)</option>
                                <option>EUR (€)</option>
                                <option>GBP (£)</option>
                            </select>
                        </div>
                        <button class="btn btn-primary">💾 Save Farm Settings</button>
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

            // Tab functionality
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Update active tab button
                    tabBtns.forEach(tab => tab.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show active tab content
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                        if (content.id === tabId) {
                            content.classList.add('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>