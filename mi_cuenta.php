<?php
session_start();

if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario'])) {
    header("Location: Login.php");
    exit;
}

require 'conexion.php';

$usuario = $_SESSION['usuario'];

$stmt = $pdo->prepare("SELECT * FROM usuario WHERE ID_Usuario = :id");
$stmt->execute(['id' => $usuario['ID_Usuario']]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$datos) {
    die("❌ Error: No se encontró el usuario.");
}

$stmt_pedidos = $pdo->prepare("SELECT p.ID_Pedido, p.Fecha_Pedido, p.Total, p.Metodo_Pago,
  dp.Cantidad, dp.Precio_Unitario, pr.Nombre AS Nombre_Producto, pr.Imagen_URL, pr.Stock
  FROM pedido p
  JOIN pedido_productos dp ON p.ID_Pedido = dp.ID_Pedido
  LEFT JOIN producto pr ON pr.Nombre = dp.Nombre_Producto
  WHERE p.ID_Usuario = :id
  ORDER BY p.Fecha_Pedido DESC");
$stmt_pedidos->execute(['id' => $usuario['ID_Usuario']]);
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
function formatearFecha($fecha) {
  $formatter = new IntlDateFormatter(
    'es_CO',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'America/Bogota',
    IntlDateFormatter::GREGORIAN,
    'd \'de\' MMMM \'de\' yyyy'
  );
  return $formatter->format(new DateTime($fecha));
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mi Cuenta - Doggies</title>
  <link rel="stylesheet" href="css/Login.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    header nav ul.menu {
      display: flex;
      justify-content: center;
      align-items: center;
      list-style: none;
      padding: 0;
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
    .pedido-box {
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .pedido-box img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
    }
    .pedido-info {
      flex: 1;
    }
    .pedido-info h4 {
      margin: 0 0 5px;
      color: #333;
    }
    .pedido-info p {
      margin: 2px 0;
      color: #555;
    }
    .pedido-box form button {
      background-color: #4caf50;
      color: white;
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .pedido-box .agotado {
      color: red;
      font-weight: bold;
    }
    .input-group.password-wrapper {
      position: relative;
    }
    .input-group.password-wrapper i.fa-eye {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
    }
  </style>
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
    <div class="auth-container" style="max-width: 800px;">
      <h2>Mi Perfil</h2>
      <form action="actualizar_perfil.php" method="POST">
        <div class="input-group">
          <i class="fas fa-user"></i>
          <input type="text" name="nombre" value="<?= htmlspecialchars($datos['Nombre']) ?>" placeholder="Nombre completo" required>
        </div>
        <div class="input-group">
          <i class="fas fa-envelope"></i>
          <input type="email" name="correo" value="<?= htmlspecialchars($datos['Correo']) ?>" placeholder="Correo electrónico" required>
        </div>
        <div class="input-group">
          <i class="fas fa-phone"></i>
          <input type="text" name="telefono" value="<?= htmlspecialchars($datos['Telefono']) ?>" placeholder="Teléfono">
        </div>
        <div class="input-group">
          <i class="fas fa-map-marker-alt"></i>
          <input type="text" name="direccion" value="<?= htmlspecialchars($datos['Direccion']) ?>" placeholder="Dirección">
        </div>
        <div class="input-group password-wrapper">
          <input type="password" name="nueva_contrasena" id="nueva_contrasena" placeholder="Nueva contraseña (opcional)">
          <i class="fas fa-eye" onclick="togglePassword()"></i>
        </div>
        <button type="submit" class="auth-btn">Actualizar perfil</button>
      </form>

      <h2 style="margin-top: 2em;">Historial de Compras</h2>
      <?php if (count($pedidos) > 0): ?>
        <?php foreach ($pedidos as $pedido): ?>
          <div class="pedido-box">
            <img src="<?= htmlspecialchars($pedido['Imagen_URL'] ?? 'img/default.jpg') ?>" alt="<?= htmlspecialchars($pedido['Nombre_Producto']) ?>">
            <div class="pedido-info">
              <h4><?= htmlspecialchars($pedido['Nombre_Producto']) ?></h4>
              <p>Fecha: <?= formatearFecha($pedido['Fecha_Pedido']) ?></p>
              <p>Cantidad: <?= $pedido['Cantidad'] ?> - Precio: $<?= number_format($pedido['Precio_Unitario'], 0, ',', '.') ?></p>
              <p>Total: $<?= number_format($pedido['Precio_Unitario'] * $pedido['Cantidad'], 0, ',', '.') ?></p>
              <p>Forma de pago: <?= $pedido['Metodo_Pago'] ?></p>
            </div>
            <?php if ($pedido['Stock'] > 0): ?>
              <form method="POST" action="agregar_carrito.php">
                <input type="hidden" name="nombre" value="<?= htmlspecialchars($pedido['Nombre_Producto']) ?>">
                <input type="hidden" name="precio" value="<?= $pedido['Precio_Unitario'] ?>">
                <input type="hidden" name="cantidad" value="1">
                <button type="submit">Volver a comprar</button>
              </form>
            <?php else: ?>
              <span class="agotado">Producto no disponible</span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No hay pedidos registrados aún.</p>
      <?php endif; ?>
    </div>
  </main>

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

  <script>
    function togglePassword() {
      const input = document.getElementById("nueva_contrasena");
      const icon = event.target;
      if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
      }
    }
  </script>
</body>
</html>
