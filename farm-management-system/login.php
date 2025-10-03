<?php
// Initialize variables to hold previous form data and messages
$email = '';
$message = '';
$message_type = ''; // 'success' or 'error'

// Check if the form was submitted using the POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the user input. Using htmlspecialchars for basic XSS prevention in display.
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : ''; 

    // --- Start: Placeholder Server-Side Validation/Processing ---
    
    // Placeholder credentials for demo purposes
    $mock_valid_email = 'user@farm.com';
    $mock_valid_password = 'securepassword123'; 
    
    // Basic presence check
    if (empty($email) || empty($password)) {
        $message = 'Error: Please enter both your email and password.';
        $message_type = 'error';
    } 
    // Mock authentication check
    elseif ($email === $mock_valid_email && $password === $mock_valid_password) {
        // Successful login (placeholder)
        
        $message = 'Success! You are now logged in as ' . $email . '. (Redirect would occur here in a real app.)';
        $message_type = 'success';
        $email = ''; // Clear email input on successful login
    } else {
        // Failed login attempt
        $message = 'Login failed. Invalid email or password.';
        $message_type = 'error';
        // The email remains sticky here to help the user correct it
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
    <title>Login | Farm Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Note: Linked CSS '/assets/css/style.css' is not used, using inline styles for a single file demo -->
    <style>
        /* General styling for the login page */
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
        <h2>🔑 Log In to Farm Management</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Form action is empty to self-submit (sticky form functionality) -->
        <form id="login-form" action="" method="POST"> 
            <div class="form-group">
                <label for="email">Email Address</label>
                <!-- PHP: Insert previous email value for sticky form -->
                <input type="email" id="email" name="email" value="<?= $email ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Log In</button>
        </form>
        <div class="link-text">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
    </div>
</body>
</html>
