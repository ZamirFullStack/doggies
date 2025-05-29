<?php
session_start();

if (!isset($_SESSION['codigo_verificacion']) || !isset($_SESSION['correo_recuperacion'])) {
  header('Location: recover.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $codigo_ingresado = trim($_POST['codigo']);
  $codigo_correcto = $_SESSION['codigo_verificacion'];

  if ($codigo_ingresado == $codigo_correcto) {
    $_SESSION['codigo_validado'] = true;
    header('Location: cambiar_contrasena.php');
    exit;
  } else {
    $error = 'Código incorrecto. Intenta nuevamente.';
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Verificar Código</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { height: 100%; }
    body.login-page {
      font-family: 'Roboto', sans-serif;
      background-color: #f4f7fc;
      color: #333;
      display: flex;
      flex-direction: column;
    }
    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 70px;
      background-color: rgba(255,255,255,0.95);
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 100;
    }
    .menu {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      max-width: 1200px;
      padding: 0 20px;
      list-style: none;
    }
    .menu li {
      flex: 1;
      text-align: center;
    }
    .menu li.logo a {
      display: block;
      width: 150px;
      height: 60px;
      background-image: url('img/fondo.jpg');
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      text-indent: -9999px;
      margin: 0 auto;
    }
    .menu a {
      text-decoration: none;
      color: #333;
      font-weight: bold;
      font-size: 1rem;
    }
    main {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding-top: 90px;
      padding-bottom: 40px;
    }
    .auth-container {
      width: 100%;
      max-width: 400px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 2em;
    }
    .auth-container h2 {
      text-align: center;
      margin-bottom: 1.5em;
      font-size: 1.8rem;
    }
    .input-group {
      position: relative;
      margin-bottom: 1.2em;
    }
    .input-group i {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      color: #888;
      font-size: 1.2em;
    }
    .input-group input {
      width: 100%;
      padding: 0.8em 0.8em 0.8em 2.8em;
      border: 1px solid #ccc;
      border-radius: 4px;
      background-color: #f9f9f9;
    }
    .auth-btn {
      width: 100%;
      padding: 0.8em;
      background-color: #4caf50;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1.1rem;
      font-weight: bold;
      cursor: pointer;
    }
    .auth-btn:hover {
      background-color: #43a047;
    }
    footer {
      background-color: rgba(51, 51, 51, 0.95);
      color: #fff;
      text-align: center;
      padding: 1.5em 2em;
      width: 100%;
    }
    .footer-content h3 {
      font-size: 1.4em;
      margin-bottom: 0.5em;
    }
    .social-links {
      display: flex;
      justify-content: center;
      gap: 1em;
      margin-bottom: 10px;
    }
    .social-links a {
      color: #fff;
      font-size: 1.5rem;
    }
    .social-links a:hover {
      color: #ffd700;
    }
    footer::after {
      content: "© 2025 Doggies. Todos los derechos reservados.";
      display: block;
      font-size: 0.9rem;
      color: #ccc;
      margin-top: 1em;
    }
  </style>
</head>
<body class="login-page">
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php"><i class="fas fa-dog"></i> Productos</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <div class="auth-container">
      <h2>Verificar Código</h2>
      <form method="POST">
        <div class="input-group">
          <i class="fas fa-key"></i>
          <input type="text" name="codigo" placeholder="Código recibido" required>
        </div>
        <button type="submit" class="auth-btn">Verificar</button>
        <?php if ($error): ?><p style="color: red; text-align: center; margin-top: 1em;">⚠️ <?= $error ?></p><?php endif; ?>
      </form>
    </div>
  </main>

  <footer>
    <div class="footer-content">
      <h3>Síguenos</h3>
      <div class="social-links">
        <a href="https://www.facebook.com/profile.php?id=100069951193254" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/doggiespaseadores/" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://www.tiktok.com/@doggies_paseadores" target="_blank"><i class="fab fa-tiktok"></i></a>
        <a href="mailto:doggiespasto@gmail.com"><i class="fas fa-envelope"></i></a>
      </div>
    </div>
  </footer>
</body>
</html>
