<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('farmer');

// Get animal ID from URL
$animal_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($animal_id == 0) {
    header("Location: animals.php");
    exit();
}

// Fetch animal details
$stmt = $pdo->prepare("
    SELECT a.*, s.name as shed_name 
    FROM animals a 
    LEFT JOIN sheds s ON a.shed_id = s.id 
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$animal_id, $_SESSION['user_id']]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    header("Location: animals.php");
    exit();
}

// Fetch sheds for dropdown
$sheds_stmt = $pdo->prepare("SELECT id, name FROM sheds WHERE user_id = ?");
$sheds_stmt->execute([$_SESSION['user_id']]);
$sheds = $sheds_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tag_number = trim($_POST['tag_number']);
    $name = trim($_POST['name']);
    $species = trim($_POST['species']);
    $breed = trim($_POST['breed']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $weight = $_POST['weight'];
    $health_status = $_POST['health_status'];
    $shed_id = $_POST['shed_id'] ?: null;
    $notes = trim($_POST['notes']);

    // Validation
    if (empty($tag_number) || empty($species)) {
        $error = "Tag number and species are required fields.";
    } else {
        // Check if tag number already exists (excluding current animal)
        $check_stmt = $pdo->prepare("SELECT id FROM animals WHERE tag_number = ? AND id != ? AND user_id = ?");
        $check_stmt->execute([$tag_number, $animal_id, $_SESSION['user_id']]);
        
        if ($check_stmt->fetch()) {
            $error = "An animal with this tag number already exists.";
        } else {
            // Update animal
            $update_stmt = $pdo->prepare("
                UPDATE animals 
                SET tag_number = ?, name = ?, species = ?, breed = ?, date_of_birth = ?, 
                    gender = ?, weight = ?, health_status = ?, shed_id = ?, notes = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            
            if ($update_stmt->execute([
                $tag_number, $name, $species, $breed, $date_of_birth, $gender, 
                $weight, $health_status, $shed_id, $notes, $animal_id, $_SESSION['user_id']
            ])) {
                $success = "Animal updated successfully!";
                // Refresh animal data
                $stmt->execute([$animal_id, $_SESSION['user_id']]);
                $animal = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Failed to update animal. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Animal - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-edit"></i> Edit Animal</h1>
                <a href="animals.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Animals
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" class="animal-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tag_number">Tag Number *</label>
                            <input type="text" id="tag_number" name="tag_number" 
                                   value="<?php echo htmlspecialchars($animal['tag_number']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($animal['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="species">Species *</label>
                            <select id="species" name="species" required>
                                <option value="">Select Species</option>
                                <option value="Cow" <?php echo ($animal['species'] == 'Cow') ? 'selected' : ''; ?>>Cow</option>
                                <option value="Chicken" <?php echo ($animal['species'] == 'Chicken') ? 'selected' : ''; ?>>Chicken</option>
                                <option value="Goat" <?php echo ($animal['species'] == 'Goat') ? 'selected' : ''; ?>>Goat</option>
                                <option value="Sheep" <?php echo ($animal['species'] == 'Sheep') ? 'selected' : ''; ?>>Sheep</option>
                                <option value="Pig" <?php echo ($animal['species'] == 'Pig') ? 'selected' : ''; ?>>Pig</option>
                                <option value="Horse" <?php echo ($animal['species'] == 'Horse') ? 'selected' : ''; ?>>Horse</option>
                                <option value="Duck" <?php echo ($animal['species'] == 'Duck') ? 'selected' : ''; ?>>Duck</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="breed">Breed</label>
                            <input type="text" id="breed" name="breed" 
                                   value="<?php echo htmlspecialchars($animal['breed'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" 
                                   value="<?php echo $animal['date_of_birth'] ?? ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($animal['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($animal['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="weight">Weight (kg)</label>
                            <input type="number" id="weight" name="weight" step="0.1" 
                                   value="<?php echo $animal['weight'] ?? ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="health_status">Health Status</label>
                            <select id="health_status" name="health_status">
                                <option value="Excellent" <?php echo ($animal['health_status'] == 'Excellent') ? 'selected' : ''; ?>>Excellent</option>
                                <option value="Good" <?php echo ($animal['health_status'] == 'Good') ? 'selected' : ''; ?>>Good</option>
                                <option value="Fair" <?php echo ($animal['health_status'] == 'Fair') ? 'selected' : ''; ?>>Fair</option>
                                <option value="Poor" <?php echo ($animal['health_status'] == 'Poor') ? 'selected' : ''; ?>>Poor</option>
                                <option value="Critical" <?php echo ($animal['health_status'] == 'Critical') ? 'selected' : ''; ?>>Critical</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="shed_id">Shed</label>
                            <select id="shed_id" name="shed_id">
                                <option value="">No Shed</option>
                                <?php foreach ($sheds as $shed): ?>
                                    <option value="<?php echo $shed['id']; ?>" 
                                        <?php echo ($animal['shed_id'] == $shed['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($shed['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($animal['notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Animal
                        </button>
                        <a href="animals.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Add confirmation before leaving if form has changes
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.animal-form');
            let formChanged = false;
            
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    formChanged = true;
                });
            });
            
            form.addEventListener('submit', () => {
                formChanged = false;
            });
            
            window.addEventListener('beforeunload', (e) => {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>
</body>
</html>