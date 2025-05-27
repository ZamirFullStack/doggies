<?php
require __DIR__ . '/vendor/autoload.php';

if (class_exists('MercadoPago\SDK')) {
    echo "✅ MercadoPago SDK cargado correctamente";
} else {
    echo "❌ No se pudo cargar MercadoPago\\SDK";
}
?>