<?php
// products_and_sales_order_items.migration.php
// Migration for products and sales_order_items tables

$mysqli = new mysqli("localhost", "root", "1234", "farm");

// Create products table
$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($mysqli->query($sql_products) === TRUE) {
    echo "Table 'products' created successfully.\n";
} else {
    echo "Error creating table 'products': " . $mysqli->error . "\n";
}

// Create sales_order_items table
$sql_sales_order_items = "CREATE TABLE IF NOT EXISTS sales_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES sales_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

if ($mysqli->query($sql_sales_order_items) === TRUE) {
    echo "Table 'sales_order_items' created successfully.\n";
} else {
    echo "Error creating table 'sales_order_items': " . $mysqli->error . "\n";
}

$mysqli->close();
