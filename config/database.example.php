<?php
declare(strict_types=1);

$host = '127.0.0.1';
$dbname = 'your_database_name';
$username = 'your_database_user';
$password = 'your_database_password';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    throw new RuntimeException('Erreur de connexion a la base de donnees.', 0, $e);
}
