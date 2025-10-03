<?php
// Initialize variables to hold previous form data
$username = '';
$email = '';
$message = '';
$message_type = ''; // 'success' or 'error'

// Check if the form was submitted using the POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the user input. Using htmlspecialchars for basic XSS prevention in display.
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    
    // --- Start: Placeholder Server-Side Validation/Processing ---
    
    // Check if passwords match (Basic dummy validation)
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $message = 'Error: Passwords do not match. Please try again.';
        $message_type = 'error';
    } else {
        
        $message = 'Success! User registration processed for ' . $username . '. (Redirect would occur here in a real app.)';
        $message_type = 'success';
        
        // Clear inputs on success for cleanliness, as they are "registered" now
        $username = '';
        $email = '';
    }
    
    // --- End: Placeholder Server-Side Validation/Processing ---
}
// PHP BLOCK END
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register | Farm Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Note: Linked CSS '/assets/css/style.css' is not used, using inline styles for a single file demo -->
    <style>
        /* General styling for the registration page */
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
            background-color: #e8f5e9; /* Light green */
            color: #2e7d32; /* Dark green */
            border: 1px solid #c8e6c9;
        }
        .message.error {
            background-color: #ffebee; /* Light red */
            color: #c62828; /* Dark red */
            border: 1px solid #ffcdd2;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus {
            border-color: #4CAF50;
            outline: none;
        }
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
            transition: background-color 0.3s, transform 0.1s;
        }
        button:hover {
            background-color: #2e7d32;
        }
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
        .link-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🚜 Create Account</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Form action is empty to self-submit (sticky form functionality) -->
        <form id="register-form" action="" method="POST"> 
            <div class="form-group">
                <label for="username">Username</label>
                <!-- PHP: Insert previous username value -->
                <input type="text" id="username" name="username" value="<?= $username ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <!-- PHP: Insert previous email value -->
                <input type="email" id="email" name="email" value="<?= $email ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="link-text">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>
</body>
</html>
