<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_id'])) {
    $wishlist_id = intval($_POST['wishlist_id']);
    $user_id = $_SESSION['user_id'];

    $host = 'localhost';
    $db_user = 'root';
    $db_pass = '1234';
    $db_name = 'farm';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }

    // Only allow deletion if the wishlist item belongs to the logged-in user
    $stmt = $conn->prepare('DELETE FROM wishlist WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $wishlist_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header('Location: wishlist.php?message=Removed+from+wishlist');
    exit();
} else {
    header('Location: wishlist.php?error=Invalid+request');
    exit();
}
