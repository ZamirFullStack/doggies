<?php
session_start();

// --- Descomenta la siguiente línea SOLO si quieres limpiar el carrito manualmente y después vuelve a comentarla ---
// unset($_SESSION['carrito']);

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$url = 'mysql://root:AaynZNNKYegnXoInEgQefHggDxoRieEL@centerbeam.proxy.rlwy.net:58462/railway';
$dbparts = parse_url($url);
$host = $dbparts['host'];
$port = $dbparts['port'];
$user = $dbparts['user'];
$pass = $dbparts['pass'];
$db   = ltrim($dbparts['path'], '/');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('❌ Error de conexión: ' . $e->getMessage());
}

// --- AGREGAR AL CARRITO ---
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' &&
  isset($_POST['id_producto'], $_POST['id_presentacion'], $_POST['nombre'], $_POST['precio'], $_POST['cantidad'], $_POST['imagen'])
) {

    $nombre = $_POST['nombre'];
    $precio = floatval($_POST['precio']);
    $cantidad = max(1, min(25, intval($_POST['cantidad'])));
    $imagen = $_POST['imagen'];
    $id_presentacion = intval($_POST['id_presentacion']); // El id_presentacion viene desde POST

    // 1. Trae la presentación (para el peso y el ID_Producto)
    $stmt = $pdo->prepare("SELECT Peso, ID_Producto FROM presentacion WHERE ID_Presentacion = ?");
    $stmt->execute([$id_presentacion]);
    $present = $stmt->fetch(PDO::FETCH_ASSOC);

    // Extrae solo el número del peso (puede ser con o sin decimales)
    $peso = preg_replace('/[^\d.]/', '', $present['Peso']);
    if ($peso === '' || !is_numeric($peso)) {
        http_response_code(400);
        exit('Error: El campo Peso debe contener un número válido.');
    }
    $peso = floatval($peso); // Ahora siempre es numérico (en kilos)

    // Toma el ID de producto asociado a la presentación
    $id_producto = $present['ID_Producto'];

    // 2. Trae las dimensiones del producto asociado a la presentación
    $stmtProd = $pdo->prepare("SELECT alto_cm, largo_cm, ancho_cm FROM producto WHERE ID_Producto = ?");
    $stmtProd->execute([$id_producto]);
    $producto_dim = $stmtProd->fetch(PDO::FETCH_ASSOC);

    if (
        !$producto_dim ||
        empty($producto_dim['Alto_cm']) || empty($producto_dim['Largo_cm']) || empty($producto_dim['Ancho_cm']) ||
        !is_numeric($producto_dim['Alto_cm']) || !is_numeric($producto_dim['Largo_cm']) || !is_numeric($producto_dim['Ancho_cm'])
    ) {
        http_response_code(400);
        exit('Error: Faltan dimensiones (alto, largo, ancho) en el producto.');
    }

    $alto  = $producto_dim['Alto_cm'];
    $largo = $producto_dim['Largo_cm'];
    $ancho = $producto_dim['Ancho_cm'];

    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if (
            $item['nombre'] === $nombre &&
            $item['precio'] == $precio &&
            $item['imagen'] === $imagen &&
            intval($item['presentacion']) === $id_presentacion
        ) {
            $item['cantidad'] = min(25, $item['cantidad'] + $cantidad);
            $encontrado = true;
            break;
        }
    }
    unset($item);

    if (!$encontrado) {
    $_SESSION['carrito'][] = [
        'id_producto'   => $id_producto,       // agregado para que esté disponible
        'presentacion'  => $id_presentacion,   // id presentación, tal cual
        'nombre'        => $nombre,
        'precio'        => $precio,
        'cantidad'      => $cantidad,
        'imagen'        => $imagen,
        'peso'          => $peso,
        'largo'         => $largo,
        'ancho'         => $ancho,
        'alto'          => $alto,
    ];
    }
    exit;
}


// --- ELIMINAR DEL CARRITO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_index'])) {
    $idx = intval($_POST['delete_index']);
    if (isset($_SESSION['carrito'][$idx])) {
        unset($_SESSION['carrito'][$idx]);
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
    header('Location: carrito.php');
    exit;
}

