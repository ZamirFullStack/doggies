<?php
session_start();

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
    die("❌ Error de conexión: " . $e->getMessage());
}

$resumenPedido = null;
$resumenDireccion = null;
$total = 0;
$cliente_nombre = '';

if (!empty($_SESSION['carrito'])) {
    $resumenPedido = $_SESSION['carrito'];
}

if (!empty($_SESSION['cliente'])) {
    $cli = $_SESSION['cliente'];
    $resumenDireccion = "{$cli['direccion']}, {$cli['barrio']}, {$cli['ciudad']}, {$cli['departamento']}";
    $cliente_nombre = $cli['nombre'] ?? '';
} else {
    $resumenDireccion = '';
    $cliente_nombre = '';
}

// Limpiar sesión después de mostrar resumen
$limpiarSesion = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pago Exitoso</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap"/>
  <style>
    body {
      text-align: center;
      padding: 2em 0.5em;
      background-color: #e8f5e9;
      font-family: 'Roboto', sans-serif;
      min-height: 100vh;
      margin: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .box {
      background: #fff;
      padding: 2em;
      border-radius: 10px;
      max-width: 600px;
      margin: 2em auto 0 auto;
      box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    h1 {
      color: #2e7d32;
      font-size: 2rem;
      margin-top: 0;
    }
    p {
      margin-top: 1em;
      color: #444;
    }
    a {
      display: inline-block;
      margin-top: 2em;
      color: #fff;
      background-color: #28a745;
      padding: 0.8em 1.5em;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      font-size: 1.12rem;
      transition: background 0.19s;
    }
    a:hover {
      background: #23923c;
    }
    .resumen-box {
      margin: 2em auto 1em auto;
      background: #fafafa;
      border-radius: 8px;
      box-shadow: 0 1px 5px #e0e0e0;
      padding: 1.2em 1em;
      max-width: 540px;
      overflow-x: auto;
    }
    .resumen-box h3 {
      color: #27ae60;
      margin-bottom: .5em;
      font-size: 1.15rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: .7em;
      font-size: 1rem;
    }
    th, td {
      padding: 10px 7px;
      border: 1px solid #ddd;
      text-align: left;
      vertical-align: middle;
    }
    th {
      background: #e0f2f1;
      text-align: center;
      font-size: 1.01rem;
      font-weight: 700;
    }
    tfoot td {
      font-weight: bold;
      background: #f3f3f3;
    }
    .direccion {
      margin-top: 1em;
      color: #388e3c;
      font-size: 1.04rem;
      word-break: break-word;
    }
    .prod-img {
      width: 38px;
      height: 38px;
      object-fit: cover;
      border-radius: 6px;
      margin-right: 8px;
      vertical-align: middle;
    }
    .prod-cell {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Responsive Styles */
    @media (max-width: 600px) {
      .box {
        max-width: 99vw;
        padding: 1em 0.4em;
        margin: 1em auto 0 auto;
      }
      .resumen-box {
        padding: 0.7em 0.3em;
        max-width: 98vw;
      }
      table, th, td {
        font-size: 0.95rem;
      }
      th, td {
        padding: 7px 2px;
      }
      h1 {
        font-size: 1.25rem;
      }
      a {
        font-size: 1rem;
        width: 95vw;
        box-sizing: border-box;
      }
      .prod-img { width: 30px; height: 30px; }
      .direccion { font-size: .97rem; }
    }

    @media (max-width: 400px) {
      .box { padding: 0.8em 0.1em; }
      .resumen-box { padding: 0.5em 0.1em; }
      th, td { font-size: 0.90rem; }
    }
  </style>
</head>
<body>
  <div class="box">
    <h1>✅ ¡Gracias por tu compra<?= $cliente_nombre ? ", $cliente_nombre" : "" ?>!</h1>
    <p>Tu pago fue procesado exitosamente.</p>

    <?php if ($resumenPedido): ?>
    <div class="resumen-box">
      <h3>Resumen de tu pedido</h3>
      <table>
        <thead>
          <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($resumenPedido as $producto): 
            // Buscar imagen
            $stmt = $pdo->prepare("SELECT Imagen_URL FROM producto WHERE Nombre = ?");
            $stmt->execute([$producto['nombre']]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $imgRuta = ($fila && !empty($fila['Imagen_URL'])) ? $fila['Imagen_URL'] : 'img/Productos/default.jpg';

            $subtotal = $producto['precio'] * $producto['cantidad'];
            $total += $subtotal;
        ?>
          <tr>
            <td class="prod-cell">
                <img src="<?= htmlspecialchars($imgRuta) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="prod-img">
                <?= htmlspecialchars($producto['nombre']) ?>
            </td>
            <td style="text-align:center;"><?= $producto['cantidad'] ?></td>
            <td>$ <?= number_format($subtotal, 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2" style="text-align:right;">Total:</td>
            <td><b>$ <?= number_format($total, 0, ',', '.') ?></b></td>
          </tr>
        </tfoot>
      </table>
      <?php if ($resumenDireccion): ?>
        <div class="direccion"><b>Enviado a:</b> <?= htmlspecialchars($resumenDireccion) ?></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <a href="index.php">Volver al inicio</a>
  </div>
</body>
</html>
<?php
// Limpia la sesión DESPUÉS de mostrar el resumen
if ($limpiarSesion) {
    unset($_SESSION['carrito']);
    unset($_SESSION['cliente']);
    unset($_SESSION['pedido_id']);
}
?>
