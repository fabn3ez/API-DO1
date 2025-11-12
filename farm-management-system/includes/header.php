<?php
// header.php - Admin Header

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Farm Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* HEADER SOFT GREEN FARMING THEME - v20251112 */
        .admin-header-unique {
            background: linear-gradient(90deg, #b7e4c7 0%, #81c784 100%);
            color: #22543d;
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 18px rgba(129,199,132,0.10);
            font-size: 1.08rem;
            border-bottom: 2px solid #81c784;
            border-radius: 0 0 18px 18px;
        }
        .logo-section-unique {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .logo-icon-unique {
            font-size: 1.5rem;
            color: #81c784;
            filter: drop-shadow(0 2px 2px #b7e4c7);
        }
        .logo-text-unique {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #388e3c;
            text-shadow: 0 2px 6px rgba(129,199,132,0.10);
        }
        .admin-menu-unique {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .menu-item-unique {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease, box-shadow 0.2s;
            font-size: 1.08rem;
            background: #eafaf1;
            color: #22543d;
        }
        .menu-item-unique:hover {
            background-color: #b7e4c7;
            color: #388e3c;
            box-shadow: 0 2px 8px rgba(129,199,132,0.10);
        }
        .notification-badge-unique {
            background: #81c784;
            color: #fff;
            border-radius: 50%;
            padding: 3px 8px;
            font-size: 0.8rem;
            margin-left: 4px;
            font-weight: bold;
        }
    </style>
    </style>
    </style>
    </style>
    </style>
    </style>
    </style>
</head>
<body>
    <header class="admin-header-unique">
        <div class="logo-section-unique">
            <div class="logo-icon-unique">�</div>
            <div class="logo-text-unique">Farm Management System</div>
        </div>
        <div class="admin-menu-unique">
            <div class="menu-item-unique" onclick="window.location.href='/API-DO1/farm-management-system/views/users/farmer/dashboard.php'" style="cursor:pointer;">
                <span>🏠</span>
                <span>Back to Dashboard</span>
            </div>
            <div class="menu-item-unique" onclick="window.location.href='/API-DO1/farm-management-system/views/users/farmer/notifications.php'" style="cursor:pointer;">
                <span>🔔</span>
                <span>Notifications</span>
                <span class="notification-badge-unique">3</span>
            </div>
            <div class="menu-item-unique" onclick="window.location.href='/API-DO1/farm-management-system/views/users/farmer/profile.php'" style="cursor:pointer;">
                <span>👤</span>
                <span><?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
            </div>
            <div class="menu-item-unique" onclick="window.location.href='/API-DO1/farm-management-system/views/users/auth/logout.php'" style="cursor:pointer;">
                <span>🚪</span>
                <span>Logout</span>
            </div>
        </div>
    </header>