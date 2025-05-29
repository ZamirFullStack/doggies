<?php
require 'conexion.php';

if (!isset($_GET['id'])) {
    die("ID de usuario no proporcionado.");
}

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM usuario WHERE ID_Usuario = :id");
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';

    if (!empty($nueva_contrasena)) {
        if (strlen($nueva_contrasena) < 8) {
            echo "<script>alert('La nueva contraseña debe tener al menos 8 caracteres.');</script>";
        } else {
            $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuario SET Nombre = ?, Correo = ?, Telefono = ?, Direccion = ?, ID_Rol = ?, Contrasena = ? WHERE ID_Usuario = ?");
            $stmt->execute([$nombre, $correo, $telefono, $direccion, $rol, $hash, $id]);
            echo "<script>alert('Usuario y contraseña actualizados.'); window.location.href='admin.php';</script>";
            exit;
        }
    } else {
        $stmt = $pdo->prepare("UPDATE usuario SET Nombre = ?, Correo = ?, Telefono = ?, Direccion = ?, ID_Rol = ? WHERE ID_Usuario = ?");
        $stmt->execute([$nombre, $correo, $telefono, $direccion, $rol, $id]);
        echo "<script>alert('Usuario actualizado.'); window.location.href='admin.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario</title>
  <link rel="stylesheet" href="css/Login.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    
    .password-wrapper {
      position: relative;
    }
    .toggle-btn {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: #888;
      font-size: 1.2em;
      padding: 0;
    }

    .password-wrapper i {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      cursor: pointer;
      color: #888;
    }
  </style>
</head>
<body class="login-page">
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php">Productos</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li><a href="Servicios.php">Servicios</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <div class="auth-container" style="max-width:600px;">
      <h2>Editar Usuario</h2>
      <form method="POST">
        <div class="input-group">
          <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['Nombre'] ?? '') ?>" placeholder="Nombre" required>
        </div>
        <div class="input-group">
          <input type="email" name="correo" value="<?= htmlspecialchars($usuario['Correo'] ?? '') ?>" placeholder="Correo" required>
        </div>
        <div class="input-group">
          <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['Telefono'] ?? '') ?>" placeholder="Teléfono">
        </div>
        <div class="input-group">
          <input type="text" name="direccion" value="<?= htmlspecialchars($usuario['Direccion'] ?? '') ?>" placeholder="Dirección">
        </div>
        <div class="input-group">
          <select name="rol" required>
            <option value="1" <?= ($usuario['ID_Rol'] ?? 1) == 1 ? 'selected' : '' ?>>Cliente</option>
            <option value="2" <?= ($usuario['ID_Rol'] ?? 1) == 2 ? 'selected' : '' ?>>Administrador</option>
          </select>
        </div>
        <div class="input-group password-wrapper">
          <input type="password" name="nueva_contrasena" id="nueva_contrasena" placeholder="Nueva contraseña (opcional)">
          <i class="fas fa-eye" id="toggleIcon" onclick="togglePassword()"></i>
        </div>
        <button class="auth-btn" type="submit">Guardar Cambios</button>
      </form>
    </div>
  </main>

  <footer>
    <div class="footer-content">
      <h3>Síguenos</h3>
      <div class="social-links">
        <a href="https://facebook.com/" target="_blank">Facebook</a>
        <a href="https://instagram.com/" target="_blank">Instagram</a>
        <a href="mailto:doggiespasto@gmail.com">Email</a>
      </div>
    </div>
  </footer>

  <script>
    function togglePassword() {
      const input = document.getElementById("nueva_contrasena");
      const icon = document.getElementById("toggleIcon");
      if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
      }
    }
  </script>
</body>
</html>
