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
    $telefono_raw = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';

    // Limpiar teléfono (solo dígitos)
    $telefono = preg_replace('/\D/', '', $telefono_raw);

    // Validar teléfono: 10 dígitos y no inicia con 0
    if (strlen($telefono) !== 10 || $telefono[0] === '0') {
        echo "<script>alert('Error: El teléfono debe tener exactamente 10 dígitos y no puede comenzar con 0.');</script>";
    } else {
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
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario - Doggies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="css/Login.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
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
        <section class="auth-container">
            <h2>Editar Usuario</h2>
            <form method="POST">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['Nombre'] ?? '') ?>" placeholder="Nombre" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="correo" value="<?= htmlspecialchars($usuario['Correo'] ?? '') ?>" placeholder="Correo Electrónico" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-phone"></i>
                    <input
                type="number"
                name="telefono"
                value="<?= htmlspecialchars($usuario['Telefono'] ?? '') ?>"
                placeholder="Teléfono"
                min="1000000000"
                max="9999999999"
                step="1"
                oninput="if(this.value.length > 10) this.value = this.value.slice(0,10);"
                />
                </div>
                <div class="input-group">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" name="direccion" value="<?= htmlspecialchars($usuario['Direccion'] ?? '') ?>" placeholder="Dirección">
                </div>
                <div class="input-group">
                    <select name="rol" required>
                        <option value="1" <?= ($usuario['ID_Rol'] ?? 1) == 1 ? 'selected' : '' ?>>Cliente</option>
                        <option value="2" <?= ($usuario['ID_Rol'] ?? 1) == 2 ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="input-group password-group">
                    <input type="password" name="nueva_contrasena" id="nueva_contrasena" placeholder="Nueva contraseña (opcional)">
                    <span class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
                <button class="auth-btn" type="submit">Guardar Cambios</button>
            </form>
        </section>
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
