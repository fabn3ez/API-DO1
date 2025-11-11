<?php
// Migration for animal_health table using PDO
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$conn = $database->getConnection();

// Create animal_health table
$sql = "CREATE TABLE IF NOT EXISTS animal_health (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    health_event ENUM('vaccination', 'treatment', 'checkup', 'sickness') NOT NULL,
    description TEXT,
    date_performed DATE,
    vet_name VARCHAR(100),
    cost DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

try {
    $conn->exec($sql);
    echo "Table 'animal_health' created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
