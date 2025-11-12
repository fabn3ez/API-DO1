<?php
require_once __DIR__ . '/../../config/db.php';
$database = new Database();
$conn = $database->getConnection();

try {
    $sql = "ALTER TABLE animals ADD COLUMN user_id INT DEFAULT NULL";
    $conn->exec($sql);
    echo "Column 'user_id' added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>