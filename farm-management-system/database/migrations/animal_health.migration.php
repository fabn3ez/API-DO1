<?php
//create animal_health table migration
$mysqli = new mysqli("localhost", "root", "qwer4321..E", "farm");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create animal_health table
$sql = "CREATE TABLE IF NOT EXISTS animal_health (
   id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    health_event ENUM('vaccination','treatment','checkup','sickness'),
    description TEXT,
    date_performed DATE,
    vet_name VARCHAR(100),
    cost DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE
)";
if ($mysqli->query($sql) === TRUE) {
    echo "Table 'animal_health' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}
$mysqli->close();   