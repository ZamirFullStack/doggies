<?php
session_start();

if (empty($_SESSION['carrito'])) {
    echo "<h3>Tu carrito está vacío.</h3>";
    exit;
}

$carrito = $_SESSION['carrito'];
$total = 0;

require_once 'conexion.php';

function obtenerValoresEnum($conexion, $tabla, $columna) {
    $sql = "SHOW COLUMNS FROM `$tabla` LIKE '$columna'";
    $resultado = mysqli_query($conexion, $sql);
    if ($resultado) {
        $fila = mysqli_fetch_assoc($resultado);
        if (preg_match("/^enum\((.*)\)\$/", $fila['Type'], $matches)) {
            $valores = str_getcsv($matches[1], ',', "'");
            return $valores;
        }
    }
    return [];
}

$tiposDocumento = obtenerValoresEnum($conexion, 'usuario', 'Tipo_Documento');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finalizar Compra - Doggies</title>
  <link rel="stylesheet" href="css/Login.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
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
      <table>
        <thead>
          <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
          <?php foreach ($carrito as $producto): 
            $img = !empty($producto['imagen']) ? htmlspecialchars($producto['imagen']) : 'img/Productos/default.jpg';
            $subtotal = $producto['precio'] * $producto['cantidad'];
            $total += $subtotal;
          ?>
          <tr>
            <td class="product-summary">
              <img src="<?= $img ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
              <span><?= htmlspecialchars($producto['nombre']) ?></span>
            </td>
            <td><?= $producto['cantidad'] ?></td>
            <td>$<?= number_format($producto['precio'], 0, ',', '.') ?></td>
            <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <?php
          $iva = $total * 0.05;
          $envio = 10000;
          $totalConTodo = $total + $iva + $envio;
        ?>
        <tfoot>
          <tr><td colspan="3">Subtotal (sin IVA):</td><td>$<?= number_format($total, 0, ',', '.') ?></td></tr>
          <tr><td colspan="3">IVA (5%):</td><td>$<?= number_format($iva, 0, ',', '.') ?></td></tr>
          <tr><td colspan="3">Envío:</td><td>$<?= number_format($envio, 0, ',', '.') ?></td></tr>
          <tr><td colspan="3"><strong>Total de tu compra:</strong></td><td><strong>$<?= number_format($totalConTodo, 0, ',', '.') ?></strong></td></tr>
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