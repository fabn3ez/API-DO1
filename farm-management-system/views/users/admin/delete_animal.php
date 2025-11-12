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
$deleted = false;

// Get animal data if ID is provided
if (isset($_GET['id'])) {
    $animal_id = intval($_GET['id']);
    
    // Get animal details for confirmation message
    $stmt = $conn->prepare("SELECT * FROM animals WHERE id = ?");
    $stmt->bind_param("i", $animal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $animal = $result->fetch_assoc();
    $stmt->close();
}

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $animal_id = intval($_POST['animal_id']);
    
    $stmt = $conn->prepare("DELETE FROM animals WHERE id = ?");
    $stmt->bind_param("i", $animal_id);
    
    if ($stmt->execute()) {
        $message = '✅ Animal record deleted successfully!';
        $message_type = 'success';
        $deleted = true;
        
        // Also delete related records (optional - uncomment if you have related tables)
        // $conn->query("DELETE FROM animal_health WHERE animal_id = $animal_id");
        // $conn->query("DELETE FROM animal_feeding WHERE animal_id = $animal_id");
    } else {
        $message = '❌ Error deleting animal record: ' . $conn->error;
        $message_type = 'error';
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Animal - Farm Management System</title>
    <style>
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
            --danger-red: #dc3545;
            --warning-orange: #ffc107;
        }

        .delete-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--danger-red);
        }

        .page-title {
            color: var(--danger-red);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
        }

        .warning-section {
            background: #fff3cd;
            border: 2px solid var(--warning-orange);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .warning-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .warning-title {
            color: #856404;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .warning-text {
            color: #856404;
            line-height: 1.5;
        }

        .animal-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--danger-red);
        }

        .animal-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1rem;
        }

        .animal-icon {
            font-size: 2.5rem;
        }

        .animal-info {
            flex: 1;
        }

        .animal-name {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--dark-brown);
            margin-bottom: 0.5rem;
        }

        .animal-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .meta-item {
            text-align: center;
            padding: 0.5rem;
            background: white;
            border-radius: 6px;
        }

        .meta-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .meta-value {
            font-weight: bold;
            color: var(--dark-brown);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
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
            min-width: 140px;
            justify-content: center;
        }

        .btn-danger {
            background: var(--danger-red);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--forest-green);
            color: white;
        }

        .btn-success:hover {
            background: var(--earth-brown);
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-weight: 500;
            text-align: center;
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

        .success-section {
            text-align: center;
            padding: 2rem;
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--forest-green);
        }

        .consequences {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin: 1.5rem 0;
            border-left: 4px solid var(--warning-orange);
        }

        .consequences-title {
            color: var(--dark-brown);
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .consequences-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .consequences-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .consequences-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="delete-container">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title">
                        <span>🗑️</span>
                        <span>Delete Animal</span>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type === 'error' ? 'error' : 'success'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($deleted): ?>
                    <!-- Success State -->
                    <div class="success-section">
                        <div class="success-icon">✅</div>
                        <h2 style="color: var(--forest-green); margin-bottom: 1rem;">Animal Deleted Successfully</h2>
                        <p style="color: #666; margin-bottom: 2rem; line-height: 1.6;">
                            The animal record has been permanently removed from the system.
                        </p>
                        <div class="action-buttons">
                            <a href="animals_list.php" class="btn btn-success">
                                <span>📋</span>
                                <span>Back to Animals List</span>
                            </a>
                            <a href="animal_add.php" class="btn btn-secondary">
                                <span>➕</span>
                                <span>Add New Animal</span>
                            </a>
                        </div>
                    </div>

                <?php elseif ($animal): ?>
                    <!-- Confirmation State -->
                    <div class="warning-section">
                        <div class="warning-icon">⚠️</div>
                        <div class="warning-title">Confirm Deletion</div>
                        <div class="warning-text">
                            You are about to permanently delete this animal record. This action cannot be undone.
                        </div>
                    </div>

                    <!-- Animal Details -->
                    <div class="animal-details">
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
                            <div class="animal-icon"><?php echo $icon; ?></div>
                            <div class="animal-info">
                                <div class="animal-name">
                                    <?php echo $animal['type']; ?> - <?php echo $animal['breed']; ?>
                                </div>
                                <div style="color: #666; font-size: 0.9rem;">
                                    Shed: <?php echo $animal['shed_no']; ?> | Record ID: #<?php echo $animal['id']; ?>
                                </div>
                            </div>
                        </div>

                        <div class="animal-meta">
                            <div class="meta-item">
                                <div class="meta-label">Total Animals</div>
                                <div class="meta-value"><?php echo $animal['number']; ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Average Weight</div>
                                <div class="meta-value"><?php echo $animal['avg_weight']; ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Gender</div>
                                <div class="meta-value"><?php echo $animal['gender']; ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Created</div>
                                <div class="meta-value"><?php echo date('M j, Y', strtotime($animal['created_at'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Consequences Warning -->
                    <div class="consequences">
                        <div class="consequences-title">
                            <span>📋</span>
                            <span>What will be deleted:</span>
                        </div>
                        <ul class="consequences-list">
                            <li>🗂️ Animal record and all associated data</li>
                            <li>📊 This animal's contribution to farm statistics</li>
                            <li>📈 Any production records linked to this animal</li>
                            <li>💊 Health and medical records for this animal</li>
                        </ul>
                    </div>

                    <!-- Confirmation Form -->
                    <form method="POST" action="">
                        <input type="hidden" name="animal_id" value="<?php echo $animal['id']; ?>">
                        <div class="action-buttons">
                            <button type="submit" name="confirm_delete" class="btn btn-danger" onclick="return confirm('Are you absolutely sure? This cannot be undone!')">
                                <span>🗑️</span>
                                <span>Delete Permanently</span>
                            </button>
                            <a href="animals_list.php" class="btn btn-secondary">
                                <span>↩️</span>
                                <span>Cancel</span>
                            </a>
                            <a href="view_animal.php?id=<?php echo $animal['id']; ?>" class="btn" style="background: var(--sky-blue); color: white;">
                                <span>👁️</span>
                                <span>View Details</span>
                            </a>
                        </div>
                    </form>

                <?php else: ?>
                    <!-- Animal Not Found -->
                    <div class="warning-section">
                        <div class="warning-icon">❓</div>
                        <div class="warning-title">Animal Not Found</div>
                        <div class="warning-text">
                            The animal record you're trying to delete doesn't exist or has already been deleted.
                        </div>
                        <div class="action-buttons" style="margin-top: 1.5rem;">
                            <a href="animals_list.php" class="btn btn-secondary">
                                <span>📋</span>
                                <span>Back to Animals List</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add extra confirmation for delete button
            const deleteButton = document.querySelector('.btn-danger');
            if (deleteButton) {
                deleteButton.addEventListener('click', function(e) {
                    if (!confirm('🚨 FINAL WARNING: This will permanently delete the animal record and all associated data. This action cannot be undone!\n\nClick OK to confirm deletion.')) {
                        e.preventDefault();
                    }
                });
            }

            // Add visual feedback for buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>