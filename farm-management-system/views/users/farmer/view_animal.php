<?php
session_start();
require_once '../../auth/check_role.php';
check_role('farmer');

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
    header('Location: animals.php');
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Animal - Farm Management System</title>
    <style>
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
        }
        
        .animal-details {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .animal-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--forest-green);
        }
        
        .animal-icon {
            font-size: 3rem;
        }
        
        .animal-title {
            color: var(--forest-green);
            font-size: 1.8rem;
            margin: 0;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .detail-item {
            padding: 1rem;
            background: var(--wheat);
            border-radius: 8px;
            border-left: 4px solid var(--forest-green);
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark-brown);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail-value {
            font-size: 1.1rem;
            color: var(--forest-green);
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--forest-green);
            color: white;
        }
        
        .btn-secondary {
            background: var(--sky-blue);
            color: var(--dark-brown);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
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
                    <h1 class="animal-title"><?php echo $animal['type']; ?> - <?php echo $animal['breed']; ?></h1>
                </div>
                
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">🏷️ Breed</div>
                        <div class="detail-value"><?php echo $animal['breed']; ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">⚧️ Gender</div>
                        <div class="detail-value"><?php echo $animal['gender']; ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">🔢 Number</div>
                        <div class="detail-value"><?php echo $animal['number']; ?> animals</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">⚖️ Average Weight</div>
                        <div class="detail-value"><?php echo $animal['avg_weight']; ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">🏠 Shed Location</div>
                        <div class="detail-value"><?php echo $animal['shed_no']; ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">📅 Record Created</div>
                        <div class="detail-value"><?php echo date('M j, Y', strtotime($animal['created_at'])); ?></div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="edit_animal.php?id=<?php echo $animal['id']; ?>" class="btn btn-primary">
                        <span>✏️</span>
                        <span>Edit Animal</span>
                    </a>
                    <a href="animals.php" class="btn btn-secondary">
                        <span>↩️</span>
                        <span>Back to Animals</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>