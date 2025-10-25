<?php
require_once __DIR__ . '/../config/Database.php';

class InventoryController {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function listProducts() {
        $stmt = $this->conn->query("SELECT id, product_name, quantity, unit_price, created_at FROM inventory ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addProduct($data) {
        $product = $data['product'] ?? '';
        $quantity = (int)($data['quantity'] ?? 0);
        $unit_price = (float)($data['unit_price'] ?? 0);

        $sql = "INSERT INTO inventory (product_name, quantity, unit_price) VALUES (:product, :quantity, :unit_price)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product', $product);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':unit_price', $unit_price);
        $stmt->execute();

        return ["status" => "success", "message" => "Product added"];
    }
}
?>
