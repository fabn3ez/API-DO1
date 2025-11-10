<?php
// customer_reviews.migration.php
// Customer reviews and ratings

$mysqli = new mysqli("localhost", "root", "1234", "farm");

$sql = "CREATE TABLE IF NOT EXISTS customer_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    animal_id INT NOT NULL,
    order_id INT,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_title VARCHAR(255),
    review_text TEXT,
    is_approved TINYINT(1) DEFAULT 0,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES sales_orders(id) ON DELETE SET NULL
)";

if ($mysqli->query($sql) === TRUE) {
    echo "Table 'customer_reviews' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

$mysqli->close();
?>