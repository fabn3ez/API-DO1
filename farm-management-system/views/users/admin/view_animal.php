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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal Details - Farm Management System</title>
    <style>
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
        }

        .detail-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--forest-green);
        }

        .page-title {
            color: var(--forest-green);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.8rem;
        }

        .animal-header {
            background: linear-gradient(135deg, var(--sky-blue), var(--forest-green));
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .animal-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .animal-basic-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: var(--wheat);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid var(--forest-green);
        }

        .info-label {
            font-size: 0.9rem;
            color: var(--dark-brown);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .info-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--forest-green);
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-section {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 10px;
        }

        .section-title {
            color: var(--forest-green);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
        }

        .detail-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .detail-label {
            font-weight: 500;
            color: var(--dark-brown);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            color: #333;
        }

        .notes-section {
            background: #f0f8ff;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
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

        .btn-edit {
            background: var(--sky-blue);
            color: white;
        }

        .empty-notes {
            color: #666;
            font-style: italic;
        }

        .created-info {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="detail-container">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title">
                        <span>👁️</span>
                        <span>Animal Details</span>
                    </div>
                    <div class="action-buttons">
                        <a href="animal_edit.php?id=<?php echo $animal['id']; ?>" class="btn btn-edit">
                            <span>✏️</span>
                            <span>Edit</span>
                        </a>
                        <a href="animals_list.php" class="btn btn-secondary">
                            <span>↩️</span>
                            <span>Back to List</span>
                        </a>
                    </div>
                </div>

                <!-- Animal Header -->
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
                    <h1><?php echo $animal['type']; ?> - <?php echo $animal['breed']; ?></h1>
                    <p>Shed: <?php echo $animal['shed_no']; ?> | Record ID: #<?php echo $animal['id']; ?></p>
                </div>

                <!-- Basic Info Cards -->
                <div class="animal-basic-info">
                    <div class="info-card">
                        <div class="info-label">Total Animals</div>
                        <div class="info-value"><?php echo $animal['number']; ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Average Weight</div>
                        <div class="info-value"><?php echo $animal['avg_weight']; ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo $animal['gender']; ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Shed Location</div>
                        <div class="info-value"><?php echo $animal['shed_no']; ?></div>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="details-grid">
                    <div class="detail-section">
                        <div class="section-title">
                            <span>📋</span>
                            <span>Basic Information</span>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Animal Type</div>
                            <div class="detail-value"><?php echo $animal['type']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Breed</div>
                            <div class="detail-value"><?php echo $animal['breed']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Gender Distribution</div>
                            <div class="detail-value"><?php echo $animal['gender']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Housing</div>
                            <div class="detail-value"><?php echo $animal['shed_no']; ?></div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <div class="section-title">
                            <span>⚖️</span>
                            <span>Physical Attributes</span>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Total Count</div>
                            <div class="detail-value"><?php echo $animal['number']; ?> animals</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Average Weight</div>
                            <div class="detail-value"><?php echo $animal['avg_weight']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span style="color: var(--forest-green); font-weight: bold;">● Active</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Last Updated</div>
                            <div class="detail-value">
                                <?php 
                                if (isset($animal['updated_at']) && $animal['updated_at'] !== null) {
                                    echo date('M j, Y g:i A', strtotime($animal['updated_at']));
                                } else {
                                    echo 'Never updated';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="notes-section">
                    <div class="section-title">
                        <span>📝</span>
                        <span>Additional Notes</span>
                    </div>
                    <?php if (!empty($animal['notes'])): ?>
                        <div class="detail-value" style="line-height: 1.6; white-space: pre-wrap;"><?php echo htmlspecialchars($animal['notes']); ?></div>
                    <?php else: ?>
                        <div class="empty-notes">No additional notes provided for this animal group.</div>
                    <?php endif; ?>
                </div>

                <!-- Creation Info -->
                <div class="created-info">
                    Record created on <?php echo date('F j, Y', strtotime($animal['created_at'])); ?> 
                    at <?php echo date('g:i A', strtotime($animal['created_at'])); ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>