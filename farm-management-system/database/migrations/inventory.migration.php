<?php
//create inventory table migration
$mysqli = new mysqli("localhost", "root", "1234", "farm");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
// Create inventory table

$sql = "CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2),
    total_value DECIMAL(10,2) AS (quantity * unit_price) STORED,
    supplier VARCHAR(255),
    purchase_date DATE,
    expiration_date DATE,
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($mysqli->query($sql) === TRUE) {
    echo "Table 'inventory' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

$mysqli->close();