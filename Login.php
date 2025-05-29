<?php
session_start();
require 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE Correo = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && isset($usuario['Contrasena']) && password_verify($password, $usuario['Contrasena'])) {
            if (!$usuario['Confirmado']) {
                $error = 'Por favor, verifica tu correo electrónico antes de iniciar sesión.';
            } else {
                $_SESSION['usuario'] = [
                    'ID_Usuario' => $usuario['ID_Usuario'],
                    'Nombre' => $usuario['Nombre'],
                    'ID_Rol' => $usuario['ID_Rol']
                ];
                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Doggies</title>
    <link rel="stylesheet" href="css/Login.css">
</head>
<body class="login-page">
    <header>
        <nav>
            <ul class="menu">
                <li><a href="productos.php"><i class="fas fa-dog"></i> Productos</a></li>
                <li class="logo"><a href="index.php">Doggies</a></li>
                <li><a href="servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="auth-container">
            <h2>Iniciar Sesión</h2>
            <?php if ($error): ?>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Correo Electrónico" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <button class="auth-btn" type="submit">Iniciar Sesión</button>
            </form>
            <p>¿No tienes cuenta? <a href="Registro.php">Regístrate</a></p>
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
</body>
</html>
