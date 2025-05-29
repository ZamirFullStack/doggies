<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['ID_Rol'] != 2) {
    header("Location: index.php");
    exit;
}

$idPedido = $_GET['id'] ?? null;
if (!$idPedido) {
    echo "<h3>Pedido no especificado.</h3>";
    exit;
}

$stmtPedido = $pdo->prepare("SELECT p.*, u.Nombre AS Nombre_Usuario FROM pedido p LEFT JOIN usuario u ON p.ID_Usuario = u.ID_Usuario WHERE ID_Pedido = ?");
$stmtPedido->execute([$idPedido]);
$pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "<h3>Pedido no encontrado.</h3>";
    exit;
}

$stmtDetalle = $pdo->prepare("SELECT * FROM pedido_productos WHERE ID_Pedido = ?");
$stmtDetalle->execute([$idPedido]);
$productos = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalle del Pedido</title>
  <link rel="stylesheet" href="css/Login.css">
  <style>
    .detalle-container {
      max-width: 800px;
      margin: 2em auto;
      background: #fff;
      padding: 2em;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2, h3 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 1em; }
    th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: center; }
    th { background-color: #f3f3f3; font-weight: bold; }
    a.btn { display: inline-block; margin-top: 1em; padding: 0.6em 1em; background-color: #2196f3; color: #fff; text-decoration: none; border-radius: 6px; }
  </style>
</head>
<body class="login-page">
  <div class="detalle-container">
    <h2>Detalle del Pedido #<?= $pedido['ID_Pedido'] ?></h2>
    <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['Nombre_Usuario'] ?? 'Invitado') ?></p>
    <p><strong>Fecha:</strong> <?= $pedido['Fecha_Pedido'] ?></p>
    <p><strong>Dirección:</strong> <?= htmlspecialchars($pedido['Direccion_Entrega']) ?></p>
    <p><strong>Ciudad:</strong> <?= htmlspecialchars($pedido['Ciudad'] ?? 'N/A') ?></p>
    <p><strong>Departamento:</strong> <?= htmlspecialchars($pedido['Departamento'] ?? 'N/A') ?></p>
    <p><strong>Método de Pago:</strong> <?= htmlspecialchars($pedido['Metodo_Pago']) ?></p>
    <p><strong>Estado:</strong> <?= htmlspecialchars($pedido['Estado']) ?></p>
    <p><strong>Total:</strong> $<?= number_format($pedido['Total'], 0, ',', '.') ?></p>

    <h3>Productos</h3>
    <table>
      <thead>
        <tr><th>Nombre</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $prod): ?>
        <tr>
          <td><?= htmlspecialchars($prod['Nombre_Producto']) ?></td>
          <td><?= $prod['Cantidad'] ?></td>
          <td>$<?= number_format($prod['Precio_Unitario'], 0, ',', '.') ?></td>
          <td>$<?= number_format($prod['Cantidad'] * $prod['Precio_Unitario'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <a href="admin.php" class="btn">← Volver al panel</a>
  </div>
</body>
</html>
