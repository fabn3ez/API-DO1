<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('farmer');

$animal_id = isset($_GET['animal_id']) ? intval($_GET['animal_id']) : null;
$error = '';
$success = '';

// Fetch animals for dropdown
$animals_stmt = $pdo->prepare("SELECT id, tag_number, name FROM animals WHERE user_id = ? ORDER BY tag_number");
$animals_stmt->execute([$_SESSION['user_id']]);
$animals = $animals_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle health record submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_record'])) {
    $record_animal_id = $_POST['animal_id'];
    $checkup_date = $_POST['checkup_date'];
    $health_condition = trim($_POST['health_condition']);
    $treatment = trim($_POST['treatment']);
    $vaccination = trim($_POST['vaccination']);
    $vet_name = trim($_POST['vet_name']);
    $notes = trim($_POST['notes']);
    $next_checkup = $_POST['next_checkup'] ?: null;

    if (empty($checkup_date) || empty($health_condition)) {
        $error = "Checkup date and health condition are required fields.";
    } else {
        $insert_stmt = $pdo->prepare("
            INSERT INTO health_records 
            (animal_id, checkup_date, health_condition, treatment, vaccination, vet_name, notes, next_checkup, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        if ($insert_stmt->execute([
            $record_animal_id, $checkup_date, $health_condition, $treatment, 
            $vaccination, $vet_name, $notes, $next_checkup
        ])) {
            $success = "Health record added successfully!";
            
            // Update animal's health status if provided
            if (isset($_POST['update_health_status']) && !empty($_POST['health_status'])) {
                $update_stmt = $pdo->prepare("UPDATE animals SET health_status = ? WHERE id = ? AND user_id = ?");
                $update_stmt->execute([$_POST['health_status'], $record_animal_id, $_SESSION['user_id']]);
            }
        } else {
            $error = "Failed to add health record. Please try again.";
        }
    }
}

// Fetch health records
$health_records = [];
if ($animal_id) {
    $records_stmt = $pdo->prepare("
        SELECT hr.*, a.tag_number, a.name as animal_name 
        FROM health_records hr 
        JOIN animals a ON hr.animal_id = a.id 
        WHERE hr.animal_id = ? AND a.user_id = ? 
        ORDER BY hr.checkup_date DESC
    ");
    $records_stmt->execute([$animal_id, $_SESSION['user_id']]);
    $health_records = $records_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch current animal details if animal_id is provided
$current_animal = null;
if ($animal_id) {
    $animal_stmt = $pdo->prepare("SELECT * FROM animals WHERE id = ? AND user_id = ?");
    $animal_stmt->execute([$animal_id, $_SESSION['user_id']]);
    $current_animal = $animal_stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Records - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-heartbeat"></i> Health Records</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="health-management">
                <!-- Add Health Record Form -->
                <div class="form-section">
                    <h2><i class="fas fa-plus-circle"></i> Add Health Record</h2>
                    <form method="POST" class="health-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="animal_id">Animal *</label>
                                <select id="animal_id" name="animal_id" required 
                                    onchange="if(this.value) window.location.href = 'health.php?animal_id=' + this.value">
                                    <option value="">Select Animal</option>
                                    <?php foreach ($animals as $animal): ?>
                                        <option value="<?php echo $animal['id']; ?>" 
                                            <?php echo ($animal_id == $animal['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($animal['tag_number'] . ' - ' . ($animal['name'] ?: 'Unnamed')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="checkup_date">Checkup Date *</label>
                                <input type="date" id="checkup_date" name="checkup_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="health_condition">Health Condition *</label>
                                <input type="text" id="health_condition" name="health_condition" required>
                            </div>

                            <div class="form-group">
                                <label for="treatment">Treatment</label>
                                <textarea id="treatment" name="treatment" rows="3" placeholder="Treatment given..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="vaccination">Vaccination</label>
                                <input type="text" id="vaccination" name="vaccination" placeholder="Vaccination details...">
                            </div>

                            <div class="form-group">
                                <label for="vet_name">Veterinarian Name</label>
                                <input type="text" id="vet_name" name="vet_name" placeholder="Vet's name...">
                            </div>

                            <div class="form-group">
                                <label for="next_checkup">Next Checkup Date</label>
                                <input type="date" id="next_checkup" name="next_checkup">
                            </div>

                            <?php if ($current_animal): ?>
                            <div class="form-group">
                                <label for="health_status">Update Animal Health Status</label>
                                <select id="health_status" name="health_status">
                                    <option value="">Keep Current Status</option>
                                    <option value="Excellent">Excellent</option>
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="4" placeholder="Additional notes..."></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="add_record" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Health Record
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Health Records List -->
                <?php if ($animal_id && $current_animal): ?>
                <div class="records-section">
                    <h2>
                        <i class="fas fa-history"></i> Health History for 
                        <?php echo htmlspecialchars($current_animal['tag_number'] . ' - ' . ($current_animal['name'] ?: 'Unnamed')); ?>
                    </h2>

                    <?php if (empty($health_records)): ?>
                        <div class="no-data">
                            <p>No health records found for this animal.</p>
                        </div>
                    <?php else: ?>
                        <div class="records-list">
                            <?php foreach ($health_records as $record): ?>
                            <div class="record-card">
                                <div class="record-header">
                                    <h3><?php echo date('M j, Y', strtotime($record['checkup_date'])); ?></h3>
                                    <span class="health-condition"><?php echo htmlspecialchars($record['health_condition']); ?></span>
                                </div>
                                
                                <div class="record-details">
                                    <?php if (!empty($record['treatment'])): ?>
                                        <p><strong>Treatment:</strong> <?php echo nl2br(htmlspecialchars($record['treatment'])); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record['vaccination'])): ?>
                                        <p><strong>Vaccination:</strong> <?php echo htmlspecialchars($record['vaccination']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record['vet_name'])): ?>
                                        <p><strong>Veterinarian:</strong> <?php echo htmlspecialchars($record['vet_name']); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record['next_checkup'])): ?>
                                        <p><strong>Next Checkup:</strong> <?php echo date('M j, Y', strtotime($record['next_checkup'])); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record['notes'])): ?>
                                        <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($record['notes'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="record-footer">
                                    <small>Recorded on: <?php echo date('M j, Y g:i A', strtotime($record['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php elseif ($animal_id): ?>
                    <div class="no-data">
                        <p>Animal not found or you don't have permission to view it.</p>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>Please select an animal to view its health records.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>