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
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body, html { height: 100%; font-family: 'Roboto', sans-serif; }

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
    .submenu-opciones li { padding: 8px 12px; }
    .submenu-opciones li a {
      color: #333;
      text-decoration: none;
      display: block;
    }

    .carousel {
      position: relative;
      width: 100%;
      height: 100vh;
      overflow: hidden;
    }

    .carousel-slide {
      display: none;
      position: absolute;
      width: 100%;
      height: 100%;
      background-size: cover;
      background-position: center;
      transition: opacity 1s ease-in-out;
    }

    .carousel-slide::after {
      content: "";
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      background: rgba(0,0,0,0.4);
      z-index: 1;
    }

    .carousel-slide.active {
      display: block;
    }

    .carousel-slide .content {
      position: relative;
      z-index: 2;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      color: white;
      padding: 2rem;
    }

    .carousel-slide .content h1 {
      font-size: 3.5rem;
      margin-bottom: 1rem;
    }
    .carousel-slide .content p {
      font-size: 1.8rem;
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
      transition: background-color 0.3s ease;
    }
    .nosotros-link:hover { background-color: #388e3c; }

    .carousel-indicators {
      position: absolute;
      bottom: 40px;
      width: 100%;
      text-align: center;
      z-index: 10;
    }
    .carousel-indicators span {
      display: inline-block;
      width: 12px;
      height: 12px;
      margin: 0 6px;
      background-color: white;
      border-radius: 50%;
      opacity: 0.5;
      cursor: pointer;
    }
    .carousel-indicators span.active {
      opacity: 1;
      background-color: #4caf50;
    }

    .huesito {
      width: 40px;
      position: fixed;
      z-index: 10;
    }
    .h1 { top: 0; left: 0; }
    .h2 { top: 0; right: 0; }
    .h3 { bottom: 0; left: 0; }
    .h4 { bottom: 0; right: 0; }

    footer {
      background-color: rgba(51,51,51,0.95);
      color: #fff;
      text-align: center;
      padding: 1.5em 2em;
      width: 100%;
      z-index: 15;
      position: relative;
    }
  </style>
</head>
<body>
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php"><i class="fas fa-dog"></i> Productos</a></li>
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
        <li class="logo"><img src="img/fondo.jpg" alt="Logo Doggies" class="logo-img"></li>
        <?php if (isset($_SESSION['usuario'])): ?>
          <li class="submenu">
            <a href="cuenta"><i class="fas fa-user"></i> Mi cuenta</a>
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

  <section class="carousel">
    <div class="carousel-slide active" style="background-image: url('img/fondo.jpg');">
      <div class="content">
        <h1>¡Bienvenido a Doggies!</h1>
        <p>El lugar donde el amor, cuidado y alegría para tus peludos es nuestra pasión.</p>
        <a href="Nosotros.php" class="nosotros-link"><i class="fas fa-paw"></i> Conócenos</a>
      </div>
    </div>
    <div class="carousel-slide" style="background-image: url('img/productos.png');"></div>
    <div class="carousel-slide" style="background-image: url('img/promociones.png');"></div>
    <div class="carousel-slide" style="background-image: url('img/servicios.png');"></div>

    <div class="carousel-indicators">
      <span class="active"></span>
      <span></span>
      <span></span>
      <span></span>
    </div>

    <img src="img/hueso.png" class="huesito h1" alt="decoracion" />
    <img src="img/hueso.png" class="huesito h2" alt="decoracion" />
    <img src="img/hueso.png" class="huesito h3" alt="decoracion" />
    <img src="img/hueso.png" class="huesito h4" alt="decoracion" />
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

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.submenu > a').forEach(el => {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          el.parentElement.classList.toggle('open');
        });
      });

      const slides = document.querySelectorAll('.carousel-slide');
      const indicators = document.querySelectorAll('.carousel-indicators span');
      let current = 0;

      function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(dot => dot.classList.remove('active'));
        slides[index].classList.add('active');
        indicators[index].classList.add('active');
      }

      function autoSlide() {
        current = (current + 1) % slides.length;
        showSlide(current);
      }

      indicators.forEach((dot, index) => {
        dot.addEventListener('click', () => {
          current = index;
          showSlide(current);
        });
      });

      setInterval(autoSlide, 5000);
    });
  </script>
</body>
</html>
