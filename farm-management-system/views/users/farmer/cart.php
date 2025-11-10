<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('customer');

$error = '';
$success = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $index => $quantity) {
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or less
                unset($_SESSION['cart'][$index]);
            } else {
                // Update quantity
                $_SESSION['cart'][$index]['quantity'] = intval($quantity);
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
        $success = "Cart updated successfully!";
    }
    elseif (isset($_POST['remove_item'])) {
        $index = intval($_POST['item_index']);
        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            $success = "Item removed from cart!";
        }
    }
    elseif (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $success = "Cart cleared successfully!";
    }
}

// Calculate cart totals
$subtotal = 0;
$shipping = 5.99; // Standard shipping
$tax_rate = 0.08; // 8% tax

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
}

$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <span class="cart-count"><?php echo count($_SESSION['cart']); ?> items</span>
                <?php endif; ?>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="cart-container">
                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart fa-4x"></i>
                        <h2>Your cart is empty</h2>
                        <p>Browse our farm products and add some items to your cart.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <div class="cart-layout">
                        <div class="cart-items">
                            <div class="cart-header">
                                <h2>Cart Items (<?php echo count($_SESSION['cart']); ?>)</h2>
                                <form method="POST">
                                    <button type="submit" name="clear_cart" class="btn btn-text" 
                                            onclick="return confirm('Are you sure you want to clear your entire cart?')">
                                        <i class="fas fa-trash"></i> Clear Cart
                                    </button>
                                </form>
                            </div>

                            <form method="POST">
                                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                                <div class="cart-item">
                                    <div class="item-image">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="../../assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-box-open"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="item-details">
                                        <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                        <p class="item-farm">From: <?php echo htmlspecialchars($item['farm_name']); ?></p>
                                        <p class="item-price">$<?php echo number_format($item['price'], 2); ?> each</p>
                                    </div>

                                    <div class="item-quantity">
                                        <label>Quantity:</label>
                                        <input type="number" name="quantities[<?php echo $index; ?>]" 
                                               value="<?php echo $item['quantity']; ?>" min="1" max="99" class="qty-input">
                                    </div>

                                    <div class="item-total">
                                        <span class="total-price">
                                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                        </span>
                                    </div>

                                    <div class="item-actions">
                                        <form method="POST" class="remove-form">
                                            <input type="hidden" name="item_index" value="<?php echo $index; ?>">
                                            <button type="submit" name="remove_item" class="btn btn-text btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <div class="cart-actions">
                                    <a href="products.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Continue Shopping
                                    </a>
                                    <button type="submit" name="update_cart" class="btn btn-primary">
                                        <i class="fas fa-sync"></i> Update Cart
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="cart-summary">
                            <div class="summary-card">
                                <h3>Order Summary</h3>
                                
                                <div class="summary-details">
                                    <div class="summary-row">
                                        <span>Subtotal:</span>
                                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Shipping:</span>
                                        <span>$<?php echo number_format($shipping, 2); ?></span>
                                    </div>
                                    <div class="summary-row">
                                        <span>Tax (8%):</span>
                                        <span>$<?php echo number_format($tax, 2); ?></span>
                                    </div>
                                    <div class="summary-row total">
                                        <span><strong>Total:</strong></span>
                                        <span><strong>$<?php echo number_format($total, 2); ?></strong></span>
                                    </div>
                                </div>

                                <div class="shipping-options">
                                    <h4>Shipping Method</h4>
                                    <div class="shipping-option">
                                        <input type="radio" id="standard" name="shipping" value="standard" checked>
                                        <label for="standard">
                                            <span class="method">Standard Shipping</span>
                                            <span class="time">3-5 business days</span>
                                            <span class="price">$5.99</span>
                                        </label>
                                    </div>
                                    <div class="shipping-option">
                                        <input type="radio" id="express" name="shipping" value="express">
                                        <label for="express">
                                            <span class="method">Express Shipping</span>
                                            <span class="time">1-2 business days</span>
                                            <span class="price">$12.99</span>
                                        </label>
                                    </div>
                                    <div class="shipping-option">
                                        <input type="radio" id="pickup" name="shipping" value="pickup">
                                        <label for="pickup">
                                            <span class="method">Farm Pickup</span>
                                            <span class="time">Schedule with farmer</span>
                                            <span class="price">Free</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="checkout-actions">
                                    <a href="checkout.php" class="btn btn-success btn-large">
                                        <i class="fas fa-lock"></i> Proceed to Checkout
                                    </a>
                                    <p class="secure-checkout">
                                        <i class="fas fa-shield-alt"></i> Secure checkout guaranteed
                                    </p>
                                </div>

                                <div class="cart-features">
                                    <div class="feature">
                                        <i class="fas fa-truck"></i>
                                        <span>Free shipping on orders over $50</span>
                                    </div>
                                    <div class="feature">
                                        <i class="fas fa-undo"></i>
                                        <span>7-day return policy</span>
                                    </div>
                                    <div class="feature">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Secure payment</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Update shipping cost when method changes
        document.querySelectorAll('input[name="shipping"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // In a real application, this would recalculate totals via AJAX
                const shippingCosts = {
                    standard: 5.99,
                    express: 12.99,
                    pickup: 0
                };
                
                const newShipping = shippingCosts[this.value];
                // Update display (simplified - in real app, recalculate all totals)
                document.querySelector('.summary-row:nth-child(2) span:last-child').textContent = '$' + newShipping.toFixed(2);
                
                // Recalculate total (simplified)
                const subtotal = <?php echo $subtotal; ?>;
                const tax = subtotal * 0.08;
                const total = subtotal + newShipping + tax;
                
                document.querySelector('.summary-row.total span:last-child').innerHTML = 
                    '<strong>$' + total.toFixed(2) + '</strong>';
            });
        });

        // Quantity input validation
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('change', function() {
                let value = parseInt(this.value);
                if (value < 1) this.value = 1;
                if (value > 99) this.value = 99;
            });
        });
    </script>
</body>
</html>