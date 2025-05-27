<?php
require __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

// Access token de prueba
MercadoPagoConfig::setAccessToken("TEST-7533043630493954-052015-7926e661894c7b075e8d779a3c67e94d-822556558");

// Crear preferencia
$client = new PreferenceClient();

$preference = $client->create([
    "items" => [[
        "title" => "Collar para perro",
        "quantity" => 1,
        "unit_price" => 20000,
        "currency_id" => "COP"
    ]],
    "back_urls" => [
        "success" => "http://localhost/Doggies/pago_exitoso.php",
        "failure" => "http://localhost/Doggies/pago_fallido.php",
        "pending" => "http://localhost/Doggies/pago_pendiente.php"
    ],
    "auto_return" => "approved"
]);
?>

<!DOCTYPE html>
<html>
<head><title>Pago con Mercado Pago</title></head>
<body>
  <h1>Pago vía Checkout Pro</h1>

  <script src="https://www.mercadopago.com.co/integrations/v1/web-payment-checkout.js"
          data-preference-id="<?= $preference->id ?>">
  </script>
</body>
</html>
