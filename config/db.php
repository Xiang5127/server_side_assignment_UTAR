<?php
// Database configuration for default XAMPP setup
$host = "localhost";
$username = "root"; 
$password = "";     
$dbname = "cocurricular_db"; 

// Create the connection using Object-Oriented MySQLi
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // If it fails, stop the script and print the error
    die("Database Connection Failed: " . $conn->connect_error);
}

// Optional but recommended: Set the character set to ensure symbols (like emojis or special characters) save correctly
$conn->set_charset("utf8mb4");
?>