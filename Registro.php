<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Crear Cuenta – Doggies</title>
  <link rel="stylesheet" href="css/Login.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <?php
  $url = 'mysql://root:AaynZNNKYegnXoInEgQefHggDxoRieEL@centerbeam.proxy.rlwy.net:58462/railway';
  $dbparts = parse_url($url);
  $host = $dbparts["host"];
  $port = $dbparts["port"];
  $user = $dbparts["user"];
  $pass = $dbparts["pass"];
  $db   = ltrim($dbparts["path"], '/');

  try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
  }

  function obtenerValoresEnum(PDO $pdo, string $tabla, string $columna): array {
    $stmt = $pdo->query("SHOW COLUMNS FROM `$tabla` LIKE '$columna'");
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fila && preg_match("/^enum\((.*)\)\$/", $fila['Type'], $matches)) {
        return str_getcsv($matches[1], ',', "'");
    }
    return [];
  }

  $tiposDocumento = obtenerValoresEnum($pdo, 'usuario', 'Tipo_Documento');
  ?>
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
      <h2>Crear Cuenta</h2>
      <form action="register_action.php" method="POST" onsubmit="return validarContrasenas()">
        <div class="input-group">
          <i class="fas fa-user"></i>
          <input type="text" id="reg-name" name="name" placeholder="Nombres" required />
        </div>
        <div class="input-group">
          <i class="fas fa-envelope"></i>
          <input type="email" id="reg-email" name="email" placeholder="Correo Electrónico" required />
        </div>
        <div class="input-group">
          <select name="tipo_documento" required>
            <option value="">Tipo de Documento</option>
            <?php foreach ($tiposDocumento as $tipo): ?>
              <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="input-group">
          <i class="fas fa-id-card"></i>
          <input type="number" id="reg-id" name="id_number" placeholder="No. Identificación" required min="0" />
        </div>
        <div class="input-group password-group">
          <input type="password" id="reg-password" name="password" placeholder="Contraseña" required minlength="8" />
          <span class="toggle-password" data-target="reg-password">
            <i class="fas fa-eye"></i>
          </span>
        </div>
        <div class="input-group password-group">
          <input type="password" id="reg-password2" placeholder="Repetir Contraseña" required />
          <span class="toggle-password" data-target="reg-password2">
            <i class="fas fa-eye"></i>
          </span>
        </div>
        <button class="auth-btn" type="submit">Crear Cuenta</button>
      </form>
      <p>¿Ya tienes cuenta? <a href="Login.php">Iniciar sesión</a></p>
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
    document.querySelectorAll('.toggle-password').forEach(btn => {
      btn.addEventListener('click', () => {
        const pass1 = document.getElementById('reg-password');
        const pass2 = document.getElementById('reg-password2');
        const icon1 = document.querySelector('[data-target="reg-password"] i');
        const icon2 = document.querySelector('[data-target="reg-password2"] i');

        if (pass1.type === 'password' || pass2.type === 'password') {
          pass1.type = 'text';
          pass2.type = 'text';
          icon1.classList.replace('fa-eye', 'fa-eye-slash');
          icon2.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
          pass1.type = 'password';
          pass2.type = 'password';
          icon1.classList.replace('fa-eye-slash', 'fa-eye');
          icon2.classList.replace('fa-eye-slash', 'fa-eye');
        }
      });
    });

    function validarContrasenas() {
      const pass1 = document.getElementById('reg-password').value;
      const pass2 = document.getElementById('reg-password2').value;
      if (pass1.length < 8) {
        alert('La contraseña debe tener al menos 8 caracteres.');
        return false;
      }
      if (pass1 !== pass2) {
        alert('Las contraseñas no coinciden.');
        return false;
      }
      return true;
    }
  </script>

</body>
</html>
