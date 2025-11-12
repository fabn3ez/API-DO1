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

// Fetch cart items for summary
$cart_query = $conn->prepare("
    SELECT ci.quantity, a.type, a.breed, a.avg_weight
    FROM cart_items ci
    JOIN animals a ON ci.animal_id = a.id
    WHERE ci.user_id = ?
");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$cart_items = $cart_query->get_result()->fetch_all(MYSQLI_ASSOC);
$cart_query->close();

// Calculate total
$base_prices = [
    'Cow' => 50000, 'Cattle' => 45000, 'Hen' => 800, 'Cock' => 1200,
    'Goat' => 8000, 'Sheep' => 7000, 'Rabbit' => 1500, 'Horse' => 80000,
    'Dog' => 10000, 'Cat' => 5000, 'Fish' => 300, 'Turkey' => 4000,
    'Goose' => 3500
];
$subtotal = 0;
foreach ($cart_items as $item) {
    $base_price = $base_prices[$item['type']] ?? 5000;
    $weight_factor = 1.0;
    if (preg_match('/(\d+\.?\d*)\s*kg/i', $item['avg_weight'], $matches)) {
        $weight = floatval($matches[1]);
        $weight_factor = $weight / 100;
    }
    $subtotal += $base_price * $weight_factor * $item['quantity'];
}
$shipping_fee = $subtotal > 0 ? 200 : 0;
$discount_amount = $_SESSION['discount_amount'] ?? 0;
$total = $subtotal + $shipping_fee - $discount_amount;
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Farm Management System</title>
    <style>
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--cream-white); color: var(--dark-brown); }
        .header { background: linear-gradient(to right, var(--forest-green), var(--earth-brown)); color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .logo { display: flex; align-items: center; gap: 10px; font-size: 1.5rem; font-weight: bold; }
        .user-menu { display: flex; align-items: center; gap: 15px; }
        .logout-btn { background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 20px; text-decoration: none; color: white; transition: all 0.3s ease; }
        .logout-btn:hover { background: rgba(255,255,255,0.3); }
        .container { display: flex; min-height: calc(100vh - 80px); }
        .sidebar { width: 250px; background: var(--wheat); padding: 2rem 1rem; border-right: 3px solid var(--earth-brown); }
        .nav-item { padding: 12px 15px; margin: 8px 0; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 10px; font-weight: 500; text-decoration: none; color: var(--dark-brown); }
        .nav-item:hover, .nav-item.active { background-color: var(--forest-green); color: white; transform: translateX(5px); }
        .main-content { flex: 1; padding: 2rem; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.03"><text x="50" y="50" font-size="80" text-anchor="middle" dominant-baseline="middle">🛒</text></svg>'); }
        .checkout-section { background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .section-title { color: var(--forest-green); font-size: 1.3rem; font-weight: 700; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
        .order-summary { margin-bottom: 2rem; }
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.8rem 0; border-bottom: 1px solid #eee; }
        .summary-row:last-child { border-bottom: none; }
        .summary-label { color: var(--dark-brown); }
        .summary-value { font-weight: 600; }
        .summary-total { font-size: 1.2rem; font-weight: bold; color: var(--forest-green); }
        .checkout-form { margin-top: 2rem; }
        .form-group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #555; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 16px; }
        input:focus, select:focus, textarea:focus { border-color: var(--forest-green); outline: none; }
        .btn { padding: 12px 25px; background-color: var(--forest-green); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1.1rem; font-weight: 600; margin-top: 10px; }
        .btn:hover { background-color: var(--earth-brown); }
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
        <div class="main-content">
            <div class="checkout-section">
                <div class="section-title">
                    <span>🧾</span>
                    <span>Checkout</span>
                </div>
                <div class="order-summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal (<?php echo count($cart_items); ?> items)</span>
                        <span class="summary-value">KSh <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Shipping Fee</span>
                        <span class="summary-value">KSh <?php echo number_format($shipping_fee, 2); ?></span>
                    </div>
                    <?php if ($discount_amount > 0): ?>
                    <div class="summary-row">
                        <span class="summary-label">Discount</span>
                        <span class="summary-value" style="color:#28a745;">-KSh <?php echo number_format($discount_amount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row summary-total">
                        <span>Total Amount</span>
                        <span>KSh <?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
                <form class="checkout-form" method="POST" action="place_order.php">
                    <div class="form-group">
                        <label for="address">Delivery Address</label>
                        <textarea id="address" name="address" rows="3" required placeholder="Enter your delivery address..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="mpesa">M-Pesa</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash on Delivery</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Place Order</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
