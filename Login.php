<?php
session_start();
require 'conexion.php'; // Asegúrate de tener este archivo y la variable $pdo configurada

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // Busca el usuario por email
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE Correo = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && isset($usuario['Contrasena']) && password_verify($password, $usuario['Contrasena'])) {
            if (!$usuario['Confirmado']) {
                echo "<script>alert('Tu cuenta aún no ha sido verificada. Revisa tu correo.');</script>";
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
            echo "<script>alert('Correo o contraseña incorrectos.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Doggies</title>
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
            <h2>Iniciar Sesión</h2>
            <form method="POST">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Correo Electrónico" required>
                </div>
                <div class="input-group password-group">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <span class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
                <button class="auth-btn" type="submit">Iniciar Sesión</button>
            </form>
            <p><a href="recover.php">¿Olvidaste tu contraseña?</a></p>
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

    <script>
    function togglePassword() {
        const passwordField = document.getElementById("password");
        const icon = document.getElementById("toggleIcon");
        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            passwordField.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
    </script>
</body>
</html>
