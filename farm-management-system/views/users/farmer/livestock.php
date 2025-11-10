<?php
// livestock.php - Livestock Dashboard View

// Sample data (replace with database queries in production)
$livestock = [
    ['id' => 1, 'type' => 'Cow', 'breed' => 'Holstein', 'age' => 4, 'status' => 'Healthy'],
    ['id' => 2, 'type' => 'Goat', 'breed' => 'Boer', 'age' => 2, 'status' => 'Sick'],
    ['id' => 3, 'type' => 'Chicken', 'breed' => 'Leghorn', 'age' => 1, 'status' => 'Healthy'],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Livestock Dashboard</title>
    <link rel="stylesheet" href="/API-DO1/farm-management-system/assets/styles.css">
    <style>
        table { border-collapse: collapse; width: 80%; margin: 20px auto; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: center; }
        th { background: #f4f4f4; }
        .status-Healthy { color: green; }
        .status-Sick { color: red; }
    </style>
</head>
<body>
    <h1 style="text-align:center;">Livestock Dashboard</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Breed</th>
                <th>Age (years)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($livestock as $animal): ?>
                <tr>
                    <td><?= htmlspecialchars($animal['id']) ?></td>
                    <td><?= htmlspecialchars($animal['type']) ?></td>
                    <td><?= htmlspecialchars($animal['breed']) ?></td>
                    <td><?= htmlspecialchars($animal['age']) ?></td>
                    <td class="status-<?= htmlspecialchars($animal['status']) ?>">
                        <?= htmlspecialchars($animal['status']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>