<?php
// header.php - Admin Header
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Farm Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-icon {
            font-size: 1.8rem;
        }
        
        .logo-text {
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .admin-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .menu-item:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .notification-badge {
            background: #ff4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="logo-section">
            <div class="logo-icon">🌱</div>
            <div class="logo-text">Farm Management System</div>
            <div style="margin-left: 20px; font-size: 0.9rem; opacity: 0.9;">Admin Panel</div>
        </div>
        
        <div class="admin-menu">
            <div class="menu-item">
                <span>🔔</span>
                <span>Notifications</span>
                <span class="notification-badge">3</span>
            </div>
            <div class="menu-item">
                <span>👤</span>
                <span><?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
            </div>
            <div class="menu-item" onclick="location.href='../auth/logout.php'">
                <span>🚪</span>
                <span>Logout</span>
            </div>
        </div>
    </header>