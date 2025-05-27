# 🐶 Doggies - Tienda Canina

**Doggies** es una plataforma web para la venta de productos y servicios para perros. Incluye funcionalidades como carrito de compras, pagos en línea con Mercado Pago, gestión de reservas, panel de administración y más.

---

## 🔧 Tecnologías Utilizadas

* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP 8.2 (con Apache)
* **Base de Datos:** MySQL
* **Pagos:** Mercado Pago SDK (modo sandbox)
* **Despliegue:** Docker + Railway

---

## 📁 Estructura del Proyecto

```
Doggies/
├── css/                    # Estilos CSS para todas las vistas
├── img/                    # Imágenes del sitio
├── videos/                 # Videos para modales
├── carrito.php             # Página del carrito de compras
├── conexion.php            # Configuración de la base de datos
├── index.php               # Página de bienvenida
├── login.php               # Login de usuarios
├── logout.php              # Cierre de sesión
├── registro.php            # Registro de nuevos usuarios
├── mi_cuenta.php           # Perfil del usuario e historial de compras
├── productos.php           # Catálogo de productos (con filtro por edad/precio)
├── servicios.php           # Catálogo de servicios con modal de reserva
├── agregar_carrito.php     # Script para añadir productos al carrito
├── eliminar.php            # Script para eliminar productos del carrito
├── pagar_carrito.php       # Inicia pago con Mercado Pago
├── pago_exitoso.php        # Página de éxito después del pago
├── pago_fallido.php        # Página de error si el pago falla
├── pago_pendiente.php      # Página cuando el pago está pendiente
├── actualizar_perfil.php   # Permite editar datos del perfil del usuario
├── Dockerfile              # Imagen Docker para Railway
├── railway.json            # Configuración de despliegue Railway
├── .gitignore              # Ignora archivos innecesarios en GitHub
└── README.md               # Este archivo
```

---

## 🔐 Seguridad y Sesiones

* Todas las páginas privadas requieren sesión activa (`$_SESSION['usuario']`).
* Contraseñas se almacenan con `password_hash`.

---

## 🛒 Funcionalidades Destacadas

* Filtro por edad y precio en productos
* Sistema de carrito con cantidades y subtotal
* Historial de compras con imágenes, fecha, forma de pago y opción de volver a comprar
* Gestión de stock: los productos agotados no pueden ser comprados
* Reservas para servicios con campos personalizados
* Envío de reservas directo a WhatsApp

---

## 💳 Pagos con Mercado Pago

* **Integración vía Checkout Pro**
* Configuración en `pagar_carrito.php`
* Redirección automática a `pago_exitoso.php`, `pago_fallido.php`, `pago_pendiente.php`
* Validación del usuario logueado antes de pagar

---

## 🚀 Despliegue con Railway

1. Crea cuenta en Railway e importa el repositorio de GitHub
2. Railway detecta el `Dockerfile` y construye la imagen
3. Configura tu base de datos MySQL desde Railway o externa
4. Establece variables de entorno si usas `.env`
5. Obtén tu dominio `.railway.app` para usar en Mercado Pago

---

## 🧪 Pruebas y Datos de Prueba

* Puedes usar cuentas de test de Mercado Pago:
  [https://www.mercadopago.com.co/developers/panel](https://www.mercadopago.com.co/developers/panel)

---

## 📬 Contacto

Soporte: [doggiespasto@gmail.com](mailto:doggiespasto@gmail.com)
Instagram: [@doggiespaseadores](https://www.instagram.com/doggiespaseadores)
" " 
