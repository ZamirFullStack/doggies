<?php
session_start();

if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario'])) {
    header("Location: Login.php");
    exit;
}

require 'conexion.php';

$usuario = $_SESSION['usuario'];

// Info del usuario
$stmt = $pdo->prepare("SELECT * FROM usuario WHERE ID_Usuario = :id");
$stmt->execute(['id' => $usuario['ID_Usuario']]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$datos) {
    die("❌ Error: No se encontró el usuario.");
}

// Trae todos los pedidos del usuario
$stmt_pedidos = $pdo->prepare("SELECT * FROM pedido WHERE ID_Usuario = :id ORDER BY Fecha_Pedido DESC");
$stmt_pedidos->execute(['id' => $usuario['ID_Usuario']]);
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

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
    .pedido-box {
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .pedido-producto {
      display: flex;
      align-items: center;
      gap: 18px;
      background: #f8f8f8;
      border-radius: 7px;
      padding: 9px 7px;
    }
    .pedido-producto img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #eee;
    }
    .pedido-info {
      flex: 1;
    }
    .pedido-info h4 {
      margin: 0 0 5px;
      color: #333;
      font-size: 18px;
    }
    .pedido-info p {
      margin: 2px 0;
      color: #555;
    }
    .pedido-producto form button {
      background-color: #4caf50;
      color: white;
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .pedido-producto .agotado {
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
        <?php foreach ($pedidos as $pedido): ?>
          <div class="pedido-box">
            <div style="margin-bottom:5px;">
              <b>Pedido N° <?= $pedido['ID_Pedido'] ?></b> - <?= formatearFecha($pedido['Fecha_Pedido']) ?>
              <span style="margin-left:10px; font-size:13px; color:#555;">Estado: <?= htmlspecialchars($pedido['Estado']) ?></span>
              <div style="font-size:13px; color:#666;">Total: $<?= number_format($pedido['Total'], 0, ',', '.') ?> | Pago: <?= htmlspecialchars($pedido['Metodo_Pago']) ?></div>
            </div>
            <?php
            // Productos de este pedido
            $stmt_prod = $pdo->prepare("
                SELECT pp.Cantidad, pp.Precio_Unitario, pp.Nombre_Producto, pr.Imagen_URL, pr.Stock
                FROM pedido_productos pp
                LEFT JOIN producto pr ON pp.Nombre_Producto = pr.Nombre
                WHERE pp.ID_Pedido = ?
            ");
            $stmt_prod->execute([$pedido['ID_Pedido']]);
            $productos = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php foreach ($productos as $prod): ?>
              <div class="pedido-producto">
                <img src="<?= htmlspecialchars($prod['Imagen_URL'] ?? 'img/Productos/default.jpg') ?>" alt="<?= htmlspecialchars($prod['Nombre_Producto']) ?>">
                <div class="pedido-info">
                  <h4><?= htmlspecialchars($prod['Nombre_Producto']) ?></h4>
                  <p>Cantidad: <?= $prod['Cantidad'] ?> - Precio: $<?= number_format($prod['Precio_Unitario'], 0, ',', '.') ?></p>
                  <p>Subtotal: $<?= number_format($prod['Precio_Unitario'] * $prod['Cantidad'], 0, ',', '.') ?></p>
                </div>
                <?php if (isset($prod['Stock']) && $prod['Stock'] > 0): ?>
                  <form method="POST" action="agregar_carrito.php" style="margin-left:auto;">
                    <input type="hidden" name="nombre" value="<?= htmlspecialchars($prod['Nombre_Producto']) ?>">
                    <input type="hidden" name="precio" value="<?= $prod['Precio_Unitario'] ?>">
                    <input type="hidden" name="cantidad" value="1">
                    <button type="submit">Volver a comprar</button>
                  </form>
                <?php else: ?>
                  <span class="agotado" style="margin-left:auto;">Producto no disponible</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
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
        <a href="mailto:doggiespasto22@gmail.com"><i class="fas fa-envelope"></i></a>
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
