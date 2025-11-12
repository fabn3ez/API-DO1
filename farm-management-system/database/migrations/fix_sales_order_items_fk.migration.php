<?php
// fix_sales_order_items_fk.migration.php
// Alters sales_order_items to reference animals(id) instead of products(id)

$mysqli = new mysqli("localhost", "root", "1234", "farm");

// Drop the old foreign key constraint
$drop_fk = "ALTER TABLE sales_order_items DROP FOREIGN KEY sales_order_items_ibfk_2";
if ($mysqli->query($drop_fk) === TRUE) {
    echo "Old foreign key dropped.\n";
} else {
    echo "Warning: Could not drop old foreign key (it may not exist): " . $mysqli->error . "\n";
}

// Add the new foreign key constraint
$add_fk = "ALTER TABLE sales_order_items ADD CONSTRAINT sales_order_items_ibfk_2 FOREIGN KEY (product_id) REFERENCES animals(id) ON DELETE CASCADE";
if ($mysqli->query($add_fk) === TRUE) {
    echo "Foreign key updated to reference animals(id).\n";
} else {
    echo "Error adding new foreign key: " . $mysqli->error . "\n";
}

$mysqli->close();
