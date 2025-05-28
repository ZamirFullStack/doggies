<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finalizar Compra - Doggies</title>
  <link rel="stylesheet" href="css/Login.css" />
  <style>
    body {
      background-color: #f5f5f5;
      font-family: 'Roboto', sans-serif;
    }
    .checkout-container {
      max-width: 1200px;
      margin: 2rem auto;
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 2rem;
    }
    .checkout-form, .summary-box {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .checkout-form h3, .summary-box h3 {
      margin-bottom: 1rem;
      border-bottom: 1px solid #ccc;
      padding-bottom: .5rem;
    }
    .input-group {
      margin-bottom: 1rem;
    }
    .input-group label {
      font-weight: bold;
      display: block;
      margin-bottom: .3rem;
    }
    .input-group input, .input-group select, .input-group textarea {
      width: 100%;
      padding: .5rem;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .checkboxes label {
      display: flex;
      align-items: center;
      margin-bottom: .5rem;
    }
    .checkboxes input {
      margin-right: .5rem;
    }
    .summary-box table {
      width: 100%;
      border-collapse: collapse;
    }
    .summary-box th, .summary-box td {
      text-align: left;
      padding: .5rem 0;
    }
    .summary-box tfoot tr td {
      font-weight: bold;
    }
    .btn-primary {
      background: #28a745;
      color: white;
      border: none;
      padding: 1rem;
      width: 100%;
      font-size: 1rem;
      border-radius: 5px;
      cursor: pointer;
    }
  </style>
  <script>
    let departamentosCiudades = [];

    async function cargarDepartamentos() {
      try {
        const response = await fetch('departamentos.json');
        departamentosCiudades = await response.json();

        const depSelect = document.getElementById('departamento');
        depSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
        departamentosCiudades.forEach(dep => {
          const option = document.createElement('option');
          option.value = dep.departamento;
          option.textContent = dep.departamento;
          depSelect.appendChild(option);
        });
      } catch (error) {
        console.error('Error al cargar departamentos:', error);
      }
    }

    function actualizarCiudades() {
      const dep = document.getElementById('departamento').value;
      const ciudadSelect = document.getElementById('ciudad');
      ciudadSelect.innerHTML = '<option value="">Seleccione una ciudad</option>';

      const departamento = departamentosCiudades.find(d => d.departamento === dep);
      if (departamento) {
        departamento.ciudades.forEach(ciudad => {
          const option = document.createElement('option');
          option.value = ciudad;
          option.textContent = ciudad;
          ciudadSelect.appendChild(option);
        });
      }
    }

    document.addEventListener('DOMContentLoaded', cargarDepartamentos);
  </script>
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

  <div class="checkout-container">
    <form class="checkout-form" method="POST" action="pagar_carrito.php">
      <h3>1. Dirección de envío</h3>
      <div class="input-group">
        <label for="email">Correo electrónico</label>
        <input type="email" name="email" id="email" required />
      </div>
      <div class="input-group">
        <label for="departamento">Departamento</label>
        <select name="departamento" id="departamento" onchange="actualizarCiudades()" required></select>
      </div>
      <div class="input-group">
        <label for="ciudad">Ciudad</label>
        <select name="ciudad" id="ciudad" required></select>
      </div>
      <div class="input-group">
        <label for="direccion">Dirección exacta</label>
        <input type="text" name="direccion" id="direccion" required />
      </div>
      <div class="input-group">
        <label for="barrio">Barrio</label>
        <input type="text" name="barrio" id="barrio" required />
      </div>

      <h3>2. Datos personales</h3>
      <div class="input-group">
        <label for="nombre">Nombre completo</label>
        <input type="text" name="nombre" id="nombre" required />
      </div>
      <div class="input-group">
        <label for="telefono">Teléfono</label>
        <input type="text" name="telefono" id="telefono" required />
      </div>

      <div class="checkboxes">
        <label><input type="checkbox" name="info" /> Deseo recibir información relevante</label>
        <label><input type="checkbox" name="terminos" required /> Acepto los términos y condiciones</label>
      </div>
      <button type="submit" class="btn-primary">Realizar pedido</button>
    </form>

    <div class="summary-box">
      <h3>4. Resumen del pedido</h3>
      <table>
        <tr><td>Subtotal (sin IVA):</td><td>$323.577</td></tr>
        <tr><td>IVA incluido:</td><td>$16.179</td></tr>
        <tr><td>Valor del envío:</td><td>$10.000</td></tr>
        <tfoot>
          <tr><td>Total de tu compra:</td><td><strong>$349.756</strong></td></tr>
        </tfoot>
      </table>
    </div>
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
</body>
</html>
