<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - Under Maintenance</title>
    <link rel="stylesheet" href="/API-DO1/farm-management-system/assets/css/style.css">

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

        /* Under Maintenance Page */
        .dashboard-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--sky-blue), var(--wheat));
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 24px rgba(44, 62, 80, 0.15);
            padding: 40px 32px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            border-left: 6px solid var(--forest-green);
            animation: fadeIn 0.8s ease-in-out;
        }

        .dashboard-title {
            font-size: 2rem;
            color: var(--forest-green);
            margin-bottom: 18px;
            font-weight: 700;
        }

        .dashboard-text {
            color: var(--dark-brown);
            font-size: 1.1rem;
            margin-bottom: 28px;
        }

        .dashboard-btn {
            display: inline-block;
            padding: 10px 28px;
            background: var(--forest-green);
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .dashboard-btn:hover {
            background: var(--earth-brown);
            transform: translateY(-3px);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <h1 class="dashboard-title">🚧 Under Maintenance</h1>
            <p class="dashboard-text">
                This page is currently under maintenance.<br>
                Please check back later.
            </p>
            <a href="/API-DO1/farm-management-system/views/users/customer/dashboard.php" class="dashboard-btn">
                Return to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
