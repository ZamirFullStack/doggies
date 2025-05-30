<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Nosotros - Doggies</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link rel="icon" type="image/jpeg" href="img/fondo.jpg" />
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(to right, #f8f9fa, #e9f7ef);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      color: #333;
      margin: 0;
    }
    header {
      background-color: #fff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    nav ul.menu {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1em 2em;
      list-style: none;
      margin: 0;
    }
    
    .menu li {
      flex: 1;
      text-align: center;
    }
    .menu li.logo {
      flex: 0 0 auto;
    }
    .menu li.logo a {
      display: block;
      width: 150px;
      height: 60px;
      background-image: url('img/fondo.jpg');
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      text-indent: -9999px;
    }
    .menu a {
      text-decoration: none;
      color: #333;
      font-weight: bold;
    }
    main {
      flex: 1;
      padding: 50px 20px;
      max-width: 1000px;
      margin: auto;
    }
    main h1, main h2, .legal h3 {
      text-align: center;
      color: #2c3e50;
    }
    main p {
      font-size: 1.05rem;
      line-height: 1.7;
      text-align: justify;
      margin-bottom: 1em;
    }
    .redes-gallery {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
      margin: 40px 0;
    }
    .redes-gallery iframe {
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .legal {
      margin-top: 50px;
    }
    .legal p {
      font-size: 0.95rem;
    }
    footer {
      background-color: rgba(51, 51, 51, 0.95);
      color: white;
      text-align: center;
      padding: 2em 1em 1em;
    }
    .footer-content h3 {
      font-size: 1.3em;
    }
    .social-links {
      display: flex;
      justify-content: center;
      gap: 1.2em;
      margin-top: 0.5em;
    }
    .social-links a {
      font-size: 1.5rem;
      color: #fff;
      transition: color 0.3s ease;
    }
    .social-links a:hover {
      color: #ffd700;
    }
    footer::after {
      content: "© 2025 Doggies. Todos los derechos reservados.";
      display: block;
      font-size: 0.9rem;
      color: #ccc;
      margin-top: 1em;
    }
  </style>
</head>
<body>
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
    <h1>Sobre Nosotros</h1>
    <p>
      En <strong>Doggies</strong>, nuestra pasión son los animales. Desde el primer día nos hemos enfocado en ofrecer productos de calidad y servicios especializados que garanticen el bienestar y felicidad de las mascotas, especialmente los perros. Nuestra tienda es un punto de encuentro para dueños comprometidos y amantes de los animales.
    </p>
    <p>
      Nuestro equipo está conformado por profesionales en veterinaria, entrenadores y asesores especializados en nutrición canina. Nos esforzamos por brindar una experiencia personalizada, segura y confiable, tanto en tienda como a través de nuestra plataforma online.
    </p>

    <h2>Mira más en nuestras redes</h2>
    <div class="redes-gallery">
      <iframe src="https://www.instagram.com/doggiespaseadores/embed" width="320" height="400" frameborder="0"></iframe>
      <iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Fprofile.php%3Fid%3D100069951193254&tabs=timeline&width=340&height=400" width="340" height="400" style="border:none;overflow:hidden" frameborder="0" allowfullscreen></iframe>
    </div>

    <section class="legal">
      <h3>Política de Cambios y Devoluciones</h3>
      <p>
        En Doggies estamos comprometidos con tu satisfacción. Si algún producto no cumple con tus expectativas, puedes devolverlo en un plazo máximo de 7 días hábiles a partir de la fecha de entrega, siempre y cuando el artículo esté sin usar, en su empaque original y acompañado de la factura de compra. El proceso de devolución se iniciará una vez se verifique el estado del producto. El reembolso se realizará utilizando el mismo método de pago original dentro de los 10 días hábiles siguientes.
      </p>
      <p>
        Para los servicios (guardería, paseos, peluquería), aceptamos cancelaciones o reprogramaciones con al menos 24 horas de antelación. De lo contrario, se aplicará una penalización del 50% del valor del servicio reservado. Para iniciar cualquier proceso de cambio o devolución, escríbenos a <strong>doggiespasto@gmail.com</strong>.
      </p>

      <h3>Política de Privacidad y Protección de Datos</h3>
      <p>
        Doggies respeta y protege la privacidad de todos sus clientes y visitantes. Todos los datos personales que recopilamos a través de formularios de contacto, registro de usuarios o compras en línea se utilizan exclusivamente para procesar tus pedidos, brindarte soporte, enviar actualizaciones de tus compras y ofrecerte promociones relacionadas con nuestros productos o servicios.
      </p>
      <p>
        Cumplimos con la Ley 1581 de 2012 y el Decreto 1377 de 2013 de Colombia. Implementamos medidas de seguridad físicas, electrónicas y administrativas para proteger tu información contra acceso no autorizado. Puedes solicitar en cualquier momento la actualización, corrección o eliminación de tus datos mediante solicitud escrita al correo <strong>doggiespasto@gmail.com</strong>.
      </p>

      <h3>Condiciones Generales de Uso</h3>
      <p>
        El uso del sitio web de Doggies implica la aceptación de las siguientes condiciones: Todos los contenidos, precios, imágenes y promociones están sujetos a cambios sin previo aviso. Doggies se reserva el derecho de cancelar cualquier pedido si se detecta actividad sospechosa, uso indebido del sistema, suplantación de identidad o errores de precios evidentes.
      </p>
      <p>
        El cliente es responsable de la veracidad de la información ingresada durante su compra. No está permitido el uso del sitio para fines ilegales, distribuir virus o manipular los procesos comerciales. Todo contenido generado por Doggies (textos, imágenes, logotipos) es propiedad de la marca y no puede ser utilizado sin autorización previa por escrito.
      </p>
    </section>
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
