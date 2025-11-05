<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);}
        h2 { text-align: center; margin-bottom: 24px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; }
        input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;}
        button { width: 100%; padding: 10px; background: #28a745; color: #fff; border: none; border-radius: 4px; font-size: 16px;}
        .message { margin-bottom: 16px; color: #d9534f; text-align: center;}
        .success { color: #28a745; }
        a { display: block; text-align: center; margin-top: 16px; color: #007bff; text-decoration: none;}
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php if (isset($_SESSION['forgot_message'])): ?>
            <div class="message <?php echo $_SESSION['forgot_success'] ? 'success' : ''; ?>">
                <?php
                    echo htmlspecialchars($_SESSION['forgot_message']);
                    unset($_SESSION['forgot_message'], $_SESSION['forgot_success']);
                ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="/API-DO1/farm-management-system/controllers/auth/forgot-password-handler.php">
            <div class="form-group">
                <label for="email">Enter your email address:</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <button type="submit">Send Reset Link</button>
        </form>
        <a href="/API-DO1/farm-management-system/views/auth/login.php">Back to Login</a>
    </div>
</body>
</html>