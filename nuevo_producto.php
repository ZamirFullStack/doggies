<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['ID_Rol'] != 2) {
    header("Location: index.php");
    exit;
}

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $edad = $_POST['edad'];
    $imagen = $_POST['imagen'];
    $stock = intval($_POST['stock']);

    $stmt = $pdo->prepare("INSERT INTO producto (nombre, descripcion, precio, edad, imagen_URL, stock) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $precio, $edad, $imagen, $stock]);

    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Añadir Producto</title>
  <link rel="stylesheet" href="css/Login.css">
</head>
<body class="login-page">
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php"><i class="fas fa-dog"></i> Productos</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <div class="auth-container" style="max-width: 600px;">
      <h2>Nuevo Producto</h2>
      <form method="POST">
        <div class="input-group">
          <input type="text" name="nombre" placeholder="Nombre del producto" required>
        </div>
        <div class="input-group">
          <input type="text" name="descripcion" placeholder="Descripción" required>
        </div>
        <div class="input-group">
          <input type="number" name="precio" step="0.01" placeholder="Precio" required>
        </div>
        <div class="input-group">
          <input type="number" name="stock" placeholder="Cantidad en stock" required>
        </div>
        <div class="input-group">
          <select name="edad" required>
            <option value="cachorro">Cachorro</option>
            <option value="adulto">Adulto</option>
            <option value="senior">Senior</option>
          </select>
        </div>
        <div class="input-group">
          <input type="text" name="imagen" placeholder="Ruta de imagen (ej: img/Productos/nuevo.jpg)" required>
        </div>
        <button type="submit" class="auth-btn">Guardar Producto</button>
      </form>
    </div>
  </main>
</body>
</html>
