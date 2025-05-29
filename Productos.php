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
    'cantidad' => max(1, min(25, intval($_POST['cantidad']))),
    'imagen' => $_POST['imagen']
  ];

  $_SESSION['carrito'][] = $producto;
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos - Doggies</title>
  <link rel="stylesheet" href="css/Productos.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg">
  <style>
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
    }
    .modal-contenido {
      background-color: #fff;
      margin: 5% auto;
      padding: 30px;
      width: 90%;
      max-width: 800px;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    }
    .modal-contenido h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    .modal-contenido table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    .modal-contenido th, .modal-contenido td {
      border: 1px solid #ccc;
      padding: 12px;
      text-align: center;
    }
    .modal-contenido th {
      background-color: #f4f4f4;
      font-weight: bold;
    }
    .modal-contenido img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      margin-right: 10px;
      vertical-align: middle;
    }
    .producto-descripcion {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    .modal-contenido button {
      margin: 5px;
      padding: 10px 20px;
      font-size: 14px;
      border: none;
      border-radius: 6px;
      background-color: #4caf50;
      color: white;
      cursor: pointer;
    }
    .modal-contenido button:hover {
      background-color: #4caf50;
    }
    .cantidad-input {
      width: 60px;
      text-align: center;
    }
    .subtotal {
      font-weight: bold;
      text-align: center;
      font-size: 16px;
      margin-bottom: 10px;
    }
    .carrito-animado {
      position: fixed;
      z-index: 2000;
      transition: transform 0.8s ease-in-out, opacity 0.8s;
      width: 60px;
      height: 60px;
      object-fit: cover;
    }
.agotado {
    color: red;
    font-weight: bold;
    margin-top: 10px;
}

  </style>
</head>
<body>
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li class="carrito" id="icono-carrito">
          <a href="carrito.php">
            <i class="fas fa-cart-shopping"></i> Carrito
            <span id="contador-carrito"><?php echo "(" . count($_SESSION['carrito']) . ")"; ?></span>
          </a>
        </li>
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

    echo "<div class='producto-card' data-edad='$edad' data-precio='$precio'>
        <img src='$imagen' alt='$nombre'>
        <div class='producto-info'>
            <h3>$nombre</h3>
            <p>$descripcion</p>
            <span>\$$precioFormateado</span>
            <div class='cantidad-comprar'>
                <button onclick='cambiarCantidad(this, -1)'>−</button>
                <input type='number' value='1' min='1' max='25' readonly />
                <button onclick='cambiarCantidad(this, 1)'>+</button>
            </div>";
    
    if ($stock > 0) {
        echo "<form method='POST'>
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

  <div id="modal-confirmacion" class="modal">
    <div class="modal-contenido">
      <h2>Producto agregado al carrito</h2>
      <table>
        <thead>
          <tr><th>Producto</th><th>Precio</th><th>Cantidad</th></tr>
        </thead>
        <tbody id="tabla-productos"></tbody>
      </table>
      <button id="continuar-comprando">Continuar comprando</button>
      <button id="editar-carrito">Editar carrito</button>
      <form action="Checkout.php" method="get" style="display:inline-block">
        <button type="submit">Finalizar compra</button>
      </form>
    </div>
  </div>

  <a href="https://wa.me/573001112233" class="whatsapp" target="_blank" title="Contáctanos por WhatsApp">
    <i class="fab fa-whatsapp"></i><span>Contacto</span>
  </a>

  <script>
    function cambiarCantidad(btn, cambio) {
      const input = btn.parentElement.querySelector('input[type="number"]');
      let valor = parseInt(input.value);
      valor += cambio;
      if (valor < 1) valor = 1;
      if (valor > 25) valor = 25;
      input.value = valor;
    }

    document.addEventListener('DOMContentLoaded', () => {
      const botonesComprar = document.querySelectorAll('.btn-comprar');
      const modal = document.getElementById('modal-confirmacion');
      const continuarBtn = document.getElementById('continuar-comprando');
      const editarBtn = document.getElementById('editar-carrito');
      const contadorCarrito = document.getElementById('contador-carrito');
      const tablaBody = document.getElementById('tabla-productos');
      const subtotalSin = document.getElementById('subtotal-sin');
      const subtotalCon = document.getElementById('subtotal-con');

      botonesComprar.forEach(boton => {
        boton.addEventListener('click', e => {
          e.preventDefault();
          const form = boton.closest('form');
          const card = boton.closest('.producto-card');
          const img = card.querySelector('img');
          const imgRect = img.getBoundingClientRect();
          const carrito = document.getElementById('icono-carrito');
          const carritoRect = carrito.getBoundingClientRect();

          const imgClone = img.cloneNode();
          imgClone.classList.add('carrito-animado');
          imgClone.style.top = `${imgRect.top}px`;
          imgClone.style.left = `${imgRect.left}px`;
          document.body.appendChild(imgClone);

          requestAnimationFrame(() => {
            imgClone.style.transform = `translate(${carritoRect.left - imgRect.left}px, ${carritoRect.top - imgRect.top}px) scale(0.2)`;
            imgClone.style.opacity = '0';
          });

          setTimeout(() => imgClone.remove(), 800);

          const datos = new FormData(form);
          const cantidad = parseInt(card.querySelector('input[type="number"]').value);
          datos.set('cantidad', cantidad);

          fetch('Productos.php', {
            method: 'POST',
            body: datos
          }).then(() => {
            modal.style.display = 'block';
            let cantidadActual = parseInt(contadorCarrito.textContent.replace(/\D/g, '')) || 0;
            cantidadActual += cantidad;
            contadorCarrito.textContent = `(${cantidadActual})`;

            const nombre = form.querySelector('input[name="nombre"]').value;
            const precio = parseFloat(form.querySelector('input[name="precio"]').value);
            const imagen = form.querySelector('input[name="imagen"]').value;

            tablaBody.innerHTML = '';
            let fila = document.createElement('tr');
            fila.innerHTML = `
              <td class='producto-descripcion'>
                <img src="${imagen}" alt="${nombre}"><span>${nombre}</span>
              </td>
              <td>$${precio.toLocaleString('es-CO')}</td>
              <td><input type='number' class='cantidad-input' min='1' max='25' value='${cantidad}' onchange='actualizarTotales(this, ${precio})'></td>
            `;
            tablaBody.appendChild(fila);
            actualizarSubtotal();
          });
        });
      });

      continuarBtn.addEventListener('click', () => {
        modal.style.display = 'none';
      });

      editarBtn.addEventListener('click', () => {
        window.location.href = 'carrito.php';
      });

      window.addEventListener('click', e => {
        if (e.target == modal) {
          modal.style.display = 'none';
        }
      });
    });

    function actualizarTotales(input, precio) {
      let cantidad = parseInt(input.value);
      if (cantidad < 1) cantidad = 1;
      if (cantidad > 25) cantidad = 25;
      input.value = cantidad;
      actualizarSubtotal();
    }

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
