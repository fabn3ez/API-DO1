<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

// Validate animal_id
if (!isset($_GET['animal_id']) || !is_numeric($_GET['animal_id'])) {
    header('Location: products.php?error=Invalid+animal+ID');
    exit();
}

$user_id = $_SESSION['user_id'];
$animal_id = intval($_GET['animal_id']);

// Database connection
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Check if already in wishlist
$check = $conn->prepare('SELECT id FROM wishlist WHERE user_id = ? AND animal_id = ?');
$check->bind_param('ii', $user_id, $animal_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    $conn->close();
    header('Location: products.php?message=Already+in+wishlist');
    exit();
}
$check->close();

// Insert into wishlist
$insert = $conn->prepare('INSERT INTO wishlist (user_id, animal_id, added_at) VALUES (?, ?, NOW())');
$insert->bind_param('ii', $user_id, $animal_id);
if ($insert->execute()) {
    $insert->close();
    $conn->close();
    header('Location: wishlist.php?message=Added+to+wishlist');
    exit();
} else {
    $insert->close();
    $conn->close();
    header('Location: products.php?error=Could+not+add+to+wishlist');
    exit();
}
