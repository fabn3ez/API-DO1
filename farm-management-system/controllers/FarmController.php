<?php
require_once __DIR__ . '/../config/Database.php';

class FarmController
{
    private $conn;
    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getStats()
    {
        // total inventory items
        $totalProducts = 0;
        $inventoryValue = 0;
        $totalSales = 0;

        try {
            $stmt = $this->conn->query("SELECT COUNT(*) AS c FROM inventory");
            $totalProducts = (int) $stmt->fetch(PDO::FETCH_ASSOC)['c'];
        } catch (Exception $e) {
            $totalProducts = 0;
        }

        try {
            $stmt = $this->conn->query("SELECT COALESCE(SUM(quantity * unit_price),0) AS v FROM inventory");
            $inventoryValue = (float) $stmt->fetch(PDO::FETCH_ASSOC)['v'];
        } catch (Exception $e) {
            $inventoryValue = 0;
        }

        try {
            $stmt = $this->conn->query("SELECT COALESCE(SUM(total_amount),0) AS s FROM sales");
            $totalSales = (float) $stmt->fetch(PDO::FETCH_ASSOC)['s'];
        } catch (Exception $e) {
            $totalSales = 0;
        }

        return [
            "totalProducts" => $totalProducts,
            "inventoryValue" => $inventoryValue,
            "totalSales" => $totalSales
        ];
    }
}
?>