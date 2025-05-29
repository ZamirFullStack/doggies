<?php
session_start();
require 'conexion.php';

if (!isset($_GET['id'])) {
    die("ID de usuario no proporcionado.");
}

$id = $_GET['id'];

// Prevenir que el admin actual se elimine a sí mismo
if ($_SESSION['usuario']['ID_Usuario'] == $id) {
    echo "<script>alert('No puedes eliminar tu propio usuario.'); window.location.href='admin.php';</script>";
    exit;
}

// Eliminar el usuario
$stmt = $pdo->prepare("DELETE FROM usuario WHERE ID_Usuario = ?");
if ($stmt->execute([$id])) {
    echo "<script>alert('Usuario eliminado exitosamente.'); window.location.href='admin.php';</script>";
} else {
    echo "<script>alert('Error al eliminar el usuario.'); window.location.href='admin.php';</script>";
}
?>
