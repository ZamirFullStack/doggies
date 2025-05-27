<?php
session_start();
$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$metodo = $_POST['metodo_pago'] ?? '';

if ($nombre && $email && $direccion && $metodo) {
    // Aquí iría el procesamiento real
    $carrito = $_SESSION['carrito'] ?? [];
    $_SESSION['carrito'] = []; // Vaciar carrito después del pago
    echo "<h1>Gracias por tu compra, $nombre</h1>";
    echo "<p>Hemos recibido tu pedido y será entregado en <strong>$direccion</strong>.</p>";
    echo "<p>Se enviará confirmación a: $email</p>";
    echo "<p>Método de pago: <strong>$metodo</strong></p>";
} else {
    echo "<p>Faltan datos del cliente o forma de pago.</p>";
}
?>
<? 