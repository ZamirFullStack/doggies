<?php
session_start();
require 'conexion.php';

// ---- Incluye PHPMailer ----
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['ID_Rol'] != 2) {
    header("Location: index.php");
    exit;
}

$nombreAdmin = $_SESSION['usuario']['Nombre'];
$mensaje = "";

// --- Actualización de estado/guía ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_pedido'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $nuevo_estado = $_POST['nuevo_estado'];
    $guia = trim($_POST['guia']);
    $empresa = trim($_POST['empresa']);
    $link_rastreo = trim($_POST['link_rastreo']);

    $stmt = $pdo->prepare("UPDATE pedido SET Estado=?, Guia=?, Empresa_Guia=?, Link_Rastreo=? WHERE ID_Pedido=?");
    $stmt->execute([$nuevo_estado, $guia, $empresa, $link_rastreo, $id_pedido]);
    $mensaje = "<div style='color:green;font-weight:bold;'>✅ Pedido actualizado correctamente.</div>";
}

// --- Envío de email solo si se presiona el botón enviar_email ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_email'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $nuevo_estado = $_POST['nuevo_estado'];
    $guia = trim($_POST['guia']);
    $empresa = trim($_POST['empresa']);
    $link_rastreo = trim($_POST['link_rastreo']);

    // Actualiza el pedido a "despachado" y guarda datos de logística
    $stmt = $pdo->prepare("UPDATE pedido SET Estado=?, Guia=?, Empresa_Guia=?, Link_Rastreo=? WHERE ID_Pedido=?");
    $stmt->execute([$nuevo_estado, $guia, $empresa, $link_rastreo, $id_pedido]);

    $stmtC = $pdo->prepare("SELECT COALESCE(u.Correo, p.Email) as Correo, COALESCE(u.Nombre, p.Nombre) as Nombre, p.Total, p.Direccion_Entrega, p.Fecha_Pedido
                            FROM pedido p 
                            LEFT JOIN usuario u ON p.ID_Usuario=u.ID_Usuario 
                            WHERE p.ID_Pedido=?");
    $stmtC->execute([$id_pedido]);
    $pedidoInfo = $stmtC->fetch(PDO::FETCH_ASSOC);

    // Obtener los productos del pedido
    $stmtD = $pdo->prepare("SELECT pp.Cantidad, (pp.Cantidad * pp.Precio_Unitario) AS Subtotal, pp.Nombre_Producto AS Nombre, pr.Imagen_URL
                            FROM pedido_productos pp
                            LEFT JOIN producto pr ON pp.ID_Producto = pr.ID_Producto
                            WHERE pp.ID_Pedido = ?");
    $stmtD->execute([$id_pedido]);
    $productosPedido = $stmtD->fetchAll(PDO::FETCH_ASSOC);

    // FECHA DE DESPACHO EN HORA COLOMBIA
    $dtBogota = new DateTime($pedidoInfo['Fecha_Pedido'], new DateTimeZone('UTC'));
    $dtBogota->setTimezone(new DateTimeZone('America/Bogota'));
    $fechaDespacho = $dtBogota->format('d/m/Y, H:i');

    // Construir tabla de productos en HTML alineada
    $tablaProductos = '
    <table style="border-collapse:collapse; width:100%; margin:18px 0; font-size:16px;">
      <tr>
        <th style="background:#fff; border-bottom:2px solid #dedede; color:#5c1769; padding:12px 8px; text-align:left;">Producto</th>
        <th style="background:#fff; border-bottom:2px solid #dedede; color:#5c1769; padding:12px 8px; text-align:center;">Cantidad</th>
        <th style="background:#fff; border-bottom:2px solid #dedede; color:#5c1769; padding:12px 8px; text-align:right;">Subtotal</th>
      </tr>';
    foreach ($productosPedido as $prod) {
        $imgUrl = !empty($prod['Imagen_URL']) ? $prod['Imagen_URL'] : 'img/Productos/default.jpg';
        $tablaProductos .= '<tr style="background:#fafbfe;">
            <td style="padding:10px 6px; border-bottom:1px solid #e8e8e8;">
                <div style="display:flex;align-items:center;gap:13px;">
                  <img src="https://doggies-production.up.railway.app/' . $imgUrl . '" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:7px;border:1px solid #e5e6ef;">
                  <span style="font-weight:500;color:#5c1769; font-size:17px;">' . htmlspecialchars($prod['Nombre']) . '</span>
                </div>
            </td>
            <td style="padding:10px 6px; border-bottom:1px solid #e8e8e8; text-align:center; font-size:16px;">' . intval($prod['Cantidad']) . '</td>
            <td style="padding:10px 6px; border-bottom:1px solid #e8e8e8; text-align:right; font-size:16px;">$ ' . number_format($prod['Subtotal'],0,',','.') . '</td>
        </tr>';
    }
    $tablaProductos .= '</table>';

    // ENVÍA EL EMAIL SOLO SI HAY CORREO
    if ($pedidoInfo && $pedidoInfo['Correo']) {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = "UTF-8";
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'doggiespasto22@gmail.com';
            $mail->Password = 'nfav ibzv txxd wvwl'; // Cambia tu pass
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('doggiespasto22@gmail.com', 'Doggies');
            $mail->addAddress($pedidoInfo['Correo'], $pedidoInfo['Nombre']);
            $mail->Subject = "Tu pedido fue despachado 🐾 - Doggies";
            $mail->isHTML(true);

            // Email con tabla de productos alineados
            $mail->Body = '
            <div style="max-width:540px;margin:auto;background:#f7fafd;border-radius:12px;padding:20px 16px;">
              <div style="background:#2cc6e7;color:#fff;padding:16px 0 10px 0;font-size:25px;border-radius:12px 12px 0 0;text-align:center;font-weight:700;margin:-20px -16px 20px -16px;">
                Tu pedido fue enviado 🐾
              </div>
              <div style="font-size:17px;margin-bottom:12px;">¡Hola <b>' . htmlspecialchars($pedidoInfo['Nombre']) . '</b>!</div>
              <div style="margin-bottom:10px;">
                <b>Fecha de despacho:</b> ' . $fechaDespacho . ' <span style="font-size:13px;color:#888;">(Hora Bogotá)</span>
              </div>
              <div style="margin-bottom:12px;">
                <b>Empresa de mensajería:</b> ' . htmlspecialchars($empresa) . '<br>
                <b>Número de guía:</b> ' . htmlspecialchars($guia) . '<br>
                <b>Enlace de rastreo:</b> <a href="' . htmlspecialchars($link_rastreo) . '" target="_blank">' . htmlspecialchars($link_rastreo) . '</a>
              </div>
              <div style="color:#5c1769; font-weight:bold; margin-bottom:10px;">Resumen de tu pedido:</div>
              ' . $tablaProductos . '
              <div style="font-size:18px;color:#5c1769;margin-top:12px;margin-bottom:6px;">
                <b>Total: $ ' . number_format($pedidoInfo['Total'], 0, ',', '.') . '</b>
              </div>
              <div style="margin-top:10px;">
                <b style="color:#5c1769;">Dirección de entrega:</b> ' . htmlspecialchars($pedidoInfo['Direccion_Entrega']) . '
              </div>
              <div style="margin-top:13px;">
                ¡Gracias por confiar en Doggies!<br>
                <b style="color:#2cc6e7">Doggies 🐾</b>
              </div>
            </div>
            ';

            $mail->AltBody = "Hola {$pedidoInfo['Nombre']},\nTu pedido #{$id_pedido} fue despachado.\nEmpresa: {$empresa}\nGuía: {$guia}\nRastreo: {$link_rastreo}\nTotal: $" . number_format($pedidoInfo['Total'], 0, ',', '.') . "\nGracias por tu compra en Doggies 🐾";

            $mail->send();
            $_SESSION['mensaje_alerta'] = [
                'icon' => 'success',
                'title' => '¡Éxito!',
                'text' => "Correo enviado correctamente a {$pedidoInfo['Correo']}"
            ];
        } catch (Exception $e) {
            $_SESSION['mensaje_alerta'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => "Error al enviar el correo: {$mail->ErrorInfo}"
            ];
        }
    } else {
        $_SESSION['mensaje_alerta'] = [
            'icon' => 'error',
            'title' => 'Error',
            'text' => "No se encontró correo para el pedido $id_pedido"
        ];
    }

    header("Location: admin.php");
    exit;
}

