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
    $role = $_POST['role'];
    
    $errors = [];
    
    // Validation
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if (empty($role)) $errors[] = "Please select a role";
    
    // Check if username/email exists
    if (empty($errors)) {
        $check_user = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_user->bind_param("ss", $username, $email);
        $check_user->execute();
        $check_user->store_result();
        
        if ($check_user->num_rows > 0) {
            $errors[] = "Username or email already exists";
        }
        $check_user->close();
    }
    
    // Create user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        
        if ($stmt->execute()) {
            // =============================
            // SEND EMAIL TO NEW USER
            // =============================
            $mail = new PHPMailer(true);

            try {
                // SMTP SETTINGS
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sirwoossah@gmail.com';
                $mail->Password   = 'hrfv ksao fhyk hwgm';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // RECIPIENTS
                $mail->setFrom('sirwoossah@gmail.com', 'Farm Management System');
                $mail->addAddress($email, $username);

                // EMAIL CONTENT
                $mail->isHTML(true);
                $mail->Subject = "Welcome to Farm Management System!";
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; background-color: #f8fff8; padding: 20px; border-radius: 10px; border: 2px solid #8B4513;'>
                    <h2 style='color: #228B22; text-align: center;'>🌱 Welcome to Farm Management System, $username! 🌱</h2>
                    <p>We're thrilled to have you on board! 🎉</p>
                    <p>You can now log in to your account and start managing your farm activities efficiently.</p>
                    <p><strong>Your Role:</strong> " . ucfirst($role) . "</p>
                    <br>
                    <div style='text-align: center;'>
                        <a href='http://localhost/API-DO1/farm-management-system/views/auth/login.php' 
                            style='display:inline-block; background-color:#228B22; color:white; padding:12px 25px; border-radius:8px; text-decoration:none; font-weight:bold;'>
                            🚜 Login to Your Account
                        </a>
                    </div>
                    <br><br>
                    <p>Best regards,<br><strong>The Farm Management Team 🌾</strong></p>
                </div>";
                
                $mail->AltBody = "Welcome to Farm Management System, $username! You can now log in and start managing your farm activities. Your role: $role";

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
            $errors[] = "Registration failed: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = implode('<br>• ', $errors);
        $message_type = 'error';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Farm Management System</title>
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
        
        .register-container {
            background: var(--cream-white);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            border: 5px solid var(--earth-brown);
        }
        
        .register-header {
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
        
        .register-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .register-form {
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
        
        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 0.5rem;
        }
        
        .role-option {
            border: 2px solid var(--forest-green);
            border-radius: 10px;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .role-option:hover {
            background: var(--wheat);
        }
        
        .role-option.selected {
            background: var(--forest-green);
            color: white;
            border-color: var(--earth-brown);
        }
        
        .role-icon {
            font-size: 1.5rem;
            margin-bottom: 5px;
            display: block;
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
    <div class="register-container">
        <div class="register-header">
            <span class="farm-icon">🚜</span>
            <h1>Join Our Farm Community</h1>
            <p>Register your account to get started</p>
        </div>
        
        <div class="register-form">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type === 'error' ? 'error' : 'success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">👤 Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email">📧 Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div class="password-strength">
                        <div class="strength-fill weak" id="password-strength"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">✅ Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>🎭 Select Your Role</label>
                    <div class="role-selector">
                        <div class="role-option" data-role="farmer">
                            <span class="role-icon">👨‍🌾</span>
                            <div>Farmer</div>
                        </div>
                        <div class="role-option" data-role="customer">
                            <span class="role-icon">🛒</span>
                            <div>Customer</div>
                        </div>
                        <div class="role-option" data-role="admin">
                            <span class="role-icon">👑</span>
                            <div>Admin</div>
                        </div>
                    </div>
                    <input type="hidden" name="role" id="selected-role" value="farmer" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <span>🌱</span>
                    <span>Create Account</span>
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        // Role selection
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.role-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update hidden input value
                document.getElementById('selected-role').value = this.dataset.role;
            });
        });
        
        // Set default selected role
        document.querySelector('.role-option[data-role="farmer"]').classList.add('selected');
        
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
    </script>
</body>
</html>