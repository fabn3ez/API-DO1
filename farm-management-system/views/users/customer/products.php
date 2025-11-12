<?php
session_start();
require_once '../../auth/check_role.php';
check_role('customer');

// Database connection
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$breed_filter = $_GET['breed'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

// Build query for animals
$query = "SELECT * FROM animals WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (type LIKE ? OR breed LIKE ? OR shed_no LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

if (!empty($type_filter)) {
    $query .= " AND type = ?";
    $params[] = $type_filter;
    $types .= "s";
}

if (!empty($breed_filter)) {
    $query .= " AND breed = ?";
    $params[] = $breed_filter;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$animals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique types and breeds for filters
$types_query = $conn->query("SELECT DISTINCT type FROM animals ORDER BY type");
$unique_types = $types_query->fetch_all(MYSQLI_ASSOC);

$breeds_query = $conn->query("SELECT DISTINCT breed FROM animals ORDER BY breed");
$unique_breeds = $breeds_query->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Animals - Farm Management System</title>
    <style>
        /* Farm Theme Styles */
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--cream-white);
            color: var(--dark-brown);
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(to right, var(--forest-green), var(--earth-brown));
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Main Layout */
        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: var(--wheat);
            padding: 2rem 1rem;
            border-right: 3px solid var(--earth-brown);
        }
        
        .nav-item {
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            text-decoration: none;
            color: var(--dark-brown);
        }
        
        .nav-item:hover, .nav-item.active {
            background-color: var(--forest-green);
            color: white;
            transform: translateX(5px);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.03"><text x="50" y="50" font-size="80" text-anchor="middle" dominant-baseline="middle">🐄</text></svg>');
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
        
        /* Search and Filters */
        .search-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            margin-bottom: 0.5rem;
            color: var(--dark-brown);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control {
            padding: 10px 15px;
            border: 2px solid var(--forest-green);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--earth-brown);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
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
            font-size: 1rem;
        }
        
        .btn-primary {
            background: var(--forest-green);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--earth-brown);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: var(--sky-blue);
            color: var(--dark-brown);
        }
        
        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--forest-green);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }
        
        .product-image {
            height: 200px;
            background: linear-gradient(135deg, var(--sky-blue), var(--forest-green));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-type {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .product-breed {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--forest-green);
            margin-bottom: 0.5rem;
        }
        
        .product-details {
            margin-bottom: 1rem;
            color: var(--dark-brown);
        }
        
        .product-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--earth-brown);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-small {
            padding: 8px 15px;
            font-size: 0.9rem;
            flex: 1;
            text-align: center;
        }
        
        .btn-wishlist {
            background: #ff6b6b;
            color: white;
            flex: 0 0 auto;
            width: 40px;
            justify-content: center;
        }
        
        .btn-wishlist:hover {
            background: #ff5252;
        }
        
        /* No Results */
        .no-results {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .no-results-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        /* Results Count */
        .results-count {
            margin-bottom: 1rem;
            color: var(--dark-brown);
            font-weight: 500;
        }
        
        /* Animal Icons Mapping */
        .animal-icon {
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <span>🚜</span>
            <span>FARM MANAGEMENT SYSTEM</span>
        </div>
        <div class="user-menu">
            <span>👋 Welcome, <?php echo $_SESSION['username']; ?> (Customer)</span>
            <span>🔔</span>
            <a href="../../auth/logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="dashboard.php" class="nav-item">
                <span>📊</span>
                <span>Dashboard</span>
            </a>
            <a href="products.php" class="nav-item active">
                <span>🐄</span>
                <span>Browse Animals</span>
            </a>
            <a href="orders.php" class="nav-item">
                <span>📋</span>
                <span>My Orders</span>
            </a>
            <a href="cart.php" class="nav-item">
                <span>🛒</span>
                <span>Shopping Cart</span>
            </a>
            <a href="wishlist.php" class="nav-item">
                <span>❤️</span>
                <span>Wishlist</span>
            </a>
            <a href="profile.php" class="nav-item">
                <span>👤</span>
                <span>My Profile</span>
            </a>
            <a href="support.php" class="nav-item">
                <span>📞</span>
                <span>Support</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <span>🐄</span>
                    <span>Browse Farm Animals</span>
                </div>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <span class="results-count">
                        <?php echo count($animals); ?> animals found
                    </span>
                    <a href="cart.php" class="btn btn-secondary">
                        <span>🛒</span>
                        <span>View Cart</span>
                    </a>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="search-section">
                <form method="GET" action="" class="search-form">
                    <div class="form-group">
                        <label class="form-label">🔍 Search Animals</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by type, breed, or shed..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">🐾 Animal Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <?php foreach($unique_types as $type): ?>
                                <option value="<?php echo $type['type']; ?>" 
                                    <?php echo $type_filter === $type['type'] ? 'selected' : ''; ?>>
                                    <?php echo $type['type']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">🏷️ Breed</label>
                        <select name="breed" class="form-control">
                            <option value="">All Breeds</option>
                            <?php foreach($unique_breeds as $breed): ?>
                                <option value="<?php echo $breed['breed']; ?>" 
                                    <?php echo $breed_filter === $breed['breed'] ? 'selected' : ''; ?>>
                                    <?php echo $breed['breed']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary">
                            <span>🔍</span>
                            <span>Search</span>
                        </button>
                    </div>
                </form>
                
                <?php if (!empty($search) || !empty($type_filter) || !empty($breed_filter)): ?>
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="products.php" class="btn btn-secondary" style="padding: 5px 15px; font-size: 0.9rem;">
                        <span>🗑️</span>
                        <span>Clear Filters</span>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Products Grid -->
            <?php if (!empty($animals)): ?>
            <div class="products-grid">
                <?php foreach($animals as $animal): ?>
                <?php
                // Calculate price based on animal type and weight
                $base_prices = [
                    'Cow' => 50000, 'Cattle' => 45000, 'Hen' => 800, 'Cock' => 1200,
                    'Goat' => 8000, 'Sheep' => 7000, 'Rabbit' => 1500, 'Horse' => 80000,
                    'Dog' => 10000, 'Cat' => 5000, 'Fish' => 300, 'Turkey' => 4000,
                    'Goose' => 3500
                ];
                
                $base_price = $base_prices[$animal['type']] ?? 5000;
                $weight_factor = 1.0;
                if (preg_match('/(\d+\.?\d*)\s*kg/i', $animal['avg_weight'], $matches)) {
                    $weight = floatval($matches[1]);
                    $weight_factor = $weight / 100; // Adjust factor based on weight
                }
                
                $price = $base_price * $weight_factor * $animal['number'];
                $price_per_unit = $base_price * $weight_factor;
                
                // Animal icons
                $icons = [
                    'Cow' => '🐄', 'Cattle' => '🐂', 'Hen' => '🐔', 'Cock' => '🐓',
                    'Goat' => '🐐', 'Sheep' => '🐑', 'Rabbit' => '🐇', 'Horse' => '🐎',
                    'Dog' => '🐕', 'Cat' => '🐈', 'Fish' => '🐟', 'Turkey' => '🦃',
                    'Goose' => '🦆'
                ];
                $icon = $icons[$animal['type']] ?? '🐾';
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <span><?php echo $icon; ?></span>
                    </div>
                    <div class="product-info">
                        <div class="product-type">
                            <span class="animal-icon"><?php echo $icon; ?></span>
                            <?php echo $animal['type']; ?>
                        </div>
                        <div class="product-breed"><?php echo $animal['breed']; ?></div>
                        
                        <div class="product-details">
                            <div class="product-detail">
                                <span>⚧️</span>
                                <span>Gender: <?php echo $animal['gender']; ?></span>
                            </div>
                            <div class="product-detail">
                                <span>🔢</span>
                                <span>Available: <?php echo $animal['number']; ?> animals</span>
                            </div>
                            <div class="product-detail">
                                <span>⚖️</span>
                                <span>Avg Weight: <?php echo $animal['avg_weight']; ?></span>
                            </div>
                            <div class="product-detail">
                                <span>🏠</span>
                                <span>Shed: <?php echo $animal['shed_no']; ?></span>
                            </div>
                        </div>
                        
                        <div class="product-price">
                            KSh <?php echo number_format($price_per_unit, 2); ?>/animal
                        </div>
                        
                        <div class="product-actions">
                            <a href="view_animal.php?id=<?php echo $animal['id']; ?>" class="btn btn-primary btn-small">
                                <span>👁️</span>
                                <span>View Details</span>
                            </a>
                            <a href="add_to_cart.php?animal_id=<?php echo $animal['id']; ?>&quantity=1" class="btn btn-secondary btn-small">
                                <span>🛒</span>
                                <span>Add to Cart</span>
                            </a>
                            <a href="add_to_wishlist.php?animal_id=<?php echo $animal['id']; ?>" class="btn btn-wishlist">
                                <span>❤️</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">🐄</div>
                <h3>No Animals Found</h3>
                <p>We couldn't find any animals matching your search criteria.</p>
                <p>Try adjusting your filters or <a href="products.php" style="color: var(--forest-green);">browse all animals</a>.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Add to cart functionality
            document.querySelectorAll('.btn-secondary').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (this.href.includes('add_to_cart')) {
                        e.preventDefault();
                        const animalId = this.href.split('animal_id=')[1].split('&')[0];
                        
                        // Show loading state
                        const originalText = this.innerHTML;
                        this.innerHTML = '<span>⏳</span><span>Adding...</span>';
                        this.disabled = true;
                        
                        // Simulate API call
                        setTimeout(() => {
                            this.innerHTML = '<span>✅</span><span>Added!</span>';
                            setTimeout(() => {
                                this.innerHTML = originalText;
                                this.disabled = false;
                                // Redirect to cart page
                                window.location.href = `add_to_cart.php?animal_id=${animalId}&quantity=1`;
                            }, 1000);
                        }, 1000);
                    }
                });
            });
        });
    </script>
</body>
</html>