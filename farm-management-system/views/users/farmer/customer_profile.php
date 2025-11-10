<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('customer');

$error = '';
$success = '';

// Fetch user details
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = "First name, last name, and email are required fields.";
    } else {
        // Check if email already exists (excluding current user)
        $email_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_stmt->execute([$email, $_SESSION['user_id']]);
        
        if ($email_stmt->fetch()) {
            $error = "This email address is already registered.";
        } else {
            // Update user profile
            $update_stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            if ($update_stmt->execute([$first_name, $last_name, $email, $phone, $address, $_SESSION['user_id']])) {
                $success = "Profile updated successfully!";
                // Refresh user data
                $user_stmt->execute([$_SESSION['user_id']]);
                $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Failed to update profile. Please try again.";
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $password_stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            
            if ($password_stmt->execute([$password_hash, $_SESSION['user_id']])) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password. Please try again.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

// Fetch customer statistics
$stats_stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM sales_orders WHERE user_id = ?) as total_orders,
        (SELECT SUM(total_amount) FROM sales_orders WHERE user_id = ? AND status = 'delivered') as total_spent,
        (SELECT COUNT(*) FROM sales_orders WHERE user_id = ? AND status = 'pending') as pending_orders,
        (SELECT COUNT(*) FROM sales_orders WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_orders
");
$stats_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$customer_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-user-circle"></i> My Profile</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="profile-container">
                <!-- Customer Statistics -->
                <div class="stats-section">
                    <h2><i class="fas fa-chart-line"></i> Shopping Overview</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon orders">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $customer_stats['total_orders']; ?></h3>
                                <p>Total Orders</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon revenue">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-info">
                                <h3>$<?php echo number_format($customer_stats['total_spent'], 2); ?></h3>
                                <p>Total Spent</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $customer_stats['pending_orders']; ?></h3>
                                <p>Pending Orders</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon recent">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $customer_stats['recent_orders']; ?></h3>
                                <p>Orders (30 days)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Information -->
                <div class="profile-section">
                    <h2><i class="fas fa-user-edit"></i> Personal Information</h2>
                    <form method="POST" class="profile-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="password-section">
                    <h2><i class="fas fa-lock"></i> Change Password</h2>
                    <form method="POST" class="password-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="current_password">Current Password *</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>

                            <div class="form-group">
                                <label for="new_password">New Password *</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="account-section">
                    <h2><i class="fas fa-info-circle"></i> Account Information</h2>
                    <div class="account-details">
                        <div class="detail-item">
                            <label>Member Since:</label>
                            <span><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Last Updated:</label>
                            <span><?php echo date('M j, Y g:i A', strtotime($user['updated_at'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Role:</label>
                            <span class="role-badge"><?php echo ucfirst($user['role']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Account Status:</label>
                            <span class="status-badge active">Active</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="actions-section">
                    <h2><i class="fas fa-rocket"></i> Quick Actions</h2>
                    <div class="quick-actions">
                        <a href="orders.php" class="action-card">
                            <i class="fas fa-clipboard-list"></i>
                            <span>View Orders</span>
                        </a>
                        <a href="wishlist.php" class="action-card">
                            <i class="fas fa-heart"></i>
                            <span>My Wishlist</span>
                        </a>
                        <a href="products.php" class="action-card">
                            <i class="fas fa-shopping-bag"></i>
                            <span>Continue Shopping</span>
                        </a>
                        <a href="../auth/logout.php" class="action-card">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            updatePasswordStrength(strength);
        });

        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            return strength;
        }

        function updatePasswordStrength(strength) {
            const indicator = document.getElementById('password-strength') || createPasswordStrengthIndicator();
            let text = '', color = '';
            
            switch(strength) {
                case 0:
                case 1:
                    text = 'Weak';
                    color = '#f44336';
                    break;
                case 2:
                case 3:
                    text = 'Medium';
                    color = '#ff9800';
                    break;
                case 4:
                    text = 'Strong';
                    color = '#4caf50';
                    break;
            }
            
            indicator.textContent = `Password Strength: ${text}`;
            indicator.style.color = color;
        }

        function createPasswordStrengthIndicator() {
            const indicator = document.createElement('div');
            indicator.id = 'password-strength';
            indicator.style.marginTop = '5px';
            indicator.style.fontSize = '0.9em';
            document.querySelector('input[name="new_password"]').parentNode.appendChild(indicator);
            return indicator;
        }
    </script>
</body>
</html>