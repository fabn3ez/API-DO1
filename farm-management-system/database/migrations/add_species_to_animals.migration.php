<?php
// Migration to add species column to animals table
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$conn = $database->getConnection();

$sql = "ALTER TABLE animals ADD COLUMN species VARCHAR(100) AFTER name;";

try {
    $conn->exec($sql);
    echo "Column species added to animals table successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