// --- ACTUALIZAR CANTIDAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_index'], $_POST['cantidad'])) {
    $idx = intval($_POST['update_index']);
    $qty = max(1, min(25, intval($_POST['cantidad'])));
    if (isset($_SESSION['carrito'][$idx])) {
        $_SESSION['carrito'][$idx]['cantidad'] = $qty;
    }
    header('Location: carrito.php');
    exit;
}

$carrito = $_SESSION['carrito'];
$total = 0;

$tiposDocumento = ['Cédula de ciudadanía', 'Cédula de extranjería', 'Pasaporte', 'NIT', 'Tarjeta de identidad'];


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


#totales {
  border-top: 1.5px solid #d9d9d9;
}
#totales tr td {
  font-size: 1.08rem;
  padding: 0.45rem 0 0.45rem 0;
  color: #222;
  vertical-align: middle;
}
#totales tr:not(:last-child) td {
  font-weight: 500;
}
#totales tr:last-child td {
  font-size: 1.24rem;
  font-weight: 800;
  color: #222;
  border-top: 2px solid #d3d3d3;
  padding-top: 1em;
}
#totales td:first-child {
  text-align: left;
  padding-right: 0.8em;
  white-space: nowrap;
}
#totales td:last-child {
  text-align: right;
  white-space: nowrap;
  padding-left: 0.8em;
}
.summary-box {
  min-width: unset !important;  /* Elimina el min-width que genera el scroll raro */
  max-width: 600px;
  width: 100%;
  overflow-x: visible;
  margin-bottom: 1rem;
}
@media (max-width: 650px) {
  .summary-box {
    max-width: 98vw;
    min-width: unset !important;
    overflow-x: visible !important;
  }
  #totales td {
    font-size: 1rem;
  }
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

.resumen-totales {
  margin: 1.1em 0 0.6em 0;
  padding: 1.1em 0 0 0;
  border-top: 1.5px solid #d9d9d9;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.15em;
}

.resumen-totales .linea-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1.10rem;
  font-weight: 500;
  padding: 0.26em 0;
  color: #222;
}

.resumen-totales .total-pagar {
  font-size: 1.27rem;
  font-weight: bold;
  margin-top: 0.65em;
  color: #1b1b1b;
  border-top: 2px solid #d3d3d3;
  padding-top: 0.6em;
}

@media (max-width: 650px) {
  .resumen-totales {
    font-size: 1em;
    padding: 0.7em 0 0 0;
  }
  .resumen-totales .total-pagar {
    font-size: 1.08rem;
    padding-top: 0.3em;
  }
}


  </style>
<script>
let zipcodes = [];
let ultimoEnvioCotizado = 0;
let estimadoEntrega = "";

async function cargarDepartamentosZipcodes() {
    const resp = await fetch('zipcodes.co.json');
    zipcodes = await resp.json();
    const departamentosUnicos = [...new Set(zipcodes.map(item => item.state))].sort();
    const deptoSelect = document.getElementById('departamento');
    deptoSelect.innerHTML = '<option value="">Seleccione</option>';
    departamentosUnicos.forEach(depto => {
        let opt = document.createElement('option');
        opt.value = depto;
        opt.textContent = depto;
        deptoSelect.appendChild(opt);
    });
}

function actualizarCiudadesZipcodes() {
    const deptoSelect = document.getElementById('departamento');
    const ciudadSelect = document.getElementById('ciudad');
    ciudadSelect.innerHTML = '<option value="">Seleccione</option>';
    if (!deptoSelect.value) return;
    const ciudadesUnicas = [
        ...new Set(
            zipcodes
                .filter(item => item.state === deptoSelect.value)
                .map(item => item.place)
        )
    ].sort();
    ciudadesUnicas.forEach(ciudad => {
        let opt = document.createElement('option');
        opt.value = ciudad;
        opt.textContent = ciudad;
        ciudadSelect.appendChild(opt);
    });
}

