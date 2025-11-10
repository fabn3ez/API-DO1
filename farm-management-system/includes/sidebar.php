<?php
// sidebar.php - Admin Sidebar Navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .admin-container {
        display: flex;
        min-height: calc(100vh - 80px);
    }
    
    .admin-sidebar {
        width: 260px;
        background: #f8f9fa;
        border-right: 1px solid #e0e0e0;
        padding: 2rem 0;
    }
    
    .nav-section {
        margin-bottom: 2rem;
    }
    
    .nav-title {
        padding: 0 1.5rem 0.5rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .nav-item {
        padding: 12px 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #333;
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }
    
    .nav-item:hover {
        background-color: #e8f5e9;
        color: #388e3c;
    }
    
    .nav-item.active {
        background-color: #e8f5e9;
        color: #388e3c;
        border-left-color: #388e3c;
        font-weight: 500;
    }
    
    .nav-icon {
        font-size: 1.2rem;
        width: 20px;
        text-align: center;
    }
    
    .nav-text {
        flex: 1;
    }
</style>

<div class="admin-sidebar">
    <div class="nav-section">
        <div class="nav-title">Main</div>
        <a href="dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Dashboard</span>
        </a>
    </div>
    
    <div class="nav-section">
        <div class="nav-title">Management</div>
        <a href="animals/animals_list.php" class="nav-item <?php echo strpos($current_page, 'animal') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">🐄</span>
            <span class="nav-text">Animals</span>
        </a>
        <a href="inventory/inventory_list.php" class="nav-item <?php echo strpos($current_page, 'inventory') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">📦</span>
            <span class="nav-text">Inventory</span>
        </a>
        <a href="financial/transactions.php" class="nav-item <?php echo strpos($current_page, 'transaction') !== false || strpos($current_page, 'sales') !== false || strpos($current_page, 'report') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">💰</span>
            <span class="nav-text">Financial</span>
        </a>
        <a href="users/users_list.php" class="nav-item <?php echo strpos($current_page, 'user') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">👥</span>
            <span class="nav-text">Users</span>
        </a>
    </div>
    
    <div class="nav-section">
        <div class="nav-title">System</div>
        <a href="settings.php" class="nav-item <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
            <span class="nav-icon">⚙️</span>
            <span class="nav-text">Settings</span>
        </a>
    </div>
</div>