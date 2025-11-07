<?php
require_once __DIR__ . '/../config/Database.php';

class LivestockController {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function list() {
        $stmt = $this->conn->query("SELECT id, animal_type, count, health_status, milk_output FROM livestock ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
