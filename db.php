<?php
// Database connection details
$host = 'localhost';
$db = 'supermarket';
$user = "root";
$pass = '';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// DSN (Data Source Name) string
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    // Create a PDO instance
    $pdo = new PDO($dsn, $user, $pass);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //echo "Connected successfully to the database.";

} catch (PDOException $e) {
    // If connection fails, catch the exception and display the error message
    die("Connection failed: " . $e->getMessage());
}
?>
