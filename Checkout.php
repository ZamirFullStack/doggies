<?php
session_start();

if (empty($_SESSION['carrito'])) {
    echo "<h3>Tu carrito está vacío.</h3>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cantidades']) && is_array($_POST['cantidades'])) {
    foreach ($_POST['cantidades'] as $idx => $cantidad) {
        $idx = intval($idx);
        $cantidad = max(1, min(25, intval($cantidad)));
        if (isset($_SESSION['carrito'][$idx])) {
            $_SESSION['carrito'][$idx]['cantidad'] = $cantidad;
        }
    }
    header("Location: pagar_carrito.php");
    exit;
}

$carrito = $_SESSION['carrito'];

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
  .checkout-container {
    max-width: 1200px;
    margin: 0 auto 2rem auto;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    padding-top: 100px;
    box-sizing: border-box;
  }
  @media (max-width: 900px) {
    .checkout-container {
      grid-template-columns: 1fr;
      padding-top: 90px;
      gap: 2rem;
    }
    .summary-box {
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

  .summary-box {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
    min-width: 480px;
    max-width: 600px;
    width: 100%;
    overflow-x: auto;
    font-family: 'Roboto', sans-serif;
    transition: max-width 0.2s, min-width 0.2s;
  }
  .summary-box h3 {
    font-size: 1.45rem;
    margin-bottom: 1rem;
    color: #222;
    font-weight: 700;
  }
  .summary-box table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    margin-bottom: 0.6rem;
  }
  .summary-box th,
  .summary-box td {
    padding: 0.6rem 0.4rem;
    font-size: 1rem;
    text-align: left;
    vertical-align: middle;
  }
  .summary-box th {
    color: #2d2d2d;
    border-bottom: 1px solid #ddd;
    font-weight: 700;
  }
  .summary-box tr:not(:last-child) td {
    border-bottom: 1px solid #f1f1f1;
  }
  .summary-box td.product-summary {
    min-width: 170px;
    width: 55%;
    max-width: 340px;
  }

  /* --- PRODUCTO --- */
  .product-summary {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    min-width: 0;
    width: 100%;
  }
  .product-summary img {
    width: 54px;
    height: 54px;
    object-fit: contain;
    border-radius: 7px;
    flex-shrink: 0;
    background: #fff;
    border: 1px solid #ececec;
  }
  .product-summary span {
    font-weight: 500;
    font-size: 1.04rem;
    line-height: 1.23;
    word-break: break-word;
    display: block;
  }

  /* --- INPUT DE CANTIDAD --- */
  input.cantidad {
    width: 55px;
    padding: 4px;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-weight: bold;
    background: #f9f9f9;
  }

  /* --- TOTAL Y TOTALES --- */
  #totales td {
    padding: 0.4rem 0;
    font-size: 0.99rem;
  }
  #totales tr td:first-child {
    text-align: left;
  }
  #totales tr td:last-child {
    text-align: right;
  }
  #totales tr:last-child td {
    font-weight: bold;
    font-size: 1.15rem;
    color: #222;
  }

  /* ----------- MOBILE ADAPTACIÓN ----------- */
  @media (max-width: 650px) {
    .summary-box {
      min-width: unset;
      max-width: 98vw;
      width: 100%;
      padding: 1rem;
      overflow-x: visible;
    }
    .summary-box table,
    .summary-box thead,
    .summary-box tbody,
    .summary-box th,
    .summary-box td,
    .summary-box tr {
      display: block;
      width: 100%;
    }
    .summary-box thead {
      display: none;
    }
    .summary-box tbody tr {
      margin-bottom: 1.15rem;
      border-bottom: 1px solid #ececec;
      border-radius: 9px;
      background: #fafafc;
      box-shadow: 0 2px 7px #0001;
      padding: 0.6em 0.2em 0.8em 0.2em;
      display: block;
    }
    .summary-box td {
      border: none;
      background: #fff;
      padding: 0.38rem 0.18rem;
      position: relative;
      text-align: left;
    }
    .summary-box td[data-label]::before {
      content: attr(data-label) ": ";
      font-weight: 700;
      color: #666;
      margin-right: 8px;
      font-size: 1em;
      display: inline-block;
      width: 94px;
      min-width: 94px;
      vertical-align: top;
    }
    .product-summary {
      max-width: 94vw;
      gap: 0.4rem;
    }
    .product-summary img {
      width: 42px;
      height: 42px;
    }
    .product-summary span {
      font-size: 0.98rem;
      line-height: 1.12;
      word-break: break-word;
      white-space: normal;
    }
    /* Alinear precios y totales siempre a la derecha */
    #totales tr td {
      text-align: right !important;
      font-size: 1.06rem;
      padding: 0.32rem 0.12rem;
    }
    #totales tr td:first-child {
      text-align: left !important;
    }
  }

  /* ------ FORMULARIO Y FOOTER --------- */
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
    let ultimoEnvioCotizado = null;

    async function cargarDepartamentos() {
      const response = await fetch('departamentos.json');
      departamentosData = await response.json();
      const departamentoSelect = document.getElementById('departamento');
      departamentosData.forEach(depto => {
        const option = document.createElement('option');
        option.value = depto.departamento;
        option.textContent = depto.departamento;
        departamentoSelect.appendChild(option);
      });
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
      ciudadSelect.dispatchEvent(new Event('change'));
    }

    async function cotizarEnvio() {
      const departamento = document.getElementById('departamento').value;
      const ciudad = document.getElementById('ciudad').value;
      if (!departamento || !ciudad) return;

      document.getElementById('envio-mensaje').textContent = 'Calculando envío...';
      const resp = await fetch('cotizar_envio.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          departamento_destino: departamento,
          ciudad_destino: ciudad
        })
      });
      const data = await resp.json();
      if (data.ok) {
        ultimoEnvioCotizado = data.precio;
        document.getElementById('envio-mensaje').textContent = 'Envío: $' + data.precio.toLocaleString('es-CO');
        actualizarResumen();
      } else {
        ultimoEnvioCotizado = 0;
        document.getElementById('envio-mensaje').textContent = data.error || 'Error al cotizar envío';
        actualizarResumen();
      }
    }

    function actualizarResumen() {
      let total = 0;
      document.querySelectorAll('#resumen-pedido tbody tr').forEach(row => {
        const cantidadInput = row.querySelector('.cantidad');
        const precio = parseFloat(cantidadInput.dataset.precio);
        let cantidad = parseInt(cantidadInput.value);
        if (isNaN(cantidad) || cantidad < 1) cantidad = 1;
        else if (cantidad > 25) cantidad = 25;
        const subtotal = precio * cantidad;
        row.querySelector('.subtotal').textContent = '$' + subtotal.toLocaleString('es-CO');
        total += subtotal;
      });
      const iva = total * 0.05;
      const envio = ultimoEnvioCotizado !== null ? ultimoEnvioCotizado : 0;
      const totalConTodo = total + iva + envio;

      // Aquí actualizas los inputs ocultos:
      document.getElementById('campo_subtotal').value = total.toFixed(2);
      document.getElementById('campo_iva').value = iva.toFixed(2);
      document.getElementById('campo_envio').value = envio.toFixed(2);
      document.getElementById('campo_total').value = totalConTodo.toFixed(2);

      document.getElementById('totales').innerHTML = `
        <tr>
          <td colspan="3">Subtotal (sin IVA):</td>
          <td style="text-align:right;">$${total.toLocaleString('es-CO')}</td>
        </tr>
        <tr>
          <td colspan="3">IVA (5%):</td>
          <td style="text-align:right;">$${iva.toLocaleString('es-CO')}</td>
        </tr>
        <tr>
          <td colspan="3">Envío:</td>
          <td style="text-align:right;">${envio > 0 ? '$' + envio.toLocaleString('es-CO') : ''}</td>
        </tr>
        <tr>
          <td colspan="3"><strong>Total de tu compra:</strong></td>
          <td style="text-align:right;"><strong>$${totalConTodo.toLocaleString('es-CO')}</strong></td>
        </tr>
      `;
}


    document.addEventListener('DOMContentLoaded', () => {
      cargarDepartamentos();
      document.getElementById('departamento').addEventListener('change', actualizarCiudades);
      document.getElementById('ciudad').addEventListener('change', cotizarEnvio);

      setTimeout(actualizarResumen, 800);

      // Vincula evento de cambio a todos los inputs de cantidad para AJAX + actualización del resumen y envío
      document.querySelectorAll('#resumen-pedido .cantidad').forEach((input, idx) => {
        input.setAttribute('data-index', idx); // Asegura que cada input tenga su índice de carrito
        input.addEventListener('input', function () {
          let valor = parseInt(this.value);
          if (isNaN(valor) || valor < 1) {
            alert("La cantidad mínima permitida es 1.");
            this.value = 1;
          } else if (valor > 25) {
            alert("La cantidad máxima permitida es 25.");
            this.value = 25;
          }
          // AJAX para actualizar cantidad en la sesión PHP
          fetch('actualizar_cantidad.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'index=' + encodeURIComponent(this.dataset.index) + '&cantidad=' + encodeURIComponent(this.value)
          }).then(() => {
            actualizarResumen();
            cotizarEnvio();
          });
        });
      });
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
    <form class="checkout-form" method="POST" action="pagar_carrito.php" style="display: contents;">
      <div>
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
      </div>
      <div class="summary-box">
        <h3>3. Resumen del pedido</h3>
        <table id="resumen-pedido">
          <thead>
            <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
          </thead>
          <tbody>
          <?php foreach ($carrito as $index => $producto): 
              $imgStmt = $pdo->prepare("SELECT Imagen_URL FROM producto WHERE Nombre = ?");
              $imgStmt->execute([$producto['nombre']]);
              $imgFila = $imgStmt->fetch(PDO::FETCH_ASSOC);
              $imgRuta = $imgFila ? $imgFila['Imagen_URL'] : 'img/Productos/default.jpg';

              $nombre = htmlspecialchars($producto['nombre']);
              $presentacion = isset($producto['presentacion']) ? htmlspecialchars($producto['presentacion']) : '';
              $nombreCompleto = $nombre . ($presentacion ? " ({$presentacion})" : "");
          ?>
          <tr>
            <td class="product-summary" data-label="Producto">
              <img src="<?= htmlspecialchars($imgRuta) ?>" alt="<?= $nombreCompleto ?>">
              <span><?= $nombreCompleto ?></span>
            </td>
            <td data-label="Cantidad">
              <input type="number" class="cantidad" name="cantidades[<?= $index ?>]" value="<?= $producto['cantidad'] ?>" min="1" max="25" data-precio="<?= $producto['precio'] ?>" data-index="<?= $index ?>">
            </td>
            <td data-label="Precio">
              $<?= number_format($producto['precio'], 0, ',', '.') ?>
            </td>
            <td class="subtotal" data-label="Subtotal">
              $<?= number_format($producto['precio'] * $producto['cantidad'], 0, ',', '.') ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot id="totales"></tfoot>
        </table>
        <div id="envio-mensaje" style="margin-top:10px; font-weight:700; color:#227a38;"></div>
      <!-- Checkboxes y botón de pedido al final del resumen -->
      <div class="checkboxes" style="margin-top: 1.2em;">
        <label><input type="checkbox" name="info" /> Deseo recibir información relevante</label>
        <label><input type="checkbox" name="terminos" required /> Acepto los términos y condiciones</label>
      </div>
      <input type="hidden" id="campo_subtotal" name="subtotal" value="">
      <input type="hidden" id="campo_iva" name="iva" value="">
      <input type="hidden" id="campo_envio" name="envio" value="">
      <input type="hidden" id="campo_total" name="total" value="">
      <button type="submit" class="btn-primary" style="margin-top: 1em;">Realizar pedido</button>
      </div>
    </form>
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
</body>
</html>
