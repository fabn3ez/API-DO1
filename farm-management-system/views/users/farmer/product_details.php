<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('customer');

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id == 0) {
    header("Location: products.php");
    exit();
}

// Fetch product details
$stmt = $pdo->prepare("
    SELECT i.*, u.farm_name, u.first_name, u.last_name, u.phone as farmer_phone, u.address as farm_address
    FROM inventory i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.id = ? AND i.quantity > 0 AND i.status = 'active'
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit();
}

// Fetch related products
$related_stmt = $pdo->prepare("
    SELECT i.*, u.farm_name 
    FROM inventory i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.category = ? AND i.id != ? AND i.quantity > 0 AND i.status = 'active' 
    ORDER BY RAND() 
    LIMIT 4
");
$related_stmt->execute([$product['category'], $product_id]);
$related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle add to cart
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    
    if ($quantity < 1) {
        $error = "Please select a valid quantity.";
    } elseif ($quantity > $product['quantity']) {
        $error = "Requested quantity exceeds available stock.";
    } else {
        // Add to cart (session-based for now)
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $cart_item = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $product['price'],
            'name' => $product['name'],
            'image' => $product['image_url'],
            'farm_name' => $product['farm_name']
        ];
        
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = $cart_item;
        }
        
        $success = "Product added to cart successfully!";
    }
}

// Handle add to wishlist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_wishlist'])) {
    // In a real application, this would save to database
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    
    if (!in_array($product_id, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'][] = $product_id;
        $success = "Product added to wishlist!";
    } else {
        $error = "Product already in wishlist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-info-circle"></i> Product Details</h1>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="product-details">
                <div class="product-main">
                    <div class="product-gallery">
                        <?php if (!empty($product['image_url'])): ?>
                            <div class="main-image">
                                <img src="../../assets/images/products/<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                        <?php else: ?>
                            <div class="main-image no-image">
                                <i class="fas fa-box-open fa-5x"></i>
                                <p>No Image Available</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-info">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <div class="product-meta">
                            <span class="category"><?php echo htmlspecialchars($product['category']); ?></span>
                            <span class="farm">From: <?php echo htmlspecialchars($product['farm_name']); ?></span>
                        </div>

                        <div class="product-price">
                            <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                            <?php if ($product['quantity'] < 10): ?>
                                <span class="stock low-stock">Only <?php echo $product['quantity']; ?> left in stock</span>
                            <?php else: ?>
                                <span class="stock in-stock">In Stock (<?php echo $product['quantity']; ?> available)</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-actions">
                            <form method="POST" class="add-to-cart-form">
                                <div class="quantity-section">
                                    <label for="quantity">Quantity:</label>
                                    <div class="quantity-selector">
                                        <button type="button" class="qty-btn minus">-</button>
                                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" class="qty-input">
                                        <button type="button" class="qty-btn plus">+</button>
                                    </div>
                                </div>
                                
                                <div class="action-buttons">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-large">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                    <button type="submit" name="add_to_wishlist" class="btn btn-secondary">
                                        <i class="far fa-heart"></i> Add to Wishlist
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="product-specs">
                            <div class="spec-item">
                                <i class="fas fa-weight"></i>
                                <span>Unit: <?php echo htmlspecialchars($product['unit']); ?></span>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-chart-line"></i>
                                <span>Sold: <?php echo $product['sold_count']; ?> units</span>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-calendar"></i>
                                <span>Listed: <?php echo date('M j, Y', strtotime($product['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="product-tabs">
                    <div class="tabs">
                        <button class="tab-link active" data-tab="description">Description</button>
                        <button class="tab-link" data-tab="farm">Farm Information</button>
                        <button class="tab-link" data-tab="shipping">Shipping & Returns</button>
                    </div>

                    <div class="tab-content">
                        <div id="description" class="tab-pane active">
                            <h3>Product Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                            
                            <?php if (!empty($product['specifications'])): ?>
                                <h4>Specifications</h4>
                                <div class="specifications">
                                    <?php echo nl2br(htmlspecialchars($product['specifications'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="farm" class="tab-pane">
                            <h3>Farm Information</h3>
                            <div class="farm-details">
                                <div class="farm-info">
                                    <h4><?php echo htmlspecialchars($product['farm_name']); ?></h4>
                                    <p>Farmer: <?php echo htmlspecialchars($product['first_name'] . ' ' . $product['last_name']); ?></p>
                                    <?php if (!empty($product['farm_address'])): ?>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($product['farm_address']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($product['farmer_phone'])): ?>
                                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($product['farmer_phone']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="farm-description">
                                    <p>This product comes directly from our partner farm, ensuring freshness and quality. All our farms follow sustainable farming practices.</p>
                                </div>
                            </div>
                        </div>

                        <div id="shipping" class="tab-pane">
                            <h3>Shipping & Returns</h3>
                            <div class="shipping-info">
                                <h4>Delivery Options</h4>
                                <ul>
                                    <li><strong>Standard Shipping:</strong> 3-5 business days - $5.99</li>
                                    <li><strong>Express Shipping:</strong> 1-2 business days - $12.99</li>
                                    <li><strong>Farm Pickup:</strong> Free - Schedule with farmer</li>
                                </ul>
                                
                                <h4>Return Policy</h4>
                                <p>We offer a 7-day return policy for all products. Products must be returned in their original condition. Perishable items may have different return conditions.</p>
                                
                                <h4>Freshness Guarantee</h4>
                                <p>All farm products are guaranteed to be fresh and of the highest quality. If you're not satisfied, contact us within 24 hours of delivery.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <?php if (!empty($related_products)): ?>
                <div class="related-products">
                    <h2>Related Products</h2>
                    <div class="products-grid">
                        <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($related['image_url'])): ?>
                                    <img src="../../assets/images/products/<?php echo htmlspecialchars($related['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($related['name']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars(substr($related['description'], 0, 80)); ?>...</p>
                                
                                <div class="product-price">
                                    <span class="price">$<?php echo number_format($related['price'], 2); ?></span>
                                </div>

                                <div class="product-footer">
                                    <a href="product_details.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-link').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs and panes
                document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding pane
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Quantity selector
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

        // Input validation for quantity
        document.querySelector('.qty-input').addEventListener('change', function() {
            let value = parseInt(this.value);
            const max = parseInt(this.max);
            const min = parseInt(this.min);
            
            if (value < min) this.value = min;
            if (value > max) this.value = max;
        });
    </script>
</body>
</html>