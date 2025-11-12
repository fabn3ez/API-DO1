<?php
// cancel_order.php
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

// Only allow cancelling if the order belongs to the user and is not already cancelled/completed
$stmt = $conn->prepare("UPDATE sales_orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();
$conn->close();

if ($affected > 0) {
    $_SESSION['order_message'] = 'Order #' . $order_id . ' has been cancelled.';
} else {
    $_SESSION['order_message'] = 'Unable to cancel order. It may already be completed, cancelled, or not belong to you.';
}

header('Location: orders.php');
exit;
