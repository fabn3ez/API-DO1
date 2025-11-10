<?php
require __DIR__ . '/../../../vendor/autoload.php';
session_start();

$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

$message = '';
$message_type = '';
$valid_token = false;
$token = '';

// Check if token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Validate token
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $valid_token = true;
        $_SESSION['reset_user_id'] = $user['id'];
    } else {
        $message = 'Invalid or expired reset link. Please request a new one.';
        $message_type = 'error';
    }
    $stmt->close();
} else {
    $message = 'No reset token provided.';
    $message_type = 'error';
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $message = 'Please fill in both password fields.';
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $message_type = 'error';
    } else {
        // Update password and clear reset token
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $update->bind_param("si", $hashed_password, $_SESSION['reset_user_id']);
        
        if ($update->execute()) {
            $message = '✅ Password reset successfully! You can now login with your new password.';
            $message_type = 'success';
            $valid_token = false; // Invalidate token after use
            
            // Clear session
            unset($_SESSION['reset_user_id']);
        } else {
            $message = 'Error resetting password. Please try again.';
            $message_type = 'error';
        }
        $update->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Farm Management System</title>
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
        
        .btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
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
        
        .password-toggle {
            position: relative;
        }
        
        .toggle-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--forest-green);
        }
        
        .password-strength {
            margin-top: 5px;
            height: 5px;
            border-radius: 5px;
            background: #e0e0e0;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .weak { background: #ff4444; width: 33%; }
        .medium { background: #ffaa00; width: 66%; }
        .strong { background: #00c851; width: 100%; }
    </style>
</head>
<body>
    <div class="password-container">
        <div class="password-header">
            <span class="farm-icon">🔄</span>
            <h1>Create New Password</h1>
            <p>Choose a strong, secure password</p>
        </div>
        
        <div class="password-form">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($valid_token): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="password">🔒 New Password</label>
                        <div class="password-toggle">
                            <input type="password" id="password" name="password" class="form-control" required>
                            <span class="toggle-icon" id="togglePassword">👁️</span>
                        </div>
                        <div class="password-strength">
                            <div class="strength-fill weak" id="password-strength"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">✅ Confirm New Password</label>
                        <div class="password-toggle">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            <span class="toggle-icon" id="toggleConfirmPassword">👁️</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <span>🌱</span>
                        <span>Reset Password</span>
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="login-link">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirm_password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('password-strength');
            
            let strength = 'weak';
            if (password.length >= 8) strength = 'medium';
            if (password.length >= 12 && /[!@#$%^&*(),.?":{}|<>]/.test(password)) strength = 'strong';
            
            strengthBar.className = 'strength-fill ' + strength;
        });

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function(e) {
            const password = document.getElementById('password').value;
            const confirm = e.target.value;
            
            if (confirm && password !== confirm) {
                this.style.borderColor = '#ff4444';
            } else {
                this.style.borderColor = '#228B22';
            }
        });

        // Add interactive elements
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