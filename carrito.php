<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Configuración conexión PDO (adaptar según tu servidor y credenciales)
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

foreach ($_SESSION['carrito'] as &$item) {
    if (!isset($item['id_producto'])) $item['id_producto'] = 0;
    if (!isset($item['id_presentacion'])) $item['id_presentacion'] = 0;
    if (!isset($item['presentacion'])) $item['presentacion'] = '';
    if (!isset($item['cantidad'])) $item['cantidad'] = 1;
    if (!isset($item['precio'])) $item['precio'] = 0;
    if (!isset($item['imagen'])) $item['imagen'] = 'img/default.jpg';
}
unset($item);

$_SESSION['carrito'] = array_filter($_SESSION['carrito'], function($item) {
    return isset($item['id_producto']) && isset($item['id_presentacion']);
});
$_SESSION['carrito'] = array_values($_SESSION['carrito']); // reindexa



// Añadir producto al carrito desde POST
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['id_producto'], $_POST['id_presentacion'], $_POST['nombre'], $_POST['precio'], $_POST['cantidad'], $_POST['imagen'])
) {
    $idProducto = intval($_POST['id_producto']);
    $idPresentacion = intval($_POST['id_presentacion']);
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $cantidad = max(1, min(25, intval($_POST['cantidad'])));
    $imagen = trim($_POST['imagen']);

    // Obtener dimensiones desde DB
    $stmtDim = $pdo->prepare("
        SELECT p.Peso, pr.alto_cm AS Alto, pr.ancho_cm AS Ancho, pr.largo_cm AS Largo
        FROM presentacion p
        JOIN producto pr ON p.ID_Producto = pr.ID_Producto
        WHERE p.ID_Presentacion = ?
    ");
    $stmtDim->execute([$idPresentacion]);
    $dimensiones = $stmtDim->fetch(PDO::FETCH_ASSOC);

    $peso = $dimensiones['Peso'] ?? 0;
    $alto = $dimensiones['Alto'] ?? 0;
    $ancho = $dimensiones['Ancho'] ?? 0;
    $largo = $dimensiones['Largo'] ?? 0;


    $encontrado = false;

    foreach ($_SESSION['carrito'] as &$item) {
        if (
            isset($item['id_producto'], $item['id_presentacion']) &&
            $item['id_producto'] == $idProducto &&
            $item['id_presentacion'] == $idPresentacion
        ) {
            $item['cantidad'] = min(25, $item['cantidad'] + $cantidad);
            $encontrado = true;
            break;
        }
    }

    unset($item);

    // Ejemplo: obtener dimensiones y asignar
    $stmtProd = $pdo->prepare("SELECT alto_cm, largo_cm, ancho_cm FROM producto WHERE ID_Producto = ?");
    $stmtProd->execute([$idProducto]);  // Aquí usas $idProducto correcto
    $producto_dim = $stmtProd->fetch(PDO::FETCH_ASSOC);

    $alto  = floatval($producto_dim['alto_cm'] ?? 0);
    $largo = floatval($producto_dim['largo_cm'] ?? 0);
    $ancho = floatval($producto_dim['ancho_cm'] ?? 0);


    if (!$encontrado) {
        $_SESSION['carrito'][] = [
            'id_producto'    => $idProducto,
            'id_presentacion'=> $idPresentacion,
            'nombre'         => $nombre,
            'precio'         => $precio,
            'cantidad'       => $cantidad,
            'imagen'         => $imagen,
            'peso'           => $peso,
            'alto'           => $alto,
            'ancho'          => $ancho,
            'largo'          => $largo,
        ];
    }

    // Guardar log con dimensiones
    file_put_contents('debug_variables.log', date('Y-m-d H:i:s') . " - idProducto: $idProducto, idPresentacion: $idPresentacion, alto: $alto, ancho: $ancho, largo: $largo\n", FILE_APPEND);

    exit;
}


// Eliminar producto del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_index'])) {
    $idx = intval($_POST['delete_index']);
    if (isset($_SESSION['carrito'][$idx])) {
        unset($_SESSION['carrito'][$idx]);
        $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
    }
    header('Location: carrito.php');
    exit;
}

