<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Doggies</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    .submenu { position: relative; cursor: pointer; }
    .submenu-opciones {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      background-color: #fff;
      list-style: none;
      padding: 0;
      border: 1px solid #ccc;
      z-index: 1000;
    }
    .submenu.open .submenu-opciones { display: block; }
    .submenu-opciones li {
      padding: 8px 12px;
    }
    .submenu-opciones li a {
      color: #333;
      text-decoration: none;
      display: block;
    }
    .nosotros-link {
      display: inline-block;
      margin-top: 30px;
      background-color: #4caf50;
      color: white;
      padding: 12px 24px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: bold;
      font-size: 1.2rem;
      animation: fadeInUp 1.5s ease-in-out 1s forwards;
      opacity: 0;
      transition: background-color 0.3s ease;
    }
    .nosotros-link:hover {
      background-color: #388e3c;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.submenu > a').forEach(el => {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          el.parentElement.classList.toggle('open');
        });
      });
    });
  </script>
</head>
<body>
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php"><i class="fas fa-dog"></i> Productos</a></li>
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
        <li class="logo">
          <img src="img/fondo.jpg" alt="Logo Doggies" class="logo-img">
        </li>
        <?php if (isset($_SESSION['usuario'])): ?>
          <li class="submenu">
            <a href="#"><i class="fas fa-user"></i> Mi cuenta</a>
            <ul class="submenu-opciones">
              <li><a href="mi_cuenta.php">Mi perfil</a></li>
              <?php if ($_SESSION['usuario']['ID_Rol'] == 2): ?>
              <li><a href="admin.php">Panel Admin</a></li>
              <?php endif; ?>
              <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
          </li>
          <li><a href="carrito.php"><i class="fas fa-cart-shopping"></i> Carrito</a></li>
        <?php else: ?>
          <li><a href="Login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
          <li><a href="Registro.php"><i class="fas fa-user-plus"></i> Crear Cuenta</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <section class="hero">
    <img src="img/hueso.png" class="huesito h1" alt="Hueso decorativo" />
    <img src="img/hueso.png" class="huesito h2" alt="Hueso decorativo" />
    <img src="img/hueso.png" class="huesito h3" alt="Hueso decorativo" />
    <img src="img/hueso.png" class="huesito h4" alt="Hueso decorativo" />
    <div class="content">
      <h1 class="main-title">¡Bienvenido a Doggies! </h1>
      <p class="sub-title">El lugar donde el amor, cuidado y alegría para tus peludos es nuestra pasión.</p>
      <a href="Nosotros.php" class="nosotros-link"><i class="fas fa-paw"></i> Conócenos</a>
    </div>
  </section>

  <footer>
    <div class="footer-content">
      <h3>Síguenos</h3>
      <div class="social-links">
        <a href="https://www.facebook.com/profile.php?id=100069951193254" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.instagram.com/doggiespaseadores/" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://www.tiktok.com/@doggies_paseadores?is_from_webapp=1&sender_device=pc" target="_blank"><i class="fab fa-tiktok"></i></a>
        <a href="mailto:doggiespasto@gmail.com"><i class="fas fa-envelope"></i></a>
      </div>
    </div>
  </footer>

  <a href="https://wa.me/573177319062" class="whatsapp-float" target="_blank">
    <i class="fab fa-whatsapp"></i> <span>Contacto</span>
  </a>
</body>
</html>
