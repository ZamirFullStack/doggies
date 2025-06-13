<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// --------------------------
// VALIDACIÓN ID DE PRODUCTO
// --------------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'ID de producto inválido.']);
        exit;
    } else {
        die("ID de producto inválido.");
    }
}
$id = intval($_GET['id']);

// --------------------------
// --------------------------
// AGREGAR AL CARRITO (AJAX o POST)
// --------------------------
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['id_presentacion'], $_POST['nombre'], $_POST['precio'], $_POST['cantidad'], $_POST['imagen']) &&
    !isset($_POST['agregar_opinion'])
) {
    $idPresentacion = intval($_POST['id_presentacion']);
    $nombre   = $_POST['nombre'];
    $precio   = floatval($_POST['precio']);
    $cantidad = max(1, min(25, intval($_POST['cantidad'])));
    $imagen   = $_POST['imagen'];

    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if (
            isset($item['id_producto'], $item['id_presentacion']) &&
            $item['id_producto'] == $id &&
            $item['id_presentacion'] == $idPresentacion
        ) {
            $item['cantidad'] = min(25, $item['cantidad'] + $cantidad);
            $encontrado = true;
            break;
        }
    }
    unset($item);

    if (!$encontrado) {
        // Obtener peso y dimensiones desde BD
        $stmtPres = $pdo->prepare("SELECT Peso, ID_Producto FROM presentacion WHERE ID_Presentacion = ?");
        $stmtPres->execute([$idPresentacion]);
        $presData = $stmtPres->fetch(PDO::FETCH_ASSOC);

        if (!$presData) {
            http_response_code(400);
            exit('Error: Presentación no encontrada.');
        }

        $pesoNum = floatval(preg_replace('/[^\d.]/', '', $presData['Peso']));

        $stmtProd = $pdo->prepare("SELECT alto_cm, ancho_cm, largo_cm FROM producto WHERE ID_Producto = ?");
        $stmtProd->execute([$presData['ID_Producto']]);
        $prodDims = $stmtProd->fetch(PDO::FETCH_ASSOC);

        if (!$prodDims) {
            http_response_code(400);
            exit('Error: Producto no encontrado para dimensiones.');
        }

        $_SESSION['carrito'][] = [
            'id_producto' => $id,
            'id_presentacion' => $idPresentacion,
            'nombre' => $nombre,
            'precio' => $precio,
            'cantidad' => $cantidad,
            'imagen' => $imagen,
            'presentacion' => $idPresentacion, // Guardar el ID numérico en "presentacion"
            'peso' => $pesoNum,
            'alto' => $prodDims['alto_cm'],
            'ancho' => $prodDims['ancho_cm'],
            'largo' => $prodDims['largo_cm']
        ];
    }

    // Respuesta JSON para AJAX
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'contador' => count($_SESSION['carrito']),
            'carrito' => $_SESSION['carrito']
        ]);
        exit;
    }

    header('Location: producto_detalle.php?id=' . $id);
    exit;
}



// --------------------------
// ACTUALIZAR CANTIDAD (AJAX)
// --------------------------
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update'], $_POST['cantidad'], $_POST['id_presentacion']) &&
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    $qty = max(1, min(25, intval($_POST['cantidad'])));
    $idPres = intval($_POST['id_presentacion']);
    foreach ($_SESSION['carrito'] as &$item) {
        if (isset($item['id_presentacion']) && $item['id_presentacion'] == $idPres) {
            $item['cantidad'] = $qty;
            break;
        }
    }
    unset($item);
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'contador' => count($_SESSION['carrito']),
        'carrito' => $_SESSION['carrito']
    ]);
    exit;
}

// --------------------------
// CONSULTAS PRODUCTO Y VARIABLES PARA HTML
// --------------------------
$stmt = $pdo->prepare("SELECT * FROM producto WHERE ID_Producto = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) die("Producto no encontrado.");

$stmt = $pdo->prepare("SELECT AVG(Estrellas) as promedio, COUNT(*) as cantidad FROM opinion WHERE ID_Producto = ?");
$stmt->execute([$id]);
$calif = $stmt->fetch(PDO::FETCH_ASSOC);
$promedio = round($calif['promedio'] ?? 0, 1);
$cantidad = $calif['cantidad'];

$stmtOpiniones = $pdo->prepare("SELECT Usuario AS usuario, Comentario AS comentario, Estrellas AS estrellas, Fecha AS fecha FROM opinion WHERE ID_Producto = ? ORDER BY Fecha DESC, ID_Opinion DESC");
$stmtOpiniones->execute([$id]);
$opiniones = $stmtOpiniones->fetchAll(PDO::FETCH_ASSOC);

$nombre        = htmlspecialchars($producto['Nombre']);
$descripcion   = htmlspecialchars($producto['Descripcion']);
$ingredientes  = !empty($producto['Ingredientes']) ? htmlspecialchars($producto['Ingredientes']) : "No disponible";
$info_nutricional = !empty($producto['InfoNutricional']) ? htmlspecialchars($producto['InfoNutricional']) : "No disponible";
$stock         = intval($producto['Stock']);

$stmt = $pdo->prepare("SELECT * FROM presentacion WHERE ID_Producto = ? ORDER BY ID_Presentacion ASC");
$stmt->execute([$id]);
$presentaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($presentaciones) > 0) {
    $presInicial = $presentaciones[0];
    $precioInicial = floatval($presInicial['Precio']);
    $pesoInicial = htmlspecialchars($presInicial['Peso']);
} else {
    $presInicial = null;
    $precioInicial = floatval($producto['Precio']);
    $pesoInicial = 'N/A';
}

