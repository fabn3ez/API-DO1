<?php
require_once __DIR__ . '/../config/Database.php';

class AdminController
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getDashboardStats()
    {
        try {
            $stats = [];

            // Total farmers
            $query = $this->conn->query("SELECT COUNT(*) AS total FROM farmers");
            $stats['total_farmers'] = $query->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Total crops
            $query = $this->conn->query("SELECT COUNT(*) AS total FROM crops");
            $stats['total_crops'] = $query->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Total livestock
            $query = $this->conn->query("SELECT COUNT(*) AS total FROM livestock");
            $stats['total_livestock'] = $query->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            // Total sales
            $query = $this->conn->query("SELECT SUM(total) AS total_sales FROM sales");
            $stats['total_sales'] = $query->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

            return $stats;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
