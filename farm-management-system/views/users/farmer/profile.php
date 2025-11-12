<?php
session_start();
require_once __DIR__ . '/../../../config/db.php';
$database = new Database();
$pdo = $database->getConnection();
require_once '../../auth/check_role.php';
check_role('farmer');

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
    $farm_name = trim($_POST['farm_name']);
    $farm_size = $_POST['farm_size'] ?: null;
    $farm_type = $_POST['farm_type'] ?: null;

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
                SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, 
                    farm_name = ?, farm_size = ?, farm_type = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            if ($update_stmt->execute([
                $first_name, $last_name, $email, $phone, $address, 
                $farm_name, $farm_size, $farm_type, $_SESSION['user_id']
            ])) {
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

// Fetch farm statistics for dashboard

$stats_stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM animals) as total_animals,
        (SELECT COUNT(*) FROM sheds) as total_sheds,
        (SELECT COUNT(*) FROM inventory) as total_inventory,
        (SELECT COUNT(*) FROM sales_orders WHERE status = 'completed') as total_orders
");
$stats_stmt->execute();
$farm_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f4ecd8 0%, #eaffea 100%);
            color: #4e3b1f;
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        }
        .container {
            background: linear-gradient(135deg, #a67c52 0%, #eaffea 100%);
            border-radius: 16px;
            box-shadow: 0 6px 32px rgba(67,234,94,0.12);
            padding: 40px 32px;
            margin: 60px auto 0 auto;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .content-header h1 {
            color: #388e3c;
            font-size: 2rem;
            margin-bottom: 22px;
            font-family: 'Poppins', serif;
            letter-spacing: 1px;
            text-align: center;
            text-shadow: 0 2px 8px #eaffea, 0 1px 0 #a67c52;
        }
        .profile-card {
            background: #fffbe6;
            border-radius: 14px;
            box-shadow: 0 4px 10px rgba(56,142,60,0.08);
            padding: 32px 24px;
            margin-bottom: 32px;
            width: 100%;
            max-width: 600px;
        }
        .profile-card h2 {
            color: #388e3c;
            font-size: 1.3rem;
            margin-bottom: 18px;
            font-weight: 700;
        }
        .profile-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 32px;
            margin-bottom: 18px;
        }
        .profile-details label {
            color: #a67c52;
            font-weight: 600;
            font-size: 1.05rem;
        }
        .profile-details span {
            color: #388e3c;
            font-size: 1.05rem;
        }
        .profile-form {
            margin-top: 18px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            color: #a67c52;
            font-weight: 700;
            font-size: 1.05rem;
        }
        input, select, textarea {
            background: #fffbe6;
            border: 1.5px solid #a67c52;
            border-radius: 6px;
            padding: 12px;
            font-size: 1.05rem;
            color: #4e3b1f;
            margin-bottom: 10px;
            width: 100%;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #43ea5e;
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(90deg, #388e3c 0%, #a67c52 100%);
            color: #fffbe6;
            border: none;
            border-radius: 6px;
            padding: 12px 22px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(67,234,94,0.08);
            margin-top: 10px;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #a67c52 0%, #388e3c 100%);
        }
        .alert-error {
            background: #fbeee6;
            color: #8d5524;
            border-left: 5px solid #a67c52;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
        }
        .alert-success {
            background: #eaffea;
            color: #388e3c;
            border-left: 5px solid #388e3c;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
        
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

            <div class="profile-details">
                <label>Member Since:</label>
                <span><?php echo !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'Unknown'; ?></span>
                <label>Last Updated:</label>
                <span>
                    <?php 
                    $updated = (isset($user['updated_at']) && $user['updated_at'] !== null && $user['updated_at'] !== '' && $user['updated_at'] !== '0000-00-00 00:00:00');
                    if ($updated) {
                        $updated_at_val = $user['updated_at'];
                        echo date('M d, Y h:i A', strtotime($updated_at_val));
                    } else {
                        echo 'Never';
                    }
                    ?>
                </span>
                <label>Role:</label>
                <span><?php echo ucfirst($user['role']); ?></span>
                <label>Account Status:</label>
                <span><?php echo $user['active'] ? 'Active' : 'Inactive'; ?></span>
            </div>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $farm_stats['total_sheds']; ?></h3>
                                <p>Sheds</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon inventory">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $farm_stats['total_inventory']; ?></h3>
                                <p>Inventory Items</p>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon sales">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $farm_stats['total_orders']; ?></h3>
                                <p>Completed Orders</p>
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
                        value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" 
                        value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" 
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="farm_name">Farm Name</label>
                                <input type="text" id="farm_name" name="farm_name" 
                                       value="<?php echo htmlspecialchars($user['farm_name'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="farm_size">Farm Size (acres)</label>
                                <input type="number" id="farm_size" name="farm_size" step="0.1" 
                                       value="<?php echo $user['farm_size'] ?? ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="farm_type">Farm Type</label>
                                <select id="farm_type" name="farm_type">
                                    <option value="">Select Farm Type</option>
                                    <option value="Dairy" <?php echo ($user['farm_type'] ?? '') == 'Dairy' ? 'selected' : ''; ?>>Dairy</option>
                                    <option value="Poultry" <?php echo ($user['farm_type'] ?? '') == 'Poultry' ? 'selected' : ''; ?>>Poultry</option>
                                    <option value="Livestock" <?php echo ($user['farm_type'] ?? '') == 'Livestock' ? 'selected' : ''; ?>>Livestock</option>
                                    <option value="Mixed" <?php echo ($user['farm_type'] ?? '') == 'Mixed' ? 'selected' : ''; ?>>Mixed</option>
                                    <option value="Organic" <?php echo ($user['farm_type'] ?? '') == 'Organic' ? 'selected' : ''; ?>>Organic</option>
                                    <option value="Other" <?php echo ($user['farm_type'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
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
                            <span>
                                <?php
                                if (isset($user['updated_at']) && $user['updated_at'] && $user['updated_at'] !== '0000-00-00 00:00:00') {
                                    echo date('M j, Y g:i A', strtotime($user['updated_at']));
                                } else {
                                    echo 'Never';
                                }
                                ?>
                            </span>
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
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../../../includes/footer.php'; ?>

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