<?php
session_start();
require 'conexion.php'; // conexión PDO

$carrito = $_SESSION['carrito'] ?? [];
$total = array_reduce($carrito, fn($sum, $p) => $sum + $p['precio'] * $p['cantidad'], 0);

$exito = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $metodo = $_POST['metodo_pago'] ?? '';
    $pago_info = $_POST['pago_info'] ?? '';

    if ($nombre && $telefono && $email && $direccion && $metodo && $pago_info) {
        try {
            // Verificar o crear rol 'cliente'
            $stmt = $pdo->prepare("SELECT ID_Rol FROM Rol WHERE Nombre_Rol = 'cliente'");
            $stmt->execute();
            $id_rol = $stmt->fetchColumn();

            if (!$id_rol) {
                $stmt = $pdo->prepare("INSERT INTO Rol (Nombre_Rol) VALUES ('cliente')");
                $stmt->execute();
                $id_rol = $pdo->lastInsertId();
            }

            // Verificar si el usuario ya existe por correo
            $stmt = $pdo->prepare("SELECT ID_Usuario FROM Usuario WHERE Correo = ?");
            $stmt->execute([$email]);
            $id_usuario = $stmt->fetchColumn();

            // Si no existe, crearlo
            if (!$id_usuario) {
                $stmt = $pdo->prepare("INSERT INTO Usuario (Nombre, Correo, Contraseña, Telefono, Direccion, ID_Rol) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $email, 'externo', $telefono, $direccion, $id_rol]);
                $id_usuario = $pdo->lastInsertId();
            }

            // Insertar pedido
            $stmt = $pdo->prepare("INSERT INTO Pedido (ID_Usuario, Direccion_Entrega, Metodo_Pago, Estado, Total) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_usuario, $direccion, $metodo, 'pagado', $total]);
            $pedido_id = $pdo->lastInsertId();

            // Insertar productos del carrito
            foreach ($carrito as $item) {
                $stmt = $pdo->prepare("INSERT INTO pedido_productos 
                (ID_Pedido, ID_Producto, Nombre_Producto, Cantidad, Precio_Unitario)
                VALUES (?, NULL, ?, ?, ?)");
                $stmt->execute([$pedido_id, $item['nombre'], $item['cantidad'], $item['precio']]);
            }

            $_SESSION['carrito'] = [];
            $exito = true;

        } catch (Exception $e) {
            $error = "Error al guardar el pedido: " . $e->getMessage();
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Datos de Pago</title>
  <link rel="stylesheet" href="css/pago.css">
</head>
<body>
  <div class="container">
    <h1>Resumen de Compra</h1>

    <?php if ($exito): ?>
      <div class="success">
        ✅ ¡Gracias por tu compra, <strong><?= htmlspecialchars($nombre) ?></strong>!
        <p>Te contactaremos al <strong><?= htmlspecialchars($telefono) ?></strong> o <strong><?= htmlspecialchars($email) ?></strong>.</p>
      </div>
    <?php elseif (empty($carrito)): ?>
      <p>Tu carrito está vacío.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
          <?php foreach ($carrito as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['nombre']) ?></td>
              <td><?= $p['cantidad'] ?></td>
              <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
              <td>$<?= number_format($p['precio'] * $p['cantidad'], 0, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h3>Total a pagar: $<?= number_format($total, 0, ',', '.') ?></h3>

      <?php if ($error): ?>
        <p class="alert"><?= $error ?></p>
      <?php endif; ?>

      <form method="POST">
        <h2>Datos del Cliente</h2>
        <input type="text" name="nombre" placeholder="Nombre completo" required>
        <input type="text" name="telefono" placeholder="Teléfono de contacto" required>
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="text" name="direccion" placeholder="Dirección de entrega" required>

        <h2>Forma de pago</h2>
        <select name="metodo_pago" id="metodo_pago" required onchange="mostrarCamposPago()">
          <option value="">Selecciona un método</option>
          <option value="tarjeta">Tarjeta de crédito</option>
          <option value="nequi">Nequi</option>
          <option value="efecty">Efecty</option>
        </select>

        <div id="campos-pago"></div>
        <input type="hidden" name="pago_info" id="pago_info" />

        <button type="submit" onclick="prepararEnvio()">Pagar ahora</button>
      </form>
    <?php endif; ?>
  </div>

  <script>
    function mostrarCamposPago() {
      const metodo = document.getElementById('metodo_pago').value;
      const campos = document.getElementById('campos-pago');
      campos.innerHTML = '';

      if (metodo === 'tarjeta') {
        campos.innerHTML = `
          <input type="text" id="campo_pago" placeholder="Número de tarjeta" required>
          <input type="text" placeholder="Fecha (MM/AA)" required>
          <input type="text" placeholder="CVV" required>
        `;
      } else if (metodo === 'nequi') {
        campos.innerHTML = `<input type="text" id="campo_pago" placeholder="Número Nequi" required>`;
      } else if (metodo === 'efecty') {
        campos.innerHTML = `<p>Se generará un código para pagar en Efecty.</p>
                            <input type="hidden" id="campo_pago" value="Pago en punto Efecty" />`;
      }
    }

    function prepararEnvio() {
      const campo = document.getElementById('campo_pago');
      const hidden = document.getElementById('pago_info');
      if (campo) hidden.value = campo.value;
    }
  </script>
</body>
</html>
