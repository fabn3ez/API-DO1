<?php
// verify-2fa.php

session_start();

// Check if user is authenticated and 2FA is required
if (!isset($_SESSION['user_id']) || !$_SESSION['require_2fa']) {
    header('Location: views/auth/login.php');
    exit;
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_code = trim($_POST['2fa_code'] ?? '');

    // Replace this with your actual 2FA verification logic
    // For example, using Google Authenticator or similar
    $expected_code = $_SESSION['2fa_expected_code'] ?? '';

    if ($input_code === $expected_code) {
        $_SESSION['2fa_verified'] = true;
        header('Location: views/dashboard/index.php');
        exit;
    } else {
        $error = 'Invalid 2FA code. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Two-Factor Authentication</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Two-Factor Authentication</h2>
        <form method="post" action="">
            <label for="2fa_code">Enter your 2FA code:</label>
            <input type="text" id="2fa_code" name="2fa_code" required autofocus>
            <button type="submit">Verify</button>
        </form>
        <?php if ($error): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>