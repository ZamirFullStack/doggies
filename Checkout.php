<?php
session_start();

if (empty($_SESSION['carrito'])) {
    echo "<h3>Tu carrito está vacío.</h3>";
    exit;
}

$carrito = $_SESSION['carrito'];
$total = 0;

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
    die("❌ Error de conexión: " . $e->getMessage());
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
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finalizar Compra - Doggies</title>
  <link rel="stylesheet" href="css/Login.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    body {
      background-color: #f5f5f5;
      font-family: 'Roboto', sans-serif;
      min-height: 100vh;
    }
    /* Ajuste importante: deja espacio para el header fijo */
    .checkout-container {
      max-width: 1200px;
      margin: 0 auto 2rem auto;
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 2rem;
      padding-top: 100px; /* <-- deja espacio para el header fijo */
      box-sizing: border-box;
    }
    @media (max-width: 900px) {
      .checkout-container {
        grid-template-columns: 1fr;
        padding-top: 90px;
        gap: 2rem;
      }
      .summary-box {
        min-width: unset;
        max-width: 100%;
        margin-top: 2rem;
      }
    }
    @media (max-width: 600px) {
      .checkout-container {
        padding: 90px 0.5rem 2rem 0.5rem;
      }
      .summary-box, .checkout-form {
        padding: 1rem;
      }
    }
    /* Resumen del pedido */
    .summary-box {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
      max-width: 480px;
      min-width: 350px;
      overflow-x: hidden;
      font-family: 'Roboto', sans-serif;
    }
    .summary-box h3 {
      font-size: 1.25rem;
      margin-bottom: 1rem;
      color: #333;
    }
    .summary-box table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }
    .summary-box th,
    .summary-box td {
      padding: 0.6rem 0.4rem;
      font-size: 0.95rem;
      text-align: left;
      vertical-align: middle;
    }
    .summary-box th {
      color: #555;
      border-bottom: 1px solid #ddd;
    }
    .product-summary {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      overflow: hidden;
    }
    .product-summary img {
      width: 50px;
      height: 50px;
      object-fit: cover;
      flex-shrink: 0;
      border-radius: 6px;
    }
    .product-summary span {
      display: block;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 140px;
      font-weight: 500;
    }
    input.cantidad {
      width: 55px;
      padding: 4px;
      text-align: center;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-weight: bold;
      background: #f9f9f9;
    }
    /* Estilo para el resumen de totales */
    #totales td {
      padding: 0.4rem 0;
      font-size: 0.95rem;
    }
    #totales tr:last-child td {
      font-weight: bold;
      font-size: 1rem;
      color: #222;
    }
    #totales td:last-child {
      text-align: right;
    }
    .checkout-form, .summary-box {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
      padding: .5rem;
    }
    .product-summary {
      display: flex;
      align-items: center;
    }
    .product-summary img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      margin-right: 1rem;
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
    footer {
      background-color: rgba(51,51,51,0.95);
      color: #fff;
      text-align: center;
      padding: 1.5em 2em;
      width: 100%;
      margin-top: auto;
      position: relative;
    }
    .footer-content h3 {
      font-size: 1.4em;
      margin-bottom: 0.5em;
    }
    .social-links {
      display: flex;
      justify-content: center;
      gap: 1em;
    }
    .social-links a {
      color: #fff;
      font-size: 1.5rem;
      transition: color 0.3s ease;
    }
    .social-links a:hover {
      color: #ffd700;
    }
  </style>
  <script>
    let departamentosData = [];

    async function cargarDepartamentos() {
      try {
        const response = await fetch('departamentos.json');
        departamentosData = await response.json();
        const departamentoSelect = document.getElementById('departamento');
        departamentosData.forEach(depto => {
          const option = document.createElement('option');
          option.value = depto.departamento;
          option.textContent = depto.departamento;
          departamentoSelect.appendChild(option);
        });
      } catch (err) {
        console.error('Error cargando departamentos:', err);
      }
    }

    function actualizarCiudades() {
      const departamento = document.getElementById('departamento').value;
      const ciudadSelect = document.getElementById('ciudad');
      ciudadSelect.innerHTML = '';
      const depto = departamentosData.find(d => d.departamento === departamento);
      if (depto) {
        depto.ciudades.forEach(ciudad => {
          const option = document.createElement('option');
          option.value = ciudad;
          option.textContent = ciudad;
          ciudadSelect.appendChild(option);
        });
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      cargarDepartamentos();
      document.getElementById('departamento').addEventListener('change', actualizarCiudades);
    });
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

  <!-- Aquí va el padding para separar el header fijo del contenido -->
  <div class="checkout-container">
    <form class="checkout-form" method="POST" action="pagar_carrito.php">
      <h3>1. Dirección de envío</h3>
      <div class="input-group">
        <label for="email">Correo electrónico</label>
        <input type="email" name="email" required />
      </div>
      <div class="input-group">
        <label for="departamento">Departamento</label>
        <select name="departamento" id="departamento" required></select>
      </div>
      <div class="input-group">
        <label for="ciudad">Ciudad</label>
        <select name="ciudad" id="ciudad" required></select>
      </div>
      <div class="input-group">
        <label for="direccion">Dirección exacta</label>
        <input type="text" name="direccion" required />
      </div>
      <div class="input-group">
        <label for="barrio">Barrio</label>
        <input type="text" name="barrio" required />
      </div>

      <h3>2. Datos personales</h3>
      <div class="input-group">
        <label>Nombre</label>
        <input type="text" name="nombre" required />
      </div>
      <div class="input-group">
        <label>Apellidos</label>
        <input type="text" name="apellidos" required />
      </div>
      <div class="input-group">
        <label for="tipo_documento">Tipo de documento</label>
        <select name="tipo_documento" id="tipo_documento" required>
          <option value="">Seleccione</option>
          <?php foreach ($tiposDocumento as $tipo): ?>
            <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-group">
        <label>Número de documento</label>
        <input type="text" name="numero_documento" required />
      </div>
      <div class="input-group">
        <label>Teléfono</label>
        <input type="text" name="telefono" required />
      </div>

      <div class="checkboxes">
        <label><input type="checkbox" name="info" /> Deseo recibir información relevante</label>
        <label><input type="checkbox" name="terminos" required /> Acepto los términos y condiciones</label>
      </div>
      <button type="submit" class="btn-primary">Realizar pedido</button>
    </form>

    <div class="summary-box">
      <h3>3. Resumen del pedido</h3>
      <table id="resumen-pedido">
        <thead>
          <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
          <?php foreach ($carrito as $index => $producto): 
            $stmt = $pdo->prepare("SELECT Imagen_URL FROM producto WHERE Nombre = ?");
            $stmt->execute([$producto['nombre']]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $imgRuta = $fila ? $fila['Imagen_URL'] : 'img/Productos/default.jpg';
            ?>
          <tr>
            <td class="product-summary">
              <img src="<?= htmlspecialchars($imgRuta) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
              <span><?= htmlspecialchars($producto['nombre']) ?></span>
            </td>
            <td><input type="number" class="cantidad" value="<?= $producto['cantidad'] ?>" min="1" max="25" data-precio="<?= $producto['precio'] ?>"></td>
            <td>$<?= number_format($producto['precio'], 0, ',', '.') ?></td>
            <td class="subtotal">$<?= number_format($producto['precio'] * $producto['cantidad'], 0, ',', '.') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot id="totales">
          <!-- Totales se actualizarán por JavaScript -->
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
        <a href="mailto:doggiespasto22@gmail.com"><i class="fas fa-envelope"></i></a>
      </div>
    </div>
  </footer>
<script>
  document.querySelectorAll('.cantidad').forEach(input => {
    input.addEventListener('input', () => {
      let valor = parseInt(input.value);
      
      if (isNaN(valor) || valor < 1) {
        alert("La cantidad mínima permitida es 1.");
        input.value = 1;
      } else if (valor > 25) {
        alert("La cantidad máxima permitida es 25.");
        input.value = 25;
      }

      actualizarResumen();
    });
  });

  function actualizarResumen() {
    let total = 0;
    const rows = document.querySelectorAll('#resumen-pedido tbody tr');
    rows.forEach(row => {
      const cantidadInput = row.querySelector('.cantidad');
      const precio = parseFloat(cantidadInput.dataset.precio);
      let cantidad = parseInt(cantidadInput.value);

      if (isNaN(cantidad) || cantidad < 1) {
        cantidad = 1;
        cantidadInput.value = 1;
      } else if (cantidad > 25) {
        cantidad = 25;
        cantidadInput.value = 25;
      }

      const subtotal = precio * cantidad;
      row.querySelector('.subtotal').textContent = '$' + subtotal.toLocaleString('es-CO');
      total += subtotal;
    });

    const iva = total * 0.05;
    const envio = 15000;
    const totalConTodo = total + iva + envio;

    document.getElementById('totales').innerHTML = `
      <tr><td colspan="3">Subtotal (sin IVA):</td><td>$${total.toLocaleString('es-CO')}</td></tr>
      <tr><td colspan="3">IVA (5%):</td><td>$${iva.toLocaleString('es-CO')}</td></tr>
      <tr><td colspan="3">Envío:</td><td>$${envio.toLocaleString('es-CO')}</td></tr>
      <tr><td colspan="3"><strong>Total de tu compra:</strong></td><td><strong>$${totalConTodo.toLocaleString('es-CO')}</strong></td></tr>
    `;
  }

  actualizarResumen();
</script>
</body>
</html>
