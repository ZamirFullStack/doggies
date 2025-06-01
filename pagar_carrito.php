<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['cliente'] = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellidos' => trim($_POST['apellidos'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'departamento' => trim($_POST['departamento'] ?? ''),
        'ciudad' => trim($_POST['ciudad'] ?? ''),
        'barrio' => trim($_POST['barrio'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'tipo_documento' => trim($_POST['tipo_documento'] ?? ''),
        'numero_documento' => trim($_POST['numero_documento'] ?? '')
    ];
}

if (empty($_SESSION['carrito'])) {
    echo "<h3>❌ Tu carrito está vacío.</h3>";
    exit;
}

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

require __DIR__ . '/vendor/autoload.php';
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

MercadoPagoConfig::setAccessToken("APP_USR-7533043630493954-052015-54d6e694f724d08327f9bcf85aa2a2e9-822556558");

$carrito = $_SESSION['carrito'];
$cliente = $_SESSION['cliente'] ?? [];
$usuarioId = $_SESSION['usuario_id'] ?? null;
$items = [];
$total = 0;

foreach ($carrito as $producto) {
    $nombre = $producto['nombre'] ?? 'Producto sin nombre';
    $precio = floatval($producto['precio'] ?? 0);
    $cantidad = intval($producto['cantidad'] ?? 0);

    if ($precio > 0 && $cantidad > 0) {
        $items[] = [
            "title" => $nombre,
            "quantity" => $cantidad,
            "unit_price" => $precio,
            "currency_id" => "COP"
        ];
        $total += $precio * $cantidad;
    }
}

if (empty($items)) {
    echo "<h3>❌ No hay productos válidos para procesar el pago.</h3>";
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO pedido 
    (ID_Usuario, Email, Nombre, Direccion_Entrega, Departamento, Ciudad, Metodo_Pago, Estado, Total)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        null,
        $cliente['email'] ?? '',
        $cliente['nombre'] ?? '',
        $cliente['direccion'] ?? 'Sin dirección',
        $cliente['departamento'] ?? '',
        $cliente['ciudad'] ?? '',
        'mercado_pago',
        'pendiente',
        $total
    ]);


    $pedidoId = $pdo->lastInsertId();
    $_SESSION['pedido_id'] = $pedidoId;

    $stmtDetalle = $pdo->prepare("INSERT INTO pedido_productos (ID_Pedido, Nombre_Producto, Cantidad, Precio_Unitario) VALUES (?, ?, ?, ?)");
    foreach ($carrito as $producto) {
        $stmtDetalle->execute([
            $pedidoId,
            $producto['nombre'],
            $producto['cantidad'],
            $producto['precio']
        ]);
    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    echo "<h3>❌ Error al registrar el pedido:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}

// -- SOLO GENERA LA PREFERENCIA --
$preferenceData = [
    "items" => $items,
    "external_reference" => $pedidoId, // <--- ASÍ EL WEBHOOK SABE QUÉ PEDIDO ES
    "payer" => [
        "name" => $cliente['nombre'] ?? 'Cliente',
        "email" => $cliente['email'] ?? 'cliente@test.com'
    ],
    "back_urls" => [
        "success" => "https://doggies-production.up.railway.app/pago_exitoso.php",
        "failure" => "https://doggies-production.up.railway.app/pago_fallido.php",
        "pending" => "https://doggies-production.up.railway.app/pago_pendiente.php"
    ],
    "auto_return" => "approved"
];

try {
    $client = new PreferenceClient();
    $preference = $client->create($preferenceData);
} catch (Exception $e) {
    echo "<h3>❌ Error al crear la preferencia de pago:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redirigiendo a Mercado Pago</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
    }
    .container {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      margin-bottom: 1rem;
    }
    .mp-button {
      margin-top: 1rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Redirigiendo a Mercado Pago...</h2>
    <p>Si no eres redirigido automáticamente, haz clic en el botón:</p>
    <div class="mp-button">
      <script src="https://www.mercadopago.com.co/integrations/v1/web-payment-checkout.js"
              data-preference-id="<?= htmlspecialchars($preference->id) ?>">
      </script>
    </div>
  </div>
  <script>
    setTimeout(function() {
      window.location.href = "<?= htmlspecialchars($preference->init_point) ?>";
    }, 2000); // Redirige en 2 segundos
  </script>
</body>
</html>
