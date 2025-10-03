<?php

//create sales_orders table migration
$mysqli = new mysqli("localhost", "root", "qwer4321..E", "farm");

// Create sales_orders table
$sql = "CREATE TABLE IF NOT EXISTS sales_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_type ENUM('sale','purchase'),
    customer_supplier VARCHAR(100),
    total DECIMAL(10,2) DEFAULT 0,
    order_date DATE NOT NULL,
    status ENUM('pending','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($mysqli->query($sql) === TRUE) {
    echo "Table 'sales_orders' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}
$mysqli->close();
