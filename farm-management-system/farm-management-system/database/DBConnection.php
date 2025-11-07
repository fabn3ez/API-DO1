<?php
class DBConnection {
    private $host = "127.0.0.1";   // or 'localhost'
    private $db = "farm_db";       // change to your DB name
    private $user = "root";        // change to your DB user
    private $password = "";        // change to your DB password
    private $port = "3306";
    public $conn;

    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $this->conn;
        } catch (PDOException $e) {
            // For UI debugging return json - in production handle differently
            echo json_encode(["error" => "DB connection failed: " . $e->getMessage()]);
            exit;
        }
    }
}
?>
