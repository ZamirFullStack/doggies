<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Eliminar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_index'])) {
    $idx = intval($_POST['delete_index']);
    if (isset($_SESSION['carrito'][$idx])) {
        unset($_SESSION['carrito'][$idx]);
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
    header('Location: carrito.php');
    exit;
}

// Actualizar cantidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_index'], $_POST['cantidad'])) {
    $idx = intval($_POST['update_index']);
    $qty = max(1, min(25, intval($_POST['cantidad'])));
    if (isset($_SESSION['carrito'][$idx])) {
        $_SESSION['carrito'][$idx]['cantidad'] = $qty;
    }
    header('Location: carrito.php');
    exit;
}

$carrito = $_SESSION['carrito'];
$total = 0;

$url = 'mysql://root:AaynZNNKYegnXoInEgQefHggDxoRieEL@centerbeam.proxy.rlwy.net:58462/railway';
$dbparts = parse_url($url);
$host = $dbparts['host'];
$port = $dbparts['port'];
$user = $dbparts['user'];
$pass = $dbparts['pass'];
$db   = ltrim($dbparts['path'], '/');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('❌ Error de conexión: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Carrito - Doggies</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f9f9f9;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin: 0;
    }
    header {
      background-color: #fff;
      padding: 20px 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .menu {
      display: flex;
      justify-content: space-evenly;
      align-items: center;
      list-style: none;
      margin: 0;
      padding: 0 10px;
      flex-wrap: wrap;
      gap: 5px;
    }
    .menu li.logo a {
      display: block;
      width: 120px;
      height: 48px;
      background-image: url('img/fondo.jpg');
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      text-indent: -9999px;
      margin: 0 auto;
    }
    .menu li a {
      color: #333;
      font-weight: bold;
      font-size: 1rem;
      text-decoration: none;
      padding: 0.5em;
    }
    .menu li a:hover {
      color: #4caf50;
    }

    main {
      flex: 1;
      padding: 1rem;
      max-width: 1000px;
      margin: 0 auto;
      width: 100%;
    }

    h2 {
      text-align: center;
      font-size: 2rem;
      margin: 1.5rem 0 1rem 0;
      color: #333;
    }

    .carrito-wrapper {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      padding: 1rem;
      margin-bottom: 1rem;
      overflow-x: auto;
    }

    .carrito-table {
      width: 100%;
      min-width: 600px;
      border-collapse: collapse;
      margin-bottom: 0;
    }

    .carrito-table th, .carrito-table td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #eee;
      background: #fff;
      font-size: 1rem;
    }

    .carrito-table th {
      background: #f8f8f8;
      color: #333;
      position: sticky;
      top: 0;
      z-index: 1;
    }

    .producto-detalle {
      display: flex;
      align-items: center;
      gap: 10px;
      justify-content: center;
      min-width: 120px;
    }
    .carrito-img {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 8px;
    }

    .cantidad-form, .delete-form {
      display: inline-block;
      margin: 0;
    }
    .cantidad-input {
      width: 50px;
      text-align: center;
      font-size: 1rem;
      border-radius: 4px;
      border: 1px solid #bbb;
      background: #fafafa;
      padding: 3px;
    }
    .resumen-footer {
      text-align: right;
      margin-top: 1.5rem;
    }
    .boton-comprar {
      background-color: #4caf50;
      color: white;
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      font-size: 1.1rem;
      margin-top: 1em;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    .boton-comprar:hover {
      background-color: #388e3c;
    }

    footer {
      background-color: #333;
      color: #fff;
      text-align: center;
      padding: 20px 10px;
      margin-top: auto;
    }
    .footer-content h3 {
      margin-bottom: 10px;
      font-size: 1.2rem;
    }
    .social-links a {
      color: #fff;
      margin: 0 10px;
      font-size: 1.5rem;
      text-decoration: none;
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

    /* RESPONSIVE */
    @media (max-width: 900px) {
      main { max-width: 99vw; padding: 0.6rem; }
      .carrito-table { min-width: 420px; font-size: 0.98rem;}
      .menu { flex-wrap: wrap; }
    }
    @media (max-width: 600px) {
      main { padding: 0.2rem; }
      .carrito-table, .carrito-wrapper { min-width: unset; width: 100%; }
      .carrito-wrapper { padding: 3vw 1vw; }
      .carrito-table th, .carrito-table td { padding: 7px 2px; font-size: 0.92rem; }
      .producto-detalle { flex-direction: column; gap: 2px; min-width: 60px; }
      .carrito-img { width: 38px; height: 38px; }
      .boton-comprar { width: 100%; margin-top: 10px;}
    }
    @media (max-width: 480px) {
      main { max-width: 100vw; }
      .carrito-wrapper { padding: 3vw 0.5vw; border-radius: 0; box-shadow: none;}
      .carrito-table th, .carrito-table td { padding: 4px 1px; font-size: 0.86rem; }
      .carrito-table th { font-size: 0.95rem;}
      .boton-comprar { font-size: 1rem;}
      .menu { flex-direction: column; gap: 5px;}
      .menu li.logo a { width: 90px; height: 38px;}
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

  <main>
    <h2>Mi Carrito</h2>

    <?php if (empty($carrito)): ?>
      <p style="text-align:center; margin-top:2em;">Tu carrito está vacío.</p>
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
              $fila   = $stmt->fetch(PDO::FETCH_ASSOC);
              $imgRuta= $fila['Imagen_URL'] ?? 'img/Productos/default.jpg';
              $subtotal = $producto['precio'] * $producto['cantidad'];
              $total   += $subtotal;
            ?>
            <tr>
              <td>
                <div class="producto-detalle">
                  <img src="<?= htmlspecialchars($imgRuta) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="carrito-img">
                  <?= htmlspecialchars($producto['nombre']) ?>
                </div>
              </td>
              <td>$<?= number_format($producto['precio'],0,',','.') ?></td>
              <td>
                <form method="POST" action="carrito.php" class="cantidad-form">
                  <input type="hidden" name="update_index" value="<?= $index ?>">
                  <input type="number"
                         name="cantidad"
                         value="<?= $producto['cantidad'] ?>"
                         min="1" max="25"
                         class="cantidad-input"
                         onchange="this.form.submit()">
                </form>
              </td>
              <td>$<?= number_format($subtotal,0,',','.') ?></td>
              <td>
                <form method="POST" action="carrito.php" class="delete-form" onsubmit="return confirm('¿Eliminar este producto?');">
                  <input type="hidden" name="delete_index" value="<?= $index ?>">
                  <button type="submit">Eliminar</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" style="text-align:right;">Total:</th>
              <th colspan="2">$<?= number_format($total,0,',','.') ?></th>
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
