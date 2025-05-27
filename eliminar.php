<?php
session_start();

if (isset($_POST['index'])) {
    $index = (int) $_POST['index'];

    if (isset($_SESSION['carrito'][$index])) {
        unset($_SESSION['carrito'][$index]);
        $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
    }
}

header("Location: carrito.php");
exit;
?>
