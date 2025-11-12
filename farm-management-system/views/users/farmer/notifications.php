<?php
session_start();
require_once '../../auth/check_role.php';
check_role('farmer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f4ecd8 0%, #eaffea 100%);
            color: #4e3b1f;
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        }
        .container {
            background: #fffbe6;
            border-radius: 16px;
            box-shadow: 0 6px 32px rgba(67,234,94,0.12);
            padding: 40px 32px;
            margin: 60px auto 0 auto;
            max-width: 700px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            color: #388e3c;
            font-size: 2rem;
            margin-bottom: 22px;
            font-family: 'Poppins', serif;
            letter-spacing: 1px;
            text-align: center;
        }
        .notification-list {
            width: 100%;
            margin-top: 24px;
        }
        .notification-item {
            background: #eaffea;
            border-left: 5px solid #388e3c;
            padding: 18px 16px;
            margin-bottom: 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(56,142,60,0.08);
        }
        .notification-title {
            font-weight: 700;
            color: #388e3c;
            margin-bottom: 6px;
        }
        .notification-date {
            font-size: 0.95rem;
            color: #a67c52;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Notifications</h1>
        <div class="notification-list">
            <div class="notification-item">
                <div class="notification-title">Welcome to the Farm Management System!</div>
                <div class="notification-date">Nov 12, 2025</div>
                <div>Get started by adding your animals, sheds, and health records.</div>
            </div>
            <div class="notification-item">
                <div class="notification-title">System Update</div>
                <div class="notification-date">Nov 10, 2025</div>
                <div>New features have been added to shed management and health records.</div>
            </div>
            <div class="notification-item">
                <div class="notification-title">Reminder</div>
                <div class="notification-date">Nov 8, 2025</div>
                <div>Don't forget to check your animals' vaccination schedules.</div>
            </div>
        </div>
        <a href="dashboard.php" class="btn-primary">Back to Dashboard</a>
    </div>
</body>
</html>
