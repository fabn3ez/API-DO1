<?php
$animals = [
    ['name' => 'Cow', 'type' => 'Mammal', 'age' => 5],
    ['name' => 'Chicken', 'type' => 'Bird', 'age' => 2],
    ['name' => 'Sheep', 'type' => 'Mammal', 'age' => 3],
    ['name' => 'Goat', 'type' => 'Mammal', 'age' => 4],
    ['name' => 'Duck', 'type' => 'Bird', 'age' => 1]
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Farm Animals</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; }
        h1 { color: #4CAF50; }
        table { border-collapse: collapse; width: 60%; margin: 30px auto; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #e0ffe0; }
    </style>
</head>
<body>
    <h1 style="text-align:center;">Available Animals in the Farm</h1>
    <table>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Age</th>
        </tr>
        <?php foreach ($animals as $animal): ?>
        <tr>
            <td><?php echo htmlspecialchars($animal['name']); ?></td>
            <td><?php echo htmlspecialchars($animal['type']); ?></td>
            <td><?php echo htmlspecialchars($animal['age']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>