<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('customer');

// Check if user has a recent successful order
if (!isset($_SESSION['last_order_id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_SESSION['last_order_id'];

// Fetch order details
$order_stmt = $pdo->prepare("
    SELECT so.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity) as total_quantity
    FROM sales_orders so
    LEFT JOIN order_items oi ON so.id = oi.order_id
    WHERE so.id = ? AND so.user_id = ?
    GROUP BY so.id
");
$order_stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Clear the last order ID from session to prevent repeated access
unset($_SESSION['last_order_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="order-success">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h1>Order Confirmed!</h1>
                <p class="success-message">Thank you for your order. Your farm-fresh products are on their way!</p>
                
                <div class="order-summary">
                    <div class="summary-card">
                        <h2><i class="fas fa-receipt"></i> Order Details</h2>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <label>Order Number:</label>
                                <span>#<?php echo $order['id']; ?></span>
                            </div>
                            <div class="summary-item">
                                <label>Order Date:</label>
                                <span><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></span>
                            </div>
                            <div class="summary-item">
                                <label>Total Amount:</label>
                                <span class="amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <div class="summary-item">
                                <label>Items:</label>
                                <span><?php echo $order['item_count']; ?> products (<?php echo $order['total_quantity']; ?> units)</span>
                            </div>
                            <div class="summary-item">
                                <label>Shipping Method:</label>
                                <span><?php echo ucfirst(str_replace('_', ' ', $order['shipping_method'])); ?></span>
                            </div>
                            <div class="summary-item">
                                <label>Payment Method:</label>
                                <span><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="next-steps">
                    <h2><i class="fas fa-list-alt"></i> What's Next?</h2>
                    <div class="steps-timeline">
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="step-content">
                                <h3>Order Confirmation</h3>
                                <p>You'll receive an email confirmation shortly with your order details.</p>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="step-content">
                                <h3>Order Processing</h3>
                                <p>Our farmers are preparing your fresh products for shipment.</p>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="step-content">
                                <h3>Shipping</h3>
                                <p>You'll receive tracking information once your order ships.</p>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="step-content">
                                <h3>Delivery</h3>
                                <p>Your farm-fresh products will arrive at your doorstep.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="order_details.php?id=<?php echo $order_id; ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> View Order Details
                    </a>
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-clipboard-list"></i> View All Orders
                    </a>
                    <a href="products.php" class="btn btn-success">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                </div>

                <div class="support-info">
                    <h3><i class="fas fa-question-circle"></i> Need Help?</h3>
                    <p>If you have any questions about your order, please contact our customer support team.</p>
                    <div class="contact-options">
                        <div class="contact-option">
                            <i class="fas fa-phone"></i>
                            <span>1-800-FARM-NOW</span>
                        </div>
                        <div class="contact-option">
                            <i class="fas fa-envelope"></i>
                            <span>support@farmmanagement.com</span>
                        </div>
                        <div class="contact-option">
                            <i class="fas fa-comments"></i>
                            <span>Live Chat</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>