<?php
// register.php

// Include database connection
require_once "db_connect.php";
require_once __DIR__ . '/../../database/db_connect.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Start output buffering (for header redirection safety)
ob_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "⚠️ Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "⚠️ Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "⚠️ Passwords do not match.";
    } else {
        try {
            // Check if email already exists
            $checkStmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                $error = "⚠️ Email already registered.";
            } else {
                // Hash password and assign default role
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $role = 'user';

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

                if ($stmt->execute()) {
                    // Send welcome email
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'yourgmail@gmail.com'; // Change to your Gmail
                        $mail->Password = 'your-app-password'; // Use Gmail App Password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('yourgmail@gmail.com', 'Farm Management System');
                        $mail->addAddress($email, $username);

                        $mail->isHTML(true);
                        $mail->Subject = 'Welcome to the Farm Management System 🌱';
                        $mail->Body = "
                            <h3>Hello, $username!</h3>
                            <p>Welcome to the <strong>Farm Management System</strong>.</p>
                            <p>You can now <a href='login.php'>log in</a> and start managing your farm data efficiently.</p>
                            <p>— The FMS Team</p>
                        ";

                        $mail->send();
                    } catch (Exception $e) {
                        // Silent fail for email (not critical)
                    }

                    $success = "✅ Registration successful! A confirmation email has been sent to $email.";
                } else {
                    $error = "❌ Error: Registration failed, please try again.";
                }

                $stmt->close();
            }

            $checkStmt->close();
        } catch (Exception $e) {
            $error = "⚠️ Something went wrong: " . $e->getMessage();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Registration | Farm Management System</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body {
      background: #f0f8f5;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .register-container {
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      width: 380px;
    }
    .register-container h2 {
      text-align: center;
      color: #2b7a0b;
      margin-bottom: 20px;
    }
    label {
      font-weight: bold;
      color: #444;
    }
    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 8px 0 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    button {
      width: 100%;
      padding: 10px;
      background: #2b7a0b;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
    button:hover {
      background: #249106;
    }
    .message {
      margin-top: 10px;
      text-align: center;
      font-weight: bold;
    }
    .error { color: #c00; }
    .success { color: #2b7a0b; }
  </style>
</head>
<body>
  <div class="register-container">
    <h2>🧑‍🌾 Register Account</h2>

    <?php if (isset($error)): ?>
      <p class="message error"><?= $error ?></p>
    <?php elseif (isset($success)): ?>
      <p class="message success"><?= $success ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="username">Full Name:</label>
      <input type="text" id="username" name="username" required>

      <label for="email">Email Address:</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <label for="confirm_password">Confirm Password:</label>
      <input type="password" id="confirm_password" name="confirm_password" required>

      <button type="submit">Register</button>
    </form>

    <p style="text-align:center; margin-top:10px;">Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
