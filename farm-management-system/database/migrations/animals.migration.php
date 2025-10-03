<?php

//create animals table migration

$mysqli = new mysqli("localhost", "root", "qwer4321..E");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create database if not exists
$mysqli->query("CREATE DATABASE IF NOT EXISTS farm");
$mysqli->select_db("farm");

// Create animals table
$sql = "CREATE TABLE IF NOT EXISTS animals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(255) NOT NULL,
            breed VARCHAR (255) NOT NULL,
            gender VARCHAR(255) NOT NULL,
            number INT NOT NULL,
            avg_weight VARCHAR(255) NOT NULL,
            shed_no VARCHAR (255) NOT NULL
)";
if ($mysqli->query($sql) === TRUE) {
    echo "Table 'animals' created successfully.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

// Insert sample values
$insert = "INSERT INTO animals (type, breed, gender, number, avg_weight, shed_no) VALUES
('Dog', 'Labrador Retriever', 'Male', 15, '30 kg', 'Shed 1'),
('Cat', 'Siamese', 'Female', 10, '4 kg', 'Shed 2'),
('Rabbit', 'Netherland Dwarf', 'Female', 200, '1.2 kg', 'Shed 3'),
('Horse', 'Thoroughbred', 'Male', 50, '500 kg', 'Shed 4'),
('Cow', 'Jersey', 'Female', 650, '450 kg', 'Shed 5'),
('Cattle', 'Texas Longhorn', 'Male', 350, '600 kg', 'Shed 5'),
('Hen', 'Rhode Island Red', 'Female', 900, '2.5 kg', 'Shed 6'),
('Cock', 'Leghorn', 'Male', 450, '3 kg', 'Shed 6'),
('Goat', 'Boer', 'Female', 370, '70 kg', 'Shed 7'),
('Sheep', 'Dorper', 'Female', 230, '60 kg', 'Shed 8'),
('Turkey', 'Bourbon Red', 'Male', 320, '15 kg', 'Shed 9'),
('Goose', 'Embden', 'Female', 90, '8 kg', 'Shed 10'),
('Fish', 'Tilapia', 'Mixed', 500, '4.3 kg', 'Pond 1'),
('Fish', 'Nile Perch', 'Mixed', 600, '15.8 KG', 'Pond 2')";

if ($mysqli->query($insert) === TRUE) {
    echo "Sample animals inserted successfully.\n";
} else {
    echo "Error inserting animals: " . $mysqli->error . "\n";
}

$mysqli->close();