<?php
session_start();

// Verificar si hay productos en el carrito
if (empty($_SESSION['carrito'])) {
    echo "❌ Tu carrito está vacío.";
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

// Configura tu token de acceso de MercadoPago
MercadoPagoConfig::setAccessToken("TEST-7533043630493954-052015-7926e661894c7b075e8d779a3c67e94d-822556558");

$carrito = $_SESSION['carrito'];
$items = [];

// Construir el array de productos
foreach ($carrito as $producto) {
    $nombre = $producto['nombre'] ?? 'Producto sin nombre';
    $precio = floatval($producto['precio']);
    $cantidad = intval($producto['cantidad']);

    if ($precio > 0 && $cantidad > 0) {
        $items[] = [
            "title" => $nombre,
            "quantity" => $cantidad,
            "unit_price" => $precio,
            "currency_id" => "COP"
        ];
    }
}

// Validar que haya ítems válidos
if (empty($items)) {
    echo "❌ No hay productos válidos para procesar el pago.";
    exit;
}

// Crear preferencia de pago
$preferenceData = [
    "items" => $items,
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
  <title>Redirigiendo a Mercado Pago</title>
</head>
<body>
  <h2>Redirigiendo a Mercado Pago...</h2>
  <script src="https://www.mercadopago.com.co/integrations/v1/web-payment-checkout.js"
          data-preference-id="<?= htmlspecialchars($preference->id) ?>">
  </script>
</body>
</html>
