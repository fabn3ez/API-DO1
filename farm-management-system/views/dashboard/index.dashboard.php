<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farm Management Dashboard</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; }
        .dashboard-container { max-width: 1200px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);}
        h1 { margin-bottom: 24px; }
        .stats { display: flex; gap: 24px; margin-bottom: 32px; }
        .stat-card { flex: 1; background: #e9f5e9; padding: 20px; border-radius: 6px; text-align: center; }
        .stat-card h2 { margin: 0 0 8px 0; font-size: 2em; }
        .actions { display: flex; gap: 16px; }
        .action-btn { padding: 12px 24px; background: #4caf50; color: #fff; border: none; border-radius: 4px; text-decoration: none; font-weight: bold; }
        .action-btn:hover { background: #388e3c; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Farm Management Dashboard</h1>
        <div class="stats">
            <div class="stat-card">
                <h2>120</h2>
                <p>Animals</p>
            </div>
            <div class="stat-card">
                <h2>45</h2>
                <p>Crops</p>
            </div>
            <div class="stat-card">
                <h2>8</h2>
                <p>Workers</p>
            </div>
            <div class="stat-card">
                <h2>5</h2>
                <p>Machinery</p>
            </div>
        </div>
        <div class="actions">
            <a href="/animals" class="action-btn">Manage Animals</a>
            <a href="/workers" class="action-btn">Manage Workers</a>
            <a href="/machinery" class="action-btn">Manage Machinery</a>
        </div>
    </div>
</body>
</html>