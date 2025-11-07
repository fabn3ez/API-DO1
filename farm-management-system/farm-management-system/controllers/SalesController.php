<?php
require_once __DIR__ . '/../config/Database.php';

class SalesController {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function recent($limit = 10) {
        $stmt = $this->conn->prepare("SELECT id, product_name, quantity_sold, total_amount, created_at FROM sales ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function monthly($months = 6) {
        // MySQL: group by year/month
        $sql = "
          SELECT DATE_FORMAT(created_at, '%b %Y') AS month, COALESCE(SUM(total_amount),0) AS amount
          FROM sales
          WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
          GROUP BY YEAR(created_at), MONTH(created_at)
          ORDER BY YEAR(created_at), MONTH(created_at)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':months', (int)$months, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
