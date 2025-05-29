<?php
require 'conexion.php';

$token = $_GET['token'] ?? '';
if (!$token) die("Token inválido");

$stmt = $pdo->prepare("UPDATE usuario SET Confirmado = 1, Token_Confirmacion = NULL WHERE Token_Confirmacion = ?");
$stmt->execute([$token]);

if ($stmt->rowCount() > 0) {
    echo "✅ Cuenta confirmada. Ya puedes iniciar sesión.";
} else {
    echo "❌ Token inválido o ya usado.";
}
?>
