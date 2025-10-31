<?php
session_start();

// Check if session is active
if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
} else {
    $role = 'auth';
}

// Destroy the session
session_unset();
session_destroy();

// Redirect user based on role
switch ($role) {
    case 'admin':
        header("Location: ../auth/login.php?redirect=admin");
        break;
    case 'farmer':
        header("Location: ../auth/login.php?redirect=farmer");
        break;
    case 'manager':
        header("Location: ../auth/login.php?redirect=manager");
        break;
    default:
        header("Location: ../auth/login.php");
        break;
}
exit();
?>
