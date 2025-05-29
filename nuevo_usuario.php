<?php
require 'conexion.php';

function obtenerValoresEnum($pdo, $tabla, $columna) {
    $stmt = $pdo->query("SHOW COLUMNS FROM `$tabla` LIKE '$columna'");
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    preg_match("/enum\((.*)\)/", $fila['Type'], $matches);
    $valores = array();
    foreach (explode(",", $matches[1]) as $valor) {
        $valores[] = trim($valor, "' ");
    }
    return $valores;
}

$tiposDocumento = obtenerValoresEnum($pdo, 'usuario', 'Tipo_Documento');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $documento = $_POST['documento'];
    $tipo_documento = $_POST['tipo_documento'];
    $rol = $_POST['rol'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuario (Nombre, Correo, Telefono, Direccion, Documento, Tipo_Documento, Contrasena, ID_Rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $correo, $telefono, $direccion, $documento, $tipo_documento, $contrasena, $rol]);

    echo "<script>alert('Usuario creado exitosamente.'); window.location.href='admin.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Usuario</title>
  <link rel="stylesheet" href="css/Login.css" />
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
      <h2>Nuevo Usuario</h2>
      <form method="POST">
        <div class="input-group">
          <input type="text" name="nombre" placeholder="Nombre" required>
        </div>
        <div class="input-group">
          <input type="email" name="correo" placeholder="Correo" required>
        </div>
        <div class="input-group">
          <input type="password" name="contrasena" placeholder="Contraseña" required>
        </div>
        <div class="input-group">
          <input type="text" name="telefono" placeholder="Teléfono">
        </div>
        <div class="input-group">
          <input type="text" name="direccion" placeholder="Dirección">
        </div>
        <div class="input-group">
          <input type="text" name="documento" placeholder="Número de Documento" required>
        </div>
        <div class="input-group">
          <select name="tipo_documento" required>
            <option value="" disabled selected>Seleccione tipo de documento</option>
            <?php foreach ($tiposDocumento as $tipo): ?>
              <option value="<?= $tipo ?>"><?= $tipo ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="input-group">
          <select name="rol" required>
            <option value="1">Cliente</option>
            <option value="2">Administrador</option>
          </select>
        </div>
        <button class="auth-btn" type="submit">Crear Usuario</button>
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
</body>
</html>
