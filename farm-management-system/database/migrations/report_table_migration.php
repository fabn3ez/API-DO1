<?php
// report_table_migration.php - Create a report table with foreign keys to relevant tables
$mysqli = new mysqli('localhost', 'root', '1234', 'farm');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Create report table
$sql = "CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    inventory_id INT DEFAULT NULL,
    transaction_id INT DEFAULT NULL,
    sales_order_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE SET NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE SET NULL
) ENGINE=InnoDB;";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'reports' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

$mysqli->close();
echo "Migration complete.\n";
