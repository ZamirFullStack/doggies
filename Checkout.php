<?php
session_start();

if (empty($_SESSION['carrito'])) {
    echo "<h3>Tu carrito está vacío.</h3>";
    exit;
}

$carrito = $_SESSION['carrito'];
$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Finalizar Compra - Doggies</title>
  <link rel="stylesheet" href="css/Login.css">
  <style>
    body {
      background-color: #f5f5f5;
      font-family: 'Roboto', sans-serif;
    }
    .checkout-container {
      max-width: 1200px;
      margin: 2rem auto;
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 2rem;
    }
    .checkout-form, .summary-box {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .checkout-form h3, .summary-box h3 {
      margin-bottom: 1rem;
      border-bottom: 1px solid #ccc;
      padding-bottom: .5rem;
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
    .summary-box table {
      width: 100%;
      border-collapse: collapse;
    }
    .summary-box th, .summary-box td {
      text-align: left;
      padding: .5rem 0;
    }
    .summary-box tfoot tr td {
      font-weight: bold;
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
    .product-image {
      width: 50px;
      height: 50px;
      object-fit: cover;
    }
    .quantity-input {
      width: 60px;
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

  <div class="checkout-container">
    <form class="checkout-form" method="POST" action="pagar_carrito.php">
      <!-- Datos del cliente omitidos para brevedad -->
      <button type="submit" class="btn-primary">Realizar pedido</button>
    </form>

    <div class="summary-box">
      <h3>3. Resumen del pedido</h3>
      <table>
        <thead>
          <tr>
            <th>Producto</th>
            <th>Imagen</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($carrito as $producto): 
            $nombre = htmlspecialchars($producto['nombre'] ?? '');
            $precio = floatval($producto['precio'] ?? 0);
            $cantidad = intval($producto['cantidad'] ?? 1);
            $imagen = htmlspecialchars($producto['imagen'] ?? 'img/default.jpg');
            $subtotal = $precio * $cantidad;
            $total += $subtotal;
          ?>
            <tr>
              <td><?= $nombre ?></td>
              <td><img src="<?= $imagen ?>" alt="<?= $nombre ?>" class="product-image" /></td>
              <td><input type="number" name="cantidades[]" value="<?= $cantidad ?>" min="1" class="quantity-input" /></td>
              <td>$<?= number_format($precio, 0, ',', '.') ?></td>
              <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
            <td><strong>$<?= number_format($total, 0, ',', '.') ?></strong></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

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
