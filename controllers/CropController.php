<?php
require_once __DIR__ . '/../config/Database.php';

class CropController {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function statusList() {
        $stmt = $this->conn->query("SELECT id, crop_name, health_status, last_irrigation_date, expected_yield FROM crops ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
