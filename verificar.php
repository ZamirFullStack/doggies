<?php
require 'conexion.php';

$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verificación de Correo - Doggies</title>
  <style>
    body {
      background: #f0f4f8;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      font-family: 'Roboto', sans-serif;
    }
    .container {
      background: white;
      padding: 2.5rem 3rem;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 90%;
      text-align: center;
    }
    .icon {
      font-size: 4rem;
      margin-bottom: 1rem;
    }
    .icon.success {
      color: #28a745;
    }
    .icon.error {
      color: #dc3545;
    }
    h1 {
      font-size: 1.8rem;
      margin-bottom: 1rem;
      color: #333;
    }
    p {
      font-size: 1.1rem;
      color: #555;
      margin-bottom: 2rem;
    }
    a.btn {
      display: inline-block;
      background-color: #28a745;
      color: white;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.3s ease;
    }
    a.btn:hover {
      background-color: #218838;
    }
    @media (max-width: 450px) {
      .container {
        padding: 2rem 1.5rem;
      }
      h1 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <?php
    if (!$token) {
      echo '<div class="icon error">❌</div>';
      echo '<h1>Token inválido</h1>';
      echo '<p>No se proporcionó un token de verificación válido.</p>';
    } else {
      $stmt = $pdo->prepare("UPDATE usuario SET Confirmado = 1, Token_Confirmacion = NULL WHERE Token_Confirmacion = ?");
      $stmt->execute([$token]);

      if ($stmt->rowCount() > 0) {
        echo '<div class="icon success">✅</div>';
        echo '<h1>Cuenta confirmada</h1>';
        echo '<p>¡Tu correo ha sido verificado correctamente! Ya puedes iniciar sesión.</p>';
      } else {
        echo '<div class="icon error">❌</div>';
        echo '<h1>Token inválido o ya usado</h1>';
        echo '<p>El enlace de verificación es inválido o el correo ya fue confirmado anteriormente.</p>';
      }
    }
    ?>
    <a href="login.php" class="btn">Iniciar sesión</a>
  </div>
</body>
</html>
