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
  <title>Checkout - Doggies</title>
  <link rel="stylesheet" href="css/carrito.css">
  <style>
    form.checkout-form { max-width: 600px; margin: auto; padding: 1rem; border: 1px solid #ccc; border-radius: 10px; background: #f9f9f9; }
    form.checkout-form .input-group { margin-bottom: 1rem; }
    form.checkout-form label { display: block; margin-bottom: 0.5rem; }
    form.checkout-form input, form.checkout-form textarea { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 5px; }
    .boton-comprar { display: inline-block; padding: 0.75rem 1.5rem; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px; text-align: center; }
  </style>
</head>
<body>
  <h2 style="text-align:center;">Finalizar Compra</h2>

  <div style="max-width: 800px; margin: auto;">
    <table class="carrito-table">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Precio</th>
          <th>Cantidad</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($carrito as $producto): 
          $subtotal = $producto['precio'] * $producto['cantidad'];
          $total += $subtotal;
        ?>
          <tr>
            <td><?= htmlspecialchars($producto['nombre']) ?></td>
            <td>$<?= number_format($producto['precio'], 0, ',', '.') ?></td>
            <td><?= $producto['cantidad'] ?></td>
            <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3" style="text-align: right;">Total:</th>
          <th>$<?= number_format($total, 0, ',', '.') ?></th>
        </tr>
      </tfoot>
    </table>

    <form class="checkout-form" method="POST" action="pagar_carrito.php">
      <h3>Datos del Comprador</h3>
      <div class="input-group">
        <label for="nombre">Nombre completo</label>
        <input type="text" name="nombre" id="nombre" required>
      </div>
      <div class="input-group">
        <label for="correo">Correo electrónico</label>
        <input type="email" name="correo" id="correo" required>
      </div>
      <div class="input-group">
        <label for="direccion">Dirección de entrega</label>
        <textarea name="direccion" id="direccion" required></textarea>
      </div>
      <div class="input-group">
        <input type="checkbox" id="terminos" required>
        <label for="terminos">Acepto los términos y condiciones</label>
      </div>
      <button type="submit" class="boton-comprar">Proceder al pago</button>
    </form>
  </div>
</body>
</html>
