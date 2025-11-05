<?php
require_once __DIR__ . '/../database/Database.php';

class DashboardController
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getDashboardStats()
    {
        $data = [];

        // Total Products
        $stmt = $this->conn->query("SELECT COUNT(*) AS total_products FROM products");
        $data['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'] ?? 0;

        // Inventory Value
        $stmt = $this->conn->query("SELECT SUM(price * quantity) AS total_value FROM products");
        $data['inventory_value'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_value'] ?? 0;

        // Total Sales
        $stmt = $this->conn->query("SELECT SUM(amount) AS total_sales FROM sales");
        $data['total_sales'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

        // Production Trend (example: monthly crops data)
        $stmt = $this->conn->query("
            SELECT MONTH(date_added) AS month, SUM(quantity) AS total
            FROM products
            GROUP BY MONTH(date_added)
        ");
        $data['production_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Livestock Distribution
        $stmt = $this->conn->query("
            SELECT type, COUNT(*) AS count
            FROM livestock
            GROUP BY type
        ");
        $data['livestock_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

// When accessed directly via browser
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new DashboardController();
    $controller->getDashboardStats();
}
?>