<?php
require 'conexion.php';

if (!isset($_GET['id'])) {
    echo "ID de producto no proporcionado.";
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE ID_Producto = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo "Producto no encontrado.";
        exit;
    }
} catch (PDOException $e) {
    echo "Error al obtener el producto: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $imagen = $_POST['imagen'] ?? '';
    $edad = $_POST['edad'] ?? '';
    $stock = $_POST['stock'] ?? 0;

    try {
        $sql = "UPDATE producto SET Nombre = :nombre, Descripcion = :descripcion, Precio = :precio, Imagen_URL = :imagen, Edad = :edad, Stock = :stock WHERE ID_Producto = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':imagen', $imagen);
        $stmt->bindParam(':edad', $edad);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        echo "<script>alert('Producto actualizado correctamente.'); window.location.href='productos.php';</script>";
        exit;
    } catch (PDOException $e) {
        echo "Error al actualizar el producto: " . $e->getMessage();
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Producto - Doggies</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background-color: #f0f2f5;
    }
    header {
      background-color: rgba(255,255,255,0.95);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      padding: 1em 2em;
    }
    .menu {
      display: flex;
      justify-content: center;
      align-items: center;
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .menu li {
      flex: 1;
      text-align: center;
    }
    .menu li.logo a {
      display: block;
      width: 150px;
      height: 60px;
      background-image: url('img/fondo.jpg');
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      text-indent: -9999px;
      margin: 0 auto;
    }
    .menu a {
      text-decoration: none;
      color: #333;
      font-weight: bold;
      font-size: 1rem;
    }
    .form-container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 80vh;
      padding: 2em;
    }
    .form-box {
      background-color: #ffffff;
      padding: 2em;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 600px;
    }
    .form-box h2 {
      margin-top: 0;
      margin-bottom: 1em;
      color: #2c3e50;
      text-align: center;
    }
    label {
      display: block;
      margin-bottom: 1em;
      color: #34495e;
      font-weight: 600;
    }
    input[type="text"],
    input[type="number"],
    textarea,
    select {
      width: 100%;
      padding: 0.75em;
      border: 1px solid #bdc3c7;
      border-radius: 4px;
      font-size: 1em;
      margin-top: 0.5em;
    }
    button {
      background-color: #27ae60;
      color: #ffffff;
      padding: 0.75em 1.5em;
      border: none;
      border-radius: 4px;
      font-size: 1em;
      cursor: pointer;
      transition: background-color 0.3s ease;
      display: block;
      margin: 1em auto 0 auto;
    }
    button:hover {
      background-color: #219150;
    }
    footer {
      background-color: rgba(51,51,51,0.95);
      color: #fff;
      text-align: center;
      padding: 1.5em 2em;
      position: relative;
    }
    .footer-content h3 {
      font-size: 1.4em;
      margin-bottom: 0.5em;
    }
    .social-links {
      display: flex;
      justify-content: center;
      gap: 1em;
    }
    .social-links a {
      color: #fff;
      font-size: 1.5rem;
      transition: color 0.3s ease;
    }
    .social-links a:hover {
      color: #ffd700;
    }
    footer::after {
      content: "© 2025 Doggies. Todos los derechos reservados.";
      display: block;
      margin-top: 1em;
      font-size: 0.9rem;
      color: #ccc;
    }
  </style>
</head>
<body>
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php"><i class="fas fa-dog"></i> Productos</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
      </ul>
    </nav>
  </header>

  <div class="form-container">
    <div class="form-box">
      <h2>Editar Producto</h2>
      <form method="post">
        <label>Nombre:
          <input type="text" name="nombre" value="<?= htmlspecialchars($producto['Nombre']) ?>" required>
        </label>
        <label>Descripción:
          <textarea name="descripcion" required><?= htmlspecialchars($producto['Descripcion']) ?></textarea>
        </label>
        <label>Precio:
          <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($producto['Precio']) ?>" required>
        </label>
        <label>URL de la Imagen:
          <input type="text" name="imagen" value="<?= htmlspecialchars($producto['Imagen_URL']) ?>" required>
        </label>
        <label>Edad:
          <select name="edad" required>
                <option value="cachorro" <?= (isset($producto['Edad']) && $producto['Edad'] === 'cachorro') ? 'selected' : '' ?>>Cachorro</option>
                <option value="adulto" <?= (isset($producto['Edad']) && $producto['Edad'] === 'adulto') ? 'selected' : '' ?>>Adulto</option>
                <option value="senior" <?= (isset($producto['Edad']) && $producto['Edad'] === 'senior') ? 'selected' : '' ?>>Senior</option>
          </select>
        </label>
        <label>Stock:
          <input type="number" name="stock" value="<?= htmlspecialchars($producto['Stock']) ?>" required>
        </label>
        <button type="submit">Guardar Cambios</button>
      </form>
    </div>
  </div>

  <footer>
    <div class="footer-content">
      <h3>Síguenos</h3>
      <div class="social-links">
        <a href="https://www.facebook.com/profile.php?id=100069951193254" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/doggiespaseadores/" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://www.tiktok.com/@doggies_paseadores" target="_blank"><i class="fab fa-tiktok"></i></a>
        <a href="mailto:doggiespasto@gmail.com"><i class="fas fa-envelope"></i></a>
      </div>
    </div>
  </footer>
</body>
</html>
