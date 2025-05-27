<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$carrito = $_SESSION['carrito'];
$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Carrito - Doggies</title>
  <link rel="stylesheet" href="css/carrito.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
    }
    main {
      flex: 1;
    }
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

  <main class="auth-container" style="max-width: 1000px;">
    <h2>Mi Carrito</h2>
    <?php if (empty($carrito)): ?>
      <p>Tu carrito está vacío.</p>
    <?php else: ?>
      <table class="carrito-table">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($carrito as $index => $producto): 
            $subtotal = $producto['precio'] * $producto['cantidad'];
            $total += $subtotal;
          ?>
            <tr>
              <td><?= htmlspecialchars($producto['nombre']) ?></td>
              <td>$<?= number_format($producto['precio'], 0, ',', '.') ?></td>
              <td><?= $producto['cantidad'] ?></td>
              <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
              <td>
                <form method="POST" action="eliminar.php" onsubmit="return confirm('¿Eliminar este producto del carrito?');">
                  <input type="hidden" name="index" value="<?= $index ?>">
                  <button type="submit">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" style="text-align: right;">Total:</th>
            <th colspan="2">$<?= number_format($total, 0, ',', '.') ?></th>
          </tr>
        </tfoot>
      </table>
      <div class="resumen-footer">
        <a href="pagar_carrito.php" class="boton-comprar">Finalizar compra</a>
      </div>
    <?php endif; ?>
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
