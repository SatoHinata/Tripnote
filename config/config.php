<?php
$dsn = 'mysql:dbname=XXXDB;host=localhost';
$user = 'XXXUSER';
$password = 'XXXPASSWORD';

// PDOオプションで明示的にutf8mb4を指定
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    echo 'Database connection failed: ' . $e->getMessage();
    exit();
}