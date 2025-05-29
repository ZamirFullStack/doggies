<?php
session_start();

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar eliminación de un producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_index'])) {
    $idx = intval($_POST['delete_index']);
    if (isset($_SESSION['carrito'][$idx])) {
        unset($_SESSION['carrito'][$idx]);
        // Reindexar el arreglo para no dejar huecos
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
    header('Location: carrito.php');
    exit;
}

// Procesar actualización de cantidad de un producto específico
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

// Conexión a la base de datos
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
  <link rel="stylesheet" href="css/carrito.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    html, body { height:100%; margin:0; display:flex; flex-direction:column; }
    main { flex:1; padding:1rem; max-width:1000px; margin:0 auto; }
    .carrito-wrapper { max-height:420px; overflow-y:auto; border:1px solid #ccc; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:1rem; }
    .carrito-img { width:60px; height:60px; object-fit:cover; margin-right:0.5rem; }
    .carrito-table { width:100%; border-collapse:collapse; min-width:600px; }
    .carrito-table th, .carrito-table td { padding:12px; text-align:center; border-bottom:1px solid #eee; background:#fff; }
    .carrito-table thead th { background:#f8f8f8; position:sticky; top:0; z-index:1; }
    .producto-detalle { display:flex; align-items:center; }
    .cantidad-form, .delete-form { display:inline-block; margin:0; }
    .cantidad-input { width:60px; text-align:center; }
    .resumen-footer { text-align:right; margin-top:1rem; }
    .boton-comprar { display:inline-block; padding:10px 20px; background:#4caf50; color:#fff; text-decoration:none; border-radius:6px; }
    .boton-comprar:hover { background:#45a049; }
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
    <h2 style="text-align: center;">Mi Carrito</h2>

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
