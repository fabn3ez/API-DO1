<?php
// db_connect.php

$host = 'localhost';
$db_user = 'root';
$db_pass = ''; // ✅ no password
$db_name = 'farm';

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}
echo "✅ Database connection successful!";
?>