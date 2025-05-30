<?php
require __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

// Configura tu access token real o de prueba
MercadoPagoConfig::setAccessToken("TEST-7533043630493954-052015-7926e661894c7b075e8d779a3c67e94d-822556558");

// Conexión a la BD (Railway)
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
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

// Recibir JSON
header('Content-Type: application/json');
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "No se enviaron datos válidos"]);
    exit;
}

$cliente = $input['cliente'] ?? [];
$carrito = $input['carrito'] ?? [];

if (empty($cliente) || empty($carrito)) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos de cliente o carrito"]);
    exit;
}

$items = [];
$total = 0;

foreach ($carrito as $producto) {
    $nombre = $producto['nombre'] ?? 'Producto sin nombre';
    $precio = floatval($producto['precio'] ?? 0);
    $cantidad = intval($producto['cantidad'] ?? 1);

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
    http_response_code(400);
    echo json_encode(["error" => "No hay productos válidos"]);
    exit;
}

// Guardar pedido en BD como "pendiente"
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
    http_response_code(500);
    echo json_encode(["error" => "Error al guardar pedido: " . $e->getMessage()]);
    exit;
}

// Crear preferencia Mercado Pago
$client = new PreferenceClient();

try {
    $preference = $client->create([
        "items" => $items,
        "payer" => [
            "name" => $cliente['nombre'] ?? 'Cliente',
            "email" => $cliente['email'] ?? 'cliente@test.com'
        ],
        "back_urls" => [
            "success" => "https://doggies-production.up.railway.app/pago_exitoso.php",
            "failure" => "https://doggies-production.up.railway.app/pago_fallido.php",
            "pending" => "https://doggies-production.up.railway.app/pago_pendiente.php"
        ],
        "auto_return" => "approved",
        "external_reference" => $pedidoId
    ]);

    echo json_encode(["preference_id" => $preference->id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al crear preferencia: " . $e->getMessage()]);
}
?>
