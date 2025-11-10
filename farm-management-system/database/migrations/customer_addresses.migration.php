<?php
// customer_addresses.migration.php
// Multiple delivery addresses for customers

$mysqli = new mysqli("localhost", "root", "1234", "farm");

$sql = "CREATE TABLE IF NOT EXISTS customer_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    address_type ENUM('home', 'work', 'billing', 'shipping') DEFAULT 'home',
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) DEFAULT 'Kenya',
    is_default TINYINT(1) DEFAULT 0,
    contact_person VARCHAR(255),
    contact_phone VARCHAR(20),
    delivery_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
)";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'customer_addresses' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

$mysqli->close();
?>