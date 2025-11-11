<?php
session_start();
require_once '../../auth/check_role.php';
check_role('admin');

// Database connection
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type']);
    $breed = trim($_POST['breed']);
    $gender = trim($_POST['gender']);
    $number = intval($_POST['number']);
    $avg_weight = trim($_POST['avg_weight']);
    $shed_no = trim($_POST['shed_no']);
    $notes = trim($_POST['notes']);

    if (empty($type) || empty($breed) || empty($gender) || empty($number) || empty($avg_weight) || empty($shed_no)) {
        $message = 'Please fill in all required fields.';
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO animals (type, breed, gender, number, avg_weight, shed_no, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $type, $breed, $gender, $number, $avg_weight, $shed_no, $notes);

        if ($stmt->execute()) {
            $message = '✅ Animal added successfully!';
            $message_type = 'success';
            // Clear form
            $_POST = array();
        } else {
            $message = 'Error adding animal: ' . $conn->error;
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
    <title>Add Animal - Farm Management System</title>
    <style>
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-title {
            color: var(--forest-green);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-brown);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .required::after {
            content: " *";
            color: #ff4444;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid var(--forest-green);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--earth-brown);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--forest-green);
            color: white;
        }

        .btn-primary:hover {
            background: var(--earth-brown);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #666;
            color: white;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
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

        .form-help {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="form-container">
                <div class="form-title">
                    <span>➕</span>
                    <span>Add New Animal</span>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type === 'error' ? 'error' : 'success'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label required">🐄 Animal Type</label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Cow" <?php echo isset($_POST['type']) && $_POST['type'] === 'Cow' ? 'selected' : ''; ?>>Cow</option>
                            <option value="Cattle" <?php echo isset($_POST['type']) && $_POST['type'] === 'Cattle' ? 'selected' : ''; ?>>Cattle</option>
                            <option value="Hen" <?php echo isset($_POST['type']) && $_POST['type'] === 'Hen' ? 'selected' : ''; ?>>Hen</option>
                            <option value="Cock" <?php echo isset($_POST['type']) && $_POST['type'] === 'Cock' ? 'selected' : ''; ?>>Cock</option>
                            <option value="Goat" <?php echo isset($_POST['type']) && $_POST['type'] === 'Goat' ? 'selected' : ''; ?>>Goat</option>
                            <option value="Sheep" <?php echo isset($_POST['type']) && $_POST['type'] === 'Sheep' ? 'selected' : ''; ?>>Sheep</option>
                            <option value="Rabbit" <?php echo isset($_POST['type']) && $_POST['type'] === 'Rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                            <option value="Horse" <?php echo isset($_POST['type']) && $_POST['type'] === 'Horse' ? 'selected' : ''; ?>>Horse</option>
                            <option value="Dog" <?php echo isset($_POST['type']) && $_POST['type'] === 'Dog' ? 'selected' : ''; ?>>Dog</option>
                            <option value="Cat" <?php echo isset($_POST['type']) && $_POST['type'] === 'Cat' ? 'selected' : ''; ?>>Cat</option>
                            <option value="Fish" <?php echo isset($_POST['type']) && $_POST['type'] === 'Fish' ? 'selected' : ''; ?>>Fish</option>
                            <option value="Turkey" <?php echo isset($_POST['type']) && $_POST['type'] === 'Turkey' ? 'selected' : ''; ?>>Turkey</option>
                            <option value="Goose" <?php echo isset($_POST['type']) && $_POST['type'] === 'Goose' ? 'selected' : ''; ?>>Goose</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">🏷️ Breed</label>
                        <input type="text" name="breed" class="form-control"
                            value="<?php echo isset($_POST['breed']) ? htmlspecialchars($_POST['breed']) : ''; ?>"
                            required placeholder="e.g., Jersey, Boer, Rhode Island Red">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">⚧️ Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo isset($_POST['gender']) && $_POST['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo isset($_POST['gender']) && $_POST['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Mixed" <?php echo isset($_POST['gender']) && $_POST['gender'] === 'Mixed' ? 'selected' : ''; ?>>Mixed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">🔢 Number of Animals</label>
                        <input type="number" name="number" class="form-control"
                            value="<?php echo isset($_POST['number']) ? htmlspecialchars($_POST['number']) : ''; ?>"
                            required min="1" placeholder="e.g., 50">
                        <div class="form-help">Enter the total number of animals in this group</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">⚖️ Average Weight</label>
                        <input type="text" name="avg_weight" class="form-control"
                            value="<?php echo isset($_POST['avg_weight']) ? htmlspecialchars($_POST['avg_weight']) : ''; ?>"
                            required placeholder="e.g., 450 kg, 2.5 kg">
                        <div class="form-help">Include units (kg, lbs, etc.)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">🏠 Shed Number</label>
                        <input type="text" name="shed_no" class="form-control"
                            value="<?php echo isset($_POST['shed_no']) ? htmlspecialchars($_POST['shed_no']) : ''; ?>"
                            required placeholder="e.g., Shed 1, Pond 2, Field A">
                    </div>

                    <div class="form-group">
                        <label class="form-label">📝 Notes</label>
                        <textarea name="notes" class="form-control" placeholder="Any additional notes about these animals..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span>💾</span>
                            <span>Save Animal</span>
                        </button>
                        <a href="animals_list.php" class="btn btn-secondary">
                            <span>↩️</span>
                            <span>Cancel</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>