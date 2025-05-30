<?php
session_start();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

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

// Agregar producto al carrito
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['nombre'], $_POST['precio'], $_POST['cantidad'], $_POST['imagen']) &&
    !isset($_POST['update'])
) {
    $producto = [
        'nombre'   => $_POST['nombre'],
        'precio'   => floatval($_POST['precio']),
        'cantidad' => max(1, min(25, intval($_POST['cantidad']))),
        'imagen'   => $_POST['imagen']
    ];
    $_SESSION['carrito'][] = $producto;
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos - Doggies</title>
  <link rel="stylesheet" href="css/Productos.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .modal { display:none; position:fixed; z-index:1000; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); }
    .modal-contenido { background:#fff; margin:5% auto; padding:30px; width:90%; max-width:800px; border-radius:18px; box-shadow:0 0 20px rgba(0,0,0,0.18);}
    .modal-contenido h2 {
      text-align:center; margin-bottom:20px; color: #28a745; font-size:1.55em; font-weight:700; letter-spacing: 0.01em;
    }
    /* --- TABLA RESPONSIVE --- */
    .modal-contenido table {
      width:100%; border-collapse:collapse; margin-bottom:18px;
      display: block; overflow-x: auto; white-space: nowrap;
    }
    .modal-contenido thead, .modal-contenido tbody, .modal-contenido tr {
      display: table; width: 100%; table-layout: fixed;
    }
    .modal-contenido th, .modal-contenido td {
      padding:15px 7px; text-align:center; border:1px solid #ccc;
      font-size: 1rem;
      background: #fff;
      word-break: break-word;
    }
    .modal-contenido thead th {
      background:#f4f4f4; color: #333; font-weight: 600; font-size: 1.09em;
    }
    .modal-contenido img {
      width:56px; height:56px; object-fit:cover; margin-right:8px; vertical-align:middle; border-radius: 7px;
    }
    .producto-descripcion {
      display: flex;
      align-items: center;
      gap: 8px;
      text-align: left;
      min-width: 120px;
      max-width: 220px;    /* Limita el ancho en escritorio */
      overflow: hidden;
    }
    .producto-descripcion span {
      display: block;
      font-weight: 500;
      color: #222;
      white-space: normal;
      overflow-wrap: break-word;
      word-break: break-word;
      max-width: 150px;    /* Ajusta según tamaño de tu celda */
      font-size: 1rem;
    }
    .cantidad-input {
      width:54px; text-align:center; padding:7px 3px; font-size:1em; border-radius: 7px; border:1px solid #bbb;
    }
    .modal-contenido button, .modal-contenido form button {
      margin:7px 8px; padding:14px 25px; border:none; border-radius:9px;
      background:#28a745; color:#fff; cursor:pointer; font-size:1.13em; font-weight: 600; min-width: 150px;
      transition: background 0.18s;
      display: inline-block;
    }
    .modal-contenido button:hover, .modal-contenido form button:hover { background:#218838; }
    .modal-contenido form { display:inline-block; }
    .carrito-animado { position:fixed; z-index:2000; transition:transform .8s, opacity .8s; width:60px; height:60px; object-fit:cover; }
    .agotado { color:red; font-weight:bold; margin-top:10px; }

    /* Responsive */
    @media (max-width: 650px) {
      .modal-contenido { padding: 8px 1px; min-width: 0; width: 99%; border-radius:12px;}
      .modal-contenido h2 { font-size: 1.1em; }
      .modal-contenido table { font-size: 0.96em; }
      .modal-contenido th, .modal-contenido td { padding: 8px 2px; font-size: 0.93em; }
      .producto-descripcion {
        flex-direction: column;
        align-items: flex-start;
        min-width:80px;
        max-width: 88px;
      }
      .producto-descripcion img { margin-bottom: 2px; margin-right:0; }
      .producto-descripcion span {
        max-width: 78px;
        font-size: 0.98em;
      }
      .cantidad-input { width:38px; font-size:0.97em; padding:5px 2px;}
      .modal-contenido button, .modal-contenido form button {
        width: 98%; max-width: 330px; margin: 7px auto; display: block; font-size:1.09em;
      }
      .modal-contenido form { display:block; }
    }
    @media (max-width:400px) {
      .modal-contenido { padding:2px 0;}
      .modal-contenido th, .modal-contenido td { padding:3px 1px; font-size:0.90em;}
      .cantidad-input { width:32px;}
    }
  </style>
</head>
<body>
  <header>
    <nav>
      <ul class="menu">
        <li><a href="Servicios.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
        <li class="logo"><a href="index.php">Doggies</a></li>
        <li id="icono-carrito"><a href="carrito.php"><i class="fas fa-cart-shopping"></i> Carrito <span id="contador-carrito"><?php echo '(' . count($_SESSION['carrito']) . ')'; ?></span></a></li>
      </ul>
    </nav>
  </header>

  <div class="page-container">
    <aside class="filtro">
      <h2>Filtrar por edad</h2>
      <label><input type="checkbox" class="filtro-edad" value="cachorro"> Cachorro</label>
      <label><input type="checkbox" class="filtro-edad" value="adulto"> Adulto</label>
      <label><input type="checkbox" class="filtro-edad" value="senior"> Senior</label>
      <h2>Precio</h2>
      <label>Min: <input type="number" id="precio-min"></label>
      <label>Max: <input type="number" id="precio-max"></label>
      <button onclick="filtrarProductos()">Filtrar</button>
    </aside>

    <main class="productos-page">
      <div class="productos-grid">
        <?php
        require 'conexion.php';
        $productos = $pdo->query("SELECT * FROM producto")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($productos as $p):
            $nombre      = htmlspecialchars($p['Nombre']);
            $imagen      = $p['Imagen_URL'] ?: 'img/default.jpg';
            $descripcion = htmlspecialchars($p['Descripcion']);
            $precio      = floatval($p['Precio']);
            $stock       = intval($p['Stock']);
            $edad        = $p['edad'] ?: 'adulto';
            $precioF     = number_format($precio,0,',','.');
        ?>
        <div class="producto-card" data-edad="<?php echo $edad; ?>" data-precio="<?php echo $precio; ?>">
          <img src="<?php echo $imagen; ?>" alt="<?php echo $nombre; ?>">
          <div class="producto-info">
            <h3><?php echo $nombre; ?></h3>
            <p><?php echo $descripcion; ?></p>
            <span>$<?php echo $precioF; ?></span>

            <?php if ($stock > 0): ?>
            <div class="cantidad-comprar">
              <button onclick="cambiarCantidad(this,-1)">−</button>
              <input type="number" value="1" min="1" max="25" readonly>
              <button onclick="cambiarCantidad(this,1)">+</button>
            </div>
            <form method="POST">
              <input type="hidden" name="nombre"   value="<?php echo $nombre; ?>">
              <input type="hidden" name="precio"   value="<?php echo $precio; ?>">
              <input type="hidden" name="cantidad" value="1" class="input-cantidad">
              <input type="hidden" name="imagen"   value="<?php echo $imagen; ?>">
              <button type="submit" class="btn-comprar">Comprar</button>
            </form>
            <?php else: ?>
              <p class="agotado">Agotado</p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

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

  <script>
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

    document.addEventListener('DOMContentLoaded', () => {
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

          const clone = img.cloneNode();
          clone.classList.add('carrito-animado');
          clone.style.top  = rect.top + 'px';
          clone.style.left = rect.left + 'px';
          document.body.appendChild(clone);
          requestAnimationFrame(()=>{
            clone.style.transform =
              `translate(${cartIcon.left-rect.left}px,${cartIcon.top-rect.top}px) scale(0.2)`;
            clone.style.opacity = '0';
          });
          setTimeout(()=>clone.remove(),800);

          const dataPost = new FormData(form);
          const qtyCard  = parseInt(card.querySelector('input[type="number"]').value,10) || 1;
          dataPost.set('cantidad', qtyCard);

          fetch('Productos.php', { method: 'POST', body: dataPost })
            .then(()=>{
              modal.style.display = 'block';
              let count = parseInt(cont.textContent.replace(/\D/g,''),10) || 0;
              count += qtyCard;
              cont.textContent = `(${count})`;

              const name  = form.nombre.value;
              const price = parseFloat(form.precio.value);
              const src   = form.imagen.value;
              tabla.innerHTML = '';
              const tr = document.createElement('tr');
              tr.innerHTML = `
                <td class="producto-descripcion">
                  <img src="${src}" alt="${name}"><span>${name}</span>
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
  </script>
</body>
</html>
