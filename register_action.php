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
    echo "<script>alert('⚠️ Ya existe un usuario con ese correo o documento.'); window.history.back();</script>";
    exit;
} else {
    $insert = $pdo->prepare("INSERT INTO usuario (Nombre, Documento, Correo, Contrasena, ID_Rol) VALUES (:name, :doc, :email, :pass, 2)");
    $success = $insert->execute([
        'name' => $name,
        'doc' => $id_number,
        'email' => $email,
        'pass' => $password
    ]);

    if ($success) {
        echo "<script>alert('✅ Registro exitoso.'); window.location.href='Login.php';</script>";
    } else {
        echo "<script>alert('❌ Error al registrar.'); window.history.back();</script>";
    }
    exit;
}


$token = bin2hex(random_bytes(32)); // genera token seguro

$stmt = $pdo->prepare("INSERT INTO usuario (Nombre, Correo, Tipo_Documento, Documento, Contrasena, ID_Rol, Confirmado, Token_Confirmacion)
VALUES (?, ?, ?, ?, ?, 1, 0, ?)");
$stmt->execute([$name, $email, $tipo_doc, $id_number, password_hash($password, PASSWORD_DEFAULT), $token]);

// Enviar correo de verificación
$destinatario = $email;
$asunto = "Confirma tu cuenta en Doggies";
$mensaje = "Hola $name,\n\nHaz clic en el siguiente enlace para confirmar tu cuenta:\n\n";
$mensaje .= "https://doggies-production.up.railway.app/verificar.php?token=$token";

mail($destinatario, $asunto, $mensaje);


?>

