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

// Get all animals
$animals = $conn->query("SELECT * FROM animals ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Animals - Farm Management System</title>
    <style>
        /* Reuse farm theme styles from dashboard */
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
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
        
        .toolbar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .search-box {
            padding: 10px 15px;
            border: 2px solid var(--forest-green);
            border-radius: 25px;
            width: 300px;
            font-size: 1rem;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
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
        
        .animals-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .animals-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .animals-table th {
            background: var(--forest-green);
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        .animals-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .animals-table tr:hover {
            background: #f9f9f9;
        }
        
        .animal-type {
            font-size: 1.5rem;
            text-align: center;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }
        
        .edit-btn { background: var(--sky-blue); color: white; }
        .view-btn { background: var(--forest-green); color: white; }
        .delete-btn { background: #ff4444; color: white; }
        
        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 2px solid var(--forest-green);
            border-radius: 8px;
            background: white;
        }
    </style>
</head>
<body>
    <!-- Reuse Header and Sidebar from dashboard -->
    <?php 
    // Include header logic here or reuse from dashboard
    // For now, we'll use a simplified version
    ?>
    
    <div class="container">
        <!-- Sidebar would be included here -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>🐄</span>
                    <span>My Animals</span>
                </div>
                <a href="add_animal.php" class="btn btn-primary">
                    <span>➕</span>
                    <span>Add New Animal</span>
                </a>
                <a href ="dashboard.php" class="btn btn-secondary">
                    <span>🏠</span>
                    <span>Back to Dashboard</span>
                </a>
            </div>
            
            <!-- Toolbar -->
            <div class="toolbar">
                <input type="text" class="search-box" placeholder="🔍 Search animals...">
                <div class="filters">
                    <select class="filter-select">
                        <option>All Types</option>
                        <option>Cattle</option>
                        <option>Poultry</option>
                        <option>Goats</option>
                        <option>Sheep</option>
                    </select>
                    <select class="filter-select">
                        <option>All Sheds</option>
                        <option>Shed 1</option>
                        <option>Shed 2</option>
                        <option>Shed 3</option>
                    </select>
                </div>
            </div>
            
            <!-- Animals Table -->
            <div class="animals-table">
                <table>
                    <thead>
                        <tr>
                            <th width="80">Type</th>
                            <th>Breed</th>
                            <th>Gender</th>
                            <th width="120">Count</th>
                            <th width="120">Avg Weight</th>
                            <th width="100">Shed</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($animals as $animal): ?>
                        <tr>
                            <td class="animal-type">
                                <?php 
                                $icons = [
                                    'Cow' => '🐄', 'Cattle' => '🐂', 'Hen' => '🐔', 'Cock' => '🐓',
                                    'Goat' => '🐐', 'Sheep' => '🐑', 'Rabbit' => '🐇', 'Horse' => '🐎',
                                    'Dog' => '🐕', 'Cat' => '🐈', 'Fish' => '🐟', 'Turkey' => '🦃',
                                    'Goose' => '🦆'
                                ];
                                echo $icons[$animal['type']] ?? '🐾';
                                ?>
                            </td>
                            <td><strong><?php echo $animal['breed']; ?></strong></td>
                            <td><?php echo $animal['gender']; ?></td>
                            <td><?php echo $animal['number']; ?></td>
                            <td><?php echo $animal['avg_weight']; ?></td>
                            <td><?php echo $animal['shed_no']; ?></td>
                            <td class="action-buttons">
                                <a href="edit_animal.php?id=<?php echo $animal['id']; ?>" class="action-btn edit-btn">✏️ Edit</a>
                                <a href="view_animal.php?id=<?php echo $animal['id']; ?>" class="action-btn view-btn">👁️ View</a>
                                <a href="delete_animal.php?id=<?php echo $animal['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this animal?')">🗑️ Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.querySelector('.search-box').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.animals-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>