<?php
require_once __DIR__ . '/../config/Database.php';

class ReportController {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function notifications($limit = 10) {
        $stmt = $this->conn->prepare("SELECT message, type, created_at FROM notifications ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
