<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Iniciar Sesión – Doggies</title>
  <link rel="stylesheet" href="css/Login.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="login-page">

  <!-- HEADER -->
  <header>
    <nav>
      <ul class="menu">
        <li><a href="productos.php"><i class="fas fa-dog"></i> Productos</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li><a href="servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
      </ul>
    </nav>
  </header>

  <!-- LOGIN FORM -->
  <main>
    <section class="auth-container">
      <h2>Iniciar Sesión</h2>
      <form action="login_action.php" method="POST">
        <div class="input-group">
          <i class="fas fa-id-card"></i>
          <input
            type="number"
            name="id_number"
            placeholder="No. Identificación"
            required
            inputmode="numeric"
            pattern="\d*"
            min="0"
          />
        </div>
        <div class="input-group password-group">
          <input
            type="password"
            id="login-password"
            name="password"
            placeholder="Contraseña"
            required
          />
          <span class="toggle-password" data-target="login-password">
            <i class="fas fa-eye"></i>
          </span>
        </div>
        <button class="auth-btn" type="submit">Ingresar</button>
        <p class="recover"><a href="recover.php">¿Olvidaste tu contraseña?</a></p>
      </form>
      <p>¿No tienes cuenta? <a href="registro.php">Crear cuenta</a></p>
    </section>
  </main>

  <!-- FOOTER -->
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
    document.querySelectorAll('.toggle-password').forEach(btn => {
      btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
      });
    });
  </script>

</body>
</html>
