<?php
session_start();
header('Content-Type: application/json');

// 1. TOKEN seguro (ponlo en variable de entorno o config en producción)
define('ENVIA_TOKEN', '360e0f5db868461617b52260fd2b45ced6ffee5f65f3795254ce89764ea06cb5'); // Cambia por tu token real

// 2. Mapeo de abreviaturas de departamentos
$depto_abreviaturas = [
    'Amazonas' => 'AM',  'Antioquia' => 'AN',  'Arauca' => 'AR',  'Atlántico' => 'AT',
    'Bogotá D.C.' => 'DC',  'Bolívar' => 'BL',  'Boyacá' => 'BY',  'Caldas' => 'CL',
    'Caquetá' => 'CA',  'Casanare' => 'CS',  'Cauca' => 'CU',  'Cesar' => 'CE',
    'Chocó' => 'CH',  'Córdoba' => 'CO',  'Cundinamarca' => 'CN',  'Guainía' => 'GI',
    'Guaviare' => 'GV',  'Huila' => 'HU',  'La Guajira' => 'LJ',  'Magdalena' => 'MG',
    'Meta' => 'ME',  'Nariño' => 'NO',  'Norte de Santander' => 'NS',  'Putumayo' => 'PM',
    'Quindío' => 'QD',  'Risaralda' => 'RS',  'San Andrés' => 'SA',  'Santander' => 'SN',
    'Sucre' => 'SU',  'Tolima' => 'TO',  'Valle del Cauca' => 'VC',  'Vaupés' => 'VA',  'Vichada' => 'VD'
];

// 3. Validación básica de carrito y datos recibidos
if (empty($_SESSION['carrito'])) {
    echo json_encode(['ok'=>false, 'error'=>'Carrito vacío']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (
    empty($data['departamento_destino']) ||
    empty($data['ciudad_destino']) ||
    empty($data['direccion']) ||
    empty($data['barrio']) ||
    empty($data['nombre'])
) {
    echo json_encode(['ok'=>false, 'error'=>'Faltan datos de envío']);
    exit;
}

$departamento = $data['departamento_destino'];
$ciudad = $data['ciudad_destino'];

// 4. Carga el JSON de códigos postales
$zipcodes = json_decode(file_get_contents('zipcodes.co.json'), true);

// 5. Busca el código postal y código DANE municipal y abreviatura departamento
$codigoPostal = null;
$codigoDaneMunicipio = null;
$abreviaturaDepto = null;

foreach ($zipcodes as $item) {
    if (
        mb_strtolower($item['state']) === mb_strtolower($departamento) &&
        mb_strtolower($item['place']) === mb_strtolower($ciudad)
    ) {
        $codigoPostal = $item['zipcode'];
        $codigoDaneMunicipio = $item['province_code'];
        $abreviaturaDepto = $item['admin_code'] ?? null;
        break;
    }
}

// Si no lo encontró, busca abreviatura por mapeo
if (!$abreviaturaDepto) {
    $abreviaturaDepto = $depto_abreviaturas[$departamento] ?? 'NO'; // Default Nariño
}
if (!$codigoPostal) $codigoPostal = '000000';
if (!$codigoDaneMunicipio) $codigoDaneMunicipio = '00000';
$codigoDaneCiudad = str_pad($codigoDaneMunicipio, 8, '0', STR_PAD_RIGHT); // Ej: 52001000

// 6. Armar los paquetes del carrito
$peso_total = 0;
$productos_envio = [];

file_put_contents("debug_carrito.log", print_r($_SESSION['carrito'], true));


foreach ($_SESSION['carrito'] as $item) {
    // Soporta ambos: alto_cm/ancho_cm/largo_cm o alto/ancho/largo
    $peso = isset($item['peso']) ? floatval($item['peso']) : null;
    $alto = isset($item['alto']) ? floatval($item['alto']) : (isset($item['alto_cm']) ? floatval($item['alto_cm']) : null);
    $ancho = isset($item['ancho']) ? floatval($item['ancho']) : (isset($item['ancho_cm']) ? floatval($item['ancho_cm']) : null);
    $largo = isset($item['largo']) ? floatval($item['largo']) : (isset($item['largo_cm']) ? floatval($item['largo_cm']) : null);
    $cantidad = isset($item['cantidad']) ? intval($item['cantidad']) : 1;

    // Validación estricta
    if (
        is_null($peso) || is_null($alto) || is_null($ancho) || is_null($largo) ||
        !is_numeric($peso) || !is_numeric($alto) || !is_numeric($ancho) || !is_numeric($largo) ||
        $peso <= 0 || $alto <= 0 || $ancho <= 0 || $largo <= 0
    ) {
        echo json_encode(['ok'=>false, 'error'=>'Faltan datos de peso o dimensiones en el carrito. Actualiza el producto.']);
        exit;
    }

    $productos_envio[] = [
        "content" => $item['nombre'],
        "amount" => $cantidad,
        "type" => "box",
        "weight" => $peso,
        "weightUnit" => "KG",
        "lengthUnit" => "CM",
        "dimensions" => [
            "length" => $largo,
            "width" => $ancho,
            "height" => $alto
        ]
    ];
    $peso_total += $peso * $cantidad;
}


if ($peso_total == 0 || empty($productos_envio)) {
    echo json_encode(['ok'=>false, 'error'=>'No se pudo calcular peso de los productos']);
    exit;
}

// 7. Armar el payload FINAL
$payload = [
    "origin" => [
        "name" => "Animal Home",
        "street" => "Carrera 80 2-51",
        "number" => "16",
        "district" => "Corabastos",
        "city" => "11001000", // Bogotá DANE
        "state" => "CN",      // Cundinamarca
        "country" => "CO",
        "postalCode" => "111001"
    ],
    "destination" => [
        "name" => $data['nombre'],
        "street" => $data['direccion'],
        "number" => $data['numero'] ?? "",
        "district" => $data['barrio'],
        "city" => $codigoDaneCiudad,     // DANE 8 dígitos
        "state" => $abreviaturaDepto,    // Abreviatura departamento
        "country" => "CO",
        "postalCode" => $codigoPostal
    ],
    "packages" => $productos_envio,
    "shipment" => [
        "carrier" => "coordinadora", // Solo cotiza Coordinadora
        "type" => 1
    ],
    "settings" => [
        "currency" => "COP"
    ]
];

// 8. Enviar solicitud a Envia
$token = '360e0f5db868461617b52260fd2b45ced6ffee5f65f3795254ce89764ea06cb5';
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
curl_close($ch);

if ($response === false) {
    echo json_encode(['ok'=>false, 'error'=>'No se pudo conectar a la API de Envía']);
    exit;
}

$res = json_decode($response, true);

// Procesa la respuesta y envía al frontend lo que quieras
if (!empty($res['meta']) && $res['meta'] === 'rate' && !empty($res['data'])) {
    // Devuelve la info relevante
    echo json_encode([
        'ok' => true,
        'data' => $res['data'],
        'peso' => $peso_total,
        'ciudad' => $ciudad,
        'departamento' => $departamento
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'error' => $res['error']['message'] ?? 'Error desconocido',
        'api_response' => $res
    ]);
}
exit;
?>
