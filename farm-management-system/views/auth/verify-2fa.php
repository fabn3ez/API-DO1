<?php
session_start();

$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if (!isset($_SESSION['temp_user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    
    // Include role in the query
    $stmt = $conn->prepare("SELECT id, username, role, two_factor_code, two_factor_expires FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['temp_user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['two_factor_code'] === $code && strtotime($user['two_factor_expires']) > time()) {
        // Success - set session and redirect based on role
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        // Clear 2FA code
        $clear = $conn->prepare("UPDATE users SET two_factor_code = NULL, two_factor_expires = NULL WHERE id = ?");
        $clear->bind_param("i", $user['id']);
        $clear->execute();

        unset($_SESSION['temp_user_id']);
        
        // Role-based redirection
        switch($user['role']) {
            case 'admin':
            header('Location: /API-DO1/farm-management-system/views/users/admin/dashboard.php');
            break;
            case 'manager': // customer
            header('Location: /API-DO1/farm-management-system/views/users/customer/dashboard.php');
            break;
            case 'field_worker': // farmer
            header('Location: /API-DO1/farm-management-system/views/users/farmer/dashboard.php');
            break;
            default:
            header('Location: login.php');
        }
        exit;
    } else {
        $message = "Invalid or expired verification code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - Farm Management System</title>
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
        
        .verification-container {
            background: var(--cream-white);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            border: 5px solid var(--earth-brown);
            animation: fadeInUp 0.8s ease-out;
        }
        
        .verification-header {
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
            animation: bounce 2s infinite;
        }
        
        .verification-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .verification-content {
            padding: 2rem;
            text-align: center;
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
            line-height: 1.5;
        }
        
        .code-input-container {
            margin: 2rem 0;
        }
        
        .code-input {
            width: 100%;
            padding: 15px;
            font-size: 1.5rem;
            letter-spacing: 8px;
            text-align: center;
            border: 2px solid var(--forest-green);
            border-radius: 10px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .code-input:focus {
            outline: none;
            border-color: var(--earth-brown);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.2);
            transform: scale(1.02);
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
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
            text-align: center;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .timer-section {
            margin: 1.5rem 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed var(--forest-green);
        }
        
        .timer {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--dark-brown);
            margin-bottom: 10px;
        }
        
        .countdown {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--forest-green);
            transition: color 0.3s ease;
        }
        
        .countdown.expiring {
            color: #dc3545;
            animation: pulse 1s infinite;
        }
        
        .resend-section {
            margin-top: 1.5rem;
            color: var(--dark-brown);
        }
        
        .resend-link {
            color: var(--forest-green);
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        
        .resend-link:hover {
            text-decoration: underline;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--dark-brown);
        }
        
        .back-link a {
            color: var(--forest-green);
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }
        
        .digit-box {
            display: inline-block;
            width: 50px;
            height: 60px;
            margin: 0 5px;
            border: 2px solid var(--forest-green);
            border-radius: 8px;
            text-align: center;
            font-size: 1.8rem;
            font-weight: bold;
            line-height: 56px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .digit-box:focus {
            border-color: var(--earth-brown);
            box-shadow: 0 0 0 2px rgba(139, 69, 19, 0.2);
            outline: none;
        }
        
        .digit-box.filled {
            background: var(--wheat);
            border-color: var(--earth-brown);
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-header">
            <span class="farm-icon">🔐</span>
            <h1>Two-Factor Verification</h1>
            <p>Secure access to your farm</p>
        </div>
        
        <div class="verification-content">
            <?php if (!empty($message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="instructions">
                <p><strong>📧 Check your email</strong></p>
                <p>We've sent a 6-digit verification code to your email address.</p>
                <p>Enter the code below to complete your login.</p>
            </div>
            
            <form method="POST" action="" id="verifyForm">
                <div class="code-input-container">
                    <div id="digitContainer">
                        <input type="text" class="digit-box" maxlength="1" data-index="1" tabindex="1">
                        <input type="text" class="digit-box" maxlength="1" data-index="2" tabindex="2">
                        <input type="text" class="digit-box" maxlength="1" data-index="3" tabindex="3">
                        <input type="text" class="digit-box" maxlength="1" data-index="4" tabindex="4">
                        <input type="text" class="digit-box" maxlength="1" data-index="5" tabindex="5">
                        <input type="text" class="digit-box" maxlength="1" data-index="6" tabindex="6">
                    </div>
                    <input type="hidden" name="code" id="fullCode" required>
                </div>
                
                <div class="timer-section">
                    <div class="timer">Code expires in:</div>
                    <div class="countdown" id="countdown">05:00</div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="verifyBtn">
                    <span>✅</span>
                    <span>Verify & Continue</span>
                </button>
            </form>
            
            <div class="resend-section">
                Didn't receive the code? 
                <a href="#" class="resend-link" onclick="resendCode()">Resend Code</a>
            </div>
            
            <div class="back-link">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        // Countdown timer (5 minutes)
        let timeLeft = 300; // 5 minutes in seconds
        const countdownEl = document.getElementById('countdown');
        let timerInterval;

        function startTimer() {
            timerInterval = setInterval(() => {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                countdownEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                // Change color when less than 1 minute
                if (timeLeft <= 60) {
                    countdownEl.classList.add('expiring');
                }
                
                timeLeft--;
                
                if (timeLeft < 0) {
                    clearInterval(timerInterval);
                    countdownEl.textContent = "Expired!";
                    countdownEl.style.color = "#dc3545";
                    document.getElementById('verifyBtn').disabled = true;
                }
            }, 1000);
        }

        // Digit input handling
        const digitBoxes = document.querySelectorAll('.digit-box');
        const fullCodeInput = document.getElementById('fullCode');

        digitBoxes.forEach((box, index) => {
            box.addEventListener('input', (e) => {
                const value = e.target.value;
                
                // Only allow numbers
                if (!/^\d?$/.test(value)) {
                    e.target.value = '';
                    return;
                }
                
                if (value !== '') {
                    e.target.classList.add('filled');
                    
                    // Auto-focus next box
                    if (index < digitBoxes.length - 1) {
                        digitBoxes[index + 1].focus();
                    }
                } else {
                    e.target.classList.remove('filled');
                }
                
                updateFullCode();
            });
            
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (e.target.value === '' && index > 0) {
                        // Move to previous box on backspace
                        digitBoxes[index - 1].focus();
                    } else {
                        e.target.classList.remove('filled');
                    }
                    updateFullCode();
                } else if (e.key === 'ArrowLeft' && index > 0) {
                    digitBoxes[index - 1].focus();
                } else if (e.key === 'ArrowRight' && index < digitBoxes.length - 1) {
                    digitBoxes[index + 1].focus();
                }
            });
            
            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').slice(0, 6);
                if (/^\d+$/.test(pasteData)) {
                    pasteData.split('').forEach((char, charIndex) => {
                        if (charIndex < digitBoxes.length) {
                            digitBoxes[charIndex].value = char;
                            digitBoxes[charIndex].classList.add('filled');
                        }
                    });
                    updateFullCode();
                    digitBoxes[Math.min(pasteData.length, digitBoxes.length - 1)].focus();
                }
            });
        });

        function updateFullCode() {
            const code = Array.from(digitBoxes).map(box => box.value).join('');
            fullCodeInput.value = code;
        }

        function resendCode() {
            if (confirm('Send a new verification code to your email?')) {
                // In a real application, you would make an AJAX call here
                // For now, we'll simulate by resetting the timer
                clearInterval(timerInterval);
                timeLeft = 300;
                startTimer();
                countdownEl.classList.remove('expiring');
                document.getElementById('verifyBtn').disabled = false;
                
                // Clear current inputs
                digitBoxes.forEach(box => {
                    box.value = '';
                    box.classList.remove('filled');
                });
                updateFullCode();
                digitBoxes[0].focus();
                
                alert('📧 A new verification code has been sent to your email!');
            }
        }

        // Form submission validation
        document.getElementById('verifyForm').addEventListener('submit', (e) => {
            const code = fullCodeInput.value;
            if (code.length !== 6) {
                e.preventDefault();
                alert('Please enter the complete 6-digit code.');
                digitBoxes[0].focus();
            }
        });

        // Start timer and focus first input when page loads
        document.addEventListener('DOMContentLoaded', () => {
            startTimer();
            digitBoxes[0].focus();
        });
    </script>
</body>
</html>