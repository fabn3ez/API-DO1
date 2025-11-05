<?php
require_once __DIR__ . '/../config/Database.php';

class UserController {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getCurrentUser() {
        // If you have sessions, return session user — here is a demo mock
        return ["id" => 1, "name" => "Samuel", "role" => "farmer"];
    }
}
?>
