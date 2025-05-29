<?php
session_start();

$url = 'mysql://root:AaynZNNKYegnXoInEgQefHggDxoRieEL@centerbeam.proxy.rlwy.net:58462/railway';
$dbparts = parse_url($url);
$host = $dbparts["host"];
$port = $dbparts["port"];
$user = $dbparts["user"];
$pass = $dbparts["pass"];
$db   = ltrim($dbparts["path"], '/');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

if (isset($_SESSION['pedido_id'])) {
    $stmt = $pdo->prepare("UPDATE pedido SET Estado = 'pendiente' WHERE ID_Pedido = ?");
    $stmt->execute([$_SESSION['pedido_id']]);
    unset($_SESSION['pedido_id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pago Pendiente</title>
  <link rel="stylesheet" href="css/login.css">
  <style>
    body { text-align: center; padding: 2em; background-color: #fffde7; font-family: 'Roboto', sans-serif; }
    .box { background: #fff; padding: 2em; border-radius: 10px; max-width: 600px; margin: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    h1 { color: #f9a825; }
    p { margin-top: 1em; color: #444; }
    a { display: inline-block; margin-top: 2em; color: #fff; background-color: #fbc02d; padding: 0.8em 1.5em; border-radius: 8px; text-decoration: none; }
  </style>
</head>
<body>
  <div class="box">
    <h1>⏳ Tu pago está pendiente</h1>
    <p>Estamos esperando la confirmación del pago. Te notificaremos por correo cuando se apruebe.</p>
    <a href="index.php">Volver al inicio</a>
  </div>
</body>
</html>
