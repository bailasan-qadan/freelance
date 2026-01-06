<?php
// Database connection string
$connString = "mysql:host=localhost;dbname=freelance_marketplace";

// Database login information
$user = "root";
$pass = "";

try {
    // Create PDO connection
    $pdo = new PDO($connString, $user, $pass);

    // Enable error reporting using exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch results as associative arrays
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Stop execution if connection fails
    die("Database connection failed: " . $e->getMessage());
}
?>
