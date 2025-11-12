<?php
// view_animal.php
session_start();
require_once '../../auth/check_role.php';
check_role('customer');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo 'Invalid animal ID.';
    exit;
}

$animal_id = intval($_GET['id']);

$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM animals WHERE id = ?");
$stmt->bind_param("i", $animal_id);
$stmt->execute();
$animal = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$animal) {
    echo '<h2 style="color:red;text-align:center;">Animal not found.</h2>';
    exit;
}

// Animal icons
$icons = [
    'Cow' => '🐄', 'Cattle' => '🐂', 'Hen' => '🐔', 'Cock' => '🐓',
    'Goat' => '🐐', 'Sheep' => '🐑', 'Rabbit' => '🐇', 'Horse' => '🐎',
    'Dog' => '🐕', 'Cat' => '🐈', 'Fish' => '🐟', 'Turkey' => '🦃',
    'Goose' => '🦆'
];
$icon = $icons[$animal['type']] ?? '🐾';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Details - Farm Management System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; color: #333; }
        .container { max-width: 600px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #228B22; text-align: center; margin-bottom: 1.5rem; }
        .animal-icon { font-size: 3rem; display: block; text-align: center; margin-bottom: 1rem; }
        .animal-details { margin-bottom: 2rem; }
        .detail-row { display: flex; justify-content: space-between; padding: 0.7rem 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: 600; color: #555; }
        .detail-value { color: #228B22; }
        .actions { text-align: center; margin-top: 2rem; }
        .btn { padding: 10px 25px; background: #228B22; color: #fff; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer; text-decoration: none; margin: 0 10px; }
        .btn:hover { background: #8B4513; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Animal Details</h1>
        <div class="animal-icon"><?php echo $icon; ?></div>
        <div class="animal-details">
            <div class="detail-row"><span class="detail-label">Type:</span> <span class="detail-value"><?php echo htmlspecialchars($animal['type']); ?></span></div>
            <div class="detail-row"><span class="detail-label">Breed:</span> <span class="detail-value"><?php echo htmlspecialchars($animal['breed']); ?></span></div>
            <div class="detail-row"><span class="detail-label">Gender:</span> <span class="detail-value"><?php echo htmlspecialchars($animal['gender']); ?></span></div>
            <div class="detail-row"><span class="detail-label">Available:</span> <span class="detail-value"><?php echo $animal['number']; ?> animals</span></div>
            <div class="detail-row"><span class="detail-label">Avg Weight:</span> <span class="detail-value"><?php echo htmlspecialchars($animal['avg_weight']); ?></span></div>
            <div class="detail-row"><span class="detail-label">Shed:</span> <span class="detail-value"><?php echo htmlspecialchars($animal['shed_no']); ?></span></div>
        </div>
        <div class="actions">
            <a href="add_to_cart.php?animal_id=<?php echo $animal['id']; ?>&quantity=1" class="btn">🛒 Add to Cart</a>
            <a href="products.php" class="btn" style="background:#8B4513;">&larr; Back to Products</a>
        </div>
    </div>
</body>
</html>
