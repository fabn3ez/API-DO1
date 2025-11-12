<?php
// Migration to add tag_number column to animals table
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$conn = $database->getConnection();

$sql = "ALTER TABLE animals ADD COLUMN tag_number VARCHAR(50) AFTER health_status;";

try {
    $conn->exec($sql);
    echo "Column tag_number added to animals table successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