// Actualizar cantidad de producto en carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_index'], $_POST['cantidad'])) {
    $idx = intval($_POST['update_index']);
    $qty = max(1, min(25, intval($_POST['cantidad'])));
    if (isset($_SESSION['carrito'][$idx])) {
        $_SESSION['carrito'][$idx]['cantidad'] = $qty;
    }
    header('Location: carrito.php');
    exit;
}

// Obtener carrito para mostrar en HTML
$carrito = $_SESSION['carrito'];

foreach ($carrito as $item) {
  $peso = isset($item['peso']) ? $item['peso'] : '';
  $alto = isset($item['alto']) ? $item['alto'] : '';
  $ancho = isset($item['ancho']) ? $item['ancho'] : '';
  $largo = isset($item['largo']) ? $item['largo'] : '';

    // Aquí puedes hacer el cálculo de la cotización con la API de Envia usando estos valores
}

$total = 0;

file_put_contents('debug_carrito.log', date('Y-m-d H:i:s') . " - Carrito actual:\n" . print_r($carrito, true) . "\n\n", FILE_APPEND);


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
/* ==== RESETEO GENERAL ==== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Roboto', sans-serif;
  background-color: #f9f9f9;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.logo-img {
  height: 55px;         
  width: auto;
  max-width: 140px;     
  object-fit: contain;
  border-radius: 10px;
  display: block;
  margin: 0 6px;
  transition: transform 0.3s ease;
}
@media (max-width: 700px) {
  .logo-img {
    height: 42px;        
    max-width: 33vw;     
  }
}

/* ==== HEADER ==== */
header {
  background-color: #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.06);
  position: relative;
  z-index: 10;
}
.header-container {
  max-width: 900px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.7em;
  height: 70px;
  width: 100%;
  padding: 0 10px;
}
.menu-side {
  display: flex;
  align-items: center;
}
.menu-side.left {
  justify-content: flex-end;
  flex: 1 1 0%;
}
.menu-side.right {
  justify-content: flex-start;
  flex: 1 1 0%;
}
.logo-link {
  flex: 0 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}
.menu-link {
  color: #222;
  font-weight: bold;
  font-size: 1.04rem;
  text-decoration: none;
  padding: 0.5em 0.6em;
  transition: color 0.2s;
  white-space: nowrap;
  background: none;
  border: none;
}
.menu-link:hover {
  color: #28a745;
}
@media (max-width: 700px) {
  .header-container {
    height: 48px;
    gap: 0.15em;
    padding: 0 2px;
    max-width: 98vw;
  }
  .menu-link { font-size: 0.97rem; padding: 0.25em 0.3em; }
}

/* ==== MAIN / CONTENIDO ==== */
main {
  flex: 1 1 auto;
  padding: 1rem 0.5rem;
  max-width: 980px;
  margin: 0 auto;
  width: 100%;
}
h2 {
  text-align: center;
  font-size: 2rem;
  margin: 1.5rem 0 1rem 0;
  color: #333;
}

/* ==== CARD EMPTY CARRITO ==== */
.carrito-card-empty {
  max-width: 450px;
  margin: 3.3em auto 2.5em auto;
  background: #fff;
  box-shadow: 0 2px 14px rgba(0,0,0,0.10);
  border-radius: 15px;
  padding: 2.3em 1.3em 2.5em 1.3em;
  text-align: center;
  position: relative;
  border: 1px solid #eee;
  animation: cardFadeIn 1s;
}
@keyframes cardFadeIn {
  from { opacity: 0; transform: translateY(32px);}
  to   { opacity: 1; transform: none;}
}
.carrito-card-empty .icon-empty {
  font-size: 3.5em;
  color: #ffd600;
  margin-bottom: 12px;
  display: block;
}
.carrito-card-empty h3 {
  color: #333;
  font-size: 1.37em;
  margin-bottom: 9px;
  font-weight: 700;
  letter-spacing: 0.02em;
}
.carrito-card-empty p {
  color: #555;
  font-size: 1.11em;
  margin-bottom: 1.6em;
  line-height: 1.5;
}
.carrito-card-empty .acciones-empty {
  display: flex;
  gap: 1em;
  justify-content: center;
  flex-wrap: wrap;
}
.carrito-card-empty .btn-empty {
  background: #28a745;
  color: #fff;
  border: none;
  padding: 13px 30px;
  border-radius: 7px;
  font-size: 1.09em;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.17s;
  cursor: pointer;
  margin-bottom: 0.5em;
}
.carrito-card-empty .btn-empty:hover {
  background: #1b7d31;
}
.carrito-card-empty .btn-outline {
  background: #fff;
  color: #28a745;
  border: 2px solid #28a745;
  padding: 12px 27px;
  font-weight: 600;
}
.carrito-card-empty .btn-outline:hover {
  background: #28a745;
  color: #fff;
}

