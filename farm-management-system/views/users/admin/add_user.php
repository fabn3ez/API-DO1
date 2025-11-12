<?php
session_start();
require_once '../../auth/check_role.php';
check_role('admin');
require_once __DIR__ . '/../../../database/Database.php';

$db = new Database();
$conn = $db->getConnection();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $valid_roles = ['admin', 'farmer', 'customer'];
    if (!in_array($role, $valid_roles)) {
        $role = 'customer';
    }

    if ($username && $email && $password) {
        // Check if user exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $message = 'Username or email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            if ($stmt->execute([$username, $email, $hashed, $role])) {
                header('Location: users.php?user_added=1');
                exit;
            } else {
                $message = 'Error adding user.';
            }
        }
    } else {
        $message = 'All fields are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <link rel="stylesheet" href="../../../public/assets/css/style.css">
    <style>
        .form-container { max-width: 400px; margin: 2rem auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px #eee; }
        .form-container h2 { text-align: center; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; }
        .form-group button { width: 100%; padding: 0.7rem; background: #28a745; color: #fff; border: none; border-radius: 4px; font-size: 1rem; }
        .form-group button:hover { background: #218838; }
        .message { text-align: center; margin-bottom: 1rem; color: #d9534f; }
        .success { color: #28a745; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New User</h2>
        <?php if ($message): ?>
            <div class="message <?php echo ($message === 'User added successfully!') ? 'success' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="customer">Customer</option>
                    <option value="farmer">Farmer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Add User</button>
            </div>
        </form>
    </div>
</body>
</html>
