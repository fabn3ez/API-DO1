<?php
session_start();

// Database connection
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Redirect if no temp user (not coming from login)
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);

    // Get user info
    $stmt = $conn->prepare("SELECT id, username, two_factor_code, two_factor_expires FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['temp_user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $isCodeValid = $user['two_factor_code'] === $code;
        $isNotExpired = strtotime($user['two_factor_expires']) > time();

        if ($isCodeValid && $isNotExpired) {
            // ✅ Successful verification
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Clear OTP fields
            $clear = $conn->prepare("UPDATE users SET two_factor_code = NULL, two_factor_expires = NULL WHERE id = ?");
            $clear->bind_param("i", $user['id']);
            $clear->execute();

            unset($_SESSION['temp_user_id']);
            header('Location: index.dashboard.php');
            exit;
        } else {
            $message = "❌ Invalid or expired code. Please try again.";
        }
    } else {
        $message = "⚠️ User not found. Please log in again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>2FA Verification | Farm Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0ffe0 0%, #b2dfdb 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            color: #2c3e50;
        }
        .container {
            background: #fff;
            padding: 40px 50px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s forwards;
        }
        h2 {
            color: #388e3c;
            font-size: 1.8rem;
            margin-bottom: 15px;
            animation: bounceIn 0.8s ease;
        }
        p.subtitle {
            color: #555;
            font-size: 0.95rem;
            margin-bottom: 25px;
        }
        input[type="text"] {
            padding: 12px;
            font-size: 18px;
            letter-spacing: 4px;
            border-radius: 10px;
            border: 1px solid #ccc;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        input:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 10px rgba(76,175,80,0.3);
            outline: none;
        }
        button {
            background: #388e3c;
            color: #fff;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            padding: 12px 0;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover {
            background: #2e7d32;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46,125,50,0.3);
        }
        .message {
            margin-top: 15px;
            color: #c62828;
            font-weight: 600;
        }
        .timer {
            margin-top: 15px;
            color: #555;
            font-weight: 500;
        }
        .resend {
            margin-top: 15px;
        }
        .resend a {
            color: #388e3c;
            text-decoration: none;
            font-weight: 600;
        }
        .resend a:hover {
            text-decoration: underline;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounceIn {
            0% { transform: scale(0.8); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔐 2FA Verification</h2>
        <p class="subtitle">Enter the 6-digit code sent to your email.</p>

        <form method="POST" id="verifyForm">
            <input type="text" name="code" id="code" maxlength="6" placeholder="Enter code" required>
            <button type="submit">Verify Code</button>
        </form>

        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <div class="timer" id="timer">Code expires in: <span id="countdown">05:00</span></div>
        <div class="resend">
            Didn’t receive the code? <a href="#" onclick="resendCode(event)">Resend</a>
        </div>
    </div>

    <script>
        // Auto-focus
        document.getElementById('code').focus();

        // Countdown timer (5 minutes)
        let timeLeft = 300;
        const countdownEl = document.getElementById('countdown');

        const timer = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownEl.textContent = `${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}`;
            timeLeft--;

            if (timeLeft < 0) {
                clearInterval(timer);
                countdownEl.textContent = "Expired";
                countdownEl.style.color = "red";
            }
        }, 1000);

        // Prevent non-numeric input
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', () => {
            codeInput.value = codeInput.value.replace(/\D/g, '');
        });

        // Resend code (we'll wire this later)
        function resendCode(e) {
            e.preventDefault();
            alert('✅ A new verification code will be sent shortly.');
            // Later: Use AJAX to trigger resend_otp.php
        }
    </script>
</body>
</html>
