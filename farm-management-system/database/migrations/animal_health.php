<?php

//create animal_health table migration

$mysqli = new mysqli("localhost", "root", "1234");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create database if not exists
$mysqli->query("CREATE DATABASE IF NOT EXISTS farm");
$mysqli->select_db("farm");
// Create animal_health table
$sql = "CREATE TABLE animal_health (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    health_event ENUM('vaccination', 'treatment', 'checkup', 'sickness') NOT NULL,
    description TEXT,
    date_performed DATE,
    vet_name VARCHAR(100),
    cost DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
 if ($mysqli->query($sql) === TRUE) {
    echo "Table 'animal health' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}