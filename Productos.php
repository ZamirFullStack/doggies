<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Actualizar cantidad del último producto agregado
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update'], $_POST['cantidad'])
) {
    $qty = max(1, min(25, intval($_POST['cantidad'])));
    $last = count($_SESSION['carrito']) - 1;
    if ($last >= 0) {
        $_SESSION['carrito'][$last]['cantidad'] = $qty;
    }
    exit;
}

// Agregar producto al carrito (SUMAR cantidad si ya existe)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['nombre'], $_POST['precio'], $_POST['cantidad'], $_POST['imagen']) &&
    !isset($_POST['update'])
) {
    $nombre   = $_POST['nombre'];
    $precio   = floatval($_POST['precio']);
    $cantidad = max(1, min(25, intval($_POST['cantidad'])));
    $imagen   = $_POST['imagen'];

    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if (
            $item['nombre'] === $nombre &&
            $item['precio'] == $precio &&
            $item['imagen'] === $imagen
        ) {
            // Suma la cantidad, máximo 25
            $item['cantidad'] = min(25, $item['cantidad'] + $cantidad);
            $encontrado = true;
            break;
        }
    }
    unset($item); // Rompe referencia

    if (!$encontrado) {
        $_SESSION['carrito'][] = [
            'nombre'   => $nombre,
            'precio'   => $precio,
            'cantidad' => $cantidad,
            'imagen'   => $imagen
        ];
    }
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
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    .modal { display:none; position:fixed; z-index:1000; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);}
    .modal-contenido { background:#fff; margin:5% auto; padding:30px; width:90%; max-width:800px; border-radius:18px; box-shadow:0 0 20px rgba(0,0,0,0.18);}
    .modal-contenido h2 { text-align:center; margin-bottom:20px; color: #28a745; font-size:1.55em; font-weight:700; letter-spacing: 0.01em;}
    .modal-contenido table { width:100%; border-collapse:collapse; margin-bottom:18px; display: block; overflow-x: auto; white-space: nowrap;}
    .modal-contenido thead, .modal-contenido tbody, .modal-contenido tr { display: table; width: 100%; table-layout: fixed;}
    .modal-contenido th, .modal-contenido td { padding:15px 7px; text-align:center; border:1px solid #ccc; font-size: 1rem; background: #fff; word-break: break-word;}
    .modal-contenido thead th { background:#f4f4f4; color: #333; font-weight: 600; font-size: 1.09em;}
    .modal-contenido img { width:56px; height:56px; object-fit:cover; margin-right:8px; vertical-align:middle; border-radius: 7px;}
    .cantidad-input { width:54px; text-align:center; padding:7px 3px; font-size:1em; border-radius: 7px; border:1px solid #bbb;}
    .modal-contenido button, .modal-contenido form button { margin:7px 8px; padding:14px 25px; border:none; border-radius:9px; background:#28a745; color:#fff; cursor:pointer; font-size:1.13em; font-weight: 600; min-width: 150px; transition: background 0.18s; display: inline-block;}
    .modal-contenido button:hover, .modal-contenido form button:hover { background:#218838; }
    .modal-contenido form { display:inline-block;}
    .carrito-animado { position:fixed; z-index:2000; transition:transform .8s, opacity .8s; width:60px; height:60px; object-fit:cover; }
    .agotado { color:red; font-weight:bold; margin-top:10px; }
    @media (max-width: 650px) {
      .modal-contenido { padding: 8px 1px; min-width: 0; width: 99%; border-radius:12px;}
      .modal-contenido h2 { font-size: 1.1em;}
      .modal-contenido table { font-size: 0.96em;}
      .modal-contenido th, .modal-contenido td { padding: 8px 2px; font-size: 0.93em;}
      .cantidad-input { width:38px; font-size:0.97em; padding:5px 2px;}
      .modal-contenido button, .modal-contenido form button { width: 98%; max-width: 330px; margin: 7px auto; display: block; font-size:1.09em;}
      .modal-contenido form { display:block;}
    }
    @media (max-width:400px) {
      .modal-contenido { padding:2px 0;}
      .modal-contenido th, .modal-contenido td { padding:3px 1px; font-size:0.90em;}
      .cantidad-input { width:32px;}
    }

    /* -------- FILTRO -------- */
    .filtro {
      min-width: 210px;
      max-width: 310px;
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 3px 15px rgba(50,80,60,0.13);
      margin: 2em 1.5em 2em 1.5em;
      padding: 28px 22px 18px 22px;
      font-size: 1.07em;
      display: flex;
      flex-direction: column;
      gap: 16px;
      height: fit-content;
      border: 1px solid #f1f1f1;
      transition: box-shadow 0.18s;
      align-items: flex-start;
    }
    .filtro:hover { box-shadow: 0 6px 24px rgba(40, 160, 90, 0.14);}
    .filtro h2 { font-size: 1.17em; margin-bottom: 0.25em; color: #28a745; font-weight: 700; letter-spacing: 0.5px;}
    .filtro label { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; font-size: 1em; cursor: pointer; color: #252525; padding: 5px 0 3px 2px; border-radius: 6px; transition: background 0.15s;}
    .filtro label:hover { background: #f3faf6;}
    .filtro input[type="checkbox"] { accent-color: #28a745; width: 18px; height: 18px; border-radius: 5px; border: 1.5px solid #28a745; margin-right: 4px; cursor: pointer; transition: box-shadow 0.16s;}
    /* --- NUEVO: campos de precios vertical y largos --- */
    .filtro-precio-box {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .filtro-precio-box label {
      width: 100%;
      font-size: 1em;
      gap: 12px;
      margin-bottom: 0;
      justify-content: flex-start;
      color: #252525;
    }
    .filtro-precio-box input[type="number"] {
      width: 100%;
      max-width: 200px;
      min-width: 110px;
      border-radius: 7px;
      padding: 10px 12px;
      background: #f7fbfa;
      font-size: 1.07em;
      border: 1.5px solid #dadada;
      box-shadow: 0 1px 3px rgba(30,150,40,0.03);
      outline: none;
      transition: border-color 0.19s, box-shadow 0.17s;
      text-align: left;
      color: #333;
      font-family: inherit;
    }
    .filtro-precio-box input[type="number"]:focus {
      border-color: #28a745;
      background: #effff7;
      box-shadow: 0 1px 8px rgba(40,170,80,0.13);
    }
    .filtro button {
      background: linear-gradient(90deg, #28a745 60%, #21936a 100%);
      color: #fff;
      font-weight: bold;
      border: none;
      border-radius: 11px;
      padding: 11px 0;
      margin-top: 11px;
      font-size: 1.07em;
      cursor: pointer;
      box-shadow: 0 1px 6px rgba(40, 160, 90, 0.10);
      transition: background 0.17s, box-shadow 0.13s;
      letter-spacing: 0.6px;
      width: 100%;
    }
    .filtro label input[type="number"] { margin-left: 6px;}
    /* Carrusel vertical y compacto */
    .carrusel-banner {
      width: 100%;
      min-height: 100px;
      margin: 16px 0 0 0;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      justify-content: flex-start;
      position: relative;
      gap: 10px;
      z-index: 3;
    }
    .carrusel-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      margin-bottom: 5px;
    }
    .carrusel-btn {
      background: none;
      border: none;
      color: #d8a200;
      font-size: 1.2em;
      cursor: pointer;
      padding: 2px 9px;
      border-radius: 50%;
      transition: background 0.13s;
    }
    .carrusel-btn:hover { background: #fff7d2;}
    .carrusel-btn:active { background: #ffe393;}
    .carrusel-contenido {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      align-items: stretch;
      min-width: 0;
      width: 100%;
      gap: 0;
    }
    .banner-item {
      display: none;
      background: linear-gradient(90deg, #ffe083 70%, #ffd97d 100%);
      border-radius: 12px;
      font-size: 1em;
      font-weight: 500;
      box-shadow: 0 2px 8px #ffe0864d;
      align-items: center;
      gap: 10px;
      min-width: 160px;
      width: 100%;
      min-height: 52px;
      max-width: 280px;
      padding: 13px 14px;
      color: #bc8400;
      margin: 0 auto 7px auto;
    }
    .banner-item.activo { display: flex;}
    .banner-item i { font-size: 1.45em; color: #f7b500; flex-shrink: 0; margin-right: 9px;}
    .banner-item strong { color: #bc8400;}
    @media (max-width:700px) {
      .filtro { padding: 15px 4vw 16px 4vw;}
      .carrusel-banner { max-width: 99vw; }
      .banner-item { font-size: 0.97em; padding: 10px 2vw 10px 2vw;}
      .banner-item i { font-size: 1.13em;}
      .filtro-precio-box input[type="number"] { font-size: 0.97em; max-width: none;}
    }
    @media (max-width:430px) {
      .banner-item { font-size: 0.95em; padding: 7px 1vw 7px 2vw; }
      .carrusel-btn { font-size: 1em;}
    }
    /* --- resto estilos productos... igual que antes --- */
    .productos-page { flex: 1 1 auto; display: flex; flex-direction: column; align-items: stretch; justify-content: flex-start; width: 100%; min-width: 0;}
    .productos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 28px 18px; padding: 25px 25px 65px 25px; width: 100%; box-sizing: border-box; align-items: stretch;}
    .producto-card { background: #fff; border-radius: 13px; box-shadow: 0 2px 12px rgba(30,30,30,0.08); padding: 17px 13px 15px 13px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; width: 100%; max-width: 330px; min-width: 0; margin: 0 auto; transition: box-shadow 0.18s;}
    .producto-card img { width: 155px; height: 155px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; background: #f4f4f4; display: block; box-shadow: 0 2px 7px rgba(60,60,60,0.08);}
    .producto-info { width: 100%; display: flex; flex-direction: column; align-items: center; gap: 4px;}
    .producto-info h3 { font-size: 1.09em; margin-bottom: 0.5px; font-weight: bold; color: #333; text-align: center;}
    .producto-info p { font-size: 0.99em; color: #666; margin-bottom: 4px; text-align: center; min-height: 34px; max-height: 40px; overflow: hidden;}
    .producto-info span { font-size: 1.10em; color: #28a745; font-weight: bold; margin-bottom: 6px;}
    .cantidad-comprar { display: flex; align-items: center; gap: 7px; margin-bottom: 7px;}
    .producto-descripcion { display: flex; align-items: center; gap: 8px; text-align: left; min-width: 120px; max-width: 220px; overflow: hidden;}
    .producto-descripcion span { display: block; font-weight: 500; color: #222; white-space: normal; overflow-wrap: break-word; word-break: break-word; max-width: 150px; font-size: 1rem;}
    .agotado { color:red; font-weight:bold; margin-top:10px;}
    /* ... Más media queries igual que antes ... */
    body { min-height: 100vh; display: flex; flex-direction: column;}
    .page-container { flex: 1 0 auto;}
    footer { margin-top: auto; width: 100%; background: #333; color: #fff; text-align: center; padding: 20px 10px 36px 10px; box-sizing: border-box;}
    @media (max-width: 700px) { footer { padding: 18px 3vw 32px 3vw; font-size: 0.99em;} }
  </style>
</head>
<body>
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li id="icono-carrito"><a href="carrito.php"><i class="fas fa-cart-shopping"></i> Carrito <span id="contador-carrito"><?php echo '(' . count($_SESSION['carrito']) . ')'; ?></span></a></li>
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
      <div class="filtro-precio-box">
        <label for="precio-max">Max:
          <input type="number" id="precio-max" placeholder="999999">
        </label>
        <label for="precio-min">Min:
          <input type="number" id="precio-min" placeholder="0">
        </label>
      </div>
      <button onclick="filtrarProductos()">Filtrar</button>
      <!-- Carrusel banners vertical compacto -->
<div class="carrusel-banner" id="carrusel-banners">
  <div class="carrusel-controls">
    <button class="carrusel-btn carrusel-prev" onclick="moverCarrusel(-1)" aria-label="Anterior">
      <i class="fas fa-chevron-up"></i>
    </button>
    <button class="carrusel-btn carrusel-next" onclick="moverCarrusel(1)" aria-label="Siguiente">
      <i class="fas fa-chevron-down"></i>
    </button>
  </div>
  <div class="carrusel-contenido">
    <div class="banner-item activo">
      <i class="fas fa-bone"></i>
      <span><strong>¡20% OFF!</strong> Snacks para perros este mes.</span>
    </div>
    <div class="banner-item">
      <i class="fas fa-dog"></i>
      <span><strong>ENVÍO GRATIS</strong> por compras superiores a $80.000</span>
    </div>
    <div class="banner-item">
      <i class="fas fa-star"></i>
      <span><strong>2x1</strong> en juguetes seleccionados 🦴</span>
    </div>
    <div class="banner-item">
      <i class="fas fa-gift"></i>
      <span>¡Participa por <strong>premios</strong> en nuestro sorteo mensual!</span>
    </div>
  </div>
</div>

<!-- Preguntas Frecuentes (FAQ) debajo del carrusel -->
<div class="faq-section" style="margin-top:22px; background:#f8f9fc; border-radius:16px; box-shadow:0 2px 10px rgba(60,60,80,0.07); padding:18px 10px 13px 18px;">
  <div class="faq-title" style="font-size:1.13em; font-weight:700; color:#1a8357; margin-bottom:9px;">
    <i class="fas fa-question-circle"></i> Preguntas frecuentes
  </div>
  <div class="faq-list" style="display:flex; flex-direction:column; gap:8px;">
    <div class="faq-item" style="background:#fff; border-radius:11px; box-shadow:0 1px 5px #e5f9ed6b; padding:9px 14px; cursor:pointer; border:1.5px solid #e3f2ed; transition:.15s;">
      <div class="faq-question" style="font-weight:600; color:#22885c; display:flex; align-items:center; justify-content:space-between;">
        ¿Cómo puedo hacer un pedido?
        <span class="faq-toggle-icon"><i class="fas fa-chevron-down"></i></span>
      </div>
      <div class="faq-answer" style="display:none; color:#285246; font-size:0.97em; margin-top:6px;">
        Solo debes seleccionar los productos, ajustar la cantidad y hacer clic en "Comprar". Luego puedes finalizar tu compra en el carrito.
      </div>
    </div>
    <div class="faq-item" style="background:#fff; border-radius:11px; box-shadow:0 1px 5px #e5f9ed6b; padding:9px 14px; cursor:pointer; border:1.5px solid #e3f2ed; transition:.15s;">
      <div class="faq-question" style="font-weight:600; color:#22885c; display:flex; align-items:center; justify-content:space-between;">
        ¿Cuánto tarda el envío?
        <span class="faq-toggle-icon"><i class="fas fa-chevron-down"></i></span>
      </div>
      <div class="faq-answer" style="display:none; color:#285246; font-size:0.97em; margin-top:6px;">
        El envío estándar tarda de 1 a 3 días hábiles en Pasto. Para otras ciudades, puede tomar hasta 5 días hábiles.
      </div>
    </div>
    <div class="faq-item" style="background:#fff; border-radius:11px; box-shadow:0 1px 5px #e5f9ed6b; padding:9px 14px; cursor:pointer; border:1.5px solid #e3f2ed; transition:.15s;">
      <div class="faq-question" style="font-weight:600; color:#22885c; display:flex; align-items:center; justify-content:space-between;">
        ¿Puedo pagar contraentrega?
        <span class="faq-toggle-icon"><i class="fas fa-chevron-down"></i></span>
      </div>
      <div class="faq-answer" style="display:none; color:#285246; font-size:0.97em; margin-top:6px;">
        Sí, aceptamos pago contraentrega en Pasto y algunas ciudades. También puedes pagar en línea con tarjeta o PSE.
      </div>
    </div>
    <div class="faq-item" style="background:#fff; border-radius:11px; box-shadow:0 1px 5px #e5f9ed6b; padding:9px 14px; cursor:pointer; border:1.5px solid #e3f2ed; transition:.15s;">
      <div class="faq-question" style="font-weight:600; color:#22885c; display:flex; align-items:center; justify-content:space-between;">
        ¿Los productos tienen garantía?
        <span class="faq-toggle-icon"><i class="fas fa-chevron-down"></i></span>
      </div>
      <div class="faq-answer" style="display:none; color:#285246; font-size:0.97em; margin-top:6px;">
        Sí, todos los productos tienen garantía de satisfacción. Si tienes algún inconveniente, contáctanos y te ayudaremos.
      </div>
    </div>
    <div class="faq-item" style="background:#fff; border-radius:11px; box-shadow:0 1px 5px #e5f9ed6b; padding:9px 14px; cursor:pointer; border:1.5px solid #e3f2ed; transition:.15s;">
      <div class="faq-question" style="font-weight:600; color:#22885c; display:flex; align-items:center; justify-content:space-between;">
        ¿Hacen entregas el mismo día?
        <span class="faq-toggle-icon"><i class="fas fa-chevron-down"></i></span>
      </div>
      <div class="faq-answer" style="display:none; color:#285246; font-size:0.97em; margin-top:6px;">
        Sí, para pedidos hechos antes de las 12:00pm en Pasto. Sujeto a disponibilidad.
      </div>
    </div>
  </div>
</div>

    </aside>

    <main class="productos-page">
      <div class="productos-grid">
        <?php
        require 'conexion.php';
        $productos = $pdo->query("SELECT * FROM producto")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($productos as $p):
            $nombre      = htmlspecialchars($p['Nombre']);
            $imagen      = $p['Imagen_URL'] ?: 'img/default.jpg';
            $descripcion = htmlspecialchars($p['Descripcion']);
            $precio      = floatval($p['Precio']);
            $stock       = intval($p['Stock']);
            $edad        = $p['edad'] ?: 'adulto';
            $precioF     = number_format($precio,0,',','.');
        ?>
        <div class="producto-card" data-edad="<?php echo $edad; ?>" data-precio="<?php echo $precio; ?>">
          <img src="<?php echo $imagen; ?>" alt="<?php echo $nombre; ?>">
          <div class="producto-info">
            <h3><?php echo $nombre; ?></h3>
            <p><?php echo $descripcion; ?></p>
            <span>$<?php echo $precioF; ?></span>

            <?php if ($stock > 0): ?>
            <div class="cantidad-comprar">
              <button onclick="cambiarCantidad(this,-1)">−</button>
              <input type="number" value="1" min="1" max="25" readonly>
              <button onclick="cambiarCantidad(this,1)">+</button>
            </div>
            <form method="POST">
              <input type="hidden" name="nombre"   value="<?php echo $nombre; ?>">
              <input type="hidden" name="precio"   value="<?php echo $precio; ?>">
              <input type="hidden" name="cantidad" value="1" class="input-cantidad">
              <input type="hidden" name="imagen"   value="<?php echo $imagen; ?>">
              <button type="submit" class="btn-comprar">Comprar</button>
            </form>
            <?php else: ?>
              <p class="agotado">Agotado</p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

  <div id="modal-confirmacion" class="modal">
    <div class="modal-contenido">
      <h2>Producto agregado al carrito</h2>
      <table>
        <thead>
          <tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th></tr>
        </thead>
        <tbody id="tabla-productos"></tbody>
      </table>
      <button id="continuar-comprando">Continuar comprando</button>
      <button id="editar-carrito">Editar carrito</button>
      <form action="Checkout.php" method="get">
        <button type="submit">Finalizar compra</button>
      </form>
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
    // Carrusel banners vertical
    let carruselIndex = 0;
    let banners;
    function mostrarBanner(idx) {
      banners.forEach((b, i) => {
        b.classList.toggle('activo', i === idx);
      });
    }
    function moverCarrusel(dir) {
      if (!banners) banners = document.querySelectorAll('.banner-item');
      carruselIndex = (carruselIndex + dir + banners.length) % banners.length;
      mostrarBanner(carruselIndex);
      reiniciarCarruselAuto();
    }
    let carruselInterval;
    function reiniciarCarruselAuto() {
      clearInterval(carruselInterval);
      carruselInterval = setInterval(() => {
        moverCarrusel(1);
      }, 6000);
    }
    document.addEventListener('DOMContentLoaded', () => {
      banners = document.querySelectorAll('.banner-item');
      mostrarBanner(0);
      reiniciarCarruselAuto();

      // Modal compra
      const btns = document.querySelectorAll('.btn-comprar');
      const modal = document.getElementById('modal-confirmacion');
      const cont  = document.getElementById('contador-carrito');
      const tabla = document.getElementById('tabla-productos');
      const btnCont = document.getElementById('continuar-comprando');
      const btnEdit = document.getElementById('editar-carrito');

      btns.forEach(boton => {
        boton.addEventListener('click', e => {
          e.preventDefault();
          const form = boton.closest('form');
          const card = boton.closest('.producto-card');
          const img  = card.querySelector('img');
          const rect = img.getBoundingClientRect();
          const cartIcon = document.getElementById('icono-carrito').getBoundingClientRect();

          const clone = img.cloneNode();
          clone.classList.add('carrito-animado');
          clone.style.top  = rect.top + 'px';
          clone.style.left = rect.left + 'px';
          document.body.appendChild(clone);
          requestAnimationFrame(()=>{
            clone.style.transform =
              `translate(${cartIcon.left-rect.left}px,${cartIcon.top-rect.top}px) scale(0.2)`;
            clone.style.opacity = '0';
          });
          setTimeout(()=>clone.remove(),800);

          const dataPost = new FormData(form);
          const qtyCard  = parseInt(card.querySelector('input[type="number"]').value,10) || 1;
          dataPost.set('cantidad', qtyCard);

          fetch('Productos.php', { method: 'POST', body: dataPost })
            .then(()=>{
              modal.style.display = 'block';
              let count = parseInt(cont.textContent.replace(/\D/g,''),10) || 0;
              count += qtyCard;
              cont.textContent = `(${count})`;

              const name  = form.nombre.value;
              const price = parseFloat(form.precio.value);
              const src   = form.imagen.value;
              tabla.innerHTML = '';
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td class="producto-descripcion">
                  <img src="${src}" alt="${name}"><span>${name}</span>
                </td>
                <td>$${price.toLocaleString('es-CO')}</td>
                <td>
                  <input type="number"
                         class="cantidad-input"
                         data-price="${price}"
                         min="1" max="25"
                         value="${qtyCard}"
                         onchange="actualizarSubtotalModal(this)">
                </td>
                <td class="subtotal-cell">$${(price*qtyCard).toLocaleString('es-CO')}</td>
              `;
              tabla.appendChild(tr);
            });
        });
      });

      btnCont.onclick = ()=> modal.style.display = 'none';
      btnEdit.onclick = ()=> window.location = 'carrito.php';
      window.onclick = e=>{ if(e.target===modal) modal.style.display='none'; };
    });

    function cambiarCantidad(btn, cambio) {
      const input = btn.parentNode.querySelector('input[type="number"]');
      let v = parseInt(input.value,10) || 1;
      v = Math.max(1, Math.min(25, v + cambio));
      input.value = v;
    }

    function actualizarSubtotalModal(input) {
      const price = parseFloat(input.dataset.price);
      let qty = parseInt(input.value,10) || 1;
      qty = Math.max(1, Math.min(25, qty));
      input.value = qty;
      // actualizar session
      const data = new URLSearchParams();
      data.append('update', '1');
      data.append('cantidad', qty);
      fetch('Productos.php', {
        method: 'POST',
        body: data,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
      });
      // actualizar subtotal en tabla
      const cell = input.closest('tr').querySelector('.subtotal-cell');
      cell.textContent = '$' + (price * qty).toLocaleString('es-CO');
    }

    function filtrarProductos() {
      const edades = Array.from(document.querySelectorAll('.filtro-edad:checked')).map(cb=>cb.value);
      const min = parseInt(document.getElementById('precio-min').value,10) || 0;
      const max = parseInt(document.getElementById('precio-max').value,10) || Infinity;
      document.querySelectorAll('.producto-card').forEach(card=>{
        const ed = card.dataset.edad;
        const pr = parseFloat(card.dataset.precio);
        card.style.display = ((edades.length===0||edades.includes(ed)) && pr>=min&&pr<=max)?'flex':'none';
      });
    }
  </script>
  <script>
  document.querySelectorAll('.faq-item').forEach(item => {
    item.addEventListener('click', function() {
      this.classList.toggle('open');
      const ans = this.querySelector('.faq-answer');
      ans.style.display = (ans.style.display === "block") ? "none" : "block";
    });
  });
</script>

</body>
</html>
