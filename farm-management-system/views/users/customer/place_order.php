<?php
session_start();
require_once '../../auth/check_role.php';
check_role('customer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

$address = trim($_POST['address'] ?? '');
$payment_method = $_POST['payment_method'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($address) || empty($payment_method)) {
    $_SESSION['order_error'] = 'Please provide all required information.';
    header('Location: checkout.php');
    exit;
}

$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Fetch cart items
$cart_query = $conn->prepare("
    SELECT ci.quantity, a.id as animal_id, a.type, a.breed, a.avg_weight
    FROM cart_items ci
    JOIN animals a ON ci.animal_id = a.id
    WHERE ci.user_id = ?
");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$cart_items = $cart_query->get_result()->fetch_all(MYSQLI_ASSOC);
$cart_query->close();

if (empty($cart_items)) {
    $_SESSION['order_error'] = 'Your cart is empty.';
    header('Location: checkout.php');
    exit;
}

// Calculate total
$base_prices = [
    'Cow' => 50000, 'Cattle' => 45000, 'Hen' => 800, 'Cock' => 1200,
    'Goat' => 8000, 'Sheep' => 7000, 'Rabbit' => 1500, 'Horse' => 80000,
    'Dog' => 10000, 'Cat' => 5000, 'Fish' => 300, 'Turkey' => 4000,
    'Goose' => 3500
];
$subtotal = 0;
foreach ($cart_items as $item) {
    $base_price = $base_prices[$item['type']] ?? 5000;
    $weight_factor = 1.0;
    if (preg_match('/(\d+\.?\d*)\s*kg/i', $item['avg_weight'], $matches)) {
        $weight = floatval($matches[1]);
        $weight_factor = $weight / 100;
    }
    $subtotal += $base_price * $weight_factor * $item['quantity'];
}
$shipping_fee = $subtotal > 0 ? 200 : 0;
$discount_amount = $_SESSION['discount_amount'] ?? 0;
$total = $subtotal + $shipping_fee - $discount_amount;

// Insert order
$order_stmt = $conn->prepare("INSERT INTO sales_orders (user_id, order_type, customer_supplier, total, order_date, status) VALUES (?, 'sale', ?, ?, CURDATE(), 'pending')");
$order_stmt->bind_param("isd", $user_id, $address, $total);
$order_stmt->execute();
$order_id = $order_stmt->insert_id;
$order_stmt->close();

// Insert order items
foreach ($cart_items as $item) {
    $unit_price = $base_prices[$item['type']] ?? 5000;
    $weight_factor = 1.0;
    if (preg_match('/(\d+\.?\d*)\s*kg/i', $item['avg_weight'], $matches)) {
        $weight = floatval($matches[1]);
        $weight_factor = $weight / 100;
    }
    $unit_price = $unit_price * $weight_factor;
    $item_stmt = $conn->prepare("INSERT INTO sales_order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    $item_stmt->bind_param("iiid", $order_id, $item['animal_id'], $item['quantity'], $unit_price);
    $item_stmt->execute();
    $item_stmt->close();
}

// Clear cart
$clear_stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
$clear_stmt->bind_param("i", $user_id);
$clear_stmt->execute();
$clear_stmt->close();

$conn->close();

// Success message and redirect
$_SESSION['order_success'] = 'Your order has been placed successfully!';
header('Location: orders.php');
exit;