/* ==== CARRITO WRAPPER ==== */
.carrito-wrapper {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  padding: 1rem;
  margin-bottom: 1rem;
  overflow-x: auto;
}

/* ==== TABLA DESKTOP ==== */
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

/* ==== MOBILE CARD STYLE ==== */
.carrito-lista-movil {
  display: none;
}
@media (max-width: 520px) {
  .carrito-wrapper table.carrito-table { display: none; }
  .carrito-lista-movil {
    display: flex !important;
    flex-direction: column;
    gap: 15px;
    padding: 0 8px;
  }
  .carrito-item-movil {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 14px 16px 12px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 0.5em;
    position: relative;
  }
  .carrito-item-header {
    display: flex;
    gap: 10px;
    align-items: center;
  }
  .carrito-img {
    width: 50px !important;
    height: 50px !important;
    border-radius: 8px;
    object-fit: cover;
  }
  .carrito-nombre {
    font-weight: 700;
    font-size: 1.08rem;
    flex: 1 1 auto;
    white-space: normal;
    word-break: break-word;
  }
  .carrito-row-detalles {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    width: 100%;
    gap: 6px;
  }
  .carrito-detalles {
    display: flex;
    align-items: center;
    gap: 22px;
    flex: 1 1 auto;
  }
  .carrito-acciones {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    margin-left: auto;
  }
}

/* ==== BOTÓN ELIMINAR ==== */
.delete-form button {
  background-color: #f44336 !important;
  color: #fff !important;
  border: none !important;
  border-radius: 6px !important;
  padding: 7px 14px !important;
  font-size: 1rem !important;
  font-weight: 500 !important;
  cursor: pointer !important;
  transition: background-color 0.16s ease !important;
  min-width: 84px;
  text-align: center !important;
  margin-left: 12px;
}
.delete-form button:hover {
  background-color: #d32f2f !important;
}
@media (max-width: 520px) {
  .delete-form button {
    min-width: 64px;
    width: auto !important;
    padding: 7px 12px !important;
    font-size: 0.98rem !important;
    margin-top: 4px;
    margin-left: 12px;
  }
}

/* ==== BOTÓN COMPRAR CENTRADO ==== */
.resumen-footer {
  text-align: center;
  margin-top: 1.2rem;
}
.boton-comprar {
  background-color: #28a745;
  color: white;
  padding: 12px 38px;
  border: none;
  border-radius: 7px;
  text-decoration: none;
  font-weight: bold;
  font-size: 1.13rem;
  margin-top: 1em;
  cursor: pointer;
  transition: background-color 0.19s;
  box-shadow: 0 1px 3px rgba(40,167,69,0.10);
  display: inline-block;
  min-width: 210px;
  max-width: 90vw;
}
.boton-comprar:hover {
  background-color: #218838;
}
@media (max-width: 520px) {
  .boton-comprar {
    width: auto;
    min-width: 55vw;
    max-width: 95vw;
    margin: 0 auto;
    font-size: 1.05rem;
    padding: 13px 0;
    display: inline-block;
  }
  .resumen-footer { text-align: center; }
}

