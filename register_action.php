<?php
require 'conexion.php';

$name = trim($_POST['name']);
$id_number = trim($_POST['id_number']);
$email = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Verificar si el usuario ya existe
$stmt = $pdo->prepare("SELECT * FROM usuario WHERE Correo = :email OR Documento = :doc");
$stmt->execute(['email' => $email, 'doc' => $id_number]);

if ($stmt->rowCount() > 0) {
    echo "⚠️ Ya existe un usuario con ese correo o documento.";
} else {
    $insert = $pdo->prepare("INSERT INTO usuario (Nombre, Documento, Correo, Contrasena, ID_Rol) VALUES (:name, :doc, :email, :pass, 2)");
    $success = $insert->execute([
        'name' => $name,
        'doc' => $id_number,
        'email' => $email,
        'pass' => $password
    ]);

    if ($success) {
        echo "✅ Registro exitoso. <a href='Login.php'>Iniciar sesión</a>";
    } else {
        echo "❌ Error al registrar.";
    }
}
?>