$stmtImgs = $pdo->prepare("SELECT URL_Imagen FROM producto_imagen WHERE ID_Producto = ? ORDER BY ID_Imagen ASC");
$stmtImgs->execute([$id]);
$imagenes = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
if (empty($imagenes)) {
    $imagenes = [$producto['Imagen_URL'] ?: 'img/default.jpg'];
}
$imagenPrincipal = $imagenes[0];

$usuarioLogueado = isset($_SESSION['usuario']);
$usuarioActual = $usuarioLogueado ? $_SESSION['usuario'] : null;
$recomendados = $pdo->query("SELECT * FROM producto WHERE ID_Producto != $id ORDER BY RAND() LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

$puedeOpinar = false;
$yaOpino = false;

if ($usuarioLogueado && isset($_SESSION['id_usuario'])) {
    $idUsuario = $_SESSION['id_usuario'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedido p
        INNER JOIN pedido_productos pp ON p.ID_Pedido = pp.ID_Pedido
        WHERE p.ID_Usuario = ? AND pp.ID_Producto = ? AND p.Estado = 'pagado'");
    $stmt->execute([$idUsuario, $id]);
    $haComprado = $stmt->fetchColumn();
    $puedeOpinar = $haComprado > 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM opinion WHERE ID_Producto = ? AND Usuario = ?");
    $stmt->execute([$id, $idUsuario]);
    $yaOpino = $stmt->fetchColumn() > 0;
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Detalle de <?php echo $nombre; ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
  <style>
* { box-sizing: border-box; }

body {
  font-family: 'Roboto', sans-serif;
  background: #fafafa;
  color: #333;
  margin: 0;
  padding: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

a {
  color: #28a745;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s;
}

a:hover { color: #1b5e20; }

header {
  background: #fff;
  padding: 10px 15px;
  box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
  flex-wrap: wrap;
  font-weight: 600;
}

header a.nav-link {
  color: #333;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  padding: 6px 10px;
  border-radius: 6px;
  transition: background 0.2s;
}

header a.nav-link:hover {
  background: #e6f0e6;
  color: #1b5e20;
}

header .logo-box img {
  height: 40px;
  margin: 0 15px;
  flex-shrink: 0;
}

header form {
  flex: 1 1 250px;
  max-width: 350px;
  display: flex;
}

header input[type=text] {
  flex: 1;
  padding: 7px 10px;
  border: 2px solid #28a745;
  border-right: none;
  border-radius: 6px 0 0 6px;
  font-size: 0.95rem;
  outline: none;
  min-width: 0;
}

header button {
  background: #28a745;
  border: none;
  color: white;
  padding: 0 12px;
  cursor: pointer;
  border-radius: 0 6px 6px 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem;
  transition: background 0.3s;
}

header button:hover { background: #218838; }

main {
  flex: 1;
  max-width: 1100px;
  margin: 25px auto 40px auto;
  padding: 0 10px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 3px 18px rgb(0 0 0 / 0.08);
}

.volver-link {
  display: inline-block;
  margin-bottom: 15px;
  font-weight: 600;
  color: #28a745;
}

.detalle-producto-container {
  width: 100%;
}

.producto-main {
  display: flex;
  gap: 30px;
  flex-wrap: wrap;
  justify-content: center;
  align-items: flex-start;
}

.producto-imagenes {
  flex: 0 0 350px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: white;
  border-radius: 16px;
  padding: 15px;
  box-shadow: 0 8px 16px rgb(0 0 0 / 0.12);
  gap: 15px;
  min-width: 240px;
  width: 100%;
  max-width: 350px;
}

.imagen-principal {
  width: 320px;
  height: 320px;
  object-fit: contain;
  border-radius: 12px;
  cursor: zoom-in;
  border: 1px solid #ddd;
  box-shadow: 0 4px 15px rgb(0 0 0 / 0.10);
  background: white;
  max-width: 100%;
  max-height: 60vw;
}

.miniaturas {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
}
.miniatura {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid transparent;
  cursor: pointer;
  transition: border-color 0.3s;
}
.miniatura.activa {
  border-color: #28a745;
}

.producto-info {
  flex: 1 1 350px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  max-width: 600px;
  min-width: 0;
}

.producto-info h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #222;
  margin-bottom: 5px;
}

.producto-precio {
  font-size: 2rem;
  font-weight: 800;
  color: #28a745;
  margin-bottom: 20px;
}

.producto-codigo {
  font-size: 0.9rem;
  color: #666;
  font-weight: 600;
  margin-bottom: 10px;
}

/* Calificación por estrellas */
.estrellas {
  display: flex;
  gap: 6px;
  margin-bottom: 15px;
  font-size: 1.7rem;
  user-select: none;
  align-items: center;
}
.estrellas i { color: #ccc; transition: color 0.2s; }
.estrellas i.seleccionada { color: #ffbc00; }
.estrellas span {
  font-size: 1.2rem;
  color: #666;
  margin-left: 12px;
  font-weight: 600;
}

/* Presentacion opciones */
.presentacion-opciones {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.presentacion-opciones button {
  border: 1.5px solid #ccc;
  background: white;
  font-size: 1rem;
  padding: 10px 16px;
  cursor: pointer;
  border-radius: 8px;
  font-weight: 600;
  color: #444;
  transition: all 0.3s;
  min-width: 90px;
}
.presentacion-opciones button.activa,
.presentacion-opciones button:hover {
  border-color: #28a745;
  background: #28a745;
  color: white;
}

/* Control cantidad */
.cantidad-control {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 30px;
}
.cantidad-control button {
  width: 36px;
  height: 36px;
  border: 1.6px solid #28a745;
  background: white;
  color: #28a745;
  font-weight: 700;
  font-size: 1.6rem;
  cursor: pointer;
  border-radius: 8px;
  line-height: 0;
  user-select: none;
  transition: background 0.3s;
}
.cantidad-control button:hover {
  background: #28a745;
  color: white;
}
.cantidad-control input {
  width: 60px;
  text-align: center;
  font-size: 1.2rem;
  font-weight: 600;
  border: 1.6px solid #28a745;
  border-radius: 8px;
  outline: none;
  -moz-appearance: textfield;
}
.cantidad-control input::-webkit-outer-spin-button,
.cantidad-control input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Botón agregar */
.btn-agregar {
  background: #28a745;
  border: none;
  color: white;
  font-weight: 700;
  font-size: 1.3rem;
  padding: 14px 25px;
  border-radius: 10px;
  cursor: pointer;
  box-shadow: 0 6px 12px rgba(40, 160, 90, 0.3);
  transition: background 0.3s;
  width: 100%;
  max-width: 320px;
  align-self: start;
}
.btn-agregar:hover { background: #218838; }

/* Pestañas */
.pestanas {
  display: flex;
  gap: 20px;
  margin-bottom: 35px;
  border-bottom: 2px solid #ddd;
  flex-wrap: wrap;
}
.pestanas button {
  border: none;
  background: none;
  font-weight: 600;
  font-size: 1.1rem;
  padding: 14px 20px;
  cursor: pointer;
  color: #555;
  border-bottom: 3px solid transparent;
  transition: all 0.3s;
}
.pestanas button.activo,
.pestanas button:hover {
  border-color: #28a745;
  color: #28a745;
}
.pestanas-contenido > div {
  display: none;
  color: #444;
  font-size: 1rem;
  line-height: 1.5;
  padding-bottom: 50px;
}
.pestanas-contenido > div.activo {
  display: block;
}

/* Opiniones */
.comentario {
  margin-bottom: 20px;
  border-bottom: 1px solid #ddd;
  padding-bottom: 10px;
}
.comentario strong { font-weight: 700; font-size: 1.05rem; color: #222; }
.comentario em { color: #999; font-size: 0.9rem; margin-left: 8px; }
.comentario p { margin-top: 5px; font-size: 1rem; color: #333; }
.sin-opiniones { font-style: italic; color: #777; margin-top: 15px; }

/* Formulario opinion */
.form-opinion {
  margin-top: 30px;
  max-width: 500px;
}
.form-opinion textarea {
  width: 100%;
  min-height: 90px;
  padding: 10px;
  font-size: 1rem;
  border-radius: 8px;
  border: 1.5px solid #ccc;
  resize: vertical;
}
.form-opinion label {
  font-weight: 600;
  margin-top: 10px;
  display: block;
}
.form-opinion button {
  margin-top: 12px;
  background: #28a745;
  border: none;
  color: white;
  font-weight: 700;
  padding: 12px 20px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.3s;
}
.form-opinion button:hover { background: #218838; }

/* Recomendados */
.recomendados {
  max-width: 1100px;
  margin: 0 auto 30px auto;
}
.recomendados h2 {
  font-size: 1.6rem;
  font-weight: 700;
  margin-bottom: 25px;
  color: #222;
  text-align: center;
}
.productos-recomendados {
  display: grid;
  grid-template-columns: repeat(auto-fill,minmax(160px,1fr));
  gap: 20px;
}
.productos-recomendados .producto-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgb(0 0 0 / 0.1);
  padding: 10px;
  text-align: center;
  cursor: pointer;
  transition: box-shadow 0.3s;
}
.productos-recomendados .producto-card:hover {
  box-shadow: 0 4px 15px #28a745;
}
.productos-recomendados .producto-card img {
  width: 100%;
  max-width: 140px;
  height: 120px;
  object-fit: contain;
  margin-bottom: 10px;
  border-radius: 8px;
}
.productos-recomendados .producto-card h3 {
  font-size: 1rem;
  color: #222;
  font-weight: 600;
}
.productos-recomendados .producto-card .precio {
  color: #28a745;
  font-weight: 700;
  margin-top: 5px;
  font-size: 1.1rem;
}

/* Footer */
footer {
  background: #222;
  color: white;
  padding: 20px 15px;
  text-align: center;
}
footer h3 { margin-bottom: 15px; font-weight: 700; font-size: 1.5rem; }
footer .social-links a {
  color: white;
  margin: 0 10px;
  font-size: 1.5rem;
  transition: color 0.3s;
}
footer .social-links a:hover { color: #28a745; }
footer p { margin-top: 15px; font-size: 0.9rem; }

/* MODAL */
.modal { display:none; position:fixed; z-index:1000; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.55);}
.modal-contenido { background:#fff; margin:5% auto; padding:30px; width:90%; max-width:800px; border-radius:18px; box-shadow:0 0 20px rgba(0,0,0,0.18);}
.modal-contenido h2 { text-align:center; margin-bottom:20px; color: #28a745; font-size:1.55em; font-weight:700; }
.modal-contenido table { width:100%; border-collapse:collapse; margin-bottom:18px; }
.modal-contenido th, .modal-contenido td { padding:15px 7px; text-align:center; border:1px solid #ccc; font-size: 1rem; background: #fff; }
.modal-contenido thead th { background:#f4f4f4; color: #333; font-weight: 600;}
.modal-contenido img { width:56px; height:56px; object-fit:cover; border-radius: 7px;}
.modal-contenido button, .modal-contenido form button { margin:7px 8px; padding:14px 25px; border:none; border-radius:9px; background:#28a745; color:#fff; cursor:pointer; font-size:1.13em; min-width: 150px;}
.modal-contenido button:hover, .modal-contenido form button:hover { background:#218838; }
.modal-contenido form { display:inline-block;}

#tabla-carrito {
  margin-top: 30px;
  border-collapse: collapse;
  width: 100%;
  max-width: 800px;
  font-size: 0.9rem;
  box-shadow: 0 2px 12px rgb(0 0 0 / 0.1);
}
#tabla-carrito th, #tabla-carrito td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
}
#tabla-carrito th {
  background-color: #28a745;
  color: white;
  font-weight: 600;
}
#tabla-carrito img {
  width: 50px;
  height: 50px;
  object-fit: contain;
  border-radius: 6px;
}

/* ------------ BREAKPOINTS RESPONSIVE ------------ */

@media (max-width: 1100px) {
  main {
    max-width: 98vw;
    margin: 14px auto 24px auto;
    padding: 0 2vw;
  }
  .recomendados {
    max-width: 98vw;
    padding: 0 2vw;
  }
}

@media (max-width: 900px) {
  .producto-main {
    flex-direction: column;
    align-items: center;
    gap: 18px;
  }
  .producto-imagenes,
  .producto-info {
    max-width: 98vw;
    width: 100%;
  }
  .producto-info { padding: 0 5px; }
}

@media (max-width: 650px) {
  .producto-imagenes,
  .producto-info {
    padding: 0 2vw;
    box-shadow: none;
    border-radius: 10px;
    min-width: 0;
  }
  .imagen-principal {
    width: 98vw;
    max-width: 320px;
    height: 48vw;
    min-height: 160px;
  }
  .presentacion-opciones button {
    font-size: 0.97rem;
    padding: 9px 8px;
    min-width: 70px;
  }
  .btn-agregar { font-size: 1.05rem; }
}

@media (max-width: 480px) {
  body { padding: 0 !important; }
  main { margin: 10px auto 20px auto; padding: 10px 2vw;}
  .producto-info h1 { font-size: 1.18rem; }
  .producto-precio { font-size: 1.13rem; }
  .pestanas button { font-size: 1rem; padding: 10px 9px;}
  .cantidad-control input { width: 38px; font-size: 0.99rem;}
  .cantidad-control button { width: 27px; height: 27px; font-size: 1.1rem;}
  .miniatura { width: 40px; height: 40px;}
  .imagen-principal { width: 95vw; max-width: 220px; height: 35vw; min-height: 90px;}
  .btn-agregar { font-size: 0.96rem; padding: 11px 0; }
  header { flex-direction: column; gap: 5px;}
  header .logo-box img { height: 28px; margin: 2px 0; }
  header form { max-width: 95vw; }
  footer h3 { font-size: 1.1rem; }
  footer .social-links a { font-size: 1.09rem; }
  .recomendados h2 { font-size: 1.1rem; }
  .productos-recomendados { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
}

.producto-main {
  display: flex;
  flex-direction: row;
  align-items: flex-start !important;
  justify-content: flex-start;
  gap: 30px;
  min-height: 350px;
}

.producto-imagenes {
  min-width: 350px;
  max-width: 350px;
  align-self: flex-start;
}

.producto-info {
  flex: 1;
  min-width: 350px;
  max-width: 600px;
  text-align: left;
  align-items: flex-start;
  justify-content: flex-start;
  align-self: flex-start;
}

.imagen-principal {
  min-width: 320px;
  min-height: 320px;
  max-width: 100%;
  max-height: 60vw;
  object-fit: contain;
  background: #fff;
}

</style>
</head>
<body>

<header>
  <a href="Servicios.php" class="nav-link"><i class="fas fa-concierge-bell"></i> Servicios</a>
  <a href="index.php" class="logo-box"><img src="img/fondo.jpg" alt="Doggies" class="logo-img"></a>
  <form method="get" action="Productos.php" style="flex:1 1 300px; max-width:450px; display:flex;">
    <input 
      name="busqueda" 
      id="buscador-producto" 
      type="text" 
      placeholder="Buscar productos..." 
      autocomplete="off" 
      value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>"
      style="flex:1; padding:8px 12px; border: 2px solid #28a745; border-right:none; border-radius:6px 0 0 6px; font-size:1rem; outline:none;">
    <button type="submit" style="background:#28a745; border:none; color:white; padding:0 15px; cursor:pointer; border-radius:0 6px 6px 0; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">
      <i class="fas fa-search"></i>
    </button>
  </form>


<a href="carrito.php" class="nav-link">
  <i class="fas fa-cart-shopping" id="icono-carrito"></i>
  Carrito (<span id="contador-carrito"><?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?></span>)
</a>  
</header>

<main>
<div class="detalle-producto-container">
  <a href="Productos.php" class="volver-link">← Volver a productos</a>
  <div class="producto-main">

    <div class="producto-imagenes">
      <img id="imagen-principal" class="imagen-principal" src="<?php echo $imagenPrincipal; ?>" alt="<?php echo $nombre; ?>">

      <?php if (count($imagenes) > 1): ?>
        <div class="miniaturas">
          <?php foreach ($imagenes as $idx => $imgMini): ?>
            <img src="<?php echo $imgMini; ?>" alt="Miniatura <?php echo $idx+1; ?>" class="miniatura <?php echo $idx === 0 ? 'activa' : ''; ?>" data-index="<?php echo $idx; ?>">
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="producto-info">
      <h1><?php echo $nombre; ?></h1>
      <div class="estrellas" id="estrellas">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <i class="fa fa-star" data-valor="<?php echo $i; ?>"></i>
        <?php endfor; ?>
        <span id="puntuacion-texto" style="font-weight:600; margin-left: 10px; color:#666;">0 / 5</span>
      </div>

      <!-- Presentación y precio dinámico -->
<form id="form-carrito" method="POST" action="producto_detalle.php?id=<?php echo $id; ?>">
  <div>
    <label><strong>Presentación:</strong></label>
    <?php if (count($presentaciones) > 0): ?>
      <div class="presentacion-opciones" id="presentacion-opciones">
    <?php foreach ($presentaciones as $index => $p): ?>
      <button type="button"
        class="<?php echo $index==0 ? 'activa' : ''; ?>"
        data-precio="<?php echo floatval($p['Precio']); ?>"
        data-peso="<?php echo htmlspecialchars($p['Peso']); ?>"
        data-id="<?php echo intval($p['ID_Presentacion']); ?>">
        <?php echo htmlspecialchars($p['Peso']); ?>
      </button>
    <?php endforeach; ?>

      </div>
    <?php else: ?>
      <div style="padding: 7px 12px; background:#eaf2e4; color:#28a745; border-radius:8px; display:inline-block;">
        N/A
      </div>
    <?php endif; ?>
  </div>

  <div class="producto-precio" id="precio-mostrado">
    $<?php echo number_format($precioInicial, 0, ',', '.'); ?>
  </div>

  <input type="hidden" name="nombre" value="<?php echo $nombre; ?>">
  <input type="hidden" name="precio" id="input-precio" value="<?php echo $precioInicial; ?>">
  <input type="hidden" name="imagen" id="input-imagen" value="<?php echo $imagenPrincipal; ?>">
  <input type="hidden" name="id_presentacion" id="input-id-presentacion" value="<?php echo $presentaciones[0]['ID_Presentacion'] ?? ''; ?>">
  <input type="hidden" name="presentacion" id="input-presentacion" value="<?php echo $presentaciones[0]['ID_Presentacion'] ?? ''; ?>" class="input-presentacion">


  <label>Cantidad:</label>
  <div class="cantidad-control">
    <button type="button" onclick="cambiarCantidad(-1)">−</button>
    <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="25" readonly>
    <button type="button" onclick="cambiarCantidad(1)">+</button>
  </div>

  <?php if ($stock > 0): ?>
    <button type="submit" class="btn-agregar" <?php if ($stock <= 0) echo 'disabled'; ?>>Agregar al carrito</button>
  <?php else: ?>
    <p class="agotado">Producto agotado</p>
  <?php endif; ?>
</form>


      <div>
        <strong>Descripción:</strong>
        <p><?php echo nl2br($descripcion); ?></p>
      </div>
      <div>
        <strong>Ingredientes:</strong>
        <p><?php echo nl2br($ingredientes); ?></p>
      </div>
      <div class="stock">Stock disponible: <?php echo $stock; ?></div>
    </div>
  </div>

<!-- MODAL flotante de confirmación (carrito) -->
<div id="modal-confirmacion" class="modal">
  <div class="modal-contenido">
    <h2>Producto agregado al carrito</h2>
    <table>
    <thead>
      <tr>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Precio Unitario</th>
        <th>Subtotal</th>
      </tr>
    </thead>
      <tbody id="tabla-productos"></tbody>
    </table>
    <button id="continuar-comprando">Continuar comprando</button>
    <button id="editar-carrito">Editar carrito</button>
    <form action="Checkout.php" method="get" style="display:inline;">
      <button type="submit">Finalizar compra</button>
    </form>
  </div>
</div>

  <div class="pestanas">
    <button class="activo" onclick="abrirPestana(event, 'descripcion')">Descripción</button>
    <button onclick="abrirPestana(event, 'opiniones')">Opiniones</button>
    <button onclick="abrirPestana(event, 'info-nutricional')">Información Nutricional</button>
  </div>

  <div class="pestanas-contenido">
  <div id="descripcion" class="activo">
    <p><?php echo nl2br($descripcion); ?></p>
  </div>

  <div style="margin-bottom:10px;">
    <strong>Calificación promedio:</strong>
    <span>
      <?php for($i=1;$i<=5;$i++): ?>
        <i class="fa fa-star<?php echo ($i <= round($promedio)) ? ' seleccionada' : ''; ?>"></i>
      <?php endfor; ?>
      <?php echo "$promedio / 5 ($cantidad opiniones)"; ?>
    </span>
  </div>

  <div id="opiniones">
    <?php if (count($opiniones) > 0): ?>
      <?php foreach ($opiniones as $op): ?>
        <div class="comentario">
          <strong><?php echo htmlspecialchars($op['usuario']); ?></strong>
          <em>(<?php echo $op['fecha']; ?>)</em>
          <div class="estrellas" aria-label="Calificación: <?php echo $op['estrellas']; ?> de 5">
            <?php for ($i=1; $i<=5; $i++): ?>
              <i class="fa fa-star <?php echo $i <= $op['estrellas'] ? 'seleccionada' : ''; ?>"></i>
            <?php endfor; ?>
          </div>
          <p><?php echo htmlspecialchars($op['comentario']); ?></p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="sin-opiniones">No hay opiniones para este producto.</p>
    <?php endif; ?>

    <?php if ($usuarioLogueado): ?>
      <?php if (!isset($puedeOpinar)) $puedeOpinar = false; ?>
      <?php if (!isset($yaOpino)) $yaOpino = false; ?>
      <?php if (!$puedeOpinar): ?>
          <p>Solo puedes opinar si ya compraste este producto.</p>
      <?php elseif ($yaOpino): ?>
          <p>Ya dejaste una opinión para este producto.</p>
      <?php else: ?>
          <form id="form-opinion" class="form-opinion" method="POST" action="producto_detalle.php?id=<?php echo $id; ?>">
              <label for="comentario">Deja tu opinión:</label>
              <div class="estrellas" id="estrellas-form" aria-label="Selecciona una calificación de 1 a 5 estrellas">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                      <i class="fa fa-star" data-valor="<?php echo $i; ?>" tabindex="0" role="button" aria-label="Calificación <?php echo $i; ?>"></i>
                  <?php endfor; ?>
              </div>
              <input type="hidden" id="input-estrellas" name="estrellas" value="0">
              <textarea id="comentario" name="comentario" placeholder="Escribe aquí tu opinión..." required></textarea>
              <button type="submit" name="agregar_opinion">Enviar opinión</button>
          </form>
      <?php endif; ?>
    <?php else: ?>
        <p>Para dejar una opinión debes <a href="login.php">iniciar sesión</a>.</p>
    <?php endif; ?>
  </div>

  <div id="info-nutricional">
    <p><?php echo nl2br($producto['InfoNutricional'] ?? "Información nutricional no disponible."); ?></p>
  </div>
</div>

  <!-- Productos recomendados -->
  <section class="recomendados" aria-label="Productos recomendados">
    <h2>Productos recomendados</h2>
    <div class="productos-recomendados">
      <?php foreach($recomendados as $rec):
        $recNombre = htmlspecialchars($rec['Nombre']);
        $recImagen = $rec['Imagen_URL'] ?: 'img/default.jpg';
        $recPrecio = number_format(floatval($rec['Precio']),0,',','.');
        $recId = intval($rec['ID_Producto']);
      ?>
        <a href="producto_detalle.php?id=<?php echo $recId; ?>" class="producto-card" aria-label="Ver producto <?php echo $recNombre; ?>">
          <img src="<?php echo $recImagen; ?>" alt="<?php echo $recNombre; ?>">
          <h3><?php echo $recNombre; ?></h3>
          <div class="precio">$<?php echo $recPrecio; ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
</div>
</main>
<footer>
  <div class="footer-content">
    <h3>Síguenos</h3>
    <div class="social-links">
      <a href="https://www.facebook.com/profile.php?id=100069951193254" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
      <a href="https://www.instagram.com/doggiespaseadores/" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      <a href="https://www.tiktok.com/@doggies_paseadores" target="_blank" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
      <a href="mailto:doggiespasto22@gmail.com" aria-label="Correo"><i class="fas fa-envelope"></i></a>
    </div>
    <p>© 2025 Doggies. Todos los derechos reservados.</p>
  </div>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // PRESENTACIONES, cantidad y miniaturas
    document.querySelectorAll('.presentacion-opciones button').forEach(b => {
        b.addEventListener('click', function() {
            document.querySelectorAll('.presentacion-opciones button').forEach(btn => btn.classList.remove('activa'));
            b.classList.add('activa');
            document.getElementById('precio-mostrado').textContent = '$' + parseInt(b.dataset.precio).toLocaleString('es-CO');
            document.getElementById('input-precio').value = b.dataset.precio;
            // Cambiar aquí para usar id_presentacion (numérico)
            document.getElementById('input-presentacion').value = b.dataset.id;
            document.getElementById('input-id-presentacion').value = b.dataset.id;
        });
    });
    window.cambiarCantidad = function(valor) {
        const input = document.getElementById('cantidad');
        let val = parseInt(input.value) || 1;
        val += valor;
        if(val < 1) val = 1;
        if(val > 25) val = 25;
        input.value = val;
    };

    window.abrirPestana = function(evt, nombre) {
        document.querySelectorAll('.pestanas-contenido > div').forEach(div => div.classList.remove('activo'));
        document.querySelectorAll('.pestanas button').forEach(btn => btn.classList.remove('activo'));
        document.getElementById(nombre).classList.add('activo');
        evt.currentTarget.classList.add('activo');
    };

    // ESTRELLAS FORMULARIO DE OPINIÓN
    const estrellasForm = document.querySelectorAll('#estrellas-form i');
    const inputEstrellas = document.getElementById('input-estrellas');
    let calificacionSeleccionada = 0;
    estrellasForm.forEach(star => {
        star.addEventListener('click', () => {
            calificacionSeleccionada = parseInt(star.dataset.valor);
            inputEstrellas.value = calificacionSeleccionada;
            actualizarEstrellasForm(calificacionSeleccionada);
        });
        star.addEventListener('keydown', e => {
            if(e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                calificacionSeleccionada = parseInt(star.dataset.valor);
                inputEstrellas.value = calificacionSeleccionada;
                actualizarEstrellasForm(calificacionSeleccionada);
            }
        });
        star.addEventListener('mouseover', () => actualizarEstrellasForm(parseInt(star.dataset.valor)));
        star.addEventListener('mouseout', () => actualizarEstrellasForm(calificacionSeleccionada));
    });
    function actualizarEstrellasForm(n) {
        estrellasForm.forEach(star => {
            star.classList.toggle('seleccionada', parseInt(star.dataset.valor) <= n);
        });
    }
    // Validación de estrellas para opinión
    const formOpinion = document.getElementById('form-opinion');
    if (formOpinion) {
        formOpinion.addEventListener('submit', function(e) {
            if (parseInt(inputEstrellas.value) === 0) {
                e.preventDefault();
                alert('Selecciona una calificación en estrellas antes de enviar tu opinión.');
            }
        });
    }

// AGREGAR AL CARRITO (AJAX) Y MOSTRAR MODAL
var formCarrito = document.getElementById('form-carrito');
if (formCarrito) {
    formCarrito.addEventListener('submit', function(e) {
        e.preventDefault();
        var datos = new FormData(formCarrito);
        fetch(formCarrito.action, {
            method: 'POST',
            body: datos,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(resp => resp.json())
        .then(data => {
            document.getElementById('contador-carrito').textContent = data.contador;
          // Encuentra el producto agregado/actualizado realmente
          var nombre = formCarrito.querySelector('input[name="nombre"]').value;
          var precio = parseFloat(formCarrito.querySelector('input[name="precio"]').value);
          var presentacion = formCarrito.querySelector('input[name="presentacion"]').value.trim().toLowerCase();

          var idPres = formCarrito.querySelector('#input-id-presentacion').value;
          var last = data.carrito.find(item =>
              item.nombre === nombre &&
              parseFloat(item.precio) === precio &&
              item.id_presentacion == idPres
          );

          if (!last) last = data.carrito[data.carrito.length - 1];

            var nombreCompleto = last.nombre + ' - ' + last.presentacion;
document.getElementById('tabla-productos').innerHTML = `
  <tr>
    <td>
      <div style="display:flex;align-items:center;gap:10px;">
        <img src="${last.imagen}" alt="${last.nombre}" style="width:48px;height:48px;object-fit:contain;border-radius:6px;">
        <div style="text-align:left;">
          <div style="font-weight:700;color:#333;">
            ${last.nombre} <span style="font-weight:700;font-size:0.98em;color:#222;">(<strong>${last.presentacion}</strong>)</span>
          </div>
        </div>
      </div>
    </td>
    <td style="text-align:center;">
      <input
        type="number"
        id="modal-cantidad"
        min="1"
        max="25"
        value="${last.cantidad}"
        style="
          width:44px;
          text-align:center;
          font-size:1.08rem;
          border:1.2px solid black;
          border-radius:7px;
          background:#fff;
          outline:none;
          transition:border .2s;
          font-weight:400;
        "
      >
    </td>
    <td style="text-align:center;">
      $${parseInt(last.precio).toLocaleString('es-CO')}
    </td>
    <td id="modal-subtotal" style="text-align:center;">
      $${(last.precio * last.cantidad).toLocaleString('es-CO')}
    </td>
  </tr>
`;





// Evento para actualizar cantidad y subtotal en tiempo real y en el backend
document.getElementById('modal-cantidad').addEventListener('input', function() {
  let nuevaCantidad = parseInt(this.value) || 1;
  if (nuevaCantidad < 1) nuevaCantidad = 1;
  if (nuevaCantidad > 25) nuevaCantidad = 25;
  this.value = nuevaCantidad;

  fetch('producto_detalle.php?id=<?php echo $id; ?>', {
    method: 'POST',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'update=1&cantidad=' + nuevaCantidad + '&id_presentacion=' + encodeURIComponent(last.id_presentacion)

  })
  .then(resp => resp.json())
  .then(data => {
    document.getElementById('modal-subtotal').textContent =
      "$" + (last.precio * nuevaCantidad).toLocaleString('es-CO');
    document.getElementById('contador-carrito').textContent = data.contador;
  });
});


              // 🚀 ANTES de mostrar el modal, ejecuta la animación:
              const imgElem = document.getElementById('imagen-principal');
              const cartElem = document.getElementById('icono-carrito');
              if (imgElem && cartElem) flyToCartAnim(imgElem.src, imgElem, cartElem);

              // Ahora sí, muestra el modal
              document.getElementById('modal-confirmacion').style.display = 'block';
          });
    });
}



var btnContinuar = document.getElementById('continuar-comprando');
if (btnContinuar) btnContinuar.onclick = function() {
    document.getElementById('modal-confirmacion').style.display = 'none';
};
var btnEditar = document.getElementById('editar-carrito');
if (btnEditar) btnEditar.onclick = function() {
    window.location = 'carrito.php';
};

function flyToCartAnim(imgSrc, startElem, cartElem) {
    const img = document.createElement('img');
    img.src = imgSrc;
    img.style.position = 'fixed';
    const rect = startElem.getBoundingClientRect();
    img.style.left = rect.left + "px";
    img.style.top = rect.top + "px";
    img.style.width = "70px";
    img.style.height = "70px";
    img.style.borderRadius = "16px";
    img.style.zIndex = 5000; // <-- subido para ir sobre todo
    img.style.transition = "all 0.7s cubic-bezier(.6,-0.28,.74,.05)";

    document.body.appendChild(img);

    const cartRect = cartElem.getBoundingClientRect();
    setTimeout(() => {
        img.style.left = cartRect.left + 20 + "px";
        img.style.top = cartRect.top + "px";
        img.style.width = "30px";
        img.style.height = "30px";
        img.style.opacity = 0.4;
    }, 30);

    setTimeout(() => {
        img.remove();
    }, 750);
}

});

(function(){
    const principal = document.getElementById('imagen-principal');
    const miniaturas = Array.from(document.querySelectorAll('.miniatura'));
    if (!principal || miniaturas.length === 0) return;

    principal.style.cursor = 'zoom-in';

    // Modal de zoom
    let modalZoom = document.createElement('div');
    modalZoom.style.cssText = 'display:none;position:fixed;z-index:12000;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.48);align-items:center;justify-content:center;backdrop-filter:blur(2px);';
    modalZoom.innerHTML = `
        <button id="zoom-prev" style="position:absolute;left:30px;top:50%;transform:translateY(-50%);background:none;border:none;color:#fff;font-size:3em;cursor:pointer;">&#8678;</button>
        <img id="zoom-img" style="max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 4px 24px #0007;">
        <button id="zoom-next" style="position:absolute;right:30px;top:50%;transform:translateY(-50%);background:none;border:none;color:#fff;font-size:3em;cursor:pointer;">&#8680;</button>
        <span style="position:absolute;top:30px;right:50px;font-size:3em;color:#fff;cursor:pointer;">&times;</span>
    `;
    document.body.appendChild(modalZoom);

    let currIndex = 0;

    function showZoom(idx) {
        currIndex = idx;
        modalZoom.style.display = 'flex';
        modalZoom.querySelector('#zoom-img').src = miniaturas[currIndex].src;
        updateNavButtons();
        modalZoom.focus();
    }

    function updateNavButtons() {
        modalZoom.querySelector('#zoom-prev').style.opacity = currIndex === 0 ? '0.4' : '1';
        modalZoom.querySelector('#zoom-next').style.opacity = currIndex === miniaturas.length-1 ? '0.4' : '1';
    }

    principal.addEventListener('click', function(){
        // buscar cuál miniatura está activa
        let idx = miniaturas.findIndex(m => m.classList.contains('activa'));
        if (idx < 0) idx = 0;
        showZoom(idx);
    });
    principal.tabIndex = 0;
    principal.addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
            let idx = miniaturas.findIndex(m => m.classList.contains('activa'));
            if (idx < 0) idx = 0;
            showZoom(idx);
        }
    });

    // Flechas y cerrar modal con teclado
    modalZoom.addEventListener('keydown', function(e){
        if (e.key === 'Escape') modalZoom.style.display = 'none';
        if (e.key === 'ArrowLeft') goPrev();
        if (e.key === 'ArrowRight') goNext();
    });
    modalZoom.tabIndex = 0;

    // Botones prev y next
    modalZoom.querySelector('#zoom-prev').onclick = goPrev;
    modalZoom.querySelector('#zoom-next').onclick = goNext;
    modalZoom.querySelector('span').onclick = function(){
        modalZoom.style.display = 'none';
    };
    modalZoom.addEventListener('click', function(e){
        if (e.target === modalZoom) modalZoom.style.display = 'none';
    });

    function goPrev() {
        if (currIndex > 0) {
            currIndex--;
            modalZoom.querySelector('#zoom-img').src = miniaturas[currIndex].src;
            updateNavButtons();
        }
    }
    function goNext() {
        if (currIndex < miniaturas.length-1) {
            currIndex++;
            modalZoom.querySelector('#zoom-img').src = miniaturas[currIndex].src;
            updateNavButtons();
        }
    }

    // También permite navegar con click sobre miniaturas (en la página normal)
    miniaturas.forEach(function(mini, idx){
        function activarMiniatura() {
            document.getElementById('imagen-principal').src = mini.src;
            miniaturas.forEach(m => m.classList.remove('activa'));
            mini.classList.add('activa');
        }
        mini.addEventListener('mouseover', activarMiniatura); // <--- HOVER para escritorio
        mini.addEventListener('focus', activarMiniatura);     // <--- HOVER con tab (accesibilidad)
        mini.addEventListener('click', activarMiniatura);     // Por si alguien sí quiere clickear
        mini.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                activarMiniatura();
            }
        });
        mini.tabIndex = 0;
    });


})();



</script>

</body>
</html>