/* ==== RESPONSIVE TABLA ==== */
@media (max-width: 700px) {
  .carrito-wrapper { padding: 1vw 1vw; }
  .carrito-table { min-width: 400px; font-size: 0.97rem;}
  .producto-detalle { min-width: 70px; gap: 5px; }
  .carrito-img { width: 34px; height: 34px; }
  .carrito-table th, .carrito-table td { padding: 8px 4px; font-size: 0.94rem; }
}

/* ==== FOOTER: NO TOCAR SEGÚN INSTRUCCIÓN ==== */
/* ==== FOOTER ==== */
footer {
  background-color: #333;
  color: #fff;
  text-align: center;
  padding: 20px 10px 36px 10px;
  margin-top: auto;
  width: 100%;
  position: relative;
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
@media (max-width: 700px) {
  body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  main { flex: 1 1 auto; }
  footer {
    margin-top: auto;
    position: static;
    bottom: 0;
    left: 0;
    width: 100vw;
    min-width: 0;
  }
}

/* ==== MENÚ ==== */
.menu-side a.menu-link {
  color: #222;
  font-weight: bold;
  font-size: 1.04rem;
  text-decoration: none;
  padding: 0.5em 0.6em;
  transition: color 0.2s, background-color 0.3s ease;
  border-radius: 6px;
  white-space: nowrap;
}
.menu-side a.menu-link:hover {
  color: white;
  background-color: #28a745;
}

</style>
</head>
<body>
<header>
  <nav>
    <div class="header-container">
      <div class="menu-side left">
        <a href="Productos.php" class="menu-link"><i class="fas fa-dog"></i> Productos</a>
      </div>
      <a href="index.php" class="logo-link">
        <img src="img/fondo.jpg" alt="Doggies" class="logo-img">
      </a>
      <div class="menu-side right">
        <a href="Servicios.php" class="menu-link"><i class="fas fa-concierge-bell"></i> Servicios</a>
      </div>
    </div>
  </nav>
</header>

<main>
  <h2>Mi Carrito</h2>

  <?php if (empty($carrito)): ?>
    <div class="carrito-card-empty">
      <span class="icon-empty"><i class="fas fa-shopping-cart"></i></span>
      <h3>¡Tu carrito está vacío!</h3>
      <p>
        Agrega productos a tu carrito para verlos aquí y finalizar tu compra.<br>
        ¿Buscas productos para tu mascota? ¡Explora nuestra tienda!<br>
        También puedes revisar nuestros <b>servicios</b> personalizados.
      </p>
      <div class="acciones-empty">
        <a href="Productos.php" class="btn-empty"><i class="fas fa-dog"></i> Ver Productos</a>
        <a href="Servicios.php" class="btn-empty btn-outline"><i class="fas fa-concierge-bell"></i> Ver Servicios</a>
        <a href="index.php" class="btn-empty btn-outline"><i class="fas fa-home"></i> Inicio</a>
      </div>
    </div>
  <?php else: ?>
    <div class="carrito-wrapper">
      <!-- VERSIÓN DESKTOP (TABLA) -->
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
        <?php
        $total = 0;
        foreach ($carrito as $index => $producto):
          // Obtener imagen del producto
          $stmt = $pdo->prepare("SELECT Imagen_URL FROM producto WHERE Nombre = ?");
          $stmt->execute([$producto['nombre']]);
          $fila = $stmt->fetch(PDO::FETCH_ASSOC);

          $imgFile = trim($fila['Imagen_URL'] ?? '');
          $imgFile = ltrim($imgFile, '/');
          if (!$imgFile) {
              $imgRuta = "https://doggies-production.up.railway.app/img/default.jpg";
          } else if (filter_var($imgFile, FILTER_VALIDATE_URL)) {
              $imgRuta = $imgFile;
          } else if (strpos($imgFile, 'img/') === 0) {
              $imgRuta = "https://doggies-production.up.railway.app/" . $imgFile;
          } else {
              $imgRuta = "https://doggies-production.up.railway.app/img/" . $imgFile;
          }

          // Obtener peso real
          $peso = '';
          if (!empty($producto['presentacion'])) {
              $stmtPeso = $pdo->prepare("SELECT Peso FROM presentacion WHERE ID_Presentacion = ?");
              $stmtPeso->execute([$producto['presentacion']]);
              $filaPeso = $stmtPeso->fetch(PDO::FETCH_ASSOC);
              if ($filaPeso) {
                  $peso = $filaPeso['Peso'];
              }
          }


          $subtotal = $producto['precio'] * $producto['cantidad'];
          $total += $subtotal;
        ?>
        <tr>
          <td>
            <div class="producto-detalle" style="justify-content:flex-start;gap:16px;">
              <img 
                src="<?= htmlspecialchars($imgRuta) ?>" 
                alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                class="carrito-img"
                style="min-width:50px;min-height:50px;"
                onerror="this.onerror=null;this.src='https://doggies-production.up.railway.app/img/default.jpg';" >
              <span style="font-size:1.08rem;">
                <?= htmlspecialchars($producto['nombre'] . ($peso ? ' (' . $peso . ')' : '')) ?>
              </span>
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
              <button type="submit"><i class="fas fa-trash"></i> Eliminar</button>
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
      <!-- VERSIÓN MÓVIL (CARDS) -->
      <div class="carrito-lista-movil" style="display:none;">
        <?php foreach ($carrito as $index => $producto):
            // Obtener imagen
            $stmt = $pdo->prepare("SELECT Imagen_URL FROM producto WHERE Nombre = ?");
            $stmt->execute([$producto['nombre']]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            $imgFile = trim($fila['Imagen_URL'] ?? '');
            $imgFile = ltrim($imgFile, '/');
            if (!$imgFile) {
                $imgRuta = "https://doggies-production.up.railway.app/img/default.jpg";
            } else if (filter_var($imgFile, FILTER_VALIDATE_URL)) {
                $imgRuta = $imgFile;
            } else if (strpos($imgFile, 'img/') === 0) {
                $imgRuta = "https://doggies-production.up.railway.app/" . $imgFile;
            } else {
                $imgRuta = "https://doggies-production.up.railway.app/img/" . $imgFile;
            }

            // Obtener peso real
            $peso = '';
            if (!empty($producto['presentacion'])) {
                $stmtPeso = $pdo->prepare("SELECT Peso FROM presentacion WHERE ID_Presentacion = ?");
                $stmtPeso->execute([$producto['presentacion']]);
                $filaPeso = $stmtPeso->fetch(PDO::FETCH_ASSOC);
                if ($filaPeso) {
                    $peso = $filaPeso['Peso'];
                }
            }

            $subtotal = $producto['precio'] * $producto['cantidad'];
        ?>
      <div class="carrito-item-movil">
        <div class="carrito-item-header">
          <img src="<?= htmlspecialchars($imgRuta) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="carrito-img" />
          <div class="carrito-nombre"><?= htmlspecialchars($producto['nombre'] . ($peso ? ' (' . $peso . ')' : '')) ?></div>
        </div>
        <div class="carrito-detalles">
          <span>Precio: $<?= number_format($producto['precio'],0,',','.') ?></span>
          <span>Subtotal: $<?= number_format($subtotal,0,',','.') ?></span>
          <form method="POST" action="carrito.php" class="cantidad-form" style="flex-shrink:0;">
            <label style="margin-right:3px;">Cantidad:</label>
            <input type="hidden" name="update_index" value="<?= $index ?>">
            <input type="number"
                  name="cantidad"
                  value="<?= $producto['cantidad'] ?>"
                  min="1" max="25"
                  class="cantidad-input"
                  onchange="this.form.submit()">
            </form>
          </div>
          <div class="carrito-acciones">
            <form method="POST" action="carrito.php" class="delete-form" onsubmit="return confirm('¿Eliminar este producto?');">
              <input type="hidden" name="delete_index" value="<?= $index ?>">
              <button type="submit"><i class="fas fa-trash"></i> Eliminar</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
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
      <a href="mailto:doggiespasto22@gmail.com"><i class="fas fa-envelope"></i></a>
    </div>
  </div>
</footer>
</body>
</html>
