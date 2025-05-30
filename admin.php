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

// --- Actualización de estado/guía y envío de email ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_pedido'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $nuevo_estado = $_POST['nuevo_estado'];
    $guia = trim($_POST['guia']);
    $empresa = trim($_POST['empresa']);
    $link_rastreo = trim($_POST['link_rastreo']);

    $stmt = $pdo->prepare("UPDATE pedido SET Estado=?, Guia=?, Empresa_Guia=?, Link_Rastreo=? WHERE ID_Pedido=?");
    $stmt->execute([$nuevo_estado, $guia, $empresa, $link_rastreo, $id_pedido]);

    // Envío de email si fue solicitado
    if (!empty($_POST['enviar_email'])) {
        // Busca correo del cliente
        $stmtC = $pdo->prepare("SELECT u.Correo, u.Nombre, p.Total FROM pedido p LEFT JOIN usuario u ON p.ID_Usuario=u.ID_Usuario WHERE p.ID_Pedido=?");
        $stmtC->execute([$id_pedido]);
        $pedidoInfo = $stmtC->fetch(PDO::FETCH_ASSOC);

        if ($pedidoInfo && $pedidoInfo['Correo']) {
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP (puedes usar Gmail, SMTP propio, etc.)
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';         // Cambia según tu proveedor SMTP
                $mail->SMTPAuth = true;
                $mail->Username = 'doggiespasto22@gmail.com';   // Tu correo Gmail
                $mail->Password = 'nfav ibzv txxd wvwl'; // Contraseña de aplicación
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('doggiespasto22@gmail.com', 'Doggies');
                $mail->addAddress($pedidoInfo['Correo'], $pedidoInfo['Nombre']);
                $mail->Subject = "Tu pedido ha sido despachado - Doggies";
                $mail->isHTML(true);
                $mail->Body = "<h2>¡Tu pedido ha sido despachado!</h2>
                    <p>Hola <b>{$pedidoInfo['Nombre']}</b>,</p>
                    <p>Tu pedido #{$id_pedido} ha sido despachado.<br>
                    <b>Empresa de mensajería:</b> {$empresa}<br>
                    <b>Número de guía:</b> {$guia}<br>
                    <b><a href='{$link_rastreo}' target='_blank'>Rastrea tu pedido aquí</a></b><br><br>
                    <b>Total:</b> $" . number_format($pedidoInfo['Total'],0,',','.') . "<br><br>
                    ¡Gracias por tu compra en Doggies 🐾!
                    </p>";

                $mail->AltBody = "Hola {$pedidoInfo['Nombre']},\nTu pedido #{$id_pedido} ha sido despachado.\n
                Empresa de mensajería: {$empresa}\nNúmero de guía: {$guia}\nRastrea tu pedido aquí: {$link_rastreo}\nTotal: $" . number_format($pedidoInfo['Total'],0,',','.') . "\nGracias por tu compra en Doggies 🐾";

                $mail->send();
            } catch (Exception $e) {
                // Puedes mostrar un mensaje si falla el envío
                error_log("Mailer Error: {$mail->ErrorInfo}");
            }
        }
    }
    header("Location: admin.php?msg=actualizado");
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
<!-- AQUI TODO TU HTML (lo puedes dejar igual) -->
<!-- Solo cambia los botones de formulario de pedidos, como lo tienes: 
     <button class="btn btn-edit" name="actualizar_pedido" type="submit" title="Actualizar">...</button>
     <button class="btn btn-send" name="enviar_email" value="1" title="Enviar Email">...</button>
-->

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Admin - Doggies</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="css/Login.css"/>
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
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
    @media (max-width: 900px) {
      .admin-container { max-width: 97vw; padding: 1em 2vw;}
      table, th, td { font-size: 0.95rem; }
    }
    @media (max-width: 600px) {
      .admin-container { padding: 0.2em 0.2em;}
      table, th, td { font-size: 0.85rem;}
      th, td { padding: 7px 2px;}
      .form-inline { flex-direction: column; align-items: stretch;}
      .filtro-form { text-align: left; }
    }
  </style>
</head>
<body>
  <header>
    <nav>
      <ul class="menu" style="display:flex;justify-content:space-between;align-items:center;list-style:none;background:#fff;padding:14px 7px;margin-bottom:2em;">
        <li><a href="Productos.php"><i class="fas fa-dog"></i> Productos</a></li>
        <li class="logo"><a href="index.php"><img src="img/fondo.jpg" style="height:38px;width:92px;object-fit:contain;border-radius:12px;"></a></li>
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
      </ul>
    </nav>
  </header>
  <main>
    <div class="admin-container">
      <h2>Bienvenido, <?= htmlspecialchars($nombreAdmin) ?></h2>
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
                <button class="btn btn-send" name="enviar_email" value="1" title="Enviar Email"><i class="fas fa-envelope"></i></button>
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
              <td><?= htmlspecialchars($p['ID_Usuario']) ?></td>
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
