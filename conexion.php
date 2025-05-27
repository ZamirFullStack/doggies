<?php
// Solo intentar cargar .env en desarrollo (cuando esté en local)
if (file_exists(__DIR__ . '/.env')) {
    require __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$url = getenv('DATABASE_URL');
if ($url) {
    $dbparts = parse_url($url);
    $host = $dbparts["host"];
    $port = $dbparts["port"];
    $user = $dbparts["user"];
    $pass = $dbparts["pass"];
    $db   = ltrim($dbparts["path"], '/');
} else {
    die("❌ No se encontró la variable DATABASE_URL");
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
