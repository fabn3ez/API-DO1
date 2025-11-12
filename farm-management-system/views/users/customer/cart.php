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

$user_id = $_SESSION['user_id'];

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity > 0) {
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $cart_item_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    elseif (isset($_POST['remove_item'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_item_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    elseif (isset($_POST['clear_cart'])) {
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    elseif (isset($_POST['apply_coupon'])) {
        $coupon_code = trim($_POST['coupon_code']);
        // In a real application, you would validate the coupon code here
        $_SESSION['coupon_code'] = $coupon_code;
        $_SESSION['discount_amount'] = 500; // Example discount
    }
    elseif (isset($_POST['remove_coupon'])) {
        unset($_SESSION['coupon_code']);
        unset($_SESSION['discount_amount']);
    }
}

// Get cart items
$cart_query = "
    SELECT 
        ci.id as cart_item_id,
        ci.quantity,
        ci.animal_id,
        a.type,
        a.breed,
        a.gender,
        a.avg_weight,
        a.shed_no,
        a.number as available_quantity
    FROM cart_items ci
    JOIN animals a ON ci.animal_id = a.id
    WHERE ci.user_id = ?
    ORDER BY ci.created_at DESC
";

$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate prices and totals
$base_prices = [
    'Cow' => 50000, 'Cattle' => 45000, 'Hen' => 800, 'Cock' => 1200,
    'Goat' => 8000, 'Sheep' => 7000, 'Rabbit' => 1500, 'Horse' => 80000,
    'Dog' => 10000, 'Cat' => 5000, 'Fish' => 300, 'Turkey' => 4000,
    'Goose' => 3500
];

$subtotal = 0;
$cart_items_with_prices = [];

foreach ($cart_items as $item) {
    $base_price = $base_prices[$item['type']] ?? 5000;
    $weight_factor = 1.0;
    
    if (preg_match('/(\d+\.?\d*)\s*kg/i', $item['avg_weight'], $matches)) {
        $weight = floatval($matches[1]);
        $weight_factor = $weight / 100;
    }
    
    $price_per_unit = $base_price * $weight_factor;
    $item_total = $price_per_unit * $item['quantity'];
    $subtotal += $item_total;
    
    $cart_items_with_prices[] = array_merge($item, [
        'price_per_unit' => $price_per_unit,
        'item_total' => $item_total
    ]);
}

$shipping_fee = $subtotal > 0 ? 1000 : 0; // Example shipping fee
$discount_amount = $_SESSION['discount_amount'] ?? 0;
$total = $subtotal + $shipping_fee - $discount_amount;

// Animal icons
$icons = [
    'Cow' => '🐄', 'Cattle' => '🐂', 'Hen' => '🐔', 'Cock' => '🐓',
    'Goat' => '🐐', 'Sheep' => '🐑', 'Rabbit' => '🐇', 'Horse' => '🐎',
    'Dog' => '🐕', 'Cat' => '🐈', 'Fish' => '🐟', 'Turkey' => '🦃',
    'Goose' => '🦆'
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Farm Management System</title>
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.03"><text x="50" y="50" font-size="80" text-anchor="middle" dominant-baseline="middle">🛒</text></svg>');
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
        
        /* Cart Layout */
        .cart-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        /* Cart Items */
        .cart-items {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .cart-header {
            background: var(--forest-green);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-title {
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .cart-count {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        
        .cart-body {
            padding: 1.5rem;
        }
        
        /* Cart Item */
        .cart-item {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--sky-blue), var(--forest-green));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            flex-shrink: 0;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-type {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--forest-green);
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .item-breed {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        
        .item-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .item-info span {
            display: inline-block;
            margin-right: 1rem;
        }
        
        .item-price {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--earth-brown);
        }
        
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 2px solid var(--forest-green);
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
            background: var(--forest-green);
            color: white;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 2px solid var(--forest-green);
            border-radius: 8px;
            font-weight: bold;
        }
        
        .remove-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }
        
        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .summary-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--forest-green);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            color: var(--dark-brown);
        }
        
        .summary-value {
            font-weight: 600;
        }
        
        .summary-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--forest-green);
        }
        
        .discount-amount {
            color: #28a745;
        }
        
        /* Coupon Section */
        .coupon-section {
            margin: 1.5rem 0;
            padding: 1rem;
            background: var(--wheat);
            border-radius: 8px;
            border-left: 4px solid var(--forest-green);
        }
        
        .coupon-form {
            display: flex;
            gap: 0.5rem;
        }
        
        .coupon-input {
            flex: 1;
            padding: 10px;
            border: 2px solid var(--forest-green);
            border-radius: 8px;
            font-size: 0.9rem;
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
        
        .btn-danger {
            background: #ff6b6b;
            color: white;
        }
        
        .btn-danger:hover {
            background: #ff5252;
        }
        
        .btn-full {
            width: 100%;
            justify-content: center;
            padding: 12px;
            font-size: 1.1rem;
        }
        
        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-cart-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        /* Cart Actions */
        .cart-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .item-actions {
                align-items: center;
                flex-direction: row;
                justify-content: space-between;
                width: 100%;
            }
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
            <a href="products.php" class="nav-item">
                <span>🐄</span>
                <span>Browse Animals</span>
            </a>
            <a href="orders.php" class="nav-item">
                <span>📋</span>
                <span>My Orders</span>
            </a>
            <a href="cart.php" class="nav-item active">
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
                    <span>🛒</span>
                    <span>Shopping Cart</span>
                </div>
                <a href="products.php" class="btn btn-primary">
                    <span>🐄</span>
                    <span>Continue Shopping</span>
                </a>
            </div>

            <?php if (!empty($cart_items_with_prices)): ?>
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items">
                    <div class="cart-header">
                        <div class="cart-title">
                            <span>🛒</span>
                            <span>Your Cart Items</span>
                        </div>
                        <div class="cart-count">
                            <?php echo count($cart_items_with_prices); ?> items
                        </div>
                    </div>
                    
                    <div class="cart-body">
                        <?php foreach($cart_items_with_prices as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <span><?php echo $icons[$item['type']] ?? '🐾'; ?></span>
                            </div>
                            
                            <div class="item-details">
                                <div class="item-type">
                                    <span><?php echo $icons[$item['type']] ?? '🐾'; ?></span>
                                    <?php echo $item['type']; ?>
                                </div>
                                <div class="item-breed"><?php echo $item['breed']; ?></div>
                                <div class="item-info">
                                    <span>⚧️ <?php echo $item['gender']; ?></span>
                                    <span>⚖️ <?php echo $item['avg_weight']; ?></span>
                                    <span>🏠 <?php echo $item['shed_no']; ?></span>
                                </div>
                                <div class="item-price">
                                    KSh <?php echo number_format($item['price_per_unit'], 2); ?> per animal
                                </div>
                            </div>
                            
                            <div class="item-actions">
                                <form method="POST" class="quantity-controls">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <button type="button" class="quantity-btn minus" data-item="<?php echo $item['cart_item_id']; ?>">-</button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['available_quantity']; ?>" 
                                           class="quantity-input" data-item="<?php echo $item['cart_item_id']; ?>">
                                    <button type="button" class="quantity-btn plus" data-item="<?php echo $item['cart_item_id']; ?>">+</button>
                                    <button type="submit" name="update_quantity" class="btn btn-secondary" style="padding: 8px 12px; margin-left: 10px;">
                                        💾 Update
                                    </button>
                                </form>
                                
                                <form method="POST">
                                    <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                    <button type="submit" name="remove_item" class="remove-btn" 
                                            onclick="return confirm('Remove <?php echo $item['type']; ?> from cart?')">
                                        🗑️ Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Clear Cart Button -->
                        <div class="cart-actions">
                            <form method="POST">
                                <button type="submit" name="clear_cart" class="btn btn-danger" 
                                        onclick="return confirm('Are you sure you want to clear your entire cart?')">
                                    🗑️ Clear Entire Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="cart-summary">
                    <div class="summary-title">
                        <span>💰</span>
                        <span>Order Summary</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Subtotal (<?php echo count($cart_items_with_prices); ?> items)</span>
                        <span class="summary-value">KSh <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Shipping Fee</span>
                        <span class="summary-value">KSh <?php echo number_format($shipping_fee, 2); ?></span>
                    </div>
                    
                    <?php if (isset($_SESSION['coupon_code'])): ?>
                    <div class="summary-row">
                        <span class="summary-label">
                            Discount (<?php echo $_SESSION['coupon_code']; ?>)
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="remove_coupon" class="btn" style="padding: 2px 6px; font-size: 0.7rem; background: #ff6b6b; color: white;">Remove</button>
                            </form>
                        </span>
                        <span class="summary-value discount-amount">-KSh <?php echo number_format($discount_amount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="summary-row summary-total">
                        <span>Total Amount</span>
                        <span>KSh <?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <!-- Coupon Section -->
                    <div class="coupon-section">
                        <form method="POST" class="coupon-form">
                            <input type="text" name="coupon_code" placeholder="Enter coupon code" 
                                   value="<?php echo $_SESSION['coupon_code'] ?? ''; ?>" 
                                   class="coupon-input" <?php echo isset($_SESSION['coupon_code']) ? 'disabled' : ''; ?>>
                            <?php if (!isset($_SESSION['coupon_code'])): ?>
                            <button type="submit" name="apply_coupon" class="btn btn-primary">Apply</button>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <!-- Checkout Button -->
                    <a href="checkout.php" class="btn btn-primary btn-full">
                        <span>🚀</span>
                        <span>Proceed to Checkout</span>
                    </a>
                    
                    <!-- Continue Shopping -->
                    <a href="products.php" class="btn btn-secondary btn-full" style="margin-top: 1rem;">
                        <span>🐄</span>
                        <span>Continue Shopping</span>
                    </a>
                </div>
            </div>
            <?php else: ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h3>Your Cart is Empty</h3>
                <p>Looks like you haven't added any farm animals to your cart yet.</p>
                <p>Explore our collection of healthy farm animals and start shopping!</p>
                <a href="products.php" class="btn btn-primary" style="margin-top: 1.5rem;">
                    <span>🐄</span>
                    <span>Browse Animals</span>
                </a>
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

            // Quantity controls
            document.querySelectorAll('.quantity-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const itemId = this.dataset.item;
                    const input = document.querySelector(`.quantity-input[data-item="${itemId}"]`);
                    let quantity = parseInt(input.value);
                    const max = parseInt(input.max);
                    
                    if (this.classList.contains('plus') && quantity < max) {
                        quantity++;
                    } else if (this.classList.contains('minus') && quantity > 1) {
                        quantity--;
                    }
                    
                    input.value = quantity;
                    
                    // Auto-update when using +/- buttons
                    const form = input.closest('form');
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                });
            });

            // Auto-update on quantity input change
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function() {
                    const form = this.closest('form');
                    form.submit();
                });
            });

            // Show loading state on buttons
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span>⏳</span><span>Updating...</span>';
                        submitBtn.disabled = true;
                        
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 2000);
                    }
                });
            });
        });
    </script>
</body>
</html>