<?php
session_start();

if (!isset($_SESSION['usuario']) || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

MercadoPagoConfig::setAccessToken("TEST-7533043630493954-052015-7926e661894c7b075e8d779a3c67e94d-822556558");

$carrito = $_SESSION['carrito'];
$items = [];

// Validar datos y construir items
foreach ($carrito as $producto) {
    $titulo = $producto['nombre'] ?? 'Producto sin nombre';
    $precio = floatval($producto['precio']);
    $cantidad = intval($producto['cantidad']);

    if ($precio <= 0 || $cantidad <= 0) continue;

    $items[] = [
        "title" => $titulo,
        "quantity" => $cantidad,
        "unit_price" => $precio,
        "currency_id" => "COP"
    ];
}

if (empty($items)) {
    echo "❌ No hay productos válidos para procesar el pago.";
    exit;
}

$preferenceData = [
    "items" => $items,
    "back_urls" => [
        "success" => "http://localhost/Doggies/pago_exitoso.php",
        "failure" => "http://localhost/Doggies/pago_fallido.php",
        "pending" => "http://localhost/Doggies/pago_pendiente.php"
    ],
    "auto_return" => "approved"
];

try {
    $client = new PreferenceClient();
    $preference = $client->create($preferenceData);
} catch (Exception $e) {
    echo "❌ Error al crear la preferencia: " . htmlspecialchars($e->getMessage());
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Redirigiendo a Mercado Pago</title></head>
<body>
  <h2>Redirigiendo a Mercado Pago...</h2>
  <script src="https://www.mercadopago.com.co/integrations/v1/web-payment-checkout.js"
          data-preference-id="<?= htmlspecialchars($preference->id) ?>">
  </script>
</body>
</html>
