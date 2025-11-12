<?php
// Migration to add user_id column to animals table
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$conn = $database->getConnection();

$sql = "ALTER TABLE animals ADD COLUMN user_id INT AFTER species;";

try {
    $conn->exec($sql);
    echo "Column user_id added to animals table successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
