<?php
$host = 'localhost';
$dbname = 'u3097720_default';
$username = 'u3097720_default';
$password = 'KN886oVZfIN3tqj4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET CHARACTER SET utf8mb4");
} catch (PDOException $e) {
    error_log("Ошибка подключения к базе данных: " . $e->getMessage());
    die(json_encode(['success' => false, 'error' => 'Ошибка подключения к базе данных']));
}
?>