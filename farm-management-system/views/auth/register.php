<?php
// =============================
// DATABASE CONNECTION
// =============================
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// =============================
// IMPORT PHPMailer
// =============================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../vendor/autoload.php';


// =============================
// INITIALIZE VARIABLES
// =============================
$username = '';
$email = '';
$message = '';
$message_type = ''; // success or error

// =============================
// FORM SUBMISSION LOGIC
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = 'Please fill in all fields.';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'Error: Passwords do not match.';
        $message_type = 'error';
    } else {
        // Check if email already exists
        $check_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_query->bind_param("s", $email);
        $check_query->execute();
        $check_query->store_result();

        if ($check_query->num_rows > 0) {
            $message = 'Error: Email already registered.';
            $message_type = 'error';
        } else {
            // Hash password and insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                // =============================
                // SEND EMAIL TO NEW USER
                // =============================
                $mail = new PHPMailer(true);

                try {
                    // SMTP SETTINGS
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com'; // Use your mail host
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'sirwoossah@gmail.com'; // your sender email
                    $mail->Password   = 'hrfv ksao fhyk hwgm';    // use app password if Gmail
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    // RECIPIENTS
                    $mail->setFrom('sirwoossah@gmail.com', 'Farm Management System');
                    $mail->addAddress($email, $username);
                    //$mail->addReplyTo('elizabeth.githiri@strathmore.edu', 'Farm Management Support');

                    // EMAIL CONTENT
                    $mail->isHTML(true);
                    $mail->Subject = "Welcome to Farm Management System!";
                    $mail->Body = "
                    <div style='font-family: Poppins, sans-serif; color: #333; background-color: #f8fff8; padding: 20px; border-radius: 10px;'>
                        <h2 style='color: #388e3c;'>Welcome to Farm Management System, $username!</h2>
                        <p>We’re thrilled to have you on board. 🎉</p>
                        <p>You can now log in to your account and start managing your farm activities efficiently.</p>
                        <br>
                        <a href='http://localhost/API-DO1/farm-management-system/views/auth/login.php' 
                            style='display:inline-block; background-color:#388e3c; color:white; padding:10px 20px; border-radius:8px; text-decoration:none;'>
                            Login to Your Account
                        </a>
                        <br><br>
                        <p>Best regards,<br><strong>The Farm Management Team 🌱</strong></p>
                    </div>";
                    $mail->AltBody = "Welcome to Farm Management System, $username! You can now log in and start managing your farm activities.";

                    $mail->send();
                    echo "<script>
                        alert('✅ Registration successful! A welcome email has been sent.');
                        window.location.href = 'login.php';
                    </script>";
                    exit;
                } catch (Exception $e) {
                    echo "<script>
                        alert('Registration successful, but email could not be sent: {$mail->ErrorInfo}');
                        window.location.href = 'login.php';
                    </script>";
                    exit;
                }
            } else {
                $message = 'Error: Could not register user. Please try again.';
                $message_type = 'error';
            }

            $stmt->close();
        }
        $check_query->close();
    }
}

$conn->close();
?>

<!-- 
=============================
HTML FORM (same as before)
============================= 
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Farm Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0ffe0 0%, #c8e6c9 100%);
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }
        .container {
            background: #fff; padding: 30px 40px; border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px;
        }
        h2 { text-align: center; color: #388e3c; margin-bottom: 25px; font-weight: 700; }
        .message { padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600; }
        .message.success { background-color: #e8f5e9; color: #2e7d32; }
        .message.error { background-color: #ffebee; color: #c62828; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #388e3c; color: #fff; border: none; border-radius: 8px; font-weight: 600; }
        button:hover { background: #2e7d32; }
        .link-text { text-align: center; margin-top: 20px; font-size: 0.9rem; }
        .link-text a { color: #388e3c; font-weight: 600; text-decoration: none; }
        .link-text a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🚜 Create Account</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="link-text">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>
</body>
</html>
