<?php
session_start();

if (!isset($_POST['nombre'], $_POST['precio'], $_POST['cantidad'])) {
    die("❌ Datos incompletos.");
}

$nombre = $_POST['nombre'];
$precio = floatval($_POST['precio']);
$cantidad = max(1, intval($_POST['cantidad'])); // mínimo 1

$item = [
    'nombre' => $nombre,
    'precio' => $precio,
    'cantidad' => $cantidad,
    'imagen' => $imagen,
    'presentacion' => $presentacion,
    'peso' => $_POST['peso'] ?? '',
    'alto' => $alto,
    'ancho' => $ancho,
    'largo' => $largo,
];


if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Verifica si ya existe
$indexExistente = -1;
foreach ($_SESSION['carrito'] as $i => $producto) {
    if ($producto['nombre'] === $nombre) {
        $indexExistente = $i;
        break;
    }
}

if ($indexExistente !== -1) {
    // Actualiza cantidad si ya existe
    $_SESSION['carrito'][$indexExistente]['cantidad'] += $cantidad;
} else {
    $_SESSION['carrito'][] = $item;
}

// 🔄 Redirige automáticamente al carrito
header("Location: carrito.php");
exit;
?>
