<?php
require __DIR__ . '/../../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = 'Please enter your email address.';
        $message_type = 'error';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $update->bind_param("ssi", $token, $expires, $user['id']);
            $update->execute();
            
            // Send reset email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'sirwoossah@gmail.com';
                $mail->Password = 'trgl cuuq okpd bjuf';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('sirwoossah@gmail.com', 'Farm Management System');
                $mail->addAddress($email, $user['username']);

                $mail->isHTML(true);
                $mail->Subject = "Reset Your Farm Management Password";
                $reset_link = "http://localhost/API-DO1/farm-management-system/views/auth/reset-password.php?token=" . $token;
                
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; background-color: #f8fff8; padding: 20px; border-radius: 10px; border: 2px solid #8B4513;'>
                    <h2 style='color: #228B22; text-align: center;'>🌱 Password Reset Request 🌱</h2>
                    <p>Hello " . htmlspecialchars($user['username']) . ",</p>
                    <p>We received a request to reset your password. Click the button below to create a new password:</p>
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='$reset_link' 
                           style='display:inline-block; background-color:#228B22; color:white; padding:12px 25px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:1.1rem;'>
                           🔒 Reset Your Password
                        </a>
                    </div>
                    <p><strong>This link will expire in 1 hour.</strong></p>
                    <p>If you didn't request this reset, please ignore this email.</p>
                    <br>
                    <p>Best regards,<br><strong>The Farm Management Team 🌾</strong></p>
                </div>";
                
                $mail->AltBody = "Hello " . $user['username'] . ", Please use this link to reset your password: $reset_link (expires in 1 hour)";

                $mail->send();
                $message = '📧 Password reset link has been sent to your email!';
                $message_type = 'success';
                
            } catch (Exception $e) {
                $message = "Could not send reset email. Error: {$mail->ErrorInfo}";
                $message_type = 'error';
            }
        } else {
            $message = 'No account found with that email address.';
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Farm Management System</title>
    <style>
        /* Farm Theme Styles */
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--forest-green), var(--sky-blue));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .password-container {
            background: var(--cream-white);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            border: 5px solid var(--earth-brown);
        }
        
        .password-header {
            background: linear-gradient(to right, var(--forest-green), var(--earth-brown));
            color: white;
            text-align: center;
            padding: 2rem;
            position: relative;
        }
        
        .farm-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .password-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .password-form {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-brown);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--forest-green);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--earth-brown);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: var(--forest-green);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--earth-brown);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--dark-brown);
        }
        
        .login-link a {
            color: var(--forest-green);
            text-decoration: none;
            font-weight: bold;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .instructions {
            background: var(--wheat);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--forest-green);
        }
        
        .instructions p {
            margin: 5px 0;
            color: var(--dark-brown);
        }
    </style>
</head>
<body>
    <div class="password-container">
        <div class="password-header">
            <span class="farm-icon">🔑</span>
            <h1>Reset Your Password</h1>
            <p>We'll send you a reset link</p>
        </div>
        
        <div class="password-form">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="instructions">
                <p><strong>📧 How it works:</strong></p>
                <p>• Enter your email address below</p>
                <p>• We'll send you a secure reset link</p>
                <p>• Click the link to create a new password</p>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">📧 Your Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                           required placeholder="Enter your registered email">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <span>📨</span>
                    <span>Send Reset Link</span>
                </button>
            </form>
            
            <div class="login-link">
                Remember your password? <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>