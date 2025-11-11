<?php
session_start();
require_once '../../db.php';
require_once '../../auth/check_role.php';
check_role('farmer');

$error = '';
$success = '';

// Handle shed operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_shed'])) {
        $name = trim($_POST['name']);
        $location = trim($_POST['location']);
        $capacity = intval($_POST['capacity']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $error = "Shed name is required.";
        } else {
            $insert_stmt = $pdo->prepare("
                INSERT INTO sheds (user_id, name, location, capacity, description, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            if ($insert_stmt->execute([$_SESSION['user_id'], $name, $location, $capacity, $description])) {
                $success = "Shed added successfully!";
            } else {
                $error = "Failed to add shed. Please try again.";
            }
        }
    }
    elseif (isset($_POST['edit_shed'])) {
        $shed_id = intval($_POST['shed_id']);
        $name = trim($_POST['name']);
        $location = trim($_POST['location']);
        $capacity = intval($_POST['capacity']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $error = "Shed name is required.";
        } else {
            $update_stmt = $pdo->prepare("
                UPDATE sheds 
                SET name = ?, location = ?, capacity = ?, description = ?, updated_at = NOW() 
                WHERE id = ? AND user_id = ?
            ");
            
            if ($update_stmt->execute([$name, $location, $capacity, $description, $shed_id, $_SESSION['user_id']])) {
                $success = "Shed updated successfully!";
            } else {
                $error = "Failed to update shed. Please try again.";
            }
        }
    }
    elseif (isset($_POST['delete_shed'])) {
        $shed_id = intval($_POST['shed_id']);
        
        // Check if shed has animals
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM animals WHERE shed_id = ? AND user_id = ?");
        $check_stmt->execute([$shed_id, $_SESSION['user_id']]);
        $animal_count = $check_stmt->fetchColumn();
        
        if ($animal_count > 0) {
            $error = "Cannot delete shed. There are animals assigned to this shed. Please reassign or remove the animals first.";
        } else {
            $delete_stmt = $pdo->prepare("DELETE FROM sheds WHERE id = ? AND user_id = ?");
            if ($delete_stmt->execute([$shed_id, $_SESSION['user_id']])) {
                $success = "Shed deleted successfully!";
            } else {
                $error = "Failed to delete shed. Please try again.";
            }
        }
    }
}

// Fetch all sheds
$sheds_stmt = $pdo->prepare("
    SELECT s.*, 
           COUNT(a.id) as animal_count,
           (SELECT COUNT(*) FROM animals WHERE shed_id = s.id AND health_status = 'Critical') as critical_count
    FROM sheds s 
    LEFT JOIN animals a ON s.id = a.shed_id 
    WHERE s.user_id = ? 
    GROUP BY s.id 
    ORDER BY s.name
");
$sheds_stmt->execute([$_SESSION['user_id']]);
$sheds = $sheds_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch animals for dropdown (for assignment)
$animals_stmt = $pdo->prepare("
    SELECT id, tag_number, name, species, shed_id 
    FROM animals 
    WHERE user_id = ? 
    ORDER BY tag_number
");
$animals_stmt->execute([$_SESSION['user_id']]);
$animals = $animals_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shed Management - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-home"></i> Shed Management</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="shed-management">
                <!-- Add Shed Form -->
                <div class="form-section">
                    <h2><i class="fas fa-plus"></i> Add New Shed</h2>
                    <form method="POST" class="shed-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Shed Name *</label>
                                <input type="text" id="name" name="name" required>
                            </div>

                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" placeholder="Shed location...">
                            </div>

                            <div class="form-group">
                                <label for="capacity">Capacity</label>
                                <input type="number" id="capacity" name="capacity" min="1" value="10">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3" placeholder="Shed description..."></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="add_shed" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Shed
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sheds List -->
                <div class="sheds-section">
                    <h2><i class="fas fa-list"></i> Your Sheds</h2>

                    <?php if (empty($sheds)): ?>
                        <div class="no-data">
                            <p>No sheds found. Add your first shed above.</p>
                        </div>
                    <?php else: ?>
                        <div class="sheds-grid">
                            <?php foreach ($sheds as $shed): ?>
                            <div class="shed-card">
                                <div class="shed-header">
                                    <h3><?php echo htmlspecialchars($shed['name']); ?></h3>
                                    <div class="shed-actions">
                                        <button class="btn-icon edit-shed" data-shed='<?php echo json_encode($shed); ?>'>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="shed_id" value="<?php echo $shed['id']; ?>">
                                            <button type="submit" name="delete_shed" class="btn-icon btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this shed?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="shed-info">
                                    <?php if (!empty($shed['location'])): ?>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($shed['location']); ?></p>
                                    <?php endif; ?>
                                    
                                    <p><i class="fas fa-users"></i> 
                                        <?php echo $shed['animal_count']; ?> / <?php echo $shed['capacity']; ?> animals
                                        (<?php echo round(($shed['animal_count'] / $shed['capacity']) * 100); ?>% capacity)
                                    </p>
                                    
                                    <?php if ($shed['critical_count'] > 0): ?>
                                        <p class="critical-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            <?php echo $shed['critical_count']; ?> animal(s) in critical condition
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($shed['description'])): ?>
                                        <p><?php echo nl2br(htmlspecialchars($shed['description'])); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="shed-footer">
                                    <small>Created: <?php echo date('M j, Y', strtotime($shed['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Edit Shed Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Shed</h2>
                <span class="close">&times;</span>
            </div>
            <form method="POST" id="editShedForm">
                <input type="hidden" name="shed_id" id="edit_shed_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Shed Name *</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_location">Location</label>
                        <input type="text" id="edit_location" name="location">
                    </div>

                    <div class="form-group">
                        <label for="edit_capacity">Capacity</label>
                        <input type="number" id="edit_capacity" name="capacity" min="1">
                    </div>

                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_shed" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Shed
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancelEdit">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Modal functionality for editing sheds
        const modal = document.getElementById('editModal');
        const editButtons = document.querySelectorAll('.edit-shed');
        const closeBtn = document.querySelector('.close');
        const cancelBtn = document.getElementById('cancelEdit');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const shed = JSON.parse(this.dataset.shed);
                document.getElementById('edit_shed_id').value = shed.id;
                document.getElementById('edit_name').value = shed.name;
                document.getElementById('edit_location').value = shed.location || '';
                document.getElementById('edit_capacity').value = shed.capacity;
                document.getElementById('edit_description').value = shed.description || '';
                modal.style.display = 'block';
            });
        });

        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        cancelBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>