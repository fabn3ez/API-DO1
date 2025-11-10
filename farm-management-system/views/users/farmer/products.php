<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('customer');

// Search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 1000;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

// Build query
$query = "
    SELECT i.*, u.farm_name, u.first_name, u.last_name 
    FROM inventory i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.quantity > 0 AND i.status = 'active'
";

$params = [];
$types = '';

// Add search condition
if (!empty($search)) {
    $query .= " AND (i.name LIKE ? OR i.description LIKE ? OR i.category LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

// Add category filter
if (!empty($category)) {
    $query .= " AND i.category = ?";
    $params[] = $category;
    $types .= 's';
}

// Add price range
$query .= " AND i.price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;
$types .= 'dd';

// Add sorting
$sort_options = [
    'name' => 'i.name ASC',
    'name_desc' => 'i.name DESC',
    'price' => 'i.price ASC',
    'price_desc' => 'i.price DESC',
    'newest' => 'i.created_at DESC',
    'popular' => 'i.sold_count DESC'
];
$query .= " ORDER BY " . $sort_options[$sort];

// Fetch products
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for filter
$categories_stmt = $pdo->prepare("SELECT DISTINCT category FROM inventory WHERE quantity > 0 AND status = 'active' ORDER BY category");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-shopping-bag"></i> Farm Products</h1>
                <p>Discover fresh products from local farms</p>
            </div>

            <!-- Search and Filters -->
            <div class="products-filters">
                <form method="GET" class="filter-form">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label for="search">Search Products</label>
                            <div class="search-box">
                                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search products...">
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                                        <?php echo $category == $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="sort">Sort By</label>
                            <select id="sort" name="sort">
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                                <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name Z-A</option>
                                <option value="price" <?php echo $sort == 'price' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Price Range</label>
                            <div class="price-range">
                                <input type="number" name="min_price" value="<?php echo $min_price; ?>" placeholder="Min" step="0.01" min="0">
                                <span>to</span>
                                <input type="number" name="max_price" value="<?php echo $max_price; ?>" placeholder="Max" step="0.01" min="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="products.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="products-section">
                <?php if (empty($products)): ?>
                    <div class="no-products">
                        <i class="fas fa-search fa-3x"></i>
                        <h3>No products found</h3>
                        <p>Try adjusting your search criteria or browse different categories.</p>
                        <a href="products.php" class="btn btn-primary">View All Products</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="../../assets/images/products/<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <button class="btn-icon wishlist-btn" data-product="<?php echo $product['id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <button class="btn-icon quick-view" data-product="<?php echo $product['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                
                                <div class="product-meta">
                                    <span class="category"><?php echo htmlspecialchars($product['category']); ?></span>
                                    <span class="farm"><?php echo htmlspecialchars($product['farm_name']); ?></span>
                                </div>

                                <div class="product-price">
                                    <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if ($product['quantity'] < 10): ?>
                                        <span class="stock low-stock">Only <?php echo $product['quantity']; ?> left</span>
                                    <?php else: ?>
                                        <span class="stock in-stock">In Stock</span>
                                    <?php endif; ?>
                                </div>

                                <div class="product-footer">
                                    <form method="POST" action="cart.php" class="add-to-cart-form">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <div class="quantity-selector">
                                            <button type="button" class="qty-btn minus">-</button>
                                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="qty-input">
                                            <button type="button" class="qty-btn plus">+</button>
                                        </div>
                                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick View Modal -->
            <div id="quickViewModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Product Details</h2>
                        <span class="close">&times;</span>
                    </div>
                    <div class="modal-body" id="quickViewContent">
                        <!-- Content loaded via AJAX -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Quantity selector functionality
        document.querySelectorAll('.qty-btn').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('.qty-input');
                let value = parseInt(input.value);
                
                if (this.classList.contains('plus')) {
                    if (value < parseInt(input.max)) {
                        input.value = value + 1;
                    }
                } else if (this.classList.contains('minus')) {
                    if (value > parseInt(input.min)) {
                        input.value = value - 1;
                    }
                }
            });
        });

        // Quick view modal
        const quickViewModal = document.getElementById('quickViewModal');
        const quickViewButtons = document.querySelectorAll('.quick-view');
        const closeBtn = document.querySelector('.close');

        quickViewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.product;
                loadQuickView(productId);
            });
        });

        closeBtn.addEventListener('click', () => {
            quickViewModal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target == quickViewModal) {
                quickViewModal.style.display = 'none';
            }
        });

        function loadQuickView(productId) {
            // In a real application, this would be an AJAX call to fetch product details
            // For now, we'll redirect to product_details.php
            window.location.href = `product_details.php?id=${productId}`;
        }

        // Wishlist functionality
        document.querySelectorAll('.wishlist-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.product;
                const heartIcon = this.querySelector('i');
                
                // Toggle heart icon
                if (heartIcon.classList.contains('far')) {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas');
                    addToWishlist(productId);
                } else {
                    heartIcon.classList.remove('fas');
                    heartIcon.classList.add('far');
                    removeFromWishlist(productId);
                }
            });
        });

        function addToWishlist(productId) {
            // AJAX call to add to wishlist
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `add_to_wishlist=true&product_id=${productId}`
            });
        }

        function removeFromWishlist(productId) {
            // AJAX call to remove from wishlist
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `remove_from_wishlist=true&product_id=${productId}`
            });
        }
    </script>
</body>
</html>