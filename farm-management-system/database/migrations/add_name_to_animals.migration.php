<?php
// Migration to add name column to animals table
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$conn = $database->getConnection();

$sql = "ALTER TABLE animals ADD COLUMN name VARCHAR(100) AFTER tag_number;";

try {
    $conn->exec($sql);
    echo "Column name added to animals table successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
