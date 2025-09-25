<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Farm Management System</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Farm Management System</h1>
            <h2>Login to Your Account</h2>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Username or Email:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-primary">Login</button>
            </form>
            
            <div id="2faSection" style="display: none;">
                <h3>Two-Factor Authentication</h3>
                <p>Enter the verification code sent to your <span id="2faMethod"></span></p>
                <form id="2faForm">
                    <input type="hidden" id="tempSessionId">
                    <div class="form-group">
                        <label for="token">Verification Code:</label>
                        <input type="text" id="token" name="token" maxlength="6" required>
                    </div>
                    <button type="submit" class="btn-primary">Verify</button>
                </form>
            </div>
            
            <div class="auth-links">
                <a href="/register">Create an account</a> | 
                <a href="/forgot-password">Forgot password?</a>
            </div>
            
            <div id="message" class="message"></div>
        </div>
    </div>

    <script src="/assets/js/auth.js"></script>
</body>
</html>