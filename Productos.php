<?php
session_start();

if (!isset($_SESSION['carrito'])) {
  $_SESSION['carrito'] = [];
}

if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['nombre'], $_POST['precio'], $_POST['cantidad'], $_POST['imagen'])
) {
  $producto = [
    'nombre' => $_POST['nombre'],
    'precio' => floatval($_POST['precio']),
    'cantidad' => intval($_POST['cantidad']),
    'imagen' => $_POST['imagen']
  ];

  $_SESSION['carrito'][] = $producto;
  header("Location: carrito.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Productos - Doggies</title>
  <link rel="stylesheet" href="css/Productos.css" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li class="carrito"><a href="carrito.php"><i class="fas fa-cart-shopping"></i> Carrito</a></li>
      </ul>
    </nav>
  </header>

  <div class="page-container">
    <aside class="filtro">
      <h2>Filtrar por edad</h2>
      <label><input type="checkbox" class="filtro-edad" value="cachorro"> Cachorro</label>
      <label><input type="checkbox" class="filtro-edad" value="adulto"> Adulto</label>
      <label><input type="checkbox" class="filtro-edad" value="senior"> Senior</label>

      <h2>Precio</h2>
      <label>Min: <input type="number" id="precio-min" /></label>
      <label>Max: <input type="number" id="precio-max" /></label>
      <button onclick="filtrarProductos()">Filtrar</button>
    </aside>

    <main class="productos-page">
      <div class="productos-grid">
        <?php
        require 'conexion.php';
        $stmt = $pdo->query("SELECT * FROM producto");
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($productos as $p) {
          $nombre = $p['Nombre'];
          $imagen = $p['Imagen_URL'] ?? 'img/default.jpg';
          $descripcion = $p['Descripcion'];
          $precio = $p['Precio'];
          $stock = $p['Stock'];
          $edad = $p['edad'] ?? 'adulto';

$precioFormateado = number_format($precio, 0, ',', '.');

echo "
<div class='producto-card' data-edad='$edad' data-precio='$precio'>
  <img src='$imagen' alt='$nombre'>
  <div class='producto-info'>
    <h3>$nombre</h3>
    <p>$descripcion</p>
    <span>\$$precioFormateado</span>";

          if ($stock > 0) {
            echo "
              <div class='cantidad-comprar'>
                <button onclick='cambiarCantidad(this, -1)'>−</button>
                <input type='number' value='1' min='1' max='$stock' readonly />
                <button onclick='cambiarCantidad(this, 1)'>+</button>
              </div>
              <form method='POST'>
                <input type='hidden' name='nombre' value='$nombre' />
                <input type='hidden' name='precio' value='$precio' />
                <input type='hidden' name='cantidad' value='1' class='input-cantidad' />
                <input type='hidden' name='imagen' value='$imagen' />
                <button type='submit' class='btn-comprar'>Comprar</button>
              </form>";
          } else {
            echo "<p class='agotado'>Agotado</p>";
          }

          echo "</div></div>";
        }
        ?>
      </div>
    </main>
  </div>

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

  <a href="https://wa.me/573001112233" class="whatsapp" target="_blank" title="Contáctanos por WhatsApp">
    <i class="fab fa-whatsapp"></i><span>Contacto</span>
  </a>

  <script>
    function cambiarCantidad(btn, cambio) {
      const input = btn.parentElement.querySelector('input[type="number"]');
      let valor = parseInt(input.value);
      valor += cambio;
      if (valor < 1) valor = 1;
      if (valor > parseInt(input.max)) valor = parseInt(input.max);
      input.value = valor;
    }

    document.querySelectorAll('.btn-comprar').forEach(btn => {
      btn.addEventListener('click', e => {
        const card = e.target.closest('.producto-card');
        const cantidad = card.querySelector('input[type="number"]').value;
        card.querySelector('input.input-cantidad').value = cantidad;
      });
    });

    function filtrarProductos() {
      const edadSeleccionada = Array.from(document.querySelectorAll('.filtro-edad:checked')).map(cb => cb.value);
      const min = parseInt(document.getElementById('precio-min').value) || 0;
      const max = parseInt(document.getElementById('precio-max').value) || Infinity;

      document.querySelectorAll('.producto-card').forEach(card => {
        const edad = card.getAttribute('data-edad');
        const precio = parseInt(card.getAttribute('data-precio'));

        const mostrar = (edadSeleccionada.length === 0 || edadSeleccionada.includes(edad)) && precio >= min && precio <= max;

        card.style.display = mostrar ? 'flex' : 'none';
      });
    }
  </script>
</body>
</html>
