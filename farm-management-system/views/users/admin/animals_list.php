<?php
// animals_list.php - Animals Management
session_start();
require_once '../auth/check_role.php';
check_role('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animals Management - Farm System</title>
    <style>
        /* Reuse the farm theme styles from dashboard */
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
            justify-content: between;
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
        
        .progress-bar {
            width: 100px;
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--forest-green);
            border-radius: 5px;
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
        }
        
        .edit-btn { background: var(--sky-blue); color: white; }
        .view-btn { background: var(--forest-green); color: white; }
        .delete-btn { background: #ff4444; color: white; }
    </style>
</head>
<body>
    <!-- Reuse Header and Sidebar from dashboard -->
    <?php include 'header.php'; ?>
    
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>🐄</span>
                    <span>Animals Management</span>
                </div>
            </div>
            
            <!-- Toolbar -->
            <div class="toolbar">
                <input type="text" class="search-box" placeholder="🔍 Search animals...">
                <select style="padding: 10px; border: 2px solid var(--forest-green); border-radius: 8px;">
                    <option>🌾 All Types</option>
                    <option>🐄 Cattle</option>
                    <option>🐔 Poultry</option>
                    <option>🐐 Goats</option>
                    <option>🐑 Sheep</option>
                </select>
                <select style="padding: 10px; border: 2px solid var(--forest-green); border-radius: 8px;">
                    <option>🏠 All Sheds</option>
                    <option>🏠 Shed 1</option>
                    <option>🏠 Shed 2</option>
                    <option>🏠 Shed 3</option>
                </select>
                <button class="btn btn-secondary">
                    <span>📤</span>
                    <span>Export</span>
                </button>
                <button class="btn btn-primary" onclick="location.href='animal_add.php'">
                    <span>➕</span>
                    <span>Add New Animal</span>
                </button>
            </div>
            
            <!-- Animals Table -->
            <div class="animals-table">
                <table>
                    <thead>
                        <tr>
                            <th width="80">Type</th>
                            <th>Breed</th>
                            <th width="120">Count</th>
                            <th width="100">Shed</th>
                            <th width="120">Avg Weight</th>
                            <th width="150">Population</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="animal-type">🐄</td>
                            <td><strong>Jersey</strong></td>
                            <td>650</td>
                            <td>Shed 5</td>
                            <td>450 kg</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 85%"></div>
                                </div>
                                <small>85% capacity</small>
                            </td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn view-btn">👁️ View</button>
                                <button class="action-btn delete-btn">🗑️ Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="animal-type">🐔</td>
                            <td><strong>Rhode Island Red</strong></td>
                            <td>900</td>
                            <td>Shed 6</td>
                            <td>2.5 kg</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 90%"></div>
                                </div>
                                <small>90% capacity</small>
                            </td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn view-btn">👁️ View</button>
                                <button class="action-btn delete-btn">🗑️ Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="animal-type">🐐</td>
                            <td><strong>Boer</strong></td>
                            <td>370</td>
                            <td>Shed 7</td>
                            <td>70 kg</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 65%"></div>
                                </div>
                                <small>65% capacity</small>
                            </td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn view-btn">👁️ View</button>
                                <button class="action-btn delete-btn">🗑️ Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td class="animal-type">🐑</td>
                            <td><strong>Dorper</strong></td>
                            <td>230</td>
                            <td>Shed 8</td>
                            <td>60 kg</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 45%"></div>
                                </div>
                                <small>45% capacity</small>
                            </td>
                            <td class="action-buttons">
                                <button class="action-btn edit-btn">✏️ Edit</button>
                                <button class="action-btn view-btn">👁️ View</button>
                                <button class="action-btn delete-btn">🗑️ Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                <div>Showing 1-4 of 14 animals</div>
                <div style="display: flex; gap: 5px;">
                    <button class="btn">⬅️ Previous</button>
                    <button class="btn btn-primary">1</button>
                    <button class="btn">2</button>
                    <button class="btn">3</button>
                    <button class="btn">Next ➡️</button>
                </div>
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