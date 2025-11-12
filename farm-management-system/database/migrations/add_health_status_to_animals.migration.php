<?php
// Migration to add health_status column to animals table
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$conn = $database->getConnection();

$sql = "ALTER TABLE animals ADD COLUMN health_status VARCHAR(100) AFTER shed_id;";

try {
    $conn->exec($sql);
    echo "Column health_status added to animals table successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
