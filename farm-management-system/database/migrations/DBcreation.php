<?php

//create connection
$mysqli = new mysqli("localhost", "root", "1234", "farm");

//create database farm
$sql = "CREATE DATABASE IF NOT EXISTS farm";
if ($mysqli->query($sql) === TRUE) {
	echo "Database created successfully";
} else {
	echo "Error creating database: " . $mysqli->error;
}