<?php
session_start();

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$carrito = $_SESSION['carrito'];
$total = 0;

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
    die("\u274c Error de conexi\u00f3n: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Carrito - Doggies</title>
  <link rel="stylesheet" href="css/carrito.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }
    main {
      flex: 1;
    }
    .carrito-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      margin-right: 0.5rem;
  }
    .carrito-wrapper {
      max-height: 420px;
      overflow-y: auto;
      border: 1px solid #ccc;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      margin-bottom: 1rem;
    }

    .carrito-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 600px;
    }

    .carrito-table th, .carrito-table td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #eee;
      background-color: #fff;
    }

    .carrito-table thead th {
      background-color: #f8f8f8;
      position: sticky;
      top: 0;
      z-index: 1;
    }

    .producto-detalle {
      display: flex;
      align-items: center;
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

  <main class="auth-container" style="max-width: 1000px;">
    <h2>Mi Carrito</h2>
    <?php if (empty($carrito)): ?>
      <p>Tu carrito está vacío.</p>
    <?php else: ?>
      <div class="carrito-wrapper">
      <table class="carrito-table">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($carrito as $index => $producto): 
            $stmt = $pdo->prepare("SELECT Imagen_URL FROM producto WHERE Nombre = ?");
            $stmt->execute([$producto['nombre']]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $imgRuta = $fila ? $fila['Imagen_URL'] : 'img/Productos/default.jpg';

            $subtotal = $producto['precio'] * $producto['cantidad'];
            $total += $subtotal;
          ?>
            <tr>
              <td>
                <div class="producto-detalle">
                  <img src="<?= htmlspecialchars($imgRuta) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="carrito-img">
                  <?= htmlspecialchars($producto['nombre']) ?>
                </div>
              </td>
              <td>$<?= number_format($producto['precio'], 0, ',', '.') ?></td>
              <td><?= $producto['cantidad'] ?></td>
              <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
              <td>
                <form method="POST" action="eliminar.php" onsubmit="return confirm('¿Eliminar este producto del carrito?');">
                  <input type="hidden" name="index" value="<?= $index ?>">
                  <button type="submit">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" style="text-align: right;">Total:</th>
            <th colspan="2">$<?= number_format($total, 0, ',', '.') ?></th>
          </tr>
        </tfoot>
      </table>
      </div>
      <div class="resumen-footer">
        <a href="Checkout.php" class="boton-comprar">Finalizar compra</a>
      </div>
    <?php endif; ?>
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
</body>
</html>
