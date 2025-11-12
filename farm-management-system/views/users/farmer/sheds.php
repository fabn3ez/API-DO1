<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'farmer') {
    header('Location: ../../auth/login.php');
    exit();
}
require_once __DIR__ . '/../../../config/db.php';
$database = new Database();
$conn = $database->getConnection();
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
            $insert_stmt = $conn->prepare("
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
            $update_stmt = $conn->prepare("
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
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM animals WHERE shed_id = ? AND user_id = ?");
        $check_stmt->execute([$shed_id, $_SESSION['user_id']]);
        $animal_count = $check_stmt->fetchColumn();
        
        if ($animal_count > 0) {
            $error = "Cannot delete shed. There are animals assigned to this shed. Please reassign or remove the animals first.";
        } else {
            $delete_stmt = $conn->prepare("DELETE FROM sheds WHERE id = ? AND user_id = ?");
            if ($delete_stmt->execute([$shed_id, $_SESSION['user_id']])) {
                $success = "Shed deleted successfully!";
            } else {
                $error = "Failed to delete shed. Please try again.";
            }
        }
    }
}

// Fetch all sheds
$sheds_stmt = $conn->prepare("
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
$animals_stmt = $conn->prepare("
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
    <style>
        body {
            background: linear-gradient(135deg, #43ea5e 0%, #1b8a3c 100%);
            color: #1b8a3c;
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        }
        .container {
            background: linear-gradient(135deg, #a67c52 0%, #eaffea 100%);
            border-radius: 16px;
            box-shadow: 0 6px 32px rgba(67,234,94,0.12);
            padding: 40px 32px;
            margin: 60px auto 0 auto;
            max-width: 700px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .main-content h1 {
            color: #4e3b1f;
            font-size: 2.2rem;
            margin-bottom: 22px;
            font-family: 'Poppins', serif;
            letter-spacing: 1px;
            text-align: center;
            text-shadow: 0 2px 8px #eaffea, 0 1px 0 #a67c52;
        }
        .form-section {
            background: #a67c52;
            border-radius: 10px;
            padding: 22px 18px;
            margin-bottom: 28px;
            box-shadow: 0 2px 12px rgba(67,234,94,0.13);
        }
        .form-group label {
            color: #fffbe6;
            font-weight: 700;
            font-size: 1.05rem;
            text-shadow: 0 1px 2px #4e3b1f;
        }
        .shed-form input, .shed-form textarea {
            background: #fffbe6;
            border: 1.5px solid #a67c52;
            border-radius: 6px;
            padding: 12px;
            font-size: 1.05rem;
            color: #4e3b1f;
            margin-bottom: 10px;
        }
        .shed-form input:focus, .shed-form textarea:focus {
            border-color: #43ea5e;
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(90deg, #43ea5e 0%, #1b8a3c 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 22px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(67,234,94,0.08);
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #1b8a3c 0%, #43ea5e 100%);
        }
        .alert-error {
            background: #eaffea;
            color: #1b8a3c;
            border-left: 5px solid #43ea5e;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 5px;
            font-size: 1rem;
        }
        .alert-success {
            background: #43ea5e;
            color: #fff;
            border-left: 5px solid #1b8a3c;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 5px;
            font-size: 1rem;
        }
        .content-header {
            margin-bottom: 28px;
        }
        .shed-management {
            background: #eaffea;
            border-radius: 10px;
            padding: 24px 18px;
            box-shadow: 0 2px 8px rgba(67,234,94,0.10);
        }
        .shed-list {
            margin-top: 18px;
        }
        .shed-item {
            background: #43ea5e;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 1px 6px rgba(67,234,94,0.07);
            display: flex;
            flex-direction: column;
        }
        .shed-item h3 {
            color: #1b8a3c;
            margin-bottom: 6px;
            font-size: 1.15rem;
        }
        .shed-item p {
            color: #fff;
            font-size: 1rem;
        }
        /* Farmer iconography */
        .farmer-icon {
            font-size: 2.2rem;
            color: #43ea5e;
            margin-right: 8px;
        }
        /* Edit Shed Modal */
        #editModal {
            background: rgba(67,234,94,0.15);
        }
        #editModal .modal-content {
            background: #eaffea;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(67,234,94,0.13);
            padding: 32px 24px;
        }
        #editModal label {
            color: #1b8a3c;
            font-weight: 700;
        }
        #editModal input, #editModal textarea {
            background: #eaffea;
            border: 1.5px solid #43ea5e;
            border-radius: 6px;
            padding: 12px;
            font-size: 1.05rem;
            color: #1b8a3c;
        }
        #editModal input:focus, #editModal textarea:focus {
            border-color: #1b8a3c;
            outline: none;
        }
        #editModal .btn-primary {
            background: linear-gradient(90deg, #43ea5e 0%, #1b8a3c 100%);
            color: #fff;
        }
        #editModal .btn-primary:hover {
            background: linear-gradient(90deg, #1b8a3c 0%, #43ea5e 100%);
        }
    </style>
    <!-- Removed inline earthy CSS for consistency. Use main style.css for unified look. -->
</head>
<body>
    <div class="container">
    <?php include __DIR__ . '/../../../includes/header.php'; ?>
        
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

                                <!-- Removed shed-footer navigation -->
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
                <!-- Modal footer removed for cleaner UI -->
            </form>
        </div>
    </div>

    <!-- Removed bottom bar include: only top navigation remains -->

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