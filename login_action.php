<?php
session_start();
require 'conexion.php';

// Usar email en lugar de id_number
$email = trim($_POST['email']);
$password = $_POST['password'];

// Consultar por correo electrónico
$stmt = $pdo->prepare("SELECT * FROM usuario WHERE Correo = :correo");
$stmt->execute(['correo' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['Contrasena'])) {
    $_SESSION['usuario'] = $user;
    header("Location: index.php");
    exit;
} else {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Error de inicio de sesión</title>
      <link rel="stylesheet" href="css/login.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
      <style>
        .error-container {
          max-width: 400px;
          margin: 100px auto;
          background-color: #fff3f3;
          color: #b00020;
          padding: 2rem;
          border: 1px solid #f5c2c7;
          border-radius: 10px;
          text-align: center;
          font-family: 'Roboto', sans-serif;
        }
        .error-container h2 {
          margin-bottom: 1rem;
        }
        .error-container a {
          color: #fff;
          background-color: #4caf50;
          padding: 10px 20px;
          border-radius: 5px;
          text-decoration: none;
          font-weight: bold;
          display: inline-block;
          margin-top: 1rem;
        }
      </style>
    </head>
    <body>

      <div class="error-container">
        <h2>❌ Correo o contraseña incorrectos.</h2>
        <p>Verifica tus credenciales e intenta nuevamente.</p>
        <a href="login.php">Volver al inicio de sesión</a>
      </div>
    </body>
    </html>
    <?php
    exit;
}
?>
