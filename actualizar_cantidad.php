<?php
session_start();

// Verifica que lleguen los datos necesarios
if (
    isset($_POST['index'], $_POST['cantidad']) &&
    is_numeric($_POST['index']) && is_numeric($_POST['cantidad']) &&
    isset($_SESSION['carrito'][$_POST['index']])
) {
    $idx = intval($_POST['index']);
    $cantidad = max(1, min(25, intval($_POST['cantidad'])));
    $_SESSION['carrito'][$idx]['cantidad'] = $cantidad;

    // Puedes retornar un json si lo prefieres, pero para el fetch actual esto basta
    echo 'ok';
    exit;
}

echo 'error';
exit;