// --- Reportes de pagos ---
$mes_actual = $_GET['mes'] ?? date('m');
$ano_actual = $_GET['ano'] ?? date('Y');
$pagos = $pdo->query("SELECT * FROM pedido WHERE Estado='despachado' AND MONTH(Fecha_Pedido)='$mes_actual' AND YEAR(Fecha_Pedido)='$ano_actual'")->fetchAll(PDO::FETCH_ASSOC);

// --- Exportar a Excel (CSV) ---
if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=pagos_{$ano_actual}_{$mes_actual}.csv");
    echo "ID,Cliente,Fecha,Total,Estado,Guía,Empresa\n";
    foreach ($pagos as $p) {
        echo "{$p['ID_Pedido']},{$p['ID_Usuario']},{$p['Fecha_Pedido']},{$p['Total']},{$p['Estado']},{$p['Guia']},{$p['Empresa_Guia']}\n";
    }
    exit;
}

// --- Filtros ---
$estadoFiltro = $_GET['estado'] ?? '';
$whereEstado = $estadoFiltro ? "WHERE p.Estado = " . $pdo->quote($estadoFiltro) : "";

$usuarios = $pdo->query("SELECT u.*, r.Nombre_Rol FROM usuario u JOIN rol r ON u.ID_Rol = r.ID_Rol")->fetchAll(PDO::FETCH_ASSOC);
$productos = $pdo->query("SELECT * FROM producto")->fetchAll(PDO::FETCH_ASSOC);
$pedidos = $pdo->query("SELECT p.*, u.Nombre AS Nombre_Usuario FROM pedido p LEFT JOIN usuario u ON p.ID_Usuario = u.ID_Usuario $whereEstado ORDER BY p.Fecha_Pedido DESC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Admin - Doggies</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="css/Login.css"/>
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: 'Roboto', sans-serif; background: #f6f7fb; margin: 0; min-height: 100vh; }
    .admin-container { max-width: 1150px; margin: 2em auto; background: #fff; padding: 2em 1em; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.09); }
    h2, h3 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 2em; background: #fff;}
    th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: center; }
    th { background: #f8f8f8; font-weight: bold; }
    .btn { padding: 6px 16px; border-radius: 6px; border: none; cursor: pointer; text-decoration: none; margin: 2px 0; display: inline-block; font-weight: bold; }
    .btn-edit { background-color: #4caf50; color: #fff; }
    .btn-delete { background-color: #e53935; color: #fff; }
    .btn-add { background-color: #2196f3; color: #fff; margin: 10px 0; }
    .btn-send { background-color: #ffc107; color: #333;}
    .btn-csv { background: #00b894; color: #fff;}
    .acciones { display: flex; flex-direction: column; gap: 4px; }
    .acciones a, .acciones button, .acciones form { margin: 0 auto; }
    .filtro-form { margin-bottom: 1em; text-align: right;}
    .filtro-form label { margin-right: 0.5em;}
    .form-inline input, .form-inline select { padding: 4px 7px; border-radius: 5px; border: 1px solid #ccc;}
    .form-inline { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; justify-content: center;}
    .report-title { margin: 2em 0 0.5em 0; text-align: center; }
    .reporte-pagos { background: #e8f5e9; border-radius: 8px; padding: 1em; box-shadow: 0 2px 7px #d6ebe2; margin: 2em 0 1em 0; }
    .reporte-pagos th { background: #c8e6c9; }
    header nav .menu {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 80px;
      list-style: none;
      background: #fff;
      padding: 18px 7px;
      margin-bottom: 2em;
    }
    header nav .menu li {
      padding-top: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    header nav .menu .logo {
      padding-top: 0.8rem;
      height: 54px;
      width: auto;
      object-fit: contain;
      border-radius: 12px;
      margin: 0 18px;
      display: block;
    }
    @media (max-width: 900px) {
      .admin-container { max-width: 97vw; padding: 1em 2vw;}
      table, th, td { font-size: 0.95rem; }
      header nav .menu { gap: 20px; }
    }
    @media (max-width: 600px) {
      .admin-container { padding: 0.2em 0.2em;}
      table, th, td { font-size: 0.85rem;}
      th, td { padding: 7px 2px;}
      .form-inline { flex-direction: column; align-items: stretch;}
      .filtro-form { text-align: left; }
      header nav .menu { gap: 4px; }
      header nav .menu .logo img { height: 40px; }
    }
  </style>
</head>
<body>
  <script>
  <?php if (!empty($_SESSION['mensaje_alerta'])): ?>
    Swal.fire({
      icon: '<?= $_SESSION['mensaje_alerta']['icon'] ?>',
      title: '<?= $_SESSION['mensaje_alerta']['title'] ?>',
      text: '<?= $_SESSION['mensaje_alerta']['text'] ?>',
      confirmButtonColor: '#39c5e0'
    });
    <?php unset($_SESSION['mensaje_alerta']); ?>
  <?php endif; ?>
  </script>
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Productos.php"><i class="fas fa-dog"></i> <span style="font-size:21px;font-weight:600;margin-left:6px;">Productos</span></a></li>
        <li class="logo"><a href="index.php"><img src="img/fondo.jpg" alt="Doggies logo"></a></li>
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> <span style="font-size:21px;font-weight:600;margin-left:6px;">Servicios</span></a></li>
      </ul>
    </nav>
  </header>
  <main>
    <div class="admin-container">
      <h2>Bienvenido, <?= htmlspecialchars($nombreAdmin) ?></h2>
      <?php if (!empty($mensaje)) echo $mensaje; ?>
      <!-- Usuarios -->
      <h3>Usuarios Registrados</h3>
      <a href="nuevo_usuario.php" class="btn btn-add">➕ Añadir Usuario</a>
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Tipo de Documento</th>
            <th>Documento</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Rol</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['Nombre'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['Tipo_Documento'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['Documento'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['Correo'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['Telefono'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['Direccion'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['Nombre_Rol'] ?? '') ?></td>
            <td class="acciones">
              <a href="editar_usuario.php?id=<?= $u['ID_Usuario'] ?>" class="btn btn-edit">Editar</a>
              <a href="eliminar_usuario.php?id=<?= $u['ID_Usuario'] ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">Eliminar</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <!-- Productos -->
      <h3>Gestión de Productos</h3>
      <a href="nuevo_producto.php" class="btn btn-add">➕ Añadir Producto</a>
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Descripción</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($productos as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['Nombre']) ?></td>
            <td>$<?= number_format($p['Precio'], 0, ',', '.') ?></td>
            <td><?= $p['Stock'] ?></td>
            <td><?= htmlspecialchars($p['Descripcion']) ?></td>
            <td class="acciones">
              <a href="editar_producto.php?id=<?= $p['ID_Producto'] ?>" class="btn btn-edit">Editar</a>
              <a href="eliminar_producto.php?id=<?= $p['ID_Producto'] ?>" class="btn btn-delete" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <!-- Pedidos -->
      <h3>Pedidos Recientes</h3>
      <form method="GET" class="filtro-form">
        <label for="estado">Filtrar por estado:</label>
        <select name="estado" id="estado" onchange="this.form.submit()">
          <option value="">Todos</option>
          <option value="pagado" <?= $estadoFiltro === 'pagado' ? 'selected' : '' ?>>Pagado</option>
          <option value="pendiente" <?= $estadoFiltro === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
          <option value="despachado" <?= $estadoFiltro === 'despachado' ? 'selected' : '' ?>>Despachado</option>
          <option value="fallido" <?= $estadoFiltro === 'fallido' ? 'selected' : '' ?>>Fallido</option>
        </select>
      </form>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Dirección</th>
            <th>Pago</th>
            <th>Estado</th>
            <th>Total</th>
            <th>Guía</th>
            <th>Empresa</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pedidos as $p): ?>
          <tr>
            <td><?= $p['ID_Pedido'] ?></td>
            <td><?= htmlspecialchars($p['Nombre_Usuario'] ?? 'Invitado') ?></td>
            <td><?= $p['Fecha_Pedido'] ?></td>
            <td><?= htmlspecialchars($p['Direccion_Entrega']) ?></td>
            <td><?= htmlspecialchars($p['Metodo_Pago']) ?></td>
            <td><?= htmlspecialchars($p['Estado']) ?></td>
            <td>$<?= number_format($p['Total'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($p['Guia'] ?? '') ?></td>
            <td><?= htmlspecialchars($p['Empresa_Guia'] ?? '') ?></td>
            <td>
              <form method="POST" class="form-inline" style="margin:0; padding:0;">
                <input type="hidden" name="id_pedido" value="<?= $p['ID_Pedido'] ?>">
                <select name="nuevo_estado">
                  <option value="pendiente" <?= $p['Estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                  <option value="pagado" <?= $p['Estado'] === 'pagado' ? 'selected' : '' ?>>Pagado</option>
                  <option value="despachado" <?= $p['Estado'] === 'despachado' ? 'selected' : '' ?>>Despachado</option>
                  <option value="fallido" <?= $p['Estado'] === 'fallido' ? 'selected' : '' ?>>Fallido</option>
                </select>
                <input type="text" name="guia" value="<?= htmlspecialchars($p['Guia'] ?? '') ?>" placeholder="N° guía" style="width:80px;">
                <input type="text" name="empresa" value="<?= htmlspecialchars($p['Empresa_Guia'] ?? '') ?>" placeholder="Empresa" style="width:80px;">
                <input type="text" name="link_rastreo" value="<?= htmlspecialchars($p['Link_Rastreo'] ?? '') ?>" placeholder="Link rastreo" style="width:105px;">
                <button class="btn btn-edit" name="actualizar_pedido" type="submit" title="Actualizar"><i class="fas fa-save"></i></button>
                <button class="btn btn-send" name="enviar_email" value="1" type="submit" title="Enviar Email"><i class="fas fa-envelope"></i></button>
              </form>
              <a href="detalle_pedido.php?id=<?= $p['ID_Pedido'] ?>" class="btn btn-csv" style="margin-top:4px;display:block;">Ver</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <!-- Reportes -->
      <h3 class="report-title">Reporte de Pagos (Despachados)</h3>
      <form method="GET" class="form-inline" style="margin-bottom:10px;">
        <input type="hidden" name="estado" value="<?= htmlspecialchars($estadoFiltro) ?>">
        <label>Mes: <select name="mes"><?php
          for ($m = 1; $m <= 12; $m++) {
              $mes_nom = date("F", mktime(0,0,0,$m,10));
              echo "<option value='".str_pad($m,2,'0',STR_PAD_LEFT)."'".($mes_actual==$m?" selected":"").">".ucfirst($mes_nom)."</option>";
          }
        ?></select></label>
        <label>Año: <select name="ano"><?php
          for ($y = date('Y'); $y >= 2023; $y--) {
              echo "<option value='$y'".($ano_actual==$y?" selected":"").">$y</option>";
          }
        ?></select></label>
        <button class="btn btn-edit" type="submit">Ver</button>
        <a href="admin.php?exportar=excel&mes=<?= $mes_actual ?>&ano=<?= $ano_actual ?>" class="btn btn-csv">Descargar Excel</a>
      </form>
      <div class="reporte-pagos">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Guía</th>
              <th>Empresa</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $totalMensual = 0;
            foreach ($pagos as $p):
              $totalMensual += $p['Total'];
            ?>
            <tr>
              <td><?= $p['ID_Pedido'] ?></td>
              <td><?= htmlspecialchars($p['ID_Usuario'] ?? 'Invitado') ?></td>
              <td><?= $p['Fecha_Pedido'] ?></td>
              <td>$<?= number_format($p['Total'], 0, ',', '.') ?></td>
              <td><?= htmlspecialchars($p['Guia'] ?? '') ?></td>
              <td><?= htmlspecialchars($p['Empresa_Guia'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="text-align:right;"><b>Total mensual:</b></td>
              <td colspan="3"><b>$<?= number_format($totalMensual, 0, ',', '.') ?></b></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </main>
  <footer style="background:#333;color:#fff;text-align:center;padding:18px 0 10px 0;margin-top:20px;">
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
