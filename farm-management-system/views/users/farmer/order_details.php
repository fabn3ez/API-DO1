<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('customer');

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id == 0) {
    header("Location: orders.php");
    exit();
}

// Fetch order details
$order_stmt = $pdo->prepare("
    SELECT so.*, 
           u.first_name, u.last_name, u.email, u.phone
    FROM sales_orders so
    JOIN users u ON so.user_id = u.id
    WHERE so.id = ? AND so.user_id = ?
");
$order_stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Fetch order items
$items_stmt = $pdo->prepare("
    SELECT oi.*, i.name as product_name, i.image_url, i.category, u.farm_name
    FROM order_items oi
    JOIN inventory i ON oi.product_id = i.id
    JOIN users u ON i.user_id = u.id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Order status timeline
$status_timeline = [
    'pending' => ['icon' => 'clock', 'label' => 'Order Placed', 'active' => true],
    'confirmed' => ['icon' => 'check-circle', 'label' => 'Confirmed', 'active' => false],
    'processing' => ['icon' => 'cog', 'label' => 'Processing', 'active' => false],
    'shipped' => ['icon' => 'truck', 'label' => 'Shipped', 'active' => false],
    'delivered' => ['icon' => 'home', 'label' => 'Delivered', 'active' => false]
];

// Activate steps up to current status
$current_status = $order['status'];
foreach ($status_timeline as $status => &$step) {
    $step['active'] = true;
    if ($status == $current_status) break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-file-invoice"></i> Order Details</h1>
                <a href="orders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>

            <div class="order-details-container">
                <!-- Order Status Timeline -->
                <div class="status-timeline">
                    <?php foreach ($status_timeline as $status => $step): ?>
                    <div class="timeline-step <?php echo $step['active'] ? 'active' : ''; ?>">
                        <div class="step-icon">
                            <i class="fas fa-<?php echo $step['icon']; ?>"></i>
                        </div>
                        <div class="step-label"><?php echo $step['label']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="order-summary-section">
                    <div class="summary-grid">
                        <div class="summary-card">
                            <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                            <div class="detail-list">
                                <div class="detail-item">
                                    <label>Order Number:</label>
                                    <span>#<?php echo $order['id']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Order Date:</label>
                                    <span><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Status:</label>
                                    <span class="status-badge <?php echo $status_colors[$order['status']] ?? 'primary'; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <label>Total Amount:</label>
                                    <span class="amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="summary-card">
                            <h3><i class="fas fa-truck"></i> Shipping Information</h3>
                            <div class="address-details">
                                <p><strong><?php echo htmlspecialchars($order['ship_first_name'] . ' ' . $order['ship_last_name']); ?></strong></p>
                                <p><?php echo htmlspecialchars($order['ship_address']); ?></p>
                                <p><?php echo htmlspecialchars($order['ship_city'] . ', ' . $order['ship_state'] . ' ' . $order['ship_zip']); ?></p>
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($order['ship_phone']); ?></p>
                            </div>
                        </div>

                        <div class="summary-card">
                            <h3><i class="fas fa-credit-card"></i> Payment Information</h3>
                            <div class="detail-list">
                                <div class="detail-item">
                                    <label>Payment Method:</label>
                                    <span><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Shipping Method:</label>
                                    <span><?php echo ucfirst(str_replace('_', ' ', $order['shipping_method'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Payment Status:</label>
                                    <span class="status-badge success">Paid</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="order-items-section">
                    <h2><i class="fas fa-boxes"></i> Order Items</h2>
                    
                    <div class="items-table">
                        <div class="table-header">
                            <div class="col-product">Product</div>
                            <div class="col-price">Price</div>
                            <div class="col-quantity">Quantity</div>
                            <div class="col-total">Total</div>
                        </div>
                        
                        <?php foreach ($order_items as $item): ?>
                        <div class="table-row">
                            <div class="col-product">
                                <div class="product-info">
                                    <?php if (!empty($item['image_url'])): ?>
                                        <img src="../../assets/images/products/<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-image">
                                    <?php else: ?>
                                        <div class="product-image no-image">
                                            <i class="fas fa-box-open"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-details">
                                        <h4><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                        <p class="product-category"><?php echo htmlspecialchars($item['category']); ?></p>
                                        <p class="product-farm">From: <?php echo htmlspecialchars($item['farm_name']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-price">
                                $<?php echo number_format($item['unit_price'], 2); ?>
                            </div>
                            
                            <div class="col-quantity">
                                <?php echo $item['quantity']; ?>
                            </div>
                            
                            <div class="col-total">
                                $<?php echo number_format($item['total_price'], 2); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Order Totals -->
                        <div class="table-footer">
                            <div class="totals">
                                <div class="total-row">
                                    <span>Subtotal:</span>
                                    <span>$<?php echo number_format($order['total_amount'] - 5.99 - ($order['total_amount'] * 0.08), 2); ?></span>
                                </div>
                                <div class="total-row">
                                    <span>Shipping:</span>
                                    <span>$5.99</span>
                                </div>
                                <div class="total-row">
                                    <span>Tax:</span>
                                    <span>$<?php echo number_format($order['total_amount'] * 0.08, 2); ?></span>
                                </div>
                                <div class="total-row grand-total">
                                    <span>Total:</span>
                                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Actions -->
                <div class="order-actions-section">
                    <h2><i class="fas fa-cog"></i> Order Actions</h2>
                    <div class="action-buttons">
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Continue Shopping
                        </a>
                        
                        <?php if ($order['status'] == 'pending'): ?>
                            <form method="POST" action="order_actions.php" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                <button type="submit" name="cancel_order" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to cancel this order?')">
                                    <i class="fas fa-times"></i> Cancel Order
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <button class="btn btn-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Invoice
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>