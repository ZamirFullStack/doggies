<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Servicios - Doggies</title>
  <link rel="stylesheet" href="css/Login.css" />
  <link rel="stylesheet" href="css/servicios.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    /* Header fijo */
    header {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: 70px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .menu {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      max-width: 1200px;
      padding: 0 20px;
      list-style: none;
    }
    .menu li {
      flex: 1;
      text-align: center;
    }
    .menu li.logo {
      flex: 0 0 auto;
    }

    .menu li.logo a {
      display: block;
      width: 120px;   /* <-- Ajusta el ancho aquí */
      height: 50px;   /* <-- Ajusta la altura aquí */
      background-image: url('img/fondo.jpg');
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      text-indent: -9999px;
      margin: 0 auto;
    }

    .menu a {
      text-decoration: none;
      color: #333;
      font-weight: bold;
      font-size: 1rem;
      padding: 0.5em;
      display: inline-block;
    }
    .menu a:hover { color: #4caf50; }

    main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1rem 2rem 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      /* Quita el padding-top de 2rem aquí */
    }
    /* --- Título debajo del header, centrado y estático --- */
    .titulo-servicios {
      text-align: center;
      font-size: 2rem;
      font-weight: bold;
      margin: 0 auto 2rem auto;
      padding-top: 90px; /* justo debajo del header fijo */
      width: 100%;
      color: #222;
      background: transparent;
      z-index: 2;
      /* NO STICKY, NO FIXED */
    }
    /* Responsive: ajusta espacio si el header es más pequeño */
    @media (max-width: 600px) {
      header { height: 55px; }
      .titulo-servicios { padding-top: 65px; font-size: 1.15rem; }
      main { padding-left: 2vw; padding-right: 2vw; }
    }
  </style>
</head>
<body class="login-page">
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php"><i class="fas fa-dog"></i> Productos</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li><a href="carrito.php"><i class="fas fa-cart-shopping"></i> Carrito</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h1 class="titulo-servicios">Nuestros Servicios</h1>
    <div class="productos-grid">
      <div class="producto-card">
        <img src="img/servicios/guarderia.jpg" alt="Guardería">
        <div class="producto-info">
          <h3>Hotel y Guardería Canina</h3>
          <p>Cuidado diario para tu peludo amigo.</p>
          <span>$15.000 COP / día</span>
          <button onclick="abrirModal('guarderia')">Reservar</button>
        </div>
      </div>
      <div class="producto-card">
        <img src="img/servicios/peluqueria.jpg" alt="Peluquería">
        <div class="producto-info">
          <h3>Peluquería Canina</h3>
          <p>Corte profesional para cualquier raza.</p>
          <span>$50.000 COP / corte</span>
          <button onclick="abrirModal('peluqueria')">Reservar</button>
        </div>
      </div>
      <div class="producto-card">
        <img src="img/servicios/paseo.jpg" alt="Paseo Canino">
        <div class="producto-info">
          <h3>Paseo y Adiestramiento Canino</h3>
          <p>Salidas diarias durante el mes.</p>
          <span>$130.000 COP / mes</span>
          <button onclick="abrirModal('paseo')">Reservar</button>
        </div>
      </div>
      <div class="producto-card">
        <img src="img/servicios/asesoria.jpg" alt="Asesoría">
        <div class="producto-info">
          <h3>Asesoría Canina</h3>
          <p>Consejos y atención profesional.</p>
          <span>$20.000 COP / hora</span>
          <button onclick="abrirModal('asesoria')">Reservar</button>
        </div>
      </div>
    </div>
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

  <!-- MODAL SCRIPTS AQUÍ (igual que tu código original) -->
  <div id="modalReserva" class="modal">
    <div class="modal-content">
      <span class="close" onclick="cerrarModal()">&times;</span>
      <div class="modal-form">
        <h2 id="tituloServicio">Reserva</h2> <br/>
        <form id="formReserva">
          <input type="hidden" id="tipoServicio">
          <label>Nombre del Cliente:</label>
          <input type="text" id="nombre" required>
          <label>Teléfono:</label>
          <input type="tel" id="telefono" required>
          <label>Nombre de la Mascota:</label>
          <input type="text" id="nombre_mascota" required>
          <label>Raza:</label>
          <input type="text" id="raza" required>
          <label>Edad:</label>
          <input type="number" id="edad" required>
          <div id="camposAdicionales"></div>
          <label>Observaciones:</label>
          <textarea id="observaciones"></textarea>
          <button type="submit" class="auth-btn">Enviar a WhatsApp</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function abrirModal(tipo) {
      document.getElementById('modalReserva').style.display = 'flex';
      document.getElementById('tipoServicio').value = tipo;
      document.getElementById('tituloServicio').textContent = 'Reserva para ' + tipo.charAt(0).toUpperCase() + tipo.slice(1);
      const campos = document.getElementById('camposAdicionales');
      campos.innerHTML = '';
      if (tipo === 'guarderia') {
        campos.innerHTML += `<label>Fecha Entrada:</label><input type="date" id="fecha_entrada" required>`;
        campos.innerHTML += `<label>Fecha Salida:</label><input type="date" id="fecha_salida" required>`;
      } else if (tipo === 'peluqueria') {
        campos.innerHTML += `<label>¿Tiene nudos?</label><select id="tiene_nudos"><option value="no">No</option><option value="sí">Sí</option></select>`;
        campos.innerHTML += `<label>¿Pelo largo?</label><select id="pelo_largo"><option value="no">No</option><option value="sí">Sí</option></select>`;
      } else if (tipo === 'paseo') {
        campos.innerHTML += `<label>Cantidad de perros:</label><input type="number" id="cantidad_perros" required>`;
      }
    }

    function cerrarModal() {
      document.getElementById('modalReserva').style.display = 'none';
      document.getElementById('formReserva').reset();
    }

    document.getElementById('formReserva').addEventListener('submit', function(e) {
      e.preventDefault();
      const servicio = document.getElementById('tipoServicio').value;
      const nombre = document.getElementById('nombre').value;
      const telefono = document.getElementById('telefono').value;
      const mascota = document.getElementById('nombre_mascota').value;
      const raza = document.getElementById('raza').value;
      const edad = document.getElementById('edad').value;
      const observaciones = document.getElementById('observaciones').value;
      let mensaje = `Hola, quiero reservar el servicio de ${servicio}%0A` +
                    `Nombre: ${nombre}%0ATeléfono: ${telefono}%0AMascota: ${mascota}%0ARaza: ${raza}%0AEdad: ${edad}`;
      if (servicio === 'guarderia') {
        mensaje += `%0AEntrada: ${document.getElementById('fecha_entrada').value}`;
        mensaje += `%0ASalida: ${document.getElementById('fecha_salida').value}`;
      } else if (servicio === 'peluqueria') {
        mensaje += `%0ANudos: ${document.getElementById('tiene_nudos').value}`;
        mensaje += `%0APelo largo: ${document.getElementById('pelo_largo').value}`;
      } else if (servicio === 'paseo') {
        mensaje += `%0ACantidad de perros: ${document.getElementById('cantidad_perros').value}`;
      }
      mensaje += `%0AObservaciones: ${observaciones}`;
      window.open(`https://wa.me/573216734085?text=${mensaje}`, '_blank');
    });
  </script>
</body>
</html>
