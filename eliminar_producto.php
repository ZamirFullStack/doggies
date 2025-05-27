<?php
require 'conexion.php';

if (!isset($_GET['id'])) {
    echo "ID de producto no proporcionado.";
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM producto WHERE ID_Producto = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    echo "<script>alert('Producto eliminado correctamente.'); window.location.href='productos.php';</script>";
    exit;
} catch (PDOException $e) {
    echo "Error al eliminar el producto: " . $e->getMessage();
    exit;
}
?>