// Calcula totales, IVA, y suma envío
function actualizarResumen() {
    const filas = document.querySelectorAll('#resumen-pedido tbody tr');
    let subtotal = 0;
    filas.forEach(fila => {
        const cantidad = parseInt(fila.querySelector('.cantidad').value) || 1;
        const precio = parseFloat(fila.querySelector('.cantidad').dataset.precio) || 0;
        subtotal += cantidad * precio;
        // Actualiza el subtotal de cada fila (por si el usuario cambia la cantidad)
        fila.querySelector('.subtotal').textContent = '$' + (cantidad * precio).toLocaleString('es-CO');
    });

    // Calculo IVA 19%
    let iva = Math.round(subtotal * 0.19);
    let subtotalSinIva = subtotal;
    let total = subtotalSinIva + iva + (ultimoEnvioCotizado || 0);

    // Mostrar el resumen de totales fuera de la tabla
    const resumenDiv = document.getElementById('resumen-totales');
    resumenDiv.innerHTML = `
      <div class="linea-total"><span>Subtotal (sin IVA)</span><span>$${subtotalSinIva.toLocaleString('es-CO')}</span></div>
      <div class="linea-total"><span>IVA (19%)</span><span>$${iva.toLocaleString('es-CO')}</span></div>
      <div class="linea-total"><span>Envío</span><span>${ultimoEnvioCotizado ? '$' + ultimoEnvioCotizado.toLocaleString('es-CO') : 'Por calcular...'}</span></div>
      <div class="linea-total total-pagar"><span>Total</span><span>$${total.toLocaleString('es-CO')}</span></div>
    `;
    // Setear a los inputs ocultos
    document.getElementById('campo_subtotal').value = subtotalSinIva;
    document.getElementById('campo_iva').value = iva;
    document.getElementById('campo_envio').value = ultimoEnvioCotizado;
    document.getElementById('campo_total').value = total;
}



// Cotiza envío en tiempo real y muestra estimado de llegada
async function cotizarEnvio() {
  const departamento = document.getElementById('departamento').value;
  const ciudad = document.getElementById('ciudad').value;
  const direccion = document.querySelector('[name="direccion"]').value;
  const barrio = document.querySelector('[name="barrio"]').value;
  const nombre = document.querySelector('[name="nombre"]').value || "Cliente";

  // Prevenir si falta dato clave
  if (!departamento || !ciudad || !direccion || !barrio) {
    document.getElementById('envio-mensaje').textContent = 'Completa datos de envío para cotizar.';
    ultimoEnvioCotizado = 0; estimadoEntrega = "";
    actualizarResumen();
    return;
  }

  document.getElementById('envio-mensaje').textContent = 'Calculando envío...';
  const resp = await fetch('cotizar_envio.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      departamento_destino: departamento,
      ciudad_destino: ciudad,
      direccion: direccion,
      barrio: barrio,
      nombre: nombre
    })
  });
  const data = await resp.json();
  if (data.ok) {
    // Toma solo el primer carrier cotizado (Interrapidisimo)
    let servicio = data.data && data.data.length > 0 ? data.data[0] : null;
    ultimoEnvioCotizado = servicio ? Math.round(servicio.totalPrice || 0) : (data.precio || 0);
    estimadoEntrega = servicio && servicio.deliveryEstimate ? servicio.deliveryEstimate : '';
    document.getElementById('envio-mensaje').textContent = 
        'Envío: $' + ultimoEnvioCotizado.toLocaleString('es-CO') + 
        (estimadoEntrega ? ' | Estimado de entrega: ' + estimadoEntrega : '');
  } else {
    ultimoEnvioCotizado = 0;
    estimadoEntrega = '';
    document.getElementById('envio-mensaje').textContent = data.error || 'Error al cotizar envío';
  }
  actualizarResumen();
}

