<?php
$host = 'sql207.infinityfree.com';
$db   = 'if0_42226342_allonone';
$user = 'if0_42226342';
$pass = 'VqIUuAIZ38T0f8';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
