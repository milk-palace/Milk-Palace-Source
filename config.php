<?php
session_start();

$host = "[sqlserver]";
$dbname = "[database name]";
$username = "[cPanel/DB Username]";
$password = "[cPanel/DB Password]";   // <- your DB / cPanel password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>