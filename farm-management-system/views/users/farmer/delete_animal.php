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

// Fetch animal details to verify ownership
$stmt = $pdo->prepare("SELECT * FROM animals WHERE id = ? AND user_id = ?");
$stmt->execute([$animal_id, $_SESSION['user_id']]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    header("Location: animals.php");
    exit();
}

$error = '';
$success = '';

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_delete'])) {
        try {
            $pdo->beginTransaction();
            
            // Delete animal
            $delete_stmt = $pdo->prepare("DELETE FROM animals WHERE id = ? AND user_id = ?");
            $delete_stmt->execute([$animal_id, $_SESSION['user_id']]);
            
            $pdo->commit();
            $success = "Animal deleted successfully!";
            
            // Redirect after 2 seconds
            header("Refresh: 2; URL=animals.php");
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to delete animal. Please try again.";
        }
    } else {
        header("Location: animals.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Animal - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-trash"></i> Delete Animal</h1>
                <a href="animals.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Animals
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php else: ?>

            <div class="delete-confirmation">
                <div class="warning-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h2>Are you sure you want to delete this animal?</h2>
                </div>

                <div class="animal-info-card">
                    <h3>Animal Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Tag Number:</label>
                            <span><?php echo htmlspecialchars($animal['tag_number']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($animal['name'] ?: 'Unnamed'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Species:</label>
                            <span><?php echo htmlspecialchars($animal['species']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Breed:</label>
                            <span><?php echo htmlspecialchars($animal['breed'] ?: 'Not specified'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Health Status:</label>
                            <span class="health-status <?php echo strtolower($animal['health_status']); ?>">
                                <?php echo htmlspecialchars($animal['health_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="delete-warning">
                    <p><strong>Warning:</strong> This action cannot be undone. All data related to this animal will be permanently deleted.</p>
                </div>

                <form method="POST" class="delete-form">
                    <div class="form-actions">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Confirm Delete
                        </button>
                        <a href="animals.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>

            <?php endif; ?>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>