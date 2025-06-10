<?php
require 'conexion.php';

if (!isset($_GET['id'])) {
    echo "ID de producto no proporcionado.";
    exit;
}

$id = $_GET['id'];

// Obtener producto
try {
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE ID_Producto = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo "Producto no encontrado.";
        exit;
    }
} catch (PDOException $e) {
    echo "Error al obtener el producto: " . $e->getMessage();
    exit;
}

// Presentaciones del producto
try {
    $stmt = $pdo->prepare("SELECT * FROM presentacion WHERE ID_Producto = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $presentaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener las presentaciones: " . $e->getMessage();
    $presentaciones = [];
}

// Imágenes adicionales del producto (tabla producto_imagen)
try {
    $stmt = $pdo->prepare("SELECT * FROM producto_imagen WHERE ID_Producto = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $imagenes_adicionales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $imagenes_adicionales = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $ingredientes = $_POST['ingredientes'] ?? '';
    $info_nutricional = $_POST['info_nutricional'] ?? '';
    $imagen_principal = $_POST['imagen'] ?? '';
    $edad = $_POST['edad'] ?? '';
    $stock = $_POST['stock'] ?? 0;
    $alto_cm = $_POST['alto_cm'] ?? null;
    $largo_cm = $_POST['largo_cm'] ?? null;
    $ancho_cm = $_POST['ancho_cm'] ?? null;

    try {
        $sql = "UPDATE producto 
                SET Nombre = :nombre, 
                    Descripcion = :descripcion, 
                    Ingredientes = :ingredientes,
                    InfoNutricional = :info_nutricional,
                    Imagen_URL = :imagen, 
                    Edad = :edad, 
                    Stock = :stock, 
                    alto_cm = :alto_cm, 
                    largo_cm = :largo_cm, 
                    ancho_cm = :ancho_cm 
                WHERE ID_Producto = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':ingredientes', $ingredientes);
        $stmt->bindParam(':info_nutricional', $info_nutricional);
        $stmt->bindParam(':imagen', $imagen_principal);
        $stmt->bindParam(':edad', $edad);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':alto_cm', $alto_cm);
        $stmt->bindParam(':largo_cm', $largo_cm);
        $stmt->bindParam(':ancho_cm', $ancho_cm);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Presentaciones
        $presentacionesPost = $_POST['presentaciones'] ?? [];
        $idsActuales = array_column($presentaciones, 'ID_Presentacion');
        $idsRecibidos = array_filter(array_column($presentacionesPost, 'id'));
        // Eliminar presentaciones que ya no están
        $idsAEliminar = array_diff($idsActuales, $idsRecibidos);
        if (!empty($idsAEliminar)) {
            $in = str_repeat('?,', count($idsAEliminar) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM presentacion WHERE ID_Presentacion IN ($in)");
            $stmt->execute($idsAEliminar);
        }
        // Insertar/actualizar presentaciones
        foreach ($presentacionesPost as $pres) {
            $peso_valor = $pres['peso'];
            $unidad = $pres['unidad'];
            $peso_completo = $peso_valor . $unidad;
            $precio_pres = $pres['precio'];
            $idPres = $pres['id'];
            if ($idPres) {
                $stmt = $pdo->prepare("UPDATE presentacion SET Peso = ?, Precio = ? WHERE ID_Presentacion = ?");
                $stmt->execute([$peso_completo, $precio_pres, $idPres]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO presentacion (ID_Producto, Peso, Precio) VALUES (?, ?, ?)");
                $stmt->execute([$id, $peso_completo, $precio_pres]);
            }
        }

        // Imágenes adicionales
        if (isset($_POST['imagenes_adicionales'])) {
            $stmt = $pdo->prepare("DELETE FROM producto_imagen WHERE ID_Producto = ?");
            $stmt->execute([$id]);
            foreach ($_POST['imagenes_adicionales'] as $img_url) {
                $img_url = trim($img_url);
                if ($img_url !== '') {
                    $stmt = $pdo->prepare("INSERT INTO producto_imagen (ID_Producto, URL_Imagen) VALUES (?, ?)");
                    $stmt->execute([$id, $img_url]);
                }
            }
        }

        echo "<script>alert('Producto actualizado correctamente.'); window.location.href='admin.php';</script>";
        exit;
    } catch (PDOException $e) {
        echo "Error al actualizar el producto: " . $e->getMessage();
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Producto - Doggies</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto&display=swap" rel="stylesheet">
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
<style>
  body {
    margin: 0;
    font-family: 'Roboto', sans-serif;
    background: #f0f2f5;
    min-height: 100vh;
    color: #232323;
    display: flex;
    flex-direction: column;
  }
  header {
    background-color: rgba(255,255,255,0.97);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 1em 0;
  }
  .menu {
    display: flex;
    justify-content: center;
    align-items: center;
    list-style: none;
    padding: 0 12px;
    margin: 0;
    gap: 28px;
    flex-wrap: wrap;
  }
  .menu li { text-align: center; }
  .menu li.logo a {
    display: block;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto;
  }
  .menu li.logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 2.5px solid #fff;
    box-shadow: 0 1px 8px #bbb;
    background: #fff;
  }
  .menu a {
    text-decoration: none;
    color: #232323;
    font-weight: bold;
    font-size: 1rem;
    padding: 7px 7px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: background .16s, color .16s;
  }
  
  .menu a:hover {
  background-color: #28a745; /* verde */
  color: white !important;
  border-radius: 6px;
  padding: 8px 12px; /* para que el fondo abarque bien */
  transition: background-color 0.3s ease, color 0.3s ease;
}

  .menu a:hover i {
    color: white !important;
  }

  /* Eliminar hover verde en el logo */
.menu li.logo a:hover,
.menu li.logo a:hover i {
  background-color: transparent !important;
  color: inherit !important;
  padding: 7px 7px !important; /* Igual que el padding normal para no cambiar tamaño */
  border-radius: 0 !important;
  transition: none !important;
}


  /* --- FORMULARIO --- */
  .form-container {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 34px 0 30px 0;
    flex: 1;
    min-height: 85vh;
  }
  .form-box {
    background: #fff;
    padding: 2.1em 2.5em 1.2em 2.5em;
    border-radius: 14px;
    box-shadow: 0 8px 24px 0 rgba(0,0,0,0.10);
    width: 100%;
    max-width: 560px;
    margin: 0 1.2em;
  }
  .form-box h2 {
    text-align: center;
    margin-top: 0;
    margin-bottom: 1.3em;
    color:black;
    font-family: 'Playfair Display', serif;
    font-weight: 800;
    font-size: 2.2em;
    letter-spacing: -1px;
  }
  label {
    display: block;
    margin-bottom: 0.9em;
    color: #2e3955;
    font-weight: 600;
    font-size: 1em;
  }
  input[type="text"], input[type="number"], textarea, select {
    width: 100%;
    padding: 0.72em;
    border: 1.3px solid #c6d2d8;
    border-radius: 6px;
    font-size: 1em;
    margin-top: 0.37em;
    background: #f5f7fa;
    color: #222;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.19s;
  }
  input:focus, textarea:focus, select:focus { border-color: #28a745; }

  textarea { resize: vertical; min-height: 38px; }

  .presentacion-item, .extra-img-row {
    margin-bottom: 10px;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 10px;
  }
  .pres-peso, .pres-precio {
    display: flex;
    flex-direction: column;
    min-width: 100px;
    flex: 1 1 120px;
    max-width: 160px;
  }
  .presentacion-item label,
  .pres-peso label, .pres-precio label {
    margin-bottom: 2px;
    font-weight: 500;
    font-size: 0.98em;
  }
  .extra-img-row input[type="text"] { flex: 1; min-width: 90px; }
  .extra-img-row button { background: #c43; color: #fff; border: none; padding: 5px 16px; border-radius: 5px; cursor: pointer; font-weight: 500;}
  .extra-img-row button:hover { background: #d4411e;}
  .miniaturas-preview {
    margin: 8px 0 12px 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }
  .miniaturas-preview img {
    width: 54px;
    height: 54px;
    object-fit: cover;
    border-radius: 7px;
    border: 1.5px solid #ddd;
    background: #f8f8f8;
  }
  button[type="submit"], .form-box button:not(.btn-eliminar) {
    background-color: #229157;
    color: #fff;
    padding: 0.72em 1.7em;
    border: none;
    border-radius: 7px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    margin-top: 1.1em;
    margin-bottom: 0.5em;
    transition: background 0.18s;
    box-shadow: 0 1.5px 4px 0 rgba(46,170,84,0.09);
    display: block;
    width: 100%;
  }
  button[type="submit"]:hover, .form-box button:not(.btn-eliminar):hover { background: #217947; }
  .btn-eliminar {
    background: #e55 !important;
    color: #fff !important;
    margin-bottom: 0;
    border-radius: 5px;
    font-weight: 500;
    padding: 7px 18px;
    transition: background .15s;
  }
  .btn-eliminar:hover { background: #b22 !important; }

  /* --- RESPONSIVE --- */
  @media (max-width: 700px) {
    .form-container {
      padding: 13px 0 18px 0;
    }
    .form-box {
      padding: 1.25em 0.85em 1.1em 0.85em;
      max-width: 99vw;
    }
    .menu { gap: 8px; }
    .menu li.logo a, .menu li.logo img {
      width: 38px; height: 38px; min-width: 36px; min-height: 36px;
    }
    .form-box h2 { font-size: 1.4em; }
    label { font-size: 0.97em; }
  }
  @media (max-width: 540px) {
    .presentacion-item, .extra-img-row {
      flex-direction: column;
      align-items: stretch;
      gap: 2px;
    }
    .miniaturas-preview img { width: 43px; height: 43px; }
    .form-container { padding: 0.5em 0 0.5em 0; }
    .form-box { padding: 1em 0.2em; }
    .menu { font-size: 0.97em; }
  }

    footer {
    background-color: rgba(51,51,51,0.98);
    color: #fff;
    text-align: center;
    padding: 1.5em 2em 1em 2em;
    position: relative;
    margin-top: 18px;
  }
  .footer-content h3 {
    font-size: 1.6em;
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
    transition: color 0.3s;
    text-decoration: none;
  }
  .social-links a:hover { color: #ffd700; }
  footer::after {
    content: "© 2025 Doggies. Todos los derechos reservados.";
    display: block;
    margin-top: 1em;
    font-size: 0.9em;
    color: #ccc;
  }

</style>

</head>
<body>
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php"><i class="fas fa-dog"></i> Productos</a></li>
        <li class="logo">
        <a href="index.php">
          <img src="img/fondo.jpg" alt="Logo Doggies" class="logo-img">
        </a>
        </li>

        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
      </ul>
    </nav>
  </header>
  <div class="form-container">
    <div class="form-box">
      <h2>Editar Producto</h2>
<form method="post">
  <label>Nombre:
    <input type="text" name="nombre" value="<?= htmlspecialchars($producto['Nombre']) ?>" required>
  </label>

  <label>Descripción:
    <textarea name="descripcion" required><?= htmlspecialchars($producto['Descripcion']) ?></textarea>
  </label>

  <label>Ingredientes:
    <textarea name="ingredientes" required><?= htmlspecialchars($producto['Ingredientes'] ?? '') ?></textarea>
  </label>

  <label>Información Nutricional:
    <textarea name="info_nutricional"><?= htmlspecialchars($producto['InfoNutricional'] ?? '') ?></textarea>
  </label>

  <label>URL de la Imagen Principal:
    <input type="text" name="imagen" value="<?= htmlspecialchars($producto['Imagen_URL']) ?>" required>
  </label>
  <div>
    <strong>Vista previa:</strong><br>
    <img src="<?= htmlspecialchars($producto['Imagen_URL']) ?>" alt="Principal" style="width:85px;height:85px;object-fit:cover;border-radius:6px;border:1px solid #ccc;margin-top:4px;">
  </div>
  <br>

  <!-- Imágenes adicionales -->
  <label>Imágenes Adicionales:</label>
  <div id="imagenes-adicionales-container">
    <?php foreach ($imagenes_adicionales as $k => $img): ?>
      <div class="extra-img-row">
        <input type="text" name="imagenes_adicionales[]" value="<?= htmlspecialchars($img['URL_Imagen']) ?>" placeholder="URL de la imagen adicional">
        <button type="button" onclick="this.parentNode.remove()">Eliminar</button>
      </div>
    <?php endforeach; ?>
  </div>
  <button type="button" onclick="agregarCampoImagenAdicional()">Agregar otra imagen</button>
  <div class="miniaturas-preview">
    <?php foreach ($imagenes_adicionales as $img): ?>
      <img src="<?= htmlspecialchars($img['URL_Imagen']) ?>" alt="Miniatura">
    <?php endforeach; ?>
  </div>
  <br>

  <label>Edad:
    <select name="edad" required>
      <option value="cachorro" <?= (isset($producto['Edad']) && $producto['Edad'] === 'cachorro') ? 'selected' : '' ?>>Cachorro</option>
      <option value="adulto" <?= (isset($producto['Edad']) && $producto['Edad'] === 'adulto') ? 'selected' : '' ?>>Adulto</option>
      <option value="senior" <?= (isset($producto['Edad']) && $producto['Edad'] === 'senior') ? 'selected' : '' ?>>Senior</option>
    </select>
  </label>

  <label>Stock:
    <input type="number" name="stock" value="<?= htmlspecialchars($producto['Stock']) ?>" required>
  </label>

  <label>Alto (cm):
    <input type="number" step="0.01" name="alto_cm" value="<?= htmlspecialchars($producto['alto_cm']) ?>">
  </label>

  <label>Largo (cm):
    <input type="number" step="0.01" name="largo_cm" value="<?= htmlspecialchars($producto['largo_cm']) ?>">
  </label>

  <label>Ancho (cm):
    <input type="number" step="0.01" name="ancho_cm" value="<?= htmlspecialchars($producto['ancho_cm']) ?>">
  </label>

  <!-- Presentaciones dinámicas -->
  <div id="presentaciones-container">
    <?php foreach ($presentaciones as $index => $pres): ?>
      <?php
        $peso_valor = floatval($pres['Peso']);
        if (strpos($pres['Peso'], 'kg') !== false) {
            $unidad = 'kg';
        } elseif (strpos($pres['Peso'], 'g') !== false) {
            $unidad = 'g';
        } elseif (strpos($pres['Peso'], 'Lb') !== false) {
            $unidad = 'Lb';
        } else {
            $unidad = '';
        }
      ?>
      <div class="presentacion-item" data-index="<?= $index ?>">
        <input type="hidden" name="presentaciones[<?= $index ?>][id]" value="<?= $pres['ID_Presentacion'] ?>">
        <div class="pres-peso">
          <label>Peso:
            <input type="number" step="0.01" min="0" name="presentaciones[<?= $index ?>][peso]" value="<?= htmlspecialchars($peso_valor) ?>" required>
          </label>
        <select name="presentaciones[<?= $index ?>][unidad]" required>
            <option value="kg" <?= ($unidad == 'kg') ? 'selected' : '' ?>>kg</option>
            <option value="g" <?= ($unidad == 'g') ? 'selected' : '' ?>>g</option>
            <option value="Lb" <?= ($unidad == 'Lb') ? 'selected' : '' ?>>Lb</option>
        </select>
        </div>
        <div class="pres-precio">
          <label>Precio:
            <input type="number" step="0.01" min="0" name="presentaciones[<?= $index ?>][precio]" value="<?= $pres['Precio'] ?>" required>
          </label>
        </div>
        <button type="button" class="btn-eliminar" onclick="this.parentNode.remove()">Eliminar</button>
      </div>
    <?php endforeach; ?>
  </div>
  <button type="button" onclick="agregarPresentacion()">Agregar presentación</button>
  <button type="submit">Guardar Cambios</button>
</form>
    </div>
  </div>
<script>
function agregarPresentacion() {
  const container = document.getElementById('presentaciones-container');
  const index = container.children.length;
  const div = document.createElement('div');
  div.className = 'presentacion-item';
  div.dataset.index = index;
  div.innerHTML = `
    <input type="hidden" name="presentaciones[${index}][id]" value="">
    <div class="pres-peso">
      <label>Peso:
        <input type="number" step="0.01" min="0" name="presentaciones[${index}][peso]" required>
      </label>
    <select name="presentaciones[${index}][unidad]" required>
        <option value="kg">kg</option>
        <option value="g">g</option>
        <option value="Lb">Lb</option>
    </select>
    </div>
    <div class="pres-precio">
      <label>Precio:
        <input type="number" step="0.01" min="0" name="presentaciones[${index}][precio]" required>
      </label>
    </div>
    <button type="button" class="btn-eliminar" onclick="this.parentNode.remove()">Eliminar</button>
  `;
  container.appendChild(div);
}
function agregarCampoImagenAdicional() {
    let container = document.getElementById('imagenes-adicionales-container');
    let div = document.createElement('div');
    div.className = 'extra-img-row';
    div.innerHTML = `
        <input type="text" name="imagenes_adicionales[]" placeholder="URL de la imagen adicional" style="width:85%">
        <button type="button" onclick="this.parentNode.remove()">Eliminar</button>
    `;
    container.appendChild(div);
}
</script>
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
