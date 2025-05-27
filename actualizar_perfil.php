<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: Login.php");
    exit;
}

$id = $_SESSION['usuario']['ID_Usuario'];
$nombre = trim($_POST['nombre']);
$correo = trim($_POST['correo']);
$telefono = trim($_POST['telefono']);
$direccion = trim($_POST['direccion']);
$nueva_contrasena = trim($_POST['nueva_contrasena']);

// Actualización básica
$sql = "UPDATE usuario SET Nombre = :nombre, Correo = :correo, Telefono = :telefono, Direccion = :direccion";

// Añadir contraseña si se ingresó
$params = [
    'nombre' => $nombre,
    'correo' => $correo,
    'telefono' => $telefono,
    'direccion' => $direccion
];

if (!empty($nueva_contrasena)) {
    $sql .= ", Contrasena = :contrasena";
    $params['contrasena'] = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
}

$sql .= " WHERE ID_Usuario = :id";
$params['id'] = $id;

$stmt = $pdo->prepare($sql);
$success = $stmt->execute($params);

if ($success) {
    echo "✅ Perfil actualizado con éxito. <a href='mi_cuenta.php'>Volver</a>";
} else {
    echo "❌ Error al actualizar.";
}
?>
