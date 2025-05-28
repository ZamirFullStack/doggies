<?php
session_start();

// Verificar si el usuario ha iniciado sesión
$usuario = $_SESSION['usuario'] ?? null;

// Verificar si hay productos en el carrito
if (empty($_SESSION['carrito'])) {
    echo "<h3>Tu carrito está vacío.</h3>";
    exit;
}

$carrito = $_SESSION['carrito'];
$total = 0;
$iva_porcentaje = 0.19; // 19% de IVA
$envio = 10000; // Valor fijo de envío en COP
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Checkout - Doggies</title>
  <link rel="stylesheet" href="css/Login.css">
  <link rel="stylesheet" href="css/carrito.css">
  <style>
    .checkout-container {
      max-width: 1000px;
      margin: auto;
      padding: 1rem;
    }
    .checkout-form, .order-summary {
      background: #f9f9f9;
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 2rem;
    }
    .checkout-form .input-group {
      margin-bottom: 1rem;
    }
    .checkout-form label {
      display: block;
      margin-bottom: 0.5rem;
    }
    .checkout-form input, .checkout-form select, .checkout-form textarea {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .order-summary table {
      width: 100%;
      border-collapse: collapse;
    }
    .order-summary th, .order-summary td {
      border: 1px solid #ccc;
      padding: 0.5rem;
      text-align: left;
    }
    .order-summary th {
      background-color: #f0f0f0;
    }
    .boton-comprar {
      display: inline-block;
      padding: 0.75rem 1.5rem;
      background-color: #007bff;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      text-align: center;
      border: none;
      cursor: pointer;
    }
    .login-prompt {
      text-align: center;
      margin-bottom: 1rem;
    }
  </style>
  <script>
    // Datos de departamentos y ciudades
    const departamentos = {
      "Nariño": ["Pasto", "Ipiales", "Tumaco"],
      "Cundinamarca": ["Bogotá", "Soacha", "Chía"],
      "Antioquia": ["Medellín", "Envigado", "Bello"]
      // Agrega más departamentos y ciudades según sea necesario
    };

    function actualizarCiudades() {
      const deptoSelect = document.getElementById("departamento");
      const ciudadSelect = document.getElementById("ciudad");
      const ciudades = departamentos[deptoSelect.value] || [];
      ciudadSelect.innerHTML = "";
      ciudades.forEach(ciudad => {
        const option = document.createElement("option");
        option.value = ciudad;
        option.textContent = ciudad;
        ciudadSelect.appendChild(option);
      });
    }

    document.addEventListener("DOMContentLoaded", () => {
      document.getElementById("departamento").addEventListener("change", actualizarCiudades);
      actualizarCiudades();
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

  <main class="checkout-container">
    <h2>Finalizar Compra</h2>

    <?php if (!$usuario): ?>
      <div class="login-prompt">
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
      </div>
    <?php endif; ?>

    <div class="order-summary">
      <h3>Resumen del Pedido</h3>
      <table>
        <thead>
          <tr>
            <th>Producto</th>
            <th>Imagen</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($carrito as $producto): 
            $subtotal = $producto['precio'] * $producto['cantidad'];
            $total += $subtotal;
          ?>
            <tr>
              <td><?= htmlspecialchars($producto['nombre']) ?></td>
              <td><img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" width="50"></td>
              <td>$<?= number_format($producto['precio'], 0, ',', '.') ?></td>
              <td><?= $producto['cantidad'] ?></td>
              <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <?php
            $subtotal_sin_iva = $total / (1 + $iva_porcentaje);
            $iva = $total - $subtotal_sin_iva;
            $total_con_envio = $total + $envio;
          ?>
          <tr>
            <th colspan="4" style="text-align: right;">Subtotal (sin IVA):</th>
            <td>$<?= number_format($subtotal_sin_iva, 0, ',', '.') ?></td>
          </tr>
          <tr>
            <th colspan="4" style="text-align: right;">IVA (19%):</th>
            <td>$<?= number_format($iva, 0, ',', '.') ?></td>
          </tr>
          <tr>
            <th colspan="4" style="text-align: right;">Envío:</th>
            <td>$<?= number_format($envio, 0, ',', '.') ?></td>
          </tr>
          <tr>
            <th colspan="4" style="text-align: right;">Total:</th>
            <td><strong>$<?= number_format($total_con_envio, 0, ',', '.') ?></strong></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <form class="checkout-form" method="POST" action="pagar_carrito.php">
      <h3>Datos del Comprador</h3>
      <div class="input-group">
        <label for="nombre">Nombre completo</label>
        <input type="text" name="nombre" id="nombre" required>
      </div>
      <div class="input-group">
        <label for="correo">Correo electrónico</label>
        <input type="email" name="correo" id="correo" required>
      </div>
      <div class="input-group">
        <label for="departamento">Departamento</label>
        <select name="departamento" id="departamento" required>
          <?php foreach (array_keys($departamentos) as $depto): ?>
            <option value="<?= $depto ?>"><?= $depto ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="input-group">
        <label for="ciudad">Ciudad</label>
        <select name="ciudad" id="ciudad" required>
          <!-- Las opciones se llenan dinámicamente con JavaScript -->
        </select>
      </div>
      <div class="input-group">
        <label for="barrio">Barrio</label>
        <input type="text" name="barrio" id="barrio" required>
      </div>
      <div class="input-group">
        <label for="direccion">Dirección exacta</label>
        <textarea name="direccion" id="direccion" required></textarea>
      </div>
      <div class="input-group">
        <input type="checkbox" name="informacion" id="informacion">
        <label for="informacion">Deseo recibir información relevante</label>
      </div>
      <div class="input-group">
        <input type="checkbox" name="terminos" id="terminos" required>
        <label for="terminos">Acepto los términos y condiciones</label>
      </div>
      <button type="submit" class="boton-comprar">Proceder al pago</button>
    </form>
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
</body>
</html>
