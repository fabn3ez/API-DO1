<?php
session_start();
require_once __DIR__ . '/../../auth/check_role.php';
check_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Under Maintenance - Animal Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .maintenance-container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            text-align: center;
            max-width: 400px;
        }
        .maintenance-container h1 {
            font-size: 2.2rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .maintenance-container p {
            color: #555;
            margin-bottom: 30px;
        }
        .btn-back {
            background: #3498db;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-back:hover {
            background: #2980b9;
        }
        .maintenance-icon {
            font-size: 3rem;
            margin-bottom: 18px;
            color: #f39c12;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">🚧</div>
        <h1>Under Maintenance</h1>
        <p>The Animal Report page is currently under maintenance.<br>
           Please check back later.</p>
        <button class="btn-back" onclick="window.history.back()">Go Back</button>
    </div>
</body>
</html>
