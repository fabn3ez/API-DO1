<?php
require __DIR__ . '/../../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start(); // Start session to store login state
if (isset($_GET['redirect'])) {
    $role = htmlspecialchars($_GET['redirect']);
    echo "<p style='color: green; text-align:center;'>You have been logged out of your $role session.</p>";
}

// Database connection
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ✅ Ensure two-factor columns exist
$conn->query("
    ALTER TABLE users
    ADD COLUMN IF NOT EXISTS two_factor_code VARCHAR(6) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS two_factor_expires DATETIME DEFAULT NULL
");

// Initialize variables
$email = '';
$message = '';
$message_type = ''; // 'success' or 'error'

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = 'Error: Please enter both your email and password.';
        $message_type = 'error';
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Generate 6-digit 2FA code
                $code = rand(100000, 999999);
                $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                // Save 2FA code in database
                $update = $conn->prepare("UPDATE users SET two_factor_code = ?, two_factor_expires = ? WHERE id = ?");
                $update->bind_param("ssi", $code, $expires, $user['id']);
                $update->execute();

                // Send 2FA email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'sirwoossah@gmail.com'; // your Gmail
                    $mail->Password = 'trgl cuuq okpd bjuf'; // your Gmail App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('sirwoossah@gmail.com', 'Farm Management System');
                    $mail->addAddress($user['email'], $user['username']);

                    $mail->isHTML(true);
                    $mail->Subject = "Your Farm Management 2FA Verification Code";
                    $username = htmlspecialchars($user['username']);
                    $mail->Body = "
                        <div style='font-family: Poppins, sans-serif; color: #333; background-color: #f8fff8; padding: 20px; border-radius: 10px;'>
                            <h2 style='color: #388e3c;'>Hello $username,</h2>
                            <p>Your login verification code is:</p>
                            <h1 style='text-align:center; letter-spacing: 8px; color: #2e7d32;'>$code</h1>
                            <p>This code will expire in <strong>5 minutes</strong>. Please do not share it with anyone.</p>
                            <br>
                            <p>Best regards,<br><strong>Farm Management Team 🌱</strong></p>
                        </div>";
                    $mail->AltBody = "Hello $username, your verification code is: $code (valid for 5 minutes).";

                    $mail->send();

                    // Store user ID for verification
                    $_SESSION['temp_user_id'] = $user['id'];

                    // Redirect to verify page
                    header('Location: verify-2fa.php');
                    exit;
                } catch (Exception $e) {
                    $message = "Could not send 2FA email. Error: {$mail->ErrorInfo}";
                    $message_type = 'error';
                }
            } else {
                $message = 'Invalid password. Please try again.';
                $message_type = 'error';
            }
        } else {
            $message = 'No account found with that email.';
            $message_type = 'error';
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login | Farm Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0ffe0 0%, #c8e6c9 100%);
            color: #2c3e50;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #388e3c;
            margin-bottom: 25px;
            font-weight: 700;
        }
        .message {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        .message.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .message.error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
        }
        input:focus { border-color: #4CAF50; outline: none; }
        button {
            width: 100%;
            padding: 12px;
            background-color: #388e3c;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 15px;
        }
        button:hover { background-color: #2e7d32; }
        .link-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        .link-text a {
            color: #388e3c;
            text-decoration: none;
            font-weight: 600;
        }
        .link-text a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔑 Log In to Farm Management</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form id="login-form" action="" method="POST"> 
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Log In</button>
        </form>
        <div class="link-text">
            Don't have an account? <a href="register.php">Sign Up</a>
        </div>
    </div>
</body>
</html>
