<?php
// order_details.php
session_start();
require_once '../../auth/check_role.php';
check_role('customer');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo 'Invalid order ID.';
    exit;
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Fetch order
$stmt = $conn->prepare("SELECT * FROM sales_orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo '<h2 style="color:red;text-align:center;">Order not found or access denied.</h2>';
    $conn->close();
    exit;
}

// Fetch order items
$item_stmt = $conn->prepare("
    SELECT soi.quantity, soi.unit_price, a.type, a.breed, a.avg_weight
    FROM sales_order_items soi
    JOIN animals a ON soi.product_id = a.id
    WHERE soi.order_id = ?
");
$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$order_items = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$item_stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Farm Management System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; color: #333; }
        .container { max-width: 700px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 2rem; }
        h1 { color: #228B22; text-align: center; margin-bottom: 1.5rem; }
        .order-meta { margin-bottom: 2rem; }
        .order-meta span { display: inline-block; margin-right: 2rem; font-weight: 600; }
        .order-status { padding: 4px 12px; border-radius: 8px; font-size: 0.95rem; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #228B22; color: #fff; }
        .total-row td { font-weight: bold; color: #228B22; }
        .back-link { display: inline-block; margin-top: 1rem; color: #228B22; text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Order #<?php echo $order['id']; ?> Details</h1>
        <div class="order-meta">
            <span><strong>Date:</strong> <?php echo date('M j, Y', strtotime($order['order_date'])); ?></span>
            <span><strong>Status:</strong> <span class="order-status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></span>
            <span><strong>Total:</strong> KSh <?php echo number_format($order['total'], 2); ?></span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Breed</th>
                    <th>Weight</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['type']); ?></td>
                    <td><?php echo htmlspecialchars($item['breed']); ?></td>
                    <td><?php echo htmlspecialchars($item['avg_weight']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>KSh <?php echo number_format($item['unit_price'], 2); ?></td>
                    <td>KSh <?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">Total:</td>
                    <td>KSh <?php echo number_format($order['total'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
        <a href="orders.php" class="back-link">&larr; Back to Orders</a>
    </div>
</body>
</html>
