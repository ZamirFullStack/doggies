<?php
session_start();
header('Content-Type: application/json');

define('ENVIA_TOKEN', '360e0f5db868461617b52260fd2b45ced6ffee5f65f3795254ce89764ea06cb5');

// --- Validación básica de carrito y datos ---
if (empty($_SESSION['carrito'])) {
    echo json_encode(['ok'=>false, 'error'=>'Carrito vacío']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (
    empty($data['departamento_destino']) ||
    empty($data['ciudad_destino'])
) {
    echo json_encode(['ok'=>false, 'error'=>'Departamento o ciudad no recibidos']);
    exit;
}

$departamento_destino = $data['departamento_destino'];
$ciudad_destino = $data['ciudad_destino'];

// --- (Opcional) Mapeo de nombres de ciudad/depto si lo usas
if (isset($fix_departamentos[$departamento_destino])) {
    $departamento_destino = $fix_departamentos[$departamento_destino];
}
if (isset($fix_ciudades[$ciudad_destino])) {
    $ciudad_destino = $fix_ciudades[$ciudad_destino];
}

// --- Conexión a BD ---
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
    echo json_encode(['ok'=>false, 'error'=>'Error de conexión']);
    exit;
}

// --- Armar paquetes desde el carrito ---
$peso_total = 0;
$alto_max = $ancho_max = $largo_max = 0;
$productos_envio = [];

foreach ($_SESSION['carrito'] as $item) {
    if (empty($item['presentacion'])) continue;
    $stmt = $pdo->prepare(
        "SELECT p.Nombre, p.alto_cm AS Alto, p.ancho_cm AS Ancho, p.largo_cm AS Largo, pr.Peso
         FROM producto p
         JOIN presentacion pr ON p.ID_Producto = pr.ID_Producto
         WHERE pr.ID_Presentacion = ? LIMIT 1"
    );
    $stmt->execute([$item['presentacion']]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$prod) continue;

    $peso     = floatval($prod['Peso']);
    $alto     = floatval($prod['Alto']);
    $ancho    = floatval($prod['Ancho']);
    $largo    = floatval($prod['Largo']);
    $cantidad = intval($item['cantidad']);

    $productos_envio[] = [
        "height" => $alto,
        "width"  => $ancho,
        "length" => $largo,
        "weight" => $peso,
        "quantity" => $cantidad,
        "description" => $prod['Nombre']
    ];

    $peso_total += $peso * $cantidad;
    $alto_max   = max($alto_max, $alto);
    $ancho_max  = max($ancho_max, $ancho);
    $largo_max  = max($largo_max, $largo);
}

if ($peso_total == 0 || empty($productos_envio)) {
    echo json_encode(['ok'=>false, 'error'=>'No se pudo calcular peso de los productos']);
    exit;
}

// --- Empaquetar para la API ---
$paquetes = [];
foreach ($productos_envio as $prod) {
    for ($i = 0; $i < $prod['quantity']; $i++) {
        $paquetes[] = [
            "content" => $prod['description'],
            "amount"  => 1,
            "type"    => "box",
            "weight"  => $prod['weight'],
            "width"   => $prod['width'],
            "height"  => $prod['height'],
            "length"  => $prod['length'],
        ];
    }
}
$direccion = !empty($data['direccion']) ? $data['direccion'] : $ciudad_destino;
$payload = [
    "origin" => [
        "country" => "CO",
        "state" => "Cundinamarca",
        "city" => "Bogotá",
        "address" => "Carrera 80 2-51, Corabastos, Bodega 16, Local 4, Bogotá, Cundinamarca"
    ],
    "destination" => [
        "country" => "CO",
        "state" => $departamento_destino,
        "city" => $ciudad_destino,
        "address" => $direccion
    ],
    "packages" => $paquetes
];

// --- Enviar solicitud a Envía ---
$token = ENVIA_TOKEN;
$ch = curl_init('https://api.envia.com/ship/rate/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
if ($response === false) {
    echo json_encode(['ok'=>false, 'error'=>'No se pudo conectar a la API de Envía']);
    exit;
}

curl_close($ch);

$res = json_decode($response, true);

// --- Procesar respuesta buscando interrapidisimo ---
$precio_envio = null;
$carrier = null;
$delivery = '';
if (!empty($res['data']) && is_array($res['data'])) {
    foreach ($res['data'] as $servicio) {
        if (
            isset($servicio['carrier']) && strtolower($servicio['carrier']) === 'interrapidisimo'
            && isset($servicio['totalPrice'])
        ) {
            $precio_envio = floatval($servicio['totalPrice']);
            $carrier = $servicio['carrierDescription'] ?? $servicio['carrier'];
            $delivery = $servicio['deliveryEstimate'] ?? '';
            break;
        }
    }
}

file_put_contents('debug_envia_checkout.txt', json_encode($res, JSON_PRETTY_PRINT));

if (!$precio_envio) {
    echo json_encode([
        'ok'=>false,
        'error'=>'No se obtuvo precio de ningún carrier',
        'api_response'=>$res
    ]);
    exit;
}

// --- Responder al frontend ---
echo json_encode([
    'ok'      => true,
    'precio'  => intval(round($precio_envio)),
    'carrier' => $carrier,
    'delivery' => $delivery,
    'peso'    => $peso_total,
    'ciudad'  => $ciudad_destino,
    'departamento' => $departamento_destino
]);
exit;
?>
