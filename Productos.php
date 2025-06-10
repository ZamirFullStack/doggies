<?php
session_start();
require 'conexion.php'; // Solo una vez, aquí

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

if ($busqueda !== '') {
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE Nombre LIKE ? OR Descripcion LIKE ?");
    $param = '%' . $busqueda . '%';
    $stmt->execute([$param, $param]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $productos = $pdo->query("SELECT * FROM producto")->fetchAll(PDO::FETCH_ASSOC);
}

$presentaciones = $pdo->query("SELECT * FROM presentacion")->fetchAll(PDO::FETCH_ASSOC);

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
    isset($_POST['nombre'], $_POST['precio'], $_POST['cantidad'], $_POST['imagen'], $_POST['presentacion']) &&
    !isset($_POST['update'])
) {
    $nombre   = $_POST['nombre'];
    $precio   = floatval($_POST['precio']);
    $cantidad = max(1, min(25, intval($_POST['cantidad'])));
    $imagen   = $_POST['imagen'];
    $presentacion = $_POST['presentacion']; // <<--- AGREGA ESTO

    
// Estandarizar la presentación para evitar duplicados "2kg" vs "2 kg" vs "2KG"
$presentacion = strtolower(trim($_POST['presentacion']));
// ...lo demás igual...

$encontrado = false;
foreach ($_SESSION['carrito'] as &$item) {
    if (
        $item['nombre'] === $nombre &&
        $item['precio'] == $precio &&
        $item['imagen'] === $imagen &&
        isset($item['presentacion']) &&
        strtolower(trim($item['presentacion'])) === $presentacion
    ) {
        $item['cantidad'] = min(25, $item['cantidad'] + $cantidad);
        $encontrado = true;
        break;
    }
}
unset($item);

if (!$encontrado) {
    $_SESSION['carrito'][] = [
        'nombre'   => $nombre,
        'precio'   => $precio,
        'cantidad' => $cantidad,
        'imagen'   => $imagen,
        'presentacion' => $presentacion
    ];
}

}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos - Doggies</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
* {
  box-sizing: border-box;
}

body {
  font-family: 'Roboto', 'Arial', sans-serif;
  background: #fafafa;
  color: #222;
  margin: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Header/Nav Compacto Mercado Libre style */
.header-principal {
  background: #fff;
  box-shadow: 0 2px 7px rgba(0,0,0,0.07);
  padding: 0;
  position: sticky;
  top: 0;
  z-index: 20;
}

/* --- CONTENEDOR CENTRALIZADO DEL HEADER --- */
.header-principal .nav-bar {
  max-width: 1050px;    /* No más largo en PC, bien compacto */
  width: 100%;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;   /* CENTRA todo horizontalmente */
  gap: 18px;
  padding: 14px 14px;
  box-sizing: border-box;
}

.nav-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18px;
  flex-wrap: nowrap; /* <-- ¡Esto es clave! */
}

.nav-links-row {
  display: flex;
  flex-direction: row; /* SIEMPRE en fila */
  align-items: center;
  gap: 16px;
  margin: 0;
  padding: 0;
  /* Para evitar que se hagan columnas por poco espacio: */
  min-width: 0;
  flex-shrink: 0;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 9px;
  font-size: 1.06em;
  white-space: nowrap; /* <- nunca hagas salto de línea interno */
}

.texto-desktop {
  display: inline;
}

@media (max-width: 700px) {
  .nav-bar {
    padding: 7px 3vw;
    gap: 3vw;
  }
  .search-box {
    margin: 0 8px 0 8px;
    min-width: 0;
    width: 100%;
    max-width: 100vw;
  }
  .nav-links-row {
    gap: 4vw;
  }
  .texto-desktop {
    display: none !important;  /* Solo íconos en móvil */
  }
  .nav-link {
    font-size: 1.34em;
    padding: 5px 3px;
  }
}

/* --- LOGO --- */
.logo-box img {
  height: 34px;
  border-radius: 8px;
  margin-right: 6px;
  flex-shrink: 0;
}

.logo-box {
  display: flex;
  align-items: center;
}

