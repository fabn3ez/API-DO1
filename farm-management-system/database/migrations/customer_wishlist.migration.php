<?php
// customer_wishlist.migration.php
// Customer wishlist/saved items

$mysqli = new mysqli("localhost", "root", "1234", "farm");

$sql = "CREATE TABLE IF NOT EXISTS customer_wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    animal_id INT NOT NULL,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (customer_id, animal_id)
)";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'customer_wishlist' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

$mysqli->close();
?>