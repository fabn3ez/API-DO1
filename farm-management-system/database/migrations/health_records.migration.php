

<?php
// Include database connection
require_once __DIR__ . '/../../config/db.php';

$database = new Database();
$conn = $database->getConnection();

// Migration for health_records table

$dropSql = "DROP TABLE IF EXISTS health_records;";
$createSql = "CREATE TABLE health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    health_status VARCHAR(100) NOT NULL,
    health_issue VARCHAR(255),
    treatment VARCHAR(255),
    treatment_status VARCHAR(100),
    record_date DATE NOT NULL,
    check_date DATE,
    next_vaccination DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";

try {
    $conn->exec($dropSql);
    $conn->exec($createSql);
    echo "Table health_records dropped and recreated successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
