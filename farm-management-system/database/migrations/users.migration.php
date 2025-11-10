<?php

//create users table migration

$mysqli = new mysqli("localhost", "root", "1234");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create database if not exists
$mysqli->query("CREATE DATABASE IF NOT EXISTS farm");
$mysqli->select_db("farm");
// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'farmer', 'customer') DEFAULT 'customer',
    active TINYINT(1) DEFAULT 1,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            
)";
if ($mysqli->query($sql) === TRUE) {
    echo "Table 'users' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}
$mysqli->close();
?>