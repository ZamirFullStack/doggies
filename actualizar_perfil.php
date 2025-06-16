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
$telefono_raw = trim($_POST['telefono']);
$direccion = trim($_POST['direccion']);
$nueva_contrasena = trim($_POST['nueva_contrasena']);

// Validar teléfono: solo números, sin letras ni símbolos
$telefono = preg_replace('/\D/', '', $telefono_raw);

if (strlen($telefono) !== 10 || $telefono[0] === '0') {
    echo "<script>
        alert('❌ Error: El teléfono debe tener exactamente 10 dígitos y no puede comenzar con 0.');
        window.location.href = 'mi_cuenta.php';
    </script>";
    exit;
}

// Opcional: convertir a entero
$telefono = intval($telefono);

// Actualización básica
$sql = "UPDATE usuario SET Nombre = :nombre, Correo = :correo, Telefono = :telefono, Direccion = :direccion";

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
    echo "<script>
        alert('✅ Perfil actualizado con éxito');
        window.location.href = 'mi_cuenta.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Error al actualizar');
        window.location.href = 'mi_cuenta.php';
    </script>";
}
?>

