<?php
require 'conexion.php';
require 'vendor/autoload.php'; // Asegúrate de que Composer haya generado este archivo

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Obtener y sanitizar los datos del formulario
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$tipo_doc = $_POST['tipo_documento'];
$id_number = trim($_POST['id_number']);
$password = $_POST['password'];

// Verificar si el usuario ya existe
$stmt = $pdo->prepare("SELECT * FROM usuario WHERE Correo = :email OR Documento = :doc");
$stmt->execute(['email' => $email, 'doc' => $id_number]);

if ($stmt->rowCount() > 0) {
    echo "<script>alert('⚠️ Ya existe un usuario con ese correo o documento.'); window.history.back();</script>";
    exit;
}

// Generar token de confirmación
$token = bin2hex(random_bytes(32));

// Hash de la contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insertar el nuevo usuario en la base de datos
$insert = $pdo->prepare("INSERT INTO usuario (Nombre, Correo, Tipo_Documento, Documento, Contrasena, ID_Rol, Confirmado, Token_Confirmacion) VALUES (:name, :email, :tipo_doc, :doc, :pass, 1, 0, :token)");
$success = $insert->execute([
    'name' => $name,
    'email' => $email,
    'tipo_doc' => $tipo_doc,
    'doc' => $id_number,
    'pass' => $hashed_password,
    'token' => $token
]);

if ($success) {
    // Enviar correo de verificación
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'doggiespasto22@gmail.com';
        $mail->Password = 'nfav ibzv txxd wvwl'; // Reemplaza con tu contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom('doggiespasto22@gmail.com', 'Doggies');
        $mail->addAddress($email, $name);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Confirma tu cuenta en Doggies';
        $mail->Body = "Hola $name,<br><br>Haz clic en el siguiente enlace para confirmar tu cuenta:<br><a href='https://doggies-production.up.railway.app/verificar.php?token=$token'>Confirmar Cuenta</a><br><br>Si no solicitaste este registro, puedes ignorar este correo.";

        $mail->send();
        echo "<script>alert('✅ Registro exitoso. Revisa tu correo para confirmar tu cuenta.'); window.location.href='Login.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('❌ Error al enviar el correo de confirmación: {$mail->ErrorInfo}'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('❌ Error al registrar.'); window.history.back();</script>";
}
?>
