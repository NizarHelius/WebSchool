<?php
// db.php - Database connection

$host = 'localhost';     // Database host
$dbname = 'e6school';    // Database name
$username = 'root';      // Database user (change if needed)
$password = '';          // Database password (change if needed)

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If connection fails, display an error message
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
