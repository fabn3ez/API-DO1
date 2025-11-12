<?php
// Migration for sheds table
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$conn = $database->getConnection();

$dropSql = "DROP TABLE IF EXISTS sheds;";
$createSql = "CREATE TABLE sheds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150),
    capacity INT,
    description TEXT,
    type ENUM('barn', 'coop', 'stable', 'warehouse', 'other') DEFAULT 'other',
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";

try {
    $conn->exec($dropSql);
    $conn->exec($createSql);
    echo "Table sheds dropped and recreated successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
