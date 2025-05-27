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
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $rol = $_POST['rol'];

    $stmt = $pdo->prepare("UPDATE usuario SET Nombre = ?, Correo = ?, Telefono = ?, Direccion = ?, ID_Rol = ? WHERE ID_Usuario = ?");
    $stmt->execute([$nombre, $correo, $telefono, $direccion, $rol, $id]);

    echo "<script>alert('Usuario actualizado.'); window.location.href='admin.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario</title>
  <link rel="stylesheet" href="css/login.css" />
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
        <div class="input-group"><input type="text" name="nombre" value="<?= htmlspecialchars($usuario['Nombre']) ?>" required></div>
        <div class="input-group"><input type="email" name="correo" value="<?= htmlspecialchars($usuario['Correo']) ?>" required></div>
        <div class="input-group"><input type="text" name="telefono" value="<?= htmlspecialchars($usuario['Telefono']) ?>"></div>
        <div class="input-group"><input type="text" name="direccion" value="<?= htmlspecialchars($usuario['Direccion']) ?>"></div>
        <div class="input-group">
          <select name="rol" required>
            <option value="1" <?= $usuario['ID_Rol'] == 1 ? 'selected' : '' ?>>Cliente</option>
            <option value="2" <?= $usuario['ID_Rol'] == 2 ? 'selected' : '' ?>>Administrador</option>
          </select>
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
        <a href="mailto:doggies@example.com">Email</a>
      </div>
    </div>
  </footer>
</body>
</html>
