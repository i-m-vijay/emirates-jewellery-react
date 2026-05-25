<?php
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host:$port", $user, $password);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS laravel_admin");
    echo "Database created successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Please ensure MySQL is running in XAMPP.\n";
}
?>
