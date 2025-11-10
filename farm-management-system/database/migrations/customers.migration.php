<?php
// customers.migration.php
// Extends users table with customer-specific information

$mysqli = new mysqli("localhost", "root", "1234");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create database if not exists
$mysqli->query("CREATE DATABASE IF NOT EXISTS farm");
$mysqli->select_db("farm");

// Create customers table
$sql = "CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone_number VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Kenya',
    company_name VARCHAR(255),
    tax_id VARCHAR(100),
    preferred_payment_method ENUM('mpesa', 'card', 'bank_transfer', 'cash') DEFAULT 'mpesa',
    loyalty_points INT DEFAULT 0,
    customer_type ENUM('individual', 'business', 'wholesaler') DEFAULT 'individual',
    delivery_instructions TEXT,
    newsletter_subscription TINYINT(1) DEFAULT 1,
    marketing_emails TINYINT(1) DEFAULT 0,
    account_balance DECIMAL(10,2) DEFAULT 0.00,
    credit_limit DECIMAL(10,2) DEFAULT 0.00,
    customer_since DATE,
    last_purchase_date DATE,
    total_purchases DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
)";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'customers' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

// Insert sample customer data (linking to existing users with customer role)
$insert = "INSERT IGNORE INTO customers (
    user_id, phone_number, address, city, state, zip_code, 
    company_name, preferred_payment_method, customer_type, 
    customer_since, last_purchase_date, total_purchases
) VALUES 
(
    (SELECT id FROM users WHERE role='customer' LIMIT 1), 
    '+254712345678', 
    '123 Moi Avenue', 
    'Nairobi', 
    'Nairobi County', 
    '00100', 
    'Nairobi Butchery', 
    'mpesa', 
    'business', 
    '2024-01-15', 
    '2024-03-20', 
    150000.00
),
(
    (SELECT id FROM users WHERE role='customer' AND id NOT IN (SELECT user_id FROM customers) LIMIT 1), 
    '+254723456789', 
    '456 Kimathi Street', 
    'Nakuru', 
    'Nakuru County', 
    '20100', 
    NULL, 
    'mpesa', 
    'individual', 
    '2024-02-01', 
    '2024-03-18', 
    75000.00
),
(
    (SELECT id FROM users WHERE role='customer' AND id NOT IN (SELECT user_id FROM customers) LIMIT 1), 
    '+254734567890', 
    '789 Eldoret Road', 
    'Eldoret', 
    'Uasin Gishu', 
    '30100', 
    'Eldoret Meat Suppliers', 
    'bank_transfer', 
    'wholesaler', 
    '2024-01-10', 
    '2024-03-22', 
    450000.00
)";

if ($mysqli->query($insert) === TRUE) {
    echo "Sample customers inserted successfully.\n";
} else {
    echo "Error inserting customers: " . $mysqli->error . "\n";
}

$mysqli->close();
?>