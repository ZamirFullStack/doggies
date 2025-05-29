<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['ID_Rol'] != 2) {
    header("Location: index.php");
    exit;
}

$nombreAdmin = $_SESSION['usuario']['Nombre'];

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
  <link rel="stylesheet" href="css/Login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .admin-container {
      max-width: 1100px;
      margin: 2em auto;
      background: #fff;
      padding: 2em;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .admin-container h2 { text-align: center; margin-bottom: 1em; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 2em; }
    th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: center; }
    th { background-color: #f3f3f3; font-weight: bold; }
    .btn { padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; text-decoration: none; margin: 2px 0; display: inline-block; }
    .btn-edit { background-color: #4caf50; color: #fff; }
    .btn-delete { background-color: #e53935; color: #fff; }
    .btn-add { background-color: #2196f3; color: #fff; margin: 10px 0; display: inline-block; }
    .acciones { display: flex; flex-direction: column; gap: 4px; }
    .acciones a { margin: 0 auto; }
    form input, form select { padding: 0.5em; width: 100%; margin-bottom: 0.5em; }
    .filtro-form { margin-bottom: 1em; }
  </style>
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

  <main>
    <div class="admin-container">
      <h2>Bienvenido, <?= htmlspecialchars($nombreAdmin) ?></h2>

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
            <td><?= htmlspecialchars($u['Nombre']) ?></td>
            <td><?= htmlspecialchars($u['Tipo_Documento'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($u['Documento']) ?></td>
            <td><?= htmlspecialchars($u['Correo']) ?></td>
            <td><?= htmlspecialchars($u['Telefono']) ?></td>
            <td><?= htmlspecialchars($u['Direccion']) ?></td>
            <td><?= $u['Nombre_Rol'] ?></td>
            <td class="acciones">
              <a href="editar_usuario.php?id=<?= $u['ID_Usuario'] ?>" class="btn btn-edit">Editar</a>
              <a href="eliminar_usuario.php?id=<?= $u['ID_Usuario'] ?>" class="btn btn-delete" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">Eliminar</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

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

      <h3>Pedidos Recientes</h3>
      <form method="GET" class="filtro-form">
        <label for="estado">Filtrar por estado:</label>
        <select name="estado" id="estado" onchange="this.form.submit()">
          <option value="">Todos</option>
          <option value="pagado" <?= $estadoFiltro === 'pagado' ? 'selected' : '' ?>>Pagado</option>
          <option value="pendiente" <?= $estadoFiltro === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
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
            <td><a href="detalle_pedido.php?id=<?= $p['ID_Pedido'] ?>" class="btn btn-edit">Ver</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
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
