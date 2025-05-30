<?php
// mp_webhook.php

require __DIR__ . '/vendor/autoload.php';
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configura tu token real aquí
MercadoPagoConfig::setAccessToken("TEST-7533043630493954-052015-7926e661894c7b075e8d779a3c67e94d-822556558"); // Cambia por tu token real

// Recibe el JSON de Mercado Pago
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Solo procesamos eventos de pago (payment)
if (!isset($data['type']) || $data['type'] !== 'payment') {
    http_response_code(200);
    exit('No es un pago');
}

$payment_id = $data['data']['id'] ?? null;
if (!$payment_id) {
    http_response_code(200);
    exit('Sin ID de pago');
}

// Consultar el pago para confirmar datos reales
$client = new PaymentClient();
try {
    $payment = $client->get($payment_id);
} catch (Exception $e) {
    http_response_code(500);
    exit('Error consultando pago');
}

// Solo actuar si fue aprobado
if ($payment->status !== 'approved') {
    http_response_code(200);
    exit('No aprobado');
}

$pedidoId = $payment->external_reference;

// Conecta a la BD (ajusta los datos según tu entorno)
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
    exit("Error de conexión BD");
}

// Actualiza el estado a "pagado" si no lo está ya
$stmt = $pdo->prepare("UPDATE pedido SET Estado = 'pagado' WHERE ID_Pedido = ?");
$stmt->execute([$pedidoId]);

// Trae datos del cliente y productos para el correo
$stmt = $pdo->prepare("SELECT * FROM pedido WHERE ID_Pedido = ?");
$stmt->execute([$pedidoId]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM pedido_productos WHERE ID_Pedido = ?");
$stmt->execute([$pedidoId]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traer datos del cliente asociados al pedido (si tienes la tabla usuario, ajusta esto si lo necesitas)
$cliente_nombre = '';
$cliente_email = '';
$direccion = $pedido['Direccion_Entrega'] ?? '';
$departamento = $pedido['Departamento'] ?? '';
$ciudad = $pedido['Ciudad'] ?? '';
// Si guardas email/nombre en el pedido pon aquí la columna, si no, busca el usuario por ID_Usuario

if (!empty($pedido['ID_Usuario'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE ID_Usuario = ?");
    $stmt->execute([$pedido['ID_Usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) {
        $cliente_nombre = $usuario['Nombre'] ?? '';
        $cliente_email  = $usuario['Email'] ?? '';
    }
}

// Si no tienes usuario, y guardas el email en el pedido, usa eso:
if (empty($cliente_email) && !empty($pedido['Email'])) $cliente_email = $pedido['Email'];

// Construye el resumen bonito
$total = 0;
$productosHtml = '';
foreach ($productos as $producto) {
    $nombre = $producto['Nombre_Producto'];
    $cantidad = $producto['Cantidad'];
    $precio = $producto['Precio_Unitario'];
    $subtotal = $precio * $cantidad;
    $total += $subtotal;

    // Busca la imagen
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

$body = "
<div style='font-family:Arial,sans-serif; max-width:540px; margin:auto; border:1px solid #eee; border-radius:8px; box-shadow:0 0 10px #eee;'>
    <div style='background:#39c5e0; color:#fff; padding:16px 0; border-radius:8px 8px 0 0; text-align:center;'>
        <h2>Resumen de tu compra en Doggies 🐾</h2>
    </div>
    <div style='padding:24px; background:#fff;'>
        <p>$saludo</p>
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

// Envía el email solo si hay email
if ($cliente_email) {
    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'doggiespasto22@gmail.com'; // Cambia por el tuyo
        $mail->Password = 'nfav ibzv txxd wvwl';     // App Password Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('doggiespasto22@gmail.com', 'Doggies');
        $mail->addAddress($cliente_email, $cliente_nombre);

        $mail->Subject = mb_encode_mimeheader('Resumen de tu compra en Doggies 🐾', 'UTF-8');
        $mail->isHTML(true);
        $mail->Body    = $body;
        $mail->AltBody = "Gracias por tu compra en Doggies. Total: $".number_format($total, 0, ',', '.')."\nDirección: $direccion, $ciudad, $departamento";

        $mail->send();
    } catch (Exception $e) {
        // Loguea si quieres
    }
}

http_response_code(200);
echo "OK";
?>
