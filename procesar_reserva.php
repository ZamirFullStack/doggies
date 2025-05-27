<?php
// servicios.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Servicios - Doggies</title>
  <link rel="stylesheet" href="css/servicios.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
  <!-- Header -->
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
    <section>
      <h1>Nuestros Servicios</h1>
      <div class="productos-grid">
        <!-- Guardería -->
        <div class="producto-card">
          <img src="img/servicios/guarderia.jpg" alt="Guardería">
          <div class="producto-info">
            <h3>Hotel y Guardería Canina</h3>
            <p>Cuidado diario para tu peludo amigo.</p>
            <span>$15.000 COP / día</span>
            <button onclick="abrirModal('guarderia')">Reservar</button>
          </div>
        </div>
        <!-- Peluquería -->
        <div class="producto-card">
          <img src="img/servicios/peluqueria.jpg" alt="Peluquería">
          <div class="producto-info">
            <h3>Peluquería Canina</h3>
            <p>Corte profesional para cualquier raza.</p>
            <span>$50.000 COP / corte</span>
            <button onclick="abrirModal('peluqueria')">Reservar</button>
          </div>
        </div>
        <!-- Paseo canino -->
        <div class="producto-card">
          <img src="img/servicios/paseo.jpg" alt="Paseo Canino">
          <div class="producto-info">
            <h3>Paseo y Adiestramiento Canino</h3>
            <p>Salidas diarias durante el mes.</p>
            <span>$130.000 COP / mes</span>
            <button onclick="abrirModal('paseo')">Reservar</button>
          </div>
        </div>
        <!-- Asesoría -->
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
    </section>
  </main>

  <!-- Modal de Reserva -->
  <div id="modalReserva" class="modal">
    <div class="modal-content">
      <span class="close" onclick="cerrarModal()">&times;</span>
      <div class="modal-form">
        <h2 id="tituloServicio">Reserva</h2>
        <form id="formReserva" action="procesar_reserva.php" method="POST">
          <input type="hidden" name="tipo_servicio" id="tipoServicio" value="">
          <label for="nombre">Nombre del Cliente:</label>
          <input type="text" id="nombre" name="nombre" required>
          <label for="telefono">Teléfono:</label>
          <input type="tel" id="telefono" name="telefono" required>
          <label for="nombre_mascota">Nombre de la Mascota:</label>
          <input type="text" id="nombre_mascota" name="nombre_mascota" required>
          <label for="raza">Raza:</label>
          <input type="text" id="raza" name="raza" required>
          <label for="edad">Edad:</label>
          <input type="number" id="edad" name="edad" required>
          <div id="camposAdicionales"></div>
          <label for="observaciones">Observaciones:</label>
          <textarea id="observaciones" name="observaciones"></textarea>
          <button type="submit">Enviar Reserva</button>
        </form>
      </div>
      <div class="modal-video">
        <video id="videoServicio" controls>
          <source src="" type="video/mp4">
          Tu navegador no soporta el elemento de video.
        </video>
      </div>
    </div>
  </div>


  <script>
    function abrirModal(tipo) {
      document.getElementById('modalReserva').style.display = 'block';
      document.getElementById('tipoServicio').value = tipo;
      document.getElementById('tituloServicio').textContent = 'Reserva para ' + tipo.charAt(0).toUpperCase() + tipo.slice(1);
      const video = document.getElementById('videoServicio');
      const campos = document.getElementById('camposAdicionales');
      campos.innerHTML = '';
      if (tipo === 'guarderia') {
        campos.innerHTML += `<label for="fecha_entrada">Fecha Entrada:</label><input type="date" name="fecha_entrada" required>`;
        campos.innerHTML += `<label for="fecha_salida">Fecha Salida:</label><input type="date" name="fecha_salida" required>`;
      } else if (tipo === 'peluqueria') {
        campos.innerHTML += `<label for="tiene_nudos">¿Tiene nudos?</label><select name="tiene_nudos"><option value="no">No</option><option value="sí">Sí</option></select>`;
        campos.innerHTML += `<label for="pelo_largo">¿Pelo largo?</label><select name="pelo_largo"><option value="no">No</option><option value="sí">Sí</option></select>`;
      } else if (tipo === 'paseo') {
        campos.innerHTML += `<label for="cantidad_perros">Cantidad de perros:</label><input type="number" name="cantidad_perros" required>`;
      }
      video.src = 'videos/' + tipo + '.mp4';
    }

    function cerrarModal() {
      document.getElementById('modalReserva').style.display = 'none';
      document.getElementById('formReserva').reset();
    }
  </script>
</body>
</html>
