<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('customer');

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

$error = '';
$success = '';

// Handle wishlist actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['remove_from_wishlist'])) {
        $product_id = intval($_POST['product_id']);
        $key = array_search($product_id, $_SESSION['wishlist']);
        if ($key !== false) {
            unset($_SESSION['wishlist'][$key]);
            $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
            $success = "Product removed from wishlist!";
        }
    }
    elseif (isset($_POST['add_to_cart'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        // Fetch product details
        $product_stmt = $pdo->prepare("
            SELECT i.*, u.farm_name 
            FROM inventory i 
            JOIN users u ON i.user_id = u.id 
            WHERE i.id = ? AND i.quantity > 0 AND i.status = 'active'
        ");
        $product_stmt->execute([$product_id]);
        $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
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
            
            $success = "Product added to cart!";
        } else {
            $error = "Product not available.";
        }
    }
    elseif (isset($_POST['clear_wishlist'])) {
        $_SESSION['wishlist'] = [];
        $success = "Wishlist cleared!";
    }
}

// Fetch wishlist products
$wishlist_products = [];
if (!empty($_SESSION['wishlist'])) {
    $placeholders = str_repeat('?,', count($_SESSION['wishlist']) - 1) . '?';
    $wishlist_stmt = $pdo->prepare("
        SELECT i.*, u.farm_name 
        FROM inventory i 
        JOIN users u ON i.user_id = u.id 
        WHERE i.id IN ($placeholders) AND i.status = 'active'
        ORDER BY FIELD(i.id, " . $placeholders . ")
    ");
    
    $params = array_merge($_SESSION['wishlist'], $_SESSION['wishlist']);
    $wishlist_stmt->execute($params);
    $wishlist_products = $wishlist_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-heart"></i> My Wishlist</h1>
                <?php if (!empty($wishlist_products)): ?>
                    <span class="wishlist-count"><?php echo count($wishlist_products); ?> items</span>
                <?php endif; ?>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="wishlist-container">
                <?php if (empty($wishlist_products)): ?>
                    <div class="empty-wishlist">
                        <i class="fas fa-heart fa-4x"></i>
                        <h2>Your wishlist is empty</h2>
                        <p>Save your favorite farm products here for easy access later.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Browse Products
                        </a>
                    </div>
                <?php else: ?>
                    <div class="wishlist-header">
                        <h2>Saved Products (<?php echo count($wishlist_products); ?>)</h2>
                        <form method="POST">
                            <button type="submit" name="clear_wishlist" class="btn btn-text" 
                                    onclick="return confirm('Are you sure you want to clear your entire wishlist?')">
                                <i class="fas fa-trash"></i> Clear Wishlist
                            </button>
                        </form>
                    </div>

                    <div class="wishlist-grid">
                        <?php foreach ($wishlist_products as $product): ?>
                        <div class="wishlist-item">
                            <div class="product-image">
                                <a href="product_details.php?id=<?php echo $product['id']; ?>">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="../../assets/images/products/<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-box-open"></i>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="product-info">
                                <h3 class="product-name">
                                    <a href="product_details.php?id=<?php echo $product['id']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                                </p>
                                
                                <div class="product-meta">
                                    <span class="category"><?php echo htmlspecialchars($product['category']); ?></span>
                                    <span class="farm">From: <?php echo htmlspecialchars($product['farm_name']); ?></span>
                                </div>

                                <div class="product-price">
                                    <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php if ($product['quantity'] > 0): ?>
                                        <span class="stock in-stock">In Stock</span>
                                    <?php else: ?>
                                        <span class="stock out-of-stock">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="product-actions">
                                <?php if ($product['quantity'] > 0): ?>
                                <form method="POST" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <form method="POST" class="remove-form">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="remove_from_wishlist" class="btn btn-text btn-danger">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                                
                                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-text">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="wishlist-suggestions">
                        <h3>You might also like</h3>
                        <div class="suggestions-grid">
                            <?php
                            // Fetch random products as suggestions
                            $suggestions_stmt = $pdo->prepare("
                                SELECT i.*, u.farm_name 
                                FROM inventory i 
                                JOIN users u ON i.user_id = u.id 
                                WHERE i.quantity > 0 AND i.status = 'active' 
                                AND i.id NOT IN (" . str_repeat('?,', count($_SESSION['wishlist']) - 1) . "?) 
                                ORDER BY RAND() 
                                LIMIT 4
                            ");
                            $suggestions_stmt->execute(array_merge($_SESSION['wishlist']));
                            $suggestions = $suggestions_stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($suggestions as $suggestion):
                            ?>
                            <div class="product-card small">
                                <div class="product-image">
                                    <a href="product_details.php?id=<?php echo $suggestion['id']; ?>">
                                        <?php if (!empty($suggestion['image_url'])): ?>
                                            <img src="../../assets/images/products/<?php echo htmlspecialchars($suggestion['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($suggestion['name']); ?>">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                </div>

                                <div class="product-info">
                                    <h4 class="product-name">
                                        <a href="product_details.php?id=<?php echo $suggestion['id']; ?>">
                                            <?php echo htmlspecialchars($suggestion['name']); ?>
                                        </a>
                                    </h4>
                                    
                                    <div class="product-price">
                                        <span class="price">$<?php echo number_format($suggestion['price'], 2); ?></span>
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
</body>
</html>