<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('customer');

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$error = '';
$success = '';

// Fetch user details for pre-filling form
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Calculate order totals
$subtotal = 0;
$shipping = 5.99;
$tax_rate = 0.08;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * $tax_rate;
$total = $subtotal + $shipping + $tax;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $shipping_method = $_POST['shipping_method'];
    $payment_method = $_POST['payment_method'];
    
    // Shipping address
    $ship_first_name = trim($_POST['ship_first_name']);
    $ship_last_name = trim($_POST['ship_last_name']);
    $ship_address = trim($_POST['ship_address']);
    $ship_city = trim($_POST['ship_city']);
    $ship_state = trim($_POST['ship_state']);
    $ship_zip = trim($_POST['ship_zip']);
    $ship_phone = trim($_POST['ship_phone']);
    
    // Billing address (if different)
    $same_as_shipping = isset($_POST['same_as_shipping']);
    $bill_first_name = $same_as_shipping ? $ship_first_name : trim($_POST['bill_first_name']);
    $bill_last_name = $same_as_shipping ? $ship_last_name : trim($_POST['bill_last_name']);
    $bill_address = $same_as_shipping ? $ship_address : trim($_POST['bill_address']);
    $bill_city = $same_as_shipping ? $ship_city : trim($_POST['bill_city']);
    $bill_state = $same_as_shipping ? $ship_state : trim($_POST['bill_state']);
    $bill_zip = $same_as_shipping ? $ship_zip : trim($_POST['bill_zip']);
    
    // Payment details
    $card_number = trim($_POST['card_number']);
    $card_expiry = trim($_POST['card_expiry']);
    $card_cvv = trim($_POST['card_cvv']);
    $card_name = trim($_POST['card_name']);
    
    // Validation
    if (empty($ship_first_name) || empty($ship_last_name) || empty($ship_address) || 
        empty($ship_city) || empty($ship_state) || empty($ship_zip)) {
        $error = "Please fill in all required shipping address fields.";
    } elseif (empty($card_number) || empty($card_expiry) || empty($card_cvv) || empty($card_name)) {
        $error = "Please fill in all payment details.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Create sales order
            $order_stmt = $pdo->prepare("
                INSERT INTO sales_orders 
                (user_id, total_amount, shipping_method, payment_method, status, order_date,
                 ship_first_name, ship_last_name, ship_address, ship_city, ship_state, ship_zip, ship_phone,
                 bill_first_name, bill_last_name, bill_address, bill_city, bill_state, bill_zip)
                VALUES (?, ?, ?, ?, 'pending', NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $order_stmt->execute([
                $_SESSION['user_id'], $total, $shipping_method, $payment_method,
                $ship_first_name, $ship_last_name, $ship_address, $ship_city, $ship_state, $ship_zip, $ship_phone,
                $bill_first_name, $bill_last_name, $bill_address, $bill_city, $bill_state, $bill_zip
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Create order items and update inventory
            foreach ($_SESSION['cart'] as $item) {
                $order_item_stmt = $pdo->prepare("
                    INSERT INTO order_items 
                    (order_id, product_id, quantity, unit_price, total_price)
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $item_total = $item['price'] * $item['quantity'];
                $order_item_stmt->execute([
                    $order_id, $item['product_id'], $item['quantity'], 
                    $item['price'], $item_total
                ]);
                
                // Update inventory
                $update_inv_stmt = $pdo->prepare("
                    UPDATE inventory 
                    SET quantity = quantity - ?, sold_count = sold_count + ? 
                    WHERE id = ?
                ");
                $update_inv_stmt->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
            }
            
            $pdo->commit();
            
            // Clear cart and redirect to success page
            $_SESSION['cart'] = [];
            $_SESSION['last_order_id'] = $order_id;
            header("Location: order_success.php");
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to place order. Please try again. Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-lock"></i> Checkout</h1>
                <div class="checkout-steps">
                    <div class="step active">1. Shipping</div>
                    <div class="step active">2. Payment</div>
                    <div class="step">3. Confirmation</div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="checkout-form">
                <div class="checkout-layout">
                    <div class="checkout-main">
                        <!-- Shipping Information -->
                        <div class="checkout-section">
                            <h2><i class="fas fa-truck"></i> Shipping Information</h2>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="ship_first_name">First Name *</label>
                                    <input type="text" id="ship_first_name" name="ship_first_name" 
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="ship_last_name">Last Name *</label>
                                    <input type="text" id="ship_last_name" name="ship_last_name" 
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="ship_phone">Phone Number *</label>
                                    <input type="tel" id="ship_phone" name="ship_phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group full-width">
                                    <label for="ship_address">Address *</label>
                                    <input type="text" id="ship_address" name="ship_address" 
                                           value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="ship_city">City *</label>
                                    <input type="text" id="ship_city" name="ship_city" required>
                                </div>

                                <div class="form-group">
                                    <label for="ship_state">State *</label>
                                    <input type="text" id="ship_state" name="ship_state" required>
                                </div>

                                <div class="form-group">
                                    <label for="ship_zip">ZIP Code *</label>
                                    <input type="text" id="ship_zip" name="ship_zip" required>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Method -->
                        <div class="checkout-section">
                            <h2><i class="fas fa-shipping-fast"></i> Shipping Method</h2>
                            <div class="shipping-options">
                                <div class="shipping-option">
                                    <input type="radio" id="standard" name="shipping_method" value="standard" checked>
                                    <label for="standard">
                                        <span class="method">Standard Shipping</span>
                                        <span class="time">3-5 business days</span>
                                        <span class="price">$5.99</span>
                                    </label>
                                </div>
                                <div class="shipping-option">
                                    <input type="radio" id="express" name="shipping_method" value="express">
                                    <label for="express">
                                        <span class="method">Express Shipping</span>
                                        <span class="time">1-2 business days</span>
                                        <span class="price">$12.99</span>
                                    </label>
                                </div>
                                <div class="shipping-option">
                                    <input type="radio" id="pickup" name="shipping_method" value="pickup">
                                    <label for="pickup">
                                        <span class="method">Farm Pickup</span>
                                        <span class="time">Schedule with farmer</span>
                                        <span class="price">Free</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="checkout-section">
                            <h2><i class="fas fa-credit-card"></i> Payment Information</h2>
                            
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                                    <label for="credit_card">
                                        <i class="fab fa-cc-visa"></i>
                                        <i class="fab fa-cc-mastercard"></i>
                                        <i class="fab fa-cc-amex"></i>
                                        Credit Card
                                    </label>
                                </div>
                                <div class="payment-method">
                                    <input type="radio" id="paypal" name="payment_method" value="paypal">
                                    <label for="paypal">
                                        <i class="fab fa-paypal"></i>
                                        PayPal
                                    </label>
                                </div>
                            </div>

                            <div class="payment-details" id="creditCardDetails">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="card_name">Name on Card *</label>
                                        <input type="text" id="card_name" name="card_name">
                                    </div>

                                    <div class="form-group">
                                        <label for="card_number">Card Number *</label>
                                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                                    </div>

                                    <div class="form-group">
                                        <label for="card_expiry">Expiry Date *</label>
                                        <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY">
                                    </div>

                                    <div class="form-group">
                                        <label for="card_cvv">CVV *</label>
                                        <input type="text" id="card_cvv" name="card_cvv" placeholder="123">
                                    </div>
                                </div>
                            </div>

                            <div class="payment-details" id="paypalDetails" style="display: none;">
                                <div class="paypal-info">
                                    <p>You will be redirected to PayPal to complete your payment after placing the order.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Address -->
                        <div class="checkout-section">
                            <h2><i class="fas fa-receipt"></i> Billing Address</h2>
                            <div class="billing-address">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="same_as_shipping" name="same_as_shipping" checked>
                                    <span>Same as shipping address</span>
                                </label>

                                <div id="billingAddressFields" style="display: none;">
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="bill_first_name">First Name *</label>
                                            <input type="text" id="bill_first_name" name="bill_first_name">
                                        </div>

                                        <div class="form-group">
                                            <label for="bill_last_name">Last Name *</label>
                                            <input type="text" id="bill_last_name" name="bill_last_name">
                                        </div>

                                        <div class="form-group full-width">
                                            <label for="bill_address">Address *</label>
                                            <input type="text" id="bill_address" name="bill_address">
                                        </div>

                                        <div class="form-group">
                                            <label for="bill_city">City *</label>
                                            <input type="text" id="bill_city" name="bill_city">
                                        </div>

                                        <div class="form-group">
                                            <label for="bill_state">State *</label>
                                            <input type="text" id="bill_state" name="bill_state">
                                        </div>

                                        <div class="form-group">
                                            <label for="bill_zip">ZIP Code *</label>
                                            <input type="text" id="bill_zip" name="bill_zip">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-sidebar">
                        <div class="order-summary">
                            <h3>Order Summary</h3>
                            
                            <div class="order-items">
                                <?php foreach ($_SESSION['cart'] as $item): ?>
                                <div class="order-item">
                                    <div class="item-info">
                                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="item-quantity">Qty: <?php echo $item['quantity']; ?></span>
                                    </div>
                                    <span class="item-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-totals">
                                <div class="total-row">
                                    <span>Subtotal:</span>
                                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <div class="total-row">
                                    <span>Shipping:</span>
                                    <span>$<?php echo number_format($shipping, 2); ?></span>
                                </div>
                                <div class="total-row">
                                    <span>Tax:</span>
                                    <span>$<?php echo number_format($tax, 2); ?></span>
                                </div>
                                <div class="total-row grand-total">
                                    <span>Total:</span>
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>

                            <div class="checkout-actions">
                                <button type="submit" name="place_order" class="btn btn-success btn-large">
                                    <i class="fas fa-lock"></i> Place Order
                                </button>
                                <a href="cart.php" class="btn btn-text">Return to Cart</a>
                            </div>

                            <div class="security-notice">
                                <i class="fas fa-shield-alt"></i>
                                <span>Your payment information is secure and encrypted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Toggle billing address fields
        document.getElementById('same_as_shipping').addEventListener('change', function() {
            const billingFields = document.getElementById('billingAddressFields');
            billingFields.style.display = this.checked ? 'none' : 'block';
            
            // Toggle required attribute on billing fields
            const billingInputs = billingFields.querySelectorAll('input');
            billingInputs.forEach(input => {
                input.required = !this.checked;
            });
        });

        // Toggle payment method details
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('creditCardDetails').style.display = 
                    this.value === 'credit_card' ? 'block' : 'none';
                document.getElementById('paypalDetails').style.display = 
                    this.value === 'paypal' ? 'block' : 'none';
                
                // Toggle required attribute on credit card fields
                const cardInputs = document.querySelectorAll('#creditCardDetails input');
                cardInputs.forEach(input => {
                    input.required = this.value === 'credit_card';
                });
            });
        });

        // Format card number
        document.getElementById('card_number').addEventListener('input', function() {
            let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let matches = value.match(/\d{4,16}/g);
            let match = matches && matches[0] || '';
            let parts = [];
            
            for (let i = 0; i < match.length; i += 4) {
                parts.push(match.substring(i, i + 4));
            }
            
            if (parts.length) {
                this.value = parts.join(' ');
            }
        });

        // Format expiry date
        document.getElementById('card_expiry').addEventListener('input', function() {
            let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            if (value.length >= 2) {
                this.value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
        });
    </script>
</body>
</html>