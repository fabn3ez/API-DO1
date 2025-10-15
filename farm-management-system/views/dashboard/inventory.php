<?php
// inventory.php - Dashboard Inventory View

// Example: Fetch inventory data (replace with actual data source)
$inventory = [
    ['item' => 'Corn', 'quantity' => 120, 'unit' => 'kg'],
    ['item' => 'Wheat', 'quantity' => 80, 'unit' => 'kg'],
    ['item' => 'Fertilizer', 'quantity' => 50, 'unit' => 'bags'],
];

// HTML Output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #2c3e50; }
        table { border-collapse: collapse; width: 60%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Farm Inventory</h1>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventory as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['item']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                <td><?php echo htmlspecialchars($row['unit']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>