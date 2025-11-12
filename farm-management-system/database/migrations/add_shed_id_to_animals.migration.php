<?php
// Migration to add shed_id column to animals table
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$conn = $database->getConnection();

$sql = "ALTER TABLE animals ADD COLUMN shed_id INT AFTER id;";

try {
    $conn->exec($sql);
    echo "Column shed_id added to animals table successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
