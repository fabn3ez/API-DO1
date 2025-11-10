<?php
session_start();
require_once '../db.php';
require_once '../../auth/check_role.php';
check_role('customer');

// Fetch customer orders
$stmt = $pdo->prepare("
    SELECT so.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity) as total_quantity
    FROM sales_orders so
    LEFT JOIN order_items oi ON so.id = oi.order_id
    WHERE so.user_id = ?
    GROUP BY so.id
    ORDER BY so.order_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Order status colors
$status_colors = [
    'pending' => 'warning',
    'confirmed' => 'info',
    'processing' => 'primary',
    'shipped' => 'success',
    'delivered' => 'success',
    'cancelled' => 'error'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Farm Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include '../includes/header.php'; ?>
        
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-clipboard-list"></i> My Orders</h1>
                <p>Track and manage your farm product orders</p>
            </div>

            <div class="orders-container">
                <?php if (empty($orders)): ?>
                    <div class="no-orders">
                        <i class="fas fa-shopping-bag fa-4x"></i>
                        <h2>No orders yet</h2>
                        <p>Start shopping and place your first order for fresh farm products.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Order #<?php echo $order['id']; ?></h3>
                                    <p class="order-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?>
                                    </p>
                                </div>
                                
                                <div class="order-status">
                                    <span class="status-badge <?php echo $status_colors[$order['status']]; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-details">
                                <div class="detail-item">
                                    <label>Items:</label>
                                    <span><?php echo $order['item_count']; ?> products (<?php echo $order['total_quantity']; ?> units)</span>
                                </div>
                                
                                <div class="detail-item">
                                    <label>Total Amount:</label>
                                    <span class="order-total">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <label>Shipping Method:</label>
                                    <span><?php echo ucfirst(str_replace('_', ' ', $order['shipping_method'])); ?></span>
                                </div>
                                
                                <div class="detail-item">
                                    <label>Payment Method:</label>
                                    <span><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                                </div>
                            </div>

                            <div class="order-actions">
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                
                                <?php if ($order['status'] == 'pending'): ?>
                                    <form method="POST" action="order_actions.php" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" name="cancel_order" class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to cancel this order?')">
                                            <i class="fas fa-times"></i> Cancel Order
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($order['status'] == 'delivered'): ?>
                                    <button class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reorder
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>