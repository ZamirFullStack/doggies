<?php
$host = 'mysql.railway.internal'; // Host para entornos internos en Railway
$port = 3306;
$db   = 'railway';
$user = 'root';
$pass = 'AaynZNNKYegnXoInEgQefHggDxoRieEL';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
