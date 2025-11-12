<?php
// add_to_cart.php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$user_id = $_SESSION['user_id'];
$animal_id = isset($_GET['animal_id']) ? intval($_GET['animal_id']) : 0;
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

if ($animal_id < 1 || $quantity < 1) {
    http_response_code(400);
    echo 'Invalid animal or quantity.';
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

// Check if item already in cart
$stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND animal_id = ?");
$stmt->bind_param("ii", $user_id, $animal_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    // Update quantity
    $new_quantity = $row['quantity'] + $quantity;
    $update = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $new_quantity, $row['id']);
    $update->execute();
    $update->close();
} else {
    // Insert new cart item
    $insert = $conn->prepare("INSERT INTO cart_items (user_id, animal_id, quantity) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $user_id, $animal_id, $quantity);
    $insert->execute();
    $insert->close();
}
$stmt->close();
$conn->close();

// Redirect back to products or cart
header('Location: cart.php');
exit;