/* --- BUSCADOR --- */
.search-box {
  display: flex;
  align-items: center;
  width: 340px;     /* BUSCADOR COMPACTO! */
  max-width: 360px;
  min-width: 120px;
  margin: 0 18px;
  height: 39px;
  background: #fff;
  border-radius: 8px;
  border: 2px solid #28a745;
  overflow: hidden;
  position: relative;
}

#buscador-producto {
  flex: 1 1 0%;
  padding: 0 14px;
  height: 39px;
  font-size: 1.04em;
  border: none;
  outline: none;
  background: #f5fbf8;
}
#buscador-producto::placeholder {
  color: #8c8c8c;
  opacity: 1;
}

.icono-lupa {
  width: 39px;
  height: 39px;
  background: #28a745;
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  border: none;
  border-radius: 0 7px 7px 0;
  font-size: 1.22em;
  cursor: pointer;
  transition: background 0.2s;
  position: absolute;
  right: 0;
  top: 0;
}
.icono-lupa:hover { background: #218838; }

/* --- LINKS HEADER --- */
.nav-link {
  color: #222;
  display: flex;
  align-items: center;
  gap: 7px;
  cursor: pointer;
  font-weight: 600;
  border-radius: 7px;
  padding: 7px 11px;
  text-decoration: none;
}

a, a:visited, a:focus, a:active, a:hover {
  text-decoration: none !important;
  outline: none !important;
}


.page-container {
  flex: 1 1 auto;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
  padding: 28px 0 28px 0;
  gap: 36px;
}

@media (max-width: 800px) {
  .page-container {
    flex-direction: column;
    gap: 18px;
    align-items: stretch;
    padding: 19px 1vw;
  }
  aside.filtro {
    margin: 0 auto 18px auto;
    width: 100%;
    max-width: 100vw;
  }
}


/* Filtros */
.filtro {
  min-width: 200px;
  max-width: 300px;
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 3px 14px rgba(50,80,60,0.09);
  padding: 28px 22px 18px 22px;
  font-size: 1em;
  display: flex;
  flex-direction: column;
  gap: 16px;
  border: 1px solid #f1f1f1;
  align-items: flex-start;
}
.filtro h2 { font-size: 1.08em; color: #28a745; font-weight: 700; margin: 0 0 0.4em 0;}
.filtro label {
  display: flex;
  align-items: center;
  gap: 9px;
  font-size: 1em;
  cursor: pointer;
  color: #222;
  margin-bottom: 4px;
  border-radius: 5px;
  padding: 4px 3px 4px 3px;
  transition: background 0.17s;
}
.filtro label:hover { background: #f6fcf7; }
.filtro input[type="checkbox"] { accent-color: #28a745; width: 16px; height: 16px; }

.filtro-precio-box {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.filtro-precio-box input[type="number"] {
  width: 100%;
  max-width: 170px;
  border-radius: 7px;
  padding: 9px 10px;
  background: #f8faf9;
  font-size: 1em;
  border: 1.5px solid #dadada;
  outline: none;
  transition: border-color 0.17s;
}
.filtro-precio-box input[type="number"]:focus {
  border-color: #28a745;
  background: #f3fff7;
}

.filtro button {
  background: linear-gradient(90deg, #28a745 60%, #21936a 100%);
  color: #fff;
  font-weight: bold;
  border: none;
  border-radius: 10px;
  padding: 11px 0;
  margin-top: 13px;
  font-size: 1.05em;
  cursor: pointer;
  box-shadow: 0 1px 6px rgba(40, 160, 90, 0.10);
  transition: background 0.17s;
  width: 100%;
}
.filtro button:hover { background: #21936a; }

/* FAQ sección lateral */
.faq-section {
  margin-top: 20px;
  background: #f8f9fc;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(60,60,80,0.07);
  padding: 15px 12px 11px 15px;
  font-size: 1em;
}
.faq-title { font-size: 1.08em; font-weight: 700; color: #1a8357; margin-bottom: 8px;}
.faq-list { display: flex; flex-direction: column; gap: 8px; }
.faq-item {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 1px 5px #e5f9ed6b;
  padding: 8px 12px;
  cursor: pointer;
  border: 1.3px solid #e3f2ed;
  transition: background 0.13s;
}
.faq-item.open { background: #f2fff4; }
.faq-question { font-weight: 600; color: #22885c; display: flex; align-items: center; justify-content: space-between;}
.faq-answer { display: none; color: #2e5a42; font-size: 0.97em; margin-top: 5px; }
.faq-item.open .faq-answer { display: block; }

@media (max-width:700px) {
  .filtro { padding: 12px 2vw 14px 2vw; min-width: 0; max-width: 100vw;}
  .faq-section { font-size: 0.99em;}
}

/* Grid productos */
.productos-page {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: stretch;
}

.productos-grid {
  width: 100%;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 23px;
  margin-bottom: 30px;
}

@media (max-width: 900px) {
  .productos-grid { grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px;}
}

@media (max-width: 540px) {
  .productos-grid {
    grid-template-columns: 1fr; /* SOLO 1 PRODUCTO POR FILA */
    gap: 11px; /* Ajusta el espacio si quieres */
  }
}


.producto-card {
  background: #fff;
  border-radius: 13px;
  box-shadow: 0 2px 12px rgba(55, 160, 90, 0.06);
  padding: 15px 11px 14px 11px;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: box-shadow 0.21s, border 0.14s;
  border: 1.3px solid #e5f4ee;
  position: relative;
  min-height: 350px;
  height: 100%;
}
.producto-card:hover { box-shadow: 0 8px 25px #38e38b22; border-color: #27a045; z-index: 2;}
.producto-card img {
  width: 140px;
  height: 140px;
  object-fit: contain;
  background: #f2f2f2;
  border-radius: 8px;
  box-shadow: 0 2px 7px rgba(60,60,60,0.09);
  margin-bottom: 12px;
}

.producto-info {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1 1 auto;
  justify-content: flex-start;
}

.producto-info h3 {
  font-size: 1.09em;
  margin-bottom: 0.5px;
  font-weight: bold;
  color: #222;
  text-align: center;
}
.producto-info p {
  font-size: 0.98em;
  color: #666;
  margin-bottom: 4px;
  text-align: center;
  min-height: 34px;
  max-height: 40px;
  overflow: hidden;
}

.presentaciones-lista {
  margin: 7px 0 7px 0;
  display: flex;
  gap: 7px;
  justify-content: center;
  flex-wrap: wrap;
}
.btn-presentacion {
  padding: 6px 12px;
  border-radius: 6px;
  background: #e3e8e4;
  border: none;
  color: #555;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.99em;
  transition: background 0.17s, color 0.18s;
}
.btn-presentacion.active,
.btn-presentacion:hover {
  background: #28a745;
  color: #fff;
}

.precio-actual {
  display: block;
  font-size: 1.28em;
  font-weight: 700;
  color: #28a745;
  text-align: center;
  margin-bottom: 8px;
}

.acciones-producto {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  margin-top: auto;  /* <-- Hace que esto siempre esté al fondo */
}

.cantidad-comprar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 0;
}
.cantidad-comprar button {
  width: 31px;
  height: 31px;
  border: 1.6px solid #28a745;
  background: white;
  color: #28a745;
  font-weight: 700;
  font-size: 1.2rem;
  cursor: pointer;
  border-radius: 8px;
  line-height: 0;
  user-select: none;
  transition: background 0.17s, color 0.18s;
}
.cantidad-comprar button:hover {
  background: #28a745;
  color: white;
}
.cantidad-comprar input {
  width: 48px;
  text-align: center;
  font-size: 1.05rem;
  font-weight: 600;
  border: 1.4px solid #28a745;
  border-radius: 8px;
  outline: none;
  -moz-appearance: textfield;
  background: #f5faf8;
  padding: 2px 0;
}
.cantidad-comprar input::-webkit-outer-spin-button,
.cantidad-comprar input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

.btn-comprar {
  background: #28a745;
  border: none;
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  padding: 11px 25px;       /* Cambia aquí: más padding a los lados */
  border-radius: 8px;
  cursor: pointer;
  width: auto;              /* Quita el 100% y max-width, usa auto */
  min-width: 100px;         /* Para que no sea muy pequeño */
  margin: 18px auto 14px auto;
  box-shadow: 0 6px 12px rgba(40, 160, 90, 0.15);
  transition: background 0.18s;
  display: block;
  white-space: nowrap;
  text-align: center;
}

.btn-comprar:hover { background: #218838; }
.agotado { color: red; font-weight: bold; margin-top: 9px; }

/* MODAL Carrito */
.modal { display: none; position: fixed; z-index: 1000; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.55);}
.modal-contenido {
  background: #fff;
  margin: 5% auto;
  padding: 30px;
  width: 90%;
  max-width: 700px;
  border-radius: 18px;
  box-shadow: 0 0 20px rgba(0,0,0,0.16);
}
.modal-contenido h2 { text-align: center; margin-bottom: 18px; color: #28a745; font-size: 1.3em; font-weight: 700;}
.modal-contenido table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
.modal-contenido th, .modal-contenido td { padding: 10px 7px; text-align: center; border: 1px solid #ccc; font-size: 1em; background: #fff;}
.modal-contenido thead th { background: #f4f4f4; color: #333; font-weight: 600; }
.modal-contenido img { width: 44px; height: 44px; object-fit: cover; border-radius: 6px;}
.modal-contenido button, .modal-contenido form button {
  margin: 5px 7px;
  padding: 12px 21px;
  border: none;
  border-radius: 8px;
  background: #28a745;
  color: #fff;
  cursor: pointer;
  font-size: 1.06em;
  min-width: 110px;
  transition: background 0.16s;
}
.modal-contenido button:hover, .modal-contenido form button:hover { background: #218838; }
.modal-contenido form { display: inline-block;}
.cantidad-input { width: 48px; text-align: center; padding: 6px 2px; font-size: 1em; border-radius: 7px; border: 1px solid #bbb;}

@media (max-width: 650px) {
  .modal-contenido { padding: 10px 2vw; width: 98vw; }
  .modal-contenido th, .modal-contenido td { padding: 6px 2px; font-size: 0.93em;}
  .cantidad-input { width: 32px;}
}

/* Footer */
footer {
  margin-top: auto;
  width: 100%;
  background: #222;
  color: white;
  text-align: center;
  padding: 22px 10px 35px 10px;
  box-sizing: border-box;
  position: relative;
}

footer::after {
  content: "© 2025 Doggies. Todos los derechos reservados.";
  display: block;
  margin-top: 0.6em;
  font-size: 0.93rem;
  color: #bdbdbd;
}
.footer-content h3 { font-size: 1.28rem; font-weight: 700; margin-bottom: 13px; }
.social-links a {
  color: white;
  margin: 0 10px;
  font-size: 1.55rem;
  transition: color 0.18s;
  display: inline-block;
}
.social-links a:hover { color: #28a745;}
.footer-content p { margin-top: 12px; font-size: 1em;}

@media (max-width:700px) {
  .footer-content h3 { font-size: 1.07rem;}
  .social-links a { font-size: 1.18rem;}
}
@media (max-width:480px) {
  footer { padding: 13px 3vw 18px 3vw; font-size: 0.99em;}
}

/* Solo se muestra el contador-movil en móvil, y se oculta el texto largo */
.contador-movil {
  display: none;
  font-size: 1.05em;   /* <--- BAJA este valor, antes tenías 1.19em */
  font-weight: 700;
  margin-left: 3px;
  line-height: 1.1;
}
@media (max-width: 700px) {
  .contador-movil {
    display: inline !important;
    color: black;
    margin-left: 5px;
    font-size: 0.9em !important;  /* <--- AJUSTA aquí para móvil */
  }
  #icono-carrito {
    font-size: 1.35em;
    padding: 0 6px 0 0;
  }
}


.carrito-animado {
  position: fixed;
  z-index: 9999;
  width: 140px;   /* igual que las imágenes de producto */
  height: 140px;
  object-fit: contain;
  border-radius: 8px;
  transition:
    transform 0.7s cubic-bezier(.55,-0.25,.54,1.39),
    opacity 0.7s;
  pointer-events: none;
}


</style>
</head>

<body>
<header class="header-principal">
<nav class="nav-bar">
  <a href="Servicios.php" class="nav-link nav-servicios" id="nav-servicios">
    <i class="fas fa-concierge-bell"></i>
    <span class="texto-desktop">Servicios</span>
  </a>
  <a href="index.php" class="logo-box" id="nav-logo">
    <img src="img/fondo.jpg" alt="Doggies" class="logo-img" />
  </a>
  <form class="search-box" method="GET" action="Productos.php">
    <input type="text" id="buscador-producto" name="busqueda" placeholder="Buscar.." autocomplete="off" 
          value="<?php echo htmlspecialchars($busqueda ?? '') ?>">
    <button type="submit" class="icono-lupa">
      <i class="fas fa-search"></i>
    </button>
  </form>

<a href="carrito.php" id="icono-carrito" class="nav-link nav-carrito">
  <i class="fas fa-cart-shopping"></i>
  <span class="texto-desktop">
    Carrito (<span id="contador-carrito"><?php echo count($_SESSION['carrito']); ?></span>)
  </span>
  <span class="contador-movil">
    (<span id="contador-carrito-movil"><?php echo count($_SESSION['carrito']); ?></span>)
  </span>
</a>

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

$productosOrganizados = [];
foreach ($productos as $producto) {
    $producto['Presentaciones'] = [];
    foreach ($presentaciones as $pres) {
        if ($pres['ID_Producto'] == $producto['ID_Producto']) {
            $producto['Presentaciones'][] = $pres;
        }
    }
    $productosOrganizados[] = $producto;
}
    foreach ($productosOrganizados as $p): 
    $nombre      = htmlspecialchars($p['Nombre']);
    $imagen      = $p['Imagen_URL'] ?: 'img/default.jpg';
    $descripcion = htmlspecialchars($p['Descripcion']);
    $precio      = floatval($p['Precio']);
    $stock       = intval($p['Stock']);
    $edad        = $p['edad'] ?: 'adulto';
    $precioF     = number_format($precio,0,',','.');
?>
  <div class="producto-card" data-edad="<?php echo $edad; ?>" data-precio="<?php echo $precio; ?>">
    <a href="producto_detalle.php?id=<?php echo intval($p['ID_Producto']); ?>">
      <img src="<?php echo $imagen; ?>" alt="<?php echo $nombre; ?>">
    </a>
    <div class="producto-info">
      <h3><a href="producto_detalle.php?id=<?php echo intval($p['ID_Producto']); ?>" style="color:inherit; text-decoration:none;"><?php echo $nombre; ?></a></h3>
      <p><?php echo $descripcion; ?></p>
      <?php if (!empty($p['Presentaciones'])): ?>
        <div class="presentaciones-lista">
          <?php foreach ($p['Presentaciones'] as $index => $pres): ?>
        <button type="button" 
          class="btn-presentacion <?= $index === 0 ? 'active' : '' ?>"
          data-precio="<?= $pres['Precio'] ?>"
          data-peso="<?= htmlspecialchars($pres['Peso']) ?>"
          data-idpres="<?= $pres['ID_Presentacion'] ?>">
          <?= htmlspecialchars($pres['Peso']) ?>
        </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <span class="precio-actual">
        $<?php echo number_format($p['Presentaciones'][0]['Precio'] ?? $precio, 0, ',', '.'); ?>
      </span>
      <div class="acciones-producto">
        <?php if ($stock > 0): ?>
          <div class="cantidad-comprar">
            <button onclick="cambiarCantidad(this,-1)">−</button>
            <input type="number" value="1" min="1" max="25" readonly>
            <button onclick="cambiarCantidad(this,1)">+</button>
          </div>
          <form method="POST" class="form-compra">
            <input type="hidden" name="nombre" value="<?php echo $nombre; ?>">
            <input type="hidden" name="precio" value="<?php echo $p['Presentaciones'][0]['Precio'] ?? $precio; ?>" class="input-precio">
            <input type="hidden" name="presentacion" value="<?php echo htmlspecialchars($p['Presentaciones'][0]['Peso'] ?? ''); ?>" class="input-presentacion">
            <input type="hidden" name="cantidad" value="1" class="input-cantidad">
            <input type="hidden" name="imagen" value="<?php echo $imagen; ?>">
            <button type="submit" class="btn-comprar">Comprar</button>
          </form>
        <?php else: ?>
          <p class="agotado">Agotado</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

  <!-- ... El resto del HTML permanece igual ... -->

  <!-- MODAL Y FOOTER (sin cambios) -->
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

    // --- ANIMACIÓN IMAGEN AL CARRITO ---
    const clone = img.cloneNode();
    clone.classList.add('carrito-animado');
    clone.style.top  = rect.top + 'px';
    clone.style.left = rect.left + 'px';
    clone.style.width = rect.width + 'px';
    clone.style.height = rect.height + 'px';
    document.body.appendChild(clone);
    // Forzar reflow
    void clone.offsetWidth;
    // Animar hasta el ícono del carrito
    clone.style.transform =
      `translate(${cartIcon.left-rect.left}px,${cartIcon.top-rect.top}px) scale(0.2)`;
    clone.style.opacity = '0';
    setTimeout(()=>clone.remove(), 700);
    // --- FIN ANIMACIÓN ---

    // Todo lo demás igual...
    const dataPost = new FormData(form);
    const qtyCard  = parseInt(card.querySelector('input[type="number"]').value,10) || 1;
    dataPost.set('cantidad', qtyCard);

  fetch('Productos.php', { method: 'POST', body: dataPost })
    .then(()=>{
      modal.style.display = 'block';
      let count = parseInt(cont.textContent.replace(/\D/g,''),10) || 0;
      count += qtyCard;
      cont.textContent = count;
      const contMovil = document.getElementById('contador-carrito-movil');
      if (contMovil) contMovil.textContent = count;

      const name  = form.nombre.value;
      const price = parseFloat(form.precio.value);
      const src   = form.imagen.value;
      const pres  = form.presentacion.value;
      tabla.innerHTML = '';
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="celda-producto">
          <div style="display: flex; align-items: center; gap: 12px;">
            <img src="${src}" alt="${name}" class="img-modal" style="width:48px;height:48px;object-fit:contain;border-radius:6px;flex-shrink:0;">
<span style="font-weight:700;font-size:1.13em;line-height:1.1;color:#222;font-family:'Roboto',Arial,sans-serif;">
  ${name}${pres ? ' ('+pres+')' : ''}
</span>

          </div>
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

    // Actualizar precio y presentación al hacer clic en botones
    document.querySelectorAll('.producto-card').forEach(card => {
      const botones = card.querySelectorAll('.btn-presentacion');
      const precioSpan = card.querySelector('.precio-actual');
      const inputPrecio = card.querySelector('.input-precio');
      const inputPresentacion = card.querySelector('.input-presentacion');

      botones.forEach(boton => {
        boton.addEventListener('click', () => {
          // Quitar active a todos
          botones.forEach(b => b.classList.remove('active'));
          // Poner active al clickeado
          boton.classList.add('active');
          // Actualizar precio visible
          const nuevoPrecio = boton.dataset.precio;
          precioSpan.textContent = '$' + parseInt(nuevoPrecio).toLocaleString('es-CO');
          // Actualizar inputs ocultos
          inputPrecio.value = nuevoPrecio;
          inputPresentacion.value = boton.dataset.peso.trim();
        });
      });
    });
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
<script>
function filtrarPorNombre() {
  const texto = document.getElementById('buscador-producto').value.trim().toLowerCase();
  document.querySelectorAll('.producto-card').forEach(card => {
    const nombre = card.querySelector('h3').textContent.toLowerCase();
    const descripcion = card.querySelector('p').textContent.toLowerCase();
    card.style.display = (
      nombre.includes(texto) || descripcion.includes(texto)
    ) ? 'flex' : 'none';
  });
}
</script>

</body>
</html>