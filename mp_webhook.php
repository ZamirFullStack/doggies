<?php
// mp_webhook.php

require __DIR__ . '/vendor/autoload.php';
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Access Token Mercado Pago
MercadoPagoConfig::setAccessToken("APP_USR-7533043630493954-052015-54d6e694f724d08327f9bcf85aa2a2e9-822556558");

// Recibe el JSON de Mercado Pago
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Solo procesar tipo payment
if (!isset($data['type']) || $data['type'] !== 'payment') {
    http_response_code(200);
    exit('No es un pago');
}

$payment_id = $data['data']['id'] ?? null;
if (!$payment_id) {
    http_response_code(200);
    exit('Sin ID de pago');
}

// Consultar estado real del pago
$client = new PaymentClient();
try {
    $payment = $client->get($payment_id);
} catch (Exception $e) {
    file_put_contents('/tmp/webhook_error.log', "[Consulta MP] ".$e->getMessage()."\n", FILE_APPEND);
    http_response_code(500);
    exit('Error consultando pago');
}

if ($payment->status !== 'approved') {
    http_response_code(200);
    exit('No aprobado');
}

$pedidoId = $payment->external_reference;

// Conexión a la BD
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
    file_put_contents('/tmp/webhook_error.log', "[BD] ".$e->getMessage()."\n", FILE_APPEND);
    http_response_code(500);
    exit("Error de conexión BD");
}

// Actualiza el estado y la fecha de pago
$stmt = $pdo->prepare("UPDATE pedido SET Estado = 'pagado', Fecha_Pago = NOW() WHERE ID_Pedido = ?");
$stmt->execute([$pedidoId]);

// Datos del pedido y productos
$stmt = $pdo->prepare("SELECT * FROM pedido WHERE ID_Pedido = ?");
$stmt->execute([$pedidoId]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM pedido_productos WHERE ID_Pedido = ?");
$stmt->execute([$pedidoId]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extrae nombre/email del pedido o usuario
$cliente_nombre = $pedido['Nombre'] ?? '';
$cliente_email = $pedido['Email'] ?? '';
$direccion = $pedido['Direccion_Entrega'] ?? '';
$departamento = $pedido['Departamento'] ?? '';
$ciudad = $pedido['Ciudad'] ?? '';

// Busca nombre/email si hay usuario vinculado y no están ya en el pedido
if (empty($cliente_email) && !empty($pedido['ID_Usuario'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE ID_Usuario = ?");
    $stmt->execute([$pedido['ID_Usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) {
        if (!$cliente_nombre && !empty($usuario['Nombre'])) $cliente_nombre = $usuario['Nombre'];
        if (!$cliente_email  && !empty($usuario['Email']))  $cliente_email = $usuario['Email'];
    }
}

if (!$cliente_email) {
    // No hay email, responde OK y no reintenta
    http_response_code(200);
    exit("No hay email en el pedido");
}

// FECHA BONITA en español
$fechaPago = $pedido['Fecha_Pago'] ?? '';
$fechaBonita = '';
if ($fechaPago) {
    $meses = [
        '01'=>'enero', '02'=>'febrero', '03'=>'marzo', '04'=>'abril', '05'=>'mayo', '06'=>'junio',
        '07'=>'julio', '08'=>'agosto', '09'=>'septiembre', '10'=>'octubre', '11'=>'noviembre', '12'=>'diciembre'
    ];
    $dt = strtotime($fechaPago);
    $fechaBonita = date('d', $dt) . ' de ' . $meses[date('m', $dt)] . ' de ' . date('Y', $dt) . ', ' . date('H:i', $dt);
}

// Construir resumen bonito
$total = 0;
$productosHtml = '';
foreach ($productos as $producto) {
    $nombre = $producto['Nombre_Producto'];
    $cantidad = $producto['Cantidad'];
    $precio = $producto['Precio_Unitario'];
    $subtotal = $precio * $cantidad;
    $total += $subtotal;

    $stmtImg = $pdo->prepare("SELECT Imagen_URL FROM producto WHERE Nombre = ?");
    $stmtImg->execute([$nombre]);
    $fila = $stmtImg->fetch(PDO::FETCH_ASSOC);
    $imgRuta = ($fila && !empty($fila['Imagen_URL'])) ? $fila['Imagen_URL'] : 'img/Productos/default.jpg';

    $productosHtml .= "
        <tr>
            <td style='padding:8px; border:1px solid #ddd; display:flex; align-items:center; gap:8px;'>
                <img src='https://doggies-production.up.railway.app/$imgRuta' alt='' style='width:40px;height:40px;object-fit:cover;border-radius:6px;margin-right:8px;'>
                <span style='vertical-align:middle;'>".htmlspecialchars($nombre)."</span>
            </td>
            <td style='padding:8px; border:1px solid #ddd; text-align:center;'>$cantidad</td>
            <td style='padding:8px; border:1px solid #ddd;'>$ ".number_format($subtotal, 0, ',', '.')."</td>
        </tr>
    ";
}

$saludo = $cliente_nombre ? "¡Hola <b>$cliente_nombre</b>!" : "¡Hola y gracias por confiar en Doggies!";

// Email con fecha bonita incluida
$body = "
<div style='font-family:Arial,sans-serif; max-width:540px; margin:auto; border:1px solid #eee; border-radius:8px; box-shadow:0 0 10px #eee;'>
    <div style='background:#39c5e0; color:#fff; padding:16px 0; border-radius:8px 8px 0 0; text-align:center;'>
        <h2>Resumen de tu compra en Doggies 🐾</h2>
    </div>
    <div style='padding:24px; background:#fff;'>
        <p>$saludo</p>
        <p><b>Fecha de pago:</b> $fechaBonita</p>
        <p>Este es el resumen de tu pedido:</p>
        <table style='width:100%; border-collapse:collapse; margin-bottom:20px;'>
            <thead>
                <tr>
                    <th style='padding:8px; border:1px solid #ddd;'>Producto</th>
                    <th style='padding:8px; border:1px solid #ddd;'>Cantidad</th>
                    <th style='padding:8px; border:1px solid #ddd;'>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                $productosHtml
            </tbody>
        </table>
        <p style='font-size:18px;'><b>Total: $ ".number_format($total, 0, ',', '.')."</b></p>
        <p><b>Dirección de entrega:</b><br>$direccion, $ciudad, $departamento</p>
        <p>Te avisaremos cuando tu pedido sea despachado.<br>¡Gracias por tu compra!</p>
        <p><b>Doggies 🐾</b></p>
    </div>
</div>
";

// Enviar el correo
try {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'doggiespasto22@gmail.com';
    $mail->Password = 'nfav ibzv txxd wvwl';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('doggiespasto22@gmail.com', 'Doggies');
    $mail->addAddress($cliente_email, $cliente_nombre);

    $mail->Subject = mb_encode_mimeheader('Resumen de tu compra en Doggies 🐾', 'UTF-8');
    $mail->isHTML(true);
    $mail->Body    = $body;
    $mail->AltBody = "Gracias por tu compra en Doggies. Total: $".number_format($total, 0, ',', '.')."\nFecha: $fechaBonita\nDirección: $direccion, $ciudad, $departamento";

    $mail->send();
} catch (Exception $e) {
    file_put_contents('/tmp/webhook_error.log', "[Mailer] ".$e->getMessage()."\n", FILE_APPEND);
}

http_response_code(200);
echo "OK";
?>
