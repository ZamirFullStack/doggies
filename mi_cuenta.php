<?php
session_start();

if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario'])) {
    header("Location: Login.php");
    exit;
}

require 'conexion.php';

$usuario = $_SESSION['usuario'];

// Datos de usuario
$stmt = $pdo->prepare("SELECT * FROM usuario WHERE ID_Usuario = :id");
$stmt->execute(['id' => $usuario['ID_Usuario']]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$datos) {
    die("❌ Error: No se encontró el usuario.");
}

// Obtener historial de pedidos y sus productos (agrupados)
$stmt = $pdo->prepare("
    SELECT p.ID_Pedido, p.Fecha_Pedido, p.Total, p.Metodo_Pago,
        dp.Cantidad, dp.Precio_Unitario, dp.Nombre_Producto,
        pr.Imagen_URL, pr.Stock
    FROM pedido p
    JOIN pedido_productos dp ON p.ID_Pedido = dp.ID_Pedido
    LEFT JOIN producto pr ON pr.Nombre = dp.Nombre_Producto
    WHERE p.ID_Usuario = :id
    ORDER BY p.Fecha_Pedido DESC, p.ID_Pedido DESC
");
$stmt->execute(['id' => $usuario['ID_Usuario']]);
$rawPedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar productos por pedido
$pedidos = [];
foreach ($rawPedidos as $row) {
    $id = $row['ID_Pedido'];
    if (!isset($pedidos[$id])) {
        $pedidos[$id] = [
            'ID_Pedido' => $row['ID_Pedido'],
            'Fecha_Pedido' => $row['Fecha_Pedido'],
            'Total' => $row['Total'],
            'Metodo_Pago' => $row['Metodo_Pago'],
            'productos' => [],
        ];
    }
    // Construir ruta de imagen (igual que en webhook/admin)
    $imgRuta = !empty($row['Imagen_URL']) ? $row['Imagen_URL'] : 'default.jpg';
    if (strpos($imgRuta, 'img/Productos/') === false) {
        $imgRuta = 'img/Productos/' . $imgRuta;
    }
    $imgRuta = ltrim($imgRuta, '/');
    $imgUrlFull = "https://doggies-production.up.railway.app/$imgRuta";
    $row['Imagen_Url_Full'] = $imgUrlFull;
    $pedidos[$id]['productos'][] = $row;
}

function formatearFecha($fecha) {
    $meses = [
        '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
        '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
        '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
    ];
    $fechaObj = new DateTime($fecha);
    $dia = $fechaObj->format('d');
    $mes = $meses[$fechaObj->format('m')];
    $anio = $fechaObj->format('Y');
    return "$dia de $mes de $anio";
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
    .pedido-group {
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 15px 18px;
      margin-bottom: 30px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.10);
    }
    .pedido-header {
      font-size: 17px;
      font-weight: bold;
      margin-bottom: 10px;
      color: #1b2945;
    }
    .productos-list {
      margin: 0;
      padding: 0;
      list-style: none;
    }
    .pedido-producto {
      display: flex;
      align-items: center;
      border-bottom: 1px solid #f0f0f0;
      padding: 10px 0;
      gap: 18px;
    }
    .pedido-producto:last-child {
      border-bottom: none;
    }
    .pedido-producto img {
      width: 68px;
      height: 68px;
      object-fit: cover;
      border-radius: 8px;
      background: #f9f9f9;
      border: 1px solid #ececec;
    }
    .pedido-info {
      flex: 1;
    }
    .pedido-info h4 {
      margin: 0 0 2px;
      color: #333;
      font-size: 16px;
      font-weight: bold;
    }
    .pedido-info p {
      margin: 2px 0;
      color: #555;
      font-size: 14px;
    }
    .pedido-producto form button {
      background-color: #39c5e0;
      color: white;
      padding: 5px 13px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      margin-left: 8px;
      transition: background 0.18s;
    }
    .pedido-producto .agotado {
      color: red;
      font-weight: bold;
      margin-left: 14px;
    }
    .pedido-total {
      color: #311e4c;
      font-size: 16px;
      font-weight: bold;
      margin-top: 8px;
      text-align: right;
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
    <div class="auth-container" style="max-width: 900px;">
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
          <input type="text" name="telefono" value="<?= htmlspecialchars($datos['Telefono'] ?? '') ?>" placeholder="Teléfono">
        </div>
        <div class="input-group">
          <i class="fas fa-map-marker-alt"></i>
          <input type="text" name="direccion" value="<?= htmlspecialchars($datos['Direccion'] ?? '') ?>" placeholder="Dirección">
        </div>
        <div class="input-group password-wrapper">
          <input type="password" name="nueva_contrasena" id="nueva_contrasena" placeholder="Nueva contraseña (opcional)">
          <i class="fas fa-eye" onclick="togglePassword()"></i>
        </div>
        <button type="submit" class="auth-btn">Actualizar perfil</button>
      </form>

      <h2 style="margin-top: 2em;">Historial de Compras</h2>
      <?php if (count($pedidos) > 0): ?>
        <?php foreach ($pedidos as $p): ?>
          <div class="pedido-group">
            <div class="pedido-header">
              Pedido #<?= $p['ID_Pedido'] ?> &mdash; <?= formatearFecha($p['Fecha_Pedido']) ?>
              <span style="float:right; color:#556;">Forma de pago: <?= htmlspecialchars($p['Metodo_Pago']) ?></span>
            </div>
            <ul class="productos-list">
              <?php foreach ($p['productos'] as $prod): ?>
                <li class="pedido-producto">
                  <img src="<?= htmlspecialchars($prod['Imagen_Url_Full']) ?>" alt="<?= htmlspecialchars($prod['Nombre_Producto']) ?>">
                  <div class="pedido-info">
                    <h4><?= htmlspecialchars($prod['Nombre_Producto']) ?></h4>
                    <p>Cantidad: <?= $prod['Cantidad'] ?> &mdash; Precio: $<?= number_format($prod['Precio_Unitario'], 0, ',', '.') ?></p>
                    <p>Subtotal: $<?= number_format($prod['Precio_Unitario'] * $prod['Cantidad'], 0, ',', '.') ?></p>
                  </div>
                  <?php if ($prod['Stock'] > 0): ?>
                    <form method="POST" action="agregar_carrito.php">
                      <input type="hidden" name="nombre" value="<?= htmlspecialchars($prod['Nombre_Producto']) ?>">
                      <input type="hidden" name="precio" value="<?= $prod['Precio_Unitario'] ?>">
                      <input type="hidden" name="cantidad" value="1">
                      <button type="submit">Volver a comprar</button>
                    </form>
                  <?php else: ?>
                    <span class="agotado">Producto no disponible</span>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
            <div class="pedido-total">
              Total del pedido: $<?= number_format($p['Total'], 0, ',', '.') ?>
            </div>
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
