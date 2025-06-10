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
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    .menu {
      list-style: none;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 40px;
      padding: 2px 10px;
      background-color: rgba(255, 255, 255, 0.9);
      padding: 1em 2em;
      flex-wrap: wrap;
    }

    .menu li a {
      display: inline-block;
      text-align: center;
      line-height: 1.2;
      font-weight: bold;
      color: #000;
      text-decoration: none;
    }

    .submenu {
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    /* ----------- Mi Cuenta - VERSIÓN ESCRITORIO ----------- */
    .submenu > a {
      text-align: center;
      white-space: nowrap;
      padding: 0 4px;
      font-size: 1em;
    }
    .submenu > a .escritorio-micuenta {
      display: inline;
      font-size: 1em;
    }
    .submenu > a .movil-micuenta {
      display: none;
    }

    .submenu-opciones {
      min-width: 140px;
      background-color: #fff;
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translateX(-50%);
      padding: 0;
      margin-top: 4px;
      border: 1px solid #ccc;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      z-index: 999;
      display: none;
    }

    .submenu.open .submenu-opciones {
      display: block;
    }

    .submenu-opciones li {
      padding: 8px 12px;
      text-align: center;
    }

    .submenu-opciones li a {
      display: block;
      text-decoration: none;
      color: #333;
    }

    .submenu-opciones li a:hover {
      background-color: #f4f4f4;
    }

    /* LOGO - SIEMPRE CIRCULAR Y RESPONSIVO */
    .logo img.logo-img {
      height: 60px;
      width: 60px;
      object-fit: cover;
      border-radius: 50%;
      border: 2.5px solid #fff;
      box-shadow: 0 1px 8px #bbb;
      background: #fff;
      display: block;
      margin: 0 auto;
      transition: height 0.2s, width 0.2s;
    }

    @media (max-width: 800px) {
      .menu {
        gap: 16px;
        padding: 1em 0.5em;
      }
    }
    @media (max-width: 600px) {
      .menu {
        gap: 7px;
        padding: 1em 0.1em;
      }
      .menu li a {
        font-size: 0.98rem;
        line-height: 1.2;
      }

      /* --------- Mi Cuenta RESPONSIVO --------- */
      .submenu > a .escritorio-micuenta {
        display: none;
      }
      .submenu > a .movil-micuenta {
        display: block;
        font-size: 1em;
        text-align: center;
      }
      .movil-micuenta .mi-parte {
        display: inline-block;
      }
      .movil-micuenta .cuenta-parte {
        display: block;
        margin-top: 2px;
      }

      /* Logo más pequeño pero siempre circular */
      .logo img.logo-img {
        height: 38px;
        width: 38px;
        border-radius: 50%;
      }
    }
    
    .nosotros-link {
      display: inline-block;
      margin-top: 30px;
      background-color: #4caf50;
      color: white !important;
      padding: 12px 24px;
      border-radius: 12px;
      text-decoration: none !important;
      font-weight: bold;
      font-size: 1.2rem;
      box-shadow: 0 4px 18px 0 rgba(0,0,0,0.13);
      border: none;
      outline: none;
      cursor: pointer;
      opacity: 0;
      /* Usa fadeInUp igual que el subtítulo, pero con un delay mayor */
      animation: fadeInUp 1.5s ease-in-out 1.5s forwards;
      transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    }

    .nosotros-link:hover,
    .nosotros-link:focus {
      background-color: #388e3c;
      color: #fff !important;
      box-shadow: 0 2px 12px 0 rgba(76,175,80,0.23);
      text-decoration: none;
    }

    .menu li a:hover {
      background-color: #28a745; /* verde */
      color: white !important;
      border-radius: 6px;
      padding: 8px 12px; /* un poco de padding para que el fondo abarque bien */
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .menu li a:hover i {
      color: white !important;
    }

    /* EXCLUIMOS EL LOGO PARA QUE NO TENGA HOVER */
    .menu li.logo a:hover {
      background-color: transparent !important;
      color: inherit !important;
      padding: 0 !important;
      cursor: default;
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
<body style="background: url('img/fondo.jpg') center/cover no-repeat fixed;">
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php"><i class="fas fa-dog"></i><br>Productos</a></li>
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i><br>Servicios</a></li>
        <li class="logo">
          <a href="index.php">
            <img src="img/fondo.jpg" alt="Logo Doggies" class="logo-img" />
          </a>
        </li>
        <?php if (isset($_SESSION['usuario'])): ?>
          <li class="submenu">
            <a href="cuenta">
              <!-- VERSIÓN ESCRITORIO -->
              <span class="escritorio-micuenta">
                Mi Cuenta <i class="fas fa-user-group"></i>
              </span>
              <!-- VERSIÓN MÓVIL -->
              <span class="movil-micuenta">
                <span class="mi-parte">Mi <i class="fas fa-user-group"></i></span>
                <span class="cuenta-parte">Cuenta</span>
              </span>
            </a>
            <ul class="submenu-opciones">
              <li><a href="mi_cuenta.php">Mi perfil</a></li>
              <?php if ($_SESSION['usuario']['ID_Rol'] == 2): ?>
              <li><a href="admin.php">Panel Admin</a></li>
              <?php endif; ?>
              <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
          </li>
          <li><a href="carrito.php"><i class="fas fa-cart-shopping"></i><br>Carrito</a></li>
        <?php else: ?>
          <li><a href="Login.php"><i class="fas fa-sign-in-alt"></i><br>Login</a></li>
          <li><a href="Registro.php"><i class="fas fa-user-plus"></i><br>Crear Cuenta</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>

  <!-- El resto igual que tu código -->
  <section class="hero">
    <img src="img/hueso.png" class="huesito h1" alt="Hueso decorativo" />
    <img src="img/hueso.png" class="huesito h2" alt="Hueso decorativo" />
    <img src="img/hueso.png" class="huesito h3" alt="Hueso decorativo" />
    <img src="img/hueso.png" class="huesito h4" alt="Hueso decorativo" />
    <div class="content">
      <h1 class="main-title">¡Bienvenido a Doggies!</h1>
      <p class="sub-title">Tu Tienda y Centro de Cuidado para Peluditos</p>
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
        <a href="mailto:doggiespasto22@gmail.com"><i class="fas fa-envelope"></i></a>
      </div>
    </div>
  </footer>

  <a href="https://wa.me/573216734085" class="whatsapp-float" target="_blank">
    <i class="fab fa-whatsapp"></i> <span>Contacto</span>
  </a>
</body>
</html>