document.addEventListener('DOMContentLoaded', () => {
    cargarDepartamentosZipcodes();
    document.getElementById('departamento').addEventListener('change', actualizarCiudadesZipcodes);
    document.getElementById('ciudad').addEventListener('change', cotizarEnvio);
    document.getElementById('direccion').addEventListener('input', cotizarEnvio);
    document.getElementById('barrio').addEventListener('input', cotizarEnvio);
    document.getElementById('nombre')?.addEventListener('input', cotizarEnvio);

    setTimeout(actualizarResumen, 800);

document.querySelectorAll('#resumen-pedido .cantidad').forEach((input, idx) => {
    input.setAttribute('data-index', idx);
    input.addEventListener('input', function () {
        let valor = parseInt(this.value);
        if (isNaN(valor) || valor < 1) {
            alert("La cantidad mínima permitida es 1.");
            this.value = 1;
        } else if (valor > 25) {
            alert("La cantidad máxima permitida es 25.");
            this.value = 25;
        }
        fetch('actualizar_cantidad.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'index=' + encodeURIComponent(this.dataset.index) + '&cantidad=' + encodeURIComponent(this.value)
        }).then(() => {
            // PRIMERO actualiza el resumen, luego cotiza envío
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
      <input type="text" name="direccion" id="direccion" required />
    </div>
    <div class="input-group">
      <label for="barrio">Barrio</label>
      <input type="text" name="barrio" id="barrio" required />
    </div>
    <h3>2. Datos personales</h3>
    <div class="input-group">
      <label>Nombre</label>
      <input type="text" name="nombre" id="nombre" required />
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
      <input type="number" name="numero_documento" required min="1" step="1" />
    </div>
    <div class="input-group">
      <label>Teléfono</label>
      <input
      type="number"
      name="telefono"
      required
      min="1000000000"
      max="9999999999"
      step="1"
      oninput="if(this.value.length > 10) this.value = this.value.slice(0,10);"
    />
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
          $presentacionNombre = '';
          if (!empty($producto['presentacion'])) {
            $stmtPres = $pdo->prepare("SELECT Peso FROM presentacion WHERE ID_Presentacion = ?");
            $stmtPres->execute([$producto['presentacion']]);
            $filaPres = $stmtPres->fetch(PDO::FETCH_ASSOC);
            if ($filaPres) {
              $presentacionNombre = $filaPres['Peso'];
            }
          }

          $nombreCompleto = $nombre . ($presentacionNombre ? " ({$presentacionNombre})" : "");
        ?>
        <tr>
          <td class="product-summary" data-label="Producto">
            <img src="<?= htmlspecialchars($imgRuta) ?>" alt="<?= $nombreCompleto ?>">
            <span><?= $nombreCompleto ?></span>
          </td>
          <td data-label="Cantidad">
            <input type="number" class="cantidad"
              name="cantidades[<?= $index ?>]"
              value="<?= $producto['cantidad'] ?>" min="1" max="25"
              data-precio="<?= $producto['precio'] ?>"
              data-index="<?= $index ?>"
              data-peso="<?= $producto['peso'] ?? 1 ?>"
              data-largo="<?= $producto['largo'] ?? 20 ?>"
              data-ancho="<?= $producto['ancho'] ?? 20 ?>"
              data-alto="<?= $producto['alto'] ?? 20 ?>"
            >
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
    </table>

    <div class="resumen-totales" id="resumen-totales"></div>
    <div id="envio-mensaje" style="margin-top:10px; font-weight:700; color:#227a38;"></div>
    
    <div class="checkboxes" style="margin-top: 1.2em;">
      <label><input type="checkbox" name="info" /> Deseo recibir información relevante</label>
      <label><input type="checkbox" name="terminos" required /> Acepto los términos y condiciones</label>
    </div>

    <input type="hidden" id="campo_subtotal" name="subtotal" value="">
    <input type="hidden" id="campo_iva" name="iva" value="">
    <input type="hidden" id="campo_envio" name="envio" value="">
    <input type="hidden" id="campo_total" name="total" value="">

    <!-- Aquí agregamos los inputs ocultos para las dimensiones e IDs -->
    <?php foreach ($_SESSION['carrito'] as $index => $item): ?>
      <input type="hidden" name="carrito[<?= $index ?>][id_producto]" value="<?= htmlspecialchars($item['id_producto'] ?? '') ?>">
      <input type="hidden" name="carrito[<?= $index ?>][presentacion]" value="<?= htmlspecialchars($item['presentacion'] ?? '') ?>">
      <input type="hidden" name="carrito[<?= $index ?>][alto]" value="<?= htmlspecialchars($item['alto'] ?? '') ?>">
      <input type="hidden" name="carrito[<?= $index ?>][ancho]" value="<?= htmlspecialchars($item['ancho'] ?? '') ?>">
      <input type="hidden" name="carrito[<?= $index ?>][largo]" value="<?= htmlspecialchars($item['largo'] ?? '') ?>">
    <?php endforeach; ?>

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
