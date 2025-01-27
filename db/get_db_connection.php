<?php
$host = getenv(name: 'DB_HOST');
$db   = getenv(name: 'DB_NAME');
$user = getenv(name: 'DB_USER');
$pass = getenv(name: 'DB_PASS');

try {
    $pdo = new PDO(
        dsn: "mysql:host=$host;dbname=$db;charset=utf8mb4",
        username: $user,
        password: $pass
    );
    $pdo->setAttribute(
        attribute: PDO::ATTR_ERRMODE,
        value: PDO::ERRMODE_EXCEPTION
    );
    // Connection established
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>