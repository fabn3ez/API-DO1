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
$animal = null;

// Get animal data if ID is provided
if (isset($_GET['id'])) {
    $animal_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM animals WHERE id = ?");
    $stmt->bind_param("i", $animal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $animal = $result->fetch_assoc();
    $stmt->close();
}

if (!$animal) {
    header('Location: animals_list.php');
    exit;
}

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
        $stmt = $conn->prepare("UPDATE animals SET type=?, breed=?, gender=?, number=?, avg_weight=?, shed_no=?, notes=? WHERE id=?");
        $stmt->bind_param("sssissi", $type, $breed, $gender, $number, $avg_weight, $shed_no, $notes, $animal_id);

        if ($stmt->execute()) {
            $message = '✅ Animal updated successfully!';
            $message_type = 'success';
            // Refresh animal data
            $stmt = $conn->prepare("SELECT * FROM animals WHERE id = ?");
            $stmt->bind_param("i", $animal_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $animal = $result->fetch_assoc();
        } else {
            $message = 'Error updating animal: ' . $conn->error;
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
    <title>Edit Animal - Farm Management System</title>
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

        .animal-header {
            background: var(--wheat);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--forest-green);
        }

        .animal-type-icon {
            font-size: 2rem;
            margin-right: 10px;
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
                    <span>✏️</span>
                    <span>Edit Animal</span>
                </div>

                <div class="animal-header">
                    <?php
                    $icons = [
                        'Cow' => '🐄', 'Cattle' => '🐂', 'Hen' => '🐔', 'Cock' => '🐓',
                        'Goat' => '🐐', 'Sheep' => '🐑', 'Rabbit' => '🐇', 'Horse' => '🐎',
                        'Dog' => '🐕', 'Cat' => '🐈', 'Fish' => '🐟', 'Turkey' => '🦃',
                        'Goose' => '🦆'
                    ];
                    $icon = $icons[$animal['type']] ?? '🐾';
                    ?>
                    <span class="animal-type-icon"><?php echo $icon; ?></span>
                    <strong><?php echo $animal['type']; ?> - <?php echo $animal['breed']; ?></strong>
                    <div style="font-size: 0.9rem; color: #666;">
                        Shed: <?php echo $animal['shed_no']; ?> | Count: <?php echo $animal['number']; ?>
                    </div>
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
                            <option value="Cow" <?php echo $animal['type'] === 'Cow' ? 'selected' : ''; ?>>Cow</option>
                            <option value="Cattle" <?php echo $animal['type'] === 'Cattle' ? 'selected' : ''; ?>>Cattle</option>
                            <option value="Hen" <?php echo $animal['type'] === 'Hen' ? 'selected' : ''; ?>>Hen</option>
                            <option value="Cock" <?php echo $animal['type'] === 'Cock' ? 'selected' : ''; ?>>Cock</option>
                            <option value="Goat" <?php echo $animal['type'] === 'Goat' ? 'selected' : ''; ?>>Goat</option>
                            <option value="Sheep" <?php echo $animal['type'] === 'Sheep' ? 'selected' : ''; ?>>Sheep</option>
                            <option value="Rabbit" <?php echo $animal['type'] === 'Rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                            <option value="Horse" <?php echo $animal['type'] === 'Horse' ? 'selected' : ''; ?>>Horse</option>
                            <option value="Dog" <?php echo $animal['type'] === 'Dog' ? 'selected' : ''; ?>>Dog</option>
                            <option value="Cat" <?php echo $animal['type'] === 'Cat' ? 'selected' : ''; ?>>Cat</option>
                            <option value="Fish" <?php echo $animal['type'] === 'Fish' ? 'selected' : ''; ?>>Fish</option>
                            <option value="Turkey" <?php echo $animal['type'] === 'Turkey' ? 'selected' : ''; ?>>Turkey</option>
                            <option value="Goose" <?php echo $animal['type'] === 'Goose' ? 'selected' : ''; ?>>Goose</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">🏷️ Breed</label>
                        <input type="text" name="breed" class="form-control"
                            value="<?php echo htmlspecialchars($animal['breed']); ?>"
                            required placeholder="e.g., Jersey, Boer, Rhode Island Red">
                    </div>

                    <div class="form-group">
                        <label class="form-label required">⚧️ Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo $animal['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $animal['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Mixed" <?php echo $animal['gender'] === 'Mixed' ? 'selected' : ''; ?>>Mixed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">🔢 Number of Animals</label>
                        <input type="number" name="number" class="form-control"
                            value="<?php echo $animal['number']; ?>"
                            required min="1" placeholder="e.g., 50">
                        <div class="form-help">Enter the total number of animals in this group</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">⚖️ Average Weight</label>
                        <input type="text" name="avg_weight" class="form-control"
                            value="<?php echo htmlspecialchars($animal['avg_weight']); ?>"
                            required placeholder="e.g., 450 kg, 2.5 kg">
                        <div class="form-help">Include units (kg, lbs, etc.)</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">🏠 Shed Number</label>
                        <input type="text" name="shed_no" class="form-control"
                            value="<?php echo htmlspecialchars($animal['shed_no']); ?>"
                            required placeholder="e.g., Shed 1, Pond 2, Field A">
                    </div>

                    <div class="form-group">
                        <label class="form-label">📝 Notes</label>
                        <textarea name="notes" class="form-control" placeholder="Any additional notes about these animals..."><?php echo htmlspecialchars($animal['notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span>💾</span>
                            <span>Update Animal</span>
                        </button>
                        <a href="animals_list.php" class="btn btn-secondary">
                            <span>↩️</span>
                            <span>Cancel</span>
                        </a>
                        <a href="view_animal.php?id=<?php echo $animal['id']; ?>" class="btn" style="background: var(--sky-blue); color: white;">
                            <span>👁️</span>
                            <span>View</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>