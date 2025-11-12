<?php
session_start();
require_once '../../auth/check_role.php';
check_role('admin');
require_once __DIR__ . '/../../../database/Database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users.php?error=Invalid+user+ID');
    exit;
}

$user_id = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();

// Prevent deleting self (optional, for safety)
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
    header('Location: users.php?error=Cannot+delete+your+own+account');
    exit;
}

$stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
if ($stmt->execute([$user_id])) {
    header('Location: users.php?deleted=1');
    exit;
} else {
    header('Location: users.php?error=Failed+to+delete+user');
    exit;
}
