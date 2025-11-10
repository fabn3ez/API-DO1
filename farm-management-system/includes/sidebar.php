<?php
// Sidebar layout for Farm Management System
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Farm Management</h2>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li><a href="/API-DO1/farm-management-system/dashboard.php">Dashboard</a></li>
            <li><a href="/API-DO1/farm-management-system/views/farms/index.php">Farms</a></li>
            <li><a href="/API-DO1/farm-management-system/views/crops/index.php">Crops</a></li>
            <li><a href="/API-DO1/farm-management-system/views/livestock/index.php">Livestock</a></li>
            <li><a href="/API-DO1/farm-management-system/views/users/index.php">Users</a></li>
            <li><a href="/API-DO1/farm-management-system/views/reports/index.php">Reports</a></li>
            <li><a href="/API-DO1/farm-management-system/logout.php">Logout</a></li>
        </ul>
    </nav>
</aside>

<style>
.sidebar {
    width: 220px;
    background: #2c3e50;
    color: #fff;
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px 0;
}
.sidebar-header {
    text-align: center;
    margin-bottom: 30px;
}
.sidebar-nav ul {
    list-style: none;
    padding: 0;
}
.sidebar-nav ul li {
    margin: 15px 0;
}
.sidebar-nav ul li a {
    color: #fff;
    text-decoration: none;
    padding: 10px 20px;
    display: block;
    border-radius: 4px;
    transition: background 0.2s;
}
.sidebar-nav ul li a:hover {
    background: #34495e;
}
</style>